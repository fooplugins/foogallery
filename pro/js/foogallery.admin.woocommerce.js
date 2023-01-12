FooGallery.utils.ready(function ($) {

	//Launch the Master Product selector Modal
	$('#foogallery_settings').on('click', '.ecommerce-master-product-selector', function (e) {
		e.preventDefault();
		$('.foogallery-master-product-modal-wrapper').show();

		var product_id = $(this).closest('.foogallery_metabox_field-ecommerce_master_product').find('.ecommerce-master-product-input').val();

		foogallery_master_product_modal_load_content(product_id);
	});

	//Close the Modal
	$('.foogallery-master-product-modal-wrapper').on('click', '.foogallery-master-product-modal-close', function (e) {
		e.preventDefault();
		$('.foogallery-master-product-modal-wrapper').hide();

		//force a preview refresh
		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});

	// Set the chosen master product in the settings.
	$('.foogallery-master-product-modal-wrapper').on('click', '.foogallery-master-product-modal-set', function (e) {
		e.preventDefault();
		var product_id = $('.foogallery-master-product-modal-content-inner li.selected').data('id');
		//set the hidden input
		$('.foogallery-tab-active .ecommerce-master-product-input').val(product_id);
		//set the product details for the selected product.
		$('.foogallery-tab-active .foogallery-master-product-field-container').html($('.foogallery-master-product-modal-details-inner').html());

		$('.foogallery-master-product-modal-wrapper').hide();

		//force a preview refresh
		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});

	// Select a product
	$('.foogallery-master-product-modal-wrapper').on('click', '.foogallery-master-product-modal-content-inner li', function (e) {
		e.preventDefault();
		$('.foogallery-master-product-modal-content-inner li.selected').removeClass('selected');
		$(this).addClass('selected');
		foogallery_master_product_render_details($(this).data('id'));
		$('.foogallery-master-product-modal-set').removeAttr('disabled');
	});

	// Click on the reload button in the title
	$('.foogallery-master-product-modal-wrapper').on('click', '.foogallery-master-product-modal-reload', function (e) {
		e.preventDefault();
		var product_id = $('.foogallery-master-product-modal-content').data('selected');

		foogallery_master_product_modal_load_content(product_id);
	});

	//load the list of products that can be selected as the master product.
	function foogallery_master_product_modal_load_content(product_id) {
		var $content = $('.foogallery-master-product-modal-container'),
			$wrapper = $('.foogallery-master-product-modal-wrapper')
			data = {
				action: 'foogallery_master_product_content',
				foogallery_id: $wrapper.data('foogalleryid'),
				nonce: $wrapper.data('nonce'),
				product_id: product_id
			};

		$content.addClass('not-loaded').html('<div class="spinner is-active"></div>');

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(data) {
				$('.foogallery-master-product-modal-reload').show();
				$content.html(data);
			}
		});
	}

	function foogallery_master_product_render_details(product_id) {
		var $content = $('.foogallery-master-product-modal-details-inner'),
			$wrapper = $('.foogallery-master-product-modal-wrapper')
		data = {
			action: 'foogallery_master_product_details',
			foogallery_id: $wrapper.data('foogalleryid'),
			nonce: $wrapper.data('nonce'),
			product_id: product_id
		};

		$content.addClass('not-loaded').html('<div class="spinner is-active"></div>');

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(data) {
				$content.html(data);
			}
		});
	}
});
