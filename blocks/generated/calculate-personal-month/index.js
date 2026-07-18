import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "month",
		"control": "number",
		"label": "Month",
		"required": true,
		"help": "Birth month (1-12)"
	},
	{
		"name": "day",
		"control": "number",
		"label": "Day",
		"required": true,
		"help": "Birth day (1-31)"
	},
	{
		"name": "year",
		"control": "number",
		"label": "Year",
		"required": false,
		"help": "Target year for calculation (defaults to current year)"
	},
	{
		"name": "targetMonth",
		"control": "number",
		"label": "Target month",
		"required": false,
		"help": "Target calendar month to forecast (1-12, defaults to current month)"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
