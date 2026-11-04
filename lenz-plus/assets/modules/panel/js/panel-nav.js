/**
 * Lenz+ studio panel: app-like navigation. Links and forms inside the panel
 * load the next page in the background and swap it in place (no white flash,
 * no reload), with a slide or fade through the View Transitions API, a thin
 * progress bar for slow answers, pages fetched as soon as a finger touches a
 * link, and the back button restoring the page and its scroll position.
 *
 * Everything degrades: without fetch/DOMParser/pushState nothing is bound
 * and links load normally; a page that needs other scripts (WordPress's
 * classic editor), an answer that is not a panel page (login, errors) or a
 * failed request falls back to a normal load. After every swap `lzp:page`
 * is fired on document, so panel.js sets up the new page.
 *
 * Data from PHP: window.lzpPanel.nav { base } (the panel's home URL).
 */
( function () {
	'use strict';

	const config = window.lzpPanel || {};
	const i18n = config.i18n || {};
	const nav = config.nav || {};

	if ( ! window.fetch || ! window.DOMParser || ! window.history.pushState || ! nav.base ) {
		return;
	}

	const base = new URL( nav.base, window.location.href );
	const basePath = base.pathname.replace( /\/$/, '' );
	const cache = new Map();
	const CACHE_TTL = 30000;
	const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	let current = 0;
	let bar = null;
	let barTimer = 0;

	/* ---------------------------------------------------------------------
	 * Which URLs belong to the panel
	 * ------------------------------------------------------------------- */

	function isPanelUrl( url ) {
		if ( url.origin !== window.location.origin ) {
			return false;
		}

		if ( url.searchParams.has( 'lzp_panel' ) ) {
			return true;
		}

		return basePath !== '' && ( url.pathname === basePath || url.pathname.indexOf( basePath + '/' ) === 0 );
	}

	/** The panel URL a link leads to, or null when the browser should handle it. */
	function linkUrl( link, event ) {
		if ( ! link || ! link.href || link.hasAttribute( 'download' ) || ( link.target && link.target !== '_self' ) ) {
			return null;
		}

		if ( event && ( event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) ) {
			return null;
		}

		const url = new URL( link.href, window.location.href );
		if ( ! isPanelUrl( url ) ) {
			return null;
		}

		// A jump inside the same page stays a jump.
		if ( url.hash && url.pathname === window.location.pathname && url.search === window.location.search ) {
			return null;
		}

		return url;
	}

	/* ---------------------------------------------------------------------
	 * Fetching
	 * ------------------------------------------------------------------- */

	function load( url, prefetch ) {
		const key = url.toString();
		const hit = cache.get( key );
		if ( hit && Date.now() - hit.time < CACHE_TTL ) {
			return hit.promise;
		}

		const headers = { 'X-LZP-Nav': '1' };
		if ( prefetch ) {
			headers[ 'X-LZP-Prefetch' ] = '1';
		}

		const promise = window.fetch( key, { credentials: 'same-origin', headers } ).then( read );
		cache.set( key, { time: Date.now(), promise } );
		promise.catch( () => cache.delete( key ) );
		return promise;
	}

	function read( response ) {
		return response.text().then( ( html ) => ( { url: response.url, html, ok: response.ok } ) );
	}

	function parse( html ) {
		const doc = new window.DOMParser().parseFromString( html, 'text/html' );
		return doc.querySelector( '.lzp-app' ) ? doc : null;
	}

	/** Script and style files without their version query. */
	function assets( root ) {
		return Array.from( root.querySelectorAll( 'script[src], link[rel="stylesheet"][href]' ) ).map( ( node ) => ( node.src || node.href ).split( '?' )[ 0 ] );
	}

	/** Whether the new page needs files this page did not load (e.g. the classic editor). */
	function needsFullLoad( doc ) {
		const loaded = new Set( assets( document ) );
		return assets( doc ).some( ( file ) => ! loaded.has( file ) ) || !! doc.querySelector( '.wp-editor-wrap' );
	}

	/* ---------------------------------------------------------------------
	 * Progress bar
	 * ------------------------------------------------------------------- */

	function progress( on ) {
		if ( ! bar ) {
			bar = document.createElement( 'div' );
			bar.className = 'lzp-progress';
			bar.setAttribute( 'aria-hidden', 'true' );
			document.body.append( bar );
		}

		window.clearTimeout( barTimer );
		if ( on ) {
			// Fast answers show no bar at all.
			barTimer = window.setTimeout( () => {
				bar.classList.remove( 'is-done' );
				bar.classList.add( 'is-on' );
			}, 150 );
			document.documentElement.setAttribute( 'aria-busy', 'true' );
			return;
		}

		document.documentElement.removeAttribute( 'aria-busy' );
		if ( bar.classList.contains( 'is-on' ) ) {
			bar.classList.add( 'is-done' );
			bar.classList.remove( 'is-on' );
		}
	}

	/* ---------------------------------------------------------------------
	 * Swapping pages
	 * ------------------------------------------------------------------- */

	function render( doc, mode, scroll ) {
		const apply = () => {
			document.title = doc.title;
			document.body.className = doc.body.className;
			document.querySelector( '.lzp-app' ).replaceWith( document.adoptNode( doc.querySelector( '.lzp-app' ) ) );
			window.scrollTo( 0, scroll || 0 );
			document.dispatchEvent( new CustomEvent( 'lzp:page' ) );

			const main = document.getElementById( 'lzp-main' );
			if ( main ) {
				main.focus( { preventScroll: true } );
			}
		};

		if ( ! document.startViewTransition || reduceMotion.matches ) {
			apply();
			return;
		}

		document.documentElement.dataset.lzpNav = mode;
		const transition = document.startViewTransition( apply );
		transition.finished.finally( () => {
			delete document.documentElement.dataset.lzpNav;
		} );
	}

	/** Remembers where the current page was scrolled to. */
	function saveScroll() {
		window.history.replaceState( { lzp: 1, scroll: window.scrollY }, '', window.location.href );
	}

	function leaveAllowed() {
		if ( document.documentElement.dataset.lzpDirty !== '1' ) {
			return true;
		}

		if ( ! window.confirm( i18n.unsaved || '' ) ) {
			return false;
		}

		delete document.documentElement.dataset.lzpDirty;
		return true;
	}

	/**
	 * Shows a page that was loaded (or posted) in the background.
	 *
	 * @param {Promise} answer  Resolves with { url, html }.
	 * @param {string}  mode    forward | back | fade.
	 * @param {boolean} push    Add a history entry (else replace the current one).
	 * @param {number}  scroll  Scroll position to restore.
	 */
	function show( answer, mode, push, scroll ) {
		const token = ++current;
		progress( true );

		return answer
			.then( ( page ) => {
				if ( token !== current ) {
					return;
				}

				const doc = parse( page.html );
				if ( ! doc || needsFullLoad( doc ) ) {
					window.location.assign( page.url );
					return;
				}

				const state = { lzp: 1, scroll: scroll || 0 };
				if ( push && page.url !== window.location.href ) {
					window.history.pushState( state, '', page.url );
				} else {
					window.history.replaceState( state, '', page.url );
				}

				render( doc, mode, scroll );
			} )
			.finally( () => {
				if ( token === current ) {
					progress( false );
				}
			} );
	}

	function go( url, mode ) {
		if ( ! leaveAllowed() ) {
			return;
		}

		saveScroll();
		show( load( url, false ), mode, true, 0 ).catch( () => window.location.assign( url ) );
	}

	/** Lights the tapped tab or menu item at once, before the page arrives. */
	function markCurrent( link ) {
		const group = link.closest( '.lzp-tabbar, .lzp-nav' );
		if ( ! group ) {
			return;
		}

		group.querySelectorAll( '[aria-current]' ).forEach( ( item ) => item.removeAttribute( 'aria-current' ) );
		link.setAttribute( 'aria-current', 'page' );
	}

	function modeOf( link ) {
		if ( link.closest( '.lzp-tabbar, .lzp-side' ) ) {
			return 'fade';
		}

		return link.closest( '.lzp-top' ) && link.classList.contains( 'lzp-icon-btn' ) ? 'back' : 'forward';
	}

	/* ---------------------------------------------------------------------
	 * Events
	 * ------------------------------------------------------------------- */

	document.addEventListener( 'click', ( event ) => {
		if ( event.defaultPrevented ) {
			return;
		}

		const link = event.target.closest( 'a' );
		const url = linkUrl( link, event );
		if ( ! url ) {
			return;
		}

		event.preventDefault();
		markCurrent( link );
		go( url, modeOf( link ) );
	} );

	document.addEventListener( 'submit', ( event ) => {
		// panel.js may hold the form back (uploads still running).
		if ( event.defaultPrevented ) {
			return;
		}

		const form = event.target;
		const action = new URL( form.getAttribute( 'action' ) || window.location.href, window.location.href );
		if ( ! form.closest( '.lzp-app' ) || ! isPanelUrl( action ) ) {
			return;
		}

		if ( window.tinymce && window.tinymce.triggerSave ) {
			window.tinymce.triggerSave();
		}

		let data;
		try {
			data = new FormData( form, event.submitter );
		} catch ( error ) {
			data = new FormData( form );
		}

		event.preventDefault();

		if ( ( form.getAttribute( 'method' ) || 'get' ).toLowerCase() === 'get' ) {
			action.search = new URLSearchParams( data ).toString();
			go( action, 'fade' );
			return;
		}

		// Whatever was cached may have changed now.
		cache.clear();
		saveScroll();
		const answer = window.fetch( action.toString(), { method: 'POST', body: data, credentials: 'same-origin', headers: { 'X-LZP-Nav': '1' } } ).then( read );

		show( answer, 'fade', true, 0 ).catch( () => {
			// The request did not get through: let the browser send the form.
			HTMLFormElement.prototype.submit.call( form );
		} );
	} );

	window.addEventListener( 'popstate', ( event ) => {
		if ( ! event.state || ! event.state.lzp ) {
			return;
		}

		const url = new URL( window.location.href );
		show( load( url, false ), 'back', false, event.state.scroll ).catch( () => window.location.reload() );
	} );

	// Fetch a page as soon as it is likely to be opened: one at a time (each
	// one is a full page render on the server), and never on Save-Data.
	let hoverTimer = 0;
	let prefetching = false;
	const saveData = !! ( navigator.connection && navigator.connection.saveData );
	const prefetch = ( event ) => {
		const link = event.target.closest && event.target.closest( 'a' );
		const url = linkUrl( link, null );
		if ( ! url || saveData || prefetching || link.hasAttribute( 'data-lzp-noprefetch' ) ) {
			return;
		}

		prefetching = true;
		load( url, true ).catch( () => {} ).then( () => {
			prefetching = false;
		} );
	};

	document.addEventListener( 'touchstart', prefetch, { passive: true } );
	document.addEventListener( 'focusin', prefetch );
	document.addEventListener( 'mouseover', ( event ) => {
		window.clearTimeout( hoverTimer );
		hoverTimer = window.setTimeout( () => prefetch( event ), 150 );
	} );

	window.history.replaceState( { lzp: 1, scroll: window.scrollY }, '', window.location.href );
}() );
