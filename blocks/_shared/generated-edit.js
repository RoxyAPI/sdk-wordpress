/*
 * Shared editor for every spec-generated long-tail block.
 *
 * SECURITY: the preview runs server side via ServerSideRender. Do NOT refactor
 * to a browser fetch that returns the RoxyAPI key or hits roxyapi.com directly.
 *
 * One component drives all ~135 generated blocks: each block's generated
 * index.js passes its spec-derived field list (bin/generate.mjs
 * deriveBlockFields), and this renders the matching sidebar control per field
 * plus a server-rendered preview. Field names are the camelCase block
 * attribute keys; the block's render.php snake-cases them for the shortcode.
 */

import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	ToggleControl,
	Placeholder,
	Button,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

/**
 * Title-case an enum value for display: `north_indian` becomes `North Indian`.
 *
 * @param {string} value Raw enum value.
 * @return {string} Human-readable label.
 */
function humanizeOption( value ) {
	return String( value )
		.replace( /[_-]+/g, ' ' )
		.replace( /\b\w/g, ( character ) => character.toUpperCase() );
}

/**
 * Render the single sidebar control a field asks for.
 *
 * @param {Object}   props               Component props.
 * @param {Object}   props.field         Field descriptor from the generator.
 * @param {string}   props.value         Current attribute value.
 * @param {Function} props.setAttributes Block attribute setter.
 * @return {Object} The control element.
 */
function FieldControl( { field, value, setAttributes } ) {
	const onChange = ( next ) => setAttributes( { [ field.name ]: next } );

	if ( field.control === 'select' ) {
		const options = [
			{
				label: field.required
					? __( 'Select an option', 'roxyapi' )
					: __( 'Default', 'roxyapi' ),
				value: '',
			},
			...field.options.map( ( option ) => ( {
				label: humanizeOption( option ),
				value: option,
			} ) ),
		];
		return (
			<SelectControl
				label={ field.label }
				value={ value }
				options={ options }
				onChange={ onChange }
				help={ field.help }
			/>
		);
	}

	if ( field.control === 'toggle' ) {
		return (
			<ToggleControl
				label={ field.label }
				checked={ value === 'true' }
				onChange={ ( checked ) =>
					onChange( checked ? 'true' : 'false' )
				}
				help={ field.help }
			/>
		);
	}

	const inputType =
		{ number: 'number', date: 'date', time: 'time' }[ field.control ] ||
		'text';

	return (
		<TextControl
			label={ field.label }
			type={ inputType }
			value={ value }
			onChange={ onChange }
			help={ field.help }
		/>
	);
}

/**
 * Build a block edit component for a generated block from its field list.
 *
 * @param {Array}  fields    Field descriptors from the generator.
 * @param {string} blockName Registered block name (e.g. `roxyapi/get-crystal`).
 * @return {Function} The block edit component.
 */
export function makeEdit( fields, blockName ) {
	return function Edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		const editor = window.RoxyAPIEditor || {};

		const inspector = (
			<InspectorControls>
				<PanelBody title={ __( 'Reading Settings', 'roxyapi' ) }>
					{ fields.map( ( field ) => (
						<FieldControl
							key={ field.name }
							field={ field }
							value={ attributes[ field.name ] ?? '' }
							setAttributes={ setAttributes }
						/>
					) ) }
				</PanelBody>
			</InspectorControls>
		);

		if ( ! editor.hasKey ) {
			return (
				<div { ...blockProps }>
					{ inspector }
					<Placeholder
						icon="star-filled"
						label={ __( 'RoxyAPI not connected', 'roxyapi' ) }
						instructions={ __(
							'Add your RoxyAPI key in Settings to preview this reading.',
							'roxyapi'
						) }
					>
						<Button
							variant="primary"
							href={ editor.settingsUrl || '#' }
						>
							{ __( 'Open settings', 'roxyapi' ) }
						</Button>
					</Placeholder>
				</div>
			);
		}

		const needsInput = fields.some(
			( field ) => field.required && ! attributes[ field.name ]
		);
		if ( needsInput ) {
			return (
				<div { ...blockProps }>
					{ inspector }
					<Placeholder
						icon="star-filled"
						label={ __( 'Reading inputs needed', 'roxyapi' ) }
						instructions={ __(
							'Fill in the required fields in the sidebar to preview and publish this reading.',
							'roxyapi'
						) }
					/>
				</div>
			);
		}

		return (
			<div { ...blockProps }>
				{ inspector }
				<ServerSideRender
					block={ blockName }
					attributes={ attributes }
				/>
			</div>
		);
	};
}
