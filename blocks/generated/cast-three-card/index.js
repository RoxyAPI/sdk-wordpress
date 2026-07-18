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
		"help": "Optional specific question to focus the reading"
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
