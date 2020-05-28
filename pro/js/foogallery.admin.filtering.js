jQuery(function ($) {

	//Launch the Multi Level filter Modal
	$('#foogallery_settings').on('click', '.filtering-multi-builder', function (e) {
		e.preventDefault();
		$('.foogallery-multi-filtering-modal-wrapper').show();

		foogallery_multi_filtering_modal_load_content();
	});

	//Close the Modal
	$('.foogallery-multi-filtering-modal-wrapper').on('click', '.foogallery-multi-filtering-modal-close', function (e) {
		e.preventDefault();
		$('.foogallery-multi-filtering-modal-wrapper').hide();

		//force a preview refresh
		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});

	//Click on the reload button in the title
	$('.foogallery-multi-filtering-modal-wrapper').on('click', '.foogallery-multi-filtering-modal-reload', function (e) {
		e.preventDefault();

		foogallery_multi_filtering_modal_load_content();
	});

	//select a term above the gallery items
	$('.foogallery-multi-filtering-modal-container').on('click', '.foogallery-bulk-management-select-term', function(e) {
		e.preventDefault();

		var $this = $(this),
			prev_selected_term_id = $('.foogallery-bulk-management-select-term.button-primary').data('term-id'),
			current_selected_term_id = $this.data('term-id')

		//deselect all tags
		$('.foogallery-bulk-management-select-term.button-primary').removeClass('button-primary');

		//select the one that was clicked
		$this.toggleClass('button-primary');

		if ( current_selected_term_id === prev_selected_term_id ) {
			$(this).toggleClass('button-primary');
		} else {
			//set the term in the selectize control
			var $selectize = $('.foogallery-bulk-management-selectize.selectized');
			if ($selectize.length) {
				$selectize[0].selectize.setValue(current_selected_term_id, true);
			}
		}

		foogallery_bulk_management_select_by_term();
	});

	//load the terms and gallery items content
	function foogallery_multi_filtering_modal_load_content() {
		var $content = $('.foogallery-multi-filtering-modal-container'),
			$wrapper = $('.foogallery-multi-filtering-modal-wrapper')
			data = 'action=foogallery-multi-filtering-content' +
				'&foogallery_id=' + $wrapper.data('foogalleryid') +
				'&nonce=' + $wrapper.data('nonce');

		$content.addClass('not-loaded').html('<div class="spinner is-active"></div>');

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(data) {
				$('.foogallery-multi-filtering-modal-reload').show();
				$content.html(data);
			}
		});
	}

	//select all gallery items by a specific term
	function foogallery_multi_filtering_modal_select_by_term() {
		var $selected = $('.foogallery-bulk-management-select-term.button-primary'),
			selected_term_id = $selected.data('term-id');

		//first remove term and selected class from all items
		$('.foogallery-multi-filtering-modal-content ul li').removeClass('term selected');

		if ( selected_term_id ) {
			//then loop through and add term class if the term exists for the item
			$('.foogallery-multi-filtering-modal-content ul li').each(function (e) {
				var $this = $(this),
					terms = $this.data('terms');

				terms.forEach(function (item, index) {
					if (item.id === selected_term_id) {
						$this.addClass('term');
					}
				});
			});

			//then select items with the term class
			$('.foogallery-multi-filtering-modal-content ul li.term').addClass('selected');
		}

		foogallery_bulk_management_select_items();
	}
});
