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
		"help": "Timezone offset from UTC in hours"
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
			"lahiri",
			"custom"
		]
	},
	{
		"name": "ayanamsaValue",
		"control": "number",
		"label": "Ayanamsa value",
		"required": false,
		"help": "Custom ayanamsa value in degrees"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
