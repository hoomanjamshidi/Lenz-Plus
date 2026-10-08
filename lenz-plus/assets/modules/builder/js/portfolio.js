/**
 * Lenz Plus — portfolio grid filters.
 *
 * The category chips are links to the category pages and work on their
 * own. When a grid holds all its projects (`data-lzp-pf-filter`), this
 * filters them in place instead: the chips become toggle buttons
 * (aria-pressed), hidden projects get `hidden`, and the count follows.
 */
( function () {
	'use strict';

	const doc = document;

	/**
	 * Number in the digits the server used for the count (Persian or Latin).
	 *
	 * @param {number}  value   Number.
	 * @param {boolean} persian Whether to use Persian digits.
	 */
	const digits = ( value, persian ) => ( persian ? String( value ).replace( /\d/g, ( d ) => '۰۱۲۳۴۵۶۷۸۹'[ d ] ) : String( value ) );

	function initGrid( grid ) {
		const chips = grid.querySelectorAll( '[data-lzp-cat]' );
		const items = grid.querySelectorAll( '.lzp-pf__item' );
		const count = grid.querySelector( '[data-lzp-count]' );
		const persian = Boolean( count && /[۰-۹]/.test( count.textContent ) );

		grid.classList.add( 'is-filtering' );
		chips.forEach( ( chip ) => {
			chip.setAttribute( 'role', 'button' );
			chip.setAttribute( 'aria-pressed', chip.dataset.lzpCat === '' ? 'true' : 'false' );
		} );

		const apply = ( slug ) => {
			let shown = 0;
			items.forEach( ( item ) => {
				const match = slug === '' || ( ' ' + item.dataset.lzpCats + ' ' ).indexOf( ' ' + slug + ' ' ) !== -1;
				item.hidden = ! match;
				shown += match ? 1 : 0;
			} );

			chips.forEach( ( chip ) => chip.setAttribute( 'aria-pressed', chip.dataset.lzpCat === slug ? 'true' : 'false' ) );

			if ( count ) {
				const template = shown === 1 ? count.dataset.one : count.dataset.many;
				count.textContent = ( template || '%s' ).replace( '%s', digits( shown, persian ) );
			}
		};

		grid.addEventListener( 'click', ( event ) => {
			const chip = event.target.closest( '[data-lzp-cat]' );
			if ( ! chip || ! grid.contains( chip ) ) {
				return;
			}
			event.preventDefault();
			apply( chip.dataset.lzpCat );
		} );

		// role="button" links also answer to the space bar, like real buttons.
		grid.addEventListener( 'keydown', ( event ) => {
			const chip = event.target.closest( '[data-lzp-cat]' );
			if ( chip && event.key === ' ' ) {
				event.preventDefault();
				apply( chip.dataset.lzpCat );
			}
		} );
	}

	/* ---------------------------------------------------------------------
	 * Boot (the page, and each widget the Elementor editor re-renders)
	 * ------------------------------------------------------------------- */

	function initScope( scope ) {
		scope.querySelectorAll( '[data-lzp-pf-filter]' ).forEach( ( grid ) => {
			if ( ! grid.hasAttribute( 'data-lzp-ready' ) ) {
				grid.setAttribute( 'data-lzp-ready', '' );
				initGrid( grid );
			}
		} );
	}

	if ( doc.readyState === 'loading' ) {
		doc.addEventListener( 'DOMContentLoaded', () => initScope( doc ) );
	} else {
		initScope( doc );
	}

	let hooked = false;
	function hookElementor() {
		if ( hooked || ! window.elementorFrontend || ! window.elementorFrontend.hooks ) {
			return;
		}
		hooked = true;
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', ( $scope ) => {
			if ( $scope && $scope[ 0 ] ) {
				initScope( $scope[ 0 ] );
			}
		} );
	}

	// Elementor announces itself with a jQuery event (native listeners do not see it).
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', hookElementor );
	}
	window.addEventListener( 'elementor/frontend/init', hookElementor );
	hookElementor();
}() );
