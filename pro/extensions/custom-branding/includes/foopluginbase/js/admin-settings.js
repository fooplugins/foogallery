(function (FOOPluginBaseAdminSettings, $, undefined) {

	FOOPluginBaseAdminSettings.media_uploader = false;

	FOOPluginBaseAdminSettings.setupImageUploader = function() {
		$('.image-upload-button').on('click', function(e) {
			var $button = $(this),
				linkedToId = $button.data('link'),
				$linkedToInput =  $('#' + linkedToId);

			e.preventDefault();

			//if the media frame already exists, reopen it.
			if ( FOOPluginBaseAdminSettings.media_uploader !== false ) {
				// Open frame
				FOOPluginBaseAdminSettings.media_uploader.open();
				return;
			}

			// Create the media frame.
			FOOPluginBaseAdminSettings.media_uploader = wp.media.frames.file_frame = wp.media({
				title: $button.data( 'uploader-title' ),
				button: {
					text: $button.data( 'uploader-button-text' )
				},
				multiple: false  // Set to true to allow multiple files to be selected
			});

			// When an image is selected, run a callback.
			FOOPluginBaseAdminSettings.media_uploader
				.on( 'select', function() {
					var attachments = FOOPluginBaseAdminSettings.media_uploader.state().get('selection').toJSON();

					$.each(attachments, function(i, item) {
						var attachment = {
							id : item.id,
							width: item.sizes.thumbnail.width,
							height: item.sizes.thumbnail.height,
							src: item.sizes.thumbnail.url
						};

						//do something with the attachment
						$linkedToInput.val( attachment.src );
					});
				})
				.on( 'open', function() {
					if ($linkedToInput.val().length > 0) {
						var selection = FOOPluginBaseAdminSettings.media_uploader.state().get('selection');
						selection.set();    //clear any previous selections
						var url = $linkedToInput.val(),
							attachment = wp.media.attachment();
						attachment.fetch();
						selection.add( attachment ? [ attachment ] : [] );
					}
				});

			// Finally, open the modal
			FOOPluginBaseAdminSettings.media_uploader.open();
		});
	};

	FOOPluginBaseAdminSettings.setupTabs = function() {

		$(".foo-nav-tabs a.nav-tab").click( function(e) {
			e.preventDefault();

			$this = $(this);

			$this.parents(".nav-tab-wrapper:first").find(".nav-tab-active").removeClass("nav-tab-active");
			$this.addClass("nav-tab-active");

			$(".nav-container:visible").hide();

			var hash = $this.attr("href");

			$(hash+'_tab').show();

			//fix the referer so if changes are saved, we come back to the same tab
			var referer = $("input[name=_wp_http_referer]").val();
			if (referer.indexOf("#") >= 0) {
				referer = referer.substr(0, referer.indexOf("#"));
			}
			referer += hash;

			window.location.hash = hash;

			$("input[name=_wp_http_referer]").val(referer);
		});

		if (window.location.hash) {
			$('a.nav-tab[href="' + window.location.hash + '"]').click();
		}

	}; //End of setupTabs


	FOOPluginBaseAdminSettings.ready = function () {
		FOOPluginBaseAdminSettings.setupTabs();
		FOOPluginBaseAdminSettings.setupImageUploader();
    };
}(window.FOOPluginBaseAdminSettings = window.FOOPluginBaseAdminSettings || {}, jQuery));

jQuery(function () {
	FOOPluginBaseAdminSettings.ready();

});




