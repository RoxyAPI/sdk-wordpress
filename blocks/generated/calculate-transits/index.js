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
		required: false,
		help: __( 'Transit date in YYYY-MM-DD format (defaults to current date)', 'roxyapi' ),
	},
	{
		name: 'time',
		control: 'time',
		label: __( 'Time', 'roxyapi' ),
		required: false,
		help: __( 'Transit time in HH:MM:SS format (defaults to current time)', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: false,
		help: __( 'Transit timezone: decimal hours from UTC OR IANA name (e.g. "America/New_York")', 'roxyapi' ),
	},
	{
		name: 'natalChart',
		control: 'text',
		label: __( 'Natal chart', 'roxyapi' ),
		required: false,
		help: __( 'Optional natal chart data to compare transits against', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
