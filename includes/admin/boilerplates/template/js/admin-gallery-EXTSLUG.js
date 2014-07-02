//Use this file to inject custom javascript behaviour into the foogallery edit page
//For an example usage, check out wp-content/foogallery/extensions/default-templates/js/admin-gallery-default.js

(function (FOOGALLERY_{package}_TEMPLATE, $, undefined) {

	FOOGALLERY_{package}_TEMPLATE.doSomething = function() {
		//do something when the gallery template is changed to {slug}
	};

	FOOGALLERY_{package}_TEMPLATE.adminReady = function () {
		$('body').on('foogallery-gallery-template-changed-{slug}', function() {
			FOOGALLERY_DEF_TEMPLATE.doSomething();
		});
	};

}(window.FOOGALLERY_{package}_TEMPLATE = window.FOOGALLERY_{package}_TEMPLATE || {}, jQuery));

jQuery(function () {
	FOOGALLERY_{package}_TEMPLATE.adminReady();
});