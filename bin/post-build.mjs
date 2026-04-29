#!/usr/bin/env node
/**
 * Post-build fixup: inject the `ABSPATH` direct-file-access guard into every
 * PHP file wp-scripts emits without one. WP.org Plugin Check at severity 6
 * surfaces `MissingDirectFileAccessProtection` for any shipped PHP file
 * lacking the guard, and reviewers do hand-flag this.
 *
 * Targets:
 *   - build/blocks-manifest.php (registered blocks index, one per build)
 *   - build/blocks/<slug>/index.asset.php (one per block, holds dependency
 *     fingerprints; emitted by wp-scripts/dependency-extraction-webpack-plugin
 *     as bare `<?php return array(...);` files)
 *   - any future block render.php that wp-scripts copies through without a
 *     guard already in place
 *
 * Idempotent: skips files that already contain the guard string. Safe to
 * run after every `npm run build:all`.
 */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const BUILD = path.join(ROOT, "build");

const GUARD_NEEDLE = "defined( 'ABSPATH' )";
const GUARD_BLOCK = "<?php\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n";

let added = 0;
let skipped = 0;

function patch(file) {
	const c = fs.readFileSync(file, "utf8");
	if (c.includes(GUARD_NEEDLE)) {
		skipped++;
		return;
	}
	// Only touch files that actually start with `<?php` — anything else is
	// not a PHP source we should rewrite. Use a narrow regex anchored at the
	// start so we never inject into the middle of a file.
	const patched = c.replace(/^<\?php(\s*\n)?/, GUARD_BLOCK);
	if (patched === c) {
		// File didn't start with `<?php` — refuse to touch silently.
		console.warn(`[post-build] WARN: not a <?php-headed file, skipped: ${path.relative(ROOT, file)}`);
		return;
	}
	fs.writeFileSync(file, patched);
	added++;
}

function walk(dir) {
	if (!fs.existsSync(dir)) return;
	for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
		const full = path.join(dir, entry.name);
		if (entry.isDirectory()) {
			walk(full);
		} else if (entry.isFile() && entry.name.endsWith(".php")) {
			patch(full);
		}
	}
}

walk(BUILD);

console.log(
	`[post-build] ABSPATH guard: added to ${added} file(s), already present in ${skipped} file(s)`,
);
