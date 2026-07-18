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
		"name": "limit",
		"control": "number",
		"label": "Limit",
		"required": false,
		"help": "Maximum items to return per page"
	},
	{
		"name": "offset",
		"control": "number",
		"label": "Offset",
		"required": false,
		"help": "Number of items to skip for pagination"
	},
	{
		"name": "type",
		"control": "select",
		"label": "Type",
		"required": false,
		"help": "Filter results by angel number pattern type",
		"options": [
			"repeating",
			"sequential",
			"mirror",
			"master",
			"root",
			"compound"
		]
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
