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
		name: 'startDate',
		control: 'date',
		label: __( 'Start date', 'roxyapi' ),
		required: false,
		help: __( 'Start date in YYYY-MM-DD format', 'roxyapi' ),
	},
	{
		name: 'count',
		control: 'number',
		label: __( 'Count', 'roxyapi' ),
		required: false,
		help: __( 'Number of upcoming moon phase transitions to return (1-20)', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
