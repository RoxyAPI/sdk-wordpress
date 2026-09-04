import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'name',
		control: 'text',
		label: __( 'Name', 'roxyapi' ),
		required: false,
		help: __( 'The name in Latin script, to be written in Hebrew and then scored', 'roxyapi' ),
	},
	{
		name: 'nameHebrew',
		control: 'text',
		label: __( 'Name hebrew', 'roxyapi' ),
		required: false,
		help: __( 'The name already in Hebrew, which skips the transliteration step entirely and scores exactly the spelling you sent.', 'roxyapi' ),
	},
	{
		name: 'transliteration',
		control: 'select',
		label: __( 'Transliteration', 'roxyapi' ),
		required: false,
		help: __( 'How a Latin name is written in Hebrew before it is scored', 'roxyapi' ),
		options: [ 'letter-map-mathers' ],
	},
	{
		name: 'misparGadol',
		control: 'select',
		label: __( 'Mispar gadol', 'roxyapi' ),
		required: false,
		help: __( 'Which method the name mispar gadol means, because the sources use it for two', 'roxyapi' ),
		options: [ 'finals-500-900', 'milui' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
