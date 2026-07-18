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
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": true,
		"help": "Birth timezone: decimal hours from UTC (e.g. -5 for EST, 5.5 for IST) OR IANA name (e.g. \"America/New_York\")"
	},
	{
		"name": "birthLatitude",
		"control": "number",
		"label": "Birth latitude",
		"required": true,
		"help": "Birthplace latitude in decimal degrees (-90 to 90)"
	},
	{
		"name": "birthLongitude",
		"control": "number",
		"label": "Birth longitude",
		"required": true,
		"help": "Birthplace longitude in decimal degrees (-180 to 180)"
	},
	{
		"name": "relocationLatitude",
		"control": "number",
		"label": "Relocation latitude",
		"required": true,
		"help": "New location latitude in decimal degrees (-90 to 90)"
	},
	{
		"name": "relocationLongitude",
		"control": "number",
		"label": "Relocation longitude",
		"required": true,
		"help": "New location longitude in decimal degrees (-180 to 180)"
	},
	{
		"name": "houseSystem",
		"control": "select",
		"label": "House system",
		"required": false,
		"help": "House system for dividing the relocated chart into 12 houses",
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
