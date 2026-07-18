import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		"name": "name",
		"control": "text",
		"label": "Name",
		"required": true,
		"help": "The name to analyze"
	}
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
