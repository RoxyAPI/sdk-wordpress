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
		help: __( 'Response language (ISO 639-1)', 'roxyapi' ),
		options: [ 'en', 'tr', 'de', 'es', 'hi', 'pt', 'fr', 'ru' ],
	},
	{
		name: 'limit',
		control: 'number',
		label: __( 'Limit', 'roxyapi' ),
		required: false,
		help: __( 'Maximum items to return per page', 'roxyapi' ),
	},
	{
		name: 'offset',
		control: 'text',
		label: __( 'Offset', 'roxyapi' ),
		required: false,
		help: __( 'Number of items to skip for pagination', 'roxyapi' ),
	},
	{
		name: 'type',
		control: 'select',
		label: __( 'Type', 'roxyapi' ),
		required: false,
		help: __( 'Filter results by angel number pattern type', 'roxyapi' ),
		options: [ 'repeating', 'sequential', 'mirror', 'master', 'root', 'compound' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
