/**
 * Lenz Plus — request, sign-up and project booking forms.
 *
 * The forms post to admin-post.php and work on their own; this sends them
 * through admin-ajax.php instead, marks the fields the server rejected and
 * shows the "done" panel in place of the fields. The booking form
 * (`data-lzp-booking`) is also turned into steps: one fieldset at a time,
 * each checked before the next, a review of the answers before sending.
 * Configuration comes from PHP (`window.lzpForms`: ajaxUrl, Persian digits
 * and the messages).
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
		const inputs = () => form.querySelectorAll( '.lzp-form__input, .lzp-form__choice' );

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
			// A booking sent before its last step (Enter on a choice): move on instead.
			if ( form.classList.contains( 'is-stepped' ) && ! form.classList.contains( 'is-last' ) ) {
				event.preventDefault();
				form.querySelector( '[data-lzp-step-next]' ).click();
				return;
			}
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
				const code = form.querySelector( '[data-lzp-form-code]' );
				if ( code && result.code ) {
					code.textContent = result.code;
					code.parentElement.hidden = false;
				}
				form.setAttribute( 'data-lzp-sent', '' );
				done.hidden = false;
				done.focus();
				return;
			}

			say( i18n[ result.status ] || i18n.error || '' );
			if ( result.status === 'invalid' && Array.isArray( result.fields ) ) {
				markInvalid( result.fields );
			}
			// The booking steps open the step holding the first rejected field.
			form.dispatchEvent( new CustomEvent( 'lzp:form-result', { detail: result } ) );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Booking steps
	 * ------------------------------------------------------------------- */

	const digits = ( value ) => ( config.persian ? String( value ).replace( /\d/g, ( d ) => '۰۱۲۳۴۵۶۷۸۹'[ d ] ) : String( value ) );
	const reduceMotion = () => window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function initBooking( form ) {
		const steps = Array.from( form.querySelectorAll( '.lzp-book__step' ) );
		const back = form.querySelector( '[data-lzp-step-back]' );
		const next = form.querySelector( '[data-lzp-step-next]' );
		if ( steps.length < 2 || ! back || ! next ) {
			return;
		}

		const dots = Array.from( form.querySelectorAll( '[data-lzp-step-dot]' ) );
		const count = form.querySelector( '[data-lzp-step-count]' );
		const review = form.querySelector( '[data-lzp-review]' );
		const summary = form.querySelector( '[data-lzp-summary]' );
		const last = steps.length - 1;
		let current = 0;

		form.querySelectorAll( '.lzp-book__progress, .lzp-book__bar' ).forEach( ( el ) => {
			el.hidden = false;
		} );
		back.hidden = false;
		next.hidden = false;
		form.classList.add( 'is-stepped' );

		const fieldsOf = ( step ) => Array.from( step.querySelectorAll( 'input, select, textarea' ) ).filter( ( field ) => field.type !== 'hidden' );

		// The visitor's answers so far: [{ step, label, value }].
		const answers = () => {
			const out = [];
			steps.forEach( ( step, index ) => {
				fieldsOf( step ).forEach( ( field ) => {
					if ( ( field.type === 'radio' && ! field.checked ) || ( field.type === 'checkbox' && ! field.name ) ) {
						return;
					}
					const value = field.tagName === 'SELECT' ? ( field.value ? field.options[ field.selectedIndex ].text : '' ) : field.value.trim();
					if ( value ) {
						out.push( { step: index, label: field.getAttribute( 'data-label' ) || '', value } );
					}
				} );
			} );
			return out;
		};

		// Writes answers into a <dl>; the review adds an "edit" button to each step's first answer.
		const fill = ( list, items, editable ) => {
			list.textContent = '';
			items.forEach( ( item, index ) => {
				const term = doc.createElement( 'dt' );
				const detail = doc.createElement( 'dd' );
				term.textContent = item.label;
				detail.textContent = item.value;
				if ( editable && ( index === 0 || items[ index - 1 ].step !== item.step ) ) {
					const edit = doc.createElement( 'button' );
					edit.type = 'button';
					edit.className = 'lzp-book__edit';
					edit.setAttribute( 'data-lzp-goto', String( item.step ) );
					edit.textContent = i18n.edit || 'Edit';
					edit.setAttribute( 'aria-label', ( i18n.edit || 'Edit' ) + ': ' + item.label );
					detail.append( ' ', edit );
				}
				list.append( term, detail );
			} );
		};

		const show = ( index, focus ) => {
			current = Math.max( 0, Math.min( last, index ) );
			steps.forEach( ( step, i ) => step.classList.toggle( 'is-current', i === current ) );
			dots.forEach( ( dot ) => {
				const i = Number( dot.getAttribute( 'data-lzp-step-dot' ) );
				dot.classList.toggle( 'is-current', i === current );
				dot.classList.toggle( 'is-done', i < current );
			} );
			form.style.setProperty( '--lzp-book-progress', String( ( current + 1 ) / steps.length ) );
			form.classList.toggle( 'is-first', current === 0 );
			form.classList.toggle( 'is-last', current === last );
			if ( count ) {
				count.textContent = ( i18n.step || '%1$s / %2$s' ).replace( '%1$s', digits( current + 1 ) ).replace( '%2$s', digits( steps.length ) );
			}
			if ( review && current === last ) {
				fill( review, answers().filter( ( item ) => item.step < last ), true );
			}
			if ( focus ) {
				const legend = steps[ current ].querySelector( '.lzp-book__legend' );
				const top = form.getBoundingClientRect().top;
				if ( top < 0 || top > window.innerHeight * 0.6 ) {
					form.scrollIntoView( { block: 'start', behavior: reduceMotion() ? 'auto' : 'smooth' } );
				}
				if ( legend ) {
					legend.focus( { preventScroll: true } );
				}
			}
		};

		// The browser's own checks (required, email…), one step at a time.
		const stepIsValid = ( step ) => {
			const fields = fieldsOf( step );
			for ( const field of fields ) {
				if ( ! field.checkValidity() ) {
					fields.filter( ( other ) => other.name && other.name === field.name ).forEach( ( other ) => other.setAttribute( 'aria-invalid', 'true' ) );
					field.reportValidity();
					return false;
				}
			}
			return true;
		};

		next.addEventListener( 'click', () => {
			if ( stepIsValid( steps[ current ] ) ) {
				show( current + 1, true );
			}
		} );
		back.addEventListener( 'click', () => show( current - 1, true ) );

		// Enter moves on instead of sending half a booking.
		form.addEventListener( 'keydown', ( event ) => {
			const target = event.target;
			if ( event.key === 'Enter' && current < last && target.tagName === 'INPUT' && target.type !== 'checkbox' && target.type !== 'radio' ) {
				event.preventDefault();
				next.click();
			}
		} );

		form.addEventListener( 'click', ( event ) => {
			const edit = event.target.closest( '[data-lzp-goto]' );
			if ( edit ) {
				show( Number( edit.getAttribute( 'data-lzp-goto' ) ), true );
			}
		} );

		// A chosen card or pill clears its mark; the panel's summary follows the answers.
		form.addEventListener( 'change', ( event ) => {
			if ( event.target.type === 'radio' ) {
				form.querySelectorAll( '[name="' + event.target.name + '"]' ).forEach( ( radio ) => radio.removeAttribute( 'aria-invalid' ) );
			}
			if ( summary ) {
				const items = answers();
				summary.hidden = items.length === 0;
				fill( summary, items, false );
			}
		} );

		form.addEventListener( 'lzp:form-result', ( event ) => {
			const result = event.detail || {};
			if ( result.status === 'ok' ) {
				show( 0, false );
				if ( summary ) {
					summary.hidden = true;
				}
				return;
			}
			const invalid = form.querySelector( '[aria-invalid="true"]' );
			const step = invalid && invalid.closest( '.lzp-book__step' );
			if ( step ) {
				show( steps.indexOf( step ), false );
				invalid.focus();
			}
		} );

		show( 0, false );
	}

	/* ---------------------------------------------------------------------
	 * Boot (the page, and each widget the Elementor editor re-renders)
	 * ------------------------------------------------------------------- */

	function initScope( scope ) {
		scope.querySelectorAll( '[data-lzp-form]' ).forEach( ( form ) => {
			if ( ! form.hasAttribute( 'data-lzp-ready' ) ) {
				form.setAttribute( 'data-lzp-ready', '' );
				initForm( form );
				if ( form.hasAttribute( 'data-lzp-booking' ) ) {
					initBooking( form );
				}
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
