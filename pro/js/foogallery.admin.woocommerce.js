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

	$('.foogallery-master-product-modal-wrapper').on('click', '.foogallery-master-product-modal-set', function (e) {
		e.preventDefault();
		var product_id = $('.foogallery-master-product-modal-content-inner li.selected').data('id');
		$('.foogallery-tab-active .ecommerce-master-product-input').val(product_id);

		$('.foogallery-master-product-modal-wrapper').hide();

		//force a preview refresh
		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});

	$('.foogallery-master-product-modal-wrapper').on('click', '.foogallery-master-product-modal-content-inner li', function (e) {
		e.preventDefault();
		$('.foogallery-master-product-modal-content-inner li.selected').removeClass('selected');
		$(this).addClass('selected');
	});

	// Click on the reload button in the title
	$('.foogallery-master-product-modal-wrapper').on('click', '.foogallery-master-product-modal-reload', function (e) {
		e.preventDefault();
		var product_id = $('.foogallery-master-product-modal-content').data('selected');

		foogallery_master_product_modal_load_content(product_id);
	});

	//load the terms and gallery items content
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
});
