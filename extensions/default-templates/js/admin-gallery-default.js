(function (FOOGALLERY_DEF_TEMPLATE, $, undefined) {

	FOOGALLERY_DEF_TEMPLATE.setPreviewClasses = function() {

		var $previewImage = $('.foogallery-thumbnail-preview'),
			border_style = $('input[name="foogallery_settings[default_border-style]"]:checked').val(),
			hover_effect = $('input[name="foogallery_settings[default_hover-effect]"]:checked').val();

		$previewImage.attr('class' ,'foogallery-thumbnail-preview foogallery-container foogallery-default ' + hover_effect + ' ' + border_style);
	};

	FOOGALLERY_DEF_TEMPLATE.adminReady = function () {
		$('body').on('foogallery-gallery-template-changed-default', function() {
			FOOGALLERY_DEF_TEMPLATE.setPreviewClasses();
		});

		$('input[name="foogallery_settings[default_border-style]"], input[name="foogallery_settings[default_hover-effect]"]').change(function() {
			FOOGALLERY_DEF_TEMPLATE.setPreviewClasses();
		});

		$('.foogallery-thumbnail-preview').click(function(e) {
			e.preventDefault();
		});
	};

}(window.FOOGALLERY_DEF_TEMPLATE = window.FOOGALLERY_DEF_TEMPLATE || {}, jQuery));

jQuery(function () {
	FOOGALLERY_DEF_TEMPLATE.adminReady();
});