/**
 * Lenz+ — video links in Lenz's gallery box (portfolio editor).
 *
 * Lenz's "External media links" fields are `type="url"`, so the browser
 * refuses an Aparat embed code. This script turns a pasted iframe or script
 * code into the link inside it (window.lzpMediaLink, as PHP stores it),
 * switches the row to "Video" for Aparat links and video files, and adds a
 * hint under the box's heading. The rows are Lenz's (added later by its own
 * template), so the listener is on the document.
 *
 * Data from PHP: window.lzpGalleryLinks { hint }.
 */
( function () {
	'use strict';

	const config = window.lzpGalleryLinks || {};
	const links = window.lzpMediaLink;

	document.addEventListener( 'input', ( event ) => {
		const field = event.target;
		if ( ! links || ! field.matches || ! field.matches( '.lenz_portfolio_gallery_link-url' ) ) {
			return;
		}

		const url = links.normalize( field.value );
		if ( url !== field.value ) {
			field.value = url;
		}

		const row = field.closest( '.lenz_portfolio_gallery_link-wrap' );
		const type = row && row.querySelector( '.lenz_portfolio_gallery_link-type' );
		if ( type && ( links.isAparat( url ) || links.isVideoFile( url ) ) ) {
			type.value = 'video';
		}
	} );

	document.addEventListener( 'DOMContentLoaded', () => {
		const heading = document.querySelector( '#lenz_portfolio_gallery_external_link-wrap h3' );
		if ( heading && config.hint ) {
			const hint = document.createElement( 'p' );
			hint.className = 'description';
			hint.textContent = config.hint;
			heading.after( hint );
		}
	} );
}() );
