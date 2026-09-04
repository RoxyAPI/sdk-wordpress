import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'firstName',
		control: 'text',
		label: __( 'First name', 'roxyapi' ),
		required: false,
		help: __( 'First name in Latin script', 'roxyapi' ),
	},
	{
		name: 'firstNameHebrew',
		control: 'text',
		label: __( 'First name hebrew', 'roxyapi' ),
		required: false,
		help: __( 'First name already in Hebrew, which skips the transliteration step.', 'roxyapi' ),
	},
	{
		name: 'secondName',
		control: 'text',
		label: __( 'Second name', 'roxyapi' ),
		required: false,
		help: __( 'Second name in Latin script', 'roxyapi' ),
	},
	{
		name: 'secondNameHebrew',
		control: 'text',
		label: __( 'Second name hebrew', 'roxyapi' ),
		required: false,
		help: __( 'Second name already in Hebrew, which skips the transliteration step.', 'roxyapi' ),
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
