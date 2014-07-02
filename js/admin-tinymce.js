(function() {
	tinymce.PluginManager.add('foogallery', function( editor, url ) {

		editor.on( 'BeforeSetContent', function( event ) {
			if ( ! event.content ) {
				return;
			}

			event.content = event.content.replace( /\[foogallery([^\]]*)\]/g, function( match ) {
				data = window.encodeURIComponent( match );

				return '<div class="foogallery-tinymce wpview-wrap mceNonEditable" data-foogallery="' + data + '" contenteditable="false" data-mce-resize="false" data-mce-placeholder="1">' +
					'<div class="toolbar">' +
					'<div class="dashicons dashicons-edit edit">&nbsp;</div><div class="dashicons dashicons-no-alt remove">&nbsp;</div>' +
					'</div><code>' + match + '</code>' +
//					'<div class="foogallery-pile">' +
//					'<div class="foogallery-gallery-select attachment-preview landscape foogallery-add-gallery">' +
//					'	<a href="#" target="_blank" class="thumbnail" style="display: table;">' +
//					'			<div style="display: table-cell; vertical-align: middle; text-align: center;">' +
//					'			<span></span>' +
//					'			<h3>FooGallery</h3>' +
//					'		</div>' +
//					'	</a>' +
//					'</div>' +
//					'</div>' +
					'</div>';
			});
		});

		editor.on( 'PreProcess', function( event ) {
			var dom = editor.dom;

			// Replace the foogallery node with the wpview string/shortcode?
			tinymce.each( dom.select( 'div[data-foogallery]', event.node ), function( node ) {
				// Empty the wrap node
				if ( 'textContent' in node ) {
					node.textContent = '\u00a0';
				} else {
					node.innerText = '\u00a0';
				}
			});
		});

		editor.on( 'PostProcess', function( event ) {
			if ( event.content ) {
				event.content = event.content.replace( /<div [^>]*?data-foogallery="([^"]*)"[^>]*>[\s\S]*?<\/div>/g, function( match, shortcode ) {
					if ( shortcode ) {
						return '<p>' + window.decodeURIComponent( shortcode ) + '</p>';
					}
					return ''; // If error, remove the view wrapper
				});
			}
		});

		editor.on( 'mouseup', function( event ) {
			var dom = editor.dom,
				node = event.target;

			function unselect() {
				dom.removeClass( dom.select( 'div.foogallery-tinymce-selected' ), 'foogallery-tinymce-selected' );
			}

			if ( node.nodeName === 'DIV' && dom.getAttrib( node, 'data-foogallery' ) ) {
				// Don't trigger on right-click
				if ( event.button !== 2 ) {
					if ( dom.hasClass( node, 'foogallery-tinymce-selected' ) ) {
						//editMedia( node );
						alert( node );
					} else {
						unselect();
						dom.addClass( node, 'foogallery-tinymce-selected' );
					}
				}
			} else {
				unselect();
			}
		});
	});
})();

///* global tinymce */
//tinymce.PluginManager.add('wpgallery', function( editor ) {
//
//	function replaceGalleryShortcodes( content ) {
//		return content.replace( /\[gallery([^\]]*)\]/g, function( match ) {
//			return html( 'wp-gallery', match );
//		});
//	}
//
//	function html( cls, data ) {
//		data = window.encodeURIComponent( data );
//		return '<img src="' + tinymce.Env.transparentSrc + '" class="wp-media mceItem ' + cls + '" ' +
//		'data-wp-media="' + data + '" data-mce-resize="false" data-mce-placeholder="1" />';
//	}
//
//	function restoreMediaShortcodes( content ) {
//		function getAttr( str, name ) {
//			name = new RegExp( name + '=\"([^\"]+)\"' ).exec( str );
//			return name ? window.decodeURIComponent( name[1] ) : '';
//		}
//
//		return content.replace( /(?:<p(?: [^>]+)?>)*(<img [^>]+>)(?:<\/p>)*/g, function( match, image ) {
//			var data = getAttr( image, 'data-wp-media' );
//
//			if ( data ) {
//				return '<p>' + data + '</p>';
//			}
//
//			return match;
//		});
//	}
//
//	function editMedia( node ) {
//		var gallery, frame, data;
//
//		if ( node.nodeName !== 'IMG' ) {
//			return;
//		}
//
//		// Check if the `wp.media` API exists.
//		if ( typeof wp === 'undefined' || ! wp.media ) {
//			return;
//		}
//
//		data = window.decodeURIComponent( editor.dom.getAttrib( node, 'data-wp-media' ) );
//
//		// Make sure we've selected a gallery node.
//		if ( editor.dom.hasClass( node, 'wp-gallery' ) && wp.media.gallery ) {
//			gallery = wp.media.gallery;
//			frame = gallery.edit( data );
//
//			frame.state('gallery-edit').on( 'update', function( selection ) {
//				var shortcode = gallery.shortcode( selection ).string();
//				editor.dom.setAttrib( node, 'data-wp-media', window.encodeURIComponent( shortcode ) );
//				frame.detach();
//			});
//		}
//	}
//
//	// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('...');
//	editor.addCommand( 'WP_Gallery', function() {
//		editMedia( editor.selection.getNode() );
//	});
//	/*
//	 editor.on( 'init', function( e ) {
//	 //	_createButtons()
//
//	 // iOS6 doesn't show the buttons properly on click, show them on 'touchstart'
//	 if ( 'ontouchstart' in window ) {
//	 editor.dom.events.bind( editor.getBody(), 'touchstart', function( e ) {
//	 var target = e.target;
//
//	 if ( target.nodeName == 'IMG' && editor.dom.hasClass( target, 'wp-gallery' ) ) {
//	 editor.selection.select( target );
//	 editor.dom.events.cancel( e );
//	 editor.plugins.wordpress._hideButtons();
//	 editor.plugins.wordpress._showButtons( target, 'wp_gallerybtns' );
//	 }
//	 });
//	 }
//	 });
//	 */
//	editor.on( 'mouseup', function( event ) {
//		var dom = editor.dom,
//			node = event.target;
//
//		function unselect() {
//			dom.removeClass( dom.select( 'img.wp-media-selected' ), 'wp-media-selected' );
//		}
//
//		if ( node.nodeName === 'IMG' && dom.getAttrib( node, 'data-wp-media' ) ) {
//			// Don't trigger on right-click
//			if ( event.button !== 2 ) {
//				if ( dom.hasClass( node, 'wp-media-selected' ) ) {
//					editMedia( node );
//				} else {
//					unselect();
//					dom.addClass( node, 'wp-media-selected' );
//				}
//			}
//		} else {
//			unselect();
//		}
//	});
//
//	// Display gallery, audio or video instead of img in the element path
//	editor.on( 'ResolveName', function( event ) {
//		var dom = editor.dom,
//			node = event.target;
//
//		if ( node.nodeName === 'IMG' && dom.getAttrib( node, 'data-wp-media' ) ) {
//			if ( dom.hasClass( node, 'wp-gallery' ) ) {
//				event.name = 'gallery';
//			}
//		}
//	});
//
//	editor.on( 'BeforeSetContent', function( event ) {
//		// 'wpview' handles the gallery shortcode when present
//		if ( ! editor.plugins.wpview ) {
//			event.content = replaceGalleryShortcodes( event.content );
//		}
//	});
//
//	editor.on( 'PostProcess', function( event ) {
//		if ( event.get ) {
//			event.content = restoreMediaShortcodes( event.content );
//		}
//	});
//});
