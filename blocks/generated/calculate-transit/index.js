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
		help: __( 'Birth date in YYYY-MM-DD format', 'roxyapi' ),
	},
	{
		name: 'birthTime',
		control: 'time',
		label: __( 'Birth time', 'roxyapi' ),
		required: true,
		help: __( 'Birth time in HH:MM:SS format (24-hour)', 'roxyapi' ),
	},
	{
		name: 'transitDate',
		control: 'date',
		label: __( 'Transit date', 'roxyapi' ),
		required: true,
		help: __( 'Transit date to analyze in YYYY-MM-DD format', 'roxyapi' ),
	},
	{
		name: 'transitTime',
		control: 'time',
		label: __( 'Transit time', 'roxyapi' ),
		required: false,
		help: __( 'Transit time in HH:MM:SS format (24-hour)', 'roxyapi' ),
	},
	{
		name: 'latitude',
		control: 'number',
		label: __( 'Latitude', 'roxyapi' ),
		required: true,
		help: __( 'Observer latitude in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'longitude',
		control: 'number',
		label: __( 'Longitude', 'roxyapi' ),
		required: true,
		help: __( 'Observer longitude in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: false,
		help: __( 'Timezone offset from UTC in hours', 'roxyapi' ),
	},
	{
		name: 'coordinateSystem',
		control: 'select',
		label: __( 'Coordinate system', 'roxyapi' ),
		required: false,
		help: __( 'Coordinate system for longitude output', 'roxyapi' ),
		options: [ 'sidereal', 'tropical' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
