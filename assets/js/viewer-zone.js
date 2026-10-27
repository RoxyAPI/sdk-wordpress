/**
 * Preselects the visitor's own timezone in an empty timezone dropdown marked
 * `data-roxyapi-viewer-zone`, so a reading for "now" follows the visitor's
 * clock. The visitor can still pick another zone. Without JavaScript, or when
 * the browser zone is not in the list, the field stays empty and the server
 * sends the site timezone instead.
 */
( function () {
	'use strict';

	function init() {
		let zone = '';
		try {
			zone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
		} catch ( e ) {
			return;
		}
		if ( ! zone ) {
			return;
		}
		document
			.querySelectorAll( 'select[data-roxyapi-viewer-zone]' )
			.forEach( function ( select ) {
				const option = listed( select, zone );
				if ( select.value === '' && option ) {
					select.value = option.value;
				}
			} );
	}

	// Browsers may report a legacy alias (Asia/Calcutta) for a zone the list
	// carries under its current name (Asia/Kolkata), so match on what each
	// listed name resolves to as well.
	function listed( select, zone ) {
		const options = Array.prototype.slice.call( select.options );
		return (
			options.find( function ( option ) {
				return option.value === zone;
			} ) ||
			options.find( function ( option ) {
				try {
					return (
						option.value.indexOf( '/' ) > 0 &&
						Intl.DateTimeFormat( 'en', {
							timeZone: option.value,
						} ).resolvedOptions().timeZone === zone
					);
				} catch ( e ) {
					return false;
				}
			} )
		);
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
