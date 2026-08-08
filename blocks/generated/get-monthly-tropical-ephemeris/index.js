import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "year",
		"control": "number",
		"label": "Year",
		"required": false,
		"help": "Year for the monthly ephemeris (1900-2100)"
	},
	{
		"name": "month",
		"control": "number",
		"label": "Month",
		"required": false,
		"help": "Month number (1-12) for the ephemeris"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
