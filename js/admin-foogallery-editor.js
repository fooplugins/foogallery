//

(function(FOOGALLERY, $, undefined) {

	FOOGALLERY.loadGalleries = function() {
		$('.foogallery-modal-wrapper .spinner').addClass('is-active');
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
				$('.foogallery-modal-wrapper .spinner').removeClass('is-active');
				$('.foogallery-modal-reload').show();
			}
		});
	};

	//hook up the extensions search
	FOOGALLERY.bindEditorButton = function() {
		$(document.body).on('click', '.foogallery-modal-trigger', function(e) {
			e.preventDefault();
			//set the active editor
			FOOGALLERY.activeEditor = $(this).data('editor');
			$('.foogallery-modal-wrapper').show();
			if ( $('.foogallery-modal-loading').length ) {
				FOOGALLERY.loadGalleries();
			} else {
				FOOGALLERY.clearSelection();
			}
		});
	};

	FOOGALLERY.bindModalElements = function() {
		$(document.body).on('click', '.media-modal-close, .foogallery-modal-cancel', function(e) {
			$('.foogallery-modal-wrapper').hide();
		});

		$(document.body).on('click', '.foogallery-modal-reload', function(e) {
			e.preventDefault();
			FOOGALLERY.loadGalleries();
		});

		$(document.body).on('click', '.foogallery-gallery-select', function(e) {
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

		$(document.body).on('click', '.foogallery-modal-insert', function(e) {
			e.preventDefault();
			if ( $(this).attr('disabled') ) {
				return;
			}
			var shortcode_tag = window.FOOGALLERY_SHORTCODE || 'foogallery',
					shortcode = '[' + shortcode_tag + ' id="' + $('.foogallery-gallery-select.selected').data('foogallery-id') + '"]';

			var editor = tinyMCE.get(FOOGALLERY.activeEditor);
			if (editor) {
				editor.execCommand('mceInsertContent', false, shortcode);
			} else {
				wp.media.editor.insert(shortcode);
			}

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
	};

	$(function() { //wait for ready
	});

	FOOGALLERY.bindEditorButton();
	FOOGALLERY.bindModalElements();
	FOOGALLERY.activeEditor = 'content';

}(window.FOOGALLERY = window.FOOGALLERY || {}, jQuery));
