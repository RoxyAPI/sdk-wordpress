import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'length',
		control: 'number',
		label: __( 'Length', 'roxyapi' ),
		required: true,
		help: __( 'Length of the building or room, in the unit given', 'roxyapi' ),
	},
	{
		name: 'breadth',
		control: 'number',
		label: __( 'Breadth', 'roxyapi' ),
		required: true,
		help: __( 'Breadth of the building or room, in the unit given', 'roxyapi' ),
	},
	{
		name: 'perimeter',
		control: 'number',
		label: __( 'Perimeter', 'roxyapi' ),
		required: false,
		help: __( 'Perimeter of the plan, if it is not simply twice the length plus twice the breadth', 'roxyapi' ),
	},
	{
		name: 'circumference',
		control: 'number',
		label: __( 'Circumference', 'roxyapi' ),
		required: false,
		help: __( 'Circumference or height, if the Manasara vara and tithi formulas should read something other than the perimeter', 'roxyapi' ),
	},
	{
		name: 'unit',
		control: 'select',
		label: __( 'Unit', 'roxyapi' ),
		required: false,
		help: __( 'Unit the Ayadi dimensions are given in', 'roxyapi' ),
		options: [ 'hasta', 'feet', 'metres' ],
	},
	{
		name: 'hastaInches',
		control: 'number',
		label: __( 'Hasta inches', 'roxyapi' ),
		required: false,
		help: __( 'Length of one hasta, the classical cubit, in inches', 'roxyapi' ),
	},
	{
		name: 'ayadiText',
		control: 'select',
		label: __( 'Ayadi text', 'roxyapi' ),
		required: false,
		help: __( 'Which family of Ayadi formulas to apply', 'roxyapi' ),
		options: [ 'manasara', 'perimeter-texts', 'utpala' ],
	},
	{
		name: 'vyayaFormula',
		control: 'select',
		label: __( 'Vyaya formula', 'roxyapi' ),
		required: false,
		help: __( 'Which vyaya formula the perimeter family uses', 'roxyapi' ),
		options: [ 'p9-10', 'p3-14' ],
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
