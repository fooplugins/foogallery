(function(FOOGALLERYALBUM, $, undefined) {

	FOOGALLERYALBUM.bindElements = function() {
		$('.foogallery-album-gallery-list')
			.on('click', '.foogallery-gallery-select', function(e) {
				$(this).toggleClass('selected');
				FOOGALLERYALBUM.changeSelection();
			})
			.sortable({
				items: 'li',
				distance: 10,
				placeholder: 'attachment placeholder',
				stop : function() {
					FOOGALLERYALBUM.changeSelection();
				}
			});

		//init any colorpickers
		$('.colorpicker').spectrum({
			preferredFormat: "rgb",
			showInput: true,
			clickoutFiresChange: true
		});

		$('.foogallery-album-info-modal').prependTo('body');
	};

	FOOGALLERYALBUM.changeSelection = function() {
		var ids = '',
			none = true;
		$('.foogallery-gallery-select.selected').each(function() {
			ids += $(this).data('foogallery-id') + ',';
			none = false;
		});

		if (!none) {
			ids = ids.substring(0, ids.length - 1);
		}
		//build up the list of ids
		$('#foogallery_album_galleries').val(ids);
	};

	FOOGALLERYALBUM.initSettings = function() {
		$('#FooGallerySettings_AlbumTemplate').change(function() {
			var $this = $(this),
				selectedTemplate = $this.val();

			//hide all template fields
			$('.foogallery-album-metabox-settings .foogallery_template_field').not('.foogallery_template_field_selector').hide();

			//show all fields for the selected template only
			$('.foogallery-album-metabox-settings .foogallery_template_field-' + selectedTemplate).show();

			//trigger a change so custom template js can do something
			FOOGALLERYALBUM.triggerTemplateChangedEvent();
		});

		//trigger this onload too!
		FOOGALLERYALBUM.triggerTemplateChangedEvent();
	};

	FOOGALLERYALBUM.triggerTemplateChangedEvent = function() {
		var selectedTemplate = $('#FooGallerySettings_AlbumTemplate').val();
		$('body').trigger('foogallery-album-template-changed-' + selectedTemplate );
	};

	FOOGALLERYALBUM.initAlbumInfoButtons = function() {
		$('.foogallery-album-gallery-list .attachment-preview').on('click', 'a.info', function(e) {

			e.preventDefault();

			e.stopPropagation();

			var $this = $(this),
				$modal = $('.foogallery-album-info-modal'),
				$spinner = $modal.find('.media-frame-title .spinner'),
				$nonce = $modal.find('#foogallery_album_gallery_details_nonce'),
				$details = $modal.find('.gallery-details'),
				data = 'action=foogallery_get_gallery_details' +
					'&foogallery_id=' + $this.data('gallery-id') +
					'&_wpnonce=' + $nonce.val() +
					'&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

			$details.html( $details.data('loading') + $this.data('gallery-title') + '...' );
			$spinner.addClass('is-active');

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function(data) {
					$details.html(data);
				},
				complete: function() {
					$spinner.removeClass('is-active');
				}
			});

			$modal.show();
			$('.media-modal-backdrop').show();
		});

		$('.foogallery-album-info-modal .gallery-details-save').on('click', function(e) {
			e.preventDefault();

			var $this = $(this),
				$modal = $('.foogallery-album-info-modal'),
				$spinner = $modal.find('.media-frame-toolbar .spinner'),
				$nonce = $modal.find('#foogallery_album_gallery_details_nonce'),
				$form = $modal.find('form[name="foogallery_gallery_details"]'),
				data = 'action=foogallery_save_gallery_details' +
						'&_wpnonce=' + $nonce.val() +
						'&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val()) +
						'& ' + $form.serialize();

			$this.attr('disabled', 'disabled');
			$spinner.addClass('is-active');

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function() {
					$('.foogallery-album-info-modal').hide();
					$('.media-modal-backdrop').hide();
				},
				complete: function() {
					$spinner.removeClass('is-active');
					$this.removeAttr('disabled');
				}
			});
		});

		$('.foogallery-album-info-modal .media-modal-close').on('click', function() {
			$('.foogallery-album-info-modal').hide();
			$('.media-modal-backdrop').hide();
		});
	};

	$(function() { //wait for ready
		FOOGALLERYALBUM.bindElements();

		FOOGALLERYALBUM.initSettings();

		FOOGALLERYALBUM.initAlbumInfoButtons();
	});

}(window.FOOGALLERYALBUM = window.FOOGALLERYALBUM || {}, jQuery));