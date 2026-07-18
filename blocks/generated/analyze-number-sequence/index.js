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
		"name": "number",
		"control": "text",
		"label": "Number",
		"required": true,
		"help": "Number sequence to analyze (1-8 digits)"
	},
	{
		"name": "context",
		"control": "select",
		"label": "Context",
		"required": false,
		"help": "Where the number was seen",
		"options": [
			"clock",
			"receipt",
			"license-plate",
			"phone",
			"address",
			"price"
		]
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
