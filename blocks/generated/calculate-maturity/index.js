import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "lifePath",
		"control": "number",
		"label": "Life path",
		"required": false,
		"help": "Your Life Path number (1-9, 11, 22, 33)"
	},
	{
		"name": "expression",
		"control": "number",
		"label": "Expression",
		"required": false,
		"help": "Your Expression number (1-9, 11, 22, 33)"
	},
	{
		"name": "fullName",
		"control": "text",
		"label": "Full name",
		"required": false,
		"help": "Full birth name to calculate Expression number automatically"
	},
	{
		"name": "year",
		"control": "number",
		"label": "Year",
		"required": false,
		"help": "Birth year to calculate Life Path automatically"
	},
	{
		"name": "month",
		"control": "number",
		"label": "Month",
		"required": false,
		"help": "Birth month (1-12)"
	},
	{
		"name": "day",
		"control": "number",
		"label": "Day",
		"required": false,
		"help": "Birth day (1-31)"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
