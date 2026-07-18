import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "month",
		"control": "number",
		"label": "Month",
		"required": true,
		"help": "Birth month (1-12)"
	},
	{
		"name": "day",
		"control": "number",
		"label": "Day",
		"required": true,
		"help": "Birth day (1-31)"
	},
	{
		"name": "targetDate",
		"control": "date",
		"label": "Target date",
		"required": false,
		"help": "Target date in YYYY-MM-DD format"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
