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

	$('.foogallery-multi-filtering-modal-wrapper').on('click', '.foogallery-multi-filtering-modal-set', function (e) {
		e.preventDefault();

		//build up the levels data
		var $levels = $('.foogallery-multi-filtering-modal-content-level'),
			levels = [];

		//clear the table rows
		$('.filtering-multi-table tbody tr').remove();

		$levels.each(function(index) {
			var $level = $(this),
				$selected = $level.find('.foogallery-multi-filtering-select-term.button-primary'),
				allText = $level.find('input').val(),
				level = {
					all: allText,
					tags: []
				},
				tableHtml = '<tr><td>' + (index + 1) + '</td><td>' + allText + '</td><td>';

			$selected.each(function() {
				if ( level.tags.length > 0 ) {
					tableHtml += ', ';
				}
				var tag = $(this).html() + '';
				level.tags.push( tag );
				tableHtml += tag;
			});

			tableHtml += '</td>';

			$(tableHtml).appendTo( $('.filtering-multi-table tbody') );
			$('.filtering-multi-table').show();

			levels.push(level);
		});

		$('.filtering-multi-input:enabled').val( JSON.stringify( levels ) );

		$('.foogallery-multi-filtering-modal-wrapper').hide();

		//force a preview refresh
		$('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
	});


	//Click on the reload button in the title
	$('.foogallery-multi-filtering-modal-wrapper').on('click', '.foogallery-multi-filtering-modal-reload', function (e) {
		e.preventDefault();

		foogallery_multi_filtering_modal_load_content();
	});

	//select a term for a level
	$('.foogallery-multi-filtering-modal-container').on('click', '.foogallery-multi-filtering-select-term', function(e) {
		e.preventDefault();

		var $this = $(this);

		//select the one that was clicked
		$this.toggleClass('button-primary');

		foogallery_multi_filtering_modal_hide_terms();
	});

	//add a new level
	$('.foogallery-multi-filtering-modal-container').on('click', '.foogallery-multi-filtering-add-level', function(e) {
		e.preventDefault();

		var $levels = $('.foogallery-multi-filtering-modal-content-level'),
			$level = $('.foogallery-multi-filtering-modal-content-level-template').clone();

		$level
			.removeClass('foogallery-multi-filtering-modal-content-level-template')
			.addClass('foogallery-multi-filtering-modal-content-level')
			.find('.foogallery-multi-filtering-modal-content-level-count')
				.html( $levels.length + 1);

		$level.find('ul').sortable({ items: 'li' });

		$level.insertBefore($(this));

		foogallery_multi_filtering_modal_hide_terms();
	});

	//remove a level
	$('.foogallery-multi-filtering-modal-container').on('click', '.foogallery-multi-filtering-modal-content-level-remove', function(e) {
		e.preventDefault();

		$(this).closest('.foogallery-multi-filtering-modal-content-level').remove();

		foogallery_multi_filtering_modal_hide_terms();

		var $levels = $('.foogallery-multi-filtering-modal-content-level');
		$levels.each(function(index) {
			$(this).find('.foogallery-multi-filtering-modal-content-level-count')
				.html( index + 1);
		});
	});

	//load the terms and gallery items content
	function foogallery_multi_filtering_modal_load_content() {
		var $content = $('.foogallery-multi-filtering-modal-container'),
			$wrapper = $('.foogallery-multi-filtering-modal-wrapper')
			data = {
				action: 'foogallery_multi_filtering_content',
				foogallery_id: $wrapper.data('foogalleryid'),
				nonce: $wrapper.data('nonce'),
				levels: JSON.parse( $('.filtering-multi-input:enabled').val() || '{}' )
			};

		$content.addClass('not-loaded').html('<div class="spinner is-active"></div>');

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(data) {
				$('.foogallery-multi-filtering-modal-reload').show();
				$content.html(data);
				foogallery_multi_filtering_modal_hide_terms();
				$('.foogallery-multi-filtering-modal-content-level ul').sortable({ items: 'li' });
			}
		});
	}

	function foogallery_multi_filtering_modal_hide_terms() {
		var $levels = $('.foogallery-multi-filtering-modal-content-level');

		//show all
		$('.foogallery-multi-filtering-select-term').show();

		//loop through the levels and hide selected terms from other levels
		$levels.each(function() {
			var $level = $(this),
				$other_levels = $levels.not( this ),
				$selected = $level.find('.foogallery-multi-filtering-select-term.button-primary');

			$selected.each(function() {
				$other_levels.find('[data-term-id="' + $(this).data('term-id') + '"]').hide();
			});
		});
	}
});
