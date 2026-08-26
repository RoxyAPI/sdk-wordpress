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
	{
		name: 'gender',
		control: 'select',
		label: __( 'Gender', 'roxyapi' ),
		required: true,
		help: __( 'Subject sex, used only to pick the luck-pillar direction: a male born in a yang-stem year and a female born in a yin-stem year run forward through the sexagenary cycle, and the other two combinations run backward', 'roxyapi' ),
		options: [ 'male', 'female' ],
	},
	{
		name: 'count',
		control: 'number',
		label: __( 'Count', 'roxyapi' ),
		required: false,
		help: __( 'How many ten-year luck pillars to return, 1 to 12', 'roxyapi' ),
	},
	{
		name: 'annualFromYear',
		control: 'number',
		label: __( 'Annual from year', 'roxyapi' ),
		required: false,
		help: __( 'First Gregorian year of the annual pillar overlay', 'roxyapi' ),
	},
	{
		name: 'annualYears',
		control: 'number',
		label: __( 'Annual years', 'roxyapi' ),
		required: false,
		help: __( 'How many consecutive years the annual overlay covers, 1 to 20', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
