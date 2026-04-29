#!/usr/bin/env node
/**
 * Strip developer-only files that `npm-packlist` always includes from the
 * generated plugin zip.
 *
 * Background: `wp-scripts plugin-zip` delegates file discovery to
 * `npm-packlist` (when a `files` field is set in package.json). npm-packlist
 * unconditionally injects `README.md` and `package.json` because npm itself
 * needs them. Neither belongs in the WordPress.org distribution — `readme.txt`
 * is the canonical plugin readme for wp.org and `package.json` carries no
 * runtime value.
 *
 * Run after `wp-scripts plugin-zip`. Idempotent. Silently no-ops if the zip
 * does not exist (e.g. during a partial build) or the entries are already
 * gone (e.g. on a re-run).
 */
import { execFileSync } from "node:child_process";
import { existsSync } from "node:fs";
import { fileURLToPath } from "node:url";
import path from "node:path";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, "..");
const zipPath = path.join(root, "roxyapi.zip");

if (!existsSync(zipPath)) {
	console.warn(`[post-plugin-zip] ${zipPath} not found, skipping.`);
	process.exit(0);
}

const offenders = ["roxyapi/README.md", "roxyapi/package.json"];

try {
	execFileSync("zip", ["-d", zipPath, ...offenders], {
		stdio: ["ignore", "pipe", "pipe"],
	});
	console.log(
		`[post-plugin-zip] stripped ${offenders.length} developer-only files from roxyapi.zip`,
	);
} catch (err) {
	// `zip -d` exits non-zero when an entry is not present. That is the
	// idempotent re-run case — nothing to do.
	const stderr = err.stderr ? err.stderr.toString().toLowerCase() : "";
	if (stderr.includes("nothing to do")) {
		console.log(
			`[post-plugin-zip] no developer-only files to strip (already clean)`,
		);
	} else {
		throw err;
	}
}
