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
		"help": "IANA name (e.g. \"America/New_York\", \"Europe/London\", \"UTC\"), decimal hours (e.g. -5 for EST, 1 for CET), or a fixed UTC offset (e.g. \"-05:00\", \"+01:00\")"
	},
	{
		"name": "latitude",
		"control": "number",
		"label": "Latitude",
		"required": false,
		"help": "Birth latitude in decimal degrees"
	},
	{
		"name": "longitude",
		"control": "number",
		"label": "Longitude",
		"required": false,
		"help": "Birth longitude in decimal degrees"
	},
	{
		"name": "nodeType",
		"control": "select",
		"label": "Node type",
		"required": false,
		"help": "Lunar node convention for the North and South Node activations",
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
