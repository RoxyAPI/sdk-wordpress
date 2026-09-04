import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'startDate',
		control: 'date',
		label: __( 'Start date', 'roxyapi' ),
		required: true,
		help: __( 'First day of the search window, in YYYY-MM-DD', 'roxyapi' ),
	},
	{
		name: 'endDate',
		control: 'date',
		label: __( 'End date', 'roxyapi' ),
		required: true,
		help: __( 'Last day of the search window, in YYYY-MM-DD, inclusive', 'roxyapi' ),
	},
	{
		name: 'latitude',
		control: 'number',
		label: __( 'Latitude', 'roxyapi' ),
		required: true,
		help: __( 'Latitude of the house, in decimal degrees, positive north', 'roxyapi' ),
	},
	{
		name: 'longitude',
		control: 'number',
		label: __( 'Longitude', 'roxyapi' ),
		required: true,
		help: __( 'Longitude of the house, in decimal degrees, positive east.', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: true,
		help: __( 'IANA name (e.g. "America/New_York", "Europe/London", "UTC"), decimal hours (e.g. -5 for EST, 1 for CET), or a fixed UTC offset (e.g. "-05:00", "+01:00")', 'roxyapi' ),
	},
	{
		name: 'muhurtaText',
		control: 'select',
		label: __( 'Muhurta text', 'roxyapi' ),
		required: false,
		help: __( 'Which Muhurta text supplies the admissible nakshatras for entering a new house', 'roxyapi' ),
		options: [ 'muhurta-chintamani', 'kalaprakasika' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
