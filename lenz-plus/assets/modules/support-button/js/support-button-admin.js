/**
 * Lenz Plus — support button settings panel.
 *
 * Fields bind to the store through admin.js (`data-lzp-bind`); this adds:
 *   - channel rows: summary chips, the resolved link, open/close, reorder
 *   - the live phone preview and the design cards, rendered with the real
 *     front-end stylesheet
 *
 * Contracts: renderWidget() mirrors views/button.php and channelUrl()
 * mirrors Channels::url(). Change them together.
 */
( function ( $ ) {
	'use strict';

	const LZP = window.LZP;
	const root = document.querySelector( '[data-lzp-module="support_button"]' );
	if ( ! LZP || ! LZP.store || ! root ) {
		return;
	}

	const store = LZP.store;
	const esc = LZP.escapeHtml;
	const moduleData = LZP.data.moduleData;
	const channels = moduleData.channels;
	const i18n = Object.assign( {}, LZP.i18n, moduleData.i18n || {} );

	/* =====================================================================
	 * Channel links (mirror of Channels::url())
	 * =================================================================== */

	const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
	const ARABIC_DIGITS = '٠١٢٣٤٥٦٧٨٩';

	const latinDigits = ( value ) => value.replace( /[۰-۹٠-٩]/g, ( digit ) => String( Math.max( PERSIAN_DIGITS.indexOf( digit ), ARABIC_DIGITS.indexOf( digit ) ) ) );
	const isUrl = ( value ) => /^(https?:\/\/|[a-z0-9-]+(\.[a-z0-9-]+)+\/)/i.test( value );
	const withScheme = ( value ) => ( /^([a-z][a-z0-9+.-]*:|\/)/i.test( value ) ? value : 'https://' + value );
	const isPhone = ( value ) => /^\+?[\d\s()-]{8,}$/.test( value );

	function international( value ) {
		const digits = value.replace( /\D/g, '' );
		if ( value.charAt( 0 ) !== '+' && digits.indexOf( '00' ) === 0 ) {
			return digits.slice( 2 );
		}
		if ( value.charAt( 0 ) !== '+' && /^09\d{9}$/.test( digits ) ) {
			return '98' + digits.slice( 1 );
		}
		return digits;
	}

	function handleUrl( value, base ) {
		if ( isUrl( value ) ) {
			return withScheme( value );
		}
		const handle = value.replace( /^@+/, '' );
		return /^[A-Za-z0-9_.]{2,64}$/.test( handle ) ? base + handle : '';
	}

	function whatsappUrl( value, message ) {
		if ( isUrl( value ) ) {
			return withScheme( value );
		}
		const number = international( value );
		if ( number.length < 8 ) {
			return '';
		}
		return 'https://wa.me/' + number + ( message.trim() ? '?text=' + encodeURIComponent( message.trim() ) : '' );
	}

	function channelUrl( id, channel ) {
		const value = latinDigits( String( channel.value || '' ).trim() );
		if ( ! value ) {
			return '';
		}

		switch ( id ) {
			case 'telegram':
				return isPhone( value ) ? 'https://t.me/+' + international( value ) : handleUrl( value, 'https://t.me/' );
			case 'whatsapp':
				return whatsappUrl( value, String( channel.message || '' ) );
			case 'bale':
				return handleUrl( value, 'https://ble.ir/' );
			case 'eitaa':
				return handleUrl( value, 'https://eitaa.com/' );
			case 'instagram':
				return handleUrl( value, 'https://ig.me/m/' );
			case 'phone': {
				const number = ( value.charAt( 0 ) === '+' ? '+' : '' ) + value.replace( /\D/g, '' );
				return number.length >= 3 ? 'tel:' + number : '';
			}
			case 'email':
				return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( value ) ? 'mailto:' + value : '';
			case 'link':
				return isUrl( value ) || value.charAt( 0 ) === '/' ? withScheme( value ) : '';
		}

		return '';
	}

	function channelGlyph( id, channel ) {
		return id === 'link' ? moduleData.icons[ channel.icon ] || moduleData.icons.chat : channels[ id ].glyph;
	}

	const channelLabel = ( id, channel ) => channel.label || channels[ id ].label;

	/** Switched-on channels that resolve to a link, in order (mirror of Frontend::channels()). */
	function readyChannels( state ) {
		return state.order.filter( ( id ) => channels[ id ] && state.channels[ id ] && state.channels[ id ].enabled ).map( ( id ) => {
			const channel = state.channels[ id ];
			return {
				id,
				label: channelLabel( id, channel ),
				note: channel.note || '',
				url: channelUrl( id, channel ),
				glyph: channelGlyph( id, channel ),
				brand: state.colors.brand ? channels[ id ].color : '',
			};
		} ).filter( ( channel ) => channel.url );
	}

	/* =====================================================================
	 * Widget markup (mirror of views/button.php)
	 * =================================================================== */

	const brandStyle = ( brand ) => ( brand ? ' style="--lzp-sb-brand:' + esc( brand ) + '"' : '' );

	function styleVars( state ) {
		let css = '--lzp-sb-size:' + state.button.size + 'px;--lzp-sb-x:' + state.position.offset_x + 'px;--lzp-sb-y:' + state.position.offset_y + 'px;--lzp-sb-z:5;';
		if ( state.colors.button_bg ) {
			css += '--lzp-sb-button:' + state.colors.button_bg + ';';
		}
		if ( state.colors.button_icon ) {
			css += '--lzp-sb-button-icon:' + state.colors.button_icon + ';';
		}
		return css;
	}

	/**
	 * @param {Object}  state
	 * @param {Object}  options
	 * @param {string}  options.design Design to draw (defaults to the saved one).
	 * @param {boolean} options.open   Draw the menu open.
	 * @param {number}  options.limit  Maximum channels (small design cards).
	 * @return {string} HTML, or '' when no channel is ready.
	 */
	function renderWidget( state, options ) {
		const list = readyChannels( state ).slice( 0, options.limit || undefined );
		if ( ! list.length ) {
			return '';
		}

		const design = options.design || state.design;
		const single = list.length === 1;
		const button = state.button;
		// The preview is a phone, where "computers only" labels are hidden.
		const labelMode = ! button.label || button.label_mode === 'desktop' ? 'never' : button.label_mode;
		const classes = [ 'lzp-sb', 'lzp-sb--' + design, 'lzp-sb--' + state.position.side, 'lzp-sb--label-' + labelMode ];
		const label = single ? list[ 0 ].label : button.label || i18n.support;
		const icon = single ? list[ 0 ].glyph : moduleData.icons[ button.icon ];
		const showGreeting = state.greeting.enabled && state.greeting.text && ! options.open && ! options.limit;

		let html = '<div class="' + classes.join( ' ' ) + '" style="' + esc( styleVars( state ) ) + '">';

		if ( single ) {
			const brand = state.colors.button_bg ? '' : list[ 0 ].brand;
			html += '<a class="lzp-sb__toggle" href="#"' + brandStyle( brand ) + '>' +
				'<span class="lzp-sb__icons" aria-hidden="true"><span class="lzp-sb__icon">' + icon + '</span></span>' +
				'<span class="lzp-sb__label">' + esc( label ) + '</span></a>';
		} else {
			const header = design === 'card' ? state.header : { title: '', subtitle: '' };

			html += '<details class="lzp-sb__menu"' + ( options.open ? ' open' : '' ) + '>' +
				'<summary class="lzp-sb__toggle">' +
					'<span class="lzp-sb__icons" aria-hidden="true">' +
						'<span class="lzp-sb__icon">' + icon + '</span>' +
						'<span class="lzp-sb__icon lzp-sb__icon--close">' + moduleData.closeIcon + '</span>' +
					'</span>' +
					'<span class="lzp-sb__label">' + esc( label ) + '</span>' +
				'</summary>' +
				'<div class="lzp-sb__panel">';

			if ( header.title || header.subtitle ) {
				html += '<div class="lzp-sb__head">' +
					( header.title ? '<p class="lzp-sb__title">' + esc( header.title ) + '</p>' : '' ) +
					( header.subtitle ? '<p class="lzp-sb__subtitle">' + esc( header.subtitle ) + '</p>' : '' ) +
					'</div>';
			}

			html += '<ul class="lzp-sb__list" style="--lzp-sb-count:' + list.length + '">' + list.map( ( channel, index ) => (
				'<li class="lzp-sb__item" style="--lzp-sb-i:' + index + '">' +
					'<a class="lzp-sb__channel" href="#">' +
						'<span class="lzp-sb__glyph" aria-hidden="true"' + brandStyle( channel.brand ) + '>' + channel.glyph + '</span>' +
						'<span class="lzp-sb__text">' +
							'<span class="lzp-sb__name">' + esc( channel.label ) + '</span>' +
							( channel.note ? '<span class="lzp-sb__note">' + esc( channel.note ) + '</span>' : '' ) +
						'</span>' +
					'</a>' +
				'</li>'
			) ).join( '' ) + '</ul></div></details>';
		}

		if ( showGreeting ) {
			html += '<div class="lzp-sb__greeting">' +
				'<button type="button" class="lzp-sb__greeting-text">' + esc( state.greeting.text ) + '</button>' +
				'<button type="button" class="lzp-sb__greeting-close" aria-label="' + esc( i18n.close ) + '">' + moduleData.closeIcon + '</button>' +
				'</div>';
		}

		return html + '</div>';
	}

	/* =====================================================================
	 * Preview
	 * =================================================================== */

	const preview = {
		host: root.querySelector( '[data-lzp-preview-host]' ),
		empty: root.querySelector( '[data-lzp-preview-empty]' ),
		hiddenNote: root.querySelector( '[data-lzp-preview-hidden]' ),
		open: true,
	};
	const designStages = Array.from( root.querySelectorAll( '[data-lzp-design-stage]' ) );

	/** Design cards draw a phone-width widget, scaled down to the card. */
	const STAGE_PHONE_WIDTH = 360;
	const STAGE_MAX_SCALE = 0.6;
	let renderQueued = false;

	function scaleStage( stage ) {
		stage.style.setProperty( '--lzp-stage-scale', String( Math.min( STAGE_MAX_SCALE, stage.clientWidth / STAGE_PHONE_WIDTH ) ) );
	}

	function renderPreviews() {
		renderQueued = false;
		const state = store.get();
		const html = renderWidget( state, { open: preview.open } );
		const phoneHidden = state.display.devices === 'desktop';

		preview.host.innerHTML = phoneHidden ? '' : html;
		preview.empty.hidden = Boolean( html );
		preview.hiddenNote.hidden = ! ( html && phoneHidden );

		designStages.forEach( ( stage ) => {
			stage.innerHTML = '<span class="lzp-stage__viewport">' + renderWidget( state, { design: stage.dataset.lzpDesignStage, open: true, limit: 3 } ) + '</span>';
			scaleStage( stage );
		} );
	}

	function queueRender() {
		if ( ! renderQueued ) {
			renderQueued = true;
			window.requestAnimationFrame( renderPreviews );
		}
	}

	function setPreviewOpen( open ) {
		preview.open = open;
		root.querySelectorAll( '[data-lzp-preview-state]' ).forEach( ( input ) => {
			input.checked = input.value === ( open ? 'open' : 'closed' );
		} );
		queueRender();
	}

	function initPreview() {
		// Links never navigate in the preview; the main button opens and closes it.
		preview.host.addEventListener( 'click', ( event ) => {
			if ( event.target.closest( 'a, button' ) ) {
				event.preventDefault();
			}
			if ( event.target.closest( 'summary' ) ) {
				event.preventDefault();
				setPreviewOpen( ! preview.open );
			}
		} );

		// The design card's radio still receives the click.
		designStages.forEach( ( stage ) => stage.addEventListener( 'click', ( event ) => {
			if ( event.target.closest( 'a, summary' ) ) {
				event.preventDefault();
				stage.closest( 'label' ).querySelector( 'input' ).click();
			}
		} ) );

		if ( window.ResizeObserver ) {
			const observer = new ResizeObserver( ( entries ) => entries.forEach( ( entry ) => scaleStage( entry.target ) ) );
			designStages.forEach( ( stage ) => observer.observe( stage ) );
		}

		root.querySelectorAll( '[data-lzp-preview-state]' ).forEach( ( input ) => {
			input.addEventListener( 'change', () => setPreviewOpen( input.value === 'open' ) );
		} );
	}

	/* =====================================================================
	 * Channel rows
	 * =================================================================== */

	const list = root.querySelector( '[data-lzp-channels]' );
	const noneReady = root.querySelector( '[data-lzp-none-ready]' );
	const rows = () => Array.from( list.querySelectorAll( '.lzp-item[data-channel]' ) );

	/** Shortens long links for the chip; the full value stays in the field. */
	const shorten = ( text ) => ( text.length > 28 ? text.slice( 0, 27 ) + '…' : text );

	function refreshRow( row, state ) {
		const id = row.dataset.channel;
		const channel = state.channels[ id ];
		const url = channelUrl( id, channel );
		const value = String( channel.value || '' ).trim();

		row.classList.toggle( 'is-off', ! channel.enabled );
		row.querySelector( '[data-channel-icon]' ).innerHTML = channelGlyph( id, channel );
		row.querySelector( '[data-channel-label]' ).textContent = channelLabel( id, channel );

		const chip = row.querySelector( '[data-channel-value]' );
		chip.hidden = ! url;
		chip.textContent = shorten( value );

		let problem = '';
		if ( ! channel.enabled ) {
			problem = value ? '' : i18n.off;
		} else if ( ! value ) {
			problem = i18n.notSet;
		} else if ( ! url ) {
			problem = i18n.invalid;
		}
		const warning = row.querySelector( '[data-channel-warning]' );
		warning.hidden = ! problem;
		warning.textContent = problem;
		warning.classList.toggle( 'lzp-chip--warn', Boolean( channel.enabled ) );

		const link = row.querySelector( '[data-channel-url]' );
		link.hidden = ! url;
		link.innerHTML = url ? esc( i18n.opens ) + ' <a href="' + esc( url ) + '" target="_blank" rel="noopener" dir="ltr">' + esc( url ) + '</a>' : '';
	}

	/** Puts the rows in the stored order (after reordering, reset or save). */
	function syncOrder( state ) {
		const current = rows().map( ( row ) => row.dataset.channel );
		if ( current.join() === state.order.join() ) {
			return;
		}
		const byId = {};
		rows().forEach( ( row ) => {
			byId[ row.dataset.channel ] = row;
		} );
		state.order.forEach( ( id ) => byId[ id ] && list.appendChild( byId[ id ] ) );
	}

	function refreshChannels() {
		const state = store.get();
		syncOrder( state );
		rows().forEach( ( row ) => refreshRow( row, state ) );
		noneReady.hidden = readyChannels( state ).length > 0;
	}

	function setOpen( row, open ) {
		row.classList.toggle( 'is-open', open );
		row.querySelector( '.lzp-item__editor' ).hidden = ! open;
		row.querySelector( '.lzp-item__summary' ).setAttribute( 'aria-expanded', String( open ) );
	}

	function move( id, step ) {
		const order = store.get( 'order' ).slice();
		const index = order.indexOf( id );
		const target = index + step;
		if ( index < 0 || target < 0 || target >= order.length ) {
			return;
		}
		order.splice( target, 0, order.splice( index, 1 )[ 0 ] );
		store.set( 'order', order );
	}

	function initChannels() {
		list.addEventListener( 'click', ( event ) => {
			const row = event.target.closest( '.lzp-item' );
			if ( ! row ) {
				return;
			}

			if ( event.target.closest( '[data-item-toggle]' ) ) {
				setOpen( row, ! row.classList.contains( 'is-open' ) );
				return;
			}

			const mover = event.target.closest( '[data-channel-move]' );
			if ( mover ) {
				move( row.dataset.channel, mover.dataset.channelMove === 'up' ? -1 : 1 );
				mover.focus();
			}
		} );

		if ( $ && $.fn.sortable ) {
			$( list ).sortable( {
				handle: '.lzp-item__handle',
				items: '> .lzp-item',
				axis: 'y',
				tolerance: 'pointer',
				placeholder: 'lzp-item-placeholder',
				update() {
					store.set( 'order', rows().map( ( row ) => row.dataset.channel ) );
				},
			} );
		}
	}

	/**
	 * "Use it" buttons next to a value the theme suggests (the number in
	 * Lenz's mobile-menu support box): fill the field and switch the channel on.
	 */
	function initSuggestions() {
		root.querySelectorAll( '[data-lzp-fill]' ).forEach( ( button ) => {
			button.addEventListener( 'click', () => {
				store.set( button.dataset.lzpFill, button.dataset.lzpFillValue );
				if ( button.dataset.lzpFillEnable ) {
					store.set( button.dataset.lzpFillEnable, true );
				}
			} );
		} );
	}

	/* =====================================================================
	 * Boot
	 * =================================================================== */

	initPreview();
	initChannels();
	initSuggestions();

	store.subscribe( () => {
		refreshChannels();
		queueRender();
	} );
	refreshChannels();
	renderPreviews();
}( window.jQuery ) );
