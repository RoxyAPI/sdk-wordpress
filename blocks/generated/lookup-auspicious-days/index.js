import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'activity',
		control: 'select',
		label: __( 'Activity', 'roxyapi' ),
		required: true,
		help: __( 'Activity to choose a date for', 'roxyapi' ),
		options: [ 'wedding', 'travel', 'moving-house', 'opening-business', 'signing-contracts', 'construction', 'groundbreaking', 'burial', 'medical-treatment', 'praying' ],
	},
	{
		name: 'startDate',
		control: 'date',
		label: __( 'Start date', 'roxyapi' ),
		required: true,
		help: __( 'First date of the range to search, inclusive.', 'roxyapi' ),
	},
	{
		name: 'endDate',
		control: 'date',
		label: __( 'End date', 'roxyapi' ),
		required: true,
		help: __( 'Last date of the range to search, inclusive', 'roxyapi' ),
	},
	{
		name: 'avoidAnimal',
		control: 'select',
		label: __( 'Avoid animal', 'roxyapi' ),
		required: false,
		help: __( 'Zodiac animal to protect', 'roxyapi' ),
		options: [ 'rat', 'ox', 'tiger', 'rabbit', 'dragon', 'snake', 'horse', 'goat', 'monkey', 'rooster', 'dog', 'pig' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
