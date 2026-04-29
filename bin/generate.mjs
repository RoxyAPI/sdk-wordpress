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

import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, "..");

const OPENAPI_URL =
	process.env.ROXYAPI_OPENAPI_URL ||
	"https://roxyapi.com/api/v2/openapi.json";
const SPEC_FILE = path.join(ROOT, "specs", "openapi.json");
const OUT_PHP = path.join(ROOT, "src", "Generated");
const OUT_BLOCKS = path.join(ROOT, "blocks", "generated");

const ttlMap = JSON.parse(
	await fs.readFile(path.join(__dirname, "ttl-map.json"), "utf8"),
);
const heroList = JSON.parse(
	await fs.readFile(path.join(__dirname, "hero-endpoints.json"), "utf8"),
);
const heroConfig = JSON.parse(
	await fs.readFile(path.join(__dirname, "hero-config.json"), "utf8"),
);
const exampleOverride = JSON.parse(
	await fs.readFile(path.join(__dirname, "example-overrides.json"), "utf8"),
);
const domainRegistry = JSON.parse(
	await fs.readFile(path.join(__dirname, "domains.json"), "utf8"),
);

/**
 * Apply per-field overrides over a raw spec example. Used when the OpenAPI spec
 * ships a stale or invalid example value (e.g. timezone="-5" that the live
 * validator rejects). Op-specific overrides beat _global.
 */
function applyExampleOverride(operationId, fieldName, specExample) {
	const opOverride = exampleOverride[operationId];
	if (
		opOverride &&
		Object.prototype.hasOwnProperty.call(opOverride, fieldName)
	) {
		return opOverride[fieldName];
	}
	const globalOverride = exampleOverride._global || {};
	if (Object.prototype.hasOwnProperty.call(globalOverride, fieldName)) {
		return globalOverride[fieldName];
	}
	return specExample;
}
const heroSet = new Set(heroList);

console.log(`[generate] fetching ${OPENAPI_URL}`);
const response = await fetch(OPENAPI_URL);
if (!response.ok) {
	console.error(
		`[generate] fetch failed: ${response.status} ${response.statusText}`,
	);
	process.exit(1);
}
const spec = await response.json();
console.log(
	`[generate] loaded spec with ${Object.keys(spec.paths || {}).length} paths`,
);

// Write the spec snapshot
await fs.mkdir(path.dirname(SPEC_FILE), { recursive: true });
await fs.writeFile(SPEC_FILE, JSON.stringify(spec, null, 2) + "\n", "utf8");
console.log(`[generate] wrote ${SPEC_FILE}`);

// Prepare output directories (clean them first)
await fs.rm(path.join(OUT_PHP, "Shortcodes"), { recursive: true, force: true });
await fs.mkdir(path.join(OUT_PHP, "Shortcodes"), { recursive: true });
await fs.rm(path.join(OUT_PHP, "Heroes"), { recursive: true, force: true });
await fs.mkdir(path.join(OUT_PHP, "Heroes"), { recursive: true });
await fs.rm(path.join(OUT_PHP, "Forms"), { recursive: true, force: true });
await fs.mkdir(path.join(OUT_PHP, "Forms"), { recursive: true });
// Keep the README in OUT_PHP
const readmePath = path.join(OUT_PHP, "README.md");
const readmeExists = await fs.stat(readmePath).catch(() => null);
const readmeContent = readmeExists
	? await fs.readFile(readmePath, "utf8")
	: null;
await fs.rm(OUT_BLOCKS, { recursive: true, force: true });
await fs.mkdir(OUT_BLOCKS, { recursive: true });

// ---------------------------------------------------------------------------
// Walk spec.paths and collect operations
// ---------------------------------------------------------------------------

/** @type {Array<{operationId: string, method: string, path: string, tag: string, summary: string, description: string, parameters: any[], requestBody: any}>} */
const operations = [];

for (const [apiPath, methods] of Object.entries(spec.paths || {})) {
	for (const [method, op] of Object.entries(methods)) {
		if (!["get", "post", "put", "patch", "delete"].includes(method))
			continue;
		if (!op.operationId) continue;
		operations.push({
			operationId: op.operationId,
			method: method.toUpperCase(),
			path: apiPath,
			tag: (op.tags || [])[0] || "Other",
			summary: op.summary || "",
			description: op.description || "",
			parameters: op.parameters || [],
			requestBody: op.requestBody || null,
		});
	}
}

console.log(
	`[generate] found ${operations.length} operations (${heroSet.size} hero, ${
		operations.length -
		operations.filter((o) => heroSet.has(o.operationId)).length
	} generated)`,
);

// Split hero vs generated
const generated = operations.filter((o) => !heroSet.has(o.operationId));

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/** Resolve a $ref pointer like "#/components/schemas/Foo" against the spec */
function resolveRef(obj) {
	if (!obj || typeof obj !== "object") return obj;
	if (obj.$ref) {
		const path = obj.$ref.replace("#/", "").split("/");
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
				required: [
					...(merged.required || []),
					...(resolved.required || []),
				],
			};
		}
		return merged;
	}
	return obj;
}

/** Convert operationId to PascalCase PHP class name */
function toPascalCase(str) {
	return str
		.replace(/(^|[^a-zA-Z0-9])([a-z])/g, (_, _sep, c) => c.toUpperCase())
		.replace(/^[a-z]/, (c) => c.toUpperCase())
		.replace(/[^a-zA-Z0-9]/g, "");
}

/** Convert operationId to snake_case shortcode tag */
function toSnakeCase(str) {
	return str
		.replace(/([a-z])([A-Z])/g, "$1_$2")
		.replace(/[^a-zA-Z0-9]/g, "_")
		.toLowerCase();
}

/** Convert operationId to kebab-case block slug */
function toKebabCase(str) {
	return str
		.replace(/([a-z])([A-Z])/g, "$1-$2")
		.replace(/[^a-zA-Z0-9]/g, "-")
		.toLowerCase();
}

/** Use the OpenAPI tag name directly as the product family label */
function tagToFamily(tag) {
	return tag;
}

/** Pick a Dashicon deterministically from the tag name via simple hash */
function tagToIcon(tag) {
	const icons = [
		"star-filled",
		"star-half",
		"heart",
		"cloud",
		"book",
		"chart-line",
		"chart-bar",
		"calculator",
		"format-image",
		"database",
		"admin-customizer",
		"lightbulb",
		"visibility",
		"portfolio",
		"palmtree",
		"universal-access",
		"shield",
		"superhero",
		"tide",
		"games",
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
	return matches.map((m) => m.slice(1, -1));
}

/** Build the API path for the Client, with path params as PHP variables */
function buildPhpPath(apiPath) {
	// /astrology/signs/{id} -> astrology/signs/' . rawurlencode( $id ) . '
	let cleaned = apiPath.startsWith("/") ? apiPath.slice(1) : apiPath;
	if (!cleaned.includes("{")) return `'${cleaned}'`;

	const parts = cleaned.split(/(\{[^}]+\})/);
	const phpParts = parts.map((p) => {
		if (p.startsWith("{") && p.endsWith("}")) {
			const param = p.slice(1, -1);
			return `' . rawurlencode( $${toPhpVar(param)} ) . '`;
		}
		return p;
	});
	return `'${phpParts.join("")}'`;
}

/** Safe PHP variable name */
function toPhpVar(name) {
	return name.replace(/[^a-zA-Z0-9_]/g, "_");
}

/**
 * Convert an OpenAPI parameter name (camelCase or snake_case) into the snake_case
 * form WordPress will see in $atts. The shortcode parser lowercases attribute
 * keys, so a default of `birthDate` would never match the user's `birthDate=...`
 * input (which arrives as `birthdate`). Snake_case sidesteps the issue and is
 * the conventional WordPress shortcode attribute style anyway.
 */
function toSnakeAttr(name) {
	return name
		.replace(/([a-z0-9])([A-Z])/g, "$1_$2")
		.replace(/([A-Z]+)([A-Z][a-z])/g, "$1_$2")
		.toLowerCase();
}

/** Extract query/path parameters for a GET endpoint */
function extractParams(op) {
	const params = [];
	for (const p of op.parameters || []) {
		const resolvedSchema = resolveRef(p.schema);
		params.push({
			name: p.name,
			in: p.in,
			required: p.required || false,
			type: resolvedSchema?.type || "string",
			description: p.description || "",
			// Example for the demo defaults emitter. Falls back through both
			// the parameter object and its resolved schema, since either may
			// carry the canonical sample value.
			example: p.example ?? resolvedSchema?.example,
		});
	}
	return params;
}

/** Extract body fields for a POST endpoint */
function extractBodyFields(op) {
	if (!op.requestBody) return [];
	const content = op.requestBody.content?.["application/json"];
	if (!content?.schema) return [];
	const schema = resolveRef(content.schema);
	const props = schema.properties || {};
	const required = new Set(schema.required || []);
	return Object.entries(props).map(([name, prop]) => {
		const resolvedProp = resolveRef(prop);
		return {
			name,
			required: required.has(name),
			type: resolvedProp.type || "string",
			description: resolvedProp.description || "",
			example: resolvedProp.example,
		};
	});
}

