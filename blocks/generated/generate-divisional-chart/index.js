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
		name: 'time',
		control: 'time',
		label: __( 'Time', 'roxyapi' ),
		required: true,
		help: __( 'Birth time in 24-hour HH:MM:SS format', 'roxyapi' ),
	},
	{
		name: 'latitude',
		control: 'number',
		label: __( 'Latitude', 'roxyapi' ),
		required: true,
		help: __( 'Birth location latitude in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'longitude',
		control: 'number',
		label: __( 'Longitude', 'roxyapi' ),
		required: true,
		help: __( 'Birth location longitude in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: false,
		help: __( 'Timezone: IANA name (e.g. "America/New_York", "Europe/London") OR decimal hours from UTC (e.g. -5 for EST, 1 for CET)', 'roxyapi' ),
	},
	{
		name: 'ayanamsa',
		control: 'select',
		label: __( 'Ayanamsa', 'roxyapi' ),
		required: false,
		help: __( 'Sidereal frame (ayanamsa) the chart is cast in', 'roxyapi' ),
		options: [ 'kp-newcomb', 'kp-old', 'lahiri', 'raman', 'custom' ],
	},
	{
		name: 'ayanamsaValue',
		control: 'number',
		label: __( 'Ayanamsa value', 'roxyapi' ),
		required: false,
		help: __( 'Custom ayanamsa value in degrees', 'roxyapi' ),
	},
	{
		name: 'division',
		control: 'number',
		label: __( 'Division', 'roxyapi' ),
		required: true,
		help: __( 'Divisional chart number', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
