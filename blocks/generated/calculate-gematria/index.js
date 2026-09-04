import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'text',
		control: 'text',
		label: __( 'Text', 'roxyapi' ),
		required: false,
		help: __( 'Latin text to write in Hebrew and then score, up to 200 characters', 'roxyapi' ),
	},
	{
		name: 'textHebrew',
		control: 'text',
		label: __( 'Text hebrew', 'roxyapi' ),
		required: false,
		help: __( 'Hebrew text to score, up to 200 characters', 'roxyapi' ),
	},
	{
		name: 'transliteration',
		control: 'select',
		label: __( 'Transliteration', 'roxyapi' ),
		required: false,
		help: __( 'How a Latin name is written in Hebrew before it is scored', 'roxyapi' ),
		options: [ 'letter-map-mathers' ],
	},
	{
		name: 'ciphers',
		control: 'text',
		label: __( 'Ciphers', 'roxyapi' ),
		required: false,
		help: __( 'Which ciphers to return, by identifier', 'roxyapi' ),
	},
	{
		name: 'misparGadol',
		control: 'select',
		label: __( 'Mispar gadol', 'roxyapi' ),
		required: false,
		help: __( 'Which method the name mispar gadol means, because the sources use it for two', 'roxyapi' ),
		options: [ 'finals-500-900', 'milui' ],
	},
	{
		name: 'atbashOutput',
		control: 'select',
		label: __( 'Atbash output', 'roxyapi' ),
		required: false,
		help: __( 'What AtBash and Albam return: the substituted Hebrew string, its standard value, or both', 'roxyapi' ),
		options: [ 'both', 'string', 'value' ],
	},
	{
		name: 'includeMatches',
		control: 'toggle',
		label: __( 'Include matches', 'roxyapi' ),
		required: false,
		help: __( 'Whether to return the curated equal value entries for the chosen spelling', 'roxyapi' ),
	},
	{
		name: 'latinCiphers',
		control: 'toggle',
		label: __( 'Latin ciphers', 'roxyapi' ),
		required: false,
		help: __( 'Whether to also score the Latin text with the three Latin alphabet ciphers', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
