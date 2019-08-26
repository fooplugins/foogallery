/* Manage media javascript */
jQuery(function ($) {
	$('.foogallery-datasource-folder').on('click', 'button.remove', function (e) {
		e.preventDefault();

		//hide the previous info
		$(this).parents('.foogallery-datasource-folder').hide();

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

	$('.foogallery-datasource-folder').on('click', 'button.edit', function (e) {
		e.preventDefault();

		//show the modal
		$('.foogallery-datasources-modal-wrapper').show();

		//select the folders datasource
		$('.foogallery-datasource-modal-selector[data-datasource="folders"]').click();
	});

	$(document).on('foogallery-datasource-changed', function(e, activeDatasource) {
		$('.foogallery-datasource-folder').hide();

		if ( activeDatasource !== 'folders' ) {
			//clear the selected folder
		}
	});

	$(document).on('foogallery-datasource-changed-folders', function() {
		var $container = $('.foogallery-datasource-folder');

		$('#_foogallery_datasource_value').val(JSON.stringify(document.foogallery_datasource_value_temp));

		$container.find('.foogallery-items-html').html(document.foogallery_datasource_value_temp.value);

		$container.show();

		FOOGALLERY.showHiddenAreas( false );

		$('.foogallery-attachments-list').addClass('hidden');

		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});
});

/* Modal javascript */
jQuery(function ($) {

	$(document).on('foogallery-datasource-content-loaded-folders', function () {
		foogalleryInitSortable();

		$('input:radio[name=foogallery-datasource-folder-metadata]').change(function(e) {
			e.preventDefault();

			$('.foogallery-datasource-folder-metadata-selector .spinner').addClass('is-active');

			var folder = $('.foogallery-datasource-folder-selected').text();
			foogalleryRefreshDatasourceFolderContainer(folder);
		});

		$('.foogallery-datasource-folder-container').on('click', '.foogallery-datasource-folder-list ul li a', function (e) {
			e.preventDefault();

			var $this = $(this),
				folder = $this.data('folder');

			$this.append('<span class="is-active spinner"></span>');

			$('.foogallery-datasource-folder-selected').text(folder);

			foogalleryRefreshDatasourceFolderContainer(folder);
		});

		$('.foogallery-datasource-folder-container').on('click', '.foogallery-server-image-list-edit', function(e) {
			e.preventDefault();

			document.$selectedMetadataItem = $(this).parents('li:first');

			$('.foogallery-server-image-metadata-form').addClass('shown');

			//populate the form with the metadata from the image
			foogalleryPopulateMetadataForm();
		});

		$('.foogallery-datasource-folder-container').on('click', '.foogallery-server-image-metadata-form-button-cancel', function(e) {
			e.preventDefault();
			document.$selectedMetadataItem = null;
			$('.foogallery-server-image-metadata-form').removeClass('shown');
		});

		$('.foogallery-datasource-folder-container').on('click', '.foogallery-server-image-metadata-form-button-next', function(e) {
			e.preventDefault();
			foogallerySaveMetadata();
			document.$selectedMetadataItem = document.$selectedMetadataItem.next();
			foogalleryPopulateMetadataForm();
		});

		$('.foogallery-datasource-folder-container').on('click', '.foogallery-server-image-metadata-form-button-save', function(e) {
			e.preventDefault();
			$('.foogallery-server-image-metadata-form').removeClass('shown');
			//set the metadata back to the image
			foogallerySaveMetadata();
			document.$selectedMetadataItem = null;
		});

		$('.foogallery-datasource-folder-container').on('click', '.foogallery-server-image-metadata-save', function(e) {
			e.preventDefault();

			$(this).after('<span class="is-active spinner"></span>');

			var json = { "items" : [] };

			$('.foogallery-server-image-list li:not(.has_missing_metadata) img').each( function() {
				var $this = $(this);
				json.items.push({
					"file": $this.data('file'),
					"caption": $this.data('caption'),
					"description": $this.data('description'),
					"alt": $this.data('alt'),
					"custom_url": $this.data('custom-url'),
					"custom_target": $this.data('custom-target')
				});
			});

			document.foogalleryImageMetadata = json;
			var folder = $('.foogallery-datasource-folder-selected').text();
			foogalleryRefreshDatasourceFolderContainer(folder);
			document.foogalleryImageMetadata = null;
		});
	});

	function foogalleryRefreshDatasourceFolderContainer( folder ) {
		var metadata = $('input:radio[name="foogallery-datasource-folder-metadata"]:checked').val(),
			$container = $('.foogallery-datasource-folder-container');

		//set the selection
		document.foogallery_datasource_value_temp = {
			"value" : folder,
			"metadata" : metadata
		};

		$('.foogallery-datasource-modal-insert').removeAttr( 'disabled' );

		var data = {
			action: 'foogallery_datasource_folder_change',
			folder: encodeURIComponent(folder),
			metadata: encodeURIComponent(metadata),
			nonce: document.foogalleryDatasourceFolderNonce
		};

		if ( document.foogalleryImageMetadata ) {
			data.json = document.foogalleryImageMetadata;
		}

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(data) {
				$('.foogallery-datasource-folder-metadata-selector .spinner').removeClass('is-active');
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