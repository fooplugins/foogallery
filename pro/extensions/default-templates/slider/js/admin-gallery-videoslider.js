(function (FOOGALLERY_VIDEOSLIDER_TEMPLATE, $, undefined) {

	FOOGALLERY_VIDEOSLIDER_TEMPLATE.adminReady = function () {
		$('body').on('foogallery-gallery-template-changed-videoslider', function() {
			$(".rvs-container").rvslider();
		});
	};

}(window.FOOGALLERY_VIDEOSLIDER_TEMPLATE = window.FOOGALLERY_VIDEOSLIDER_TEMPLATE || {}, jQuery));

jQuery(function () {
	FOOGALLERY_VIDEOSLIDER_TEMPLATE.adminReady();
});