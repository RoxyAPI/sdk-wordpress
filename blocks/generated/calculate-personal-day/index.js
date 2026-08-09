import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'month',
		control: 'number',
		label: __( 'Month', 'roxyapi' ),
		required: true,
		help: __( 'Birth month (1-12)', 'roxyapi' ),
	},
	{
		name: 'day',
		control: 'number',
		label: __( 'Day', 'roxyapi' ),
		required: true,
		help: __( 'Birth day (1-31)', 'roxyapi' ),
	},
	{
		name: 'targetDate',
		control: 'date',
		label: __( 'Target date', 'roxyapi' ),
		required: false,
		help: __( 'Target date in YYYY-MM-DD format', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
