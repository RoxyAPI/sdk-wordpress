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
		name: 'chakra',
		control: 'select',
		label: __( 'Chakra', 'roxyapi' ),
		required: false,
		help: __( 'Filter by chakra association, case-insensitive', 'roxyapi' ),
		options: [ 'Root', 'Sacral', 'Solar Plexus', 'Heart', 'Throat', 'Third Eye', 'Crown' ],
	},
	{
		name: 'zodiac',
		control: 'select',
		label: __( 'Zodiac', 'roxyapi' ),
		required: false,
		help: __( 'Filter by zodiac sign, case-insensitive', 'roxyapi' ),
		options: [ 'aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces' ],
	},
	{
		name: 'element',
		control: 'select',
		label: __( 'Element', 'roxyapi' ),
		required: false,
		help: __( 'Filter by elemental association, case-insensitive', 'roxyapi' ),
		options: [ 'Earth', 'Water', 'Fire', 'Air', 'Storm' ],
	},
	{
		name: 'color',
		control: 'text',
		label: __( 'Color', 'roxyapi' ),
		required: false,
		help: __( 'Filter by crystal color (partial match, case-insensitive)', 'roxyapi' ),
	},
	{
		name: 'planet',
		control: 'text',
		label: __( 'Planet', 'roxyapi' ),
		required: false,
		help: __( 'Filter by planetary association (partial match, case-insensitive)', 'roxyapi' ),
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
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
