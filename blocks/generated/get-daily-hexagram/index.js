import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "seed",
		"control": "text",
		"label": "Seed",
		"required": false,
		"help": "Optional seed for reproducible readings"
	},
	{
		"name": "date",
		"control": "date",
		"label": "Date",
		"required": false,
		"help": "Date for the reading in YYYY-MM-DD format"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
