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
		help: __( 'Date in YYYY-MM-DD format', 'roxyapi' ),
	},
	{
		name: 'time',
		control: 'time',
		label: __( 'Time', 'roxyapi' ),
		required: true,
		help: __( 'Time in HH:MM:SS format (24-hour)', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: true,
		help: __( 'Timezone offset from UTC in decimal hours (NOT minutes format)', 'roxyapi' ),
	},
	{
		name: 'planets',
		control: 'text',
		label: __( 'Planets', 'roxyapi' ),
		required: false,
		help: __( 'Optional: specific bodies to calculate aspects for (defaults to all 14: the 10 classical planets, the lunar nodes, Chiron, and Black Moon Lilith)', 'roxyapi' ),
	},
	{
		name: 'aspectTypes',
		control: 'text',
		label: __( 'Aspect types', 'roxyapi' ),
		required: false,
		help: __( 'Optional: specific aspect types to find (defaults to all 9)', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
