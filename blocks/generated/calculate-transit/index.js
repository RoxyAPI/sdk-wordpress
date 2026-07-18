import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "birthDate",
		"control": "date",
		"label": "Birth date",
		"required": true,
		"help": "Birth date in YYYY-MM-DD format"
	},
	{
		"name": "birthTime",
		"control": "time",
		"label": "Birth time",
		"required": true,
		"help": "Birth time in HH:MM:SS format (24-hour)"
	},
	{
		"name": "transitDate",
		"control": "date",
		"label": "Transit date",
		"required": true,
		"help": "Transit date to analyze in YYYY-MM-DD format"
	},
	{
		"name": "transitTime",
		"control": "time",
		"label": "Transit time",
		"required": false,
		"help": "Transit time in HH:MM:SS format (24-hour)"
	},
	{
		"name": "latitude",
		"control": "number",
		"label": "Latitude",
		"required": true,
		"help": "Observer latitude in decimal degrees"
	},
	{
		"name": "longitude",
		"control": "number",
		"label": "Longitude",
		"required": true,
		"help": "Observer longitude in decimal degrees"
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": false,
		"help": "Timezone offset from UTC in hours"
	},
	{
		"name": "coordinateSystem",
		"control": "select",
		"label": "Coordinate system",
		"required": false,
		"help": "Coordinate system for longitude output",
		"options": [
			"sidereal",
			"tropical"
		]
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
