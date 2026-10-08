/**
 * Lenz Plus — video dialog.
 *
 * Play buttons are real links to the video (`[data-lzp-video]`): without
 * JavaScript, or without <dialog> support, they simply open it. This script
 * plays it in a modal <dialog> instead. Used by the home hero and the
 * course hero.
 */
( function () {
	'use strict';

	const doc = document;
	const i18n = ( window.lzpVideo && window.lzpVideo.i18n ) || {};

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
}() );
