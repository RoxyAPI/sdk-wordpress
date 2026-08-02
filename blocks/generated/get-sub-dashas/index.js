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
		"help": "Birth location latitude in decimal degrees"
	},
	{
		"name": "longitude",
		"control": "number",
		"label": "Longitude",
		"required": true,
		"help": "Birth location longitude in decimal degrees"
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": false,
		"help": "Timezone: IANA name (e.g. \"America/New_York\", \"Europe/London\") OR decimal hours from UTC (e.g. -5 for EST, 1 for CET)"
	},
	{
		"name": "ayanamsa",
		"control": "select",
		"label": "Ayanamsa",
		"required": false,
		"help": "Ayanamsa system used to place the birth Moon in its nakshatra, which sets every dasha start and end date",
		"options": [
			"kp-newcomb",
			"kp-old",
			"lahiri",
			"raman",
			"custom"
		]
	},
	{
		"name": "ayanamsaValue",
		"control": "number",
		"label": "Ayanamsa value",
		"required": false,
		"help": "Custom ayanamsa value in degrees"
	},
	{
		"name": "significators",
		"control": "toggle",
		"label": "Significators",
		"required": false,
		"help": "Set true to attach the KP significators of each period lord: its star lord, sub lord, occupied house, the houses it signifies at levels L1 to L4, and a strength grade"
	},
	{
		"name": "nodeType",
		"control": "select",
		"label": "Node type",
		"required": false,
		"help": "Lunar node type for Rahu and Ketu, used ONLY when \"significators\" is true",
		"options": [
			"mean",
			"true"
		]
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
