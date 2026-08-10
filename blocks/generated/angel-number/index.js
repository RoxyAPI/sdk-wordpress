import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'number',
		control: 'number',
		label: __( 'Number sequence', 'roxyapi' ),
		required: true,
		help: __( 'Any whole number you keep noticing', 'roxyapi' ),
		placeholder: __( '1111', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name, {
		instructions: __( 'Fill these in to publish a fixed reading, or leave them blank to publish a form your visitors fill in.', 'roxyapi' ),
	} ),
	save: () => null,
} );
