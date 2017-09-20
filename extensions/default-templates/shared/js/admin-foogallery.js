(function (FOOGALLERY, $, undefined) {

	FOOGALLERY.initFooGallery = function (options) {
		var $preview = $('.foogallery_preview_container .foogallery');
		$preview.foogallery(options);
	};

}(window.FOOGALLERY = window.FOOGALLERY || {}, jQuery));

jQuery(function ($) {
	//handle any preview changes
	$('body').on('foogallery-gallery-preview-updated-masonry ' +
		'foogallery-gallery-preview-updated-default ' +
		'foogallery-gallery-preview-updated-justified ' +
		'foogallery-gallery-preview-updated-simple_portfolio ' +
		'foogallery-gallery-preview-updated-image-viewer ' +
		'foogallery-gallery-preview-updated-thumbnail ' +
		'foogallery-gallery-preview-updated-polaroid_new', function() {
		FOOGALLERY.initFooGallery();
	} );
});

