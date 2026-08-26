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
		name: 'arcana',
		control: 'select',
		label: __( 'Arcana', 'roxyapi' ),
		required: false,
		help: __( 'Filter by arcana type', 'roxyapi' ),
		options: [ 'major', 'minor' ],
	},
	{
		name: 'suit',
		control: 'select',
		label: __( 'Suit', 'roxyapi' ),
		required: false,
		help: __( 'Filter minor arcana by suit', 'roxyapi' ),
		options: [ 'cups', 'wands', 'swords', 'pentacles' ],
	},
	{
		name: 'number',
		control: 'text',
		label: __( 'Number', 'roxyapi' ),
		required: false,
		help: __( 'Filter by card number', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
