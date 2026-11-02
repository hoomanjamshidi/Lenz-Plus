/**
 * Lenz+ studio panel: progressive enhancements. Every form and link works
 * without this file; it adds background uploads with previews, picking files
 * from the media library, video links in galleries, reordering of gallery
 * photos, the simple text editor, confirmations, a warning before leaving
 * unsaved changes and self-hiding messages.
 *
 * Document-wide listeners are bound once; the parts of a page are set up by
 * initPage(), which runs on load and again on `lzp:page`, the event
 * panel-nav.js fires after it swaps in a page without reloading. Unsaved
 * changes are flagged on <html data-lzp-dirty> so the navigation sees them.
 *
 * It also checks for new orders, bookings and requests once a minute on
 * devices whose owner switched notifications on (My account), shows them
 * through the panel's service worker and puts the waiting count on the
 * home-screen icon.
 *
 * Data from PHP: window.lzpPanel { ajaxUrl, uploadNonce, notify, i18n };
 * link reading from window.lzpMediaLink (shared media-link.js).
 */
( function () {
	'use strict';

	const config = window.lzpPanel || {};
	const i18n = config.i18n || {};

	/* ---------------------------------------------------------------------
	 * Messages
	 * ------------------------------------------------------------------- */

	function initToasts() {
		const toast = document.querySelector( '[data-lzp-toast]' );

		// Drop the message key from the address, so a reload does not repeat it.
		if ( window.history.replaceState && window.location.search.indexOf( 'lzp_msg=' ) !== -1 ) {
			const url = new URL( window.location.href );
			url.searchParams.delete( 'lzp_msg' );
			window.history.replaceState( window.history.state, '', url.toString() );
		}

		if ( toast && toast.classList.contains( 'lzp-toast--ok' ) ) {
			window.setTimeout( () => {
				toast.classList.add( 'is-leaving' );
				window.setTimeout( () => toast.remove(), 450 );
			}, 4000 );
		}
	}

	function flash( text ) {
		const main = document.getElementById( 'lzp-main' );
		if ( ! main ) {
			return;
		}

		const toast = document.createElement( 'div' );
		toast.className = 'lzp-toast lzp-toast--ok';
		toast.setAttribute( 'role', 'status' );
		toast.textContent = text;
		main.prepend( toast );
		window.setTimeout( () => toast.remove(), 3000 );
	}

	/* ---------------------------------------------------------------------
	 * Forms: confirmations, busy buttons, unsaved changes
	 * ------------------------------------------------------------------- */

	function setDirty( value ) {
		if ( value ) {
			document.documentElement.dataset.lzpDirty = '1';
		} else {
			delete document.documentElement.dataset.lzpDirty;
		}
	}

	function isDirty() {
		return document.documentElement.dataset.lzpDirty === '1';
	}

	function bindForms() {
		document.addEventListener( 'click', ( event ) => {
			const button = event.target.closest( '[data-lzp-confirm]' );
			if ( button && ! window.confirm( button.dataset.lzpConfirm ) ) {
				event.preventDefault();
			}
		} );

		[ 'input', 'change' ].forEach( ( type ) => {
			document.addEventListener( type, ( event ) => {
				if ( event.target.closest && event.target.closest( 'form.lzp-edit' ) ) {
					setDirty( true );
				}
			} );
		} );

		document.addEventListener( 'change', ( event ) => {
			const input = event.target.closest && event.target.closest( '[data-lzp-autosubmit]' );
			if ( input && input.files.length ) {
				input.form.requestSubmit ? input.form.requestSubmit() : input.form.submit();
			}
		} );

		document.addEventListener( 'submit', ( event ) => {
			const form = event.target;

			// Wait for background uploads, then send the form again.
			if ( Number( form.dataset.lzpPending || 0 ) > 0 ) {
				event.preventDefault();
				form.dataset.lzpResubmit = '1';
				return;
			}

			setDirty( false );
			const button = form.querySelector( '[data-lzp-saving]' );
			if ( button ) {
				button.classList.add( 'is-busy' );
				const label = button.querySelector( 'span' ) || button;
				label.textContent = button.dataset.lzpSaving;
			}
		} );

		window.addEventListener( 'beforeunload', ( event ) => {
			if ( isDirty() ) {
				event.preventDefault();
				event.returnValue = i18n.unsaved || '';
			}
		} );
	}

	function pending( form, change ) {
		if ( ! form ) {
			return;
		}

		const count = Math.max( 0, Number( form.dataset.lzpPending || 0 ) + change );
		form.dataset.lzpPending = String( count );

		if ( count === 0 && form.dataset.lzpResubmit === '1' ) {
			delete form.dataset.lzpResubmit;
			form.requestSubmit ? form.requestSubmit() : form.submit();
		}
	}

	/* ---------------------------------------------------------------------
	 * Uploads
	 * ------------------------------------------------------------------- */

	/** Sends one file to admin-ajax; resolves with the attachment data. */
	function upload( file, onProgress ) {
		return new Promise( ( resolve, reject ) => {
			const body = new FormData();
			body.append( 'action', 'lzp_panel_upload' );
			body.append( 'nonce', config.uploadNonce );
			body.append( 'file', file );

			const xhr = new XMLHttpRequest();
			xhr.open( 'POST', config.ajaxUrl );
			xhr.responseType = 'json';
			xhr.upload.addEventListener( 'progress', ( event ) => {
				if ( event.lengthComputable && onProgress ) {
					onProgress( event.loaded / event.total );
				}
			} );
			xhr.addEventListener( 'load', () => {
				const json = xhr.response;
				if ( json && json.success ) {
					resolve( json.data );
				} else {
					reject( new Error( ( json && json.data && json.data.message ) || i18n.uploadError ) );
				}
			} );
			xhr.addEventListener( 'error', () => reject( new Error( i18n.uploadError ) ) );
			xhr.send( body );
		} );
	}

	/**
	 * A new gallery item: a copy of the <template> printed by Uploads::field(),
	 * which is Uploads::item() itself, so PHP and JS share one markup.
	 */
	function mediaItem( field ) {
		const template = field.querySelector( 'template' );
		const item = template.content.firstElementChild.cloneNode( true );
		item.querySelectorAll( '[data-lzp-move]' ).forEach( ( button ) => {
			button.hidden = ! field.hasAttribute( 'data-multiple' );
		} );
		return item;
	}

	/**
	 * Puts the attachment ID into an item's hidden and remove inputs.
	 */
	function setItemId( item, id ) {
		item.dataset.id = id;
		item.querySelectorAll( 'input' ).forEach( ( input ) => {
			input.value = id;
		} );
	}

	function setPreview( item, data, fallbackUrl ) {
		const old = item.querySelector( ':scope > img, :scope > .lzp-media__video' );
		if ( old ) {
			old.remove();
		}

		let preview;
		if ( data.type === 'video' || ( ! data.thumb && ! fallbackUrl ) ) {
			preview = document.createElement( 'span' );
			preview.className = 'lzp-media__video';
			preview.textContent = data.name || '';
		} else {
			preview = document.createElement( 'img' );
			preview.alt = data.name || '';
			preview.src = data.thumb || fallbackUrl;
		}
		item.prepend( preview );
	}

	/* ---------------------------------------------------------------------
	 * Media library picker
	 * ------------------------------------------------------------------- */

	/** One page of the library (Uploads::ajax_library()). */
	function fetchLibrary( type, search, page ) {
		const body = new FormData();
		body.append( 'action', 'lzp_panel_library' );
		body.append( 'nonce', config.uploadNonce );
		body.append( 'type', type );
		body.append( 's', search );
		body.append( 'page', String( page ) );

		return fetch( config.ajaxUrl, { method: 'POST', body, credentials: 'same-origin' } )
			.then( ( response ) => response.json() )
			.then( ( json ) => {
				if ( ! json || ! json.success ) {
					throw new Error( ( json && json.data && json.data.message ) || i18n.libraryError );
				}
				return json.data;
			} );
	}

	function element( tag, className, text ) {
		const node = document.createElement( tag );
		if ( className ) {
			node.className = className;
		}
		if ( text ) {
			node.textContent = text;
		}
		return node;
	}

	/**
	 * Opens the library in a modal sheet; resolves with the chosen files
	 * (attachment data, in the order they were tapped), or [] when closed.
	 *
	 * @param {boolean} multiple Whether several files can be chosen.
	 * @param {boolean} videos   Whether videos are offered besides photos.
	 */
	function pickFromLibrary( multiple, videos ) {
		return new Promise( ( resolve ) => {
			const chosen = new Map();
			let type = videos ? '' : 'image';
			let search = '';
			let page = 1;
			let request = 0;
			let timer = 0;

			const dialog = element( 'dialog', 'lzp-picker' );
			dialog.setAttribute( 'aria-label', i18n.library || '' );

			const head = element( 'div', 'lzp-picker__head' );
			const title = element( 'h2', 'lzp-picker__title', i18n.library );
			const close = element( 'button', 'lzp-icon-btn lzp-picker__close' );
			close.type = 'button';
			close.setAttribute( 'aria-label', i18n.close || '' );
			close.textContent = '×';
			head.append( title, close );

			const tools = element( 'div', 'lzp-picker__tools' );
			const searchBox = element( 'input', 'lzp-input' );
			searchBox.type = 'search';
			searchBox.placeholder = i18n.librarySearch || '';
			searchBox.setAttribute( 'aria-label', i18n.librarySearch || '' );
			tools.append( searchBox );

			const chips = element( 'div', 'lzp-chips lzp-picker__chips' );
			if ( videos ) {
				[ [ '', i18n.all ], [ 'image', i18n.photos ], [ 'video', i18n.videos ] ].forEach( ( [ value, label ] ) => {
					const chip = element( 'button', 'lzp-chip', label );
					chip.type = 'button';
					chip.dataset.type = value;
					if ( value === type ) {
						chip.setAttribute( 'aria-current', 'true' );
					}
					chips.append( chip );
				} );
				tools.append( chips );
			}

			const grid = element( 'ul', 'lzp-picker__grid' );
			const status = element( 'p', 'lzp-picker__status' );
			status.setAttribute( 'aria-live', 'polite' );
			const more = element( 'button', 'lzp-btn lzp-btn--ghost lzp-picker__more', i18n.loadMore );
			more.type = 'button';
			more.hidden = true;

			const foot = element( 'div', 'lzp-picker__foot' );
			const confirm = element( 'button', 'lzp-btn lzp-btn--primary lzp-btn--block' );
			confirm.type = 'button';
			confirm.disabled = true;
			foot.append( confirm );

			const body = element( 'div', 'lzp-picker__body' );
			body.append( grid, status, more );
			dialog.append( head, tools, body, foot );

			function updateConfirm() {
				confirm.disabled = chosen.size === 0;
				confirm.textContent = chosen.size > 1 ? ( i18n.addCount || '%d' ).replace( '%d', String( chosen.size ) ) : i18n.add;
			}

			function tile( data ) {
				const item = element( 'li' );
				const button = element( 'button', 'lzp-picker__item' );
				button.type = 'button';
				button.setAttribute( 'aria-pressed', String( chosen.has( String( data.id ) ) ) );
				if ( data.thumb ) {
					const image = element( 'img' );
					image.src = data.thumb;
					image.alt = data.name || '';
					image.loading = 'lazy';
					button.append( image );
				} else {
					button.append( element( 'span', 'lzp-media__video', data.name ) );
				}
				button.addEventListener( 'click', () => {
					const key = String( data.id );
					if ( chosen.has( key ) ) {
						chosen.delete( key );
					} else {
						if ( ! multiple ) {
							chosen.clear();
							grid.querySelectorAll( '[aria-pressed="true"]' ).forEach( ( other ) => other.setAttribute( 'aria-pressed', 'false' ) );
						}
						chosen.set( key, data );
					}
					button.setAttribute( 'aria-pressed', String( chosen.has( key ) ) );
					updateConfirm();
				} );
				item.append( button );
				return item;
			}

			function load( reset ) {
				const current = ++request;
				if ( reset ) {
					page = 1;
					grid.textContent = '';
				}
				status.textContent = i18n.loading || '';
				more.hidden = true;

				fetchLibrary( type, search, page )
					.then( ( data ) => {
						if ( current !== request ) {
							return;
						}
						data.items.forEach( ( item ) => grid.append( tile( item ) ) );
						status.textContent = grid.children.length ? '' : i18n.libraryEmpty;
						more.hidden = ! data.more;
					} )
					.catch( ( error ) => {
						if ( current === request ) {
							status.textContent = error.message;
						}
					} );
			}

			chips.addEventListener( 'click', ( event ) => {
				const chip = event.target.closest( '[data-type]' );
				if ( ! chip ) {
					return;
				}
				chips.querySelectorAll( '[aria-current]' ).forEach( ( other ) => other.removeAttribute( 'aria-current' ) );
				chip.setAttribute( 'aria-current', 'true' );
				type = chip.dataset.type;
				load( true );
			} );

			searchBox.addEventListener( 'input', () => {
				window.clearTimeout( timer );
				timer = window.setTimeout( () => {
					search = searchBox.value.trim();
					load( true );
				}, 300 );
			} );

			more.addEventListener( 'click', () => {
				page++;
				load( false );
			} );

			let result = [];
			close.addEventListener( 'click', () => dialog.close() );
			confirm.addEventListener( 'click', () => {
				result = Array.from( chosen.values() );
				dialog.close();
			} );
			dialog.addEventListener( 'click', ( event ) => {
				if ( event.target === dialog ) {
					dialog.close();
				}
			} );
			dialog.addEventListener( 'close', () => {
				dialog.remove();
				resolve( result );
			} );

			updateConfirm();
			document.body.append( dialog );
			dialog.showModal();
			load( true );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Media fields
	 * ------------------------------------------------------------------- */

	/** Adds a ready item (library file or link) unless the field has it. */
	function addReadyItem( field, data ) {
		const list = field.querySelector( '.lzp-media__list' );
		const key = String( data.id );
		if ( Array.from( list.querySelectorAll( '.lzp-media__item' ) ).some( ( item ) => item.dataset.id === key ) ) {
			return;
		}
		if ( ! field.hasAttribute( 'data-multiple' ) ) {
			list.querySelectorAll( '.lzp-media__item' ).forEach( ( item ) => item.remove() );
		}

		const item = mediaItem( field );
		setItemId( item, key );
		setPreview( item, data );
		list.insertBefore( item, field.querySelector( '.lzp-media__add' ) );
		setDirty( true );
	}

	/** The video link typed into a gallery's link box, added to its list. */
	function addLink( field, box ) {
		const links = window.lzpMediaLink;
		const url = links ? links.normalize( box.value ) : box.value.trim();
		if ( ! links || ! links.isUrl( url ) ) {
			window.alert( i18n.linkInvalid );
			box.focus();
			return;
		}

		const image = links.isImage( url );
		addReadyItem( field, {
			id: url,
			type: image ? 'image' : 'video',
			thumb: image ? url : '',
			name: links.isAparat( url ) ? i18n.aparat : new URL( url ).hostname.replace( /^www\./, '' ),
		} );
		box.value = '';
	}

	function initMediaField( field ) {
		const list = field.querySelector( '.lzp-media__list' );
		const add = field.querySelector( '.lzp-media__add' );
		const file = field.querySelector( '.lzp-media__file' );
		const multiple = field.hasAttribute( 'data-multiple' );
		const pick = field.querySelector( '[data-lzp-pick]' );
		const linkButton = field.querySelector( '[data-lzp-add-link]' );

		// The library and the link button need this script; without it the link box posts with the form.
		if ( pick && typeof HTMLDialogElement === 'function' ) {
			pick.hidden = false;
			pick.addEventListener( 'click', () => {
				pickFromLibrary( multiple, ( field.dataset.accept || '' ).indexOf( 'video' ) !== -1 ).then( ( files ) => {
					files.forEach( ( data ) => addReadyItem( field, data ) );
				} );
			} );
		}

		if ( linkButton ) {
			const box = field.querySelector( '.lzp-media__link textarea' );
			linkButton.hidden = false;
			linkButton.addEventListener( 'click', () => addLink( field, box ) );
		}

		if ( multiple ) {
			field.querySelectorAll( '[data-lzp-move]' ).forEach( ( button ) => {
				button.hidden = false;
			} );
		}

		// With JavaScript the remove checkbox removes the item at once.
		field.addEventListener( 'change', ( event ) => {
			const box = event.target.closest( '.lzp-media__remove input' );
			if ( box && box.checked ) {
				box.closest( '.lzp-media__item' ).remove();
				setDirty( true );
			}
		} );

		field.addEventListener( 'click', ( event ) => {
			const move = event.target.closest( '[data-lzp-move]' );
			if ( ! move ) {
				return;
			}

			const item = move.closest( '.lzp-media__item' );
			if ( move.dataset.lzpMove === '-1' && item.previousElementSibling ) {
				list.insertBefore( item, item.previousElementSibling );
			} else if ( move.dataset.lzpMove === '1' && item.nextElementSibling && item.nextElementSibling !== add ) {
				list.insertBefore( item.nextElementSibling, item );
			}
			move.focus();
			setDirty( true );
		} );

		if ( ! file ) {
			return;
		}

		file.addEventListener( 'change', () => {
			const files = Array.from( file.files );
			// The files go up now; the form must not send them a second time.
			file.value = '';
			if ( ! multiple ) {
				list.querySelectorAll( '.lzp-media__item' ).forEach( ( item ) => item.remove() );
			}

			files.slice( 0, multiple ? files.length : 1 ).forEach( ( chosen ) => {
				const item = mediaItem( field );
				const local = chosen.type.indexOf( 'image' ) === 0 ? URL.createObjectURL( chosen ) : '';
				setPreview( item, { type: local ? 'image' : 'video', name: chosen.name }, local );
				item.classList.add( 'is-uploading' );
				list.insertBefore( item, add );
				pending( field.closest( 'form' ), 1 );
				setDirty( true );

				upload( chosen, ( share ) => item.style.setProperty( '--lzp-progress', String( share ) ) )
					.then( ( data ) => {
						setItemId( item, data.id );
						setPreview( item, data, local );
						item.classList.remove( 'is-uploading' );
					} )
					.catch( ( error ) => {
						item.remove();
						window.alert( error.message );
					} )
					.finally( () => {
						if ( local ) {
							window.setTimeout( () => URL.revokeObjectURL( local ), 60000 );
						}
						pending( field.closest( 'form' ), -1 );
					} );
			} );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Simple editor
	 * ------------------------------------------------------------------- */

	const BLOCKS = /<(p|h[1-6]|ul|ol|blockquote|div|figure|table|pre)\b/i;

	/** WordPress stores classic text without <p>: give the editor paragraphs. */
	function toParagraphs( html ) {
		if ( ! html.trim() || BLOCKS.test( html ) ) {
			return html;
		}

		return html
			.split( /\n\s*\n/ )
			.map( ( part ) => '<p>' + part.trim().replace( /\n/g, '<br>' ) + '</p>' )
			.join( '' );
	}

	function initEditor( editor ) {
		const bar = editor.querySelector( '.lzp-editor__bar' );
		const area = editor.querySelector( '[data-lzp-editor-area]' );
		const source = editor.querySelector( '.lzp-editor__source' );
		const imageInput = editor.querySelector( '[data-lzp-editor-image]' );
		let range = null;

		area.innerHTML = toParagraphs( source.value );
		area.contentEditable = 'true';
		area.hidden = false;
		bar.hidden = false;
		editor.classList.add( 'is-ready' );
		document.execCommand( 'defaultParagraphSeparator', false, 'p' );

		const sync = () => {
			source.value = area.innerHTML.trim() === '<p><br></p>' ? '' : area.innerHTML;
		};

		const saveRange = () => {
			const selection = window.getSelection();
			if ( selection.rangeCount && area.contains( selection.anchorNode ) ) {
				range = selection.getRangeAt( 0 ).cloneRange();
			}
		};

		const restoreRange = () => {
			area.focus();
			if ( range ) {
				const selection = window.getSelection();
				selection.removeAllRanges();
				selection.addRange( range );
			}
		};

		const currentBlock = () => String( document.queryCommandValue( 'formatBlock' ) || '' ).toLowerCase().replace( /[<>]/g, '' );

		const updateState = () => {
			bar.querySelectorAll( '[data-cmd]' ).forEach( ( tool ) => {
				const cmd = tool.dataset.cmd;
				let active = false;
				if ( cmd === 'formatBlock' ) {
					active = tool.dataset.arg !== 'p' && currentBlock() === tool.dataset.arg;
				} else if ( [ 'bold', 'italic', 'insertUnorderedList', 'insertOrderedList' ].indexOf( cmd ) !== -1 ) {
					active = document.queryCommandState( cmd );
				}
				tool.classList.toggle( 'is-active', active );
				tool.setAttribute( 'aria-pressed', String( active ) );
			} );
		};

		// Keep the text selection while a toolbar button is pressed.
		bar.addEventListener( 'mousedown', ( event ) => {
			if ( event.target.closest( 'button' ) ) {
				event.preventDefault();
			}
		} );

		bar.addEventListener( 'click', ( event ) => {
			const tool = event.target.closest( '[data-cmd]' );
			if ( ! tool ) {
				return;
			}

			restoreRange();
			const cmd = tool.dataset.cmd;
			if ( cmd === 'formatBlock' ) {
				// Pressing an active heading or quote turns the block back into a paragraph.
				const tag = currentBlock() === tool.dataset.arg ? 'p' : tool.dataset.arg;
				document.execCommand( 'formatBlock', false, '<' + tag + '>' );
			} else if ( cmd === 'createLink' ) {
				const url = window.prompt( i18n.linkPrompt, 'https://' );
				if ( url && url !== 'https://' ) {
					document.execCommand( 'createLink', false, url );
				}
			} else {
				document.execCommand( cmd, false, null );
			}

			sync();
			saveRange();
			updateState();
			setDirty( true );
		} );

		if ( imageInput ) {
			imageInput.addEventListener( 'change', () => {
				const chosen = imageInput.files[ 0 ];
				imageInput.value = '';
				if ( ! chosen ) {
					return;
				}

				const form = editor.closest( 'form' );
				pending( form, 1 );
				upload( chosen )
					.then( ( data ) => {
						restoreRange();
						const image = '<img src="' + encodeURI( data.url ) + '" alt="" class="wp-image-' + Number( data.id ) + '">';
						document.execCommand( 'insertHTML', false, image );
						sync();
						setDirty( true );
					} )
					.catch( ( error ) => window.alert( error.message ) )
					.finally( () => pending( form, -1 ) );
			} );
		}

		// Pasted text arrives plain, so pages from Word or other sites bring no stray styles.
		area.addEventListener( 'paste', ( event ) => {
			const text = ( event.clipboardData || window.clipboardData ).getData( 'text/plain' );
			event.preventDefault();
			document.execCommand( 'insertText', false, text );
		} );

		area.addEventListener( 'input', sync );
		area.addEventListener( 'keyup', () => {
			saveRange();
			updateState();
		} );
		area.addEventListener( 'mouseup', () => {
			saveRange();
			updateState();
		} );
		area.addEventListener( 'blur', saveRange );
		document.addEventListener( 'selectionchange', () => {
			if ( document.activeElement === area ) {
				saveRange();
				updateState();
			}
		} );

		const form = editor.closest( 'form' );
		if ( form ) {
			form.addEventListener( 'submit', sync );
		}
	}

	/* ---------------------------------------------------------------------
	 * Copy buttons
	 * ------------------------------------------------------------------- */

	function initCopy() {
		if ( ! navigator.clipboard ) {
			return;
		}

		document.querySelectorAll( '[data-lzp-copy]:not([data-lzp-ready])' ).forEach( ( button ) => {
			button.dataset.lzpReady = '';
			button.hidden = false;
			button.addEventListener( 'click', () => {
				navigator.clipboard.writeText( button.dataset.lzpCopy ).then( () => flash( i18n.copied ) );
			} );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Notifications
	 * ------------------------------------------------------------------- */

	const NOTIFY_KEY = 'lzpPanelNotify';
	const notify = config.notify || null;
	let pulseTimer = 0;
	let pulseAfter = 0;

	function storage( key, value ) {
		try {
			if ( undefined === value ) {
				return window.localStorage.getItem( key );
			}
			if ( null === value ) {
				window.localStorage.removeItem( key );
			} else {
				window.localStorage.setItem( key, value );
			}
		} catch ( error ) {
			// Private windows may refuse storage: notifications just stay off.
		}

		return null;
	}

	function notifyOn() {
		return !! notify && 'Notification' in window && 'granted' === Notification.permission && '1' === storage( NOTIFY_KEY );
	}

	function worker() {
		if ( ! notify || ! notify.worker || ! ( 'serviceWorker' in navigator ) ) {
			return Promise.resolve( null );
		}

		return navigator.serviceWorker.register( notify.worker, { scope: notify.scope } ).then( () => navigator.serviceWorker.ready ).catch( () => null );
	}

	function show( item ) {
		const options = { body: item.body, tag: item.tag, icon: notify.icon, data: { url: item.url } };

		return worker().then( ( registration ) => {
			if ( registration ) {
				return registration.showNotification( item.title, options );
			}

			const shown = new Notification( item.title, options );
			shown.onclick = () => {
				window.focus();
				window.location.href = item.url;
			};

			return null;
		} );
	}

	function setBadge( count ) {
		if ( ! ( 'setAppBadge' in navigator ) ) {
			return;
		}

		( count > 0 ? navigator.setAppBadge( count ) : navigator.clearAppBadge() ).catch( () => {} );
	}

	/** Asks the site what is new, then schedules the next check while the page is visible. */
	function pulse() {
		window.clearTimeout( pulseTimer );
		if ( ! notifyOn() || document.hidden ) {
			return;
		}

		const body = new FormData();
		body.append( 'action', notify.action );
		body.append( 'nonce', notify.nonce );
		body.append( 'after', String( pulseAfter ) );

		window.fetch( config.ajaxUrl, { method: 'POST', body, credentials: 'same-origin' } )
			.then( ( response ) => response.json() )
			.then( ( result ) => {
				if ( ! result || ! result.success ) {
					return;
				}

				pulseAfter = result.data.now;
				setBadge( result.data.badge );
				result.data.items.forEach( show );
			} )
			.catch( () => {} )
			.then( () => {
				pulseTimer = window.setTimeout( pulse, ( notify.every || 60 ) * 1000 );
			} );
	}

	function startPulse() {
		if ( ! notify ) {
			return;
		}

		document.addEventListener( 'visibilitychange', () => {
			if ( ! document.hidden ) {
				pulse();
			}
		} );
		pulse();
	}

	function initNotifySwitch() {
		const toggle = document.querySelector( '[data-lzp-notify]:not([data-lzp-ready])' );
		if ( ! toggle ) {
			return;
		}

		toggle.dataset.lzpReady = '';
		const box = toggle.closest( '[data-lzp-notify-box]' );
		if ( ! notify || ! ( 'Notification' in window ) ) {
			return;
		}

		box.hidden = false;
		toggle.checked = notifyOn();
		toggle.addEventListener( 'change', () => {
			if ( ! toggle.checked ) {
				storage( NOTIFY_KEY, null );
				setBadge( 0 );
				return;
			}

			Notification.requestPermission().then( ( permission ) => {
				if ( 'granted' !== permission ) {
					toggle.checked = false;
					flash( i18n.notifyBlocked );
					return;
				}

				storage( NOTIFY_KEY, '1' );
				worker();
				flash( i18n.notifyOn );
				pulse();
			} );
		} );
	}

	/** Sets up the parts of the current page (each element once). */
	function initPage() {
		initToasts();
		setDirty( false );

		const fresh = ( selector ) => Array.from( document.querySelectorAll( selector + ':not([data-lzp-ready])' ) ).map( ( node ) => {
			node.dataset.lzpReady = '';
			return node;
		} );

		fresh( '[data-lzp-autosubmit]' ).forEach( ( input ) => input.form.classList.add( 'is-auto' ) );
		fresh( '[data-lzp-media]' ).forEach( initMediaField );
		fresh( '[data-lzp-editor]' ).forEach( initEditor );
		initCopy();
		initNotifySwitch();
		initSeo();
	}

	/* ---------------------------------------------------------------------
	 * SEO card: live search-result preview
	 * ------------------------------------------------------------------- */

	function initSeo() {
		document.querySelectorAll( '[data-lzp-seo]:not([data-lzp-ready])' ).forEach( ( card ) => {
			card.dataset.lzpReady = '';
			const pairs = [
				[ card.querySelector( '[name="seo[title]"]' ), card.querySelector( '[data-lzp-serp-title]' ) ],
				[ card.querySelector( '[name="seo[desc]"]' ), card.querySelector( '[data-lzp-serp-desc]' ) ],
			];
			const title = document.querySelector( 'input[name="title"]' );

			const update = () => pairs.forEach( ( [ field, preview ] ) => {
				if ( ! field || ! preview ) {
					return;
				}

				const fallback = preview === pairs[ 0 ][ 1 ] && title ? title.value : preview.dataset.fallback;
				preview.textContent = field.value.trim() || fallback;
			} );

			pairs.forEach( ( [ field ] ) => field && field.addEventListener( 'input', update ) );
			if ( title ) {
				title.addEventListener( 'input', update );
			}
		} );
	}

	bindForms();
	initPage();
	startPulse();
	document.addEventListener( 'lzp:page', initPage );
}() );
