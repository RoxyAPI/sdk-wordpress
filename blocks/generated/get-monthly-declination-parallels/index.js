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
		help: __( 'Year for the declination calendar (1900-2100)', 'roxyapi' ),
	},
	{
		name: 'month',
		control: 'number',
		label: __( 'Month', 'roxyapi' ),
		required: false,
		help: __( 'Month number (1-12)', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: false,
		help: __( 'Timezone offset from UTC in hours', 'roxyapi' ),
	},
	{
		name: 'orb',
		control: 'number',
		label: __( 'Orb', 'roxyapi' ),
		required: false,
		help: __( 'How far from exact still counts, in degrees', 'roxyapi' ),
	},
	{
		name: 'nodeType',
		control: 'select',
		label: __( 'Node type', 'roxyapi' ),
		required: false,
		help: __( 'Lunar node convention', 'roxyapi' ),
		options: [ 'mean', 'true' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
