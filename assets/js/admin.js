/*
 * RoxyAPI admin script.
 *
 * Handles the Test Connection button and dismissal persistence for the
 * onboarding admin notice. Uses wp.apiFetch for all REST calls.
 */

( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		wireTestConnection();
		wireNoticeDismiss();
	} );

	function wireTestConnection() {
		var button = document.querySelector( '[data-roxyapi-test-connection]' );
		if ( ! button ) {
			return;
		}
		button.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			var result = document.querySelector( '.roxyapi-test-connection-result' );
			if ( result ) {
				result.textContent = 'Testing...';
				result.className = 'roxyapi-test-connection-result';
			}
			if ( ! window.wp || ! window.wp.apiFetch ) {
				return;
			}
			wp.apiFetch( { path: '/roxyapi/v1/test-key', method: 'GET' } )
				.then( function ( data ) {
					if ( result ) {
						result.textContent = data && data.ok ? 'Connected' : ( data && data.message ) || 'Failed';
						result.className = 'roxyapi-test-connection-result ' + ( data && data.ok ? 'is-success' : 'is-error' );
					}
				} )
				.catch( function ( err ) {
					if ( result ) {
						result.textContent = ( err && err.message ) || 'Request failed';
						result.className = 'roxyapi-test-connection-result is-error';
					}
				} );
		} );
	}

	function wireNoticeDismiss() {
		var notice = document.getElementById( 'roxyapi-setup-notice' );
		if ( ! notice ) {
			return;
		}
		notice.addEventListener( 'click', function ( event ) {
			if ( ! event.target.classList.contains( 'notice-dismiss' ) ) {
				return;
			}
			if ( ! window.wp || ! window.wp.apiFetch ) {
				return;
			}
			wp.apiFetch( {
				path: '/roxyapi/v1/dismiss-notice',
				method: 'POST',
			} ).catch( function () {
				// Silent: the notice is already dismissed for this page view.
			} );
		} );
	}
} )();
