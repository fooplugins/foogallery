jQuery(function ($) {
	//handle any masonry preview changes
	$('body').on('foogallery-gallery-preview-updated-masonry', function() {

		var options = {},
			$preview = $('#foogallery_preview .foogallery'),
			layout = $('.foogallery_template_field_template_id-masonry-layout input:checked').val();

		FooGallery.utils.obj.prop(options, 'template.layout', layout);

		if (layout === 'fixed') {
			var columnWidth = $('#FooGallerySettings_masonry_thumbnail_width').val(),
				gutter = $('#FooGallerySettings_masonry_gutter_width').val();

			FooGallery.utils.obj.prop(options, 'template.columnWidth', parseInt(columnWidth));
			FooGallery.utils.obj.prop(options, 'template.gutter', parseInt(gutter));
		}

		//clear any previously created data options
		$preview.removeData('foogallery');
		$preview.removeAttr('data-foogallery');

		$preview.foogallery(options);
	} );
});