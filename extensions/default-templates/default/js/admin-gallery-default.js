(function (FOOGALLERY_DEF_TEMPLATE, $, undefined) {

	FOOGALLERY_DEF_TEMPLATE.setPreviewClasses = function() {

		var $previewImage = $('.foogallery-default-preview'),
			border_style = $('input[name="foogallery_settings[default_border-style]"]:checked').val(),
			hover_effect = $('input[name="foogallery_settings[default_hover-effect]"]:checked').val(),
		    hover_effect_type = $('input[name="foogallery_settings[default_hover-effect-type]"]:checked').val(),
			caption_effect_type = $('input[name="foogallery_settings[default_caption-hover-effect]"]:checked').val(),
			classNames = 'foogallery-default-preview foogallery-container foogallery-default ' + border_style + ' ' + hover_effect_type;

		var $hoverEffectrow = $('.gallery_template_field-default-hover-effect'),
			$captionHoverRow = $('.gallery_template_field-default-caption-hover-effect'),
			$captionContentRow = $('.gallery_template_field-default-caption-content');

		if ( hover_effect_type === '' ) {
			//icon hover effect type
			$hoverEffectrow.show();
			$captionHoverRow.hide();
			$captionContentRow.hide();
			classNames += ' ' + hover_effect;
		} else if ( hover_effect_type === 'hover-effect-caption' ) {
			//caption hover effect type
			$hoverEffectrow.hide();
			$captionHoverRow.show();
			$captionContentRow.show();
			classNames += ' ' + caption_effect_type;
		} else {
			//no hover effect type
			$hoverEffectrow.hide();
			$captionHoverRow.hide();
			$captionContentRow.hide();
		}

		$previewImage.attr('class' , classNames);
	};

	FOOGALLERY_DEF_TEMPLATE.showHideCaptionContent = function(){
		var $previewImage = $('.foogallery-default-preview'),
			$caption = $previewImage.find('.foogallery-caption'),
			$title = $previewImage.find('.foogallery-caption-title'),
			$desc = $previewImage.find('.foogallery-caption-desc'),
			caption_content = $('input[name="foogallery_settings[default_caption-content]"]:checked').val();

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

	FOOGALLERY_DEF_TEMPLATE.adminReady = function () {
		$('body').on('foogallery-gallery-template-changed-default', function() {
			FOOGALLERY_DEF_TEMPLATE.setPreviewClasses();
			FOOGALLERY_DEF_TEMPLATE.showHideCaptionContent();
		});

		$('input[name="foogallery_settings[default_border-style]"], ' +
		  'input[name="foogallery_settings[default_hover-effect]"], ' +
		  'input[name="foogallery_settings[default_hover-effect-type]"], ' +
		  'input[name="foogallery_settings[default_caption-hover-effect]"]').change(function() {
			FOOGALLERY_DEF_TEMPLATE.setPreviewClasses();
		});

		$('input[name="foogallery_settings[default_caption-content]"]').change(function() {
			FOOGALLERY_DEF_TEMPLATE.showHideCaptionContent();
		});

		$('.foogallery-thumbnail-preview').on('click', function(e) {
			e.preventDefault();
		});

		//run when the page load for the first time too!
		FOOGALLERY_DEF_TEMPLATE.setPreviewClasses();
		FOOGALLERY_DEF_TEMPLATE.showHideCaptionContent();
	};

}(window.FOOGALLERY_DEF_TEMPLATE = window.FOOGALLERY_DEF_TEMPLATE || {}, jQuery));

jQuery(function () {
	FOOGALLERY_DEF_TEMPLATE.adminReady();
});