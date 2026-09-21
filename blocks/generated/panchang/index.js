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
		help: __( 'Date for the panchang reading', 'roxyapi' ),
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
		help: __( 'IANA name like Asia/Kolkata', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name, {
		instructions: __( 'Fill these in to publish a fixed reading, or leave them blank to publish a form your visitors fill in.', 'roxyapi' ),
	} ),
	save: () => null,
} );
