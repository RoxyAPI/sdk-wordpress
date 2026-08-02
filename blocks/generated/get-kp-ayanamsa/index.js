import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "date",
		"control": "date",
		"label": "Date",
		"required": false,
		"help": "Date for ayanamsa calculation in YYYY-MM-DD format"
	},
	{
		"name": "time",
		"control": "time",
		"label": "Time",
		"required": false,
		"help": "Time of day in 24-hour HH:MM:SS format, interpreted in the timezone below"
	},
	{
		"name": "timezone",
		"control": "text",
		"label": "Timezone",
		"required": false,
		"help": "IANA name (e.g. \"Asia/Kolkata\", \"America/New_York\"), decimal hours (e.g. 5.5 for IST, -5 for EST), or a fixed UTC offset (e.g. \"+05:30\")"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
