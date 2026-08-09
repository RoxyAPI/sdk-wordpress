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
		help: __( 'Original birth date in YYYY-MM-DD format', 'roxyapi' ),
	},
	{
		name: 'birthTime',
		control: 'time',
		label: __( 'Birth time', 'roxyapi' ),
		required: true,
		help: __( 'Original birth time in 24-hour HH:MM:SS format', 'roxyapi' ),
	},
	{
		name: 'returnDate',
		control: 'date',
		label: __( 'Return date', 'roxyapi' ),
		required: true,
		help: __( 'Approximate date near the desired lunar return (YYYY-MM-DD)', 'roxyapi' ),
	},
	{
		name: 'latitude',
		control: 'number',
		label: __( 'Latitude', 'roxyapi' ),
		required: true,
		help: __( 'Latitude of the lunar return location in decimal degrees (-90 to 90)', 'roxyapi' ),
	},
	{
		name: 'longitude',
		control: 'number',
		label: __( 'Longitude', 'roxyapi' ),
		required: true,
		help: __( 'Longitude of the lunar return location in decimal degrees (-180 to 180)', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: true,
		help: __( 'Decimal hours from UTC OR IANA name (e.g. "America/New_York")', 'roxyapi' ),
	},
	{
		name: 'houseSystem',
		control: 'select',
		label: __( 'House system', 'roxyapi' ),
		required: false,
		help: __( 'House system for the lunar return chart', 'roxyapi' ),
		options: [ 'placidus', 'whole-sign', 'equal', 'koch' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
