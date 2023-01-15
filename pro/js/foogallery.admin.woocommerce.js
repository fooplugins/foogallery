FooGallery.utils.ready(function ($) {

	var $wrapper = $('.foogallery-master-product-modal-wrapper');

	//Launch the Master Product selector Modal
	$('#foogallery_settings').on('click', '.ecommerce-master-product-selector', function (e) {
		e.preventDefault();
		$('.foogallery-master-product-modal-wrapper').show();

		var product_id = $(this).closest('.foogallery_metabox_field-ecommerce_master_product').find('.ecommerce-master-product-input').val();

		foogallery_master_product_modal_load_content(product_id);
	});

	//Close the Modal
	$wrapper.on('click', '.foogallery-master-product-modal-close', function (e) {
		e.preventDefault();
		$('.foogallery-master-product-modal-wrapper').hide();

		//force a preview refresh
		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});

	// Set the chosen master product in the settings.
	$wrapper.on('click', '.foogallery-master-product-modal-set', function (e) {
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
	$wrapper.on('click', '.foogallery-master-product-modal-content-inner li', function (e) {
		e.preventDefault();
		$('.foogallery-master-product-modal-content-inner li.selected').removeClass('selected');
		$(this).addClass('selected');
		$('.foogallery-master-product-modal-details').removeClass('hidden');
		foogallery_master_product_render_details($(this).data('id'));
		$('.foogallery-master-product-modal-set').removeAttr('disabled');
	});

	// Click on the reload button in the title
	$wrapper.on('click', '.foogallery-master-product-modal-reload', function (e) {
		e.preventDefault();
		var product_id = $('.foogallery-master-product-modal-content').data('selected');

		foogallery_master_product_modal_load_content(product_id);
	});

	// Click on the generate button.
	$wrapper.on('click', '.foogallery-master-product-generate', function (e) {
		e.preventDefault();
		var $content = $('.foogallery-master-product-modal-help'),
			$wrapper = $('.foogallery-master-product-modal-wrapper'),
			data = {
				action: 'foogallery_master_product_generate',
				nonce: $wrapper.data('nonce')
			};

		$content.find('.spinner').addClass('is-active');

		//make an ajax call to generate a master product
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(data) {
				if ( data && data.success && data.data.productId ) {
					foogallery_master_product_modal_load_content(data.data.productId);
				}
				$content.find('.spinner').removeClass('is-active');
			}
		});
	});

	//make and ajax call to load the list of products that can be selected as the master product.
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

	//make an ajax call to get the details for the selected product
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
