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
		"name": "year",
		"control": "number",
		"label": "Year",
		"required": true,
		"help": "Year to cast the solar return for"
	},
	{
		"name": "latitude",
		"control": "number",
		"label": "Latitude",
		"required": true,
		"help": "Latitude of the solar return location in decimal degrees"
	},
	{
		"name": "longitude",
		"control": "number",
		"label": "Longitude",
		"required": true,
		"help": "Longitude of the solar return location in decimal degrees"
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": true,
		"help": "IANA name (e.g. \"America/New_York\", \"Europe/London\", \"UTC\"), decimal hours (e.g. -5 for EST, 1 for CET), or a fixed UTC offset (e.g. \"-05:00\", \"+01:00\")"
	},
	{
		"name": "houseSystem",
		"control": "select",
		"label": "House system",
		"required": false,
		"help": "House system for the return chart",
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
