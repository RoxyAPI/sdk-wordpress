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
		help: __( 'Birth date, YYYY-MM-DD', 'roxyapi' ),
	},
	{
		name: 'birthTime',
		control: 'time',
		label: __( 'Birth time', 'roxyapi' ),
		required: true,
		help: __( 'Birth time, HH:MM:SS, 24 hour, local to the birth place', 'roxyapi' ),
	},
	{
		name: 'latitude',
		control: 'number',
		label: __( 'Latitude', 'roxyapi' ),
		required: true,
		help: __( 'Birth latitude in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'longitude',
		control: 'number',
		label: __( 'Longitude', 'roxyapi' ),
		required: true,
		help: __( 'Birth longitude in decimal degrees, east positive.', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: false,
		help: __( 'Timezone as an IANA name (Asia/Kolkata) or decimal hours from UTC', 'roxyapi' ),
	},
	{
		name: 'date',
		control: 'date',
		label: __( 'Date', 'roxyapi' ),
		required: false,
		help: __( 'Civil date to read, YYYY-MM-DD in the request timezone', 'roxyapi' ),
	},
	{
		name: 'time',
		control: 'time',
		label: __( 'Time', 'roxyapi' ),
		required: false,
		help: __( 'Reading moment on that date, HH:MM:SS local', 'roxyapi' ),
	},
	{
		name: 'ayanamsa',
		control: 'select',
		label: __( 'Ayanamsa', 'roxyapi' ),
		required: false,
		help: __( 'Ayanamsa system for sidereal conversion', 'roxyapi' ),
		options: [ 'kp-newcomb', 'kp-old', 'lahiri', 'raman' ],
	},
	{
		name: 'nodeType',
		control: 'select',
		label: __( 'Node type', 'roxyapi' ),
		required: false,
		help: __( 'Lunar node convention', 'roxyapi' ),
		options: [ 'mean', 'true' ],
	},
	{
		name: 'gainHouses',
		control: 'text',
		label: __( 'Gain houses', 'roxyapi' ),
		required: false,
		help: __( 'Houses whose significators count as gain, 1 to 12', 'roxyapi' ),
	},
	{
		name: 'lossHouses',
		control: 'text',
		label: __( 'Loss houses', 'roxyapi' ),
		required: false,
		help: __( 'Houses whose significators count as loss, 1 to 12', 'roxyapi' ),
	},
	{
		name: 'weights',
		control: 'text',
		label: __( 'Weights', 'roxyapi' ),
		required: false,
		help: __( 'Layer weights in percent, all four required, summing to 100', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
