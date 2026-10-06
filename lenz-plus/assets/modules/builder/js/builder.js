/**
 * Lenz Plus — page templates (front end + Elementor preview).
 *
 * Clicks are handled with delegation on the document, so widgets re-rendered
 * by the Elementor editor keep working without re-binding. Covers the header
 * parts: the menu drawer, dropdown carets, the menu button that opens Lenz's
 * mobile menu (lenz-menu.js) and the sticky header.
 */
( function () {
	'use strict';

	const config = window.lzpBuilder || {};
	const doc = document;
	const root = doc.documentElement;
	const isEditor = () => doc.body && doc.body.classList.contains( 'elementor-editor-active' );

	/* ---------------------------------------------------------------------
	 * Layers (menu drawer)
	 * ------------------------------------------------------------------- */

	let openLayer = null; // { el, trigger }

	function showLayer( el, trigger ) {
		if ( ! el ) {
			return;
		}
		if ( openLayer ) {
			hideLayer();
		}

		// A fixed layer inside the sticky, blurred header would be clipped to it: move it to <body>.
		if ( ! isEditor() && el.parentElement !== doc.body ) {
			doc.body.appendChild( el );
		}

		el.hidden = false;
		openLayer = { el, trigger };
		root.classList.add( 'lzp-lock' );

		if ( trigger ) {
			trigger.setAttribute( 'aria-expanded', 'true' );
		}

		const focusable = el.querySelector( 'a[href], button:not([data-lzp-close])' ) || el.querySelector( 'button' );
		if ( focusable ) {
			window.setTimeout( () => focusable.focus( { preventScroll: true } ), 60 );
		}
	}

	function hideLayer() {
		if ( ! openLayer ) {
			return;
		}

		const { el, trigger } = openLayer;
		openLayer = null;
		el.hidden = true;
		root.classList.remove( 'lzp-lock' );

		if ( trigger ) {
			trigger.setAttribute( 'aria-expanded', 'false' );
			trigger.focus( { preventScroll: true } );
		}
	}

	/** Keeps Tab inside the open layer. */
	function trapFocus( event ) {
		if ( ! openLayer || event.key !== 'Tab' ) {
			return;
		}

		const items = Array.from( openLayer.el.querySelectorAll( 'a[href], button, input, [tabindex]' ) )
			.filter( ( node ) => node.getAttribute( 'tabindex' ) !== '-1' && node.offsetParent !== null );
		if ( ! items.length ) {
			return;
		}

		const first = items[ 0 ];
		const last = items[ items.length - 1 ];

		if ( event.shiftKey && doc.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && doc.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	}

	/* ---------------------------------------------------------------------
	 * Menus: carets open sub-lists (dropdowns on desktop, in place in drawers)
	 * ------------------------------------------------------------------- */

	function closeMenus( except ) {
		doc.querySelectorAll( '.lzp-nav--horizontal .lzp-menu__item.is-open' ).forEach( ( item ) => {
			if ( ! except || ! item.contains( except ) ) {
				item.classList.remove( 'is-open' );
				const caret = item.querySelector( ':scope > .lzp-menu__row > .lzp-menu__caret' );
				if ( caret ) {
					caret.setAttribute( 'aria-expanded', 'false' );
				}
			}
		} );
	}

	function toggleSubmenu( caret ) {
		const item = caret.closest( '.lzp-menu__item' );
		const opening = ! item.classList.contains( 'is-open' );
		if ( opening && ! item.closest( '.lzp-menu--drawer, .lzp-nav--vertical' ) ) {
			closeMenus( item );
		}
		item.classList.toggle( 'is-open', opening );
		caret.setAttribute( 'aria-expanded', String( opening ) );
	}

	/* ---------------------------------------------------------------------
	 * Sticky header (Lenz+ → Page templates → Header & footer)
	 * ------------------------------------------------------------------- */

	function initStickyHeader() {
		const header = doc.querySelector( '.lzp-hf--header' );
		if ( ! header || header.dataset.lzpReady ) {
			return;
		}
		header.dataset.lzpReady = '1';

		const desktopQuery = window.matchMedia( '(min-width: ' + ( ( config.breakpoint || 1024 ) + 1 ) + 'px)' );
		let mode = 'none';
		let lastY = window.scrollY;

		// Sticky columns stop below the header while it is on screen.
		function updateOffset() {
			const shown = mode === 'always' || ( mode === 'scroll_up' && ! header.classList.contains( 'is-hidden' ) );
			root.style.setProperty( '--lzp-header-offset', shown ? header.offsetHeight + 'px' : '0px' );
		}

		function applyMode() {
			mode = header.dataset[ desktopQuery.matches ? 'lzpStickyDesktop' : 'lzpStickyMobile' ] || 'none';
			header.classList.toggle( 'is-sticky', mode !== 'none' );
			if ( mode === 'none' ) {
				header.classList.remove( 'is-hidden' );
			}
			updateOffset();
		}

		function onScroll() {
			const y = window.scrollY;
			if ( mode === 'scroll_up' ) {
				const goingDown = y > lastY && y > header.offsetHeight + 40;
				const hide = goingDown && ! header.contains( doc.activeElement );
				if ( hide !== header.classList.contains( 'is-hidden' ) ) {
					header.classList.toggle( 'is-hidden', hide );
					updateOffset();
				}
			}
			lastY = y;
		}

		desktopQuery.addEventListener( 'change', applyMode );
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		if ( window.ResizeObserver ) {
			new ResizeObserver( updateOffset ).observe( header );
		}
		applyMode();
	}

	/* ---------------------------------------------------------------------
	 * Delegated events
	 * ------------------------------------------------------------------- */

	doc.addEventListener( 'click', ( event ) => {
		const target = event.target instanceof Element ? event.target : null;
		if ( ! target ) {
			return;
		}

		const open = target.closest( '[data-lzp-open]' );
		if ( open ) {
			event.preventDefault();
			showLayer( doc.getElementById( open.dataset.lzpOpen ), open );
			return;
		}

		if ( target.closest( '[data-lzp-close]' ) ) {
			event.preventDefault();
			hideLayer();
			return;
		}

		// Lenz's mobile menu; the theme's own handlers close it.
		if ( target.closest( '[data-lzp-theme-menu]' ) ) {
			event.preventDefault();
			if ( window.lzpLenzMenu ) {
				window.lzpLenzMenu.open();
			}
			return;
		}

		const caret = target.closest( '.lzp-menu__caret' );
		if ( caret ) {
			toggleSubmenu( caret );
			return;
		}

		closeMenus();
	} );

	doc.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' ) {
			if ( openLayer ) {
				hideLayer();
			} else {
				closeMenus();
			}
			return;
		}

		trapFocus( event );
	} );

	function boot() {
		if ( ! isEditor() ) {
			initStickyHeader();
		}
	}

	if ( doc.readyState === 'loading' ) {
		doc.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
}() );
