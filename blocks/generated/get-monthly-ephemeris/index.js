import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "year",
		"control": "number",
		"label": "Year",
		"required": true,
		"help": "Year for monthly ephemeris (1900-2100)."
	},
	{
		"name": "month",
		"control": "number",
		"label": "Month",
		"required": true,
		"help": "Month number (1-12) for ephemeris."
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
