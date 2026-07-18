import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "id",
		"control": "text",
		"label": "Id",
		"required": true
	},
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
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
