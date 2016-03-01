(function (FOOGALLERY_MASONRY_TEMPLATE, $, undefined) {

	FOOGALLERY_MASONRY_TEMPLATE.showHideControls = function() {

		var layout = $('input[name="foogallery_settings[masonry_layout]"]:checked').val();

		if ( layout === 'fixed' ) {
			$('.gallery_template_field-masonry-gutter_width, .gallery_template_field-masonry-center_align').show();
			$('.gallery_template_field-masonry-gutter_percent').hide();
		} else {
			$('.gallery_template_field-masonry-gutter_width, .gallery_template_field-masonry-center_align').hide();
			$('.gallery_template_field-masonry-gutter_percent').show();
		}
	};

	FOOGALLERY_MASONRY_TEMPLATE.adminReady = function () {
		$('body').on('foogallery-gallery-template-changed-masonry', function() {
			FOOGALLERY_MASONRY_TEMPLATE.showHideControls();
		});

		$('input[name="foogallery_settings[masonry_layout]"]').change(function() {
			FOOGALLERY_MASONRY_TEMPLATE.showHideControls();
		});
	};

}(window.FOOGALLERY_MASONRY_TEMPLATE = window.FOOGALLERY_MASONRY_TEMPLATE || {}, jQuery));

jQuery(function () {
	FOOGALLERY_MASONRY_TEMPLATE.adminReady();
});