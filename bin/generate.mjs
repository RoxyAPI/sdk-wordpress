#!/usr/bin/env node
/**
 * RoxyAPI WordPress plugin code generator.
 *
 * Fetches the live RoxyAPI OpenAPI spec, writes it to specs/openapi.json
 * (the git-tracked baseline for drift detection), and emits the auto-generated
 * PHP client layer plus per-endpoint block.json files into src/Generated/ and
 * blocks/generated/. Hand crafted hero blocks and shortcodes in src/ and blocks/
 * are NEVER overwritten.
 *
 * Generated outputs:
 *   specs/openapi.json                 - committed spec snapshot (drift baseline)
 *   src/Generated/Client.php           - typed PHP method per endpoint
 *   src/Generated/Endpoints.php        - endpoint registry with TTLs
 *   src/Generated/Shortcodes/*.php     - one shortcode class per non-hero endpoint
 *   blocks/generated/{slug}/block.json - one generated block.json per non-hero endpoint
 *
 * Usage:
 *   npm run generate
 *
 * Drift check (CI and pre-push hook):
 *   npm run generate:check
 *
 * See docs/wordpress-plugin.md section 7.5 in the parent project for the full
 * design rationale.
 */

import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..');

const OPENAPI_URL = process.env.ROXYAPI_OPENAPI_URL || 'https://roxyapi.com/api/v2/openapi.json';
const SPEC_FILE  = path.join(ROOT, 'specs', 'openapi.json');
const OUT_PHP    = path.join(ROOT, 'src', 'Generated');
const OUT_BLOCKS = path.join(ROOT, 'blocks', 'generated');

const ttlMap   = JSON.parse(await fs.readFile(path.join(__dirname, 'ttl-map.json'), 'utf8'));
const heroList = JSON.parse(await fs.readFile(path.join(__dirname, 'hero-endpoints.json'), 'utf8'));
const heroSet  = new Set(heroList);

console.log(`[generate] fetching ${OPENAPI_URL}`);
const response = await fetch(OPENAPI_URL);
if (!response.ok) {
	console.error(`[generate] fetch failed: ${response.status} ${response.statusText}`);
	process.exit(1);
}
const spec = await response.json();
console.log(`[generate] loaded spec with ${Object.keys(spec.paths || {}).length} paths`);

// Write the spec snapshot
await fs.mkdir(path.dirname(SPEC_FILE), { recursive: true });
await fs.writeFile(SPEC_FILE, JSON.stringify(spec, null, 2) + '\n', 'utf8');
console.log(`[generate] wrote ${SPEC_FILE}`);

// Prepare output directories (clean them first)
await fs.rm(path.join(OUT_PHP, 'Shortcodes'), { recursive: true, force: true });
await fs.mkdir(path.join(OUT_PHP, 'Shortcodes'), { recursive: true });
// Keep the README in OUT_PHP
const readmePath = path.join(OUT_PHP, 'README.md');
const readmeExists = await fs.stat(readmePath).catch(() => null);
const readmeContent = readmeExists ? await fs.readFile(readmePath, 'utf8') : null;
await fs.rm(OUT_BLOCKS, { recursive: true, force: true });
await fs.mkdir(OUT_BLOCKS, { recursive: true });

// ---------------------------------------------------------------------------
// Walk spec.paths and collect operations
// ---------------------------------------------------------------------------

/** @type {Array<{operationId: string, method: string, path: string, tag: string, summary: string, description: string, parameters: any[], requestBody: any}>} */
const operations = [];

for (const [apiPath, methods] of Object.entries(spec.paths || {})) {
	for (const [method, op] of Object.entries(methods)) {
		if (!['get', 'post', 'put', 'patch', 'delete'].includes(method)) continue;
		if (!op.operationId) continue;
		operations.push({
			operationId: op.operationId,
			method: method.toUpperCase(),
			path: apiPath,
			tag: (op.tags || [])[0] || 'Other',
			summary: op.summary || '',
			description: op.description || '',
			parameters: op.parameters || [],
			requestBody: op.requestBody || null,
		});
	}
}

console.log(`[generate] found ${operations.length} operations (${heroSet.size} hero, ${operations.length - operations.filter(o => heroSet.has(o.operationId)).length} generated)`);

