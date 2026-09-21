/*
 * Shared editor for every generated block.
 *
 * SECURITY: the preview runs server side via ServerSideRender. Do NOT refactor
 * to a browser fetch that returns the RoxyAPI key or hits roxyapi.com directly.
 *
 * One component drives the whole catalogue, the five headline readings
 * included: each block's generated index.js passes its field list (bin/
 * generate.mjs, derived from the spec for a long-tail block and from the
 * hero's own visitor-form fields for a headline one), and this renders the
 * matching sidebar control per field plus a server-rendered preview. Field
 * names are the block attribute keys; the block's render.php maps them onto
 * the shortcode.
 */

import apiFetch from '@wordpress/api-fetch';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	ToggleControl,
	Placeholder,
	Button,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

/**
 * The attribute names a block uses for a place, in the two spellings the
 * catalogue carries: the headline readings say lat/lon/tz, the long tail says
 * latitude/longitude/timezone.
 *
 * @param {Array} fields Field descriptors from the generator.
 * @return {Object|null} `{ lat, lon, tz }` attribute names, or null when the
 *                       block takes no coordinates.
 */
function placeFields( fields ) {
	const names = new Set( fields.map( ( field ) => field.name ) );
	const pick = ( ...candidates ) =>
		candidates.find( ( name ) => names.has( name ) );
	const lat = pick( 'lat', 'latitude' );
	const lon = pick( 'lon', 'longitude' );
	if ( ! lat || ! lon ) {
		return null;
	}
	return { lat, lon, tz: pick( 'tz', 'timezone' ) };
}

/**
 * City search that fills the coordinate and timezone attributes, through the
 * plugin's own geocode route so no key reaches the browser. The coordinate
 * fields stay editable underneath, exactly like the visitor form.
 *
 * @param {Object}   props               Component props.
 * @param {Object}   props.place         Attribute names from placeFields().
 * @param {Function} props.setAttributes Block attribute setter.
 * @return {Object} The control element.
 */
function CitySearch( { place, setAttributes } ) {
	const [ query, setQuery ] = useState( '' );
	const [ cities, setCities ] = useState( [] );

	useEffect( () => {
		const q = query.trim();
		if ( q.length < 2 ) {
			setCities( [] );
			return undefined;
		}
		let stale = false;
		const timer = setTimeout( () => {
			apiFetch( {
				path: '/roxyapi/v1/geocode?q=' + encodeURIComponent( q ),
			} )
				.then( ( response ) => {
					if ( ! stale ) {
						setCities( response?.cities ?? [] );
					}
				} )
				.catch( () => {
					if ( ! stale ) {
						setCities( [] );
					}
				} );
		}, 250 );
		return () => {
			stale = true;
			clearTimeout( timer );
		};
	}, [ query ] );

	const pickCity = ( city ) => {
		const next = {
			[ place.lat ]: String( city.lat ),
			[ place.lon ]: String( city.lon ),
		};
		if ( place.tz ) {
			next[ place.tz ] = city.tz;
		}
		setAttributes( next );
		setQuery( '' );
	};

	return (
		<div>
			<TextControl
				label={ __( 'Search by city', 'roxyapi' ) }
				value={ query }
				onChange={ setQuery }
				placeholder={ __( 'Start typing a city name', 'roxyapi' ) }
				help={ __(
					'Picking a city fills the coordinates and timezone below.',
					'roxyapi'
				) }
			/>
			{ cities.map( ( city ) => (
				<div key={ city.label + city.tz }>
					<Button variant="link" onClick={ () => pickCity( city ) }>
						{ city.label }
					</Button>
				</div>
			) ) }
		</div>
	);
}

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
			// step="any" lets number fields accept decimals (latitude, longitude, ayanamsa) instead of the default integer-only step.
			step={ inputType === 'number' ? 'any' : undefined }
			value={ value }
			onChange={ onChange }
			help={ field.help }
			placeholder={ field.placeholder }
		/>
	);
}

/**
 * Build a block edit component for a generated block from its field list.
 *
 * @param {Array}  fields                 Field descriptors from the generator.
 * @param {string} blockName              Registered block name (e.g. `roxyapi/get-crystal`).
 * @param {Object} [options]              Per-block overrides.
 * @param {string} [options.instructions] Empty-state copy, already translated by
 *                                        the caller. What publishing an
 *                                        unconfigured block DOES differs by
 *                                        block: a headline reading with required
 *                                        inputs publishes a visitor form, while a
 *                                        long-tail one publishes nothing until it
 *                                        is filled in, so the default sentence
 *                                        would be false for half the catalogue.
 * @return {Function} The block edit component.
 */
export function makeEdit( fields, blockName, options = {} ) {
	return function Edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();

		// A reading with no inputs, such as the current moon phase, has no
		// settings panel to show.
		const place = placeFields( fields );
		const inspector = fields.length > 0 && (
			<InspectorControls>
				<PanelBody title={ __( 'Reading Settings', 'roxyapi' ) }>
					{ place && (
						<CitySearch
							place={ place }
							setAttributes={ setAttributes }
						/>
					) }
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

		// Readings render without a key, so previewing without one is NOT an
		// error state and must not be blocked. Any genuine failure is reported
		// by the reading itself.
		//
		// Preview only once the block describes a reading. `required` alone is
		// not enough to rely on: a spec that omits a required field would let an
		// empty block through, and ServerSideRender fires immediately on insert
		// and again on every keystroke, so each empty block became a stream of
		// requests that could never succeed. An untouched block is never ready.
		const needsInput =
			fields.some(
				( field ) => field.required && ! attributes[ field.name ]
			) ||
			( fields.length > 0 &&
				fields.every( ( field ) => ! attributes[ field.name ] ) );
		if ( needsInput ) {
			return (
				<div { ...blockProps }>
					{ inspector }
					<Placeholder
						icon="star-filled"
						label={ __( 'Reading inputs needed', 'roxyapi' ) }
						instructions={
							options.instructions ||
							__(
								'Fill in the required fields in the sidebar to preview and publish this reading.',
								'roxyapi'
							)
						}
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
					httpMethod="POST"
					skipBlockSupportAttributes
				/>
			</div>
		);
	};
}
