(function() {
	tinymce.PluginManager.add('foogallery', function( editor, url ) {

		function getParentFooGallery( node ) {
			while ( node && node.nodeName !== 'BODY' ) {
				if ( isFooGallery( node ) ) {
					return node;
				}

				node = node.parentNode;
			}
		}

		function isFooGallery( node ) {
			return node && /foogallery-tinymce-view/.test( node.className );
		}

		function unselectFooGallery( dom ) {
			dom.removeClass(dom.select('div.foogallery-tinymce-selected'), 'foogallery-tinymce-selected');
		}

		editor.on( 'BeforeSetContent', function( event ) {
			if ( ! event.content ) {
				return;
			}

			var shortcode_tag = window.FOOGALLERY_SHORTCODE || 'foogallery',
				regexp = new RegExp('\\[' + shortcode_tag + ' ([^\\]]*)\\]', 'g');

			event.content = event.content.replace( regexp, function( match ) {

				var data = window.encodeURIComponent( match ),
					idRegex = / id=\"(.*?)\"/ig,
					idMatch = idRegex.exec(match),
					id = idMatch ? idMatch[1] : 0;

				return '<div class="foogallery-tinymce-view mceNonEditable" data-foogallery="' + data + '" contenteditable="false" data-mce-resize="false" data-mce-placeholder="1" data-foogallery-id="' + id + '">' +
					'  <div class="foogallery-tinymce-toolbar">' +
					'    <a class="dashicons dashicons-edit foogallery-tinymce-toolbar-edit" href="post.php?post=' + id + '&action=edit" target="_blank">&nbsp;</a>' +
					'    <div class="dashicons dashicons-no-alt foogallery-tinymce-toolbar-delete">&nbsp;</div>' +
					'  </div>' +
					'  <div class="foogallery-pile">' +
					'    <div class="foogallery-pile-inner">' +
					'      <div class="foogallery-pile-inner-thumb"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAACWCAMAAAAL34HQAAAAn1BMVEXu7u7u7u7u7u7XKwzv+PrXLQ7u7u3v+vvv9vjge2nYNxru8fHv+/3jk4TXLxHXMRPfblnlopbourHZPCDv8vPv9PXmqJ3XKAju6+vYNBbpxb7cWEDYOBvcVTzt4N7kmYvv/P7u6ejgeGXr1tLpwbrdXUbbUTjaRyzWIwPw/v/hgG7dYErs3Nnln5LjjX3eaFPt5OPZQibr2NTq0MvnsKa2nujkAAAAAnRSTlPx5atUCzMAAAKjSURBVHja7dtbc6IwGMZxm0CCgIEuICKoyCJiu7XH7//ZNrGd7V7Uzj4eJszs+7vKjDf/eRkih2F0MxogHcUGaERZlPWOshCUhaAsBGUhKAtBWQjKQlAWgrIQlIWgLARlISgLQVkIykJQFoKyEJSFoCwEZSEoC0FZCMqySejXwmyAbiiLsv4iPE+wM1wpK2zbkJ3hKlminlfVvD55XtfJEuE8USqZhyd2XSmrNlWmq2YnuUqWMFU8HnPTJRjsOlnmCLr5I3Ma7p5yHC+fFRg7PStXV3lZ4+p57QKDAS6bZXYqLW0TFT2us9ksFU2kkjYVGrCLXTjLe40XZVluElUVi01plqXvq8QsF/Grx3DnZ4nsZZsrV4v8wtcLQxWFHx0W+fYlA+Z1uWnJfe4X0RGFn+8lw52f5TRcNe2PL7WN4o3DAJfLGnO+2jlf2q04H9vLmkiRSsORqWCf5MRyljcf3xlN6+muNDMCYTvLqW97rrn9qtZZy1/G2vq0nHDq+lrFH0IRzDbJ8/Nz9SYd+1m8MPJDVsejKMqnofUsOY0SQz1InbVQenKu/SwZPP189xSwAWWJIPMOUjakLL1IPwwrK7gr7434KR1QVhCUvavxZJkNKmujfK3YDjery5VSA9i3TNa9qrRD1uO267rtmyMHkLXoc62vlilbH8ys/yeaM3E6WRkP60A3GgO4sBFCOHVoSCb+sJy12pmeD+Enu1enKp7efmkaK1tZcpJXET8iqvKJnTufdNn17lF9p09M3PlZIlvu46P2S+DuFc/6tkt6R0mwCs/6Juy7n0D/1wNwyvpHlIWgLKvok1zK+kBZCMpCUBaCshCUhaAsBGUhKAtBWQjKQlAWgrIQlIWgLARlISgLQVkIykJQFoKyEJSFoCyrRr8BcFXgyRdssmUAAAAASUVORK5CYII=" /></div>' +
					'    </div>' +
					'  </div>' +
					'  <div class="foogallery-tinymce-title">&nbsp;</div>' +
					'  <div class="foogallery-tinymce-count">' + match + '</div>' +
					'</div>';
			});
		});

		editor.on( 'LoadContent', function( event ) {
			if ( ! event.content ) {
				return;
			}

			var dom = editor.dom;

			// Replace the foogallery node with the shortcode
			tinymce.each( dom.select( 'div[data-foogallery]', event.node ), function( node ) {

				if ( !dom.hasClass(node, 'foogallery-tinymce-databound') ) {
					dom.addClass(node, 'foogallery-tinymce-databound');

					//we need to post to our ajax handler and get some gallery info
					var id = dom.getAttrib( node, 'data-foogallery-id'),
						nonce = jQuery('#foogallery-timnymce-action-nonce').val(),
						data = 'action=foogallery_tinymce_load_info&foogallery_id=' + id + '&nonce=' + nonce;

					jQuery.ajax({
						type: "POST",
						url: ajaxurl,
						data: data,
						dataType: 'JSON',
						success: function(data) {
							var titleDiv = dom.select( '.foogallery-tinymce-title', node),
								countDiv = dom.select( '.foogallery-tinymce-count', node),
								galleryImg = dom.select( '.foogallery-pile-inner-thumb', node );

							if (titleDiv && titleDiv.length) {
								titleDiv[0].textContent = data.name;
							}
							if (countDiv && countDiv.length) {
								countDiv[0].textContent = data.count;
							}
							if (galleryImg && galleryImg.length) {
								jQuery(galleryImg[0]).replaceWith('<img src="' + data.src + '" />');
							}
						}
					});
				}
			});
		});

		editor.on( 'PreProcess', function( event ) {
			var dom = editor.dom;

			// Replace the foogallery node with the shortcode
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
					return ''; // If error, remove the foogallery view
				});
			}
		});

		editor.on( 'mouseup', function( event ) {
			var dom = editor.dom,
				node = event.target,
				fg = getParentFooGallery( node );

			// Don't trigger on right-click
			if ( event.button !== 2 ) {



				if (fg) {
					//we have clicked somewhere in the foogallery element

					if (node.nodeName === 'A' && dom.hasClass(node, 'foogallery-tinymce-toolbar-edit')) {
						//alert('EDIT : ' + dom.getAttrib( fg, 'data-foogallery-id' ))
					} else if (node.nodeName === 'DIV' && dom.hasClass(node, 'foogallery-tinymce-toolbar-delete')) {
						//alert('DELETE : ' + dom.getAttrib( fg, 'data-foogallery-id' ))
						dom.remove(fg);
					} else {

						if (!dom.hasClass(fg, 'foogallery-tinymce-selected')) {
							unselectFooGallery(dom);
							dom.addClass(fg, 'foogallery-tinymce-selected');
						}

					}
				} else {
					unselectFooGallery(dom);
				}
			}
		});
	});
})();