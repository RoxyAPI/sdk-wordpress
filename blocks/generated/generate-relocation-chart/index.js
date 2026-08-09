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
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: true,
		help: __( 'Birth timezone: decimal hours from UTC (e.g. -5 for EST, 5.5 for IST) OR IANA name (e.g. "America/New_York")', 'roxyapi' ),
	},
	{
		name: 'birthLatitude',
		control: 'number',
		label: __( 'Birth latitude', 'roxyapi' ),
		required: true,
		help: __( 'Birthplace latitude in decimal degrees (-90 to 90)', 'roxyapi' ),
	},
	{
		name: 'birthLongitude',
		control: 'number',
		label: __( 'Birth longitude', 'roxyapi' ),
		required: true,
		help: __( 'Birthplace longitude in decimal degrees (-180 to 180)', 'roxyapi' ),
	},
	{
		name: 'relocationLatitude',
		control: 'number',
		label: __( 'Relocation latitude', 'roxyapi' ),
		required: true,
		help: __( 'New location latitude in decimal degrees (-90 to 90)', 'roxyapi' ),
	},
	{
		name: 'relocationLongitude',
		control: 'number',
		label: __( 'Relocation longitude', 'roxyapi' ),
		required: true,
		help: __( 'New location longitude in decimal degrees (-180 to 180)', 'roxyapi' ),
	},
	{
		name: 'houseSystem',
		control: 'select',
		label: __( 'House system', 'roxyapi' ),
		required: false,
		help: __( 'House system for dividing the relocated chart into 12 houses', 'roxyapi' ),
		options: [ 'placidus', 'whole-sign', 'equal', 'koch' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
