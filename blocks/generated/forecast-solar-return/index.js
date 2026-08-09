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
		name: 'year',
		control: 'number',
		label: __( 'Year', 'roxyapi' ),
		required: true,
		help: __( 'Year to cast the solar return for', 'roxyapi' ),
	},
	{
		name: 'latitude',
		control: 'number',
		label: __( 'Latitude', 'roxyapi' ),
		required: true,
		help: __( 'Latitude of the solar return location in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'longitude',
		control: 'number',
		label: __( 'Longitude', 'roxyapi' ),
		required: true,
		help: __( 'Longitude of the solar return location in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: true,
		help: __( 'IANA name (e.g. "America/New_York", "Europe/London", "UTC"), decimal hours (e.g. -5 for EST, 1 for CET), or a fixed UTC offset (e.g. "-05:00", "+01:00")', 'roxyapi' ),
	},
	{
		name: 'houseSystem',
		control: 'select',
		label: __( 'House system', 'roxyapi' ),
		required: false,
		help: __( 'House system for the return chart', 'roxyapi' ),
		options: [ 'placidus', 'whole-sign', 'equal', 'koch' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