// Split hero vs generated
const generated = operations.filter(o => !heroSet.has(o.operationId));

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/** Resolve a $ref pointer like "#/components/schemas/Foo" against the spec */
function resolveRef(obj) {
	if (!obj || typeof obj !== 'object') return obj;
	if (obj.$ref) {
		const path = obj.$ref.replace('#/', '').split('/');
		let resolved = spec;
		for (const segment of path) {
			resolved = resolved?.[segment];
		}
		return resolved || obj;
	}
	// Handle allOf: merge all schemas together
	if (Array.isArray(obj.allOf)) {
		let merged = {};
		for (const part of obj.allOf) {
			const resolved = resolveRef(part);
			merged = {
				...merged,
				...resolved,
				properties: { ...merged.properties, ...resolved.properties },
				required: [...(merged.required || []), ...(resolved.required || [])],
			};
		}
		return merged;
	}
	return obj;
}

/** Convert operationId to PascalCase PHP class name */
function toPascalCase(str) {
	return str.replace(/(^|[^a-zA-Z0-9])([a-z])/g, (_, _sep, c) => c.toUpperCase())
		.replace(/^[a-z]/, c => c.toUpperCase())
		.replace(/[^a-zA-Z0-9]/g, '');
}

/** Convert operationId to snake_case shortcode tag */
function toSnakeCase(str) {
	return str.replace(/([a-z])([A-Z])/g, '$1_$2')
		.replace(/[^a-zA-Z0-9]/g, '_')
		.toLowerCase();
}

/** Convert operationId to kebab-case block slug */
function toKebabCase(str) {
	return str.replace(/([a-z])([A-Z])/g, '$1-$2')
		.replace(/[^a-zA-Z0-9]/g, '-')
		.toLowerCase();
}

/** Use the OpenAPI tag name directly as the product family label */
function tagToFamily(tag) {
	return tag;
}

/** Pick a Dashicon deterministically from the tag name via simple hash */
function tagToIcon(tag) {
	const icons = [
		'star-filled', 'star-half', 'heart', 'cloud', 'book',
		'chart-line', 'chart-bar', 'calculator', 'format-image', 'database',
		'admin-customizer', 'lightbulb', 'visibility', 'portfolio', 'palmtree',
		'universal-access', 'shield', 'superhero', 'tide', 'games',
	];
	let hash = 0;
	for (let i = 0; i < tag.length; i++) {
		hash = ((hash << 5) - hash + tag.charCodeAt(i)) | 0;
	}
	return icons[Math.abs(hash) % icons.length];
}

/** Get TTL for an operation from the ttl map */
function getTtl(operationId) {
	return ttlMap[operationId] ?? 0;
}

/** Extract path parameters from an OpenAPI path like /astrology/signs/{id} */
function extractPathParams(apiPath) {
	const matches = apiPath.match(/\{([^}]+)\}/g);
	if (!matches) return [];
	return matches.map(m => m.slice(1, -1));
}

/** Build the API path for the Client, with path params as PHP variables */
function buildPhpPath(apiPath) {
	// /astrology/signs/{id} -> astrology/signs/' . rawurlencode( $id ) . '
	let cleaned = apiPath.startsWith('/') ? apiPath.slice(1) : apiPath;
	if (!cleaned.includes('{')) return `'${cleaned}'`;

	const parts = cleaned.split(/(\{[^}]+\})/);
	const phpParts = parts.map(p => {
		if (p.startsWith('{') && p.endsWith('}')) {
			const param = p.slice(1, -1);
			return `' . rawurlencode( $${toPhpVar(param)} ) . '`;
		}
		return p;
	});
	return `'${phpParts.join('')}'`;
}

/** Safe PHP variable name */
function toPhpVar(name) {
	return name.replace(/[^a-zA-Z0-9_]/g, '_');
}

/** Extract query/path parameters for a GET endpoint */
function extractParams(op) {
	const params = [];
	for (const p of (op.parameters || [])) {
		const resolvedSchema = resolveRef(p.schema);
		params.push({
			name: p.name,
			in: p.in,
			required: p.required || false,
			type: resolvedSchema?.type || 'string',
			description: p.description || '',
		});
	}
	return params;
}

/** Extract body fields for a POST endpoint */
function extractBodyFields(op) {
	if (!op.requestBody) return [];
	const content = op.requestBody.content?.['application/json'];
	if (!content?.schema) return [];
	const schema = resolveRef(content.schema);
	const props = schema.properties || {};
	const required = new Set(schema.required || []);
	return Object.entries(props).map(([name, prop]) => {
		const resolvedProp = resolveRef(prop);
		return {
			name,
			required: required.has(name),
			type: resolvedProp.type || 'string',
			description: resolvedProp.description || '',
		};
	});
}

// ---------------------------------------------------------------------------
// Emit src/Generated/Client.php
// ---------------------------------------------------------------------------

