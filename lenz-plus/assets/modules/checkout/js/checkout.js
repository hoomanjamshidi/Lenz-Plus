/**
 * Lenz Plus — Latin digits in phone, postcode and email fields as the
 * visitor types (Checkout_Form).
 *
 * «۰۹۱۲» becomes «0912» in the field itself, so the visitor sees what will
 * be saved and sent to the payment gateway. The server converts again when
 * the form is posted, so this is only the visible half. Block checkout
 * fields are left alone: React owns their value and would undo the change
 * (the Store API request is converted on the server instead).
 */
( function () {
	'use strict';

	var NAME = /(phone|mobile|tel|postcode|zip|email|national|melli|code)/;
	var PERSIAN = /[۰-۹٠-٩]/g;

	function latin( digit ) {
		var code = digit.charCodeAt( 0 );
		// Persian ۰ is U+06F0, Arabic ٠ is U+0660.
		return String( code >= 0x06f0 ? code - 0x06f0 : code - 0x0660 );
	}

	document.addEventListener( 'input', function ( event ) {
		var field = event.target;
		if ( ! field || 'INPUT' !== field.tagName || ! NAME.test( field.name || '' ) || field.closest( '.wc-block-checkout, .wc-block-cart' ) ) {
			return;
		}

		var value = field.value;
		if ( ! PERSIAN.test( value ) ) {
			return;
		}
		PERSIAN.lastIndex = 0;

		// Same length before and after, so the caret can stay where it was (email fields have no selection API).
		var start = null;
		try {
			start = field.selectionStart;
		} catch ( error ) {
			start = null;
		}

		field.value = value.replace( PERSIAN, latin );

		if ( null !== start ) {
			field.setSelectionRange( start, start );
		}
	} );
}() );
