/**
 * Lenz Plus — opens Lenz's mobile menu from the plugin's own buttons (the
 * bottom navigation's menu item, the Lenz+ header's menu button).
 *
 * Lenz prints #mobile-menu on every page, but its open code is private to
 * the theme's script. The theme's header button is clicked when it exists;
 * otherwise (a Lenz+ header replaced it) the same three changes are made
 * here. The theme's close handlers (#overlay, .mobile-menu-close) work
 * either way.
 */
( function () {
	'use strict';

	/**
	 * Opens the menu once the current click has finished bubbling, so the
	 * theme's "click outside closes it" handlers do not undo it.
	 *
	 * @return {boolean} Whether the page has Lenz's mobile menu.
	 */
	function open() {
		const menu = document.getElementById( 'mobile-menu' );
		if ( ! menu ) {
			return false;
		}

		const button = document.getElementById( 'header-mobile-menu-btn' );
		window.setTimeout( () => {
			if ( button ) {
				button.click();
				return;
			}

			const overlay = document.getElementById( 'overlay' );
			document.body.classList.add( 'mobile-menu-opened' );
			menu.classList.remove( 'closed' );
			if ( overlay ) {
				if ( window.jQuery ) {
					window.jQuery( overlay ).fadeIn();
				} else {
					overlay.style.display = 'block';
				}
			}
		}, 0 );

		return true;
	}

	window.lzpLenzMenu = { open };
}() );
