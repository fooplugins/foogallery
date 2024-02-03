FooGallery.autoEnabled = false;

(function (FOOGALLERY, $, undefined) {

    FOOGALLERY.media_uploader = false;
    FOOGALLERY.previous_post_id = 0;
    FOOGALLERY.attachments = [];
    FOOGALLERY.selected_attachment_id = 0;
	FOOGALLERY.selected_gallery_template = '';

	// Used for selecting files from the media modal.
	FOOGALLERY.current_media_selector_modal = false;
	FOOGALLERY.current_media_selector_input = false;

    FOOGALLERY.calculateAttachmentIds = function() {
        var sorted = [];
        $('.foogallery-attachments-list li:not(.add-attachment)').each(function() {
        	if ( $(this).data('attachment-id') ) {
                sorted.push($(this).data('attachment-id'));
            }
        });

        $('#foogallery_attachments').val( sorted.join(',') );

        // Force the datasource to be 'media_library' so that we do not get into a strange state with other datasources that were previously selected.
        $('#foogallery_datasource').val('media_library');

		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
    };

	FOOGALLERY.calculateHiddenAreas = function() {
        FOOGALLERY.showHiddenAreas( FOOGALLERY.attachments.length === 0 );
	};

	FOOGALLERY.showHiddenAreas = function( show ) {
        if ( show ) {
            $('.foogallery-items-add').removeClass('hidden');
            $('.foogallery-attachments-list-container').addClass('hidden');
            $('.foogallery-items-empty').removeClass('hidden');
        } else {
            $('.foogallery-items-add').addClass('hidden');
            $('.foogallery-attachments-list-container').removeClass('hidden');
            $('.foogallery-items-empty').addClass('hidden');
        }
	};

    FOOGALLERY.initAttachments = function() {
        var attachments = $('#foogallery_attachments').val();
		if (attachments) {
			FOOGALLERY.attachments = $.map(attachments.split(','), function (value) {
				return parseInt(value, 10);
			});
		}
    };

	FOOGALLERY.galleryTemplateChanged = function(reloadPreview) {
		var selectedTemplate = FOOGALLERY.getSelectedTemplate(),
			$settingsToShow = $('.foogallery-settings-container-' + selectedTemplate),
			$settingsToHide = $('.foogallery-settings-container').not($settingsToShow),
			$currentTab = $settingsToHide.find('.foogallery-vertical-tab.foogallery-tab-active'),
			currentTab = $currentTab.data('name'),
			currentChildTab = $currentTab.find('.foogallery-vertical-child-tab.foogallery-tab-active').data('name'),
			previousSelectedTemplate = FOOGALLERY.selected_gallery_template;

		FOOGALLERY.selected_gallery_template = selectedTemplate;

		//hide all template fields
		$settingsToHide.hide()
			.removeClass('foogallery-settings-container-active')
			.find(':input').attr('disabled', true);

		//show all fields for the selected template only
		$settingsToShow.show()
			.addClass('foogallery-settings-container-active')
			.find(':input').removeAttr('disabled');

		if (currentTab) {
			currentTab = currentTab.replace( previousSelectedTemplate, selectedTemplate );

			//ensure the previously active tab is clicked
			$settingsToShow.find('.foogallery-vertical-tab[data-name="' + currentTab + '"]').click();

			if (currentChildTab) {
				currentChildTab = currentChildTab.replace( previousSelectedTemplate, selectedTemplate );

				//ensure the previously active child tab is clicked
				$settingsToShow.find('.foogallery-vertical-child-tab[data-name="' + currentChildTab + '"]').click();
			}
		}

		// always ensure a tab is clicked
		if ( $settingsToShow.find('.foogallery-vertical-tab.foogallery-tab-active').length === 0 ) {
			$settingsToShow.find('.foogallery-vertical-tab:first').click();
		}

		//include a preview CSS if possible
		FOOGALLERY.includePreviewCss();

		//trigger a change so custom template js can do something
		FOOGALLERY.triggerTemplateChangedEvent();

		if (reloadPreview) {
			FOOGALLERY.reloadGalleryPreview();
		}
	};

	FOOGALLERY.handleSettingFieldChange = function(reloadPreview, setContainerHeight) {

		//make sure the fields that should be hidden or shown are doing what they need to do
        FOOGALLERY.handleSettingsShowRules();

		//update the gallery preview
		FOOGALLERY.updateGalleryPreview(reloadPreview, setContainerHeight);
	};

	FOOGALLERY.updateGalleryPreview = function( initGallery, setContainerHeight ) {
		var $preview = $('.foogallery_preview_container .foogallery'),
			$preview_container = $('.foogallery_preview_container');

		if ( setContainerHeight ) {
			$preview_container.css('height', $preview_container.height());
		}

		//this allows any extensions to hook into the template change event
		$('body').trigger('foogallery-gallery-preview-updated' + FOOGALLERY.getSelectedTemplate() );

		//this handles all built-in templates that use the FooGallery core client side JS
		if ( $preview.data('fg-common-fields') ) {
			if ( initGallery ) {
				$preview.foogallery( {}, function() {
					$preview_container.css( 'height', '' );
					if ( !$preview_container.find('.foogallery').data('foogallery-lightbox') ) {
						$preview_container.find(".fg-thumb").off("click.foogallery").on("click", function (e) {
							e.preventDefault();
						});
					}
				} );
			} else {
				$preview.foogallery( 'layout' );
				$preview_container.css( 'height', '' );
			}
		} else {
			//reset the height to what it should be
			$preview_container.css('height', '');
		}
	};

	FOOGALLERY.reloadGalleryPreview = function() {
		//make sure the fields that should be hidden or shown are doing what they need to do
		FOOGALLERY.handleSettingsShowRules();

		var $preview_container = $('.foogallery_preview_container'),
			$preview = $preview_container.find('.foogallery');

		//set the preview height so there is no jump
		$preview_container.css('height', $preview_container.height());

		//build up all the data to generate a preview
        var $shortcodeFields = $('.foogallery-settings-container-active .foogallery-metabox-settings .foogallery_template_field[data-foogallery-preview*="shortcode"]'),
			data = [],
			foogallery_id = $('#post_ID').val();

        if ($shortcodeFields.length) {
			data = $shortcodeFields.find(' :input').serializeArray();
        }

        //clear any items just in case
		window['foogallery-gallery-' + foogallery_id + '_items'] = null;

        //add additional data for the preview
		data.push({name: 'foogallery_id', value: foogallery_id});
		data.push({name: 'foogallery_template', value: FOOGALLERY.getSelectedTemplate()});

		//include other preview fields
        var previewData = $('[data-foogallery-preview="include"]').serializeArray();
        data = data.concat(previewData);

		//data.push({name: 'foogallery_attachments', value: $('#foogallery_attachments').val()});
        data.push({name: 'foogallery_datasource', value: $('#foogallery_datasource').val()});
        data.push({name: 'foogallery_datasource_value', value: $('#_foogallery_datasource_value').val()});

		//add data needed for the ajax call
		data.push({name: 'action', value: 'foogallery_preview'});
		data.push({name: 'foogallery_preview_nonce', value: $('#foogallery_preview').val()});
		data.push({name: '_wp_http_referer', value: encodeURIComponent($('input[name="_wp_http_referer"]').val())});

        $('#foogallery_preview_spinner').addClass('is-active');
		$preview_container.addClass('loading');

		if ($preview.length > 0){
			var fg = $preview.data("__FooGallery__");
			if (fg instanceof FooGallery.Template){
				fg.destroy(false);
			}
		}

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
			cache: false,
            success: function(data) {
				$('.foogallery_preview_container .foogallery').foogallery("destroy");

                //updated the preview
				$preview_container.html(data);
                $('#foogallery_preview_spinner').removeClass('is-active');
				$preview_container.removeClass('loading foogallery-preview-force-refresh');

				FOOGALLERY.updateGalleryPreview(true, true);
            }
        });
	};

	FOOGALLERY.handleSettingsShowRules = function() {
		var selectedTemplate = FOOGALLERY.getSelectedTemplate();

		//hide any fields that need to be hidden initially
		$('.foogallery-settings-container-active .foogallery_template_field[data-foogallery-hidden]').hide()
			.addClass('foogallery_template_field_template_hidden')
			.find(':input').attr('disabled', true);

		$('.foogallery-settings-container-active .foogallery_template_field[data-foogallery-show-when-field]').each(function(index, item) {
			var $item = $(item),
				itemLabel = $item.find('th label').text(),
				fieldId = $item.data('foogallery-show-when-field'),
				fieldValue = $item.data('foogallery-show-when-field-value'),
                fieldOperator = $item.data('foogallery-show-when-field-operator'),
				$fieldRow = $('.foogallery_template_field_template_id-' + selectedTemplate + '-' + fieldId),
				$fieldSelector = $fieldRow.data('foogallery-value-selector'),
				fieldValueAttribute = $fieldRow.data('foogallery-value-attribute'),
				$field = $fieldRow.find($fieldSelector),
				showField = false;

			if ( $fieldRow.length === 0 ) {
				// No matching field was found, which means we can un-hide
				showField = true;
			} else {

				if ( $fieldRow.hasClass( 'foogallery_template_field_template_hidden' ) ) {
					// The field we are checking is hidden, which means we should not rely on it
				} else {

					$field.each(function () {
						var actualFieldValue = fieldValueAttribute ? $(this).attr(fieldValueAttribute) : $(this).val();

						if (fieldOperator === '!==') {
							if (actualFieldValue !== fieldValue) {
								showField = true;
							}
						} else if (fieldOperator === 'regex') {
							var re = new RegExp(fieldValue);
							if (re.test(actualFieldValue)) {
								showField = true;
							}
						} else if (fieldOperator === 'indexOf') {
							if (actualFieldValue.indexOf(fieldValue) !== -1) {
								showField = true;
							}
						} else if (actualFieldValue === fieldValue) {
							showField = true;
						}
					});
				}

			}

			if (showField) {
				$item.show()
					.removeClass('foogallery_template_field_template_hidden')
					.find(':input').removeAttr('disabled')
					.end().find('.colorpicker').spectrum("enable");
			}
		});
	};

	FOOGALLERY.initSettings = function() {
		//move the template selector into the metabox heading
		var $metabox_heading = $('#foogallery_settings .hndle span');
		//This check is done to accommodate a markup change in WP 5.5
		if ( $metabox_heading.length === 0 ) {
			$metabox_heading = $('#foogallery_settings .hndle');
			$metabox_heading.addClass( 'foogallery-custom-metabox-header' );
		}

        $('.foogallery-template-selector').appendTo( $metabox_heading ).removeClass('hidden');

		//remove the loading spinner
		$('.foogallery-gallery-items-metabox-title').remove();

		var $items_metabox_heading = $('#foogallery_items .hndle span');
		//This check is done to accommodate a markup change in WP 5.5
		if ( $items_metabox_heading.length === 0 ) {
			$items_metabox_heading = $('#foogallery_items .hndle');
			$items_metabox_heading.addClass( 'foogallery-custom-metabox-header' );
		}

		$('.foogallery-items-view-switch-container').appendTo( $items_metabox_heading ).removeClass('hidden');

		$('.foogallery-items-view-switch-container a').on('click', function(e) {
			e.stopPropagation();

			var $currentButton = $('.foogallery-items-view-switch-container a.current'),
				currentSelector = $currentButton.data('container'),
				$nextButton = $(this),
				nextSelector = $nextButton.data('container'),
				value = $nextButton.data('value');

			//if the preview button is already selected, and we are clicking it again, then force a preview refresh
			if ( currentSelector === nextSelector && value === 'preview' ) {
				$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
			}

			//toggle the views
			$(currentSelector).hide();
			$(nextSelector).show();

			//toggle the switch button
			$currentButton.removeClass('current');
			$nextButton.addClass('current');

			//set the input so that it is saved
			$('#foogallery_items_view_input').val(value);

			if ( $('.foogallery_preview_container').is(':visible') ) {
				FOOGALLERY.updateGalleryPreview(false, false);

				//check if there is no preview
				if ( !$.trim( $('.foogallery_preview_container').html() ) ||
					$( '.foogallery_preview_container.foogallery-preview-force-refresh').length > 0 ) {
					FOOGALLERY.reloadGalleryPreview();
				}
			}
		});

		$(function() {

			// Prevent inputs in settings meta box headings opening/closing contents.
			$( '#foogallery_settings' ).find( '.hndle' ).off( 'click.postboxes' );

			$( '#foogallery_settings' ).on( 'click', '.hndle', function( event ) {

				// If the user clicks on some form input inside the h3 the box should not be toggled.
				if ( $( event.target ).filter( 'input, option, label, select' ).length ) {
					return;
				}

				$( '#foogallery_settings' ).toggleClass( 'closed' );
			});

			// Prevent inputs in items meta box headings opening/closing contents.
			$( '#foogallery_items' ).find( '.hndle' ).off( 'click.postboxes' );

			$( '#foogallery_items' ).on( 'click', '.hndle', function( event ) {

				// If the user clicks on some form input inside the h3 the box should not be toggled.
				if ( $( event.target ).filter( 'input, option, label, select' ).length ) {
					return;
				}

				$( '#foogallery_items' ).toggleClass( 'closed' );
			});
		});


		$('#FooGallerySettings_GalleryTemplate').change(function() {
			FOOGALLERY.galleryTemplateChanged(true);
		});

		//hook into settings fields changes
		$('.foogallery-metabox-settings .foogallery_template_field[data-foogallery-change-selector]').each(function(index, item) {
			var $fieldContainer = $(item),
				selector = $fieldContainer.data('foogallery-change-selector');

            $fieldContainer.find(selector).change(function() {
                if ( $fieldContainer.data('foogallery-preview') && $fieldContainer.data('foogallery-preview').indexOf('shortcode') !== -1 ) {
                    FOOGALLERY.reloadGalleryPreview();
                } else {
					FOOGALLERY.handleSettingFieldChange( $fieldContainer.data('foogallery-preview') && $fieldContainer.data('foogallery-preview').indexOf('class') !== -1, true );
				}
			});
        });

		//trigger this onload too!
		FOOGALLERY.galleryTemplateChanged(false);

		//force hidden field state to be correct on load
		FOOGALLERY.handleSettingFieldChange(true, false);
	};

	FOOGALLERY.getSelectedTemplate = function() {
		return $('#FooGallerySettings_GalleryTemplate').val();
	};

	FOOGALLERY.includePreviewCss = function() {
		var selectedPreviewCss = $('#FooGallerySettings_GalleryTemplate').find(":selected").data('preview-css');

		//remove any previously added preview css
		$('link[data-foogallery-preview-css]').remove();

		if ( selectedPreviewCss ) {
			var splitPreviewCss = selectedPreviewCss.split(',');
			for (var i = 0, l = splitPreviewCss.length; i < l; i++) {
				$('head').append('<link data-foogallery-preview-css rel="stylesheet" href="' + splitPreviewCss[i] + '" type="text/css" />');
			}
		}
	};

	FOOGALLERY.triggerTemplateChangedEvent = function() {
		$('body').trigger('foogallery-gallery-template-changed-' + FOOGALLERY.getSelectedTemplate() );
	};

    FOOGALLERY.addAttachmentToGalleryList = function(attachment) {

        if ($.inArray(attachment.id, FOOGALLERY.attachments) !== -1) return;

        var $template = $($('#foogallery-attachment-template').val());

        $template.attr('data-attachment-id', attachment.id);

        $template.find('img').attr('src', attachment.src);

        if (attachment.subtype) {
			$template.find('.attachment-preview.type-image').addClass('subtype-' + attachment.subtype);
		}

        if ( $('.foogallery-attachments-list').hasClass('foogallery-add-media-button-start') ) {
			$('.foogallery-attachments-list .datasource-medialibrary').after($template);
		} else {
			$('.foogallery-attachments-list .datasource-medialibrary').before($template);
		}

        FOOGALLERY.attachments.push( attachment.id );

        FOOGALLERY.calculateAttachmentIds();

        FOOGALLERY.calculateHiddenAreas();

		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
    };

    FOOGALLERY.removeAttachmentFromGalleryList = function(id) {
        var index = $.inArray(id, FOOGALLERY.attachments);
        if (index !== -1) {
            FOOGALLERY.attachments.splice(index, 1);
        }
		$('.foogallery-attachments-list [data-attachment-id="' + id + '"]').remove();

        FOOGALLERY.calculateAttachmentIds();

		FOOGALLERY.calculateHiddenAreas();
    };

	FOOGALLERY.showAttachmentInfoModal = function(id) {
		FOOGALLERY.openMediaModal( id );
	};

	FOOGALLERY.openMediaModal = function(selected_attachment_id) {
		if (!selected_attachment_id) { selected_attachment_id = 0; }

		var modal_style = $('#foogallery-image-edit-modal').data('modal_style');

		if ( selected_attachment_id > 0 && modal_style === 'on') {
			FOOGALLERY.openAttachmentModal( selected_attachment_id );
		} else {
			FOOGALLERY.selected_attachment_id = selected_attachment_id;

			if (FOOGALLERY.media_uploader !== false){
				FOOGALLERY.media_uploader.open();
				return;
			}

			var createModal = $.isFunction(wp.foogallery) ? wp.foogallery : wp.media;

			// Create our FooGallery media frame.
			FOOGALLERY.media_uploader = createModal({
				frame: "select",
				multiple: 'add',
				title: FOOGALLERY.mediaModalTitle,
				button: {
					text: FOOGALLERY.mediaModalButtonText
				},
				library: {
					type: "image"
				}
			}).on("select", function(){
				var attachments = FOOGALLERY.media_uploader.state().get('selection').toJSON();

				$.each(attachments, function(i, item) {
					if ( item && item.id ) {
						var attachment = {
							id: item.id,
							src: null,
							subtype: null
						};
						if ( item.sizes && item.sizes.thumbnail) {
							attachment.src = item.sizes.thumbnail.url;
						} else {
							//thumbnail could not be found for whatever reason. Default to the full image URL
							attachment.src = item.url;
						}
						if ( item.subtype ) {
							attachment.subtype = item.subtype;
						}

						FOOGALLERY.addAttachmentToGalleryList(attachment);
					} else {
						//there was a problem adding the item! Move on to the next
						alert( 'There was a problem adding the item to the gallery!' );
					}
				});
			})
			.on( 'open', function() {
				var selection = FOOGALLERY.media_uploader.state().get('selection');
				if (selection && !$.isFunction(wp.foogallery)) {
					//clear any previous selections
					selection.reset();
				}

				if (FOOGALLERY.selected_attachment_id > 0) {
					var attachment = wp.media.attachment(FOOGALLERY.selected_attachment_id);
					attachment.fetch();
					selection.add( attachment ? [ attachment ] : [] );
				} else {
					//would be nice to have all previously added media selected
				}
			});

			// Finally, open the modal
			FOOGALLERY.media_uploader.open();
		}
	};

	FOOGALLERY.initUsageMetabox = function() {
		$('#foogallery_create_page').on('click', function(e) {
			e.preventDefault();

			$('#foogallery_create_page_spinner').addClass('is-active');
			var data = 'action=foogallery_create_gallery_page' +
				'&foogallery_id=' + $('#post_ID').val() +
				'&foogallery_create_gallery_page_nonce=' + $('#foogallery_create_gallery_page_nonce').val() +
				'&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function(data) {
					//refresh page
					location.reload();
				}
			});
		});
	};

	FOOGALLERY.initThumbCacheMetabox = function() {
		$('#foogallery_clear_thumb_cache').on('click', function(e) {
			e.preventDefault();

			$('#foogallery_clear_thumb_cache_spinner').addClass('is-active');
			var data = 'action=foogallery_clear_gallery_thumb_cache' +
				'&foogallery_id=' + $('#post_ID').val() +
				'&foogallery_clear_gallery_thumb_cache_nonce=' + $('#foogallery_clear_gallery_thumb_cache_nonce').val() +
				'&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function(data) {
					alert(data);
					$('#foogallery_clear_thumb_cache_spinner').removeClass('is-active');
				}
			});
		});
	};

    FOOGALLERY.adminReady = function () {
        $('.upload_image_button').on('click', function(e) {
            e.preventDefault();
			FOOGALLERY.mediaModalTitle = $(this).data( 'uploader-title' );
			FOOGALLERY.mediaModalButtonText = $(this).data( 'uploader-button-text' );
			FOOGALLERY.openMediaModal(0);
        });

		$('.remove_all_media').on('click', function(e) {
			$('.foogallery-attachments-list a.remove').click();
		});

		$(document).on('foogallery-datasource-changed', function(e, activeDatasource) {
			FOOGALLERY.showHiddenAreas( activeDatasource === 'media_library' );
		});

        FOOGALLERY.initAttachments();

		FOOGALLERY.initSettings();

		FOOGALLERY.initUsageMetabox();

		FOOGALLERY.initThumbCacheMetabox();

		FOOGALLERY.initAttachmentModal();

		FOOGALLERY.initMediaSelector();

        $('.foogallery-attachments-list')
            .on('click' ,'a.remove', function(e) {
				e.preventDefault();
                var $selected = $(this).parents('li:first'),
					attachment_id = $selected.data('attachment-id');
                FOOGALLERY.removeAttachmentFromGalleryList(attachment_id);
            })
			.on('click' ,'a.info', function() {
				var $selected = $(this).parents('li:first'),
					attachment_id = $selected.data('attachment-id');
				FOOGALLERY.showAttachmentInfoModal(attachment_id);
			})
            .sortable({
                items: 'li:not(.add-attachment)',
                distance: 10,
                placeholder: 'attachment placeholder',
                stop : function() {
                    FOOGALLERY.calculateAttachmentIds();
                }
            });

		//init any colorpickers
		$('.colorpicker').spectrum({
			preferredFormat: "rgb",
			showInput: true,
			clickoutFiresChange: true
		});

		if (typeof IntersectionObserver === "undefined") {
			$(".foogallery-attachments-list .attachment .thumbnail img").each(function(i, img){
				var $img = $(img);
				$img.attr("src", $img.data("src"));
			});
		} else {
			//lazy loading of images on the gallery edit page
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						var $target = $(entry.target);
						$target.attr("src", $target.data("src"));
						io.unobserve(entry.target);
					}
				});
			}, {
				root: $(".foogallery-attachments-list").get(0)
			});

			$(".foogallery-attachments-list .attachment .thumbnail img").each(function(i, img){
				io.observe(img);
			});
		}

	    $('.foogallery-admin-promo-dismiss').on('click', function(e) {
		    e.preventDefault();
		    alert( 'If you want to turn off these promotional messages forever, goto FooGallery Settings -> Advanced, and set the "Disable PRO Promotions" setting. Thank you for using FooGallery :)')
	    } );

		$('.foogallery-attachment-modal-toggle').on('click', function(e) {
			e.preventDefault();
			$(this).attr('disabled', 'disabled');

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					action: 'foogallery_attachment_modal_toggle',
					'nonce' : $(this).data('nonce')
				},
				success: function(data) {
					alert(data);
					//refresh page
					location.reload();
				}
			});
		} );
    };

	FOOGALLERY.initMediaSelector = function() {
		$(document).on('click', '.foogallery-media-selector-choose', function(e){
			e.preventDefault();

			var $el = $( this );

			if ( $el.data( 'input' ) ) {
				// The input has been set by data-input="#my-input"
				FOOGALLERY.current_media_selector_input = $( $el.data('input') );
			} else {
				// Try and be smart and find the input.
				FOOGALLERY.current_media_selector_input = $el.parent().find( 'input[type="text"]')
			}

			// If we do not have an input then get out!
			if ( FOOGALLERY.current_media_selector_input.length === 0 ) {
				return;
			}

			// If the media frame already exists, reopen it.
			if ( FOOGALLERY.current_media_selector_modal ) {
				FOOGALLERY.current_media_selector_modal.open();
				return;
			}

			var
				modalTitle = $el.data('modal-title') ? $el.data( 'modal-title' ) : 'Choose Image',
				modalButton = $el.data('modal-button') ? $el.data( 'modal-button' ) : 'Select Image',
				modalMultiple = $el.data('modal-multiple' ) ? $el.data( 'modal-multiple' ) === 'yes' : true;
				states = [
					// Main states.
					new wp.media.controller.Library( {
						library: wp.media.query(),
						multiple: modalMultiple,
						title: modalTitle,
						priority: 20,
						filterable: 'uploaded',
					} ),
				];

			// Create the media frame.
			FOOGALLERY.current_media_selector_modal = wp.media.frames.downloadable_file = wp.media(
				{
					// Set the title of the modal.
					title: modalTitle,
					library: {
						type: '',
					},
					button: {
						text: modalButton,
					},
					multiple: modalMultiple,
					states: states,
				}
			);

			// When an image is selected, run a callback.
			FOOGALLERY.current_media_selector_modal.on( 'select', function () {
				var file_path = '',
					selection = FOOGALLERY.current_media_selector_modal.state().get( 'selection' );

				selection.map( function ( attachment ) {
					attachment = attachment.toJSON();
					if ( attachment.url ) {
						file_path = attachment.url;
					}
				} );

				FOOGALLERY.current_media_selector_input.val( file_path ).trigger( 'change' );
			} );

			// Finally, open the modal.
			FOOGALLERY.current_media_selector_modal.open();
		});

		$(document).on('click', '.foogallery-media-selector-clear', function(e){
			e.preventDefault();

			var $el = $( this );

			if ( $el.data( 'input' ) ) {
				// The input has been set by data-input="#my-input"
				FOOGALLERY.current_media_selector_input = $( $el.data('input') );
			} else {
				// Try and be smart and find the input.
				FOOGALLERY.current_media_selector_input = $el.parent().find( 'input[type="text"]')
			}

			// If we do not have an input then get out!
			if ( FOOGALLERY.current_media_selector_input.length === 0 ) {
				return;
			}

			// Clear the input.
			FOOGALLERY.current_media_selector_input.val( '' ).trigger( 'change' );
		});
	};

	FOOGALLERY.initAttachmentModal = function() {
		//close attachments modal
		jQuery('#foogallery-image-edit-modal .media-modal-close').click(function() {
			var $content = jQuery('#foogallery-image-edit-modal'),
				$wrapper = jQuery('#foogallery-image-edit-modal .media-frame-content .attachment-details'),
				$loader = jQuery('#foogallery-image-edit-modal .media-frame-content .spinner');

			$content.hide();
			$wrapper.addClass('not-loaded');
			$loader.addClass('is-active');
		});

		// Attachment taxonomy button toggles
		$(document).on('click', '#foogallery-panel-taxonomies ul a', function(e){
			var term_id = parseInt( $(this).data('term-id') ),
				$ul = $(this).parents('ul').first();
			if ( !isNaN(term_id) && term_id > 0 ) {
				$(this).toggleClass('button-primary');
				var terms = [];
				$ul.find('a.button-primary').each(function () {
					term_id = $(this).data('term-id');
					terms.push(term_id);
				});
				var terms_str = terms.toString();
				$('#foogallery_attachment_taxonomy_' + $ul.data('taxonomy') + '_selected').val(terms_str);
			} else {
				var action = $(this).data('action');
				if ( action === 'add' ) {
					$ul.find('.foogallery_attachment_taxonomy_add').toggle();
					$ul.find('.taxonomy_add input').focus();
				} else if ( action === 'save' || action === 'cancel' ){
					var term_to_add = $ul.find('input.foogallery_attachment_taxonomy_add').val();
					if ( term_to_add && term_to_add.length > 0 && action === 'save' ) {
						var nonce = $('#foogallery-panel-taxonomies').data('nonce');
						$.ajax({
							type: "POST",
							url: ajaxurl,
							data: {
								'img_id': $('.foogallery-image-edit-main').data('img_id'),
								'nonce': nonce,
								'action': 'foogallery_attachment_modal_taxonomy_add',
								'taxonomy' : $ul.data('taxonomy'),
								'term': term_to_add
							},
							cache: false,
							success: function(res) {
								var html = '<li><a href="javascript:void(0);" class="button button-small recently-added" data-term-id="' + res.data.id + '">' + res.data.name + '</a></li>';
								$ul.find('.taxonomy_add').before(html);
								$ul.find('a.recently-added').removeClass('recently-added').click();
								$ul.find('input.foogallery_attachment_taxonomy_add').val('');
								$ul.find('.foogallery_attachment_taxonomy_add').toggle();
							}
						});						
					} else {
						$ul.find('input.foogallery_attachment_taxonomy_add').val('');
						$ul.find('.foogallery_attachment_taxonomy_add').toggle();
					}
				} else if ( action === 'remove' ) {
					// Deselect all taxonomies
					$ul.find('a.button.button-small').removeClass('button-primary');
					$('#foogallery_attachment_taxonomy_' + $ul.data('taxonomy') + '_selected').val('');
				}
			}
		});

		$(document).on('click', '#attachments-data-save-btn', function(e){
			e.preventDefault();
			FOOGALLERY.saveAttachmentModal();
		});

		$(document).on('click', '.copy-attachment-file-url', function(e) {
			$('#attachments-foogallery-file-url').select();
			document.execCommand('copy');
		});

		$(document).on('click', '#foogallery-image-edit-modal .foogallery-img-modal-tab-wrapper', function(e) {
			var panel = '#' + $(this).data('tab_id');
			$('#foogallery-image-edit-modal .tab-panel').removeClass('active');
			$(panel).addClass('active');
			$('#foogallery-image-edit-modal').data( 'current_tab', $(this).data('tab_id') );
		});

		$(document).on('click', '#foogallery_clear_img_thumb_cache', function(e) {
			e.preventDefault();

			$('#foogallery_clear_img_thumb_cache_spinner').addClass('is-active');
			var data = 'action=foogallery_clear_attachment_thumb_cache' +
				'&attachment_id=' + $('.foogallery-image-edit-main').data('img_id') +
				'&foogallery_clear_attachment_thumb_cache_nonce=' + $('#foogallery_clear_attachment_thumb_cache_nonce').val() +
				'&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function(data) {
					alert(data);
					$('#foogallery_clear_img_thumb_cache_spinner').removeClass('is-active');
				}
			});
		});

		$(document).on('click', '#foogallery-image-edit-modal .edit-media-header button:not(.media-modal-close)', function(e) {
			e.preventDefault();

			$(this).attr( 'disabled', 'disabled' );
			var selected_attachment_id = parseInt( $(this).data('attachment') ),
				autosave = jQuery('#attachment-modal-autosave').is(':checked');
			if ( selected_attachment_id > 0 ) {

				// Check if we must save the data first!
				if ( autosave ) {
					FOOGALLERY.saveAttachmentModal(function () {
						$(this).removeAttr( 'disabled' );
						FOOGALLERY.openAttachmentModal(selected_attachment_id);
					});
				} else {
					$(this).removeAttr( 'disabled' );
					FOOGALLERY.openAttachmentModal(selected_attachment_id);
				}
			}
		});
	};

	FOOGALLERY.saveAttachmentModal = function(callback) {
		var data = $('#foogallery_attachment_modal_save_form').serialize(),
			$spinner = $('.foogallery-image-edit-footer .spinner');
		$spinner.addClass('is-active');
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			cache: false,
			success: function(res) {
				$spinner.removeClass('is-active');
				if ( $.isFunction(callback) ) {
					callback();
				}
			}
		});
	};

	FOOGALLERY.openAttachmentModal = function(img_id) {
		var $content = jQuery('#foogallery-image-edit-modal'),
			$wrapper = jQuery('#foogallery-image-edit-modal .media-frame-content .attachment-details'),
			$loader = jQuery('#foogallery-image-edit-modal .media-frame-content .spinner'),
			nonce = $content.data('nonce'),
			gallery_id = $content.data('gallery_id'),
			current_tab = $content.data('current_tab');

		$content.show();
		$wrapper.addClass('not-loaded').html('<div class="spinner is-active"></div>');
		$wrapper.css({'grid-template-columns': '1fr'});
		$loader.addClass('is-active');

		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			data: {
				'img_id': img_id,
				'gallery_id': gallery_id,
				'current_tab': current_tab,
				'nonce': nonce,
				'action': 'foogallery_attachment_modal_open'
			},
			success: function(json) {
				jQuery('#foogallery-image-edit-modal .edit-media-header button.left')
					.attr('disabled', !json.prev_slide)
					.data('attachment', json.prev_img_id);
				jQuery('#foogallery-image-edit-modal .edit-media-header button.right')
					.attr('disabled', !json.next_slide)
					.data('attachment', json.next_img_id);

				if ( json.override_thumbnail ) {
					jQuery('#foogallery-image-edit-modal .tab-panels .settings span.setting.override-thumbnail').addClass('is-override-thumbnail');
					jQuery('#foogallery-image-edit-modal .tab-panels .settings span.setting.override-thumbnail-preview').addClass('is-override-thumbnail');
				}

				jQuery('#foogallery-image-edit-modal .media-modal-content .edit-attachment-frame .media-frame-content .attachment-details').html(json.html);

				if ( json.current_tab ) {
					jQuery('.foogallery-img-modal-tab-wrapper[data-tab_id="' + json.current_tab + '"] input').click();
				}

				$wrapper.removeClass('not-loaded');
				$wrapper.css({'grid-template-columns': '1fr 2fr'});
				$loader.removeClass('is-active');
			},
		});
	};

}(window.FOOGALLERY = window.FOOGALLERY || {}, jQuery));

FooGallery.utils.ready(function ($) {
	if ( $('#foogallery_attachments').length > 0 ) {
		FOOGALLERY.adminReady();
	}
});