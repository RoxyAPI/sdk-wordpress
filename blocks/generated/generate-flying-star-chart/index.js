import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = [
	{
		name: 'period',
		control: 'number',
		label: __( 'Period', 'roxyapi' ),
		required: false,
		help: __( 'Construction period of the building, 1 to 9', 'roxyapi' ),
	},
	{
		name: 'facing',
		control: 'select',
		label: __( 'Facing', 'roxyapi' ),
		required: false,
		help: __( 'The mountain the front of the building faces, by id or by compass label such as S2', 'roxyapi' ),
		options: [ 'ren', 'zi', 'gui', 'chou', 'gen', 'yin', 'jia', 'mao', 'yi', 'chen', 'xun', 'si', 'bing', 'wu', 'ding', 'wei', 'kun', 'shen', 'geng', 'you', 'xin', 'xu', 'qian', 'hai', 'N1', 'N2', 'N3', 'NE1', 'NE2', 'NE3', 'E1', 'E2', 'E3', 'SE1', 'SE2', 'SE3', 'S1', 'S2', 'S3', 'SW1', 'SW2', 'SW3', 'W1', 'W2', 'W3', 'NW1', 'NW2', 'NW3' ],
	},
	{
		name: 'facingDegrees',
		control: 'text',
		label: __( 'Facing degrees', 'roxyapi' ),
		required: false,
		help: __( 'The compass bearing the front of the building faces, 0 to 360 degrees, measured looking out from inside', 'roxyapi' ),
	},
];

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name ),
	save: () => null,
} );
