import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "date",
		"control": "date",
		"label": "Date",
		"required": true,
		"help": "Date in YYYY-MM-DD format"
	},
	{
		"name": "time",
		"control": "time",
		"label": "Time",
		"required": true,
		"help": "Time in HH:MM:SS format (24-hour)"
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": true,
		"help": "Timezone offset from UTC in decimal hours (NOT minutes format)"
	},
	{
		"name": "planets",
		"control": "text",
		"label": "Planets",
		"required": false,
		"help": "Optional: specific bodies to calculate aspects for (defaults to all 14: the 10 classical planets, the lunar nodes, Chiron, and Black Moon Lilith)"
	},
	{
		"name": "aspectTypes",
		"control": "text",
		"label": "Aspect types",
		"required": false,
		"help": "Optional: specific aspect types to find (defaults to all 9)"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
