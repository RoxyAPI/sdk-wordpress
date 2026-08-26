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
		help: __( 'Birth date in YYYY-MM-DD format', 'roxyapi' ),
	},
	{
		name: 'yearBoundary',
		control: 'select',
		label: __( 'Year boundary', 'roxyapi' ),
		required: false,
		help: __( 'Which instant starts the zodiac year', 'roxyapi' ),
		options: [ 'lunar-new-year', 'li-chun' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
