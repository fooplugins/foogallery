(function (FOOGALLERY, $, undefined) {

	FOOGALLERY.initFooGallery = function (options) {
		var $preview = $('.foogallery_preview_container .foogallery');
		$preview.foogallery(options);
	};

}(window.FOOGALLERY = window.FOOGALLERY || {}, jQuery));

jQuery(function ($) {

	//handle any masonry preview changes
	$('body').on('foogallery-gallery-preview-updated-masonry', function() {
		FOOGALLERY.initFooGallery();
	} );

	$('body').on('foogallery-gallery-preview-updated-default', function() {
		FOOGALLERY.initFooGallery({});
	});

	$('body').on('foogallery-gallery-preview-updated-justified', function() {
		FOOGALLERY.initFooGallery();
	});

	$('body').on('foogallery-gallery-preview-updated-simple_portfolio', function() {
		FOOGALLERY.initFooGallery();
	});

	$('body').on('foogallery-gallery-preview-updated-image-viewer', function() {
		FOOGALLERY.initFooGallery({});
	});

	$('body').on('foogallery-gallery-preview-updated-thumbnail', function() {
		FOOGALLERY.initFooGallery({});
	});
});

