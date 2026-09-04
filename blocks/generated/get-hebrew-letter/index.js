import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'id',
		control: 'text',
		label: __( 'Id', 'roxyapi' ),
		required: true,
	},
	{
		name: 'lang',
		control: 'select',
		label: __( 'Lang', 'roxyapi' ),
		required: false,
		help: __( 'Response language (BCP 47)', 'roxyapi' ),
		options: [ 'en', 'tr', 'de', 'es', 'hi', 'pt', 'fr', 'ru', 'zh-Hans', 'zh-Hant' ],
	},
	{
		name: 'letterAttribution',
		control: 'select',
		label: __( 'Letter attribution', 'roxyapi' ),
		required: false,
		help: __( 'Which reading assigns the element, planet or sign to each letter', 'roxyapi' ),
		options: [ 'sefer-yetzirah-gra', 'sefer-yetzirah-short', 'sefer-yetzirah-saadia', 'golden-dawn' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
