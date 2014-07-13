//Use this file to inject custom javascript behaviour into the foogallery edit page
//For an example usage, check out wp-content/foogallery/extensions/default-templates/js/admin-gallery-default.js

(function ({constant}, $, undefined) {

	{constant}.doSomething = function() {
		//do something when the gallery template is changed to {slug}
	};

	{constant}.adminReady = function () {
		$('body').on('foogallery-gallery-template-changed-{slug}', function() {
			{constant}.doSomething();
		});
	};

}(window.{constant} = window.{constant} || {}, jQuery));

jQuery(function () {
	{constant}.adminReady();
});