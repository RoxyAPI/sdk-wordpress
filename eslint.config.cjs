/**
 * Project ESLint flat config.
 *
 * Extends the @wordpress/scripts default so we keep wp-coding-standards,
 * then layers project-local overrides for the three places the default
 * is wrong for this repo:
 *
 *   1. blocks/ source uses @wordpress/* packages that wp-scripts maps to
 *      WordPress global script handles (wp-block-editor, wp-components,
 *      wp-i18n, wp-blocks) at build time via DependencyExtractionWebpackPlugin.
 *      eslint cannot resolve those via npm so we mark them as known
 *      core externals to silence import/no-unresolved + import/no-extraneous-dependencies.
 *
 *   2. bin/ files are Node CLI scripts. console.log is the output channel,
 *      not a debug leftover. Disable no-console there.
 *
 *   3. bin/generate.mjs is a >2k-line code generator written under speed
 *      not under JSDoc-discipline. Relax jsdoc/require-param-type plus
 *      a handful of stylistic rules that fight the codegen idiom (bitwise
 *      ops in hash functions, intentional shadowing, scratch unused-vars
 *      from in-progress branches).
 */

const wpScriptsConfig = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	...wpScriptsConfig,

	// Block editor source: @wordpress/* packages are externalised by
	// wp-scripts at build time; eslint should not flag them as missing.
	{
		files: [ 'blocks/**/*.{js,jsx}' ],
		rules: {
			'import/no-unresolved': [
				'error',
				{
					ignore: [ '^@wordpress/' ],
				},
			],
			'import/no-extraneous-dependencies': [
				'error',
				{
					peerDependencies: true,
					packageDir: __dirname,
				},
			],
		},
		settings: {
			'import/core-modules': [
				'@wordpress/block-editor',
				'@wordpress/blocks',
				'@wordpress/components',
				'@wordpress/element',
				'@wordpress/i18n',
				'@wordpress/server-side-render',
			],
		},
	},

	// Browser-side admin scripts: pure vanilla running in the WP admin.
	// `history`, `window`, `document`, `fetch`, `URLSearchParams` are
	// browser globals — declare them so no-undef does not fire.
	{
		files: [ 'assets/js/**/*.js' ],
		languageOptions: {
			globals: {
				window: 'readonly',
				document: 'readonly',
				history: 'readonly',
				location: 'readonly',
				navigator: 'readonly',
				fetch: 'readonly',
				FormData: 'readonly',
				URLSearchParams: 'readonly',
				console: 'readonly',
				setTimeout: 'readonly',
				clearTimeout: 'readonly',
			},
		},
		rules: {
			'no-unused-vars': [
				'error',
				{
					args: 'none',
					caughtErrors: 'none',
				},
			],
		},
	},

	// CLI scripts under bin/: console.log is the output channel.
	// generate.mjs is a code generator; relax JSDoc-strictness and
	// codegen-friendly idioms.
	{
		files: [ 'bin/**/*.{js,mjs,cjs}' ],
		languageOptions: {
			globals: {
				process: 'readonly',
				console: 'readonly',
				URL: 'readonly',
				fetch: 'readonly',
			},
		},
		rules: {
			'no-console': 'off',
			'no-bitwise': 'off',
			'no-shadow': 'off',
			'no-unused-vars': [
				'error',
				{
					args: 'none',
					caughtErrors: 'none',
					varsIgnorePattern: '^_',
				},
			],
			'@wordpress/no-unused-vars-before-return': 'off',
			'jsdoc/require-param-type': 'off',
			'jsdoc/require-returns-type': 'off',
		},
	},
];
