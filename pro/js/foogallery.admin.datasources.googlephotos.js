FooGallery.utils.ready(function ($) {

	// Define with default values
	document.foogallery_datasource_value_temp = {
		"album_id" : '',
		"album_title" : '',
		"sort" : ''
	};

	/* Manage media javascript */
	$('.foogallery-datasource-googlephotos').on('click', 'button.remove', function (e) {
		e.preventDefault();

		//hide the previous info
		$(this).parents('.foogallery-datasource-googlephotos').hide();

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

	/* Show google photos tab in modal */
	$('.foogallery-datasource-googlephotos').on('click', 'button.edit', function (e) {
		e.preventDefault();

		//show the modal
		$('.foogallery-datasources-modal-wrapper').show();

		//select the google photos datasource
		$('.foogallery-datasource-modal-selector[data-datasource="googlephotos"]').click();
	});

	/* Hide google photos data source */
	$(document).on('foogallery-datasource-changed', function(e, activeDatasource) {
		$('.foogallery-datasource-googlephotos').hide();

		if ( activeDatasource !== 'googlephotos' ) {
			//clear the selected google photos
		}
	});

	$(document).on('foogallery-datasource-changed-googlephotos', function() {
		var $container = $('.foogallery-datasource-googlephotos');

		$('#_foogallery_datasource_value').val(JSON.stringify(document.foogallery_datasource_value_temp));

		$container.find('.foogallery-items-html #foogallery-datasource-googlephotos-album-name').html(document.foogallery_datasource_value_temp.album_title);

		$container.show();

		FOOGALLERY.showHiddenAreas( false );

		$('.foogallery-attachments-list-container').addClass('hidden');

		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});

	// Disable click event on google photos album thumbnail
	$(document).on('click', '.foogallery-datasource-googlephotos-albums a', function(e){
		e.preventDefault();

		var $clicked = $(this);

		// Get data from click element
		var album_id = $clicked.attr('data-album_id');
		var album_title = $clicked.attr('data-label');

		// Set the selection
		document.foogallery_datasource_value_temp = {
			"album_id" : album_id,
			"album_title" : album_title,
			"sort" : ''
		};

		//make sure the modal insert button is active
        $('.foogallery-datasource-modal-insert').attr('disabled', false);

	});

	// Display photos from google album thumbnail double click
	$(document).on('dblclick', '.foogallery-datasource-googlephotos-albums a', function(e){
		e.preventDefault();
		var $clicked = $(this);
		//make sure the modal insert button is active
        $('.foogallery-datasource-modal-insert').attr('disabled', false);

		$(document).find('.foogallery-datasource-googlephotos-albums a').removeClass('active');
		$clicked.addClass('active');		
	});

});