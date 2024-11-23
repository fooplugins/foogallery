jQuery(function ($) {
	$('.gallery_datasources_button').on('click', function(e) {
		e.preventDefault();
		$('.foogallery-datasources-modal-wrapper').show();
	});

	$('.foogallery-datasources-modal-wrapper').on('click', '.media-modal-close, .foogallery-datasource-modal-cancel', function(e) {
		$('.foogallery-datasources-modal-wrapper').hide();
	});

	$('.foogallery-datasources-modal-wrapper').on('click', '.foogallery-datasource-modal-insert', function(e) {
		var activeDatasource = $('.foogallery-datasource-modal-selector.active').data('datasource');

		//set the datasource
		$('#foogallery_datasource').val( activeDatasource );

		//raise a general event so that other datasources can clean up
		$(document).trigger('foogallery-datasource-changed', activeDatasource);

		//raise a specific event for the new datasource so that things can be done
		$(document).trigger('foogallery-datasource-changed-' + activeDatasource);

		//hide the datasource modal
		$('.foogallery-datasources-modal-wrapper').hide();
	});

	$('.foogallery-datasources-modal-wrapper').on('click', '.foogallery-datasource-modal-reload', function(e) {
		e.preventDefault();

		var $wrapper = $('.foogallery-datasources-modal-wrapper'),
			datasource = $wrapper.data('datasource'),
			$content = $('.foogallery-datasource-modal-container-inner.' + datasource);

		$content.addClass('not-loaded');

		//force the refresh
		$('.foogallery-datasource-modal-selector.active').click();
	});

	$('.foogallery-datasource-modal-selector').on('click', function(e) {
		e.preventDefault();

		var datasource = $(this).data('datasource'),
			$content = $('.foogallery-datasource-modal-container-inner.' + datasource),
			$wrapper = $('.foogallery-datasources-modal-wrapper');

		$('.foogallery-datasource-modal-selector').removeClass('active');
		$(this).addClass('active');

		$('.foogallery-datasource-modal-container-inner').hide();

		$content.show();

		var datasource_value = $('#_foogallery_datasource_value').val();

		if ( $content.hasClass('not-loaded') ) {
			$content.find('.spinner').addClass('is-active');

			$content.removeClass('not-loaded');

			var data = 'action=foogallery_load_datasource_content' +
				'&datasource=' + datasource +
				'&datasource_value=' + encodeURIComponent(datasource_value) +
				'&foogallery_id=' + $wrapper.data('foogalleryid') +
				'&nonce=' + $wrapper.data('nonce');

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function(data) {
					$('.foogallery-datasource-modal-reload').show();
					$wrapper.data('datasource', datasource );

					$content.html(data);
					//raise a event so that datasource-specific code can run
					$(document).trigger('foogallery-datasource-content-loaded-' + datasource);
				}
			});
		}
	});
});
