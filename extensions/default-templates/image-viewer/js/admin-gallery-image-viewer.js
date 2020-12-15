//Use this file to inject custom javascript behaviour into the foogallery edit page
//For an example usage, check out wp-content/foogallery/extensions/default-templates/js/admin-gallery-default.js

(function (IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION, $, undefined) {

	IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION.setPreviewClasses = function() {
		var $previewImage = $('.foogallery-image-viewer-preview'),
			theme = $('input[name="foogallery_settings[image-viewer_theme]"]:checked').val(),
			hover_effect = $('input[name="foogallery_settings[image-viewer_hover-effect]"]:checked').val(),
			hover_effect_type = $('input[name="foogallery_settings[image-viewer_hover-effect-type]"]:checked').val();

		var $styles = $('#image-preview-custom-styles');
		if (theme === 'fiv-custom'){
			var bg_color = $('input[name="foogallery_settings[image-viewer_theme_custom_bgcolor]"]').val(),
				text_color = $('input[name="foogallery_settings[image-viewer_theme_custom_textcolor]"]').val(),
				border_color = $('input[name="foogallery_settings[image-viewer_theme_custom_bordercolor]"]').val(),
				hover_color = $('input[name="foogallery_settings[image-viewer_theme_custom_hovercolor]"]').val();

			var css = '.foogallery-image-viewer-preview.fiv-custom > .fiv-inner,';
			css += '.foogallery-image-viewer-preview.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-prev,';
			css += '.foogallery-image-viewer-preview.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-next {';
				css += 'background-color: '+bg_color+';';
				css += 'color: '+text_color+';';
			css += '}';
			css += '.foogallery-image-viewer-preview.fiv-custom > .fiv-inner,';
			css += '.foogallery-image-viewer-preview.fiv-custom > .fiv-inner > .fiv-inner-container,';
			css += '.foogallery-image-viewer-preview.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-prev,';
			css += '.foogallery-image-viewer-preview.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-next {';
				css += 'border-color: '+border_color+';';
			css += '}';
			css += '.foogallery-image-viewer-preview.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-prev:hover,';
			css += '.foogallery-image-viewer-preview.fiv-custom > .fiv-inner > .fiv-ctrls > .fiv-next:hover {';
				css += 'background-color: '+hover_color+';';
			css += '}';
			$styles.remove();
			$('head').append('<style id="image-preview-custom-styles">'+css+'</style>');
		} else {
			$styles.remove();
		}

		$previewImage.attr('class' ,'foogallery-image-viewer-preview foogallery-container foogallery-image-viewer ' + theme);
		if (hover_effect_type !== 'hover-effect-none'){
			$previewImage.addClass(hover_effect_type + ' ' + hover_effect);
		}

		var $hoverEffectrow = $('.gallery_template_field-image-viewer-hover-effect');
		if ( hover_effect_type === '' ) {
			$hoverEffectrow.show();
		} else {
			$hoverEffectrow.hide();
		}
	};

	IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION.showHideRows = function(){
		var $theme_rows = $('.gallery_template_field-image-viewer-theme_custom_bgcolor')
			.add('.gallery_template_field-image-viewer-theme_custom_textcolor')
			.add('.gallery_template_field-image-viewer-theme_custom_hovercolor')
			.add('.gallery_template_field-image-viewer-theme_custom_bordercolor');

		if ( $('input[name="foogallery_settings[image-viewer_theme]"]:checked').val() === 'fiv-custom' ) {
			$theme_rows.show();
		} else {
			$theme_rows.hide();
		}
	};

	IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION.showHideCaptionContent = function(){
		var $previewImage = $('.foogallery-image-viewer-preview'),
			$caption = $previewImage.find('.foogallery-caption'),
			$title = $previewImage.find('.foogallery-caption-title'),
			$desc = $previewImage.find('.foogallery-caption-desc'),
			caption_content = $('input[name="foogallery_settings[image-viewer_caption-content]"]:checked').val();

		$caption.add($title).add($desc).show();
		switch(caption_content){
			case 'title':
				$desc.hide();
				break;
			case 'desc':
				$title.hide();
				break;
			case 'none':
				$caption.hide();
				break;
		}
	};

	IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION.adminReady = function () {
		$('body').on('foogallery-gallery-template-changed-image-viewer', function() {
			IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION.setPreviewClasses();
			IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION.showHideRows();
			IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION.showHideCaptionContent();
		});

		var ps = 'input[name="foogallery_settings[image-viewer_hover-effect]"], ' +
			'input[name="foogallery_settings[image-viewer_hover-effect-type]"], ' +
			'input[name="foogallery_settings[image-viewer_theme]"], ' +
			'input[name="foogallery_settings[image-viewer_theme_custom_bgcolor]"], ' +
			'input[name="foogallery_settings[image-viewer_theme_custom_textcolor]"], ' +
			'input[name="foogallery_settings[image-viewer_theme_custom_bordercolor]"], ' +
			'input[name="foogallery_settings[image-viewer_theme_custom_hovercolor]"]';
		$(ps).change(function() {
			IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION.setPreviewClasses();
		});

		$('input[name="foogallery_settings[image-viewer_caption-content]"]').change(function() {
			IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION.showHideCaptionContent();
		});

		$('input[name="foogallery_settings[image-viewer_theme]"]').change(function() {
			IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION.showHideRows();
		});

		$('.foogallery-image-viewer-preview').on('click', function(e) {
			e.preventDefault();
		});
	};

}(window.IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION = window.IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION || {}, jQuery));

jQuery(function () {
	IMAGE_VIEWER_TEMPLATE_FOOGALLERY_EXTENSION.adminReady();
});