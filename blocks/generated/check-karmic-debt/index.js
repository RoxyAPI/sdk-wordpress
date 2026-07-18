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
		"help": "Birth year (checks Life Path)"
	},
	{
		"name": "month",
		"control": "number",
		"label": "Month",
		"required": false,
		"help": "Birth month (checks Life Path)"
	},
	{
		"name": "day",
		"control": "number",
		"label": "Day",
		"required": false,
		"help": "Birth day (checks Life Path)"
	},
	{
		"name": "fullName",
		"control": "text",
		"label": "Full name",
		"required": false,
		"help": "Full birth name (checks Expression, Soul Urge, Personality)"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
