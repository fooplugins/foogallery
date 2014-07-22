//

(function(FOOGALLERY, $, undefined) {

	FOOGALLERY.loadGalleries = function() {
		$('.foogallery-modal-wrapper .spinner').css('display', 'inline-block');
		$('.foogallery-modal-reload').hide();
		var data = 'action=foogallery_load_galleries' +
			'&foogallery_load_galleries=' + $('#foogallery_load_galleries').val() +
			'&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(data) {
				$('.foogallery-attachment-container').html(data);
				FOOGALLERY.clearSelection();
			},
			complete: function() {
				$('.foogallery-modal-wrapper .spinner').hide();
				$('.foogallery-modal-reload').show();
			}
		});
	};

	//hook up the extensions search
	FOOGALLERY.bindEditorButton = function() {
		$('.foogallery-modal-trigger').on('click', function(e) {
			e.preventDefault();
			$('.foogallery-modal-wrapper').show();
			if ( $('.foogallery-modal-loading').length ) {
				FOOGALLERY.loadGalleries();
			} else {
				FOOGALLERY.clearSelection();
			}
		});
	};

	FOOGALLERY.bindModalElements = function() {
		$('.media-modal-close, .foogallery-modal-cancel').on('click', function() {
			$('.foogallery-modal-wrapper').hide();
		});

		$('.foogallery-modal-reload').on('click', function(e) {
			e.preventDefault();
			FOOGALLERY.loadGalleries();
		});

		$('.foogallery-modal-wrapper').on('click', '.foogallery-gallery-select', function(e) {
			var $this = $(this);
			if ( $this.is('.foogallery-add-gallery') ) {
				//if the add icon is click then do nothing
				return;
			} else {
				$('.foogallery-gallery-select').removeClass('selected');
				$(this).addClass('selected');
				FOOGALLERY.changeSelection();
			}
		});

		$('.foogallery-modal-insert').on('click', function(e) {
			e.preventDefault();
			if ( $(this).attr('disabled') ) {
				return;
			}
			var shortcode_tag = window.FOOGALLERY_SHORTCODE || 'foogallery',
				shortcode = '[' + shortcode_tag + ' id="' + $('.foogallery-gallery-select.selected').data('foogallery-id') + '"]';
			wp.media.editor.insert(shortcode);
			$('.foogallery-modal-wrapper').hide();
		});
	};

	FOOGALLERY.changeSelection = function() {
		var selected = $('.foogallery-gallery-select.selected');
		if (selected.length) {
			$('.foogallery-modal-insert').removeAttr('disabled');
		} else {
			$('.foogallery-modal-insert').attr('disabled', 'disabled');
		}
	};

	FOOGALLERY.clearSelection = function() {
		$('.foogallery-gallery-select').removeClass('selected');
		FOOGALLERY.changeSelection();
	}

	$(function() { //wait for ready
		FOOGALLERY.bindEditorButton();
		FOOGALLERY.bindModalElements();
	});
}(window.FOOGALLERY = window.FOOGALLERY || {}, jQuery));