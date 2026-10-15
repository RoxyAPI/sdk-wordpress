/**
 * One question at a time, using only the bundled builder manifest.
 */
( function () {
	'use strict';

	const root = document.getElementById( 'roxyapi-builder' );
	const settings = window.RoxyAPIBuilder;
	if ( ! root || ! settings ) {
		return;
	}
	const { manifest, i18n: strings } = settings;
	const reserved = new Set( [
		'mode',
		...manifest.reservedAttributes.map( ( attribute ) => attribute.att ),
	] );
	const entries = manifest.entries
		.slice()
		.sort( ( a, b ) => a.order - b.order );
	const fieldLabels = new Map();
	entries.forEach( ( entry ) => {
		entry.attributes.forEach( ( attribute ) => {
			fieldLabels.set( attribute.att, attribute.label );
		} );
	} );
	const readings = new Map();
	let selected = null;
	let mode = '';
	let step = 'pick';
	let attemptedDetails = false;

	function element( tag, text, className ) {
		const node = document.createElement( tag );
		if ( text ) {
			node.textContent = text;
		}
		if ( className ) {
			node.className = className;
		}
		return node;
	}

	function button( text, className, action ) {
		const node = element( 'button', text, className );
		node.type = 'button';
		node.addEventListener( 'click', action );
		return node;
	}

	root.appendChild( element( 'p', strings.intro ) );
	const progress = element( 'p', '', 'roxyapi-builder-progress' );
	root.appendChild( progress );
	const steps = {};
	[ 'pick', 'who', 'details', 'display', 'finish' ].forEach( ( name ) => {
		const panel = element( 'section', '', 'card roxyapi-builder-step' );
		panel.hidden = true;
		const heading = element( 'h2', strings[ name ] );
		heading.id = 'roxyapi-builder-step-' + name;
		heading.tabIndex = -1;
		panel.appendChild( heading );
		root.appendChild( panel );
		steps[ name ] = panel;
	} );

	const searchLabel = element( 'label', strings.find );
	searchLabel.htmlFor = 'roxyapi-builder-search';
	const search = element( 'input' );
	search.type = 'search';
	search.placeholder = strings.searchHint;
	search.id = searchLabel.htmlFor;
	steps.pick.append( searchLabel, search );
	const groups = element( 'div' );
	steps.pick.appendChild( groups );

	const modeChoices = element( 'fieldset', '', 'roxyapi-builder-choices' );
	modeChoices.setAttribute(
		'aria-labelledby',
		steps.who.querySelector( 'h2' ).id
	);
	const modeInputs = {};
	[ 'visitor', 'set' ].forEach( ( value ) => {
		const label = element( 'label', '', 'roxyapi-builder-choice' );
		const input = element( 'input' );
		input.type = 'radio';
		input.name = 'roxyapi-builder-mode';
		input.value = value;
		input.addEventListener( 'change', () => {
			setMode( value );
			updateNavigation();
		} );
		modeInputs[ value ] = input;
		const text = element( 'span' );
		text.append(
			element( 'strong', strings[ value + 'Choice' ] ),
			element(
				'span',
				strings[ value + 'Help' ],
				'roxyapi-builder-description'
			)
		);
		label.append( input, text );
		modeChoices.appendChild( label );
	} );
	const modeNotice = element( 'p' );
	steps.who.append( modeChoices, modeNotice );

	// The shared combobox scans once. Keep its city inputs and scopes alive,
	// then create each reading's controls only when that reading is selected.
	entries.forEach( ( entry, index ) => {
		const panel = element( 'div' );
		panel.id = 'roxyapi-builder-reading-' + index;
		panel.hidden = true;
		const required = element( 'div' );
		const optional = element( 'details' );
		optional.appendChild( element( 'summary', strings.more ) );
		panel.append( required, optional );
		steps.details.appendChild( panel );
		const attributes = entry.attributes.filter(
			( attribute ) => ! reserved.has( attribute.att )
		);
		const locations = new Map();
		attributes.forEach( ( attribute ) => {
			if ( attribute.location ) {
				const { group, role } = attribute.location;
				if ( ! locations.has( group ) ) {
					locations.set( group, { fields: {}, required: false } );
				}
				const location = locations.get( group );
				location.fields[ role ] = attribute.att;
				location.required ||= attribute.required;
			}
		} );
		locations.forEach( ( location, group ) => {
			const wrapper = element( 'div', '', 'roxyapi-builder-city' );
			const label = element( 'label', strings.city );
			const input = element( 'input' );
			input.type = 'search';
			input.id = panel.id + '-city-' + group;
			label.htmlFor = input.id;
			input.setAttribute( 'data-roxyapi-geocode', '#' + panel.id );
			input.setAttribute( 'data-roxyapi-geocode-quiet', '' );
			input.setAttribute(
				'data-roxyapi-geocode-fields',
				JSON.stringify( location.fields )
			);
			const help = element( 'p', strings.cityHelp, 'description' );
			help.id = input.id + '-help';
			input.setAttribute( 'aria-describedby', help.id );
			wrapper.append( label, input, help );
			( location.required ? required : optional ).appendChild( wrapper );
		} );
		readings.set( entry.tag, {
			entry,
			attributes,
			panel,
			required,
			optional,
			controls: [],
			initialized: false,
		} );
	} );

	settings.groups.forEach( ( { label: title, tags } ) => {
		const items = tags.map( ( tag ) => readings.get( tag ).entry );
		if ( ! items.length ) {
			return;
		}
		const group = element( 'section', '', 'roxyapi-builder-group' );
		group.appendChild( element( 'h3', title ) );
		const grid = element( 'div', '', 'roxyapi-builder-grid' );
		items.forEach( ( entry ) => {
			const description = entry.description.replace(
				/`([^`]+)`/g,
				( match, name ) =>
					fieldLabels.get( name ) || name.replace( /_/g, ' ' )
			);
			const label = element( 'label', '', 'roxyapi-builder-choice' );
			const radio = element( 'input' );
			radio.type = 'radio';
			radio.name = 'roxyapi-builder-reading';
			radio.value = entry.tag;
			radio.addEventListener( 'change', () =>
				selectReading( entry.tag )
			);
			const text = element( 'span' );
			text.append(
				element( 'strong', entry.title ),
				element( 'span', description, 'roxyapi-builder-description' )
			);
			label.append( radio, text );
			label.dataset.search = (
				entry.title +
				' ' +
				description +
				' ' +
				manifest.domains[ entry.domain ].label
			).toLowerCase();
			grid.appendChild( label );
		} );
		group.appendChild( grid );
		groups.appendChild( group );
	} );
	search.addEventListener( 'input', () => {
		const query = search.value.trim().toLowerCase();
		groups.querySelectorAll( 'section' ).forEach( ( group ) => {
			let count = 0;
			group.querySelectorAll( '[data-search]' ).forEach( ( card ) => {
				card.hidden = ! card.dataset.search.includes( query );
				if ( ! card.hidden ) {
					count++;
				}
			} );
			group.hidden = count === 0;
		} );
	} );

	const writtenLabel = element( 'label', strings.written );
	const written = element( 'select' );
	written.id = 'roxyapi-builder-written';
	writtenLabel.htmlFor = written.id;
	[
		[ '', strings.inherit ],
		[ '0', strings.show ],
		[ '1', strings.hide ],
	].forEach( ( [ value, label ] ) => addOption( written, value, label ) );
	steps.display.append( writtenLabel, written );

	const output = element( 'textarea' );
	output.readOnly = true;
	output.rows = 5;
	output.setAttribute( 'aria-label', strings.finish );
	const status = element( 'p' );
	status.setAttribute( 'role', 'status' );
	status.setAttribute( 'aria-live', 'polite' );
	const copy = button( strings.copy, 'button button-primary', async () => {
		status.textContent = '';
		try {
			if ( window.navigator.clipboard?.writeText ) {
				await window.navigator.clipboard.writeText( output.value );
			} else {
				output.focus();
				output.select();
				if ( ! document.execCommand( 'copy' ) ) {
					throw new Error();
				}
			}
			status.textContent = strings.copied;
		} catch ( error ) {
			output.focus();
			output.select();
			status.textContent = strings.copyFailed;
		}
	} );
	steps.finish.append( output, copy, status, element( 'p', strings.paste ) );
	const newPage = element( 'a', strings.newPage, 'button button-secondary' );
	newPage.href = settings.newPage;
	steps.finish.appendChild( newPage );

	const switcher = button( '', 'button-link roxyapi-builder-switch', () => {
		setMode( mode === 'set' ? 'visitor' : 'set' );
		showStep( mode === 'set' ? 'details' : 'display' );
	} );
	root.appendChild( switcher );
	const navigation = element( 'div', '', 'roxyapi-builder-navigation' );
	const back = button( strings.back, 'button', () => {
		const route = itinerary();
		showStep( route[ route.indexOf( step ) - 1 ] );
	} );
	const next = button( strings.continue, 'button button-primary', () => {
		if ( next.disabled ) {
			return;
		}
		if ( step === 'details' ) {
			attemptedDetails = true;
			updateNavigation();
			const controls = Array.from(
				selected.panel.querySelectorAll( '[name]' )
			);
			const invalid =
				controls.find(
					( input ) => input.required && ! input.value.trim()
				) || controls.find( ( input ) => ! input.validity.valid );
			if ( invalid ) {
				const disclosure = invalid.closest( 'details' );
				if ( disclosure ) {
					disclosure.open = true;
				}
				invalid.focus();
				if ( invalid.value.trim() ) {
					invalid.reportValidity();
				}
				return;
			}
		}
		const route = itinerary();
		showStep( route[ route.indexOf( step ) + 1 ] );
	} );
	navigation.append( back, next );
	root.appendChild( navigation );

	function addOption( select, value, label ) {
		const option = element( 'option', label );
		option.value = value;
		select.appendChild( option );
	}

	function initializeControls( reading ) {
		if ( reading.initialized ) {
			return;
		}
		reading.initialized = true;
		reading.attributes.forEach( ( attribute, index ) => {
			const wrapper = element( 'div', '', 'roxyapi-builder-field' );
			const label = element( 'label', attribute.label );
			const isSelect = [ 'enum', 'timezone', 'boolean' ].includes(
				attribute.type
			);
			const input = element( isSelect ? 'select' : 'input' );
			input.name = attribute.att;
			input.id = reading.panel.id + '-field-' + index;
			input.required = attribute.required;
			label.htmlFor = input.id;
			if ( isSelect ) {
				addOption( input, '', '' );
				if ( attribute.type === 'timezone' ) {
					settings.timezones.forEach( ( timezone ) =>
						addOption(
							input,
							timezone,
							timezone.replace( /_/g, ' ' )
						)
					);
				} else if ( attribute.type === 'boolean' ) {
					addOption( input, '1', strings.yes );
					addOption( input, '0', strings.no );
				} else {
					attribute.enum.forEach( ( value, optionIndex ) => {
						addOption(
							input,
							String( value ),
							attribute.enum_labels[ optionIndex ] ||
								String( value )
						);
					} );
				}
			} else {
				input.type = 'text';
				if ( [ 'date', 'time', 'number' ].includes( attribute.type ) ) {
					input.type = attribute.type;
				} else if ( attribute.type === 'integer' ) {
					input.type = 'number';
				}
				if ( input.type === 'number' ) {
					input.step = attribute.type === 'integer' ? '1' : 'any';
					if ( attribute.min !== null ) {
						input.min = String( attribute.min );
					}
					if ( attribute.max !== null ) {
						input.max = String( attribute.max );
					}
				}
				if ( input.type === 'time' ) {
					input.step = '1';
				}
				if ( attribute.example !== null && attribute.type !== 'json' ) {
					input.placeholder = Array.isArray( attribute.example )
						? attribute.example.join( ', ' )
						: String( attribute.example );
				}
			}
			const value = attribute.default;
			input.value =
				value === null || typeof value === 'object'
					? ''
					: String( value );
			if ( attribute.type === 'date' && value === 'today' ) {
				input.value = settings.today;
			}
			const error = element(
				'p',
				strings.needed,
				'roxyapi-builder-error'
			);
			error.id = input.id + '-error';
			error.hidden = true;
			input.setAttribute( 'aria-describedby', error.id );
			wrapper.append( label, input, error );
			( attribute.required
				? reading.required
				: reading.optional
			).appendChild( wrapper );
			reading.controls.push( { input, error } );
			input.addEventListener( 'input', updateNavigation );
			input.addEventListener( 'change', updateNavigation );
		} );
		reading.optional.hidden =
			! reading.optional.querySelector( 'input, select' );
	}

	function selectReading( tag ) {
		attemptedDetails = false;
		selected = readings.get( tag );
		initializeControls( selected );
		readings.forEach( ( reading ) => {
			reading.panel.hidden = reading !== selected;
		} );
		mode = 'set';
		if ( selected.entry.modes.includes( 'visitor' ) ) {
			mode = 'visitor';
		} else if ( selected.entry.modes.includes( 'none' ) ) {
			mode = 'none';
		}
		modeChoices.hidden = ! (
			selected.entry.modes.includes( 'visitor' ) &&
			selected.entry.modes.includes( 'set' )
		);
		modeNotice.textContent = '';
		if ( selected.entry.formOnly ) {
			modeNotice.textContent = strings.formOnly;
		} else if ( mode === 'none' ) {
			modeNotice.textContent = strings.noDetails;
		} else if ( modeChoices.hidden ) {
			modeNotice.textContent = strings.setChoice;
		}
		modeNotice.hidden = ! modeNotice.textContent;
		updateNavigation();
	}

	function setMode( value ) {
		attemptedDetails = false;
		mode = value;
		if ( mode === 'visitor' ) {
			selected.controls.forEach( ( { input } ) => {
				input.value = '';
			} );
			selected.panel
				.querySelectorAll( '[data-roxyapi-geocode]' )
				.forEach( ( input ) => {
					input.value = '';
					input.dispatchEvent(
						new window.Event( 'input', { bubbles: true } )
					);
				} );
		}
	}

	function itinerary() {
		return mode === 'set'
			? [ 'pick', 'who', 'details', 'display', 'finish' ]
			: [ 'pick', 'who', 'display', 'finish' ];
	}

	function updateNavigation() {
		if ( selected ) {
			selected.controls.forEach( ( { input, error } ) => {
				const empty = input.required && ! input.value.trim();
				error.hidden = ! attemptedDetails || ! empty;
				input.setAttribute(
					'aria-invalid',
					attemptedDetails && ( empty || ! input.validity.valid )
						? 'true'
						: 'false'
				);
			} );
		}
		next.disabled = ! selected;
		next.hidden = step === 'finish';
		back.hidden = step === 'pick';
		const route = itinerary();
		progress.textContent = strings.progress
			.replace( '%1$d', String( route.indexOf( step ) + 1 ) )
			.replace( '%2$d', String( route.length ) );
		Object.entries( modeInputs ).forEach( ( [ value, input ] ) => {
			input.checked = value === mode;
		} );
		const canSwitch =
			selected &&
			selected.entry.modes.includes( 'set' ) &&
			( selected.entry.modes.includes( 'visitor' ) || mode === 'none' );
		switcher.hidden =
			! canSwitch ||
			step === 'pick' ||
			( step === 'who' && ! modeChoices.hidden );
		switcher.textContent = mode === 'set' ? strings.visitor : strings.set;
	}

	function quote( value ) {
		return String( value )
			.replace( /&/g, '&amp;' )
			.replace( /"/g, '&quot;' )
			.replace( /'/g, '&#039;' )
			.replace( /\[/g, '&#91;' )
			.replace( /\]/g, '&#93;' )
			.replace( /\\/g, '\\\\' );
	}

	function shortcode() {
		const attributes = [];
		if ( mode === 'set' ) {
			selected.controls.forEach( ( { input } ) => {
				if ( input.value.trim() ) {
					attributes.push(
						input.name + '="' + quote( input.value.trim() ) + '"'
					);
				}
			} );
		}
		if ( written.value !== '' ) {
			attributes.push( 'hide_readings="' + written.value + '"' );
		}
		return (
			'[' +
			selected.entry.tag +
			( attributes.length ? ' ' + attributes.join( ' ' ) : '' ) +
			']'
		);
	}

	function showStep( name ) {
		step = name;
		Object.entries( steps ).forEach( ( [ key, panel ] ) => {
			panel.hidden = key !== step;
		} );
		status.textContent = '';
		if ( step === 'finish' ) {
			output.value = shortcode();
		}
		updateNavigation();
		steps[ step ].querySelector( 'h2' ).focus();
	}

	// City selection changes values without input events. Its live status is
	// updated after filling the fields, so observe that shared picker signal.
	new window.MutationObserver( updateNavigation ).observe( steps.details, {
		childList: true,
		characterData: true,
		subtree: true,
	} );
	if ( readings.has( settings.reading ) ) {
		selectReading( settings.reading );
		groups.querySelectorAll( 'input' ).forEach( ( input ) => {
			input.checked = input.value === settings.reading;
		} );
		showStep( 'who' );
	} else {
		showStep( 'pick' );
	}
} )();
