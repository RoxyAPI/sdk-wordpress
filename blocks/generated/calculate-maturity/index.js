import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'lifePath',
		control: 'number',
		label: __( 'Life path', 'roxyapi' ),
		required: false,
		help: __( 'Your Life Path number (1-9, 11, 22, 33)', 'roxyapi' ),
	},
	{
		name: 'expression',
		control: 'number',
		label: __( 'Expression', 'roxyapi' ),
		required: false,
		help: __( 'Your Expression number (1-9, 11, 22, 33)', 'roxyapi' ),
	},
	{
		name: 'fullName',
		control: 'text',
		label: __( 'Full name', 'roxyapi' ),
		required: false,
		help: __( 'Full birth name to calculate Expression number automatically', 'roxyapi' ),
	},
	{
		name: 'year',
		control: 'number',
		label: __( 'Year', 'roxyapi' ),
		required: false,
		help: __( 'Birth year to calculate Life Path automatically', 'roxyapi' ),
	},
	{
		name: 'month',
		control: 'number',
		label: __( 'Month', 'roxyapi' ),
		required: false,
		help: __( 'Birth month (1-12)', 'roxyapi' ),
	},
	{
		name: 'day',
		control: 'number',
		label: __( 'Day', 'roxyapi' ),
		required: false,
		help: __( 'Birth day (1-31)', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
