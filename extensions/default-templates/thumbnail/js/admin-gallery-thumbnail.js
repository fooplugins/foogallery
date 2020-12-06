(function (FOOGALLERY_THUMBNAIL_TEMPLATE, $, undefined) {

	FOOGALLERY_THUMBNAIL_TEMPLATE.setPreviewClasses = function() {

		var $previewImage = $('.foogallery-thumbnail-preview'),
			border_style = $('input[name="foogallery_settings[thumbnail_border-style]"]:checked').val(),
			hover_effect = $('input[name="foogallery_settings[thumbnail_hover-effect]"]:checked').val();

		$previewImage.attr('class' ,'foogallery-thumbnail-preview foogallery-container foogallery-thumbnail ' + hover_effect + ' ' + border_style);
	};

	FOOGALLERY_THUMBNAIL_TEMPLATE.adminReady = function () {
		$('body').on('foogallery-gallery-template-changed-thumbnail', function() {
			FOOGALLERY_THUMBNAIL_TEMPLATE.setPreviewClasses();
		});

		$('input[name="foogallery_settings[thumbnail_border-style]"], input[name="foogallery_settings[thumbnail_hover-effect]"]').change(function() {
			FOOGALLERY_THUMBNAIL_TEMPLATE.setPreviewClasses();
		});

		$('.foogallery-thumbnail-preview').on('click', function(e) {
			e.preventDefault();
		});
	};

}(window.FOOGALLERY_THUMBNAIL_TEMPLATE = window.FOOGALLERY_THUMBNAIL_TEMPLATE || {}, jQuery));

jQuery(function () {
	FOOGALLERY_THUMBNAIL_TEMPLATE.adminReady();
});