import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'birth_date',
		control: 'date',
		label: __( 'Birth date', 'roxyapi' ),
		required: true,
		help: __( 'Year, month, and day of birth', 'roxyapi' ),
	},
	{
		name: 'birth_time',
		control: 'time',
		label: __( 'Birth time', 'roxyapi' ),
		required: true,
		help: __( 'Local time of birth in 24-hour format', 'roxyapi' ),
	},
	{
		name: 'lat',
		control: 'number',
		label: __( 'Latitude', 'roxyapi' ),
		required: true,
		help: __( 'Decimal degrees, negative for south', 'roxyapi' ),
	},
	{
		name: 'lon',
		control: 'number',
		label: __( 'Longitude', 'roxyapi' ),
		required: true,
		help: __( 'Decimal degrees, negative for west', 'roxyapi' ),
	},
	{
		name: 'tz',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: false,
		help: __( 'IANA name like America/New_York', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name, {
		instructions: __( 'Fill these in to publish a fixed reading, or leave them blank to publish a form your visitors fill in.', 'roxyapi' ),
	} ),
	save: () => null,
} );
