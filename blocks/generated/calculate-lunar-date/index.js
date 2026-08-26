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
		required: false,
		help: __( 'Gregorian date to convert to the lunisolar calendar', 'roxyapi' ),
	},
	{
		name: 'lunarYear',
		control: 'number',
		label: __( 'Lunar year', 'roxyapi' ),
		required: false,
		help: __( 'Lunisolar year to convert back to a Gregorian date', 'roxyapi' ),
	},
	{
		name: 'lunarMonth',
		control: 'number',
		label: __( 'Lunar month', 'roxyapi' ),
		required: false,
		help: __( 'Lunar month, 1 to 12', 'roxyapi' ),
	},
	{
		name: 'lunarDay',
		control: 'number',
		label: __( 'Lunar day', 'roxyapi' ),
		required: false,
		help: __( 'Day of the lunar month, 1 to 30', 'roxyapi' ),
	},
	{
		name: 'isLeapMonth',
		control: 'toggle',
		label: __( 'Is leap month', 'roxyapi' ),
		required: false,
		help: __( 'Set true to address the leap repetition of lunarMonth rather than the first pass', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
