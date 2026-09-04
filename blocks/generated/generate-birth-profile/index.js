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
		help: __( 'Birth date in YYYY-MM-DD, proleptic Gregorian', 'roxyapi' ),
	},
	{
		name: 'time',
		control: 'time',
		label: __( 'Time', 'roxyapi' ),
		required: false,
		help: __( 'Birth time in HH:MM:SS local to the timezone field', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: true,
		help: __( 'IANA name (e.g. "America/New_York", "Europe/London", "UTC"), decimal hours (e.g. -5 for EST, 1 for CET), or a fixed UTC offset (e.g. "-05:00", "+01:00")', 'roxyapi' ),
	},
	{
		name: 'angelDating',
		control: 'select',
		label: __( 'Angel dating', 'roxyapi' ),
		required: false,
		help: __( 'How the name of the day is found', 'roxyapi' ),
		options: [ 'solar-longitude', 'lenain-blocks' ],
	},
	{
		name: 'yearStart',
		control: 'select',
		label: __( 'Year start', 'roxyapi' ),
		required: false,
		help: __( 'Which date the civil wheel of five day periods opens on', 'roxyapi' ),
		options: [ 'march-21', 'march-20' ],
	},
	{
		name: 'leapDayPolicy',
		control: 'select',
		label: __( 'Leap day policy', 'roxyapi' ),
		required: false,
		help: __( 'Where 29 February falls', 'roxyapi' ),
		options: [ 'extend-previous', 'next-angel' ],
	},
	{
		name: 'afterSunset',
		control: 'toggle',
		label: __( 'After sunset', 'roxyapi' ),
		required: false,
		help: __( 'Set true when the moment falls after nightfall, which advances the Hebrew date by one day because the Hebrew day begins in the evening', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
