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
		"help": "Birth date in YYYY-MM-DD format"
	},
	{
		"name": "time",
		"control": "time",
		"label": "Time",
		"required": true,
		"help": "Birth time in 24-hour HH:MM:SS format"
	},
	{
		"name": "latitude",
		"control": "number",
		"label": "Latitude",
		"required": true,
		"help": "Birthplace latitude in decimal degrees (-90 to 90)"
	},
	{
		"name": "longitude",
		"control": "number",
		"label": "Longitude",
		"required": true,
		"help": "Birthplace longitude in decimal degrees (-180 to 180)"
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": true,
		"help": "Decimal hours from UTC (e.g. -5 for EST, 5.5 for IST, 9 for JST) OR IANA name (e.g. \"America/New_York\")"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
