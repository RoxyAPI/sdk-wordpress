import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "birthDate",
		"control": "date",
		"label": "Birth date",
		"required": true,
		"help": "Birth date of the person in YYYY-MM-DD format."
	},
	{
		"name": "targetDate",
		"control": "date",
		"label": "Target date",
		"required": false,
		"help": "Date to get phase information for in YYYY-MM-DD format"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
