/**
 * Lenz Plus — photo preview.
 *
 * Project photos are links to their full-size file (`a[data-lzp-lightbox]`,
 * the value names the set): without JavaScript, or without <dialog>
 * support, a tap simply opens the photo. This script shows it full screen in
 * a modal <dialog> instead, with previous/next buttons, a counter, arrow
 * keys and swipes that go through every photo of the same set (all the
 * gallery widgets of one project). The dialog is removed on close and focus
 * goes back to the photo that opened it.
 *
 * Data from PHP: window.lzpLightbox { persian, i18n { close, previous, next, label } }.
 */
( function () {
	'use strict';

	const doc = document;
	const config = window.lzpLightbox || {};
	const i18n = config.i18n || {};
	const PERSIAN = '۰۱۲۳۴۵۶۷۸۹';
	// Chevron pointing to the inline end in LTR; CSS mirrors it in RTL.
	const CHEVRON = '<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m9 6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

	function digits( number ) {
		const text = String( number );
		return config.persian ? text.replace( /\d/g, ( digit ) => PERSIAN[ digit ] ) : text;
	}

	function isRtl() {
		return getComputedStyle( doc.documentElement ).direction === 'rtl';
	}

	function button( className, label, html ) {
		const node = doc.createElement( 'button' );
		node.type = 'button';
		node.className = 'lzp-lightbox__btn ' + className;
		node.setAttribute( 'aria-label', label || '' );
		node.innerHTML = html;
		return node;
	}

	function open( link ) {
		if ( typeof HTMLDialogElement !== 'function' ) {
			return false;
		}

		const links = Array.from( doc.querySelectorAll( 'a[data-lzp-lightbox]' ) ).filter( ( node ) => node.dataset.lzpLightbox === link.dataset.lzpLightbox );
		let index = Math.max( 0, links.indexOf( link ) );

		const dialog = doc.createElement( 'dialog' );
		dialog.className = 'lzp-lightbox';
		dialog.setAttribute( 'aria-label', i18n.label || '' );

		const stage = doc.createElement( 'figure' );
		stage.className = 'lzp-lightbox__stage';
		const image = doc.createElement( 'img' );
		image.className = 'lzp-lightbox__img';
		image.decoding = 'async';
		const caption = doc.createElement( 'figcaption' );
		caption.className = 'lzp-lightbox__caption';
		stage.append( image, caption );

		const close = button( 'lzp-lightbox__close', i18n.close, '<span aria-hidden="true">×</span>' );
		const prev = button( 'lzp-lightbox__prev', i18n.previous, CHEVRON );
		const next = button( 'lzp-lightbox__next', i18n.next, CHEVRON );
		const count = doc.createElement( 'p' );
		count.className = 'lzp-lightbox__count';
		count.setAttribute( 'aria-live', 'polite' );

		const single = links.length < 2;
		prev.hidden = single;
		next.hidden = single;
		count.hidden = single;

		dialog.append( stage, close, prev, next, count );

		function preload( at ) {
			const neighbour = links[ ( at + links.length ) % links.length ];
			if ( neighbour ) {
				( new Image() ).src = neighbour.href;
			}
		}

		function show( at ) {
			index = ( at + links.length ) % links.length;
			const current = links[ index ];
			const thumb = current.querySelector( 'img' );

			dialog.classList.add( 'is-loading' );
			image.alt = thumb ? thumb.alt : '';
			image.src = current.href;
			caption.textContent = current.dataset.caption || '';
			caption.hidden = ! caption.textContent;
			count.textContent = digits( index + 1 ) + ' / ' + digits( links.length );

			if ( ! single ) {
				preload( index + 1 );
				preload( index - 1 );
			}
		}

		image.addEventListener( 'load', () => dialog.classList.remove( 'is-loading' ) );
		image.addEventListener( 'error', () => dialog.classList.remove( 'is-loading' ) );

		close.addEventListener( 'click', () => dialog.close() );
		prev.addEventListener( 'click', () => show( index - 1 ) );
		next.addEventListener( 'click', () => show( index + 1 ) );

		let startX = null;
		let swiped = false;

		// A tap beside the photo closes, as on the video dialog (not the click that ends a swipe).
		dialog.addEventListener( 'click', ( event ) => {
			if ( swiped ) {
				swiped = false;
				return;
			}
			if ( event.target === dialog || event.target === stage ) {
				dialog.close();
			}
		} );

		dialog.addEventListener( 'keydown', ( event ) => {
			if ( single ) {
				return;
			}

			// The arrow that points to the reading direction's end goes forward.
			const forward = isRtl() ? 'ArrowLeft' : 'ArrowRight';
			const backward = isRtl() ? 'ArrowRight' : 'ArrowLeft';
			if ( event.key === forward ) {
				show( index + 1 );
			} else if ( event.key === backward ) {
				show( index - 1 );
			} else if ( event.key === 'Home' ) {
				show( 0 );
			} else if ( event.key === 'End' ) {
				show( links.length - 1 );
			} else {
				return;
			}
			event.preventDefault();
		} );

		stage.addEventListener( 'pointerdown', ( event ) => {
			startX = event.isPrimary ? event.clientX : null;
			swiped = false;
		} );
		stage.addEventListener( 'pointerup', ( event ) => {
			if ( startX === null || single ) {
				return;
			}

			const moved = event.clientX - startX;
			startX = null;
			if ( Math.abs( moved ) > 40 ) {
				swiped = true;
				// Swiping towards the reading direction's start brings the next photo.
				show( index + ( ( moved < 0 ) !== isRtl() ? 1 : -1 ) );
			}
		} );

		dialog.addEventListener( 'close', () => {
			dialog.remove();
			doc.documentElement.classList.remove( 'lzp-lightbox-open' );
			links[ index ].focus();
		} );

		doc.body.appendChild( dialog );
		doc.documentElement.classList.add( 'lzp-lightbox-open' );
		show( index );
		dialog.showModal();
		return true;
	}

	doc.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( 'a[data-lzp-lightbox]' );
		// In Elementor's editor a click selects the widget.
		if ( ! link || event.ctrlKey || event.metaKey || event.shiftKey || doc.body.classList.contains( 'elementor-editor-active' ) ) {
			return;
		}

		if ( open( link ) ) {
			event.preventDefault();
		}
	} );
}() );
