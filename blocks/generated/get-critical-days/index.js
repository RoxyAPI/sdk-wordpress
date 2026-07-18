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
		"name": "startDate",
		"control": "date",
		"label": "Start date",
		"required": false,
		"help": "Start date of the search range in YYYY-MM-DD format"
	},
	{
		"name": "endDate",
		"control": "date",
		"label": "End date",
		"required": false,
		"help": "End date of the search range in YYYY-MM-DD format"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
