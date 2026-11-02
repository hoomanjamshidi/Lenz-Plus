/**
 * Lenz Plus — course widgets.
 *
 * The phone buy bar is hidden without JavaScript (the buy box does the same
 * job); this script moves it to the bottom of the screen and shows it while
 * neither the buy box's button nor the enroll form is on screen, so it is
 * there from the top of the page and steps aside where you can act anyway.
 * The intro video dialog lives in video.js.
 */
( function () {
	'use strict';

	const doc = document;

	/* ---------------------------------------------------------------------
	 * Phone buy bar: shown while the buy box and enroll form are off screen
	 * ------------------------------------------------------------------- */

	function initBuyBar( bar ) {
		// Out of any transformed or sticky ancestor, so `position: fixed` is the viewport.
		doc.body.appendChild( bar );
		doc.body.classList.add( 'lzp-buybar-on', bar.classList.contains( 'lzp-buybar--tablet' ) ? 'lzp-buybar-on--tablet' : 'lzp-buybar-on--mobile' );

		const targets = Array.from( doc.querySelectorAll( '[data-lzp-buy], #enroll' ) ).filter( ( el ) => el.offsetParent !== null );
		if ( ! targets.length || ! window.IntersectionObserver ) {
			bar.classList.add( 'is-visible' );
			return;
		}

		const onScreen = new Set();
		const observer = new IntersectionObserver( ( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					onScreen.add( entry.target );
				} else {
					onScreen.delete( entry.target );
				}
			} );
			bar.classList.toggle( 'is-visible', onScreen.size === 0 );
		} );
		targets.forEach( ( el ) => observer.observe( el ) );
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
