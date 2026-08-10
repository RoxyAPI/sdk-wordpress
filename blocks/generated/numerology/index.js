import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'name',
		control: 'text',
		label: __( 'Full name at birth', 'roxyapi' ),
		required: true,
		help: __( 'First and last name as recorded at birth', 'roxyapi' ),
	},
	{
		name: 'birth_date',
		control: 'date',
		label: __( 'Birth date', 'roxyapi' ),
		required: true,
		help: __( 'Year, month, and day of birth', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name, {
		instructions: __( 'Fill these in to publish a fixed reading, or leave them blank to publish a form your visitors fill in.', 'roxyapi' ),
	} ),
	save: () => null,
} );
