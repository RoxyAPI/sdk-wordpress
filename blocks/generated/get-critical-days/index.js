import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'birthDate',
		control: 'date',
		label: __( 'Birth date', 'roxyapi' ),
		required: true,
		help: __( 'Birth date of the person in YYYY-MM-DD format.', 'roxyapi' ),
	},
	{
		name: 'startDate',
		control: 'date',
		label: __( 'Start date', 'roxyapi' ),
		required: false,
		help: __( 'Start date of the search range in YYYY-MM-DD format', 'roxyapi' ),
	},
	{
		name: 'endDate',
		control: 'date',
		label: __( 'End date', 'roxyapi' ),
		required: false,
		help: __( 'End date of the search range in YYYY-MM-DD format', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
