FooGallery.utils.ready(function ($) {

    // Enable the modal OK button when one of the fields changes.
    $(document).on('change', '.foogallery_opensea_input', function () {
        $('.foogallery-datasource-modal-insert').removeAttr('disabled');
    });

    $(document).on('foogallery-datasource-changed-opensea', function () {
        var $container = $('.foogallery-datasource-opensea');

        //build up the datasource_value
        var value = {
            "owner": $('#foogallery_opensea_owner').val(),
            "token_ids": $('#foogallery_opensea_token_ids').val(),
            "order_direction": $(".foogallery_opensea_input.order_direction:checked").val(),
            "order_by": $(".foogallery_opensea_input.order_by:checked").val()
        };

        //save the datasource_value
        $('#_foogallery_datasource_value').val(JSON.stringify(value));

        //set the values we selected in the datasource info html
        $('#foogallery-datasource-opensea-owner').html( value.owner );
        $('#foogallery-datasource-opensea-token-ids').html( value.token_ids );
        $('#foogallery-datasource-opensea-order-by').html( value.order_by );
        $('#foogallery-datasource-opensea-order-direction').html( value.order_direction );

        // Show the OpenSea datasource info
        $container.show();

        //show the correct stuff within the Gallery Items metabox
        FOOGALLERY.showHiddenAreas(false);

        //hide the attachment list
        $('.foogallery-attachments-list-container').addClass('hidden');

        //force the gallery to refresh when next previewed
        $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
    });

    /* Manage media javascript */
    $('.foogallery-datasource-opensea').on('click', 'button.remove', function (e) {
        e.preventDefault();

        //hide the previous info
        $(this).parents('.foogallery-datasource-opensea').hide();

        //clear the datasource value
        $('#_foogallery_datasource_value').val('');

        //clear the datasource
        $('#foogallery_datasource').val('');

        //clear the values in the form.
        $('#foogallery_opensea_owner').val('');
        $('#foogallery_opensea_token_ids').val('');
        $(".foogallery_opensea_input.order_direction:checked").prop('checked', false);
        $(".foogallery_opensea_input.order_by:checked").prop('checked', false);

        //make sure the modal insert button is not active
        $('.foogallery-datasource-modal-insert').attr('disabled', 'disabled');

        // Hide and show the stuff.
        FOOGALLERY.showHiddenAreas(true);

        //ensure the preview will be refreshed
        $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
    });

    $('.foogallery-datasource-opensea').on('click', 'button.edit', function (e) {
        e.preventDefault();

        //show the modal
        $('.foogallery-datasources-modal-wrapper').show();

        //select the post_query datasource
        $('.foogallery-datasource-modal-selector[data-datasource="opensea"]').click();
    });
});