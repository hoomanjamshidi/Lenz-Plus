/**
 * Lenz Plus — bottom navigation settings panel.
 *
 * Builds on the admin shell (window.LZP) and adds:
 *   - a live phone preview and a mini preview on every style card
 *   - the sortable button list (rows cloned from #lzp-item-template)
 *   - the icon picker dialog and media-library picker
 *
 * MARKUP CONTRACT: `renderNav()` mirrors views/nav.php and `resolveItems()`
 * mirrors Item_Resolver.php, so the preview matches the site pixel for pixel
 * (both use bottom-nav.css). Update them together.
 */
( function ( $ ) {
	'use strict';

	const LZP = window.LZP;
	const root = document.querySelector( '[data-lzp-module="bottom_nav"]' );
	if ( ! LZP || ! LZP.store || ! root ) {
		return;
	}

	const store = LZP.store;
	const esc = LZP.escapeHtml;
	const moduleData = LZP.data.moduleData;
	const i18n = Object.assign( {}, LZP.i18n, moduleData.i18n || {} );
	const catalog = moduleData.catalog;
	const itemTypes = moduleData.itemTypes;

	/** Styles where the "featured" item becomes a raised centre button. */
	const hasFeaturedButton = ( style ) => Boolean( moduleData.styles[ style ] && moduleData.styles[ style ].featured );

	/* =====================================================================
	 * Icons
	 * =================================================================== */

	const FONT_AWESOME = 'fontawesome';
	const LENZ = 'lenz';
	const packs = { [ moduleData.iconPack.id ]: moduleData.iconPack.icons };
	const pendingPacks = {};

	/** Font packs are drawn with Lenz's icon fonts, which exist only while Lenz is active. */
	const isFontPack = ( id ) => id === FONT_AWESOME || id === LENZ;
	const fontPackReady = ( id ) => isFontPack( id ) && moduleData.theme.active;

	/** Loads a pack and its fallback chain; resolves when all are cached. */
	function loadPack( id ) {
		if ( ! id ) {
			return Promise.resolve();
		}

		const fallback = ( catalog.packs[ id ] || {} ).fallback;
		if ( isFontPack( id ) ) {
			// No SVG file of its own: only its fallback chain needs loading.
			return fallback ? loadPack( fallback ) : Promise.resolve();
		}

		const self = packs[ id ]
			? Promise.resolve()
			: ( pendingPacks[ id ] = pendingPacks[ id ] || fetch( moduleData.iconPackUrl + id + '.json' )
				.then( ( response ) => response.json() )
				.then( ( icons ) => {
					packs[ id ] = icons;
				} )
				.catch( () => {
					packs[ id ] = {};
				} ) );

		return Promise.all( [ self, fallback ? loadPack( fallback ) : Promise.resolve() ] );
	}

	function decorate( svg ) {
		return svg.replace( /^<svg\b/i, '<svg aria-hidden="true" focusable="false"' );
	}

	/** Mirrors Icon_Library::svg(): pack → fallback → tabler. */
	function packSvg( pack, key, active ) {
		const visited = {};
		let current = pack === FONT_AWESOME ? 'tabler' : pack;

		while ( current && ! visited[ current ] ) {
			visited[ current ] = true;
			const icon = ( packs[ current ] || {} )[ key ];
			if ( icon ) {
				return decorate( active && icon.active ? icon.active : icon.svg );
			}
			current = ( catalog.packs[ current ] || {} ).fallback || 'tabler';
		}

		return '';
	}

	const catalogIcon = ( key ) => catalog.icons.find( ( entry ) => entry.key === key );

	/** Mirrors Icon_Library::has_active_variant(). */
	function hasActiveVariant( pack, key ) {
		if ( pack === LENZ ) {
			return Boolean( ( catalogIcon( key ) || {} ).lenzActive );
		}
		return pack === FONT_AWESOME || Boolean( ( packs[ pack ] || {} )[ key ] && packs[ pack ][ key ].active );
	}

	function faClass( key, weight ) {
		const icon = catalogIcon( key );
		if ( ! icon ) {
			return weight + ' fa-circle';
		}
		return icon.fa.indexOf( 'fab ' ) === 0 ? icon.fa : weight + ' ' + icon.fa;
	}

	/** Mirrors Icon_Library::lenz_class(): '' when Lenz's font has no glyph for the key. */
	function lenzClass( key, active ) {
		const icon = catalogIcon( key ) || {};
		const glyph = active && icon.lenzActive ? icon.lenzActive : icon.lenz;
		return glyph ? 'lenz-icon-' + glyph : '';
	}

	const faSolid = ( classes ) => classes.replace( /\bfar\b/, 'fas' );
	const faIcon = ( classes ) => '<i class="' + esc( classes ) + '" aria-hidden="true"></i>';

	const SVG_TAGS = [ 'svg', 'g', 'defs', 'title', 'path', 'circle', 'ellipse', 'rect', 'line', 'polyline', 'polygon', 'clippath', 'lineargradient', 'radialgradient', 'stop' ];

	/**
	 * Client-side counterpart of Icon_Library::sanitize_svg() so unsaved custom
	 * SVG is safe to preview. The server sanitizes again on save.
	 */
	function sanitizeSvg( markup ) {
		const doc = new DOMParser().parseFromString( markup || '', 'image/svg+xml' );
		const svg = doc.documentElement;

		if ( ! svg || svg.nodeName.toLowerCase() !== 'svg' || doc.querySelector( 'parsererror' ) ) {
			return '';
		}

		svg.querySelectorAll( '*' ).forEach( ( node ) => {
			if ( ! SVG_TAGS.includes( node.nodeName.toLowerCase() ) ) {
				node.remove();
			}
		} );

		[ svg, ...svg.querySelectorAll( '*' ) ].forEach( ( node ) => {
			Array.from( node.attributes ).forEach( ( attribute ) => {
				const name = attribute.name.toLowerCase();
				if ( name.indexOf( 'on' ) === 0 || name === 'href' || name === 'xlink:href' || /javascript:/i.test( attribute.value ) ) {
					node.removeAttribute( attribute.name );
				}
			} );
		} );

		return new XMLSerializer().serializeToString( svg );
	}

	/**
	 * Icon markup for an item (mirrors Item_Resolver::icon_html()).
	 * Returns '' for the active variant when there is none.
	 */
	function itemIcon( item, active, state ) {
		const pack = state.icon_pack;
		const wantsVariant = active && state.active_filled;

		switch ( item.icon_source ) {
			case 'image':
				if ( ! active && item.icon_image ) {
					return '<img class="lzp-bn__img" src="' + esc( item.icon_image ) + '" alt="">';
				}
				break;

			case 'svg': {
				const svg = active ? '' : sanitizeSvg( item.icon_svg );
				if ( svg ) {
					return decorate( svg );
				}
				break;
			}

			case 'fontawesome':
				if ( item.icon_fa ) {
					if ( active ) {
						return wantsVariant ? faIcon( faSolid( item.icon_fa ) ) : '';
					}
					return faIcon( item.icon_fa );
				}
				break;
		}

		if ( active && ( ! wantsVariant || ! hasActiveVariant( pack, item.icon ) ) ) {
			return '';
		}

		return packGlyph( pack, item.icon, active, state.fa_weight );
	}

	/**
	 * One pack icon: a font glyph when the font pack can be drawn and has one,
	 * else the SVG fallback chain (mirrors the end of Item_Resolver::icon_html()).
	 */
	function packGlyph( pack, key, active, weight ) {
		if ( pack === FONT_AWESOME && fontPackReady( pack ) ) {
			const classes = faClass( key, weight );
			return faIcon( active ? faSolid( classes ) : classes );
		}

		const glyph = pack === LENZ && fontPackReady( pack ) ? lenzClass( key, active ) : '';
		return glyph ? faIcon( glyph ) : packSvg( pack, key, active );
	}

	/* =====================================================================
	 * Items: validation and resolution (mirrors Item_Resolver.php)
	 * =================================================================== */

	/** Why an item would be hidden on the site, or '' when it works. */
	function itemProblem( item ) {
		const type = itemTypes[ item.type ];

		if ( ! type || ! type.available ) {
			return i18n.needsWoo;
		}

		switch ( item.type ) {
			case 'link':
				return item.url ? '' : i18n.needsUrl;
			case 'reserve':
				return item.url || moduleData.theme.reserve.url ? '' : i18n.needsReserve;
			case 'archive':
				return moduleData.archiveTypes[ item.archive_type ] ? '' : i18n.needsArchive;
			case 'menu':
				return item.menu_source === 'wp_menu' && ! item.menu_id ? i18n.needsMenu : '';
			case 'content':
				return ( item.content_source === 'elementor' ? item.content_id : String( item.content_html ).trim() ) ? '' : i18n.needsContent;
			case 'selector':
				return item.selector || item.url ? '' : i18n.needsSelector;
		}

		return '';
	}

	function isVisibleTo( item, viewer ) {
		return item.visibility === 'all'
			|| ( item.visibility === 'guests' && viewer === 'guest' )
			|| ( item.visibility === 'members' && viewer === 'member' );
	}

	function itemLabel( item, viewer ) {
		if ( item.type === 'account' && viewer === 'guest' ) {
			return item.guest_label || i18n.login;
		}

		// Mirrors Item_Resolver: a booking button reuses the Lenz "Reserve" button text,
		// an archive button the content type's name.
		const fallbacks = { reserve: moduleData.theme.reserve.text, archive: moduleData.archiveTypes[ item.archive_type ] };
		const themeLabel = fallbacks[ item.type ] || '';

		return item.label || themeLabel || ( itemTypes[ item.type ] || {} ).default_label || '';
	}

	/**
	 * Items as the site would render them for a visitor, as view models.
	 *
	 * @param {Object} state  Settings.
	 * @param {string} viewer `member` or `guest`.
	 */
	function resolveItems( state, viewer ) {
		const models = state.items
			.filter( ( item ) => item.enabled && isVisibleTo( item, viewer ) && ! itemProblem( item ) )
			.map( ( item ) => {
				let icon = itemIcon( item, false, state );
				let iconActive = itemIcon( item, true, state );

				if ( item.type === 'account' && item.show_avatar && viewer === 'member' ) {
					icon = '<img class="lzp-bn__avatar" src="' + esc( moduleData.avatarUrl || '' ) + '" alt="">';
					iconActive = '';
				}

				let badge = item.badge ? '<span class="lzp-bn__badge lzp-bn__badge--text">' + esc( item.badge ) + '</span>' : '';
				if ( item.type === 'cart' ) {
					badge = '<span class="lzp-bn__badge lzp-bn-cart-count" data-count="2">2</span>' + badge;
				}

				return { item, label: itemLabel( item, viewer ), icon, iconActive, badge, featured: false };
			} );

		if ( models.length && hasFeaturedButton( state.style ) ) {
			const flagged = models.findIndex( ( model ) => model.item.featured );
			models[ flagged >= 0 ? flagged : Math.floor( models.length / 2 ) ].featured = true;
		}

		return models;
	}

	/* =====================================================================
	 * Rendering (mirrors Renderer.php + views/nav.php + Style_Vars.php)
	 * =================================================================== */

	function styleVars( state ) {
		const vars = {
			'--lzp-bn-h': state.layout.height + 'px',
			'--lzp-bn-icon': state.layout.icon_size + 'px',
			'--lzp-bn-radius': state.layout.radius + 'px',
			'--lzp-bn-offset': state.layout.offset + 'px',
			'--lzp-bn-max-w': state.layout.max_width + 'px',
			'--lzp-bn-stroke': String( state.icon_stroke ),
			'--lzp-bn-font-size': state.typography.font_size + 'px',
			'--lzp-bn-font-weight': String( state.typography.font_weight ),
		};

		// The admin loads Lenz's chosen font and prints --main-font on the preview, like the site.
		if ( state.typography.font_source === 'custom' && state.typography.font_family ) {
			vars[ '--lzp-bn-font' ] = state.typography.font_family + ', Vazirmatn, Tahoma, sans-serif';
		}

		moduleData.colorSlots.forEach( ( slot ) => {
			if ( state.colors[ slot ] ) {
				vars[ '--lzp-bn-o-' + slot ] = state.colors[ slot ];
			}
		} );

		return Object.keys( vars ).map( ( name ) => name + ':' + vars[ name ] ).join( ';' );
	}

	function navClasses( state, style, activeIndex ) {
		const labelModes = Boolean( moduleData.styles[ style ] && moduleData.styles[ style ].label_modes );
		const classes = [
			'lzp-bn',
			'lzp-bn--style-' + style,
			'lzp-bn--labels-' + ( labelModes ? state.label_mode : 'auto' ),
			'lzp-bn--motion-' + state.layout.motion,
			'lzp-bn--shadow-' + state.layout.shadow,
		];

		if ( activeIndex >= 0 ) {
			classes.push( 'has-active' );
		}
		if ( state.layout.glass ) {
			classes.push( 'lzp-bn--glass' );
		}

		return classes.join( ' ' );
	}

	/**
	 * @param {Object} state
	 * @param {Object} options { style, activeIndex, viewer }
	 * @return {string} Navigation markup.
	 */
	function renderNav( state, options ) {
		const style = options.style || state.style;
		const models = resolveItems( Object.assign( {}, state, { style } ), options.viewer || 'member' );
		const activeIndex = models.length ? Math.min( options.activeIndex, models.length - 1 ) : -1;
		const featuredIndex = models.findIndex( ( model ) => model.featured );

		const itemsHtml = models.map( ( model, index ) => {
			const classes = [ 'lzp-bn__item', 'lzp-bn__item--' + model.item.type ];
			if ( index === activeIndex ) {
				classes.push( 'is-active' );
			}
			if ( model.featured ) {
				classes.push( 'is-featured' );
			}
			if ( model.iconActive ) {
				classes.push( 'has-active-icon' );
			}

			return '<li class="' + classes.join( ' ' ) + '" style="--lzp-bn-i:' + index + '">' +
				'<a class="lzp-bn__link" href="#" data-preview-index="' + index + '" data-preview-type="' + esc( model.item.type ) + '">' +
					'<span class="lzp-bn__icon">' +
						'<span class="lzp-bn__glyph lzp-bn__glyph--base">' + model.icon + '</span>' +
						( model.iconActive ? '<span class="lzp-bn__glyph lzp-bn__glyph--active">' + model.iconActive + '</span>' : '' ) +
						model.badge +
					'</span>' +
					'<span class="lzp-bn__label">' + esc( model.label ) + '</span>' +
				'</a>' +
			'</li>';
		} ).join( '' );

		const layoutVars = '--lzp-bn-count:' + models.length + ';--lzp-bn-active:' + activeIndex + ';--lzp-bn-featured:' + featuredIndex + ';';

		return '<nav class="' + navClasses( state, style, activeIndex ) + '" style="' + esc( layoutVars + styleVars( state ) ) + '" data-count="' + models.length + '">' +
			'<div class="lzp-bn__bar" aria-hidden="true"><span class="lzp-bn__indicator"></span></div>' +
			'<ul class="lzp-bn__list">' + itemsHtml + '</ul>' +
		'</nav>';
	}

	/* =====================================================================
	 * Previews
	 * =================================================================== */

	const preview = {
		host: root.querySelector( '[data-lzp-preview-nav]' ),
		active: 0,
		viewer: 'member',
	};

	const styleStages = Array.from( root.querySelectorAll( '[data-lzp-style-stage]' ) );

	/** Style cards render a real phone-width bar, then scale it down to the card. */
	const STAGE_PHONE_WIDTH = 360;
	let renderQueued = false;

	function scaleStage( stage ) {
		stage.style.setProperty( '--lzp-stage-scale', String( Math.min( 1, stage.clientWidth / STAGE_PHONE_WIDTH ) ) );
	}

	function renderPreviews() {
		renderQueued = false;
		const state = store.get();

		preview.host.innerHTML = renderNav( state, { activeIndex: preview.active, viewer: preview.viewer } );

		styleStages.forEach( ( stage ) => {
			stage.innerHTML = '<span class="lzp-stage__viewport">' + renderNav( state, { style: stage.dataset.lzpStyleStage, activeIndex: 0, viewer: 'member' } ) + '</span>';
			scaleStage( stage );
		} );
	}

	function queueRender() {
		if ( ! renderQueued ) {
			renderQueued = true;
			window.requestAnimationFrame( renderPreviews );
		}
	}

	/** Tapping preview buttons moves the active state, like on the site. */
	function onPreviewClick( event ) {
		const link = event.target.closest( '.lzp-bn__link' );
		if ( ! link ) {
			return;
		}
		event.preventDefault();

		const nav = link.closest( '.lzp-bn' );
		const item = link.closest( '.lzp-bn__item' );
		const type = link.dataset.previewType;

		if ( type === 'cart' ) {
			item.classList.remove( 'is-bumped' );
			void item.offsetWidth;
			item.classList.add( 'is-bumped' );
		}

		const index = Number( link.dataset.previewIndex );
		nav.querySelectorAll( '.lzp-bn__item' ).forEach( ( node, i ) => node.classList.toggle( 'is-active', i === index ) );
		nav.style.setProperty( '--lzp-bn-active', String( index ) );
		nav.classList.add( 'has-active' );

		if ( nav.parentElement === preview.host ) {
			preview.active = index;
		}
	}

	function initPreview() {
		preview.host.addEventListener( 'click', onPreviewClick );

		if ( window.ResizeObserver ) {
			const observer = new ResizeObserver( ( entries ) => entries.forEach( ( entry ) => scaleStage( entry.target ) ) );
			styleStages.forEach( ( stage ) => observer.observe( stage ) );
		}

		styleStages.forEach( ( stage ) => stage.addEventListener( 'click', ( event ) => {
			// Let the card's radio receive the click, but animate the mini bar too.
			if ( event.target.closest( '.lzp-bn__link' ) ) {
				onPreviewClick( event );
				stage.closest( 'label' ).querySelector( 'input' ).click();
			}
		} ) );

		root.querySelectorAll( '[data-lzp-preview-user]' ).forEach( ( input ) => {
			input.addEventListener( 'change', () => {
				preview.viewer = input.value;
				queueRender();
			} );
		} );
	}

	/* =====================================================================
	 * Button list
	 * =================================================================== */

	const list = root.querySelector( '[data-lzp-items]' );
	const template = document.getElementById( 'lzp-item-template' );
	const counter = root.querySelector( '[data-lzp-item-count]' );
	const emptyState = root.querySelector( '[data-lzp-items-empty]' );
	const manyNote = root.querySelector( '[data-lzp-items-many]' );
	const addToggle = root.querySelector( '[data-lzp-add-toggle]' );
	const addMenu = document.getElementById( 'lzp-add-menu' );

	const openItems = new Set();
	let editingField = false; // True while a row edits the store; skips full list rebuilds.

	const getItems = () => store.get( 'items' ) || [];
	const findIndex = ( id ) => getItems().findIndex( ( item ) => item.id === id );

	function newId() {
		return 'i' + Math.random().toString( 36 ).slice( 2, 10 );
	}

	function makeItem( type ) {
		const definition = itemTypes[ type ];
		return Object.assign( LZP.clone( moduleData.itemDefaults ), {
			id: newId(),
			type,
			label: definition.default_label,
			icon: definition.icon,
		} );
	}

	/** Writes the item list back to the store. */
	function commitItems( items, options = {} ) {
		editingField = Boolean( options.inPlace );
		store.set( 'items', items );
		editingField = false;
	}

	function updateItem( id, field, value ) {
		const items = LZP.clone( getItems() );
		const item = items[ findIndex( id ) ];
		if ( ! item ) {
			return;
		}

		// Switching type: swap label/icon only if they were still the old type's defaults.
		if ( field === 'type' && itemTypes[ value ] ) {
			const previous = itemTypes[ item.type ] || {};
			if ( ! item.label || item.label === previous.default_label ) {
				item.label = itemTypes[ value ].default_label;
			}
			if ( item.icon_source === 'pack' && item.icon === previous.icon ) {
				item.icon = itemTypes[ value ].icon;
			}
		}

		// Only one item can be featured.
		if ( field === 'featured' && value ) {
			items.forEach( ( other ) => {
				other.featured = false;
			} );
		}

		item[ field ] = value;

		const needsRebuild = field === 'type' || field === 'featured';
		commitItems( items, { inPlace: ! needsRebuild } );
	}

	function readItemControl( control ) {
		if ( control.type === 'checkbox' ) {
			return control.checked;
		}
		if ( control.dataset.lzpType === 'number' ) {
			return parseInt( control.value, 10 ) || 0;
		}
		return control.value;
	}

	function fillOptions( select ) {
		const source = moduleData[ select.dataset.lzpOptions ];
		let options = [];

		if ( Array.isArray( source ) ) {
			options = [ [ '0', i18n.noneOption ] ].concat( source.map( ( entry ) => [ String( entry.id ), entry.name ] ) );
		} else if ( source ) {
			options = Object.keys( source ).map( ( key ) => [ key, source[ key ] ] );
		}

		select.innerHTML = options.map( ( [ value, label ] ) => '<option value="' + esc( value ) + '">' + esc( label ) + '</option>' ).join( '' );
	}

	/** Checkbox chips for a list setting (e.g. post types), from a `{ value: label }` source. */
	function fillChecks( group ) {
		const source = moduleData[ group.dataset.lzpOptions ] || {};

		group.innerHTML = Object.keys( source ).map( ( value ) => '<label class="lzp-check"><input type="checkbox" value="' + esc( value ) + '"><span>' + esc( source[ value ] ) + '</span></label>' ).join( '' );
	}

	/** Updates everything in a row that depends on the item, without rebuilding it. */
	function refreshRow( row, item ) {
		const state = store.get();
		const type = itemTypes[ item.type ] || {};
		const problem = itemProblem( item );

		row.classList.toggle( 'is-off', ! item.enabled );
		row.querySelector( '[data-item-icon]' ).innerHTML = itemIcon( item, false, state ) || packSvg( 'tabler', type.icon || 'home', false );
		row.querySelector( '[data-item-label]' ).textContent = itemLabel( item, 'member' );
		row.querySelector( '[data-item-type]' ).textContent = type.label || item.type;
		row.querySelector( '[data-item-type-help]' ).textContent = type.description || '';

		const featuredChip = row.querySelector( '[data-item-featured]' );
		featuredChip.hidden = ! ( item.featured && hasFeaturedButton( state.style ) );

		const audience = row.querySelector( '[data-item-audience]' );
		audience.hidden = item.visibility === 'all';
		audience.textContent = item.visibility === 'guests' ? i18n.guestsOnly : i18n.membersOnly;

		const warning = row.querySelector( '[data-item-warning]' );
		warning.hidden = ! problem;
		warning.textContent = problem;
		warning.title = problem ? i18n.hiddenOnSite : '';

		const iconPreview = row.querySelector( '[data-item-icon-preview]' );
		iconPreview.innerHTML = packGlyph( state.icon_pack, item.icon, false, state.fa_weight );

		const sheetTitle = row.querySelector( '[data-item-placeholder="label"]' );
		sheetTitle.placeholder = itemLabel( item, 'member' );

		row.querySelectorAll( '[data-item-show-if]' ).forEach( ( node ) => {
			node.hidden = ! LZP.evaluate( node.dataset.itemShowIf, ( key ) => item[ key ] );
		} );

		row.querySelectorAll( '[data-item-bind-list]' ).forEach( ( group ) => {
			const values = item[ group.dataset.itemBindList ] || [];
			group.querySelectorAll( 'input' ).forEach( ( input ) => {
				input.checked = values.includes( input.value );
			} );
		} );

		row.querySelectorAll( '[data-item-bind]' ).forEach( ( control ) => {
			const value = item[ control.dataset.itemBind ];
			if ( control.type === 'checkbox' ) {
				control.checked = Boolean( value );
			} else if ( control.type === 'radio' ) {
				control.checked = String( value ) === control.value;
			} else if ( document.activeElement !== control ) {
				control.value = value == null ? '' : value;
			}
		} );
	}

	function setOpen( row, open ) {
		const id = row.dataset.id;
		row.classList.toggle( 'is-open', open );
		row.querySelector( '.lzp-item__editor' ).hidden = ! open;
		row.querySelector( '.lzp-item__summary' ).setAttribute( 'aria-expanded', String( open ) );

		if ( open ) {
			openItems.add( id );
		} else {
			openItems.delete( id );
		}
	}

	function buildRow( item ) {
		const row = template.content.firstElementChild.cloneNode( true );
		row.dataset.id = item.id;

		row.querySelectorAll( 'select[data-lzp-options]' ).forEach( fillOptions );

		row.querySelectorAll( '[data-item-bind-list]' ).forEach( ( group ) => {
			fillChecks( group );
			group.addEventListener( 'change', () => {
				const values = Array.from( group.querySelectorAll( 'input:checked' ), ( input ) => input.value );
				updateItem( row.dataset.id, group.dataset.itemBindList, values );
			} );
		} );

		row.querySelectorAll( '[data-item-bind]' ).forEach( ( control ) => {
			const field = control.dataset.itemBind;

			// Radio groups need a unique name per row.
			if ( control.type === 'radio' ) {
				control.name = 'lzp-' + item.id + '-' + field;
			}

			const isChoice = control.type === 'checkbox' || control.type === 'radio' || control.tagName === 'SELECT';
			control.addEventListener( isChoice ? 'change' : 'input', () => {
				if ( control.type === 'radio' && ! control.checked ) {
					return;
				}
				updateItem( row.dataset.id, field, readItemControl( control ) );
			} );
		} );

		row.addEventListener( 'click', ( event ) => {
			const toggle = event.target.closest( '[data-item-toggle]' );
			if ( toggle ) {
				setOpen( row, ! row.classList.contains( 'is-open' ) );
				return;
			}

			const action = event.target.closest( '[data-item-action]' );
			if ( action ) {
				runItemAction( action.dataset.itemAction, row.dataset.id, action );
			}
		} );

		refreshRow( row, item );
		setOpen( row, openItems.has( item.id ) );

		return row;
	}

	async function runItemAction( action, id, button ) {
		const items = LZP.clone( getItems() );
		const index = findIndex( id );
		if ( index < 0 ) {
			return;
		}

		switch ( action ) {
			case 'duplicate': {
				if ( items.length >= moduleData.maxItems ) {
					LZP.toast( i18n.maxReached, 'error' );
					return;
				}
				const copy = Object.assign( LZP.clone( items[ index ] ), { id: newId(), featured: false } );
				items.splice( index + 1, 0, copy );
				commitItems( items );
				break;
			}

			case 'delete': {
				const confirmed = await LZP.confirm( {
					title: i18n.deleteTitle,
					message: i18n.deleteMessage.replace( '%s', itemLabel( items[ index ], 'member' ) ),
					confirmLabel: i18n.delete,
					danger: true,
				} );
				if ( confirmed ) {
					items.splice( index, 1 );
					openItems.delete( id );
					commitItems( items );
				}
				break;
			}

			case 'up':
			case 'down': {
				const target = index + ( action === 'up' ? -1 : 1 );
				if ( target < 0 || target >= items.length ) {
					return;
				}
				items.splice( target, 0, items.splice( index, 1 )[ 0 ] );
				commitItems( items );
				const moved = list.querySelector( '[data-id="' + id + '"] [data-item-action="' + action + '"]' );
				if ( moved ) {
					moved.focus();
				}
				break;
			}

			case 'pick-icon':
				IconPicker.open( id, button );
				break;

			case 'pick-image':
				pickImage( id );
				break;
		}
	}

	function renderList() {
		const items = getItems();

		list.innerHTML = '';
		items.forEach( ( item ) => list.appendChild( buildRow( item ) ) );

		counter.textContent = items.length + ' / ' + moduleData.maxItems;
		emptyState.hidden = items.length > 0;
		manyNote.hidden = items.filter( ( item ) => item.enabled ).length <= 5;
		addToggle.disabled = items.length >= moduleData.maxItems;
		addToggle.title = addToggle.disabled ? i18n.maxReached : '';

		if ( $ && $.fn.sortable ) {
			$( list ).sortable( 'refresh' );
		}
	}

	function refreshRows() {
		const items = getItems();
		list.querySelectorAll( '.lzp-item' ).forEach( ( row ) => {
			const item = items.find( ( entry ) => entry.id === row.dataset.id );
			if ( item ) {
				refreshRow( row, item );
			}
		} );
		manyNote.hidden = items.filter( ( item ) => item.enabled ).length <= 5;
	}

	function initSortable() {
		if ( ! $ || ! $.fn.sortable ) {
			return; // The ▲/▼ buttons still allow reordering.
		}

		$( list ).sortable( {
			handle: '.lzp-item__handle',
			items: '> .lzp-item',
			axis: 'y',
			tolerance: 'pointer',
			placeholder: 'lzp-item-placeholder',
			update() {
				const order = Array.from( list.children ).map( ( row ) => row.dataset.id );
				const byId = {};
				getItems().forEach( ( item ) => {
					byId[ item.id ] = item;
				} );
				commitItems( LZP.clone( order.map( ( id ) => byId[ id ] ).filter( Boolean ) ) );
			},
		} );
	}

	function initAddMenu() {
		const close = () => {
			addMenu.hidden = true;
			addToggle.setAttribute( 'aria-expanded', 'false' );
		};

		addToggle.addEventListener( 'click', () => {
			const opening = addMenu.hidden;
			addMenu.hidden = ! opening;
			addToggle.setAttribute( 'aria-expanded', String( opening ) );
			if ( opening ) {
				const first = addMenu.querySelector( 'button:not([disabled])' );
				if ( first ) {
					first.focus();
				}
			}
		} );

		addMenu.addEventListener( 'click', ( event ) => {
			const option = event.target.closest( '[data-lzp-add-type]' );
			if ( ! option || getItems().length >= moduleData.maxItems ) {
				return;
			}

			const item = makeItem( option.dataset.lzpAddType );
			openItems.add( item.id );
			commitItems( getItems().concat( [ item ] ) );
			close();

			const row = list.querySelector( '[data-id="' + item.id + '"]' );
			if ( row ) {
				row.scrollIntoView( { behavior: 'smooth', block: 'center' } );
				row.querySelector( '[data-item-bind="label"]' ).focus( { preventScroll: true } );
			}
		} );

		document.addEventListener( 'click', ( event ) => {
			if ( ! addMenu.hidden && ! event.target.closest( '.lzp-add' ) ) {
				close();
			}
		} );

		addMenu.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' ) {
				close();
				addToggle.focus();
			}
		} );
	}

	/* =====================================================================
	 * Media library (custom icon image)
	 * =================================================================== */

	function pickImage( id ) {
		if ( ! window.wp || ! window.wp.media ) {
			return;
		}

		const frame = window.wp.media( {
			title: i18n.chooseImage,
			button: { text: i18n.useImage },
			library: { type: 'image' },
			multiple: false,
		} );

		frame.on( 'select', () => {
			const attachment = frame.state().get( 'selection' ).first().toJSON();
			updateItem( id, 'icon_image', attachment.url );
			refreshRows();
		} );

		frame.open();
	}

	/* =====================================================================
	 * Icon picker dialog
	 * =================================================================== */

	const IconPicker = ( function () {
		const dialog = document.getElementById( 'lzp-icon-picker' );
		const grid = dialog.querySelector( '[data-lzp-icon-grid]' );
		const search = dialog.querySelector( '[data-lzp-icon-search]' );
		let itemId = null;
		let returnFocus = null;

		function render() {
			const state = store.get();
			const item = getItems()[ findIndex( itemId ) ] || {};
			const query = search.value.trim().toLowerCase();

			const matches = catalog.icons.filter( ( icon ) => ! query || ( icon.label + ' ' + icon.keywords + ' ' + icon.key ).toLowerCase().includes( query ) );

			grid.innerHTML = matches.length
				? matches.map( ( icon ) => {
					const glyph = packGlyph( state.icon_pack, icon.key, false, state.fa_weight );
					const selected = item.icon_source === 'pack' && item.icon === icon.key;
					return '<button type="button" class="lzp-icon-option" role="option" aria-selected="' + selected + '" data-icon="' + esc( icon.key ) + '">' + glyph + '<span>' + esc( icon.label ) + '</span></button>';
				} ).join( '' )
				: '<p class="lzp-field__help">' + esc( i18n.noIcons ) + '</p>';
		}

		function open( id, trigger ) {
			itemId = id;
			returnFocus = trigger;
			search.value = '';
			render();
			dialog.showModal();
			search.focus();
		}

		function close() {
			dialog.close();
		}

		search.addEventListener( 'input', render );

		grid.addEventListener( 'click', ( event ) => {
			const option = event.target.closest( '[data-icon]' );
			if ( ! option ) {
				return;
			}

			const items = LZP.clone( getItems() );
			const item = items[ findIndex( itemId ) ];
			if ( item ) {
				item.icon = option.dataset.icon;
				item.icon_source = 'pack';
				commitItems( items, { inPlace: true } );
				refreshRows();
			}
			close();
		} );

		dialog.addEventListener( 'click', ( event ) => {
			if ( event.target === dialog || event.target.closest( '[data-lzp-dialog-close]' ) ) {
				close();
			}
		} );

		dialog.addEventListener( 'close', () => {
			if ( returnFocus ) {
				returnFocus.focus();
			}
		} );

		return { open };
	}() );

	/* =====================================================================
	 * Boot
	 * =================================================================== */

	store.subscribe( ( path ) => {
		if ( ( path === '*' || path === 'items' ) && ! editingField ) {
			renderList();
		} else {
			refreshRows();
		}

		if ( path === 'icon_pack' || path === '*' ) {
			const pack = store.get( 'icon_pack' );
			loadPack( pack ).then( () => {
				refreshRows();
				queueRender();
			} );
		}

		queueRender();
	} );

	initPreview();
	initSortable();
	initAddMenu();
	renderList();

	// The fallback pack (Tabler) is needed for brand icons in most packs.
	loadPack( store.get( 'icon_pack' ) ).then( () => {
		refreshRows();
		queueRender();
	} );
	queueRender();
}( window.jQuery ) );
