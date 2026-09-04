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
		help: __( 'Proleptic Gregorian date to convert INTO a Long Count', 'roxyapi' ),
	},
	{
		name: 'longCount',
		control: 'text',
		label: __( 'Long count', 'roxyapi' ),
		required: false,
		help: __( 'Dotted Long Count to convert INTO a date, written baktun.katun.tun.winal.kin', 'roxyapi' ),
	},
	{
		name: 'correlation',
		control: 'select',
		label: __( 'Correlation', 'roxyapi' ),
		required: false,
		help: __( 'Which correlation constant ties the day count to a civil date', 'roxyapi' ),
		options: [ 'gmt-584283', 'martinez-hernandez-584281', 'astronomical-584285', 'martin-skidmore-584286' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
