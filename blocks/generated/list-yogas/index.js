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
		name: 'family',
		control: 'select',
		label: __( 'Family', 'roxyapi' ),
		required: false,
		help: __( 'Filter the catalog to one Nabhasa family: asraya (3), dala (2), akriti (20) or sankhya (7)', 'roxyapi' ),
		options: [ 'classical', 'asraya', 'dala', 'akriti', 'sankhya' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
