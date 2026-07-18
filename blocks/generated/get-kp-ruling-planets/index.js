import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
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
		"help": "Timezone: IANA name (e.g. \"America/New_York\", \"Europe/London\") OR decimal hours from UTC"
	},
	{
		"name": "datetime",
		"control": "text",
		"label": "Datetime",
		"required": false,
		"help": "ISO 8601 datetime (YYYY-MM-DDTHH:MM:SS) for ruling planets"
	},
	{
		"name": "birthDate",
		"control": "date",
		"label": "Birth date",
		"required": false,
		"help": "Birth date (YYYY-MM-DD) to calculate significators"
	},
	{
		"name": "birthTime",
		"control": "time",
		"label": "Birth time",
		"required": false,
		"help": "Birth time (HH:MM:SS) for significator calculation"
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
