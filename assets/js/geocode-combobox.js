/**
 * Roxy geocode combobox — ARIA 1.2 editable combobox with list autocomplete.
 *
 * Selector: any element with `data-roxyapi-geocode` whose value is a CSS
 * selector pointing at the section or form holding the location controls.
 * `data-roxyapi-geocode-fields` maps latitude, longitude and timezone to their
 * exact posted names. Selecting a city fills only those controls; they remain
 * visible so the visitor can enter or edit coordinates without JavaScript.
 * `data-roxyapi-geocode-quiet` keeps the status as descriptive text instead of
 * a live region on screens such as the builder that own one shared announcement.
 *
 * Implements the WAI-ARIA APG combobox-autocomplete-list pattern:
 *   role="combobox", aria-autocomplete="list", aria-expanded, aria-controls,
 *   aria-activedescendant + a sibling listbox of options.
 *
 * The listbox and its options are divs carrying role="listbox" / role="option",
 * not ul/li. The roles are what assistive technology reads, and a div is not
 * matched by the ul and li element rules that many themes ship, which otherwise
 * win over the plugin stylesheet and leave the dropdown bulleted, mispadded and
 * transparent over the fields below it.
 *
 * Vanilla, ~3 KB, no dependencies. Fail-soft: if the script never loads or
 * the network is down, the manual lat/lon/tz inputs remain fully usable.
 */
