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
		help: __( 'Date in YYYY-MM-DD format, in the PROLEPTIC GREGORIAN calendar, extended backwards unchanged through the 1582 reform', 'roxyapi' ),
	},
	{
		name: 'correlation',
		control: 'select',
		label: __( 'Correlation', 'roxyapi' ),
		required: false,
		help: __( 'Which correlation constant ties the day count to a civil date', 'roxyapi' ),
		options: [ 'gmt-584283', 'martinez-hernandez-584281', 'astronomical-584285', 'martin-skidmore-584286' ],
	},
	{
		name: 'yearBearerSystem',
		control: 'select',
		label: __( 'Year bearer system', 'roxyapi' ),
		required: false,
		help: __( 'Which Haab day is read as the start of the year when naming its Year Bearer', 'roxyapi' ),
		options: [ 'classic', 'campeche', 'colonial-yucatec' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
