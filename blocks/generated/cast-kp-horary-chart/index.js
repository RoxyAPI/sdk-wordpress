import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "horaryNumber",
		"control": "number",
		"label": "Horary number",
		"required": true,
		"help": "Horary number from 1 to 249, given by the querent while focused on their question"
	},
	{
		"name": "date",
		"control": "date",
		"label": "Date",
		"required": true,
		"help": "Date the question was taken up for judgment, YYYY-MM-DD"
	},
	{
		"name": "time",
		"control": "time",
		"label": "Time",
		"required": true,
		"help": "Time the question was taken up for judgment, 24-hour HH:MM:SS"
	},
	{
		"name": "latitude",
		"control": "number",
		"label": "Latitude",
		"required": true,
		"help": "Latitude where the question is judged, decimal degrees"
	},
	{
		"name": "longitude",
		"control": "number",
		"label": "Longitude",
		"required": true,
		"help": "Longitude where the question is judged, decimal degrees."
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": false,
		"help": "Timezone: IANA name (e.g. \"Asia/Kolkata\") OR decimal hours from UTC"
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