( function () {
	'use strict';

	const DEBOUNCE_MS = 250;
	const MIN_QUERY_LEN = 2;
	const settings = window.RoxyAPIGeocode || {};
	const REST_URL = settings.restUrl || '';
	const I18N = settings.i18n || {};

	function debounce( fn, wait ) {
		let t = null;
		return function () {
			const ctx = this;
			const args = arguments;
			window.clearTimeout( t );
			t = window.setTimeout( function () {
				fn.apply( ctx, args );
			}, wait );
		};
	}

	function setStatus( statusEl, msg ) {
		if ( statusEl ) {
			statusEl.textContent = msg || '';
		}
	}

	function clearListbox( listbox ) {
		while ( listbox.firstChild ) {
			listbox.removeChild( listbox.firstChild );
		}
	}

	function renderResults( listbox, results, onPick ) {
		clearListbox( listbox );
		if ( ! results || results.length === 0 ) {
			return;
		}
		results.forEach( function ( city, index ) {
			const option = document.createElement( 'div' );
			option.id = listbox.id + '-opt-' + index;
			option.setAttribute( 'role', 'option' );
			option.className = 'roxyapi-geocode-option';
			option.textContent = city.label;
			option.addEventListener( 'mousedown', function ( e ) {
				// mousedown so the click resolves before the input blurs.
				e.preventDefault();
				onPick( city );
			} );
			listbox.appendChild( option );
		} );
	}

	function findField( section, name ) {
		return Array.prototype.find.call(
			section.querySelectorAll( 'input[name], select[name]' ),
			function ( field ) {
				return field.name === name;
			}
		);
	}

	function applyCity( section, fields, city ) {
		const lat = findField( section, fields.latitude );
		const lon = findField( section, fields.longitude );
		const tz = findField( section, fields.timezone );
		if ( lat ) {
			lat.value = String( city.lat );
		}
		if ( lon ) {
			lon.value = String( city.lon );
		}
		if ( tz ) {
			tz.value = city.tz;
		}
	}

	function attach( input ) {
		const sectionSelector = input.getAttribute( 'data-roxyapi-geocode' );
		if ( ! sectionSelector ) {
			return;
		}
		const section = document.querySelector( sectionSelector );
		if ( ! section ) {
			return;
		}

		let fields;
		try {
			fields = JSON.parse(
				input.getAttribute( 'data-roxyapi-geocode-fields' )
			);
		} catch ( e ) {
			return;
		}
		if ( ! fields || typeof fields !== 'object' ) {
			return;
		}

		const listboxId = input.id + '-listbox';
		const statusId = input.id + '-status';

		const listbox = document.createElement( 'div' );
		listbox.id = listboxId;
		listbox.className = 'roxyapi-geocode-listbox';
		listbox.setAttribute( 'role', 'listbox' );
		listbox.setAttribute( 'hidden', '' );
		input.parentNode.insertBefore( listbox, input.nextSibling );

		const status = document.createElement( 'span' );
		status.id = statusId;
		status.className = 'roxyapi-geocode-status';
		if ( input.hasAttribute( 'data-roxyapi-geocode-quiet' ) ) {
			input.setAttribute( 'aria-describedby', statusId );
		} else {
			status.setAttribute( 'role', 'status' );
			status.setAttribute( 'aria-live', 'polite' );
		}
		input.parentNode.insertBefore( status, listbox.nextSibling );

		input.setAttribute( 'role', 'combobox' );
		input.setAttribute( 'aria-autocomplete', 'list' );
		input.setAttribute( 'aria-controls', listboxId );
		input.setAttribute( 'aria-expanded', 'false' );
		input.setAttribute( 'autocomplete', 'off' );

		let activeIndex = -1;
		let current = [];

		function open() {
			if ( current.length === 0 ) {
				return;
			}
			listbox.removeAttribute( 'hidden' );
			input.setAttribute( 'aria-expanded', 'true' );
		}

		function close() {
			listbox.setAttribute( 'hidden', '' );
			input.setAttribute( 'aria-expanded', 'false' );
			input.removeAttribute( 'aria-activedescendant' );
			activeIndex = -1;
			Array.prototype.forEach.call(
				listbox.querySelectorAll( '[aria-selected="true"]' ),
				function ( el ) {
					el.removeAttribute( 'aria-selected' );
				}
			);
		}

		// Drop every result and collapse. Without this an error after a good
		// query leaves stale options behind that arrow keys can still select.
		function reset() {
			current = [];
			clearListbox( listbox );
			close();
		}

		function setActive( index ) {
			const options = listbox.querySelectorAll( '[role="option"]' );
			if ( options.length === 0 ) {
				return;
			}
			if ( index < 0 ) {
				index = options.length - 1;
			}
			if ( index >= options.length ) {
				index = 0;
			}
			Array.prototype.forEach.call( options, function ( opt ) {
				opt.removeAttribute( 'aria-selected' );
			} );
			const target = options[ index ];
			target.setAttribute( 'aria-selected', 'true' );
			input.setAttribute( 'aria-activedescendant', target.id );
			activeIndex = index;
			// scrollIntoView because aria-activedescendant doesn't move browser focus.
			if ( typeof target.scrollIntoView === 'function' ) {
				target.scrollIntoView( { block: 'nearest' } );
			}
		}

		function pick( city ) {
			input.value = city.label;
			applyCity( section, fields, city );
			close();
			setStatus( status, I18N.selected || '' );
		}

		const fetchResults = debounce( function ( q ) {
			if ( ! REST_URL ) {
				return;
			}
			setStatus( status, I18N.searching || '' );
			const url =
				REST_URL +
				( REST_URL.indexOf( '?' ) === -1 ? '?' : '&' ) +
				'q=' +
				encodeURIComponent( q );
			window
				.fetch( url, { credentials: 'same-origin' } )
				.then( function ( res ) {
					return res.json().then( function ( data ) {
						return { ok: res.ok, data: data || {} };
					} );
				} )
				.then( function ( payload ) {
					const data = payload.data;
					// The route answers a refusal with a translated message.
					// Show it, otherwise a visitor who is being throttled sees
					// an empty dropdown and no reason for it.
					const refusal =
						data.error && typeof data.error.message === 'string'
							? data.error.message
							: '';
					if ( ! payload.ok || refusal ) {
						reset();
						setStatus( status, refusal || I18N.error || '' );
						return;
					}
					current = Array.isArray( data.cities ) ? data.cities : [];
					renderResults( listbox, current, pick );
					if ( current.length === 0 ) {
						close();
						setStatus( status, I18N.noResults || '' );
					} else {
						open();
						setStatus( status, '' );
					}
				} )
				.catch( function () {
					// Only the request itself failing or a body that is not
					// JSON lands here, which is a different problem from a
					// server answer the visitor can act on.
					reset();
					setStatus( status, I18N.error || '' );
				} );
		}, DEBOUNCE_MS );

		input.addEventListener( 'input', function () {
			const q = input.value.trim();
			if ( q.length < MIN_QUERY_LEN ) {
				reset();
				setStatus( status, '' );
				return;
			}
			fetchResults( q );
		} );

		input.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowDown' ) {
				e.preventDefault();
				if ( listbox.hasAttribute( 'hidden' ) ) {
					open();
				}
				setActive( activeIndex + 1 );
			} else if ( e.key === 'ArrowUp' ) {
				e.preventDefault();
				if ( listbox.hasAttribute( 'hidden' ) ) {
					open();
				}
				setActive( activeIndex - 1 );
			} else if ( e.key === 'Enter' ) {
				if ( activeIndex >= 0 && current[ activeIndex ] ) {
					e.preventDefault();
					pick( current[ activeIndex ] );
				}
			} else if ( e.key === 'Escape' ) {
				close();
			}
		} );

		input.addEventListener( 'blur', function () {
			// Slight delay so a click on the listbox can resolve first.
			window.setTimeout( close, 150 );
		} );
	}

	function init() {
		const inputs = document.querySelectorAll( '[data-roxyapi-geocode]' );
		Array.prototype.forEach.call( inputs, attach );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
