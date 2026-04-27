#!/usr/bin/env node
/**
 * Post-build fixup for build/blocks-manifest.php.
 *
 * wp-scripts emits the manifest without the ABSPATH guard that WP.org
 * Plugin Check requires. Idempotent. Safe to run after every build.
 */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const FILE = path.join(ROOT, "build", "blocks-manifest.php");

const c = fs.readFileSync(FILE, "utf8");
if (c.includes("defined( 'ABSPATH' )")) {
	console.log("[post-build] guard already present");
	process.exit(0);
}

const patched = c.replace(
	/^<\?php\n/,
	"<?php\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n",
);
fs.writeFileSync(FILE, patched);
console.log("[post-build] added ABSPATH guard to build/blocks-manifest.php");
