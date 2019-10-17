jQuery(function ($) {

	/* Manage media javascript */
	$('.foogallery-datasource-instagram').on('click', 'button.remove', function (e) {
		e.preventDefault();

		//hide the previous info
		$(this).parents('.foogallery-datasource-instagram').hide();

		//clear the datasource value
		$('#_foogallery_datasource_value').val('');

		//clear the datasource
		$('#foogallery_datasource').val('');

		//make sure the modal insert button is not active
		$('.foogallery-datasource-modal-insert').attr('disabled','disabled');

		FOOGALLERY.showHiddenAreas( true );

		//ensure the preview will be refreshed
		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});

	$('.foogallery-datasource-instagram').on('click', 'button.edit', function (e) {
		e.preventDefault();

		//show the modal
		$('.foogallery-datasources-modal-wrapper').show();

		//select the instagram datasource
		$('.foogallery-datasource-modal-selector[data-datasource="instagram"]').click();
	});

	$(document).on('foogallery-datasource-changed', function(e, activeDatasource) {
		$('.foogallery-datasource-instagram').hide();

		if ( activeDatasource !== 'instagram' ) {
			//clear the selected instagram
		}
	});

	$(document).on('foogallery-datasource-changed-instagram', function() {
		var $container = $('.foogallery-datasource-instagram');

		//build up the datasource_value
		var value = {
			"account" : $('#instagram_account').val(),
			"image_count" : $('#instagram_image_count').val(),
			"image_resolution" : $('#instagram_image_resolution').val()
		};

		//save the datasource_value
		$('#_foogallery_datasource_value').val( JSON.stringify( value ) );

		$container.show();

		FOOGALLERY.showHiddenAreas( false );

		$('.foogallery-attachments-list').addClass('hidden');

		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});

	
});