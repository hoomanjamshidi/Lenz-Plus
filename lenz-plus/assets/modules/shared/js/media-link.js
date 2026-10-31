/**
 * Lenz Plus — pasted video links (window.lzpMediaLink).
 *
 * Mirrors Builder\Media_Link::normalize() for previews while typing: an
 * iframe or script embed code becomes the address inside it, and every
 * Aparat address becomes the video's page link (the form PHP stores). PHP
 * cleans the value again on save. Used by the studio panel's media field and
 * by Lenz's gallery box on the portfolio editor.
 */
( function () {
	'use strict';

	const APARAT = /^(?:https?:)?\/\/(?:www\.)?aparat\.com\/(?:v|embed|video\/video\/embed\/videohash)\/([A-Za-z0-9]+)/i;
	const IMAGE = /\.(jpe?g|png|gif|webp|avif|svg)(\?|#|$)/i;
	const VIDEO_FILE = /\.(mp4|webm|ogv|mov|m4v)(\?|#|$)/i;

	function normalize( value ) {
		let url = String( value || '' ).trim();
		const code = url.match( /<(?:iframe|script)\b[^>]*\bsrc\s*=\s*["']?([^"'\s>]+)/i );
		if ( code ) {
			url = code[ 1 ].replace( /&amp;/g, '&' );
		}
		if ( url.indexOf( '//' ) === 0 ) {
			url = 'https:' + url;
		} else if ( /^(?:www\.)?[a-z0-9-]+(?:\.[a-z0-9-]+)+\//i.test( url ) ) {
			// Typed without a scheme (aparat.com/v/…).
			url = 'https://' + url;
		}

		const aparat = url.match( APARAT );
		return aparat ? 'https://www.aparat.com/v/' + aparat[ 1 ] : url;
	}

	window.lzpMediaLink = {
		normalize,
		isAparat: ( url ) => APARAT.test( url ),
		isImage: ( url ) => IMAGE.test( url ),
		isVideoFile: ( url ) => VIDEO_FILE.test( url ),
		isUrl: ( url ) => /^https?:\/\/[^\s/]+\.[^\s]+$/i.test( url ),
	};
}() );
