/**
 * Classic JSX runtime: block scripts must not depend on `react-jsx-runtime`,
 * a script handle core registers only from WordPress 6.6 (floor is 6.5).
 * The pragma plugin imports `createElement` / `Fragment` from
 * `@wordpress/element` wherever JSX appears, so sources need no import.
 */
module.exports = {
	presets: [ '@wordpress/babel-preset-default' ],
	plugins: [
		[
			'@wordpress/babel-plugin-import-jsx-pragma',
			{
				scopeVariable: 'createElement',
				scopeVariableFrag: 'Fragment',
				source: '@wordpress/element',
				isDefault: false,
			},
		],
		[
			'@babel/plugin-transform-react-jsx',
			{
				runtime: 'classic',
				pragma: 'createElement',
				pragmaFrag: 'Fragment',
			},
		],
	],
};
