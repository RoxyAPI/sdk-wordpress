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
		help: __( 'Ayanamsa system used to place the birth Moon in its nakshatra, which sets every dasha start and end date', 'roxyapi' ),
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
		name: 'significators',
		control: 'toggle',
		label: __( 'Significators', 'roxyapi' ),
		required: false,
		help: __( 'Set true to attach the KP significators of each period lord: its star lord, sub lord, occupied house, the houses it signifies at levels L1 to L4, and a strength grade', 'roxyapi' ),
	},
	{
		name: 'nodeType',
		control: 'select',
		label: __( 'Node type', 'roxyapi' ),
		required: false,
		help: __( 'Lunar node type for Rahu and Ketu, used ONLY when "significators" is true', 'roxyapi' ),
		options: [ 'mean', 'true' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
