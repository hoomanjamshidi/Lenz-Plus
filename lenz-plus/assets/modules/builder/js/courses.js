/**
 * Lenz Plus — course widgets.
 *
 * The phone buy bar is hidden without JavaScript (the buy box above does the
 * same job); this script moves it to the bottom of the screen and shows it
 * once the buy box scrolls away. The intro video dialog lives in video.js.
 */
( function () {
	'use strict';

	const doc = document;

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
