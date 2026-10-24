/**
 * Lenz Plus — click-to-load maps (Map_Embed).
 *
 * The "Show the map" link opens the map in a new tab without JavaScript;
 * here it swaps the placeholder for the embed instead, so the map service's
 * scripts and cookies load only for visitors who want the map.
 */
( function () {
	'use strict';

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest ? event.target.closest( '[data-lzp-map]' ) : null;
		if ( ! button ) {
			return;
		}

		var frame = button.closest( '.lzp-map' );
		if ( ! frame ) {
			return;
		}

		event.preventDefault();

		var iframe = document.createElement( 'iframe' );
		iframe.className = 'lzp-map__frame';
		iframe.src = button.getAttribute( 'data-lzp-map' );
		iframe.title = button.getAttribute( 'data-title' ) || '';
		iframe.loading = 'lazy';
		iframe.referrerPolicy = 'no-referrer-when-downgrade';
		iframe.setAttribute( 'allowfullscreen', '' );

		frame.classList.add( 'is-loaded' );
		frame.appendChild( iframe );
		// Keyboard users land on the map instead of the removed button.
		iframe.setAttribute( 'tabindex', '-1' );
		iframe.focus();
	} );
}() );
