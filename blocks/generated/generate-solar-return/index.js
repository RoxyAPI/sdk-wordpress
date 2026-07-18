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
		"help": "Original birth date in YYYY-MM-DD format"
	},
	{
		"name": "birthTime",
		"control": "time",
		"label": "Birth time",
		"required": true,
		"help": "Original birth time in 24-hour HH:MM:SS format"
	},
	{
		"name": "returnYear",
		"control": "number",
		"label": "Return year",
		"required": true,
		"help": "Year for which to cast the solar return chart"
	},
	{
		"name": "latitude",
		"control": "number",
		"label": "Latitude",
		"required": true,
		"help": "Latitude of the solar return location in decimal degrees (-90 to 90)"
	},
	{
		"name": "longitude",
		"control": "number",
		"label": "Longitude",
		"required": true,
		"help": "Longitude of the solar return location in decimal degrees (-180 to 180)"
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": true,
		"help": "Decimal hours from UTC OR IANA name (e.g. \"America/New_York\")"
	},
	{
		"name": "houseSystem",
		"control": "select",
		"label": "House system",
		"required": false,
		"help": "House system for the solar return chart",
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
