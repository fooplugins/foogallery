FooGallery.utils.ready(function ($) {
	$('.foogallery-datasource-modal-container').on('click', '.datasource-taxonomy a', function (e) {
		e.preventDefault();
		$(this).toggleClass('button-primary');
		var $selected = $(this).parents('ul:first').find('a.button-primary'),
			$parent_ul = $(this).parents('ul:first'),
			taxonomy = $parent_ul.data('taxonomy'),
			taxonomy_values = [],
			taxonomies = [],
			html = '';

		//validate if the OK button can be pressed.
		if ( $selected.length > 0 ) {
			$('.foogallery-datasource-modal-insert').removeAttr( 'disabled' );

			$selected.each(function() {
				taxonomy_values.push( $(this).data('termId') );
				taxonomies.push( $(this).text() );
				html += '<li>' + $(this).text() + '</li>';
			});

		} else {
			$('.foogallery-datasource-modal-insert').attr('disabled','disabled');
			html = '';
		}

		//set the selection
		document.foogallery_datasource_value_temp = {
			"taxonomy" : taxonomy,
			"value" : taxonomy_values,
			"html" : '<ul>' + html + '</ul>'
		};
	});

	$('.foogallery-datasource-taxonomy').on('click', 'button.remove', function (e) {
		e.preventDefault();

		//hide the previous info
		$(this).parents('.foogallery-datasource-taxonomy').hide();

		//clear the datasource value
		$('#_foogallery_datasource_value').val('');

		//clear the datasource
		$('#foogallery_datasource').val('');

		//deselect any media tag buttons in the modal
		$('.foogallery-datasource-modal-container .datasource-taxonomy a.active').removeClass('active');

		//make sure the modal insert button is not active
		$('.foogallery-datasource-modal-insert').attr('disabled','disabled');

		FOOGALLERY.showHiddenAreas( true );

		//ensure the preview will be refreshed
		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});

	$('.foogallery-datasource-taxonomy').on('click', 'button.edit', function (e) {
		e.preventDefault();

		//show the modal
		$('.foogallery-datasources-modal-wrapper').show();

		//select the media tags datasource
		$('.foogallery-datasource-modal-selector[data-datasource="' + $(this).data('datasource') + '"]').click();
	});

	$('.foogallery-datasource-taxonomy').on('click', 'button.media', function(e) {
		e.preventDefault();

		if (typeof(document.foogallery_taxonomy_modal) !== 'undefined'){
			document.foogallery_taxonomy_modal.open();
			return;
		}

		$container = $(this).parents('.foogallery-datasource-taxonomy:first');

		document.foogallery_taxonomy_modal = wp.media({
			frame: 'select',
			title: $container.data('media-title'),
			button: {
				text: $container.data('media-button')
			},
			library: {
				type: 'image'
			}
		}).on( 'open', function() {
			//ensure the preview will be refreshed
			$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
		});

		document.foogallery_taxonomy_modal.open();
	});

	$('.foogallery-datasource-taxonomy').on('click', 'button.help', function(e) {
		e.preventDefault();

		$('.foogallery-datasource-taxonomy-help').toggle();
	});
});