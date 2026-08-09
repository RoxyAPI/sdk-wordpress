import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'count',
		control: 'number',
		label: __( 'Count', 'roxyapi' ),
		required: true,
		help: __( 'Number of cards to draw (1-78)', 'roxyapi' ),
	},
	{
		name: 'seed',
		control: 'text',
		label: __( 'Seed', 'roxyapi' ),
		required: false,
		help: __( 'Optional seed for reproducible results', 'roxyapi' ),
	},
	{
		name: 'allowReversals',
		control: 'toggle',
		label: __( 'Allow reversals', 'roxyapi' ),
		required: false,
		help: __( 'Whether cards can appear reversed (upside down)', 'roxyapi' ),
	},
	{
		name: 'allowDuplicates',
		control: 'toggle',
		label: __( 'Allow duplicates', 'roxyapi' ),
		required: false,
		help: __( 'Whether same card can be drawn multiple times', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
