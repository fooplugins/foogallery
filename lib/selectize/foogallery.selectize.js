var FOOGALLERY_SELECTIZE = function(element) {
	var $selectize = jQuery(element),
		taxonomy = $selectize.data('taxonomy'),
		taxonomy_data = window.FOOGALLERY_TAXONOMY_DATA[taxonomy],
		options = [];

	taxonomy_data.terms.forEach(function(term) {
		options.push({
			value: term.slug,
			text: term.name,
			parents: term.parents
		});
	});

	//add a class to the table row so that it can be styled correctly
	$selectize.closest('tr').addClass('compat-field-selectize');

	$selectize.selectize({
		plugins: ['remove_button'],
		options: options,
		placeholder: taxonomy_data.labels.placeholder,
		delimiter: ', ',
		createOnBlur: true,
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
					nonce: window.FOOGALLERY_TAXONOMY_DATA['nonce'],
					taxonomy: taxonomy,
					term_label: input
				},
				success: function(response) {
					$wrapper.removeClass('loading');
					try {
                        window.FOOGALLERY_TAXONOMY_DATA[taxonomy].terms = response.all_terms;
					} catch(e) {};

					if (typeof response.new_term !== 'undefined') {
						callback({
							value: response.new_term.slug,
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
		},
		onBlur : function() {
			if ( $selectize.data('original-value') === this.getValue() ) {
				//no change - no save
				return;
			}
			//only save if the value has changed
			this.close();
			var $wrapper = this.$wrapper;
			$wrapper.addClass('loading');
            jQuery.ajax({
                url: ajaxurl,
                cache: false,
                type: 'POST',
                data: {
                    action: 'foogallery-taxonomies-save-terms',
                    nonce: window.FOOGALLERY_TAXONOMY_DATA['nonce'],
                    taxonomy: taxonomy,
                    terms: $selectize.val(),
					attachment_id: $selectize.data('attachment_id')
                },
                success: function(response) {
					$wrapper.removeClass('loading');
					if (response) {
						$selectize.data('original-value', response);
					}
                }
            });
		}
	});
};