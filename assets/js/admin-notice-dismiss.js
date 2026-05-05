/*
 * Persists the RoxyAPI onboarding notice dismissal via admin-ajax.
 * Loaded only on screens where the notice is shown.
 */
( function () {
	'use strict';
	document.addEventListener( 'click', function ( event ) {
		const target = event.target;
		if (
			! target ||
			! target.matches( '#roxyapi-setup-notice .notice-dismiss' )
		) {
			return;
		}
		const data = window.RoxyAPINotice;
		if ( ! data || ! data.ajaxUrl ) {
			return;
		}
		const body = new URLSearchParams();
		body.append( 'action', 'roxyapi_dismiss_notice' );
		body.append( '_wpnonce', data.nonce );
		fetch( data.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
		} );
	} );
} )();
