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
		name: 'treeVariant',
		control: 'select',
		label: __( 'Tree variant', 'roxyapi' ),
		required: false,
		help: __( 'Which arrangement of the twenty two paths', 'roxyapi' ),
		options: [ 'kircher' ],
	},
	{
		name: 'sephirotSystem',
		control: 'select',
		label: __( 'Sephirot system', 'roxyapi' ),
		required: false,
		help: __( 'Which reading assigns a sphere to each sephirah', 'roxyapi' ),
		options: [ 'classical', 'golden-dawn' ],
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
