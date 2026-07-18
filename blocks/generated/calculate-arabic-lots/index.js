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
		"help": "Birth location latitude in decimal degrees (-90 to 90)"
	},
	{
		"name": "longitude",
		"control": "number",
		"label": "Longitude",
		"required": true,
		"help": "Birth location longitude in decimal degrees (-180 to 180)"
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": true,
		"help": "Timezone: IANA name (e.g. \"America/New_York\", \"Europe/London\") OR decimal hours from UTC (e.g. -5 for EST, 1 for CET)"
	},
	{
		"name": "houseSystem",
		"control": "select",
		"label": "House system",
		"required": false,
		"help": "House system used to place the Sun, which determines the chart sect (day when the Sun is above the horizon, night when below) and therefore which lot formula applies",
		"options": [
			"placidus",
			"whole-sign",
			"equal",
			"koch"
		]
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
