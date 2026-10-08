/**
 * Lenz Plus — course widgets.
 *
 * Both enhance markup that works on its own: the play button links to the
 * intro video (this opens it in a <dialog> instead), and the phone buy bar
 * is hidden without JavaScript (the buy box above does the same job).
 */
( function () {
	'use strict';

	const doc = document;
	const i18n = ( window.lzpCourses && window.lzpCourses.i18n ) || {};

	/* ---------------------------------------------------------------------
	 * Intro video in a dialog
	 * ------------------------------------------------------------------- */

	function videoEmbed( url ) {
		if ( /\.(mp4|webm|ogv|mov)(\?|$)/i.test( url ) ) {
			const video = doc.createElement( 'video' );
			video.src = url;
			video.controls = true;
			video.autoplay = true;
			video.playsInline = true;
			return video;
		}

		const frame = doc.createElement( 'iframe' );
		frame.src = url;
		frame.allow = 'autoplay; fullscreen; picture-in-picture';
		frame.allowFullscreen = true;
		return frame;
	}

	function openVideo( link ) {
		if ( typeof HTMLDialogElement !== 'function' ) {
			return false;
		}

		const dialog = doc.createElement( 'dialog' );
		dialog.className = 'lzp-video-dialog';
		dialog.setAttribute( 'aria-label', link.dataset.title || '' );

		const close = doc.createElement( 'button' );
		close.type = 'button';
		close.className = 'lzp-video-dialog__close';
		close.setAttribute( 'aria-label', i18n.close || 'Close' );
		close.textContent = '×';

		dialog.append( close, videoEmbed( link.dataset.lzpVideo ) );
		doc.body.appendChild( dialog );

		// Closing removes the player, so the video stops and focus returns to the button.
		dialog.addEventListener( 'close', () => {
			dialog.remove();
			link.focus();
		} );
		close.addEventListener( 'click', () => dialog.close() );
		dialog.addEventListener( 'click', ( event ) => {
			if ( event.target === dialog ) {
				dialog.close();
			}
		} );

		dialog.showModal();
		return true;
	}

	doc.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( '[data-lzp-video]' );
		if ( link && openVideo( link ) ) {
			event.preventDefault();
		}
	} );

	/* ---------------------------------------------------------------------
	 * Phone buy bar: shown once the buy box scrolls away
	 * ------------------------------------------------------------------- */

	function initBuyBar( bar ) {
		// Out of any transformed or sticky ancestor, so `position: fixed` is the viewport.
		doc.body.appendChild( bar );
		doc.body.classList.add( 'lzp-buybar-on', bar.classList.contains( 'lzp-buybar--tablet' ) ? 'lzp-buybar-on--tablet' : 'lzp-buybar-on--mobile' );

		const target = Array.from( doc.querySelectorAll( '[data-lzp-buy]' ) ).find( ( el ) => el.offsetParent !== null );
		if ( ! target || ! window.IntersectionObserver ) {
			const onScroll = () => bar.classList.toggle( 'is-visible', window.scrollY > 320 );
			window.addEventListener( 'scroll', onScroll, { passive: true } );
			onScroll();
			return;
		}

		new IntersectionObserver( ( entries ) => {
			entries.forEach( ( entry ) => {
				bar.classList.toggle( 'is-visible', ! entry.isIntersecting && entry.boundingClientRect.top < 0 );
			} );
		} ).observe( target );
	}

	function init() {
		const bar = doc.querySelector( '[data-lzp-buybar]:not(.is-editor)' );
		if ( bar && ! bar.hasAttribute( 'data-lzp-ready' ) ) {
			bar.setAttribute( 'data-lzp-ready', '' );
			initBuyBar( bar );
		}
	}

	if ( doc.readyState === 'loading' ) {
		doc.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
