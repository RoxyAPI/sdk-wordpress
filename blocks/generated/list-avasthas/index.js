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
		name: 'system',
		control: 'select',
		label: __( 'System', 'roxyapi' ),
		required: false,
		help: __( 'Return only the states of one system: "baladi" (5), "jagradadi" (3) or "deeptadi" (9)', 'roxyapi' ),
		options: [ 'baladi', 'jagradadi', 'deeptadi' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
