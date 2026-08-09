import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'question',
		control: 'text',
		label: __( 'Question', 'roxyapi' ),
		required: false,
		help: __( 'Optional querent question to focus the career spread', 'roxyapi' ),
	},
	{
		name: 'seed',
		control: 'text',
		label: __( 'Seed', 'roxyapi' ),
		required: false,
		help: __( 'Optional seed for reproducible results', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
