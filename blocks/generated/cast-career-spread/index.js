import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "question",
		"control": "text",
		"label": "Question",
		"required": false,
		"help": "Optional querent question to focus the career spread"
	},
	{
		"name": "seed",
		"control": "text",
		"label": "Seed",
		"required": false,
		"help": "Optional seed for reproducible results"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
