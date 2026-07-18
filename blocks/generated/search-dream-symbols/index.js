import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "q",
		"control": "text",
		"label": "Q",
		"required": false,
		"help": "Search query to match against symbol names and meanings"
	},
	{
		"name": "letter",
		"control": "text",
		"label": "Letter",
		"required": false,
		"help": "Filter symbols by starting letter (a-z)"
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
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
