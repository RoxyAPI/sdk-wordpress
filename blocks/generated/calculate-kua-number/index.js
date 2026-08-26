import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'date',
		control: 'date',
		label: __( 'Date', 'roxyapi' ),
		required: true,
		help: __( 'Birth date in YYYY-MM-DD format', 'roxyapi' ),
	},
	{
		name: 'gender',
		control: 'select',
		label: __( 'Gender', 'roxyapi' ),
		required: true,
		help: __( 'Selects the Kua formula variant', 'roxyapi' ),
		options: [ 'male', 'female' ],
	},
	{
		name: 'yearBoundary',
		control: 'select',
		label: __( 'Year boundary', 'roxyapi' ),
		required: false,
		help: __( 'Which boundary starts the Chinese year', 'roxyapi' ),
		options: [ 'li-chun', 'lunar-new-year' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
