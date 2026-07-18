import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "planet",
		"control": "text",
		"label": "Planet",
		"required": true,
		"help": "Planet to track (case-insensitive)"
	},
	{
		"name": "startDate",
		"control": "date",
		"label": "Start date",
		"required": true,
		"help": "Start date for sign ingress search (YYYY-MM-DD format)"
	},
	{
		"name": "endDate",
		"control": "date",
		"label": "End date",
		"required": true,
		"help": "End date for sign ingress search (YYYY-MM-DD format)"
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