/**
 * True when an operation's request body has at least one REQUIRED nested-object
 * (or array-of-object) field. These shapes cannot be expressed as flat
 * shortcode attributes; the generator emits a visitor-form-mode shortcode
 * (FormRenderer) instead. v1.0 ships forms; v1.1 adds nested-attribute blocks.
 */
function hasRequiredObjectBody(op) {
	if (op.method !== "POST") return false;
	const content = op.requestBody?.content?.["application/json"];
	if (!content?.schema) return false;
	const schema = resolveRef(content.schema);
	const required = new Set(schema.required || []);
	for (const [name, prop] of Object.entries(schema.properties || {})) {
		if (!required.has(name)) continue;
		const resolved = resolveRef(prop);
		if (resolved.type === "object") return true;
		if (resolved.type === "array") {
			const items = resolveRef(resolved.items || {});
			if (items.type === "object") return true;
		}
	}
	return false;
}

/**
 * Humanise a property name for use as a form label.
 * `birthDate` -> `Birth date`, `house_system` -> `House system`.
 */
function humanLabel(name) {
	const spaced = name
		.replace(/([a-z])([A-Z])/g, "$1 $2")
		.replace(/([A-Za-z])([0-9])/g, "$1 $2")
		.replace(/[_-]+/g, " ")
		.trim();
	return spaced.charAt(0).toUpperCase() + spaced.slice(1).toLowerCase();
}

/**
 * Map an OpenAPI property schema + name to a FormRenderer field spec.
 * Heuristics catch lat/lon/timezone by name when the spec lacks min/max.
 */
function buildFormFieldSpec(name, schema, required) {
	const lower = name.toLowerCase();
	const field = { name, label: humanLabel(name), required };
	if (schema.description)
		field.help = String(schema.description).split(/\.\s/)[0];

	if (schema.type === "string" && schema.format === "date") {
		field.type = "date";
	} else if (schema.type === "string" && schema.format === "time") {
		field.type = "time";
	} else if (
		schema.type === "string" &&
		(lower === "time" || lower.endsWith("_time") || lower.endsWith("time"))
	) {
		// Spec often describes time fields as plain string with help text
		// "HH:MM:SS"; coerce to a time picker so users get the native UX.
		field.type = "time";
	} else if (
		schema.type === "string" &&
		(lower === "date" || lower.endsWith("_date") || lower.endsWith("date"))
	) {
		// Same for plain-string date fields like `birthDate` / `transitDate`.
		field.type = "date";
	} else if (schema.type === "string" && Array.isArray(schema.enum)) {
		field.type = "enum";
		field.enum = schema.enum;
	} else if (lower === "tz" || lower === "timezone") {
		field.type = "timezone";
	} else if (schema.type === "number" || schema.type === "integer") {
		field.type = schema.type === "integer" ? "integer" : "number";
		if (lower === "lat" || lower === "latitude") {
			field.min = -90;
			field.max = 90;
		} else if (lower === "lon" || lower === "longitude") {
			field.min = -180;
			field.max = 180;
		} else {
			if (schema.minimum !== undefined) field.min = schema.minimum;
			if (schema.maximum !== undefined) field.max = schema.maximum;
		}
		if (schema.type === "number") field.step = "any";
	} else {
		field.type = "text";
	}
	return field;
}

/**
 * Walk a POST request-body schema and produce a FormRenderer-shaped spec
 * with `sections` for nested objects and `flat_fields` for top-level scalars.
 */
function extractFormSpec(op) {
	const content = op.requestBody?.content?.["application/json"];
	if (!content?.schema) return null;
	const schema = resolveRef(content.schema);
	const required = new Set(schema.required || []);
	const sections = [];
	const flatFields = [];
	for (const [name, prop] of Object.entries(schema.properties || {})) {
		const resolved = resolveRef(prop);
		if (resolved.type === "object") {
			const subRequired = new Set(resolved.required || []);
			const fields = [];
			for (const [subName, subProp] of Object.entries(
				resolved.properties || {},
			)) {
				const subResolved = resolveRef(subProp);
				if (
					subResolved.type === "object" ||
					subResolved.type === "array"
				) {
					// v1.0: do not emit deeper-than-one nesting controls.
					continue;
				}
				fields.push(
					buildFormFieldSpec(
						subName,
						subResolved,
						subRequired.has(subName),
					),
				);
			}
			sections.push({ name, label: humanLabel(name), fields });
		} else if (resolved.type === "array") {
			// Array-of-object body fields are deferred to v1.1.
			continue;
		} else {
			flatFields.push(
				buildFormFieldSpec(name, resolved, required.has(name)),
			);
		}
	}
	return { sections, flatFields };
}

/**
 * PHP literal for a JS value — strings/numbers/bools/lists. Strict ASCII safe.
 */
