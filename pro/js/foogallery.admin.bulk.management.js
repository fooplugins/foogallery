FooGallery.utils.ready(function ($) {

	//Launch the Bulk Management Modal
	$('.foogallery-attachments-list-bar').on('click', '.bulk_media_management', function (e) {
		e.preventDefault();
		$('.foogallery-bulk-management-modal-wrapper').show();

		foogallery_bulk_management_modal_load_content();
	});

	//Close the Modal
	$('.foogallery-bulk-management-modal-wrapper').on('click', '.foogallery-bulk-management-modal-close', function (e) {
		e.preventDefault();
		$('.foogallery-bulk-management-modal-wrapper').hide();

		//force a preview refresh
		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});

	//Choose another taxonomy from the SELECT in the title
	$('.foogallery-bulk-management-modal-wrapper').on('change', '.foogallery-bulk-management-select-taxonomy', function (e) {
		e.preventDefault();

		foogallery_bulk_management_modal_load_content();
	});

	//Click on the reload button in the title
	$('.foogallery-bulk-management-modal-wrapper').on('click', '.foogallery-bulk-management-modal-reload', function (e) {
		e.preventDefault();

		foogallery_bulk_management_modal_load_content();
	});

	//Click on a gallery item
	$('.foogallery-bulk-management-modal-container').on('click', '.foogallery-bulk-management-modal-content ul li', function (e) {
		e.preventDefault();

		$(this).toggleClass('selected');

		foogallery_bulk_management_select_items();
	});

	//Clear the selection of items
	$('.foogallery-bulk-management-modal-container').on('click', '.foogallery-bulk-management-modal-action-clear', function(e) {
		e.preventDefault();

		//unselect all selected items
		$('.foogallery-bulk-management-modal-content ul li.selected').click();
	});

	//Click the assign button
	$('.foogallery-bulk-management-modal-container').on('click', '.foogallery-bulk-management-modal-action-assign', function(e) {
		e.preventDefault();

		foogallery_bulk_management_perform_assignment($(this).data('nonce'));
	});

	//select a term above the gallery items
	$('.foogallery-bulk-management-modal-container').on('click', '.foogallery-bulk-management-select-term', function(e) {
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

	//update the selected items count
	function foogallery_bulk_management_select_items() {
		var selectedItemCount = $('.foogallery-bulk-management-modal-content ul li.selected').length,
			$selected = $('.foogallery-bulk-management-modal-selected');

		$selected.find('strong').html(selectedItemCount).end();

		$('.foogallery-bulk-management-modal-action-clear').toggle(selectedItemCount > 0);
	}

	//make ajax call to do bulk assignments
	function foogallery_bulk_management_perform_assignment(nonce) {
		var itemsToAdd = [],
			itemsToRemove = [],
			taxonomies = [],
			taxonomyData = '',
			$wrapper = $('.foogallery-bulk-management-modal-wrapper');

		//get selected items
		$('.foogallery-bulk-management-modal-content ul li.selected').each(function() {
			if ( $(this).data('attachment-id') ) {
				itemsToAdd.push( $(this).data('attachment-id') );
			}
		});

		//get removed items
		$('.foogallery-bulk-management-modal-content ul li.term:not(.selected)').each(function() {
			if ( $(this).data('attachment-id') ) {
				itemsToRemove.push( $(this).data('attachment-id') );
			}
		});

		//get selected taxonomies
		$('.foogallery-bulk-management-selectize.selectized').each( function(e) {
			var $this = $(this),
				taxonomy = $this.data('taxonomy'),
				value = $this.val();

			if ( value ) {
				taxonomies.push(taxonomy);
				taxonomyData += '&taxonomy_data_' + taxonomy + '=' + value;
			}
		});

		var data = 'action=foogallery_bulk_management_assign' +
			'&foogallery_id=' + $wrapper.data('foogalleryid') +
			'&nonce=' + nonce +
			'&attachments=' + itemsToAdd.join(',') +
			'&attachments_remove=' + itemsToRemove.join(',') +
			'&taxonomies=' + taxonomies.join(',') +
			taxonomyData;

		$('.foogallery-bulk-management-modal-actions .spinner').show();
		$('.foogallery-bulk-management-modal-action-message').hide();

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(data) {
				$('.foogallery-bulk-management-modal-actions .spinner').hide();
				$('.foogallery-bulk-management-modal-action-message').html(data).show();
			}
		});
	}

	//load the terms and gallery items content
	function foogallery_bulk_management_modal_load_content() {
		var $content = $('.foogallery-bulk-management-modal-container'),
			$wrapper = $('.foogallery-bulk-management-modal-wrapper'),
			attachments = $('#foogallery_attachments').val(),
			$select = $('.foogallery-bulk-management-select-taxonomy'),
			data = 'action=foogallery_bulk_management_content' +
				'&foogallery_id=' + $wrapper.data('foogalleryid') +
				'&nonce=' + $wrapper.data('nonce') +
				'&taxonomy=' + $select.val() +
				'&attachments=' + attachments;

		$content.addClass('not-loaded').html('<div class="spinner is-active"></div>');

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(data) {
				$('.foogallery-bulk-management-modal-reload').show();

				$content.html(data);

				foogallery_bulk_management_modal_load_sidebar();

				//lazy loading of images on the gallery edit page
				var io = new IntersectionObserver(function(entries){
					entries.forEach(function(entry){
						if (entry.isIntersecting){
							var $target = $(entry.target);
							$target.attr("src", $target.data("src"));
							io.unobserve(entry.target);
						}
					});
				}, {
					root: $(".foogallery-bulk-management-modal-content ul").get(0)
				});

				$(".foogallery-bulk-management-modal-content ul li img").each(function(i, img){
					io.observe(img);
				});
			}
		});
	}

	//load the sidebar
	function foogallery_bulk_management_modal_load_sidebar() {
		$('.foogallery-bulk-management-selectize').each( function(e) {
			var $selectize = $(this),
				taxonomy = $selectize.data('taxonomy'),
				taxonomy_data = $selectize.data('taxonomy-data'),
				options = [];

			taxonomy_data.terms.forEach(function(term) {
				options.push({
					value: term.term_id,
					text: term.name,
					parents: term.parents
				});
			});

			$selectize.selectize({
				plugins: ['remove_button'],
				options: options,
				placeholder: taxonomy_data.labels.placeholder,
				delimiter: ',',
				closeAfterSelect : true,
				preload: 'focus',
				create: function(input, callback) {
					this.close();
					var $wrapper = this.$wrapper;
					$wrapper.addClass('loading');
					jQuery.ajax({
						url: ajaxurl,
						cache: false,
						type: 'POST',
						data: {
							action: 'foogallery-taxonomies-add-term',
							nonce: taxonomy_data['nonce'],
							taxonomy: taxonomy,
							term_label: input
						},
						success: function(response) {
							$wrapper.removeClass('loading');
							try {
								taxonomy_data.terms = response.all_terms;
							} catch(e) {};

							if (typeof response.new_term !== 'undefined') {
								callback({
									value: response.new_term.term_id,
									text: response.new_term.name
								});
							} else {
								callback(false);
							}
						}
					});
				},
				render: {
					option_create: function(data, escape) {
						return '<div class="create">' + taxonomy_data.labels.add + ': <strong>' + escape(data.input) + '</strong></div>';
					},
					option: function(data, escape) {
						var label = (data.parents.length ? '<span class="parent-label">' + escape(data.parents.join(' / ')) + ' /</span> ' : '') + escape(data.text);

						return '<div>' + label + '</div>';
					},
					item: function(data, escape) {
						var isNewTerm = typeof data.parents === 'undefined';

						if(isNewTerm) {
							data.parents = [];
						}

						var sortStringArray = data.parents.slice(0);
						sortStringArray.push(data.text);
						var sort_string = sortStringArray.join('-').toLowerCase();
						var label = (data.parents.length ? '<span class="parent-label">' + escape(data.parents.join(' / ')) + ' /</span> ' : '') + escape(data.text);

						return '<div data-sort-string="' + escape(sort_string) + '">' + label + '</div>';
					}
				},
				onItemAdd: function(value, $element) {
					$element.parent().children(':not(input)').sort(function(a, b) {
						var upA = jQuery(a).attr('data-sort-string');
						var upB = jQuery(b).attr('data-sort-string');
						return (upA < upB) ? -1 : (upA > upB) ? 1 : 0;
					}).removeClass('active').insertBefore($element.parent().children('input'));
				}
			});
		});
	}

	//select all gallery items by a specific term
	function foogallery_bulk_management_select_by_term() {
		var $selected = $('.foogallery-bulk-management-select-term.button-primary'),
			selected_term_id = $selected.data('term-id');

		//first remove term and selected class from all items
		$('.foogallery-bulk-management-modal-content ul li').removeClass('term selected');

		if ( selected_term_id ) {
			//then loop through and add term class if the term exists for the item
			$('.foogallery-bulk-management-modal-content ul li').each(function (e) {
				var $this = $(this),
					terms = $this.data('terms');

				terms.forEach(function (item, index) {
					if (item.id === selected_term_id) {
						$this.addClass('term');
					}
				});
			});

			//then select items with the term class
			$('.foogallery-bulk-management-modal-content ul li.term').addClass('selected');
		}

		foogallery_bulk_management_select_items();
	}
});
