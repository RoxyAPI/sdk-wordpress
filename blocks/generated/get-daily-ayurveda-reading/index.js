import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'lang',
		control: 'select',
		label: __( 'Lang', 'roxyapi' ),
		required: false,
		help: __( 'Response language (BCP 47)', 'roxyapi' ),
		options: [ 'en', 'tr', 'de', 'es', 'hi', 'pt', 'fr', 'ru', 'zh-Hans', 'zh-Hant' ],
	},
	{
		name: 'date',
		control: 'date',
		label: __( 'Date', 'roxyapi' ),
		required: false,
		help: __( 'Reading date in YYYY-MM-DD format', 'roxyapi' ),
	},
	{
		name: 'latitude',
		control: 'text',
		label: __( 'Latitude', 'roxyapi' ),
		required: false,
		help: __( 'Latitude in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'longitude',
		control: 'text',
		label: __( 'Longitude', 'roxyapi' ),
		required: false,
		help: __( 'Longitude in decimal degrees', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: false,
		help: __( 'Selects which day counts as current when date is omitted, and which local day sunrise is computed for', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
