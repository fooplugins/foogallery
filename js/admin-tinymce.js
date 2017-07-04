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
					'      <div class="foogallery-pile-inner-thumb"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAACWCAIAAACzY+a1AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAABKBJREFUeNrs3I1P2loYx3EoRaXKiyVXF7Ilg+3//4e2FBDNhPbSYgrIq3vsuZcQJwqywDnw/cUYJG5pzqfPOc9pi+l2t5ciJsdiCCAkEBIIISQQEggJhBASCAmEBEIICYQEQgIhhARCAiGBEEICIYGQQAghgZBASCCEkEBIICQQQkggJBASCCEkEBIICYQQEggJhARCCAmEBEICIYQEQgIhWS+2tkeWTqLP8TwlgXDtycGyZrNZP45Tmig+PeUc5+TkRI4KwveTyWSiMLy7uxsMBpoUotRfLpe7/vTJdV05JK3K0daw/sIwrHvefD4XS30ObDQayVFNJpNKpaJVLepYhff39/PZzMpkRFGrc0tOqftfv4rFouM4+hybrVsJxnE8HAxktGSyKpVKMnftfbDkqP6VBIFMoVJ/UoishW9lnkRGTQivrq8LhYIOhPI98P30/4FwgyZCib46prvs8rWa0k0ifLUgpAj6/b7MZtIinp6eKma29mZEuglpVjvt9nA4FMKzs7N8Pi+TrVges6JtkF8QBM1m87lZTZrD8Xjs+363263VasVS6WgVzbhGKma9Xu+m2ZQFUPBUQyHfM8nG4/b2VhpF3boMCF9G5s9XnURX5tWw292UUC2rEO4ogjedTleNuLwv0+lG/6H8kyiKDqN2DSBUG+o3CD+yrPr+zx8/GvW6NLSmKxpAKKN8kmTVLlD9wvp+Mie3Wi2ZSKUQ655nuqIxa6Fzfr6KUFRka7GR36IbUpfUjVY0g1CGuFKpFIvFF7cI5P3pZOK67jrXnV/4Le81jVY0hlDGt1qrlS4vZVFUV91UMyL19/nLl3evtL3qdxiKxmztZXxt265Wq9dXV2EUDQcDKUrZ0av6e5vwDb9lxZTnyVmi2x3dwyFMJdeapQfJFwrytbzf+HD9HYaiYU+wLe5dLPJX/IyeUY1/CNFKsr2fuYpmE8qId5P8+ZTNB/wMVbSN9ovCsFGvP7+2LGlWF+vih/1erItfq9VVJU4V/h0/z/PUj/JCflS1uKXfcn3Hcax/IVpG+6m5TjWQSjGbzba39lusskyku/BTbyrFRqPx8PAQBMH2flyd2anfQlHWwk6nc1R+KeOenVnlt1A8wnv31sH4HW3MqMLFBy3wM7IKxWw8Hreazfl8jp+pE+nzvYgja1IOf2tPIKSd2cdcuuO18N07yRCuGxnHbDb77fv33Q9oLpfTvwc2owplEPP5/F7OHgi33Q4uT2v7PYcg3OzEV6+jKLq4uMgkH9re75k0Ho0gXNdPzBzHeXx8lIHrtNv9OLYymdR+24p0etDvLz5azAe13yG0bdstl1s3N/JCBiuOY32m9Ol0Wi6Xz1c/V86+8L81758kMkz6/HkXOSrxu3Tdr9Wqbo8optvdnob9ZzqZuzq+L4vQ3ieup+Q+ieu6hUJB/SkOvYZLQ8I/21ENWy3WwrXmrhQxcS0kEEJIICQQEgghJBASCAmEEBIICYQEQggJhARCAiGEBEICIYEQQgIhgZBACCGBkEBIIISQQEggJBBCSCAkEBIIISQQEggJhBASCAmEBEIICYQEQgIhhETX/BZgAHryyaJUyijaAAAAAElFTkSuQmCC" /></div>' +
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
						var win = window.open(node.href, '_blank');
						win.focus();
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