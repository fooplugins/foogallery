(function(FOOGALLERY, $, undefined) {
	FOOGALLERY.extension_search_timer = false;
	FOOGALLERY.extension_search_timer_delay = 300; // 0.6 seconds delay after last input

	//hook up clicking the tag links above the extensions
	FOOGALLERY.bindTagLinks = function() {
		$('.extension-controls a.extension-filter').on('click', function() {
			var filter = $(this).attr('href').replace('#', '');
			$('.extension-controls a.extension-filter').removeClass('current');
			$(this).addClass('current');
			$('.foogallery-extension-browser .extensions .extension').hide();
			$('.foogallery-extension-browser .extensions .' + filter).show();
		});
	};

	FOOGALLERY.showSpinner = function($btn) {
		var $container = $btn.parents('.extension:first');
		$container.addClass('updating');
		$container.find('.banner:first').html($btn.data('banner-text'));
	};

	//show the spinner when performing actions
	FOOGALLERY.bindActionButtons = function() {
		$('a.ext_action').on('click', function(e) {
			var $btn = $(this);

			//if the target is blank then allow and get out
			if ($btn.attr('target') == '_blank') {
				return true;
			}

			//if its disabled then do nothing!
			if ($btn.is('.disabled')) {
				e.preventDefault();
				return false;
			}

			var confirmMsg = $(this).data('confirm');

			if (confirmMsg) {
				if (confirm(confirmMsg)) {
					FOOGALLERY.showSpinner($btn);
					$btn.addClass('disabled');
				} else {
					e.preventDefault();
					return false;
				}
			} else {
				//otherwise just show the spinner while redirecting
				FOOGALLERY.showSpinner($btn);
				$btn.addClass('disabled');
			}
		});
	};

	//perform the extensions search
	FOOGALLERY.doSearch = function() {
		var search = $('#extensions-search-input').val().toLowerCase();
		$('.foogallery-extension-browser .extensions .extension').hide();
		$('.foogallery-extension-browser .extensions .search-me').each(function() {
			var html = $(this).html();
			if (html && html.toLowerCase().indexOf(search) > -1) {
				$(this).parents('.extension:first').show();
			}
		});
	};

	//hook up the extensions search
	FOOGALLERY.bindSearch = function() {
		$('#extensions-search-input').bind('input', function() {
			window.clearTimeout(FOOGALLERY.extension_search_timer);
			FOOGALLERY.extension_search_timer = window.setTimeout(function(){
				FOOGALLERY.doSearch();
			}, FOOGALLERY.extension_search_timer_delay);
		});
	};

	FOOGALLERY.bindTabs = function() {
		$("a.nav-tab").click( function(e) {
			$this = $(this);

			$this.parents(".nav-tab-wrapper:first").find(".nav-tab-active").removeClass("nav-tab-active");
			$this.addClass("nav-tab-active");

			var filter = $this.attr('href').replace('#', '');
			$('.foogallery-extension-browser .extensions .extension').hide();
			$('.foogallery-extension-browser .extension-page').hide();
			$('.foogallery-extension-browser .extensions .' + filter).show();
			$('.foogallery-extension-browser .extension-page-' + filter).show();
		});

		if (window.location.hash) {
			$('a.nav-tab[href="' + window.location.hash + '"]').click();
		} else {
			$('a.nav-tab-all').click();
		}

		return false;
	};

	$(function() { //wait for ready
		FOOGALLERY.bindTabs();
		FOOGALLERY.bindTagLinks();
		FOOGALLERY.bindActionButtons();
		FOOGALLERY.bindSearch();
	});

}(window.FOOGALLERY = window.FOOGALLERY || {}, jQuery));