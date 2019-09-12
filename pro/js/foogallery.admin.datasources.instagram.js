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

	/* Modal javascript */

	$(document).on('foogallery-datasource-content-loaded-instagram', function () {
		foogalleryInitSortable();

		$('input:radio[name=foogallery-datasource-instagram-metadata]').change(function(e) {
			e.preventDefault();

			$('.foogallery-datasource-instagram-metadata-selector .spinner').addClass('is-active');

			var instagram = $('.foogallery-datasource-instagram-selected').text();
			foogalleryRefreshDatasourceinstagramContainer(instagram);
		});

		$('.foogallery-datasource-instagram-container').on('click', '.foogallery-datasource-instagram-list ul li a', function (e) {
			e.preventDefault();

			var $this = $(this),
				instagram = $this.data('instagram');

			$this.append('<span class="is-active spinner"></span>');

			$('.foogallery-datasource-instagram-selected').text(instagram);

			foogalleryRefreshDatasourceinstagramContainer(instagram);
		});

		$('.foogallery-datasource-instagram-container').on('click', '.foogallery-server-image-list-edit', function(e) {
			e.preventDefault();

			document.$selectedMetadataItem = $(this).parents('li:first');

			$('.foogallery-server-image-metadata-form').addClass('shown');

			//populate the form with the metadata from the image
			foogalleryPopulateMetadataForm();
		});

		$('.foogallery-datasource-instagram-container').on('click', '.foogallery-server-image-metadata-form-button-cancel', function(e) {
			e.preventDefault();
			document.$selectedMetadataItem = null;
			$('.foogallery-server-image-metadata-form').removeClass('shown');
		});

		$('.foogallery-datasource-instagram-container').on('click', '.foogallery-server-image-metadata-form-button-next', function(e) {
			e.preventDefault();
			foogallerySaveMetadata();
			document.$selectedMetadataItem = document.$selectedMetadataItem.next();
			foogalleryPopulateMetadataForm();
		});

		$('.foogallery-datasource-instagram-container').on('click', '.foogallery-server-image-metadata-form-button-save', function(e) {
			e.preventDefault();
			$('.foogallery-server-image-metadata-form').removeClass('shown');
			//set the metadata back to the image
			foogallerySaveMetadata();
			document.$selectedMetadataItem = null;
		});

		$('.foogallery-datasource-instagram-container').on('click', '.foogallery-server-image-metadata-save', function(e) {
			e.preventDefault();

			$(this).after('<span class="is-active spinner"></span>');

			var json = { "items" : [] };

			$('.foogallery-server-image-list li').each( function() {
				var $this = $(this),
					hasMissingMetadata = $this.hasClass('has_missing_metadata'),
					$img = $this.find('img');

				if ( !hasMissingMetadata ) {
					json.items.push({
						"file"         : $img.data('file'),
						"caption"      : $img.data('caption'),
						"description"  : $img.data('description'),
						"alt"          : $img.data('alt'),
						"custom_url"   : $img.data('custom-url'),
						"custom_target": $img.data('custom-target')
					});
				} else {
					//we need to store something so that the sort order is kept!
					json.items.push({
						"file"         : $img.data('file'),
						"missing"	   : true
					});
				}
			});

			document.foogalleryImageMetadata = json;
			var instagram = $('.foogallery-datasource-instagram-selected').text();
			foogalleryRefreshDatasourceinstagramContainer(instagram);
			document.foogalleryImageMetadata = null;
		});

		$('.foogallery-datasource-instagram-container').on('click', '.foogallery-server-image-metadata-clear', function(e) {
			e.preventDefault();

			if ( confirm('Are you sure? All metadata for this instagram will be cleared!' ) ) {

				$(this).after('<span class="is-active spinner"></span>');

				document.foogalleryClearImageMetadata = true;
				var instagram = $('.foogallery-datasource-instagram-selected').text();
				foogalleryRefreshDatasourceinstagramContainer(instagram);
				document.foogalleryClearImageMetadata = null;
			}
		});
	});

	function foogalleryRefreshDatasourceinstagramContainer( instagram ) {
		var metadata = $('input:radio[name="foogallery-datasource-instagram-metadata"]:checked').val(),
			$container = $('.foogallery-datasource-instagram-container');

		//set the selection
		document.foogallery_datasource_value_temp = {
			"value" : instagram,
			"metadata" : metadata
		};

		$('.foogallery-datasource-modal-insert').removeAttr( 'disabled' );

		var data = {
			action: 'foogallery_datasource_instagram_change',
			instagram: encodeURIComponent(instagram),
			metadata: encodeURIComponent(metadata),
			nonce: document.foogalleryDatasourceinstagramNonce
		};

		if ( document.foogalleryImageMetadata ) {
			data.json = document.foogalleryImageMetadata;
		}

		if ( document.foogalleryClearImageMetadata ) {
			data.clear = true;
		}

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(data) {
				$('.foogallery-datasource-instagram-metadata-selector .spinner').removeClass('is-active');
				$container.html(data);
				foogalleryInitSortable();
			}
		});
	}

	function foogalleryInitSortable() {
		$('.foogallery-server-image-list.sortable').sortable({
			items: 'li',
			distance: 10,
			placeholder: 'foogallery-server-image-placeholder',
			update : function() {
				$('.foogallery-server-image-metadata-save').show();
			}
		});
	}

	function foogalleryPopulateMetadataForm() {
		if ( document.$selectedMetadataItem ) {
			var $selectedImg = document.$selectedMetadataItem.find('img:first');
			$('#foogallery-server-image-metadata-form-file').text($selectedImg.data('file'));
			$('#foogallery-server-image-metadata-form-caption').val($selectedImg.data('caption'));
			$('#foogallery-server-image-metadata-form-description').val($selectedImg.data('description'));
			$('#foogallery-server-image-metadata-form-alt').val($selectedImg.data('alt'));
			$('#foogallery-server-image-metadata-form-custom_url').val($selectedImg.data('custom-url'));
			$('#foogallery-server-image-metadata-form-custom_target').val($selectedImg.data('custom-target'));
		}
	}

	function foogallerySaveMetadata() {
		if ( document.$selectedMetadataItem ) {
			var $selectedImg = document.$selectedMetadataItem.find('img:first');
			$selectedImg.data('caption', $('#foogallery-server-image-metadata-form-caption').val());
			$selectedImg.data('description', $('#foogallery-server-image-metadata-form-description').val());
			$selectedImg.data('alt', $('#foogallery-server-image-metadata-form-alt').val());
			$selectedImg.data('custom-url', $('#foogallery-server-image-metadata-form-custom_url').val());
			$selectedImg.data('custom-target', $('#foogallery-server-image-metadata-form-custom_target').val());

			document.$selectedMetadataItem.removeClass('has_missing_metadata');

			$('.foogallery-server-image-metadata-save').show();
		}
	}
});