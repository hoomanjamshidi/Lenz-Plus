/**
 * Lenz Plus — course widgets.
 *
 * The phone buy bar is hidden without JavaScript (the buy box does the same
 * job); this script moves it to the bottom of the screen and shows it while
 * neither the buy box's button nor the enroll form is on screen, so it is
 * there from the top of the page and steps aside where you can act anyway.
 * The course menu marks the section on screen and scrolls to a section
 * below the sticky header. The course grid's poster look sorts its cards
 * in place from the «most popular / newest» tabs. The intro video dialog
 * lives in video.js.
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
		const devices = [ 'all', 'tablet', 'mobile' ].find( ( d ) => bar.classList.contains( 'lzp-buybar--' + d ) ) || 'mobile';
		doc.body.classList.add( 'lzp-buybar-on', 'lzp-buybar-on--' + devices );

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

	/* ---------------------------------------------------------------------
	 * Course menu: current section, scrolling below the sticky header
	 * ------------------------------------------------------------------- */

	const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	// Height covered at the top: admin bar, sticky header and a sticky menu bar.
	function topCover() {
		const styles = getComputedStyle( doc.documentElement );
		let cover = ( parseFloat( styles.getPropertyValue( '--lzp-header-offset' ) ) || 0 ) + ( parseFloat( styles.getPropertyValue( '--lzp-admin-bar' ) ) || 0 );
		doc.querySelectorAll( '.lzp-cnav--bar' ).forEach( ( bar ) => {
			cover += bar.offsetParent !== null ? bar.offsetHeight : 0;
		} );

		return cover;
	}

	function initCourseNav( nav ) {
		const links = Array.from( nav.querySelectorAll( '.lzp-cnav__link' ) ).filter( ( link ) => {
			// Missing, or hidden because it is empty (courses.css).
			const target = doc.getElementById( decodeURIComponent( link.hash.slice( 1 ) ) );
			const shown = !! target && target.getClientRects().length > 0;
			if ( ! shown ) {
				link.parentElement.hidden = true;
			}
			return shown;
		} );
		if ( ! links.length ) {
			return;
		}

		const targets = new Map( links.map( ( link ) => [ doc.getElementById( decodeURIComponent( link.hash.slice( 1 ) ) ), link ] ) );
		const list = nav.querySelector( '.lzp-cnav__list' );

		function setCurrent( link ) {
			links.forEach( ( item ) => {
				item.classList.toggle( 'is-current', item === link );
				if ( item === link ) {
					item.setAttribute( 'aria-current', 'true' );
				} else {
					item.removeAttribute( 'aria-current' );
				}
			} );
			// Keep the current chip in view without scrolling the page.
			if ( link && nav.classList.contains( 'lzp-cnav--bar' ) && list ) {
				const start = link.offsetLeft - ( list.clientWidth - link.offsetWidth ) / 2;
				list.scrollTo( { left: start, behavior: reduceMotion.matches ? 'auto' : 'smooth' } );
			}
		}

		// The current section is the last one whose top has passed a line a
		// third of the way down the screen (the first one above them all).
		function update() {
			const line = topCover() + window.innerHeight * 0.3;
			let current = links[ 0 ];
			targets.forEach( ( link, target ) => {
				if ( target.getBoundingClientRect().top <= line ) {
					current = link;
				}
			} );
			setCurrent( current );
		}

		let ticking = false;
		window.addEventListener( 'scroll', () => {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( () => {
					ticking = false;
					update();
				} );
			}
		}, { passive: true } );
		window.addEventListener( 'resize', update );
		update();

		nav.addEventListener( 'click', ( event ) => {
			const link = event.target.closest( '.lzp-cnav__link' );
			const target = link && doc.getElementById( decodeURIComponent( link.hash.slice( 1 ) ) );
			if ( ! target ) {
				return;
			}
			event.preventDefault();
			const top = target.getBoundingClientRect().top + window.scrollY - topCover() - 16;
			window.scrollTo( { top, behavior: reduceMotion.matches ? 'auto' : 'smooth' } );
			history.replaceState( history.state, '', link.hash );
			setCurrent( link );
		} );
	}

	/**
	 * Poster look: the «most popular / newest» tabs sort the cards in place
	 * (the links sort on the server without JS).
	 */
	function initCourseSort( list ) {
		const grid = list.querySelector( '.lzp-courses' );
		const tabs = list.querySelectorAll( '[data-lzp-sort]' );
		if ( ! grid || ! tabs.length ) {
			return;
		}

		tabs.forEach( ( tab ) => tab.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			const popular = 'popular' === tab.dataset.lzpSort;
			const cards = Array.from( grid.children );
			cards.sort( ( a, b ) => ( popular ? ( b.dataset.sales - a.dataset.sales ) || ( a.dataset.new - b.dataset.new ) : a.dataset.new - b.dataset.new ) );
			cards.forEach( ( card ) => grid.appendChild( card ) );
			tabs.forEach( ( other ) => other.toggleAttribute( 'aria-current', other === tab ) );
			history.replaceState( history.state, '', tab.href );
		} ) );
	}

	function init() {
		doc.querySelectorAll( '[data-lzp-course-sort]:not([data-lzp-ready])' ).forEach( ( list ) => {
			list.setAttribute( 'data-lzp-ready', '' );
			initCourseSort( list );
		} );

		doc.querySelectorAll( '[data-lzp-course-nav]:not([data-lzp-ready])' ).forEach( ( nav ) => {
			nav.setAttribute( 'data-lzp-ready', '' );
			initCourseNav( nav );
		} );

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
