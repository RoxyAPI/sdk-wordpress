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
		name: 'number',
		control: 'text',
		label: __( 'Number', 'roxyapi' ),
		required: true,
		help: __( 'Number sequence to analyze (1-8 digits)', 'roxyapi' ),
	},
	{
		name: 'context',
		control: 'select',
		label: __( 'Context', 'roxyapi' ),
		required: false,
		help: __( 'Where the number was seen', 'roxyapi' ),
		options: [ 'clock', 'receipt', 'license-plate', 'phone', 'address', 'price' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
