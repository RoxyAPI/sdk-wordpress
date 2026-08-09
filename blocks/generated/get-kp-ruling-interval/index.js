import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'startDatetime',
		control: 'text',
		label: __( 'Start datetime', 'roxyapi' ),
		required: true,
		help: __( 'Start of the interval range in ISO 8601 (YYYY-MM-DDTHH:MM:SS)', 'roxyapi' ),
	},
	{
		name: 'endDatetime',
		control: 'text',
		label: __( 'End datetime', 'roxyapi' ),
		required: true,
		help: __( 'End of the interval range in ISO 8601 (YYYY-MM-DDTHH:MM:SS)', 'roxyapi' ),
	},
	{
		name: 'intervalMinutes',
		control: 'number',
		label: __( 'Interval minutes', 'roxyapi' ),
		required: true,
		help: __( 'Interval between calculations in minutes (1-1440)', 'roxyapi' ),
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
		help: __( 'Timezone offset from UTC in decimal hours', 'roxyapi' ),
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
		help: __( 'Lunar node type for Rahu and Ketu positions', 'roxyapi' ),
		options: [ 'mean', 'true' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
