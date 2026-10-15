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
	const { __, sprintf } = window.wp.i18n;
	const strings = {
		titleSetup: __( 'Create a reading.', 'roxyapi' ),
		titlePunch: __( 'No code needed.', 'roxyapi' ),
		mostLoved: __( 'Most loved', 'roxyapi' ),
		intro: __(
			'Answer a few questions and copy a finished shortcode.',
			'roxyapi'
		),
		pick: __( 'Pick a reading', 'roxyapi' ),
		find: __( 'Find a reading', 'roxyapi' ),
		noMatches: __( 'No matching readings.', 'roxyapi' ),
		searchHint: __( 'Try tarot, horoscope or birth chart', 'roxyapi' ),
		who: __( 'Who fills in the details?', 'roxyapi' ),
		details: __( 'Set the details', 'roxyapi' ),
		display: __( 'Display', 'roxyapi' ),
		finish: __( 'Copy your shortcode', 'roxyapi' ),
		back: __( 'Back', 'roxyapi' ),
		continue: __( 'Continue', 'roxyapi' ),
		visitor: __( 'Ask my visitor instead', 'roxyapi' ),
		set: __( 'Show a set reading instead', 'roxyapi' ),
		visitorChoice: __( 'Ask my visitor', 'roxyapi' ),
		visitorHelp: __(
			'Shows a form. Each visitor enters their own details.',
			'roxyapi'
		),
		setChoice: __( 'Show a set reading', 'roxyapi' ),
		setHelp: __(
			'You enter the details once. Every visitor sees the same reading.',
			'roxyapi'
		),
		formOnly: __( 'This reading always asks the visitor.', 'roxyapi' ),
		noDetails: __(
			'This reading needs no details. It shows straight away.',
			'roxyapi'
		),
		more: __( 'More options', 'roxyapi' ),
		needed: __( 'Needed before the shortcode works.', 'roxyapi' ),
		written: __( 'Written interpretation', 'roxyapi' ),
		show: __( 'Show', 'roxyapi' ),
		hide: __( 'Hide', 'roxyapi' ),
		inherit: __( 'Follow my site setting', 'roxyapi' ),
		copy: __( 'Copy shortcode', 'roxyapi' ),
		copied: __( 'Copied. Paste it into any page.', 'roxyapi' ),
		copyFailed: __(
			'Copy failed. Select the text and copy it.',
			'roxyapi'
		),
		paste: __(
			'In the block editor, add a Shortcode block and paste it there.',
			'roxyapi'
		),
		newPage: __( 'Create a new page', 'roxyapi' ),
		city: __( 'City', 'roxyapi' ),
		cityHelp: __(
			'Start typing. We fill in the timezone for you.',
			'roxyapi'
		),
		yes: __( 'Yes', 'roxyapi' ),
		no: __( 'No', 'roxyapi' ),
		documentation: __( 'WordPress integration guide', 'roxyapi' ),
		loadFailed: __(
			'The reading library could not load. Please reload the page.',
			'roxyapi'
		),
	};
	const { manifest } = settings;
	document
		.querySelectorAll( '[data-roxyapi-builder-title]' )
		.forEach( ( node ) => {
			node.replaceChildren(
				element( 'span', strings.titleSetup, 'roxyapi-setup' ),
				document.createTextNode( ' ' ),
				element( 'span', strings.titlePunch, 'roxyapi-punchline' )
			);
		} );
	document
		.querySelectorAll( '[data-roxyapi-builder-documentation]' )
		.forEach( ( node ) => {
			node.textContent = strings.documentation;
		} );
	if ( settings.standalone ) {
		document.title = strings.titleSetup + ' ' + strings.titlePunch;
	}
	if ( ! manifest ) {
		root.textContent = strings.loadFailed;
		return;
	}
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

	if ( settings.standalone ) {
		// Intl lists canonical zones; city results can use valid IANA aliases.
		const zones = element( 'datalist' );
		zones.id = 'roxyapi-builder-timezones';
		settings.timezones.forEach( ( zone ) =>
			addOption( zones, zone, zone.replace( /_/g, ' ' ) )
		);
		root.appendChild( zones );
	}
	root.appendChild( element( 'p', strings.intro, 'roxyapi-page-intro' ) );
	const progress = element( 'p', '', 'roxyapi-builder-progress' );
	root.appendChild( progress );
	const steps = {};
	[ 'pick', 'who', 'details', 'display', 'finish' ].forEach( ( name ) => {
		const panel = element(
			'section',
			'',
			'roxyapi-step-card roxyapi-builder-step'
		);
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
	const results = element( 'div', '', 'roxyapi-builder-grid' );
	results.hidden = true;
	const noMatches = element( 'p', strings.noMatches );
	noMatches.setAttribute( 'role', 'status' );
	noMatches.hidden = true;
	const groups = element( 'div', '', 'roxyapi-builder-groups' );
	steps.pick.append( results, noMatches, groups );

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
		const attributes = entry.attributes;
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
			if ( settings.restUrl ) {
				input.setAttribute(
					'data-roxyapi-geocode-url',
					settings.restUrl
				);
			}
			if ( settings.geocodeResponse ) {
				input.setAttribute(
					'data-roxyapi-geocode-response',
					JSON.stringify( settings.geocodeResponse )
				);
			}
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

	// Group heroes first, then the remaining readings in domain order.
	const readingGroups = [
		{
			label: strings.mostLoved,
			tags: entries
				.filter( ( entry ) => entry.hero )
				.map( ( entry ) => entry.tag ),
		},
		...Object.entries( manifest.domains ).map( ( [ domain, info ] ) => ( {
			label: info.label,
			tags: entries
				.filter( ( entry ) => ! entry.hero && entry.domain === domain )
				.map( ( entry ) => entry.tag ),
		} ) ),
	];
	readingGroups.forEach( ( { label: title, tags } ) => {
		const items = tags.map( ( tag ) => readings.get( tag ).entry );
		if ( ! items.length ) {
			return;
		}
		const group = element( 'details', '', 'roxyapi-builder-group' );
		group.name = 'roxyapi-builder-groups';
		group.appendChild(
			element( 'summary', title + ' (' + items.length + ')' )
		);
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
				description
			).toLowerCase();
			readings.get( entry.tag ).choice = label;
			readings.get( entry.tag ).grid = grid;
			readings.get( entry.tag ).group = group;
			grid.appendChild( label );
		} );
		group.appendChild( grid );
		groups.appendChild( group );
	} );
	search.addEventListener( 'input', () => {
		const query = search.value.trim().toLowerCase();
		let count = 0;
		readings.forEach( ( { choice, grid } ) => {
			const matches = choice.dataset.search.includes( query );
			if ( query && matches ) {
				results.appendChild( choice );
				count++;
			} else {
				grid.appendChild( choice );
			}
		} );
		results.hidden = ! query;
		groups.hidden = !! query;
		noMatches.hidden = ! query || count > 0;
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
			let copied = false;
			if ( window.navigator.clipboard?.writeText ) {
				try {
					await window.navigator.clipboard.writeText( output.value );
					copied = true;
				} catch ( error ) {
					// Cross-origin embeds may deny the Clipboard API.
				}
			}
			if ( ! copied ) {
				output.focus();
				output.select();
				if ( ! document.execCommand( 'copy' ) ) {
					throw new Error();
				}
			}
			status.textContent = strings.copied;
			root.dispatchEvent(
				new window.CustomEvent( 'roxy-builder:copied', {
					detail: { tag: selected.entry.tag },
				} )
			);
		} catch ( error ) {
			output.focus();
			output.select();
			status.textContent = strings.copyFailed;
		}
	} );
	steps.finish.append( output, copy, status, element( 'p', strings.paste ) );
	if ( settings.newPage ) {
		const newPage = element(
			'a',
			strings.newPage,
			'button button-secondary'
		);
		newPage.href = settings.newPage;
		steps.finish.appendChild( newPage );
	}

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
			const isSelect =
				[ 'enum', 'boolean' ].includes( attribute.type ) ||
				( attribute.type === 'timezone' && ! settings.standalone );
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
				if ( attribute.type === 'timezone' ) {
					input.setAttribute( 'list', 'roxyapi-builder-timezones' );
				}
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
			// A native picker draws the browser's own date order, so the
			// chosen value is restated in words beside it.
			const said = [ 'date', 'time' ].includes( input.type )
				? element( 'p', '', 'description' )
				: null;
			if ( said ) {
				said.id = input.id + '-said';
			}
			input.setAttribute(
				'aria-describedby',
				said ? said.id + ' ' + error.id : error.id
			);
			wrapper.append( label, input, ...( said ? [ said ] : [] ), error );
			( attribute.required
				? reading.required
				: reading.optional
			).appendChild( wrapper );
			reading.controls.push( { input, error, said } );
			input.addEventListener( 'input', updateNavigation );
			input.addEventListener( 'change', updateNavigation );
		} );
		reading.optional.hidden =
			! reading.optional.querySelector( 'input, select' );
	}

	function selectReading( tag ) {
		attemptedDetails = false;
		selected = readings.get( tag );
		selected.choice.querySelector( 'input' ).checked = true;
		selected.group.open = true;
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
			selected.controls.forEach( ( { input, error, said } ) => {
				// Writing only on change keeps the details observer from looping.
				if ( said && said.textContent !== spelled( input ) ) {
					said.textContent = spelled( input );
				}
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
		progress.textContent = sprintf(
			/* translators: 1: current step number, 2: total number of steps. */
			__( 'Step %1$d of %2$d', 'roxyapi' ),
			route.indexOf( step ) + 1,
			route.length
		);
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

	// Same contract as Catalog::quote_attribute(), pinned by the shared fixture.
	// WordPress strips C escapes but leaves HTML entities as literal text.
	function quote( value ) {
		return (
			'"' +
			String( value )
				.replace( /&/g, '&amp;' )
				.replace( /"/g, '&quot;' )
				.replace( /'/g, '&#039;' )
				.replace( /\[/g, '&#91;' )
				.replace( /\]/g, '&#93;' )
				.replace( /</g, '&lt;' )
				.replace( />/g, '&gt;' )
				.replace( /\\/g, '\\\\' ) +
			'"'
		);
	}

	function spelled( input ) {
		if ( ! input.value || ! input.validity.valid ) {
			return '';
		}
		const [ a, b, c ] = input.value.split( /[-:]/ ).map( Number );
		const locale = document.documentElement.lang || undefined;
		return input.type === 'date'
			? new Date( a, b - 1, c ).toLocaleDateString( locale, {
					day: 'numeric',
					month: 'long',
					year: 'numeric',
			  } )
			: new Date( 2000, 0, 1, a, b ).toLocaleTimeString( locale, {
					hour: 'numeric',
					minute: '2-digit',
			  } );
	}

	function shortcode() {
		const attributes = [];
		if ( mode === 'set' ) {
			selected.controls.forEach( ( { input } ) => {
				if ( input.value.trim() ) {
					attributes.push(
						input.name + '=' + quote( input.value.trim() )
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
		if ( step === 'pick' && selected ) {
			search.value = '';
			search.dispatchEvent( new window.Event( 'input' ) );
			selected.group.open = true;
		}
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
		showStep( 'who' );
	} else {
		showStep( 'pick' );
	}
} )();
