import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "fullName",
		"control": "text",
		"label": "Full name",
		"required": true,
		"help": "Full legal birth name as it appears on the birth certificate"
	},
	{
		"name": "year",
		"control": "number",
		"label": "Year",
		"required": true,
		"help": "Birth year between 100 and 2100"
	},
	{
		"name": "month",
		"control": "number",
		"label": "Month",
		"required": true,
		"help": "Birth month (1 to 12)"
	},
	{
		"name": "day",
		"control": "number",
		"label": "Day",
		"required": true,
		"help": "Birth day (1 to 31)"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