function phpLiteral(v) {
	if (v === null || v === undefined) return "''";
	if (typeof v === "boolean") return v ? "true" : "false";
	if (typeof v === "number") return String(v);
	if (Array.isArray(v))
		return "array( " + v.map(phpLiteral).join(", ") + " )";
	return "'" + String(v).replace(/\\/g, "\\\\").replace(/'/g, "\\'") + "'";
}

/**
 * Emit src/Generated/Forms/<Op>Form.php for a nested-object endpoint.
 */
function emitFormPhp(op) {
	const className = toPascalCase(op.operationId) + "Form";
	const formSpec = extractFormSpec(op);
	const title = op.summary || op.operationId;

	const sectionsPhp = (formSpec.sections || [])
		.map((s) => {
			const fieldsPhp = s.fields
				.map((f) => {
					const parts = Object.entries(f)
						.map(
							([k, v]) =>
								`\t\t\t\t\t\t'${k}' => ${phpLiteral(v)},`,
						)
						.join("\n");
					return `\t\t\t\t\tarray(\n${parts}\n\t\t\t\t\t),`;
				})
				.join("\n");
			return `\t\t\t\tarray(
					'name'   => ${phpLiteral(s.name)},
					'label'  => ${phpLiteral(s.label)},
					'fields' => array(
${fieldsPhp}
					),
				),`;
		})
		.join("\n");

	const flatPhp = (formSpec.flatFields || [])
		.map((f) => {
			const parts = Object.entries(f)
				.map(([k, v]) => `\t\t\t\t\t'${k}' => ${phpLiteral(v)},`)
				.join("\n");
			return `\t\t\t\tarray(\n${parts}\n\t\t\t\t),`;
		})
		.join("\n");

	return `<?php
/**
 * Auto-generated visitor form for ${op.operationId}.
 *
 * ${op.summary || op.operationId}
 *
 * DO NOT EDIT. Generated by bin/generate.mjs. Edit the generator instead.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ${className} {

	public static function spec(): array {
		return array(
			'operation_id' => ${phpLiteral(op.operationId)},
			'title'        => ${phpLiteral(title)},
			'submit_label' => __( 'Get reading', 'roxyapi' ),
			'sections'     => array(
${sectionsPhp}
			),
			'flat_fields'  => array(
${flatPhp}
			),
		);
	}

	/**
	 * @param array<string, mixed> $body
	 * @return array<string, mixed>|\\WP_Error
	 */
	public static function call( array $body ) {
		return \\RoxyAPI\\Generated\\Client::${op.operationId}( $body );
	}
}
`;
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

		// Build a guard block that fails fast (with a friendly WP_Error) if any
		// required path param arrived empty. Without this, an unset shortcode
		// attr produces requests like `/horoscope//monthly` that the SaaS 404s
		// or that emit `rawurlencode(null)` deprecation warnings.
		const pathGuard =
			pathParams.length > 0
				? pathParams
						.map((p) => {
							const v = toPhpVar(p);
							return `\t\tif ( $${v} === '' || $${v} === null ) {\n\t\t\treturn new \\WP_Error( 'roxyapi_missing_param', sprintf( /* translators: %s: shortcode attribute name. */ __( 'Missing required attribute "%s" for this shortcode.', 'roxyapi' ), '${p}' ) );\n\t\t}`;
						})
						.join("\n")
				: "";

		if (op.method === "GET") {
			const params = extractParams(op);
			const allParams = [
				...pathParams.map((p) => `$${toPhpVar(p)}`),
				...params
					.filter((p) => p.in === "query")
					.map((p) => `$${toPhpVar(p.name)} = null`),
			];
			const queryArray = params
				.filter((p) => p.in === "query")
				.map((p) => {
					const v = toPhpVar(p.name);
					return `\t\t\t'${p.name}' => $${v},`;
				});

			// Build use() clause: always $query, plus any path params
			const useVars = [
				"$query",
				...pathParams.map((p) => `$${toPhpVar(p)}`),
			];
			const useClause = useVars.join(", ");

			methods.push(`
	/**
	 * ${op.summary || op.operationId}
	 *
	 * @return array|\\WP_Error
	 */
	public static function ${phpMethod}( ${allParams.join(", ")} ) {
${pathGuard ? pathGuard + "\n" : ""}		$query = array_filter(
			array(
${queryArray.join("\n")}
			),
			static function ( $v ) {
				return $v !== null && $v !== '';
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
			const allParams = [
				...pathParams.map((p) => `$${toPhpVar(p)}`),
				"$body = array()",
			];

			// Build use() clause: always $body, plus any path params
			const useVars = [
				"$body",
				...pathParams.map((p) => `$${toPhpVar(p)}`),
			];
			const useClause = useVars.join(", ");

			methods.push(`
	/**
	 * ${op.summary || op.operationId}
	 *
	 * @param array $body Request body.
	 * @return array|\\WP_Error
	 */
	public static function ${phpMethod}( ${allParams.join(", ")} ) {
${pathGuard ? pathGuard + "\n" : ""}		return \\RoxyAPI\\Api\\Cache::remember(
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Client {
${methods.join("\n")}
}
`;
}

// ---------------------------------------------------------------------------
// Emit src/Generated/Endpoints.php
// ---------------------------------------------------------------------------

function emitEndpointsPhp() {
	const entries = operations.map((op) => {
		const ttl = getTtl(op.operationId);
		const isHero = heroSet.has(op.operationId);

		// Pluck OpenAPI examples for every parameter/body field so the Demo
		// page can render attribute-less shortcodes with sensible inputs.
		// Keys are snake_case to match the shortcode attribute style emitted
		// by emitShortcodePhp (WP lowercases attr names at parse time).
		// `applyExampleOverride` lets us patch fields where the spec example
		// disagrees with the live validator (e.g. timezone "-5" → "UTC").
		const attrExamples = {};
		const params = extractParams(op);
		for (const p of params) {
			const ex = applyExampleOverride(op.operationId, p.name, p.example);
			if (ex !== undefined && ex !== null) {
				attrExamples[toSnakeAttr(p.name)] = ex;
			}
		}
		if (op.method === "POST") {
			for (const f of extractBodyFields(op)) {
				const ex = applyExampleOverride(
					op.operationId,
					f.name,
					f.example,
				);
				if (ex !== undefined && ex !== null) {
					attrExamples[toSnakeAttr(f.name)] = ex;
				}
			}
		}
		const attrEntries = Object.entries(attrExamples)
			.map(([k, v]) => {
				// Stringify for shortcode-attribute use. Coerce booleans to "1"/"".
				let phpStr;
				if (typeof v === "boolean") {
					phpStr = v ? "1" : "";
				} else if (typeof v === "number") {
					phpStr = String(v);
				} else if (typeof v === "object") {
					// Skip nested object/array examples — shortcode attrs are flat
					// and these belong to a different DX (HTML form / block).
					return null;
				} else {
					phpStr = String(v);
				}
				return `\t\t\t\t'${k}' => '${phpStr.replace(/'/g, "\\'")}',`;
			})
			.filter(Boolean);
		const attributesPhp =
			attrEntries.length > 0
				? `\n\t\t\t'attributes' => array(\n${attrEntries.join(
						"\n",
				  )}\n\t\t\t),`
				: `\n\t\t\t'attributes' => array(),`;

		const blockOnly = hasRequiredObjectBody(op);
		const shortcodeTag = `roxy_${toSnakeCase(op.operationId)}`;

		return `		'${op.operationId}' => array(
			'path'          => '${op.path}',
			'method'        => '${op.method}',
			'tag'           => '${op.tag.replace(/'/g, "\\'")}',
			'summary'       => '${(op.summary || "").replace(/'/g, "\\'")}',
			'ttl'           => ${ttl},
			'hero'          => ${isHero ? "true" : "false"},
			'block_only'    => ${blockOnly ? "true" : "false"},
			'shortcode_tag' => '${shortcodeTag}',${attributesPhp}
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Endpoints {

	/**
	 * All registered endpoints keyed by operationId.
	 *
	 * @return array<string, array{path: string, method: string, tag: string, summary: string, ttl: int, hero: bool, block_only: bool, shortcode_tag: string, attributes: array<string, string>}>
	 */
	public static function all(): array {
		return array(
${entries.join("\n")}
		);
	}

	/**
	 * Get a single endpoint by operationId.
	 *
	 * @return array{path: string, method: string, tag: string, summary: string, ttl: int, hero: bool, block_only: bool, shortcode_tag: string, attributes: array<string, string>}|null
	 */
	public static function get( string $operation_id ): ?array {
		$all = self::all();
		return $all[ $operation_id ] ?? null;
	}

	/**
	 * Only the non-hero (generated) endpoints.
	 *
	 * @return array<string, array{path: string, method: string, tag: string, summary: string, ttl: int, hero: bool, block_only: bool, shortcode_tag: string, attributes: array<string, string>}>
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
	const shortcodeTag = "roxy_" + toSnakeCase(op.operationId);
	const ttl = getTtl(op.operationId);
	const isPost = op.method === "POST";
	const pathParams = extractPathParams(op.path);
	const phpPath = buildPhpPath(op.path);

	// Endpoints whose request body has a required nested object cannot be
	// expressed as flat shortcode attributes. Emit a sentinel that explains
	// the limitation rather than a broken shortcode that always 400s.
	if (hasRequiredObjectBody(op)) {
		const formClass = toPascalCase(op.operationId) + "Form";
		return `<?php
/**
 * Auto-generated shortcode: [${shortcodeTag}]
 *
 * ${op.summary || op.operationId}
 *
 * Renders a visitor-facing form (FormRenderer) when invoked. Submission is
 * handled by FormRouter at init priority 5 — nonce + rate-limit + sanitize
 * against the generated form spec, then call the typed PHP client and
 * render the result above the form via PRG redirect.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs. Edit the generator instead.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\\Support\\FormRenderer;

class ${className} {

	public const TAG = '${shortcodeTag}';

	public static function register(): void {
		if ( shortcode_exists( self::TAG ) ) {
			return;
		}
		add_shortcode( self::TAG, array( self::class, 'render' ) );
	}

	public static function render( $atts, $content = '', $tag = '' ): string {
		return FormRenderer::render( \\RoxyAPI\\Generated\\Forms\\${formClass}::class );
	}
}
`;
	}

	let attsArray = "";
	let clientCall = "";

	if (isPost) {
		const bodyFields = extractBodyFields(op);
		// Snake_case-ify every attribute key. WordPress's shortcode parser
		// lowercases attribute names, so a default of `birthDate` would never
		// match the user's `birthDate=...` (which arrives as `birthdate`).
		const allPostAtts = [
			...pathParams.map(toSnakeAttr),
			...bodyFields.map((f) => toSnakeAttr(f.name)),
		];
		const defaultAtts = allPostAtts
			.map((p) => `\t\t\t'${p}' => '',`)
			.join("\n");
		attsArray =
			defaultAtts ||
			"\t\t\t// No configurable attributes for this endpoint.";

		// Body assembly maps API field name (camelCase) to snake_case attr key
		// and casts numeric fields so the JSON body sends 40.71 not "40.71".
		const bodyBuild =
			bodyFields.length > 0
				? `\t\t$body = array_filter(\n\t\t\tarray(\n${bodyFields
						.map((f) => {
							const attr = toSnakeAttr(f.name);
							if (f.type === "integer") {
								return `\t\t\t\t'${f.name}' => $atts['${attr}'] !== '' ? (int) $atts['${attr}'] : '',`;
							}
							if (f.type === "number") {
								return `\t\t\t\t'${f.name}' => $atts['${attr}'] !== '' ? (float) $atts['${attr}'] : '',`;
							}
							if (f.type === "boolean") {
								return `\t\t\t\t'${f.name}' => $atts['${attr}'] !== '' ? filter_var( $atts['${attr}'], FILTER_VALIDATE_BOOLEAN ) : '',`;
							}
							return `\t\t\t\t'${f.name}' => $atts['${attr}'],`;
						})
						.join(
							"\n",
						)}\n\t\t\t),\n\t\t\tstatic function ( $v ) {\n\t\t\t\treturn $v !== '';\n\t\t\t}\n\t\t);`
				: "\t\t$body = array();";

		clientCall = `${bodyBuild}
		$data = \\RoxyAPI\\Generated\\Client::${op.operationId}( ${pathParams
			.map((p) => `$atts['${toSnakeAttr(p)}']`)
			.concat(["$body"])
			.join(", ")} );`;
	} else {
		const queryParams = extractParams(op).filter((p) => p.in === "query");
		const allAttParams = [
			...pathParams.map(toSnakeAttr),
			...queryParams.map((p) => toSnakeAttr(p.name)),
		];
		const defaultAtts = allAttParams
			.map((p) => `\t\t\t'${p}' => '',`)
			.join("\n");
		attsArray =
			defaultAtts ||
			"\t\t\t// No configurable attributes for this endpoint.";

		const queryArgsList = queryParams.map(
			(p) => `$atts['${toSnakeAttr(p.name)}']`,
		);
		const allArgs = [
			...pathParams.map((p) => `$atts['${toSnakeAttr(p)}']`),
			...queryArgsList,
		];

		clientCall = `$data = \\RoxyAPI\\Generated\\Client::${
			op.operationId
		}( ${allArgs.join(", ")} );`;
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\\Support\\GenericRenderer;

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
			return \\RoxyAPI\\Support\\Templates::api_error( $data );
		}

		return GenericRenderer::render( '${
			op.operationId
		}', is_array( $data ) ? $data : array() );
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
	const isPost = op.method === "POST";

	const attributes = {};
	if (isPost) {
		const bodyFields = extractBodyFields(op);
		for (const f of bodyFields) {
			attributes[f.name] = { type: "string", default: "" };
		}
	} else {
		for (const p of pathParams) {
			attributes[p] = { type: "string", default: "" };
		}
		const queryParams = extractParams(op).filter((p) => p.in === "query");
		for (const p of queryParams) {
			attributes[p.name] = { type: "string", default: "" };
		}
	}

	const keywords = [
		family.toLowerCase(),
		"roxyapi",
		...op.operationId
			.replace(/([A-Z])/g, " $1")
			.toLowerCase()
			.trim()
			.split(/\s+/)
			.slice(0, 2),
	];

	return (
		JSON.stringify(
			{
				$schema: "https://schemas.wp.org/trunk/block.json",
				apiVersion: 3,
				name: `roxyapi/${slug}`,
				title: `${op.summary || op.operationId} (${family})`,
				category: "roxyapi",
				icon,
				description: op.summary || op.operationId,
				keywords: [...new Set(keywords)],
				version: "1.0.0",
				textdomain: "roxyapi",
				supports: {
					html: false,
					align: ["wide", "full"],
					color: { background: true, text: true },
					spacing: { padding: true, margin: true },
				},
				attributes,
				render: "file:./render.php",
			},
			null,
			"\t",
		) + "\n"
	);
}

/** Emit a minimal render.php for generated blocks */
function emitBlockRenderPhp(op) {
	const className = toPascalCase(op.operationId);

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

echo wp_kses_post( \\RoxyAPI\\Generated\\Shortcodes\\${className}::render( $attributes ) );
`;
}

// ---------------------------------------------------------------------------
// Emit the Shortcode bootstrapper that registers all generated shortcodes
// ---------------------------------------------------------------------------

function emitBootstrapPhp(generatedOps) {
	const registerCalls = generatedOps.map((op) => {
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ShortcodeBootstrap {

	public static function register(): void {
${registerCalls.join("\n")}
	}
}
`;
}

// ---------------------------------------------------------------------------
// Hero shortcode emission
// ---------------------------------------------------------------------------

/** Build a quick lookup of operationId -> operation record for hero emission. */
const opsByIdMap = {};
for (const op of operations) {
	opsByIdMap[op.operationId] = op;
}

/** Classify one hero attribute against a target operation's spec slot. */
function attrSlotForOperation(attr, op) {
	// path-param wins if the spec field name matches a path placeholder
	const pathParams = extractPathParams(op.path);
	const targetField = attr.spec_field || attr.name;
	if (pathParams.includes(targetField)) {
		return "path";
	}
	// query param check
	for (const p of op.parameters || []) {
		if (p.in === "query" && p.name === targetField) return "query";
	}
	// body fallback for POST endpoints
	if (op.method === "POST") return "body";
	// else: query for GET endpoints
	return "query";
}

/** Render a Sanitize::name(...) call for the given sanitize directive. */
function renderSanitizeCall(sanitize, valueExpr) {
	if (!sanitize) return valueExpr;
	if (typeof sanitize === "string") {
		if (sanitize === "key") {
			return `sanitize_key( (string) ${valueExpr} )`;
		}
		return `\\RoxyAPI\\Support\\Sanitize::${sanitize}( ${valueExpr} )`;
	}
	if (sanitize && typeof sanitize === "object" && sanitize.name) {
		const args = (sanitize.args || [])
			.map((a) => JSON.stringify(a))
			.join(", ");
		return `\\RoxyAPI\\Support\\Sanitize::${sanitize.name}( ${valueExpr}${
			args ? ", " + args : ""
		} )`;
	}
	return valueExpr;
}

/** Build the PHP array literal contents for a DEFAULTS constant from a hero config. */
function renderDefaultsArray(attributes, hasFormMode = false) {
	const lines = Object.entries(attributes).map(([key, info]) => {
		const def = info.default ?? "";
		return `\t\t'${key}' => ${JSON.stringify(def)},`;
	});
	if (hasFormMode) {
		// Mode attribute opts in/out of form rendering. `auto` (default) shows
		// the visitor form when required attrs are missing; `form` always shows
		// the form; `static` preserves the legacy missing-attrs error message.
		lines.push(`\t\t'mode' => 'auto',`);
	}
	return lines.join("\n");
}

/** Resolve a transformer name to its PHP call. Currently only one transformer. */
function transformPhpCall(transformName, valueExpr) {
	if (transformName === "split_iso_date_into_year_month_day") {
		return `\\RoxyAPI\\Support\\HeroTransforms::split_iso_date_into_year_month_day( ${valueExpr} )`;
	}
	throw new Error(`[generate] unknown hero transform: ${transformName}`);
}

/** Build the per-attribute "sanitised" PHP block. Returns lines + a map of name -> phpExpression. */
function renderAttrSanitisation(attributes) {
	const lines = [];
	const sanitisedExpr = {}; // attr name -> PHP expression
	const transformedFields = {}; // attr name -> { fields: [string], errorTag: string }

	for (const [name, info] of Object.entries(attributes)) {
		const raw = `$atts['${name}']`;
		if (info.transform) {
			lines.push(
				`\t\t$${name}_parts = ${transformPhpCall(
					info.transform,
					raw,
				)};`,
			);
			transformedFields[name] = info.transform;
			continue;
		}
		if (info.sanitize) {
			const expr = renderSanitizeCall(info.sanitize, raw);
			lines.push(`\t\t$${name}_clean = ${expr};`);
			sanitisedExpr[name] = `$${name}_clean`;
		} else {
			sanitisedExpr[name] = raw;
		}
	}

	return { lines: lines.join("\n"), sanitisedExpr, transformedFields };
}

/** Build the "required" check block using the example interpolation for the example. */
function renderRequiredCheck(attributes, missingMessage, example, formClassName = null) {
	const requiredKeys = Object.entries(attributes)
		.filter(([_, info]) => info.required)
		.map(([k]) => k);
	if (requiredKeys.length === 0 || !missingMessage) return "";
	const conditions = requiredKeys
		.map((k) => `$atts['${k}'] === ''`)
		.join(" || ");
	const translatorComment = `/* translators: %s is the canonical example shortcode. */`;
	const messagePhp = `sprintf( ${translatorComment} __( ${JSON.stringify(
		missingMessage,
	)}, 'roxyapi' ), ${JSON.stringify(example)} )`;
	if (formClassName) {
		// When form_mode is configured, missing required attrs render the
		// visitor form (unless mode='static' explicitly opts out, which
		// preserves the legacy error message for site owners who do not want
		// a form on the page).
		return `\t\tif ( ${conditions} ) {
			if ( $atts['mode'] !== 'static' ) {
				return \\RoxyAPI\\Support\\FormRenderer::render( \\RoxyAPI\\Generated\\Forms\\${formClassName}::class );
			}
			return \\RoxyAPI\\Support\\Templates::error( ${messagePhp} );
		}`;
	}
	return `\t\tif ( ${conditions} ) {\n\t\t\treturn \\RoxyAPI\\Support\\Templates::error( ${messagePhp} );\n\t\t}`;
}

/**
 * Render the form-mode short-circuit block placed at the top of render().
 * When `mode='form'` is passed, render the visitor form regardless of
 * whether other attrs are present.
 */
function renderFormModeShortCircuit(formClassName) {
	if (!formClassName) return "";
	return `\t\tif ( $atts['mode'] === 'form' ) {
			return \\RoxyAPI\\Support\\FormRenderer::render( \\RoxyAPI\\Generated\\Forms\\${formClassName}::class );
		}`;
}

/** Required-attrs check for fetch_for_form: returns WP_Error on missing. */
function renderRequiredCheckFetch(attributes, missingMessage, example) {
	const requiredKeys = Object.entries(attributes)
		.filter(([_, info]) => info.required)
		.map(([k]) => k);
	if (requiredKeys.length === 0 || !missingMessage) return "";
	const conditions = requiredKeys
		.map((k) => `$atts['${k}'] === ''`)
		.join(" || ");
	const translatorComment = `/* translators: %s is the canonical example shortcode. */`;
	const messagePhp = `sprintf( ${translatorComment} __( ${JSON.stringify(
		missingMessage,
	)}, 'roxyapi' ), ${JSON.stringify(example)} )`;
	return `\t\tif ( ${conditions} ) {\n\t\t\treturn new \\WP_Error( 'roxyapi_missing_attrs', ${messagePhp} );\n\t\t}`;
}

/** Transform error blocks for fetch_for_form: returns WP_Error on null parts. */
function renderTransformErrorBlocksFetch(transformedFields, cfg) {
	const blocks = [];
	for (const [name] of Object.entries(transformedFields)) {
		const translatorComment = `/* translators: %s is the canonical example shortcode. */`;
		const errMessage = cfg.transform_error_message
			? `sprintf( ${translatorComment} __( ${JSON.stringify(
					cfg.transform_error_message,
			  )}, 'roxyapi' ), ${JSON.stringify(cfg.example)} )`
			: `__( ${JSON.stringify(
					`The ${name} attribute must be in YYYY-MM-DD format.`,
			  )}, 'roxyapi' )`;
		blocks.push(
			`\t\tif ( $${name}_parts === null ) {
			return new \\WP_Error( 'roxyapi_invalid_format', ${errMessage} );
		}`,
		);
	}
	return blocks.join("\n");
}

/** Build the PHP code that calls a single operation (with the right arg shape). */
function renderClientCall(opId, argsByName) {
	// argsByName: { fieldName: phpExpression } for body POST OR for path/query GET
	const op = opsByIdMap[opId];
	if (!op) {
		throw new Error(
			`[generate] hero references unknown operationId: ${opId}`,
		);
	}
	const pathParams = extractPathParams(op.path);

	if (op.method === "POST") {
		// For POST: path params come first, then body array
		const pathArgs = pathParams.map((p) =>
			argsByName[p] !== undefined ? argsByName[p] : "''",
		);
		const bodyEntries = [];
		for (const [field, expr] of Object.entries(argsByName)) {
			if (pathParams.includes(field)) continue;
			bodyEntries.push(`\t\t\t'${field}' => ${expr},`);
		}
		const bodyArrayPhp =
			bodyEntries.length === 0
				? "array()"
				: `array(\n${bodyEntries.join("\n")}\n\t\t)`;
		return `\\RoxyAPI\\Generated\\Client::${opId}( ${[
			...pathArgs,
			bodyArrayPhp,
		].join(", ")} )`;
	} else {
		// GET: ordered args = pathParams (in path order), then query params (in spec order)
		const orderedArgs = [];
		for (const p of pathParams) {
			orderedArgs.push(
				argsByName[p] !== undefined ? argsByName[p] : "''",
			);
		}
		const queryParams = (op.parameters || []).filter(
			(p) => p.in === "query",
		);
		for (const qp of queryParams) {
			if (argsByName[qp.name] !== undefined) {
				orderedArgs.push(argsByName[qp.name]);
			} else {
				orderedArgs.push("null");
			}
		}
		// trim trailing nulls
		while (
			orderedArgs.length > 0 &&
			orderedArgs[orderedArgs.length - 1] === "null"
		) {
			orderedArgs.pop();
		}
		// Drop trailing nulls only if the spec method declares them with defaults.
		// The Client.php signatures we generate have `= null` for query params, so
		// we can drop trailing nulls safely.
		return `\\RoxyAPI\\Generated\\Client::${opId}( ${orderedArgs.join(
			", ",
		)} )`;
	}
}

/** Build the full set of (fieldName -> phpExpr) for a single dispatch branch. */
function buildArgsForBranch(
	attributes,
	sanitisedExpr,
	transformedFields,
	op,
	branchOpts,
) {
	const args = {};
	const passthrough = branchOpts.passthrough || {};
	const pathArgs = branchOpts.path_args || {};
	const queryArgs = branchOpts.query_args || {};

	// Union of all attributes consumed by this branch (passthrough, path_args, query_args).
	const consumed = new Set([
		...Object.keys(passthrough),
		...Object.keys(pathArgs),
		...Object.keys(queryArgs),
	]);

	for (const attrName of consumed) {
		const info = attributes[attrName];
		if (!info) {
			throw new Error(
				`[generate] dispatch branch references unknown attr: ${attrName}`,
			);
		}
		// Determine target field name in the spec
		const specName =
			passthrough[attrName] || pathArgs[attrName] || queryArgs[attrName];
		const valueExpr =
			sanitisedExpr[attrName] !== undefined
				? sanitisedExpr[attrName]
				: `$atts['${attrName}']`;
		args[specName] = valueExpr;
	}

	return args;
}

/**
 * Build the body of the hero method that calls the upstream API.
 *
 * Two output modes:
 *   - "render": emits a PHP fragment that returns rendered HTML via
 *     `GenericRenderer::render()` on success and `Templates::api_error()` on
 *     failure. Used inside the hero's `render()` method.
 *   - "fetch": emits a PHP fragment that returns the raw API response array
 *     on success and the original `WP_Error` on failure. Used inside the
 *     hero's `fetch_for_form()` method which the matching `<Hero>Form` class
 *     calls back into.
 *
 * The dispatch / single-target / skip_if_empty branching logic is identical
 * in both modes; only the leaf return statements differ.
 */
function buildHeroBodyContent(
	tagSuffix,
	cfg,
	sanitisedExpr,
	transformedFields,
	transformedSubExpr,
	mode,
) {
	const returnsHtml = mode === "render";

	const successReturn = (opId) =>
		returnsHtml
			? `return \\RoxyAPI\\Support\\GenericRenderer::render( '${opId}', is_array( $data ) ? $data : array() );`
			: `return is_array( $data ) ? $data : array();`;
	const errorReturn = returnsHtml
		? `return \\RoxyAPI\\Support\\Templates::api_error( $data );`
		: `return $data;`;

	if (cfg.dispatch) {
		// Multi-target dispatch hero (TarotCard / IChing / Dream).
		const branches = [];
		for (let i = 0; i < cfg.dispatch.length; i++) {
			const d = cfg.dispatch[i];
			const op = opsByIdMap[d.operationId];
			if (!op) {
				throw new Error(
					`[generate] dispatch references unknown operationId: ${d.operationId}`,
				);
			}
			let condition = null;
			if (d.when) {
				const parts = [];
				for (const [attrName, expectedValue] of Object.entries(
					d.when,
				)) {
					const expr =
						sanitisedExpr[attrName] !== undefined
							? sanitisedExpr[attrName]
							: `$atts['${attrName}']`;
					parts.push(`${expr} === ${JSON.stringify(expectedValue)}`);
				}
				condition = parts.join(" && ");
			} else if (d.when_present) {
				condition = `$atts['${d.when_present}'] !== ''`;
			} else if (d.default) {
				condition = null;
			}

			const argsByName = buildArgsForBranch(
				cfg.attributes,
				sanitisedExpr,
				transformedFields,
				op,
				d,
			);
			const callExpr = renderClientCall(d.operationId, argsByName);

			const branchBody = `\t\t\t$data = ${callExpr};
			if ( is_wp_error( $data ) ) {
				${errorReturn}
			}
			${successReturn(d.operationId)}`;

			if (condition === null) {
				branches.push(`\t\t{\n${branchBody}\n\t\t}`);
			} else if (i === 0) {
				branches.push(
					`\t\tif ( ${condition} ) {\n${branchBody}\n\t\t}`,
				);
			} else {
				branches.push(`if ( ${condition} ) {\n${branchBody}\n\t\t}`);
			}
		}

		const stitched = branches.reduce(
			(acc, b, i) => (i === 0 ? b : acc + " else " + b),
			"",
		);

		// Dispatch fallback for cases where no branch matches and no default
		// branch exists. In render mode this surfaces a friendly Templates::error;
		// in fetch mode a WP_Error so FormRouter can render it via Templates::api_error.
		// Form-mode heroes also offer a re-render of the visitor form when the
		// current site mode is not 'static'.
		let fallback = "";
		const hasDefault = cfg.dispatch.some((d) => d.default);
		if (!hasDefault && cfg.missing_message) {
			const translatorComment = `/* translators: %s is the canonical example shortcode. */`;
			const messagePhp = `sprintf( ${translatorComment} __( ${JSON.stringify(
				cfg.missing_message,
			)}, 'roxyapi' ), ${JSON.stringify(cfg.example)} )`;
			if (returnsHtml && cfg.form_mode) {
				const formClassName = `${toPascalCase(tagSuffix)}Form`;
				fallback = `\n\n\t\tif ( $atts['mode'] !== 'static' ) {
			return \\RoxyAPI\\Support\\FormRenderer::render( \\RoxyAPI\\Generated\\Forms\\${formClassName}::class );
		}
		return \\RoxyAPI\\Support\\Templates::error( ${messagePhp} );`;
			} else if (returnsHtml) {
				fallback = `\n\n\t\treturn \\RoxyAPI\\Support\\Templates::error( ${messagePhp} );`;
			} else {
				fallback = `\n\n\t\treturn new \\WP_Error( 'roxyapi_missing_attrs', ${messagePhp} );`;
			}
		}

		return `${stitched}${fallback}`;
	}

	// Single-target hero.
	const op = opsByIdMap[cfg.operationId];
	if (!op) {
		throw new Error(
			`[generate] hero ${tagSuffix} references unknown operationId: ${cfg.operationId}`,
		);
	}

	const args = {};
	const skipIfEmptyEntries = [];
	for (const [attrName, info] of Object.entries(cfg.attributes)) {
		if (info.transform) {
			args.year = transformedSubExpr(attrName, "year");
			args.month = transformedSubExpr(attrName, "month");
			args.day = transformedSubExpr(attrName, "day");
			continue;
		}
		const fieldName = info.spec_field || attrName;
		const valueExpr =
			sanitisedExpr[attrName] !== undefined
				? sanitisedExpr[attrName]
				: `$atts['${attrName}']`;
		args[fieldName] = valueExpr;
		if (info.skip_if_empty) {
			skipIfEmptyEntries.push({ attrName, fieldName });
		}
	}

	if (skipIfEmptyEntries.length > 0 && op.method === "POST") {
		const pathParams = extractPathParams(op.path);
		const bodyLines = [];
		const conditional = [];
		for (const [field, expr] of Object.entries(args)) {
			if (pathParams.includes(field)) continue;
			const skipEntry = skipIfEmptyEntries.find(
				(s) => s.fieldName === field,
			);
			if (skipEntry) {
				conditional.push({
					field,
					expr,
					attrName: skipEntry.attrName,
				});
			} else {
				bodyLines.push(`\t\t\t'${field}' => ${expr},`);
			}
		}
		const bodyArrayPhp =
			bodyLines.length === 0
				? "array()"
				: `array(\n${bodyLines.join("\n")}\n\t\t)`;
		const conditionalAdds = conditional
			.map(
				(c) =>
					`\t\tif ( $atts['${c.attrName}'] !== '' ) {\n\t\t\t$body['${c.field}'] = ${c.expr};\n\t\t}`,
			)
			.join("\n");
		const pathArgsList = pathParams
			.map((p) => (args[p] !== undefined ? args[p] : "''"))
			.join(", ");
		const callArgs = pathArgsList ? `${pathArgsList}, $body` : "$body";

		return `\t\t$body = ${bodyArrayPhp};
${conditionalAdds}

		$data = \\RoxyAPI\\Generated\\Client::${cfg.operationId}( ${callArgs} );

		if ( is_wp_error( $data ) ) {
			${errorReturn}
		}

		${successReturn(cfg.operationId)}`;
	}

	const callExpr = renderClientCall(cfg.operationId, args);
	return `\t\t$data = ${callExpr};

		if ( is_wp_error( $data ) ) {
			${errorReturn}
		}

		${successReturn(cfg.operationId)}`;
}

/** Emit the PHP class for a single hero. */
function emitHeroPhp(tagSuffix, cfg) {
	const className = toPascalCase(tagSuffix);
	const shortcodeTag = "roxy_" + tagSuffix;

	// Heroes that delegate to an existing long-tail Form (synastry, gun_milan,
	// compatibility) are essentially a clean alias for the form-mode shortcode.
	// Static mode is impractical (10+ attributes) so the hero just renders the
	// Form class on every invocation. No DEFAULTS beyond the tag, no
	// fetch_for_form, no companion <Hero>Form class — we reuse the long-tail one.
	if (cfg.delegate_to_form) {
		return `<?php
/**
 * Auto-generated hero shortcode: [${shortcodeTag}]
 *
 * ${cfg.description}
 *
 * Form-only hero. Delegates rendering to the existing long-tail Form
 * class \\RoxyAPI\\Generated\\Forms\\${cfg.delegate_to_form}, which already
 * carries the right multi-section visitor form (two birth charts) and
 * call() implementation. FormRouter handles the POST cycle.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/hero-config.json.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Heroes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ${className} {

	/**
	 * Empty DEFAULTS const satisfies the Test_Hero_Attr_Contract regression
	 * test (every hero class must declare DEFAULTS). Form-only heroes do
	 * not consume attributes — every input flows through the form.
	 *
	 * @var array<string, string>
	 */
	public const DEFAULTS = array();

	/**
	 * Render the shortcode. Always shows the visitor form because static
	 * mode would require 10+ attributes to be passed inline.
	 *
	 * @param array<string, string>|string $atts    Shortcode attributes (ignored).
	 * @param string                       $content Inner content (ignored).
	 * @param string                       $tag     Shortcode tag (ignored).
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		wp_enqueue_style( 'roxyapi-frontend' );
		return \\RoxyAPI\\Support\\FormRenderer::render( \\RoxyAPI\\Generated\\Forms\\${cfg.delegate_to_form}::class );
	}
}
`;
	}

	const hasFormMode = !!cfg.form_mode;
	const formClassName = hasFormMode ? `${className}Form` : null;

	const defaultsArray = renderDefaultsArray(cfg.attributes, hasFormMode);
	const {
		lines: sanitiseLines,
		sanitisedExpr,
		transformedFields,
	} = renderAttrSanitisation(cfg.attributes);
	const requiredCheck = renderRequiredCheck(
		cfg.attributes,
		cfg.missing_message,
		cfg.example,
		formClassName,
	);
	const formModeShortCircuit = renderFormModeShortCircuit(formClassName);

	// Transform error-handling block: for every transformed field, emit a null-check.
	const transformErrorBlocks = [];
	for (const [name] of Object.entries(transformedFields)) {
		const translatorComment = `/* translators: %s is the canonical example shortcode. */`;
		const errMessage = cfg.transform_error_message
			? `sprintf( ${translatorComment} __( ${JSON.stringify(
					cfg.transform_error_message,
			  )}, 'roxyapi' ), ${JSON.stringify(cfg.example)} )`
			: `__( ${JSON.stringify(
					`The ${name} attribute must be in YYYY-MM-DD format.`,
			  )}, 'roxyapi' )`;
		transformErrorBlocks.push(
			`\t\tif ( $${name}_parts === null ) {
			return \\RoxyAPI\\Support\\Templates::error( ${errMessage} );
		}`,
		);
	}

	// Build expressions that include transformed parts injected into argsByName.
	// Transformed fields produce a map of $name_parts['year'|'month'|'day'].
	function transformedSubExpr(attrName, fieldName) {
		return `$${attrName}_parts['${fieldName}']`;
	}

	const bodyContent = buildHeroBodyContent(
		tagSuffix,
		cfg,
		sanitisedExpr,
		transformedFields,
		transformedSubExpr,
		"render",
	);

	const sections = [
		`\t\t$atts = shortcode_atts(\n\t\t\tself::DEFAULTS,\n\t\t\tis_array( $atts ) ? $atts : array(),\n\t\t\t(string) $tag\n\t\t);`,
		`\t\twp_enqueue_style( 'roxyapi-frontend' );`,
	];
	if (formModeShortCircuit) sections.push(formModeShortCircuit);
	if (requiredCheck) sections.push(requiredCheck);
	if (sanitiseLines) sections.push(sanitiseLines);
	if (transformErrorBlocks.length > 0)
		sections.push(transformErrorBlocks.join("\n"));
	sections.push(bodyContent);

	// fetch_for_form parallel method (only when form_mode is configured).
	// Same dispatch + sanitisation as render(), but returns array | WP_Error
	// so the matching <Hero>Form::call() can hand the raw response back to
	// FormRouter for a Post-Redirect-Get cycle.
	let fetchMethod = "";
	if (hasFormMode) {
		const fetchSections = [
			`\t\t$atts = array_merge( self::DEFAULTS, $atts );`,
		];
		const reqFetch = renderRequiredCheckFetch(
			cfg.attributes,
			cfg.missing_message,
			cfg.example,
		);
		if (reqFetch) fetchSections.push(reqFetch);
		if (sanitiseLines) fetchSections.push(sanitiseLines);
		const transformFetch = renderTransformErrorBlocksFetch(
			transformedFields,
			cfg,
		);
		if (transformFetch) fetchSections.push(transformFetch);
		const bodyFetch = buildHeroBodyContent(
			tagSuffix,
			cfg,
			sanitisedExpr,
			transformedFields,
			transformedSubExpr,
			"fetch",
		);
		fetchSections.push(bodyFetch);

		fetchMethod = `

	/**
	 * Visitor-form data path. Same dispatch as render() but returns the raw
	 * API response (or a WP_Error) so the matching <Hero>Form::call() can
	 * surface it via the FormRouter PRG cycle. Caller must pass the form
	 * body keyed by the same attribute names as the shortcode accepts.
	 *
	 * @param array<string, mixed> $atts Form-body attributes.
	 * @return array<string, mixed>|\\WP_Error
	 */
	public static function fetch_for_form( array $atts ) {
${fetchSections.join("\n\n")}
	}`;
	}

	return `<?php
/**
 * Auto-generated hero shortcode: [${shortcodeTag}]
 *
 * ${cfg.description}
 *
 * Example: ${cfg.example}
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/hero-config.json.
 * To change this hero, edit the config and run: npm run generate
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Heroes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ${className} {

	/**
	 * Default attributes accepted by this shortcode. Single source of truth
	 * for the hero attribute contract test.
	 *
	 * @var array<string, string>
	 */
	public const DEFAULTS = array(
${defaultsArray}
	);

	/**
	 * Render the shortcode.
	 *
	 * @param array<string, string>|string $atts    Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag     Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
${sections.join("\n\n")}
	}${fetchMethod}
}
`;
}

/**
 * Convert a hero-config form_mode field declaration to a PHP form-field
 * literal string (one entry in the spec sections/flat_fields array).
 * Only emits keys the FormRenderer recognises so a typo in the config
 * surfaces at PHP rendering rather than silently passing through.
 */
function heroFormFieldToPhp(field, indent) {
	const allowed = [
		"name",
		"label",
		"required",
		"type",
		"help",
		"placeholder",
		"min",
		"max",
		"step",
		"enum",
	];
	const lines = [];
	for (const key of allowed) {
		if (field[key] === undefined) continue;
		lines.push(`${indent}\t'${key}' => ${phpLiteral(field[key])},`);
	}
	return `${indent}array(\n${lines.join("\n")}\n${indent}),`;
}

/**
 * Emit src/Generated/Forms/<Hero>Form.php for a form-mode hero. The form
 * spec mirrors the hero-config form_mode block; the call() implementation
 * delegates back to <Hero>::fetch_for_form() so dispatch / sanitisation /
 * client-call logic stays in one place.
 */
function emitHeroFormPhp(tagSuffix, cfg) {
	if (!cfg.form_mode) return null;
	const heroClass = toPascalCase(tagSuffix);
	const formClassName = `${heroClass}Form`;
	const formId = lowerCamelCase(tagSuffix);
	const fm = cfg.form_mode;
	const submitLabel = fm.submit_label || "Submit";
	const title = fm.title || cfg.title || tagSuffix;

	// Build PHP fragments for fields. A single-section spec keeps the
	// FormRenderer geo autocomplete heuristic working (lat / lon / tz in
	// the same fieldset). Without a section, all fields go into flat_fields.
	const fields = (fm.fields || []).map((f) => ({
		...f,
		name: f.attr || f.name,
	}));

	let sectionsPhp = "";
	let flatPhp = "";
	let callBodyMap = "$body";
	if (fm.section) {
		const sectionName = fm.section.name;
		const sectionLabel = fm.section.label || humanLabel(sectionName);
		const fieldEntries = fields
			.map((f) => heroFormFieldToPhp(f, "\t\t\t\t\t"))
			.join("\n");
		sectionsPhp = `\t\t\tarray(
				'name'   => ${phpLiteral(sectionName)},
				'label'  => ${phpLiteral(sectionLabel)},
				'fields' => array(
${fieldEntries}
				),
			),`;
		// FormRouter sanitises section data into $body[<section>] sub-array;
		// flatten it for the hero method which expects top-level attr keys.
		callBodyMap = `isset( $body['${sectionName}'] ) && is_array( $body['${sectionName}'] ) ? $body['${sectionName}'] : array()`;
	} else {
		flatPhp = fields.map((f) => heroFormFieldToPhp(f, "\t\t\t")).join("\n");
	}

	return `<?php
/**
 * Auto-generated visitor form for the [${
		cfg.tag || "roxy_" + tagSuffix
 }] hero shortcode.
 *
 * Surfaced when the shortcode is invoked without required attributes (or
 * with mode="form"). FormRouter sanitises and validates the POST body
 * against this spec, then calls call() which delegates to the hero's
 * fetch_for_form() so the dispatch / sanitisation / client-call logic
 * stays in one place.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/hero-config.json.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ${formClassName} {

	/**
	 * @return array<string, mixed>
	 */
	public static function spec(): array {
		return array(
			'operation_id' => ${phpLiteral(formId)},
			'title'        => ${phpLiteral(title)},
			'submit_label' => __( ${phpLiteral(submitLabel)}, 'roxyapi' ),
			'sections'     => array(
${sectionsPhp}
			),
			'flat_fields'  => array(
${flatPhp}
			),
		);
	}

	/**
	 * @param array<string, mixed> $body
	 * @return array<string, mixed>|\\WP_Error
	 */
	public static function call( array $body ) {
		$atts = ${callBodyMap};
		return \\RoxyAPI\\Generated\\Heroes\\${heroClass}::fetch_for_form( $atts );
	}
}
`;
}

/**
 * Convert snake_case_or_kebab-case to lowerCamelCase. Matches the form-id
 * grammar FormRouter validates with `/^[A-Za-z][A-Za-z0-9]+$/`.
 */
function lowerCamelCase(str) {
	const pascal = toPascalCase(str);
	return pascal.charAt(0).toLowerCase() + pascal.slice(1);
}

/** Emit the Heroes Manifest exposing display metadata for every hero. */
function emitHeroManifest(heroConfig) {
	const lines = [];
	for (const [tagSuffix, cfg] of Object.entries(heroConfig)) {
		const tag = cfg.tag || "roxy_" + tagSuffix;
		const handWritten = cfg.hand_written ? "true" : "false";
		const className = cfg.hand_written
			? `\\RoxyAPI\\Shortcodes\\${toPascalCase(tagSuffix)}`
			: `\\RoxyAPI\\Generated\\Heroes\\${toPascalCase(tagSuffix)}`;
		// Pick the most-representative operationId for this hero so callers
		// like Catalog::all() (which looks up TTL via Endpoints::get) get a
		// hit instead of a silent null:
		//   - Single-target heroes: use cfg.operationId directly.
		//   - delegate_to_form heroes (synastry / gun_milan / compatibility):
		//     use the underlying form-class operation (e.g. CalculateSynastryForm
		//     → calculateSynastry).
		//   - dispatch heroes (tarot_card / iching / dream): use the default
		//     branch's op, falling back to the first branch if no default.
		let opId = cfg.operationId || "";
		if (!opId && cfg.delegate_to_form) {
			// `CalculateSynastryForm` → `calculateSynastry`
			const formBase = cfg.delegate_to_form.replace(/Form$/, "");
			opId = formBase.charAt(0).toLowerCase() + formBase.slice(1);
		}
		if (!opId && Array.isArray(cfg.dispatch)) {
			const def = cfg.dispatch.find((d) => d.default) || cfg.dispatch[0];
			opId = def && def.operationId ? def.operationId : "";
		}
		lines.push(
			`\t\t\t'${tag}' => array(
				'tag'           => '${tag}',
				'operation_id'  => ${JSON.stringify(opId)},
				'title'         => __( ${JSON.stringify(cfg.title)}, 'roxyapi' ),
				'description'   => __( ${JSON.stringify(cfg.description)}, 'roxyapi' ),
				'code'          => ${JSON.stringify(cfg.example)},
				'domain'        => ${JSON.stringify(cfg.domain)},
				'class'         => '${className.replace(/\\/g, "\\\\")}',
				'hand_written'  => ${handWritten},
			),`,
		);
	}

	return `<?php
/**
 * Auto-generated hero manifest. Lists every hero shortcode (the one
 * hand-written Horoscope plus every absorbed hero) with its display metadata.
 *
 * Onboarding and Catalog read from this manifest so the docs, the registrar
 * and the runtime stay in lockstep with bin/hero-config.json automatically.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/hero-config.json.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Heroes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Manifest {

	/**
	 * Hero shortcode display metadata, keyed by shortcode tag.
	 *
	 * @return array<string, array{tag:string,operation_id:string,title:string,description:string,code:string,domain:string,class:string,hand_written:bool}>
	 */
	public static function all(): array {
		return array(
${lines.join("\n")}
		);
	}
}
`;
}

/** Emit the Heroes Bootstrap that registers every absorbed hero shortcode. */
function emitHeroBootstrap(heroConfig) {
	const calls = [];
	for (const [tagSuffix, cfg] of Object.entries(heroConfig)) {
		if (cfg.hand_written) continue; // hand-written heroes are registered by Shortcodes\Registrar
		const tag = cfg.tag || "roxy_" + tagSuffix;
		const className = toPascalCase(tagSuffix);
		calls.push(
			`\t\tif ( ! shortcode_exists( '${tag}' ) ) {
			add_shortcode(
				'${tag}',
				static function ( $atts, $content, $shortcode_tag ) {
					return \\RoxyAPI\\Generated\\Heroes\\${className}::render( $atts, $content ?? '', (string) $shortcode_tag );
				}
			);
		}`,
		);
	}

	return `<?php
/**
 * Auto-generated hero bootstrap. Registers every absorbed hero shortcode.
 *
 * Called from the Shortcodes\\Registrar at priority 10 alongside the
 * hand-written Horoscope. Hero registration always runs before the generated
 * (long-tail) shortcode bootstrap at priority 20.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/hero-config.json.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Heroes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bootstrap {

	public static function register(): void {
${calls.join("\n")}
	}
}
`;
}

// ---------------------------------------------------------------------------
// Write everything
// ---------------------------------------------------------------------------

// 1. Client.php
const clientPhp = emitClientPhp();
await fs.writeFile(path.join(OUT_PHP, "Client.php"), clientPhp, "utf8");
console.log(
	`[generate] wrote src/Generated/Client.php (${operations.length} methods)`,
);

// 2. Endpoints.php
const endpointsPhp = emitEndpointsPhp();
await fs.writeFile(path.join(OUT_PHP, "Endpoints.php"), endpointsPhp, "utf8");
console.log(
	`[generate] wrote src/Generated/Endpoints.php (${operations.length} entries)`,
);

// 3. Generated shortcodes
for (const op of generated) {
	const className = toPascalCase(op.operationId);
	const phpContent = emitShortcodePhp(op);
	await fs.writeFile(
		path.join(OUT_PHP, "Shortcodes", `${className}.php`),
		phpContent,
		"utf8",
	);
}
console.log(
	`[generate] wrote ${generated.length} shortcode classes to src/Generated/Shortcodes/`,
);

// 3b. Generated form classes (visitor-form mode for nested-object endpoints)
let formCount = 0;
for (const op of generated) {
	if (!hasRequiredObjectBody(op)) continue;
	const className = toPascalCase(op.operationId) + "Form";
	await fs.writeFile(
		path.join(OUT_PHP, "Forms", `${className}.php`),
		emitFormPhp(op),
		"utf8",
	);
	formCount++;
}
console.log(
	`[generate] wrote ${formCount} form classes to src/Generated/Forms/`,
);

// 4. ShortcodeBootstrap.php
const bootstrapPhp = emitBootstrapPhp(generated);
await fs.writeFile(
	path.join(OUT_PHP, "ShortcodeBootstrap.php"),
	bootstrapPhp,
	"utf8",
);
console.log(`[generate] wrote src/Generated/ShortcodeBootstrap.php`);

// 5. Generated block.json files. Endpoints whose request body needs a nested
// object (person1, natalChart, etc.) are skipped: block attributes can hold
// objects but the editor lacks a nested-attribute UI in v1.0, so a block here
// would only show the same "structured input not supported" notice the
// shortcode does. Better to keep them out of the inserter entirely.
let blocksWritten = 0;
let blocksSkippedStructured = 0;
for (const op of generated) {
	if (hasRequiredObjectBody(op)) {
		blocksSkippedStructured++;
		continue;
	}
	const slug = toKebabCase(op.operationId);
	const blockDir = path.join(OUT_BLOCKS, slug);
	await fs.mkdir(blockDir, { recursive: true });
	await fs.writeFile(
		path.join(blockDir, "block.json"),
		emitBlockJson(op),
		"utf8",
	);
	await fs.writeFile(
		path.join(blockDir, "render.php"),
		emitBlockRenderPhp(op),
		"utf8",
	);
	blocksWritten++;
}
console.log(
	`[generate] wrote ${blocksWritten} block.json files to blocks/generated/ (skipped ${blocksSkippedStructured} that need nested-object input)`,
);

// 6. Hero shortcode classes (one per absorbed hero)
let heroClassCount = 0;
let heroFormCount = 0;
for (const [tagSuffix, cfg] of Object.entries(heroConfig)) {
	if (cfg.hand_written) continue;
	const className = toPascalCase(tagSuffix);
	const phpContent = emitHeroPhp(tagSuffix, cfg);
	await fs.writeFile(
		path.join(OUT_PHP, "Heroes", `${className}.php`),
		phpContent,
		"utf8",
	);
	heroClassCount++;

	// Companion <Hero>Form class for form-mode heroes.
	const formPhp = emitHeroFormPhp(tagSuffix, cfg);
	if (formPhp) {
		await fs.writeFile(
			path.join(OUT_PHP, "Forms", `${className}Form.php`),
			formPhp,
			"utf8",
		);
		heroFormCount++;
	}
}
console.log(
	`[generate] wrote ${heroClassCount} hero shortcode classes to src/Generated/Heroes/ (+${heroFormCount} hero form classes)`,
);

// 7. Heroes Manifest.php (display metadata for all heroes including hand-written Horoscope)
await fs.writeFile(
	path.join(OUT_PHP, "Heroes", "Manifest.php"),
	emitHeroManifest(heroConfig),
	"utf8",
);
console.log(`[generate] wrote src/Generated/Heroes/Manifest.php`);

// 8. Heroes Bootstrap.php — INTENTIONALLY NOT EMITTED.
// Hero registration runs through `src/Shortcodes/Registrar::HERO_SHORTCODES`
// (hand-maintained class → tag map). The previously-emitted
// `Generated\Heroes\Bootstrap` was never invoked anywhere in the plugin and
// shipped 5 KB of dead code per install. Removed 2026-04-28 per audit.
// `emitHeroBootstrap` is still defined below for reference but has no
// caller; safe to delete in v1.0.1 along with the function itself.

// 8b. Domains.php — admin-UI registry of OpenAPI tags ordered for the brand book.
const domainEntries = Object.entries(domainRegistry)
	.filter(([k]) => !k.startsWith("_"))
	.map(
		([tag, info]) =>
			`\t\t\t${JSON.stringify(tag)} => array(\n` +
			`\t\t\t\t'label'  => ${JSON.stringify(info.label)},\n` +
			`\t\t\t\t'slug'   => ${JSON.stringify(info.slug)},\n` +
			`\t\t\t\t'accent' => ${JSON.stringify(info.accent)},\n` +
			`\t\t\t),`,
	)
	.join("\n");
const domainsPhp = `<?php
/**
 * Auto-generated brand-domain registry. Maps OpenAPI tag strings to admin-UI
 * metadata (label, slug, accent). Order matches the brand-book domain order.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/domains.json.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Domains {

	/**
	 * Brand-ordered map of OpenAPI tag → label/slug/accent.
	 *
	 * @return array<string, array{label:string,slug:string,accent:string}>
	 */
	public static function all(): array {
		return array(
${domainEntries}
		);
	}
}
`;
await fs.writeFile(path.join(OUT_PHP, "Domains.php"), domainsPhp, "utf8");
console.log(
	`[generate] wrote src/Generated/Domains.php (${
		Object.keys(domainRegistry).filter((k) => !k.startsWith("_")).length
	} domains)`,
);

// 9. Restore README
if (readmeContent) {
	await fs.writeFile(readmePath, readmeContent, "utf8");
}

// ---------------------------------------------------------------------------
// 10. Validate every generated POST endpoint's example body against the spec
// schema using ajv. Catches type/required/enum/format drift between the
// OpenAPI spec and our example values before the plugin ever hits the SaaS.
// Failures are fatal so codegen cannot land on main without a deliberate
// fix in the spec or an entry in bin/example-overrides.json.
// ---------------------------------------------------------------------------

const { default: Ajv } = await import("ajv");
const { default: addFormats } = await import("ajv-formats");
const ajv = new Ajv({
	allErrors: true,
	strict: false,
	useDefaults: false,
	// Pre-register every component schema so $ref resolution works without
	// loading remote URIs. ajv treats #/components/schemas/Foo refs natively
	// when the root schema is the spec itself.
});
addFormats(ajv);

let validateFailures = 0;
for (const op of operations) {
	if (op.method !== "POST") continue;
	if (heroSet.has(op.operationId)) continue; // heroes have their own contract test
	if (hasRequiredObjectBody(op)) continue; // block-only by design

	const schemaNode = op.requestBody?.content?.["application/json"]?.schema;
	if (!schemaNode) continue;

	// Build the body the generated shortcode would build, using the (possibly
	// overridden) spec examples and the same numeric coercion rules.
	const body = {};
	for (const f of extractBodyFields(op)) {
		const ex = applyExampleOverride(op.operationId, f.name, f.example);
		if (ex === undefined || ex === null || ex === "") continue;
		if (f.type === "integer") {
			body[f.name] = typeof ex === "string" ? parseInt(ex, 10) : ex;
		} else if (f.type === "number") {
			body[f.name] = typeof ex === "string" ? parseFloat(ex) : ex;
		} else if (f.type === "boolean") {
			body[f.name] = ex === "1" || ex === "true" || ex === true;
		} else {
			body[f.name] = ex;
		}
	}

	// Use the spec as the root schema so #/components/schemas/* refs resolve.
	const validate = ajv.compile({
		...schemaNode,
		components: spec.components,
	});
	const ok = validate(body);
	if (!ok) {
		validateFailures++;
		console.error(
			`[generate] FAIL ${op.operationId} (${op.method} ${op.path})`,
		);
		for (const err of validate.errors || []) {
			console.error(
				`  ${err.instancePath || "/"} ${err.message} (${JSON.stringify(
					err.params,
				)})`,
			);
		}
	}
}

if (validateFailures > 0) {
	console.error(
		`[generate] ${validateFailures} endpoint(s) failed schema validation.`,
	);
	console.error(
		`[generate] Either fix the example in the spec, or override it in bin/example-overrides.json.`,
	);
	process.exit(1);
}

console.log(
	`[generate] schema validation: every example body validates against its spec schema`,
);
console.log(
	`[generate] done. ${operations.length} total, ${heroSet.size} hero, ${generated.length} generated, ${heroClassCount} absorbed-hero classes.`,
);