function emitClientPhp() {
	const methods = [];

	for (const op of operations) {
		const className = toPascalCase(op.operationId);
		const phpMethod = op.operationId;
		const ttl = getTtl(op.operationId);
		const pathParams = extractPathParams(op.path);
		const phpPath = buildPhpPath(op.path);

		if (op.method === 'GET') {
			const params = extractParams(op);
			const allParams = [...pathParams.map(p => `$${toPhpVar(p)}`), ...params.filter(p => p.in === 'query').map(p => `$${toPhpVar(p.name)} = null`)];
			const queryArray = params.filter(p => p.in === 'query').map(p => {
				const v = toPhpVar(p.name);
				return `\t\t\t'${p.name}' => $${v},`;
			});

			// Build use() clause: always $query, plus any path params
			const useVars = ['$query', ...pathParams.map(p => `$${toPhpVar(p)}`)];
			const useClause = useVars.join(', ');

			methods.push(`
	/**
	 * ${op.summary || op.operationId}
	 *
	 * @return array|\\WP_Error
	 */
	public static function ${phpMethod}( ${allParams.join(', ')} ) {
		$query = array_filter(
			array(
${queryArray.join('\n')}
			),
			static function ( $v ) {
				return $v !== null;
			}
		);
		return \\RoxyAPI\\Api\\Cache::remember(
			${phpPath},
			$query,
			${ttl},
			static function () use ( ${useClause} ) {
				return \\RoxyAPI\\Api\\Client::get( ${phpPath}, $query );
			}
		);
	}`);
		} else {
			const bodyFields = extractBodyFields(op);
			const allParams = [...pathParams.map(p => `$${toPhpVar(p)}`), '$body = array()'];

			// Build use() clause: always $body, plus any path params
			const useVars = ['$body', ...pathParams.map(p => `$${toPhpVar(p)}`)];
			const useClause = useVars.join(', ');

			methods.push(`
	/**
	 * ${op.summary || op.operationId}
	 *
	 * @param array $body Request body.
	 * @return array|\\WP_Error
	 */
	public static function ${phpMethod}( ${allParams.join(', ')} ) {
		return \\RoxyAPI\\Api\\Cache::remember(
			${phpPath},
			$body,
			${ttl},
			static function () use ( ${useClause} ) {
				return \\RoxyAPI\\Api\\Client::post( ${phpPath}, $body );
			}
		);
	}`);
		}
	}

	return `<?php
/**
 * Auto-generated typed PHP client for RoxyAPI.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from the live OpenAPI spec.
 * Edit bin/generate.mjs instead, then run: npm run generate
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated;

class Client {
${methods.join('\n')}
}
`;
}

// ---------------------------------------------------------------------------
// Emit src/Generated/Endpoints.php
// ---------------------------------------------------------------------------

function emitEndpointsPhp() {
	const entries = operations.map(op => {
		const ttl = getTtl(op.operationId);
		const isHero = heroSet.has(op.operationId);
		return `		'${op.operationId}' => array(
			'path'    => '${op.path}',
			'method'  => '${op.method}',
			'tag'     => '${op.tag.replace(/'/g, "\\'")}',
			'summary' => '${(op.summary || '').replace(/'/g, "\\'")}',
			'ttl'     => ${ttl},
			'hero'    => ${isHero ? 'true' : 'false'},
		),`;
	});

	return `<?php
/**
 * Auto-generated endpoint registry.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from the live OpenAPI spec.
 * Edit bin/generate.mjs instead, then run: npm run generate
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated;

class Endpoints {

	/**
	 * All registered endpoints keyed by operationId.
	 *
	 * @return array<string, array{path: string, method: string, tag: string, summary: string, ttl: int, hero: bool}>
	 */
	public static function all(): array {
		return array(
${entries.join('\n')}
		);
	}

	/**
	 * Get a single endpoint by operationId.
	 *
	 * @return array{path: string, method: string, tag: string, summary: string, ttl: int, hero: bool}|null
	 */
	public static function get( string $operation_id ): ?array {
		$all = self::all();
		return $all[ $operation_id ] ?? null;
	}

	/**
	 * Only the non-hero (generated) endpoints.
	 *
	 * @return array<string, array{path: string, method: string, tag: string, summary: string, ttl: int, hero: bool}>
	 */
	public static function generated(): array {
		return array_filter(
			self::all(),
			static function ( $ep ) {
				return ! $ep['hero'];
			}
		);
	}
}
`;
}

