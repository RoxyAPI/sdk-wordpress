import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'report',
		control: 'select',
		label: __( 'Report', 'roxyapi' ),
		required: false,
		options: [ 'natal', 'synastry', 'kundli', 'matchmaking', 'yearly-forecast', 'human-design', 'bazi', 'numerology', 'tarot' ],
		optionLabels: [ __( 'Natal Chart Report', 'roxyapi' ), __( 'Synastry Report', 'roxyapi' ), __( 'Kundli Report', 'roxyapi' ), __( 'Kundli Matching Report', 'roxyapi' ), __( 'Yearly Forecast Report', 'roxyapi' ), __( 'Human Design Report', 'roxyapi' ), __( 'BaZi Report', 'roxyapi' ), __( 'Numerology Report', 'roxyapi' ), __( 'Tarot Reading Report', 'roxyapi' ) ],
	},
	{
		name: 'format',
		control: 'select',
		label: __( 'Delivery', 'roxyapi' ),
		required: false,
		options: [ 'pdf', 'html' ],
		optionLabels: [ __( 'PDF download', 'roxyapi' ), __( 'Page in the browser', 'roxyapi' ) ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name, {
		instructions: __( 'Choose the report. Your visitors fill in their own details and receive it under your brand.', 'roxyapi' ),
	} ),
	save: () => null,
} );
