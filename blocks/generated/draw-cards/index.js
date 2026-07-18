import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "count",
		"control": "number",
		"label": "Count",
		"required": true,
		"help": "Number of cards to draw (1-78)"
	},
	{
		"name": "seed",
		"control": "text",
		"label": "Seed",
		"required": false,
		"help": "Optional seed for reproducible results"
	},
	{
		"name": "allowReversals",
		"control": "toggle",
		"label": "Allow reversals",
		"required": false,
		"help": "Whether cards can appear reversed (upside down)"
	},
	{
		"name": "allowDuplicates",
		"control": "toggle",
		"label": "Allow duplicates",
		"required": false,
		"help": "Whether same card can be drawn multiple times"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
