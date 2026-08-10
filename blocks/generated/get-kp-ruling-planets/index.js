import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'latitude',
		control: 'number',
		label: __( 'Latitude', 'roxyapi' ),
		required: true,
		help: __( 'Observer latitude in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'longitude',
		control: 'number',
		label: __( 'Longitude', 'roxyapi' ),
		required: true,
		help: __( 'Observer longitude in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: false,
		help: __( 'Timezone: IANA name (e.g. "America/New_York", "Europe/London") OR decimal hours from UTC', 'roxyapi' ),
	},
	{
		name: 'datetime',
		control: 'text',
		label: __( 'Datetime', 'roxyapi' ),
		required: false,
		help: __( 'ISO 8601 datetime (YYYY-MM-DDTHH:MM:SS) for ruling planets', 'roxyapi' ),
	},
	{
		name: 'birthDate',
		control: 'date',
		label: __( 'Birth date', 'roxyapi' ),
		required: false,
		help: __( 'Birth date (YYYY-MM-DD) to calculate significators', 'roxyapi' ),
	},
	{
		name: 'birthTime',
		control: 'time',
		label: __( 'Birth time', 'roxyapi' ),
		required: false,
		help: __( 'Birth time (HH:MM:SS) for significator calculation', 'roxyapi' ),
	},
	{
		name: 'nodeType',
		control: 'select',
		label: __( 'Node type', 'roxyapi' ),
		required: false,
		help: __( 'Lunar node convention', 'roxyapi' ),
		options: [ 'mean', 'true' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
