/**
 * Lenz Plus — request and sign-up forms.
 *
 * The forms post to admin-post.php and work on their own; this sends them
 * through admin-ajax.php instead, marks the fields the server rejected and
 * shows the "done" panel in place of the fields. Configuration comes from
 * PHP (`window.lzpForms`: ajaxUrl and the error messages).
 */
( function () {
	'use strict';

	const config = window.lzpForms || {};
	const i18n = config.i18n || {};
	const doc = document;

	/**
	 * Server field keys (`0`, `1`… for request forms, `contact` for sign-ups) → input names.
	 *
	 * @param {string|number} key Field key.
	 */
	const fieldName = ( key ) => ( /^\d+$/.test( String( key ) ) ? 'f[' + key + ']' : String( key ) );

	function initForm( form ) {
		const status = form.querySelector( '.lzp-form__status' );
		const done = form.querySelector( '[data-lzp-form-done]' );
		const button = form.querySelector( 'button[type="submit"]' );
		const inputs = () => form.querySelectorAll( '.lzp-form__input' );

		const say = ( message ) => {
			status.textContent = message;
			status.classList.toggle( 'is-error', message !== '' );
		};

		const markInvalid = ( keys ) => {
			const names = keys.map( fieldName );
			inputs().forEach( ( field ) => {
				if ( names.includes( field.name ) ) {
					field.setAttribute( 'aria-invalid', 'true' );
				} else {
					field.removeAttribute( 'aria-invalid' );
				}
			} );
			const first = form.querySelector( '[aria-invalid="true"]' );
			if ( first ) {
				first.focus();
			}
		};

		// A field the visitor corrects is no longer marked.
		form.addEventListener( 'input', ( event ) => {
			if ( event.target.hasAttribute( 'aria-invalid' ) ) {
				event.target.removeAttribute( 'aria-invalid' );
			}
		} );

		form.addEventListener( 'submit', async ( event ) => {
			if ( ! config.ajaxUrl || ! window.fetch ) {
				return;
			}

			event.preventDefault();
			if ( form.classList.contains( 'is-busy' ) ) {
				return;
			}
			form.classList.add( 'is-busy' );
			form.setAttribute( 'aria-busy', 'true' );
			button.disabled = true;
			say( '' );

			let result = { status: 'error' };
			try {
				const response = await fetch( config.ajaxUrl, { method: 'POST', body: new FormData( form ), credentials: 'same-origin' } );
				const json = await response.json();
				result = ( json && json.data ) || { status: json && json.success ? 'ok' : 'error' };
			} catch ( error ) {
				result = { status: 'error' };
			}

			form.classList.remove( 'is-busy' );
			form.removeAttribute( 'aria-busy' );
			button.disabled = false;

			if ( result.status === 'ok' ) {
				form.reset();
				markInvalid( [] );
				form.setAttribute( 'data-lzp-sent', '' );
				done.hidden = false;
				done.focus();
				return;
			}

			say( i18n[ result.status ] || i18n.error || '' );
			if ( result.status === 'invalid' && Array.isArray( result.fields ) ) {
				markInvalid( result.fields );
			}
		} );
	}

	/* ---------------------------------------------------------------------
	 * Boot (the page, and each widget the Elementor editor re-renders)
	 * ------------------------------------------------------------------- */

	function initScope( scope ) {
		scope.querySelectorAll( '[data-lzp-form]' ).forEach( ( form ) => {
			if ( ! form.hasAttribute( 'data-lzp-ready' ) ) {
				form.setAttribute( 'data-lzp-ready', '' );
				initForm( form );
			}
		} );
	}

	if ( doc.readyState === 'loading' ) {
		doc.addEventListener( 'DOMContentLoaded', () => initScope( doc ) );
	} else {
		initScope( doc );
	}

	let hooked = false;
	function hookElementor() {
		if ( hooked || ! window.elementorFrontend || ! window.elementorFrontend.hooks ) {
			return;
		}
		hooked = true;
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', ( $scope ) => {
			if ( $scope && $scope[ 0 ] ) {
				initScope( $scope[ 0 ] );
			}
		} );
	}

	// Elementor announces itself with a jQuery event (native listeners do not see it).
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', hookElementor );
	}
	window.addEventListener( 'elementor/frontend/init', hookElementor );
	hookElementor();
}() );
