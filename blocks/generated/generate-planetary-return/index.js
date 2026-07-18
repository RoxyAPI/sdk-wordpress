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
		"name": "planet",
		"control": "select",
		"label": "Planet",
		"required": true,
		"help": "Planet for the return calculation",
		"options": [
			"Mercury",
			"Venus",
			"Mars",
			"Jupiter",
			"Saturn"
		]
	},
	{
		"name": "approximateDate",
		"control": "date",
		"label": "Approximate date",
		"required": true,
		"help": "Approximate date near the expected planetary return (YYYY-MM-DD)"
	},
	{
		"name": "latitude",
		"control": "number",
		"label": "Latitude",
		"required": true,
		"help": "Latitude of the return location in decimal degrees (-90 to 90)"
	},
	{
		"name": "longitude",
		"control": "number",
		"label": "Longitude",
		"required": true,
		"help": "Longitude of the return location in decimal degrees (-180 to 180)."
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
