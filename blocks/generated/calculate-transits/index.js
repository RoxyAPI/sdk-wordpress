import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "date",
		"control": "date",
		"label": "Date",
		"required": false,
		"help": "Transit date in YYYY-MM-DD format (defaults to current date)"
	},
	{
		"name": "time",
		"control": "time",
		"label": "Time",
		"required": false,
		"help": "Transit time in HH:MM:SS format (defaults to current time)"
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": false,
		"help": "Transit timezone: decimal hours from UTC OR IANA name (e.g. \"America/New_York\")"
	},
	{
		"name": "natalChart",
		"control": "text",
		"label": "Natal chart",
		"required": false,
		"help": "Optional natal chart data to compare transits against"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
