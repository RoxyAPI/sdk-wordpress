import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'fullName',
		control: 'text',
		label: __( 'Full name', 'roxyapi' ),
		required: true,
		help: __( 'Full legal birth name as it appears on the birth certificate', 'roxyapi' ),
	},
	{
		name: 'year',
		control: 'number',
		label: __( 'Year', 'roxyapi' ),
		required: true,
		help: __( 'Birth year between 100 and 2100', 'roxyapi' ),
	},
	{
		name: 'month',
		control: 'number',
		label: __( 'Month', 'roxyapi' ),
		required: true,
		help: __( 'Birth month (1 to 12)', 'roxyapi' ),
	},
	{
		name: 'day',
		control: 'number',
		label: __( 'Day', 'roxyapi' ),
		required: true,
		help: __( 'Birth day (1 to 31)', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
