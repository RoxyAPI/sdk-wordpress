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
		name: 'limit',
		control: 'number',
		label: __( 'Limit', 'roxyapi' ),
		required: false,
		help: __( 'Maximum sectors to return per page', 'roxyapi' ),
	},
	{
		name: 'offset',
		control: 'text',
		label: __( 'Offset', 'roxyapi' ),
		required: false,
		help: __( 'Number of sectors to skip for pagination', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
