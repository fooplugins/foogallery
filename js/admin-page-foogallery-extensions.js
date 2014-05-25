(function(FOOGALLERY, $, undefined) {
	FOOGALLERY.extension_search_timer;
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

	//show the spinner when performing actions
	FOOGALLERY.bindActionButtons = function() {
		$('.extension-actions a').not('a.download').on('click', function() {
			$(this).parent().find('.spinner').show();
		});

		$('.extension-actions a.download').on('click', function(e) {
			var confirmMsg = $(this).data('confirm');

			if (confirmMsg) {
				if (confirm('Are you sure you want to download this extension?')) {
					$(this).parent().find('.spinner').show();
				} else {
					e.preventDefault();
					return false;
				}
			}
		})
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
		})
	};

	FOOGALLERY.bindTabs = function() {
		$("a.nav-tab").click( function(e) {
			$this = $(this);

			$this.parents(".nav-tab-wrapper:first").find(".nav-tab-active").removeClass("nav-tab-active");
			$this.addClass("nav-tab-active");

			var filter = $this.attr('href').replace('#', '');
			$('.foogallery-extension-browser .extensions .extension').hide();
			$('.foogallery-extension-browser .extensions .' + filter).show();
		});

		if (window.location.hash) {
			$('a.nav-tab[href="' + window.location.hash + '"]').click();
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


// Theme Details view
// Set ups a modal overlay with the expanded theme data
FOOGALLERY.view.Details = wp.Backbone.View.extend({

	// Wrap theme data on a div.theme element
	className: 'theme-overlay',

	events: {
		'click': 'collapse',
		'click .delete-theme': 'deleteTheme',
		'click .left': 'previousTheme',
		'click .right': 'nextTheme'
	},

	// The HTML template for the theme overlay
	html: themes.template( 'theme-single' ),

	render: function() {
		var data = this.model.toJSON();
		this.$el.html( this.html( data ) );
		// Renders active theme styles
		this.activeTheme();
		// Set up navigation events
		this.navigation();
		// Checks screenshot size
		this.screenshotCheck( this.$el );
		// Contain "tabbing" inside the overlay
		this.containFocus( this.$el );
	},

	// Adds a class to the currently active theme
	// and to the overlay in detailed view mode
	activeTheme: function() {
		// Check the model has the active property
		this.$el.toggleClass( 'active', this.model.get( 'active' ) );
	},

	// Keeps :focus within the theme details elements
	containFocus: function( $el ) {
		var $target;

		// Move focus to the primary action
		_.delay( function() {
			$( '.theme-wrap a.button-primary:visible' ).focus();
		}, 500 );

		$el.on( 'keydown.wp-themes', function( event ) {

			// Tab key
			if ( event.which === 9 ) {
				$target = $( event.target );

				// Keep focus within the overlay by making the last link on theme actions
				// switch focus to button.left on tabbing and vice versa
				if ( $target.is( 'button.left' ) && event.shiftKey ) {
					$el.find( '.theme-actions a:last-child' ).focus();
					event.preventDefault();
				} else if ( $target.is( '.theme-actions a:last-child' ) ) {
					$el.find( 'button.left' ).focus();
					event.preventDefault();
				}
			}
		});
	},

	// Single theme overlay screen
	// It's shown when clicking a theme
	collapse: function( event ) {
		var self = this,
			scroll;

		event = event || window.event;

		// Prevent collapsing detailed view when there is only one theme available
		if ( themes.data.themes.length === 1 ) {
			return;
		}

		// Detect if the click is inside the overlay
		// and don't close it unless the target was
		// the div.back button
		if ( $( event.target ).is( '.theme-backdrop' ) || $( event.target ).is( '.close' ) || event.keyCode === 27 ) {

			// Add a temporary closing class while overlay fades out
			$( 'body' ).addClass( 'closing-overlay' );

			// With a quick fade out animation
			this.$el.fadeOut( 130, function() {
				// Clicking outside the modal box closes the overlay
				$( 'body' ).removeClass( 'closing-overlay' );
				// Handle event cleanup
				self.closeOverlay();

				// Get scroll position to avoid jumping to the top
				scroll = document.body.scrollTop;

				// Clean the url structure
				themes.router.navigate( themes.router.baseUrl( '' ) );

				// Restore scroll position
				document.body.scrollTop = scroll;

				// Return focus to the theme div
				if ( themes.focusedTheme ) {
					themes.focusedTheme.focus();
				}
			});
		}
	},

	// Handles .disabled classes for next/previous buttons
	navigation: function() {

		// Disable Left/Right when at the start or end of the collection
		if ( this.model.cid === this.model.collection.at(0).cid ) {
			this.$el.find( '.left' ).addClass( 'disabled' );
		}
		if ( this.model.cid === this.model.collection.at( this.model.collection.length - 1 ).cid ) {
			this.$el.find( '.right' ).addClass( 'disabled' );
		}
	},

	// Performs the actions to effectively close
	// the theme details overlay
	closeOverlay: function() {
		$( 'body' ).removeClass( 'theme-overlay-open' );
		this.remove();
		this.unbind();
		this.trigger( 'theme:collapse' );
	},

	// Confirmation dialog for deleting a theme
	deleteTheme: function() {
		return confirm( themes.data.settings.confirmDelete );
	},

	nextTheme: function() {
		var self = this;
		self.trigger( 'theme:next', self.model.cid );
		return false;
	},

	previousTheme: function() {
		var self = this;
		self.trigger( 'theme:previous', self.model.cid );
		return false;
	},

	// Checks if the theme screenshot is the old 300px width version
	// and adds a corresponding class if it's true
	screenshotCheck: function( el ) {
		var screenshot, image;

		screenshot = el.find( '.screenshot img' );
		image = new Image();
		image.src = screenshot.attr( 'src' );

		// Width check
		if ( image.width && image.width <= 300 ) {
			el.addClass( 'small-screenshot' );
		}
	}
});