// ---------------------------------------------------------------------------
// Emit src/Generated/Shortcodes/{ClassName}.php for each non-hero endpoint
// ---------------------------------------------------------------------------

function emitShortcodePhp(op) {
	const className = toPascalCase(op.operationId);
	const shortcodeTag = 'roxy_' + toSnakeCase(op.operationId);
	const ttl = getTtl(op.operationId);
	const isPost = op.method === 'POST';
	const pathParams = extractPathParams(op.path);
	const phpPath = buildPhpPath(op.path);

	let attsArray = '';
	let clientCall = '';

	if (isPost) {
		const bodyFields = extractBodyFields(op);
		const defaultAtts = bodyFields.map(f => `\t\t\t'${f.name}' => '',`).join('\n');
		attsArray = defaultAtts || "\t\t\t// No configurable attributes for this endpoint.";

		const bodyBuild = bodyFields.length > 0
			? `\t\t$body = array_filter(\n\t\t\tarray(\n${bodyFields.map(f => `\t\t\t\t'${f.name}' => $atts['${f.name}'],`).join('\n')}\n\t\t\t),\n\t\t\tstatic function ( $v ) {\n\t\t\t\treturn $v !== '';\n\t\t\t}\n\t\t);`
			: '\t\t$body = array();';

		clientCall = `${bodyBuild}
		$data = \\RoxyAPI\\Generated\\Client::${op.operationId}( ${pathParams.map(p => `$atts['${p}']`).concat(['$body']).join(', ')} );`;
	} else {
		const queryParams = extractParams(op).filter(p => p.in === 'query');
		const allAttParams = [...pathParams, ...queryParams.map(p => p.name)];
		const defaultAtts = allAttParams.map(p => `\t\t\t'${p}' => '',`).join('\n');
		attsArray = defaultAtts || "\t\t\t// No configurable attributes for this endpoint.";

		const queryArgsList = queryParams.map(p => `$atts['${p.name}']`);
		const allArgs = [...pathParams.map(p => `$atts['${p}']`), ...queryArgsList];

		clientCall = `$data = \\RoxyAPI\\Generated\\Client::${op.operationId}( ${allArgs.join(', ')} );`;
	}

	return `<?php
/**
 * Auto-generated shortcode: [${shortcodeTag}]
 *
 * ${op.summary || op.operationId}
 *
 * DO NOT EDIT. Generated by bin/generate.mjs. Edit the generator instead.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Shortcodes;

use RoxyAPI\\Blocks\\Renderer;

class ${className} {

	public const TAG = '${shortcodeTag}';

	public static function register(): void {
		if ( shortcode_exists( self::TAG ) ) {
			return;
		}
		add_shortcode( self::TAG, array( self::class, 'render' ) );
	}

	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			array(
${attsArray}
			),
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );

		${clientCall}

		if ( is_wp_error( $data ) ) {
			return \\RoxyAPI\\Support\\Templates::error( $data->get_error_message() );
		}

		return Renderer::render_generic( '${op.operationId}', is_array( $data ) ? $data : array() );
	}
}
`;
}

// ---------------------------------------------------------------------------
// Emit blocks/generated/{slug}/block.json for each non-hero endpoint
// ---------------------------------------------------------------------------

function emitBlockJson(op) {
	const slug = toKebabCase(op.operationId);
	const family = tagToFamily(op.tag);
	const icon = tagToIcon(op.tag);
	const pathParams = extractPathParams(op.path);
	const isPost = op.method === 'POST';

	const attributes = {};
	if (isPost) {
		const bodyFields = extractBodyFields(op);
		for (const f of bodyFields) {
			attributes[f.name] = { type: 'string', default: '' };
		}
	} else {
		for (const p of pathParams) {
			attributes[p] = { type: 'string', default: '' };
		}
		const queryParams = extractParams(op).filter(p => p.in === 'query');
		for (const p of queryParams) {
			attributes[p.name] = { type: 'string', default: '' };
		}
	}

	const keywords = [
		family.toLowerCase(),
		'roxyapi',
		...op.operationId.replace(/([A-Z])/g, ' $1').toLowerCase().trim().split(/\s+/).slice(0, 2),
	];

	return JSON.stringify({
		$schema: 'https://schemas.wp.org/trunk/block.json',
		apiVersion: 3,
		name: `roxyapi/${slug}`,
		title: `${op.summary || op.operationId} (${family})`,
		category: 'roxyapi',
		icon,
		description: `${op.summary || op.operationId}. Auto-generated from the RoxyAPI OpenAPI spec.`,
		keywords: [...new Set(keywords)],
		version: '1.0.0',
		textdomain: 'roxyapi',
		supports: {
			html: false,
			align: ['wide', 'full'],
			color: { background: true, text: true },
			spacing: { padding: true, margin: true },
		},
		attributes,
		render: 'file:./render.php',
	}, null, '\t') + '\n';
}

