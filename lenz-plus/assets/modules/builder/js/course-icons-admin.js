/**
 * Session icons in the «Course details (Lenz+)» box and the studio panel's
 * course form (Session_Icons.php).
 *
 * Each session's native <select> (which posts the value and works without
 * this script) gets a button showing the chosen icon; the button opens a
 * grid of the library's icons. The rows follow the curriculum textarea:
 * typing a session adds a row, the titles update, and the chosen icons stay
 * with their position.
 *
 * The studio panel swaps pages in place (panel-nav.js), so the picker is
 * set up again on its `lzp:page` event; document listeners are bound once.
 */
( function () {
	'use strict';

	// The course box and the studio panel name the field alike (their ids differ).
	var AREA = 'textarea[name="lzp_course[curriculum]"]';
	var i18n = window.lzpCourseIcons || {};
	var box = null;
	var area = null;
	var list = null;
	var rowTemplate = null;
	var svgs = {};
	var popover = null;
	var openFor = null;

	/** Session titles typed in the curriculum box (the part before «|»). */
	function titles() {
		// Mirrors Course_Meta_Box::clean(): empty lines are not sessions.
		return area.value.split( /\r?\n/ ).filter( function ( line ) {
			return '' !== line.trim();
		} ).map( function ( line ) {
			return line.split( '|' )[ 0 ].trim();
		} );
	}

	/** Shows the chosen icon (or «No icon») on a row's button. */
	function paint( item ) {
		var select = item.querySelector( 'select' );
		var btn = item.querySelector( '.lzp-cicons__btn' );
		var key = select.value;
		btn.innerHTML = key && svgs[ key ] ? svgs[ key ] : '<span class="lzp-cicons__none">' + ( i18n.none || '' ) + '</span>';
		btn.setAttribute( 'aria-label', ( i18n.choose || '' ) + ': ' + ( select.options[ select.selectedIndex ] || {} ).text );
		item.classList.toggle( 'has-icon', !! key );
	}

	/** Adds the button to a row and hides its select. */
	function enhance( item ) {
		if ( item.querySelector( '.lzp-cicons__btn' ) ) {
			return;
		}
		var btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.className = 'lzp-cicons__btn';
		btn.setAttribute( 'aria-haspopup', 'true' );
		btn.setAttribute( 'aria-expanded', 'false' );
		item.querySelector( 'select' ).classList.add( 'screen-reader-text' );
		item.querySelector( 'select' ).tabIndex = -1;
		item.insertBefore( btn, item.firstChild );
		paint( item );
	}

	/** Keeps one row per session, titled like the session. */
	function sync() {
		if ( ! area ) {
			return;
		}
		close();
		var names = titles();
		var items = list.getElementsByClassName( 'lzp-cicons__item' );

		while ( items.length < names.length ) {
			list.appendChild( rowTemplate.content.firstElementChild.cloneNode( true ) );
			enhance( list.lastElementChild );
		}
		// Extra rows are hidden, not removed, so a session cut and pasted back keeps its icon.
		Array.prototype.forEach.call( items, function ( item, index ) {
			var name = names[ index ];
			item.hidden = undefined === name;
			item.querySelector( 'select' ).disabled = item.hidden;
			item.querySelector( '.lzp-cicons__title' ).textContent = name || '';
			item.querySelector( 'select' ).setAttribute( 'aria-label', name || ( i18n.session || '%s' ).replace( '%s', index + 1 ) );
		} );
	}

	/**
	 * Closes the grid.
	 *
	 * @param {boolean} refocus Puts focus back on the row's button.
	 */
	function close( refocus ) {
		if ( ! popover ) {
			return;
		}
		popover.remove();
		popover = null;
		if ( openFor ) {
			openFor.setAttribute( 'aria-expanded', 'false' );
			if ( refocus ) {
				openFor.focus();
			}
			openFor = null;
		}
	}

	/** Opens the icon grid under a row's button. */
	function open( btn ) {
		close();
		var item = btn.closest( '.lzp-cicons__item' );
		var select = item.querySelector( 'select' );

		// A list row of its own under the session: a floating panel would be clipped by the meta box.
		popover = document.createElement( 'li' );
		popover.className = 'lzp-cicons__grid';
		popover.setAttribute( 'role', 'dialog' );
		popover.setAttribute( 'aria-label', i18n.choose || '' );

		var none = document.createElement( 'button' );
		none.type = 'button';
		none.className = 'lzp-cicons__pick lzp-cicons__pick--none';
		none.setAttribute( 'data-key', '' );
		none.textContent = i18n.none || '';
		popover.appendChild( none );
		popover.appendChild( box.querySelector( '[data-lzp-cicons-grid]' ).content.cloneNode( true ) );

		popover.querySelectorAll( '[data-key]' ).forEach( function ( pick ) {
			var current = pick.getAttribute( 'data-key' ) === select.value;
			pick.setAttribute( 'aria-pressed', current ? 'true' : 'false' );
			pick.addEventListener( 'click', function () {
				select.value = pick.getAttribute( 'data-key' );
				select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				paint( item );
				close( true );
			} );
		} );

		item.after( popover );
		openFor = btn;
		btn.setAttribute( 'aria-expanded', 'true' );
		( popover.querySelector( '[aria-pressed="true"]' ) || none ).focus();
	}

	/** Sets up the picker on the current page (once per box). */
	function init() {
		box = document.querySelector( '[data-lzp-cicons]' );
		if ( ! box || box.hasAttribute( 'data-lzp-ready' ) ) {
			return;
		}
		box.setAttribute( 'data-lzp-ready', '' );
		area = document.querySelector( AREA );
		list = box.querySelector( '[data-lzp-cicons-list]' );
		rowTemplate = box.querySelector( '[data-lzp-cicons-row]' );
		popover = null;
		openFor = null;
		svgs = {};
		box.querySelector( '[data-lzp-cicons-grid]' ).content.querySelectorAll( '[data-key]' ).forEach( function ( btn ) {
			svgs[ btn.getAttribute( 'data-key' ) ] = btn.innerHTML;
		} );

		Array.prototype.forEach.call( list.children, enhance );
		sync();
	}

	document.addEventListener( 'click', function ( event ) {
		var btn = event.target.closest( '[data-lzp-cicons] .lzp-cicons__btn' );
		if ( btn ) {
			if ( openFor === btn ) {
				close();
			} else {
				open( btn );
			}
		} else if ( popover && ! event.target.closest( '.lzp-cicons__grid' ) ) {
			close();
		}
	} );
	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key && popover ) {
			event.preventDefault();
			close( true );
		}
	} );
	document.addEventListener( 'input', function ( event ) {
		if ( box && event.target === area ) {
			sync();
		}
	} );
	document.addEventListener( 'lzp:page', init );
	init();
}() );
