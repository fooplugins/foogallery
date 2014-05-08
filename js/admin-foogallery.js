(function (FOOGALLERY, $, undefined) {

    FOOGALLERY.media_uploader = false;
    FOOGALLERY.previous_post_id = 0;
    FOOGALLERY.attachments = new Array();
    FOOGALLERY.selected_attachment_id = 0;

    FOOGALLERY.calculateAttachmentIds = function() {
        var sorted = new Array();
        $('.foogallery-attachments-list li:not(.add-attachment)').each(function() {
            sorted.push( $(this).data('attachment-id') );
        });

        $('#foogallery_attachments').val( sorted.join(',') );
    };

    FOOGALLERY.initAttachments = function() {
        var attachments = $('#foogallery_attachments').val();
		if (attachments) {
			FOOGALLERY.attachments = $.map(attachments.split(','), function (value) {
				return parseInt(value, 10);
			});
		}
    };

	FOOGALLERY.initSettings = function() {
		$('#FooGallerySettings_GalleryTemplate').change(function() {
			var selectedTemplate = $(this).val();
			//hide all template fields
			$('.foogallery-metabox-settings .gallery_template_field').hide();
			//show all fields for the selected template only
			$('.foogallery-metabox-settings .gallery_template_field-' + selectedTemplate).show();
		});

	};

    FOOGALLERY.addAttachmentToGalleryList = function(attachment) {

        if ($.inArray(attachment.id, FOOGALLERY.attachments) !== -1) return;

        var $template = $($('#foogallery-attachment-template').val());

        $template.data('attachment-id', attachment.id);

        $template.find('img')
            .attr('src', attachment.src)
            .attr('width', attachment.width)
            .attr('height', attachment.height);

        $('.foogallery-attachments-list .add-attachment').before($template);

        FOOGALLERY.attachments.push( attachment.id );

        FOOGALLERY.calculateAttachmentIds();
    };

    FOOGALLERY.removeAttachmentFromGalleryList = function(id) {
        var index = $.inArray(id, FOOGALLERY.attachments);
        if (index !== -1) {
            FOOGALLERY.attachments.splice(index, 1);
        }
		$('[data-attachment-id="' + id + '"').remove();

        FOOGALLERY.calculateAttachmentIds();
    };

	FOOGALLERY.showAttachmentInfoModal = function(id) {
		FOOGALLERY.selected_attachment_id = id;
		$('.upload_image_button').click();
	};

    FOOGALLERY.adminReady = function () {
        $('.upload_image_button').on('click', function(e) {
            e.preventDefault();

            //if the media frame already exists, reopen it.
            if ( FOOGALLERY.media_uploader !== false ) {
                // Open frame
                FOOGALLERY.media_uploader.open();
                return;
            }

            // Create the media frame.
            FOOGALLERY.media_uploader = wp.media.frames.file_frame = wp.media({
                title: $(this).data( 'uploader-title' ),
                button: {
                    text: $(this).data( 'uploader-button-text' )
                },
                multiple: true  // Set to true to allow multiple files to be selected
            });

            // When an image is selected, run a callback.
            FOOGALLERY.media_uploader
                .on( 'select', function() {
                    var attachments = FOOGALLERY.media_uploader.state().get('selection').toJSON();

                    $.each(attachments, function(i, item) {
                        var attachment = {
                            id : item.id,
                            width: item.sizes.thumbnail.width,
                            height: item.sizes.thumbnail.height,
                            src: item.sizes.thumbnail.url
                        };

                        FOOGALLERY.addAttachmentToGalleryList(attachment);
                    });
                })
                .on( 'open', function() {
                    if (FOOGALLERY.selected_attachment_id > 0) {
                        var selection = FOOGALLERY.media_uploader.state().get('selection');
                        selection.set();    //clear any previos selections
                        var attachment = wp.media.attachment(FOOGALLERY.selected_attachment_id);
                        attachment.fetch();
                        selection.add( attachment ? [ attachment ] : [] );
                    }
                    FOOGALLERY.selected_attachment_id = 0;
                });

            // Finally, open the modal
            FOOGALLERY.media_uploader.open();
        });

        FOOGALLERY.initAttachments();

		FOOGALLERY.initSettings();

        $('.foogallery-attachments-list')
            .on('click' ,'a.remove', function() {
                var $selected = $(this).parents('li:first'),
					attachment_id = $selected.data('attachment-id');
                FOOGALLERY.removeAttachmentFromGalleryList(attachment_id);
            })
			.on('click' ,'a.info', function() {
				var $selected = $(this).parents('li:first'),
					attachment_id = $selected.data('attachment-id');
				FOOGALLERY.showAttachmentInfoModal(attachment_id);
			})
            .sortable({
                items: 'li:not(.add-attachment)',
                distance: 10,
                placeholder: 'attachment placeholder',
                stop : function() {
                    FOOGALLERY.calculateAttachmentIds();
                }
            });


    };

}(window.FOOGALLERY = window.FOOGALLERY || {}, jQuery));

jQuery(function ($) {
	if ($('#foogallery_attachments').length > 0) {
		FOOGALLERY.adminReady();
	}
});




