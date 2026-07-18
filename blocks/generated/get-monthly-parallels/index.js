import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "year",
		"control": "number",
		"label": "Year",
		"required": true,
		"help": "Year for monthly parallel analysis (1900-2100)."
	},
	{
		"name": "month",
		"control": "number",
		"label": "Month",
		"required": true,
		"help": "Month number (1-12) for parallel analysis."
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": false,
		"help": "Timezone offset from UTC in hours"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
