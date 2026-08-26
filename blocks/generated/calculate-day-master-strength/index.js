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
		help: __( 'IANA name (e.g. "America/New_York", "Europe/London", "UTC"), decimal hours (e.g. -5 for EST, 1 for CET), or a fixed UTC offset (e.g. "-05:00", "+01:00")', 'roxyapi' ),
	},
	{
		name: 'latitude',
		control: 'number',
		label: __( 'Latitude', 'roxyapi' ),
		required: false,
		help: __( 'Birth latitude in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'longitude',
		control: 'number',
		label: __( 'Longitude', 'roxyapi' ),
		required: false,
		help: __( 'Birth longitude in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'dayBoundary',
		control: 'select',
		label: __( 'Day boundary', 'roxyapi' ),
		required: false,
		help: __( 'Which instant starts the sexagenary DAY, which only matters for a birth between 23:00 and 23:59', 'roxyapi' ),
		options: [ 'split-zi', 'midnight', 'early-zi' ],
	},
	{
		name: 'yearBoundary',
		control: 'select',
		label: __( 'Year boundary', 'roxyapi' ),
		required: false,
		help: __( 'Which instant starts the sexagenary YEAR', 'roxyapi' ),
		options: [ 'li-chun', 'lunar-new-year' ],
	},
	{
		name: 'hourClock',
		control: 'select',
		label: __( 'Hour clock', 'roxyapi' ),
		required: false,
		help: __( 'Which clock the HOUR branch is read from', 'roxyapi' ),
		options: [ 'clock', 'local-mean', 'solar' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
