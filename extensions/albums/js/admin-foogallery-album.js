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
			$('.foogallery-album-metabox-settings .gallery_template_field').not('.gallery_template_field_selector').hide();

			//show all fields for the selected template only
			$('.foogallery-album-metabox-settings .gallery_template_field-' + selectedTemplate).show();

			//trigger a change so custom template js can do something
			FOOGALLERY.triggerTemplateChangedEvent();
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
				$modal = $('.foogallery-album-info-modal');

			$modal.find('.gallery-title').html( $this.data('gallery-title') );
			$modal.find('.gallery-images').html( $this.data('gallery-images') );
			$modal.find('.gallery-url').val( $this.data('gallery-url') );

			$modal.show();
		});

		$('.foogallery-album-info-modal .media-modal-close').on('click', function() {
			$('.foogallery-album-info-modal').hide();
		});
	};

	$(function() { //wait for ready
		FOOGALLERYALBUM.bindElements();

		FOOGALLERYALBUM.initSettings();

		FOOGALLERYALBUM.initAlbumInfoButtons();
	});

}(window.FOOGALLERYALBUM = window.FOOGALLERYALBUM || {}, jQuery));