import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'year',
		control: 'number',
		label: __( 'Year', 'roxyapi' ),
		required: false,
		help: __( 'Birth year (checks Life Path)', 'roxyapi' ),
	},
	{
		name: 'month',
		control: 'number',
		label: __( 'Month', 'roxyapi' ),
		required: false,
		help: __( 'Birth month (checks Life Path)', 'roxyapi' ),
	},
	{
		name: 'day',
		control: 'number',
		label: __( 'Day', 'roxyapi' ),
		required: false,
		help: __( 'Birth day (checks Life Path)', 'roxyapi' ),
	},
	{
		name: 'fullName',
		control: 'text',
		label: __( 'Full name', 'roxyapi' ),
		required: false,
		help: __( 'Full birth name (checks Expression, Soul Urge, Personality)', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
