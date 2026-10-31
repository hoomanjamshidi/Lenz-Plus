/**
 * Lenz+ studio panel: progressive enhancements. Every form and link works
 * without this file; it adds background uploads with previews, reordering of
 * gallery photos, the simple text editor, confirmations, a warning before
 * leaving unsaved changes and self-hiding messages.
 *
 * Data from PHP: window.lzpPanel { ajaxUrl, uploadNonce, i18n }.
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
			window.history.replaceState( null, '', url.toString() );
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

	let dirty = false;

	function initForms() {
		document.addEventListener( 'click', ( event ) => {
			const button = event.target.closest( '[data-lzp-confirm]' );
			if ( button && ! window.confirm( button.dataset.lzpConfirm ) ) {
				event.preventDefault();
			}
		} );

		document.querySelectorAll( 'form.lzp-edit' ).forEach( ( form ) => {
			form.addEventListener( 'input', () => {
				dirty = true;
			} );
			form.addEventListener( 'change', () => {
				dirty = true;
			} );
		} );

		document.addEventListener( 'submit', ( event ) => {
			const form = event.target;

			// Wait for background uploads, then send the form again.
			if ( Number( form.dataset.lzpPending || 0 ) > 0 ) {
				event.preventDefault();
				form.dataset.lzpResubmit = '1';
				return;
			}

			dirty = false;
			const button = form.querySelector( '[data-lzp-saving]' );
			if ( button ) {
				button.classList.add( 'is-busy' );
				const label = button.querySelector( 'span' ) || button;
				label.textContent = button.dataset.lzpSaving;
			}
		} );

		window.addEventListener( 'beforeunload', ( event ) => {
			if ( dirty ) {
				event.preventDefault();
				event.returnValue = i18n.unsaved || '';
			}
		} );

		document.querySelectorAll( '[data-lzp-autosubmit]' ).forEach( ( input ) => {
			const form = input.form;
			form.classList.add( 'is-auto' );
			input.addEventListener( 'change', () => {
				if ( input.files.length ) {
					form.requestSubmit ? form.requestSubmit() : form.submit();
				}
			} );
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

	function initMediaField( field ) {
		const list = field.querySelector( '.lzp-media__list' );
		const add = field.querySelector( '.lzp-media__add' );
		const file = field.querySelector( '.lzp-media__file' );
		const multiple = field.hasAttribute( 'data-multiple' );

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
				dirty = true;
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
			dirty = true;
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
				dirty = true;

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
			dirty = true;
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
						dirty = true;
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

		document.querySelectorAll( '[data-lzp-copy]' ).forEach( ( button ) => {
			button.hidden = false;
			button.addEventListener( 'click', () => {
				navigator.clipboard.writeText( button.dataset.lzpCopy ).then( () => flash( i18n.copied ) );
			} );
		} );
	}

	initToasts();
	initForms();
	document.querySelectorAll( '[data-lzp-media]' ).forEach( initMediaField );
	document.querySelectorAll( '[data-lzp-editor]' ).forEach( initEditor );
	initCopy();
}() );
