(function (FOOGALLERY, $, undefined) {

	FOOGALLERY.initFooGallery = function (options) {
		var $preview = $('.foogallery_preview_container .foogallery');

		//clear any previously created data options
		$preview.removeData('foogallery');
		$preview.removeAttr('data-foogallery');

		$preview.foogallery(options);
	};

}(window.FOOGALLERY = window.FOOGALLERY || {}, jQuery));

jQuery(function ($) {

	//handle any masonry preview changes
	$('body').on('foogallery-gallery-preview-updated-masonry', function() {

		var options = {},
			layout = $('.foogallery_template_field_template_id-masonry-layout input:checked').val();

		FooGallery.utils.obj.prop(options, 'template.layout', layout);

		if (layout === 'fixed') {
			var columnWidth = $('#FooGallerySettings_masonry_thumbnail_width').val(),
				gutter = $('#FooGallerySettings_masonry_gutter_width').val();

			FooGallery.utils.obj.prop(options, 'template.columnWidth', parseInt(columnWidth));
			FooGallery.utils.obj.prop(options, 'template.gutter', parseInt(gutter));
		}

		FOOGALLERY.initFooGallery(options);
	} );

	$('body').on('foogallery-gallery-preview-updated-default', function() {
		FOOGALLERY.initFooGallery({});
	});

	$('body').on('foogallery-gallery-preview-updated-justified', function() {
		var options = {},
			rowHeight = $('#FooGallerySettings_justified_row_height').val(),
			maxRowHeight = $('#FooGallerySettings_justified_max_row_height').val(),
			margins = $('#FooGallerySettings_justified_margins').val();

		FooGallery.utils.obj.prop(options, 'template.rowHeight', parseInt(rowHeight));
		FooGallery.utils.obj.prop(options, 'template.maxRowHeight', parseInt(maxRowHeight));
		FooGallery.utils.obj.prop(options, 'template.margins', parseInt(margins));

		FOOGALLERY.initFooGallery(options);
	});

	$('body').on('foogallery-gallery-preview-updated-simple_portfolio', function() {
		var options = {},
			gutter = $('#FooGallerySettings_simple_portfolio_gutter').val();

		FooGallery.utils.obj.prop(options, 'template.gutter', parseInt(gutter));

		FOOGALLERY.initFooGallery(options);
	});
});

