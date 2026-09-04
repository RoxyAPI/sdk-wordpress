import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'date',
		control: 'date',
		label: __( 'Date', 'roxyapi' ),
		required: true,
		help: __( 'The date to resolve, in YYYY-MM-DD format', 'roxyapi' ),
	},
	{
		name: 'ritucharyaScheme',
		control: 'select',
		label: __( 'Ritucharya scheme', 'roxyapi' ),
		required: false,
		help: __( 'Which six-season division the year is cut into', 'roxyapi' ),
		options: [ 'sutrasthana-6', 'vimana-8' ],
	},
	{
		name: 'rituZodiac',
		control: 'select',
		label: __( 'Ritu zodiac', 'roxyapi' ),
		required: false,
		help: __( 'Which zodiac the solar-month boundaries are measured in', 'roxyapi' ),
		options: [ 'sayana', 'nirayana' ],
	},
	{
		name: 'hemisphere',
		control: 'select',
		label: __( 'Hemisphere', 'roxyapi' ),
		required: false,
		help: __( 'Which half of the world the season names are stated for', 'roxyapi' ),
		options: [ 'northern', 'southern' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
