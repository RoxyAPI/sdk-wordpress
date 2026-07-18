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
		"help": "Date in YYYY-MM-DD format"
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
		"help": "Timezone offset from UTC in decimal hours"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
