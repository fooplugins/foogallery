jQuery(function ($) {

    /* Manage media javascript */
    $('.foogallery-datasource-post_query').on('click', 'button.remove', function (e) {
        e.preventDefault();

        //hide the previous info
        $(this).parents('.foogallery-datasource-post_query').hide();

        //clear the datasource value
        $('#_foogallery_datasource_value').val('');

        //clear the datasource
        $('#foogallery_datasource').val('');

        //make sure the modal insert button is not active
        $('.foogallery-datasource-modal-insert').attr('disabled', 'disabled');

        FOOGALLERY.showHiddenAreas(true);

        //ensure the preview will be refreshed
        $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
    });

    $('.foogallery-datasource-post_query').on('click', 'button.edit', function (e) {
        e.preventDefault();

        //show the modal
        $('.foogallery-datasources-modal-wrapper').show();

        //select the post_query datasource
        $('.foogallery-datasource-modal-selector[data-datasource="post_query"]').click();
    });

    $(document).on('foogallery-datasource-changed', function (e, activeDatasource) {
        $('.foogallery-datasource-post_query').hide();

        if (activeDatasource !== 'post_query') {
            //clear the selected post_query
        }
    });

    $(document).on('foogallery-datasource-changed-post_query', function () {
        var $container = $('.foogallery-datasource-post_query');

        //build up the datasource_value
        var value = {
            "gallery_post_type": $('#gallery_post_type').val(),
            "no_of_post": $('#no_of_post').val(),
            "exclude": $('#exclude').val(),
            "link_to": $(".link_to:checked").val()
        };

        //save the datasource_value
        $('#_foogallery_datasource_value').val(JSON.stringify(value));

        $container.show();

        FOOGALLERY.showHiddenAreas(false);

        $('.foogallery-attachments-list').addClass('hidden');

        $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
    });


});