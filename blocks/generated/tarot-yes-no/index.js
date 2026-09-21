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
		help: __( 'Optional. The card answers whatever you bring to it.', 'roxyapi' ),
		placeholder: __( 'Should I take the new job', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name, {
		instructions: __( 'Choose the options in the sidebar to preview this reading here.', 'roxyapi' ),
	} ),
	save: () => null,
} );
