import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'q',
		control: 'text',
		label: __( 'Q', 'roxyapi' ),
		required: false,
		help: __( 'Search query to match against symbol names and meanings', 'roxyapi' ),
	},
	{
		name: 'letter',
		control: 'text',
		label: __( 'Letter', 'roxyapi' ),
		required: false,
		help: __( 'Filter symbols by starting letter (a-z)', 'roxyapi' ),
	},
	{
		name: 'limit',
		control: 'number',
		label: __( 'Limit', 'roxyapi' ),
		required: false,
		help: __( 'Maximum items to return per page', 'roxyapi' ),
	},
	{
		name: 'offset',
		control: 'text',
		label: __( 'Offset', 'roxyapi' ),
		required: false,
		help: __( 'Number of items to skip for pagination', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