/** Emit a minimal render.php for generated blocks */
function emitBlockRenderPhp(op) {
	const shortcodeTag = 'roxy_' + toSnakeCase(op.operationId);
	const attrExtract = [];

	if (op.method === 'POST') {
		const bodyFields = extractBodyFields(op);
		for (const f of bodyFields) {
			attrExtract.push(`\t'${f.name}' => $attributes['${f.name}'] ?? '',`);
		}
	} else {
		const pathParams = extractPathParams(op.path);
		for (const p of pathParams) {
			attrExtract.push(`\t'${p}' => $attributes['${p}'] ?? '',`);
		}
		const queryParams = extractParams(op).filter(p => p.in === 'query');
		for (const p of queryParams) {
			attrExtract.push(`\t'${p.name}' => $attributes['${p.name}'] ?? '',`);
		}
	}

	return `<?php
/**
 * Server-side render for the auto-generated ${op.operationId} block.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs.
 *
 * @package RoxyAPI
 * @var array $attributes Block attributes.
 * @var string $content Inner block content.
 * @var WP_Block $block Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo do_shortcode( '[${shortcodeTag} ' . \\RoxyAPI\\Support\\Sanitize::attributes_to_string( array(
${attrExtract.join('\n')}
) ) . ']' );
`;
}

// ---------------------------------------------------------------------------
// Emit the Shortcode bootstrapper that registers all generated shortcodes
// ---------------------------------------------------------------------------

function emitBootstrapPhp(generatedOps) {
	const registerCalls = generatedOps.map(op => {
		const className = toPascalCase(op.operationId);
		return `\t\t\\RoxyAPI\\Generated\\Shortcodes\\${className}::register();`;
	});

	return `<?php
/**
 * Auto-generated shortcode bootstrap.
 *
 * Registers all generated shortcodes. Called from the Shortcodes\\Registrar
 * at priority 20 (after hero shortcodes at priority 10).
 *
 * DO NOT EDIT. Generated by bin/generate.mjs.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated;

class ShortcodeBootstrap {

	public static function register(): void {
${registerCalls.join('\n')}
	}
}
`;
}

// ---------------------------------------------------------------------------
// Write everything
// ---------------------------------------------------------------------------

// 1. Client.php
const clientPhp = emitClientPhp();
await fs.writeFile(path.join(OUT_PHP, 'Client.php'), clientPhp, 'utf8');
console.log(`[generate] wrote src/Generated/Client.php (${operations.length} methods)`);

// 2. Endpoints.php
const endpointsPhp = emitEndpointsPhp();
await fs.writeFile(path.join(OUT_PHP, 'Endpoints.php'), endpointsPhp, 'utf8');
console.log(`[generate] wrote src/Generated/Endpoints.php (${operations.length} entries)`);

// 3. Generated shortcodes
for (const op of generated) {
	const className = toPascalCase(op.operationId);
	const phpContent = emitShortcodePhp(op);
	await fs.writeFile(path.join(OUT_PHP, 'Shortcodes', `${className}.php`), phpContent, 'utf8');
}
console.log(`[generate] wrote ${generated.length} shortcode classes to src/Generated/Shortcodes/`);

// 4. ShortcodeBootstrap.php
const bootstrapPhp = emitBootstrapPhp(generated);
await fs.writeFile(path.join(OUT_PHP, 'ShortcodeBootstrap.php'), bootstrapPhp, 'utf8');
console.log(`[generate] wrote src/Generated/ShortcodeBootstrap.php`);

// 5. Generated block.json files
for (const op of generated) {
	const slug = toKebabCase(op.operationId);
	const blockDir = path.join(OUT_BLOCKS, slug);
	await fs.mkdir(blockDir, { recursive: true });
	await fs.writeFile(path.join(blockDir, 'block.json'), emitBlockJson(op), 'utf8');
	await fs.writeFile(path.join(blockDir, 'render.php'), emitBlockRenderPhp(op), 'utf8');
}
console.log(`[generate] wrote ${generated.length} block.json files to blocks/generated/`);

// 6. Restore README
if (readmeContent) {
	await fs.writeFile(readmePath, readmeContent, 'utf8');
}

console.log(`[generate] done. ${operations.length} total, ${heroSet.size} hero, ${generated.length} generated.`);
