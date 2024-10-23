jQuery(function ($) {
    // Show the modal when the button is clicked
    $('.gallery_datasources_button').on('click', function(e) {
        e.preventDefault();
        $('.foogallery-datasources-modal-wrapper').show();
    });

    // Hide the modal when the close or cancel buttons are clicked
    $('.foogallery-datasources-modal-wrapper').on('click', '.media-modal-close, .foogallery-datasource-modal-cancel', function(e) {
        $('.foogallery-datasources-modal-wrapper').hide();
    });

    // Capture the selection mode when the radio buttons change
    $('input[name="selection_mode"]').on('change', function () {
        var selectionMode = $('input[name="selection_mode"]:checked').val();
        $('#foogallery_selection_mode').val(selectionMode);  // Update hidden input with the selected mode
    });

    // Capture the selected datasource and the selection mode when the "OK" button is clicked
    $('.foogallery-datasource-modal-insert').on('click', function(e) {
        var activeDatasource = $('.foogallery-datasource-modal-selector.active').data('datasource');
        var selectionMode = $('input[name="selection_mode"]:checked').val();  // Get the selected mode

        // Set the datasource and selection mode in the hidden fields
        $('#foogallery_datasource').val(activeDatasource);
        $('#foogallery_selection_mode').val(selectionMode);

        // Trigger events for other datasources to clean up
        $(document).trigger('foogallery-datasource-changed', activeDatasource);
        $(document).trigger('foogallery-datasource-changed-' + activeDatasource);

        // Hide the datasource modal
        $('.foogallery-datasources-modal-wrapper').hide();
    });

    // Reload the content when the reload button is clicked
    $('.foogallery-datasources-modal-wrapper').on('click', '.foogallery-datasource-modal-reload', function(e) {
        e.preventDefault();

        var $wrapper = $('.foogallery-datasources-modal-wrapper'),
            datasource = $wrapper.data('datasource'),
            $content = $('.foogallery-datasource-modal-container-inner.' + datasource);

        $content.addClass('not-loaded');

        // Force a refresh
        $('.foogallery-datasource-modal-selector.active').click();
    });

    // Load and display the content for the selected datasource
    $('.foogallery-datasource-modal-selector').on('click', function(e) {
        e.preventDefault();

        var datasource = $(this).data('datasource'),
            $content = $('.foogallery-datasource-modal-container-inner.' + datasource),
            $wrapper = $('.foogallery-datasources-modal-wrapper');

        // Set the active class for the selected datasource
        $('.foogallery-datasource-modal-selector').removeClass('active');
        $(this).addClass('active');

        $('.foogallery-datasource-modal-container-inner').hide();
        $content.show();

        var datasource_value = $('#_foogallery_datasource_value').val();

        if ($content.hasClass('not-loaded')) {
            $content.find('.spinner').addClass('is-active');
            $content.removeClass('not-loaded');

            var data = 'action=foogallery_load_datasource_content' +
                '&datasource=' + datasource +
                '&datasource_value=' + encodeURIComponent(datasource_value) +
                '&foogallery_id=' + $wrapper.data('foogalleryid') +
                '&nonce=' + $wrapper.data('nonce');

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function(data) {
                    $('.foogallery-datasource-modal-reload').show();
                    $wrapper.data('datasource', datasource);

                    $content.html(data);
                    // Raise an event so that datasource-specific code can run
                    $(document).trigger('foogallery-datasource-content-loaded-' + datasource);
                }
            });
        }
    });
});
