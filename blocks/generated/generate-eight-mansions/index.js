import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'kua',
		control: 'number',
		label: __( 'Kua', 'roxyapi' ),
		required: false,
		help: __( 'Kua number to build the map for, if you already have one', 'roxyapi' ),
	},
	{
		name: 'date',
		control: 'date',
		label: __( 'Date', 'roxyapi' ),
		required: false,
		help: __( 'Birth date in YYYY-MM-DD format, used to derive the Kua when no kua is sent', 'roxyapi' ),
	},
	{
		name: 'gender',
		control: 'select',
		label: __( 'Gender', 'roxyapi' ),
		required: false,
		help: __( 'Selects the Kua formula variant', 'roxyapi' ),
		options: [ 'male', 'female' ],
	},
	{
		name: 'yearBoundary',
		control: 'select',
		label: __( 'Year boundary', 'roxyapi' ),
		required: false,
		help: __( 'Which boundary starts the Chinese year when the Kua is derived from a birth date', 'roxyapi' ),
		options: [ 'li-chun', 'lunar-new-year' ],
	},
	{
		name: 'facing',
		control: 'select',
		label: __( 'Facing', 'roxyapi' ),
		required: false,
		help: __( 'Optional compass sector the main door faces, one of North, Northeast, East, Southeast, South, Southwest, West, Northwest', 'roxyapi' ),
		options: [ 'North', 'Northeast', 'East', 'Southeast', 'South', 'Southwest', 'West', 'Northwest' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
