/**
 * Lenz Plus — admin shell.
 *
 * Provides the pieces every module panel shares and exposes them as
 * `window.LZP` for module scripts:
 *   - store:    module settings with get/set by dot-path, dirty tracking and subscriptions
 *   - binding:  `[data-lzp-bind]` controls ⇄ store, `[data-lzp-show-if]` conditions
 *   - widgets:  tabs, colour fields, range read-outs
 *   - actions:  save (Ctrl/⌘+S), reset, dashboard module switches
 *   - feedback: toasts and a promise-based confirm dialog
 */
( function () {
	'use strict';

	const app = document.getElementById( 'lzp-admin' );
	if ( ! app ) {
		return;
	}

	const data = window.lzpAdmin || {};
	const i18n = data.i18n || {};

	/* ---------------------------------------------------------------------
	 * Utilities
	 * ------------------------------------------------------------------- */

	const clone = ( value ) => JSON.parse( JSON.stringify( value ) );

	function getPath( object, path ) {
		return path.split( '.' ).reduce( ( node, key ) => ( node == null ? undefined : node[ key ] ), object );
	}

	function setPath( object, path, value ) {
		const keys = path.split( '.' );
		const last = keys.pop();
		const target = keys.reduce( ( node, key ) => {
			if ( node[ key ] == null || typeof node[ key ] !== 'object' ) {
				node[ key ] = {};
			}
			return node[ key ];
		}, object );

		target[ last ] = value;
	}

	function escapeHtml( value ) {
		return String( value == null ? '' : value ).replace( /[&<>"']/g, ( char ) => (
			{ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ char ]
		) );
	}

	/**
	 * Evaluates a condition such as `style=floating|pill;!layout.glass`.
	 * Parts separated by `;` must all pass.
	 *
	 * @param {string}   expression Condition.
	 * @param {Function} lookup     Resolves a path to its current value.
	 */
	function evaluate( expression, lookup ) {
		return expression.split( ';' ).every( ( rawPart ) => {
			const part = rawPart.trim();
			if ( ! part ) {
				return true;
			}
			if ( part.charAt( 0 ) === '!' ) {
				return ! lookup( part.slice( 1 ) );
			}

			const eq = part.indexOf( '=' );
			if ( eq === -1 ) {
				return Boolean( lookup( part ) );
			}

			return part.slice( eq + 1 ).split( '|' ).includes( String( lookup( part.slice( 0, eq ) ) ) );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Store
	 * ------------------------------------------------------------------- */

	function createStore( initial ) {
		let state = clone( initial || {} );
		let savedSnapshot = JSON.stringify( state );
		const listeners = new Set();

		const emit = ( path ) => listeners.forEach( ( listener ) => listener( path, state ) );

		return {
			get: ( path ) => ( path ? getPath( state, path ) : state ),

			set( path, value ) {
				setPath( state, path, value );
				emit( path );
			},

			/** Replaces everything (after save/reset). `clean` marks it as saved. */
			replace( next, options = {} ) {
				state = clone( next );
				if ( options.clean ) {
					savedSnapshot = JSON.stringify( state );
				}
				emit( '*' );
			},

			isDirty: () => JSON.stringify( state ) !== savedSnapshot,

			subscribe( listener ) {
				listeners.add( listener );
				return () => listeners.delete( listener );
			},
		};
	}

	const store = data.settings ? createStore( data.settings ) : null;

	/* ---------------------------------------------------------------------
	 * Feedback: toasts and confirm dialog
	 * ------------------------------------------------------------------- */

	const toastHost = app.querySelector( '.lzp-toasts' );

	function toast( message, type = 'success' ) {
		const node = document.createElement( 'div' );
		node.className = 'lzp-toast lzp-toast--' + type;
		node.setAttribute( 'role', type === 'error' ? 'alert' : 'status' );
		node.textContent = message;
		toastHost.appendChild( node );

		window.setTimeout( () => {
			node.classList.add( 'is-leaving' );
			window.setTimeout( () => node.remove(), 300 );
		}, type === 'error' ? 5000 : 3000 );
	}

	/**
	 * @param {Object}  options
	 * @param {string}  options.title
	 * @param {string}  options.message
	 * @param {string}  options.confirmLabel
	 * @param {boolean} [options.danger]
	 * @return {Promise<boolean>}
	 */
	function confirmDialog( options ) {
		return new Promise( ( resolve ) => {
			const dialog = document.createElement( 'dialog' );
			dialog.className = 'lzp-dialog lzp-dialog--confirm';
			dialog.innerHTML =
				'<div class="lzp-dialog__head"><h2>' + escapeHtml( options.title ) + '</h2></div>' +
				'<div class="lzp-dialog__body"><p>' + escapeHtml( options.message ) + '</p></div>' +
				'<div class="lzp-dialog__actions">' +
					'<button type="button" class="lzp-btn lzp-btn--ghost" value="cancel">' + escapeHtml( i18n.cancel ) + '</button>' +
					'<button type="button" class="lzp-btn ' + ( options.danger ? 'lzp-btn--danger' : 'lzp-btn--primary' ) + '" value="ok">' + escapeHtml( options.confirmLabel ) + '</button>' +
				'</div>';

			app.appendChild( dialog );

			const finish = ( result ) => {
				dialog.close();
				dialog.remove();
				resolve( result );
			};

			dialog.addEventListener( 'click', ( event ) => {
				const button = event.target.closest( 'button[value]' );
				if ( button ) {
					finish( button.value === 'ok' );
				} else if ( event.target === dialog ) {
					finish( false ); // Backdrop click.
				}
			} );
			dialog.addEventListener( 'cancel', ( event ) => {
				event.preventDefault();
				finish( false );
			} );

			dialog.showModal();
			dialog.querySelector( 'button[value="cancel"]' ).focus();
		} );
	}

	/* ---------------------------------------------------------------------
	 * AJAX
	 * ------------------------------------------------------------------- */

	async function post( action, payload = {} ) {
		const body = new FormData();
		body.append( 'action', action );
		body.append( 'nonce', data.nonce );
		Object.keys( payload ).forEach( ( key ) => body.append( key, payload[ key ] ) );

		let json = null;
		try {
			const response = await fetch( data.ajaxUrl, { method: 'POST', credentials: 'same-origin', body } );
			json = await response.json();
		} catch ( error ) {
			throw new Error( i18n.saveFailed );
		}

		if ( ! json || ! json.success ) {
			throw new Error( ( json && json.data && json.data.message ) || i18n.saveFailed );
		}

		return json.data;
	}

	/* ---------------------------------------------------------------------
	 * Field binding
	 * ------------------------------------------------------------------- */

	function readControl( control ) {
		if ( control.type === 'checkbox' ) {
			return control.checked;
		}

		if ( control.dataset.lzpType === 'number' ) {
			const number = parseFloat( control.value );
			return Number.isFinite( number ) ? number : 0;
		}

		return control.value;
	}

	function writeControl( control, value ) {
		if ( control.type === 'checkbox' ) {
			control.checked = Boolean( value );
		} else if ( control.type === 'radio' ) {
			control.checked = String( value ) === control.value;
		} else if ( document.activeElement !== control || control.type === 'range' ) {
			// Never rewrite a text field while the user is typing in it.
			control.value = value == null ? '' : value;
		}

		if ( control.type === 'range' ) {
			paintRange( control );
		}
	}

	/** Colours the filled part of a range track. */
	function paintRange( control ) {
		const min = parseFloat( control.min ) || 0;
		const max = parseFloat( control.max ) || 100;
		const percent = ( ( parseFloat( control.value ) - min ) / ( max - min ) ) * 100;
		control.style.setProperty( '--lzp-fill', percent + '%' );
	}

	function formatOutput( value ) {
		return typeof value === 'number' && ! Number.isInteger( value ) ? value.toFixed( 2 ).replace( /0$/, '' ) : String( value );
	}

	function bindControls( scope ) {
		const controls = Array.from( scope.querySelectorAll( '[data-lzp-bind]' ) );
		const outputs = Array.from( scope.querySelectorAll( '[data-lzp-output]' ) );
		const conditionals = Array.from( scope.querySelectorAll( '[data-lzp-show-if]' ) );

		controls.forEach( ( control ) => {
			const isChoice = control.type === 'checkbox' || control.type === 'radio' || control.tagName === 'SELECT';

			control.addEventListener( isChoice ? 'change' : 'input', () => {
				if ( control.type === 'radio' && ! control.checked ) {
					return;
				}
				store.set( control.dataset.lzpBind, readControl( control ) );
			} );
		} );

		function sync() {
			controls.forEach( ( control ) => writeControl( control, store.get( control.dataset.lzpBind ) ) );
			outputs.forEach( ( output ) => {
				output.textContent = formatOutput( store.get( output.dataset.lzpOutput ) );
			} );
			conditionals.forEach( ( node ) => {
				node.hidden = ! evaluate( node.dataset.lzpShowIf, store.get );
			} );
		}

		store.subscribe( sync );
		sync();
	}

	/* ---------------------------------------------------------------------
	 * Tabs (remembers the last tab per module)
	 * ------------------------------------------------------------------- */

	function initTabs() {
		app.querySelectorAll( '[data-lzp-tabs]' ).forEach( ( list ) => {
			const storageKey = 'lzp-tab-' + list.dataset.lzpTabs;
			const tabs = Array.from( list.querySelectorAll( '[data-lzp-tab]' ) );

			function select( id, focus ) {
				tabs.forEach( ( tab ) => {
					const selected = tab.dataset.lzpTab === id;
					const panel = document.getElementById( tab.getAttribute( 'aria-controls' ) );

					tab.setAttribute( 'aria-selected', String( selected ) );
					tab.tabIndex = selected ? 0 : -1;
					if ( panel ) {
						panel.hidden = ! selected;
					}
					if ( selected && focus ) {
						tab.focus();
					}
				} );

				try {
					window.localStorage.setItem( storageKey, id );
				} catch ( error ) {
					// Remembering the tab is optional.
				}
			}

			tabs.forEach( ( tab ) => tab.addEventListener( 'click', () => select( tab.dataset.lzpTab ) ) );

			list.addEventListener( 'keydown', ( event ) => {
				if ( event.key !== 'ArrowLeft' && event.key !== 'ArrowRight' ) {
					return;
				}

				const rtl = getComputedStyle( list ).direction === 'rtl';
				const forward = ( event.key === 'ArrowRight' ) !== rtl;
				const index = tabs.findIndex( ( tab ) => tab.getAttribute( 'aria-selected' ) === 'true' );
				const next = tabs[ ( index + ( forward ? 1 : -1 ) + tabs.length ) % tabs.length ];

				event.preventDefault();
				select( next.dataset.lzpTab, true );
			} );

			let remembered = null;
			try {
				remembered = window.localStorage.getItem( storageKey );
			} catch ( error ) {
				remembered = null;
			}

			select( tabs.some( ( tab ) => tab.dataset.lzpTab === remembered ) ? remembered : tabs[ 0 ].dataset.lzpTab );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Colour fields: swatch (native picker) + free text (hex/rgba) + reset
	 * ------------------------------------------------------------------- */

	const RESET_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>';

	const colorProbe = document.createElement( 'canvas' ).getContext( '2d' );

	/** Converts any CSS colour to #rrggbb for <input type="color">. */
	function toHex( color ) {
		if ( ! color || ! colorProbe ) {
			return '#000000';
		}

		colorProbe.fillStyle = '#000000';
		colorProbe.fillStyle = color;
		const normalized = colorProbe.fillStyle;

		if ( normalized.charAt( 0 ) === '#' ) {
			return normalized;
		}

		const channels = normalized.match( /\d+(\.\d+)?/g ) || [ 0, 0, 0 ];
		return '#' + channels.slice( 0, 3 ).map( ( channel ) => Number( channel ).toString( 16 ).padStart( 2, '0' ) ).join( '' );
	}

	const isValidColor = ( value ) => window.CSS && CSS.supports( 'color', value );

	function initColorFields( scope ) {
		scope.querySelectorAll( '[data-lzp-color]' ).forEach( ( host ) => {
			const path = host.dataset.lzpColor;
			const fallback = host.dataset.fallback || '';
			const labelNode = host.parentElement.querySelector( '.lzp-field__label' );
			const label = labelNode ? labelNode.textContent.trim() : '';

			host.innerHTML =
				'<label class="lzp-color__swatch"><input type="color" tabindex="-1" aria-hidden="true"></label>' +
				'<input type="text" class="lzp-color__text" spellcheck="false" autocomplete="off" aria-label="' + escapeHtml( label ) + '" placeholder="' + escapeHtml( i18n.themeDefault ) + '">' +
				'<button type="button" class="lzp-icon-btn lzp-color__reset" title="' + escapeHtml( i18n.resetColor ) + '" aria-label="' + escapeHtml( i18n.resetColor ) + '">' + RESET_ICON + '</button>';

			const picker = host.querySelector( 'input[type="color"]' );
			const text = host.querySelector( '.lzp-color__text' );
			const swatch = host.querySelector( '.lzp-color__swatch' );

			function render() {
				const value = store.get( path ) || '';
				host.classList.toggle( 'has-value', value !== '' );
				swatch.style.setProperty( '--lzp-swatch', value || fallback );
				picker.value = toHex( value || fallback );
				if ( document.activeElement !== text ) {
					text.value = value;
				}
			}

			picker.addEventListener( 'input', () => store.set( path, picker.value ) );
			text.addEventListener( 'input', () => {
				const value = text.value.trim();
				if ( value === '' || isValidColor( value ) ) {
					store.set( path, value );
				}
			} );
			text.addEventListener( 'blur', render );
			host.querySelector( '.lzp-color__reset' ).addEventListener( 'click', () => store.set( path, '' ) );

			store.subscribe( render );
			render();
		} );
	}

	/* ---------------------------------------------------------------------
	 * Save / reset
	 * ------------------------------------------------------------------- */

	function setStatusDot( moduleId, enabled ) {
		const dot = app.querySelector( '[data-lzp-status="' + moduleId + '"]' );
		if ( dot ) {
			dot.classList.toggle( 'is-on', Boolean( enabled ) );
		}
	}

	function initSaving() {
		const saveButton = app.querySelector( '[data-lzp-save]' );
		const resetButton = app.querySelector( '[data-lzp-reset]' );
		const dirtyBadge = app.querySelector( '[data-lzp-dirty]' );
		const saveLabel = saveButton.querySelector( '[data-lzp-save-label]' );
		let saving = false;

		function refresh() {
			const dirty = store.isDirty();
			saveButton.disabled = saving || ! dirty;
			dirtyBadge.hidden = ! dirty;
		}

		async function save() {
			if ( saving || ! store.isDirty() ) {
				return;
			}

			saving = true;
			saveButton.classList.add( 'is-busy' );
			saveLabel.textContent = i18n.saving;
			refresh();

			try {
				const result = await post( 'lzp_save_settings', { module: data.module, settings: JSON.stringify( store.get() ) } );
				store.replace( result.settings, { clean: true } );
				setStatusDot( data.module, result.settings.enabled );
				toast( i18n.saved );
			} catch ( error ) {
				toast( error.message, 'error' );
			} finally {
				saving = false;
				saveButton.classList.remove( 'is-busy' );
				saveLabel.textContent = i18n.save;
				refresh();
			}
		}

		async function reset() {
			const confirmed = await confirmDialog( {
				title: i18n.resetTitle,
				message: i18n.resetMessage,
				confirmLabel: i18n.resetConfirm,
				danger: true,
			} );
			if ( ! confirmed ) {
				return;
			}

			try {
				const result = await post( 'lzp_reset_settings', { module: data.module } );
				store.replace( result.settings, { clean: true } );
				setStatusDot( data.module, result.settings.enabled );
				toast( i18n.resetDone );
			} catch ( error ) {
				toast( error.message, 'error' );
			}
		}

		saveButton.addEventListener( 'click', save );
		resetButton.addEventListener( 'click', reset );

		document.addEventListener( 'keydown', ( event ) => {
			if ( ( event.metaKey || event.ctrlKey ) && event.key.toLowerCase() === 's' ) {
				event.preventDefault();
				save();
			}
		} );

		window.addEventListener( 'beforeunload', ( event ) => {
			if ( store.isDirty() ) {
				event.preventDefault();
				event.returnValue = i18n.unsavedLeave;
			}
		} );

		store.subscribe( refresh );
		refresh();
	}

	/* ---------------------------------------------------------------------
	 * Dashboard: enable/disable modules instantly
	 * ------------------------------------------------------------------- */

	function initModuleSwitches() {
		app.querySelectorAll( '[data-lzp-module-toggle]' ).forEach( ( input ) => {
			input.addEventListener( 'change', async () => {
				const moduleId = input.dataset.lzpModuleToggle;
				const enabled = input.checked;
				input.disabled = true;

				try {
					await post( 'lzp_toggle_module', { module: moduleId, enabled: enabled ? '1' : '0' } );
					setStatusDot( moduleId, enabled );
					toast( enabled ? i18n.enabled : i18n.disabled );
				} catch ( error ) {
					input.checked = ! enabled;
					toast( error.message, 'error' );
				} finally {
					input.disabled = false;
				}
			} );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Boot
	 * ------------------------------------------------------------------- */

	initTabs();
	initModuleSwitches();

	if ( store ) {
		bindControls( app );
		initColorFields( app );
		initSaving();
	}

	window.LZP = {
		data,
		i18n,
		store,
		clone,
		escapeHtml,
		evaluate,
		getPath,
		toast,
		confirm: confirmDialog,
		post,
	};
}() );
