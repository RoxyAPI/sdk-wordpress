import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'planet',
		control: 'text',
		label: __( 'Planet', 'roxyapi' ),
		required: true,
		help: __( 'Planet to track (case-insensitive)', 'roxyapi' ),
	},
	{
		name: 'startDate',
		control: 'date',
		label: __( 'Start date', 'roxyapi' ),
		required: true,
		help: __( 'Start date for sublord change search (YYYY-MM-DD format)', 'roxyapi' ),
	},
	{
		name: 'endDate',
		control: 'date',
		label: __( 'End date', 'roxyapi' ),
		required: true,
		help: __( 'End date for sublord change search (YYYY-MM-DD format)', 'roxyapi' ),
	},
	{
		name: 'timezone',
		control: 'text',
		label: __( 'Timezone', 'roxyapi' ),
		required: false,
		help: __( 'IANA name (e.g. "America/New_York", "Europe/London") OR decimal hours from UTC', 'roxyapi' ),
	},
	{
		name: 'ayanamsa',
		control: 'select',
		label: __( 'Ayanamsa', 'roxyapi' ),
		required: false,
		help: __( 'Ayanamsa system for sidereal conversion', 'roxyapi' ),
		options: [ 'kp-newcomb', 'kp-old', 'lahiri', 'raman' ],
	},
	{
		name: 'nodeType',
		control: 'select',
		label: __( 'Node type', 'roxyapi' ),
		required: false,
		help: __( 'Lunar node convention', 'roxyapi' ),
		options: [ 'mean', 'true' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
