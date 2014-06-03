(function (FOOGALLERY_DEF_TEMPLATE, $, undefined) {

	FOOGALLERY_DEF_TEMPLATE.setPreviewClasses = function() {

		var $featuredImage = $('#set-post-thumbnail'),
			border_style = $('input[name="foogallery_settings[default_border-style]"]:checked').val(),
			hover_effect = $('input[name="foogallery_settings[default_hover-effect]"]:checked').val();

		//only do this if we have set a featured image
		if ( $featuredImage.find('img').length ) {
			if ($featuredImage.parent().is('.foogallery-container')) {
				$featuredImage.unwrap();	//remove previous!
			}
			//wrap!
			$featuredImage.wrap('<div class="foogallery-container foogallery-default ' + hover_effect + ' ' + border_style + '"></div>');
		}
	};

	FOOGALLERY_DEF_TEMPLATE.adminReady = function () {
		$('body').on('foogallery-gallery-template-changed-default', function() {
			FOOGALLERY_DEF_TEMPLATE.setPreviewClasses();
		});

		$('input[name="foogallery_settings[default_border-style]"], input[name="foogallery_settings[default_hover-effect]"]').change(function() {
			FOOGALLERY_DEF_TEMPLATE.setPreviewClasses();
		});
	};

}(window.FOOGALLERY_DEF_TEMPLATE = window.FOOGALLERY_DEF_TEMPLATE || {}, jQuery));

jQuery(function () {
	FOOGALLERY_DEF_TEMPLATE.adminReady();
});