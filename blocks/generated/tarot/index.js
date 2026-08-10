import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'question',
		control: 'text',
		label: __( 'Your question', 'roxyapi' ),
		required: false,
		help: __( 'Optional. The cards reflect on whatever you bring to them.', 'roxyapi' ),
		placeholder: __( 'What should I focus on this week', 'roxyapi' ),
	},
	{
		name: 'spread',
		control: 'select',
		label: __( 'Spread', 'roxyapi' ),
		required: false,
		help: __( 'Daily is one card. Three is past, present, future. Celtic is a ten-card layout.', 'roxyapi' ),
		options: [ 'daily', 'three', 'celtic' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name, {
		instructions: __( 'Choose the options in the sidebar to preview this reading here.', 'roxyapi' ),
	} ),
	save: () => null,
} );
