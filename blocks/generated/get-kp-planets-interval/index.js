import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "startDatetime",
		"control": "text",
		"label": "Start datetime",
		"required": true,
		"help": "Start datetime in ISO 8601 (YYYY-MM-DDTHH:MM:SS)"
	},
	{
		"name": "endDatetime",
		"control": "text",
		"label": "End datetime",
		"required": true,
		"help": "End datetime in ISO 8601 (YYYY-MM-DDTHH:MM:SS)"
	},
	{
		"name": "intervalMinutes",
		"control": "number",
		"label": "Interval minutes",
		"required": true,
		"help": "Time between calculations in minutes"
	},
	{
		"name": "latitude",
		"control": "number",
		"label": "Latitude",
		"required": true,
		"help": "Observer latitude in decimal degrees (for future Lagna calculations)"
	},
	{
		"name": "longitude",
		"control": "number",
		"label": "Longitude",
		"required": true,
		"help": "Observer longitude in decimal degrees (for future Lagna calculations)"
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": false,
		"help": "IANA name (e.g. \"America/New_York\", \"Europe/London\") OR decimal hours from UTC"
	},
	{
		"name": "ayanamsa",
		"control": "select",
		"label": "Ayanamsa",
		"required": false,
		"help": "Ayanamsa system for sidereal conversion",
		"options": [
			"kp-newcomb",
			"kp-old",
			"lahiri"
		]
	},
	{
		"name": "nodeType",
		"control": "select",
		"label": "Node type",
		"required": false,
		"help": "Lunar node type for Rahu and Ketu positions",
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
