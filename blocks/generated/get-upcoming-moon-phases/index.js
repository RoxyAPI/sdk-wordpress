import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "lang",
		"control": "select",
		"label": "Lang",
		"required": false,
		"help": "Response language (ISO 639-1)",
		"options": [
			"en",
			"tr",
			"de",
			"es",
			"hi",
			"pt",
			"fr",
			"ru"
		]
	},
	{
		"name": "startDate",
		"control": "date",
		"label": "Start date",
		"required": false,
		"help": "Start date in YYYY-MM-DD format"
	},
	{
		"name": "count",
		"control": "number",
		"label": "Count",
		"required": false,
		"help": "Number of upcoming moon phase transitions to return (1-20)"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
