/**
 * Coming soon!
 */



// Extension Details view
// Set ups a modal overlay with the expanded extension data
FOOGALLERY.view.Details = wp.Backbone.View.extend({

	// Wrap extension data on a div.extension element
	className: 'extension-overlay',

	events: {
		'click': 'collapse',
		'click .delete-extension': 'deleteExtension',
		'click .left': 'previousExtension',
		'click .right': 'nextExtension'
	},

	// The HTML template for the extension overlay
	html: extensions.template( 'extension-single' ),

	render: function() {
		var data = this.model.toJSON();
		this.$el.html( this.html( data ) );
		// Renders active extension styles
		this.activeExtension();
		// Set up navigation events
		this.navigation();
		// Checks screenshot size
		this.screenshotCheck( this.$el );
		// Contain "tabbing" inside the overlay
		this.containFocus( this.$el );
	},

	// Adds a class to the currently active extension
	// and to the overlay in detailed view mode
	activeExtension: function() {
		// Check the model has the active property
		this.$el.toggleClass( 'active', this.model.get( 'active' ) );
	},

	// Keeps :focus within the extension details elements
	containFocus: function( $el ) {
		var $target;

		// Move focus to the primary action
		_.delay( function() {
			$( '.extension-wrap a.button-primary:visible' ).focus();
		}, 500 );

		$el.on( 'keydown.wp-extensions', function( event ) {

			// Tab key
			if ( event.which === 9 ) {
				$target = $( event.target );

				// Keep focus within the overlay by making the last link on extension actions
				// switch focus to button.left on tabbing and vice versa
				if ( $target.is( 'button.left' ) && event.shiftKey ) {
					$el.find( '.extension-actions a:last-child' ).focus();
					event.preventDefault();
				} else if ( $target.is( '.extension-actions a:last-child' ) ) {
					$el.find( 'button.left' ).focus();
					event.preventDefault();
				}
			}
		});
	},

	// Single extension overlay screen
	// It's shown when clicking a extension
	collapse: function( event ) {
		var self = this,
			scroll;

		event = event || window.event;

		// Prevent collapsing detailed view when there is only one extension available
		if ( extensions.data.extensions.length === 1 ) {
			return;
		}

		// Detect if the click is inside the overlay
		// and don't close it unless the target was
		// the div.back button
		if ( $( event.target ).is( '.extension-backdrop' ) || $( event.target ).is( '.close' ) || event.keyCode === 27 ) {

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
				extensions.router.navigate( extensions.router.baseUrl( '' ) );

				// Restore scroll position
				document.body.scrollTop = scroll;

				// Return focus to the extension div
				if ( extensions.focusedExtension ) {
					extensions.focusedExtension.focus();
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
	// the extension details overlay
	closeOverlay: function() {
		$( 'body' ).removeClass( 'extension-overlay-open' );
		this.remove();
		this.unbind();
		this.trigger( 'extension:collapse' );
	},

	nextExtension: function() {
		var self = this;
		self.trigger( 'extension:next', self.model.cid );
		return false;
	},

	previousExtension: function() {
		var self = this;
		self.trigger( 'extension:previous', self.model.cid );
		return false;
	},

	// Checks if the extension screenshot is the old 300px width version
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