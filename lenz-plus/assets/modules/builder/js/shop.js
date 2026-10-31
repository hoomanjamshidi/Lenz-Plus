/**
 * Lenz Plus — WooCommerce pages in the brand style (Shop_Pages).
 *
 * - The classic cart updates itself shortly after a quantity changes, so
 *   visitors do not have to find "Update cart" (it stays for visitors
 *   without JavaScript; shop.css hides it once `lzp-cart-auto` is set).
 *   WooCommerce's cart.js does the update when the button is clicked, and
 *   swaps the whole form afterwards, so events are delegated.
 * - The cart's coupon answers under its field (error or success) without
 *   reloading the page; at the top of the page the answer is out of sight
 *   on a phone.
 * - Block prices read amount first («۱۲۰٬۰۰۰ تومان»): with the store's currency
 *   position on "left" the blocks print «تومان۱۲۰٬۰۰۰» as one text, which no
 *   CSS can reorder. Classic prices are reordered in shop.css; the store
 *   setting itself is left as it is.
 * - The success hero's "Download the files" button jumps to the order's
 *   downloads table, which WooCommerce prints without an id.
 * - The account menu, a scrolling row on phones, scrolls its current item
 *   into view.
 */
( function () {
	'use strict';

	var QTY = '.woocommerce-cart-form input.qty';
	var DELAY = 700;
	var timer = 0;

	function updateCart() {
		var button = document.querySelector( '.woocommerce-cart-form [name="update_cart"]' );
		if ( ! button ) {
			return;
		}
		button.disabled = false;
		button.click();
	}

	function scheduleUpdate( event ) {
		var input = event.target;
		// Wait for a complete number: an empty field while typing would remove the item.
		if ( ! input || '' === input.value || ! input.checkValidity() ) {
			return;
		}
		window.clearTimeout( timer );
		timer = window.setTimeout( updateCart, DELAY );
	}

	if ( document.querySelector( QTY ) ) {
		document.body.classList.add( 'lzp-cart-auto' );

		// Lenz's +/- buttons announce the new value with jQuery's trigger(), which native listeners never hear.
		if ( window.jQuery ) {
			window.jQuery( document.body ).on( 'change input', QTY, scheduleUpdate );
		} else {
			document.body.addEventListener( 'change', function ( event ) {
				if ( event.target.matches && event.target.matches( QTY ) ) {
					scheduleUpdate( event );
				}
			} );
		}
	}

	/**
	 * Shows the result of a coupon under its field: an error (as WooCommerce
	 * does for its own cart form) or a short success line.
	 */
	function couponMessage( box, html, isError ) {
		var input = box.querySelector( '#coupon_code' );
		var old = box.querySelectorAll( '.coupon-error-notice, .lzp-coupon-notice' );
		Array.prototype.forEach.call( old, function ( el ) {
			el.remove();
		} );
		if ( input ) {
			input.classList.remove( 'has-error' );
			input.removeAttribute( 'aria-invalid' );
			input.removeAttribute( 'aria-describedby' );
		}

		var text = window.jQuery( window.jQuery.parseHTML( html || '' ) ).text().trim();
		if ( '' === text ) {
			return;
		}

		// Not `woocommerce-message`: cart.js removes those when it refreshes the cart right after.
		var note = document.createElement( 'p' );
		note.className = isError ? 'coupon-error-notice' : 'lzp-coupon-notice';
		note.id = isError ? 'coupon-error-notice' : 'lzp-coupon-notice';
		note.setAttribute( 'role', isError ? 'alert' : 'status' );
		note.textContent = text;
		box.appendChild( note );

		if ( input && isError ) {
			input.classList.add( 'has-error' );
			input.setAttribute( 'aria-invalid', 'true' );
			input.setAttribute( 'aria-describedby', 'coupon-error-notice' );
		}
	}

	var $ = window.jQuery;
	if ( $ && window.wc_cart_params ) {
		// Lenz puts the cart's coupon in its own form, which WooCommerce's cart.js does not
		// handle (it would reload the page and print the result at the top). Without
		// JavaScript the form still posts normally.
		$( document.body ).on( 'submit', '.cart_bottom form.cart-section', function ( event ) {
			var form = this;
			var input = form.querySelector( '#coupon_code' );
			var box = input ? input.closest( '.coupon' ) : null;
			if ( ! box ) {
				return;
			}
			event.preventDefault();
			if ( form.classList.contains( 'is-busy' ) ) {
				return;
			}

			var code = input.value.trim();
			var button = form.querySelector( '[name="apply_coupon"]' );
			form.classList.add( 'is-busy' );
			if ( button ) {
				button.disabled = true;
			}

			$.ajax( {
				type: 'POST',
				url: window.wc_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'apply_coupon' ),
				data: {
					security: window.wc_cart_params.apply_coupon_nonce,
					coupon_code: code,
				},
				dataType: 'html',
			} )
				.done( function ( html ) {
					var isError = -1 !== html.indexOf( 'woocommerce-error' ) || -1 !== html.indexOf( 'is-error' );
					couponMessage( box, html, isError );
					if ( ! isError ) {
						input.value = '';
						$( document.body ).trigger( 'applied_coupon', [ code ] );
						// cart.js reloads the products and totals.
						$( document ).trigger( 'wc_update_cart' );
					}
				} )
				.fail( function () {
					// Post the form as without JavaScript; submit() leaves out the clicked button WooCommerce looks for.
					var flag = document.createElement( 'input' );
					flag.type = 'hidden';
					flag.name = 'apply_coupon';
					flag.value = '1';
					form.appendChild( flag );
					form.submit();
				} )
				.always( function () {
					form.classList.remove( 'is-busy' );
					if ( button ) {
						button.disabled = false;
					}
				} );
		} );

		// A new code starts clean.
		$( document.body ).on( 'input', '.cart_bottom #coupon_code', function () {
			couponMessage( this.closest( '.coupon' ), '', false );
		} );
	}

	var symbol = window.wcSettings && window.wcSettings.currency ? window.wcSettings.currency.symbol : '';
	var blocks = document.querySelector( '.wp-block-woocommerce-cart, .wp-block-woocommerce-checkout' );
	if ( symbol && blocks && document.body.classList.contains( 'lzp-currency-first' ) ) {
		var amountFirst = function () {
			Array.prototype.forEach.call( blocks.querySelectorAll( '.wc-block-formatted-money-amount' ), function ( el ) {
				var node = el.firstChild;
				if ( ! node || 3 !== node.nodeType ) {
					return;
				}
				// Discounts start with a minus: «-تومان11,349,000».
				var text = node.nodeValue;
				var sign = '-' === text.charAt( 0 ) ? '-' : '';
				if ( 0 !== text.indexOf( sign + symbol ) ) {
					return;
				}
				// Rewrite the text node in place: React keeps updating the same node when the amount changes.
				node.nodeValue = sign + text.slice( sign.length + symbol.length ).trim() + ' ' + symbol;
			} );
		};
		amountFirst();
		new MutationObserver( amountFirst ).observe( blocks, { childList: true, subtree: true, characterData: true } );
	}

	var downloads = document.querySelector( '.woocommerce-order .woocommerce-order-downloads' );
	if ( downloads && ! document.getElementById( 'lzp-downloads' ) ) {
		downloads.id = 'lzp-downloads';
	}

	var list = document.querySelector( '.lzp-account-nav__list' );
	var current = list ? list.querySelector( '[aria-current]' ) : null;
	if ( current && list.scrollWidth > list.clientWidth ) {
		var item = current.getBoundingClientRect();
		var box = list.getBoundingClientRect();
		// scrollBy() is relative, so this centres the item in RTL and LTR alike.
		list.scrollBy( item.left + item.width / 2 - ( box.left + box.width / 2 ), 0 );
	}
}() );
