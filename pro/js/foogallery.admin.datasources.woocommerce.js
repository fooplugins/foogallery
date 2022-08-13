FooGallery.utils.ready(function ($) {

    $(document).on('change', '.foogallery_woocommerce_input', function () {
        $('.foogallery-datasource-modal-insert').removeAttr('disabled');
    });

    /* Manage media javascript */
    $('.foogallery-datasource-woocommerce').on('click', 'button.remove', function (e) {
        e.preventDefault();

        //hide the previous info
        $(this).parents('.foogallery-datasource-woocommerce').hide();

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

    $('.foogallery-datasource-woocommerce').on('click', 'button.edit', function (e) {
        e.preventDefault();

        //show the modal
        $('.foogallery-datasources-modal-wrapper').show();

        //select the woocommerce datasource
        $('.foogallery-datasource-modal-selector[data-datasource="woocommerce"]').click();
    });

    $(document).on('foogallery-datasource-changed', function (e, activeDatasource) {
        $('.foogallery-datasource-woocommerce').hide();

        if (activeDatasource !== 'woocommerce') {
            //clear anything
        }
    });

    $(document).on('click', '.foogallery-datasource-woocommerce-form .foogallery_woocommerce_categories a', function (e) {
        e.preventDefault();
        $(this).toggleClass('button-primary');
        foogallery_woocommerce_set_selected_categories();
    });

    $(document).on('foogallery-datasource-changed-woocommerce', function () {

        var $container = $('.foogallery-datasource-woocommerce'),
            min_price = $('#foogallery_woocommerce_min_price_range').val(),
            max_price = $( '#foogallery_woocommerce_max_price_range' ).val(),
            price_range = 'any';

        if ( $.isNumeric( min_price ) && $.isNumeric( max_price ) ) {
            price_range = min_price + ' - ' + max_price;
        } else {
            min_price = max_price = '';
        }

        //build up the datasource_value
        var value = {
            "no_of_post": $('#foogallery_woocommerce_no_of_post').val(),
            "sort": $(".foogallery_woocommerce_sort:checked").val(),
            "stock": $(".foogallery_woocommerce_stock:checked").val(),
            "exclude": $('#foogallery_woocommerce_exclude').val(),
            "caption_title_source": $(".foogallery_woocommerce_caption_title_source:checked").val(),
            "caption_desc_source": $(".foogallery_woocommerce_caption_desc_source:checked").val(),
            "categories" : document.foogallery_datasource_woocommerce_temp.categories,
            "categories_html" : document.foogallery_datasource_woocommerce_temp.categories_html,
            "min_price_range": min_price,
            "max_price_range": max_price,
        };

        var sort = ( value.sort !== '' ) ? value.sort : 'newest first',
            stock = ( value.stock !== '' ) ? value.stock : 'any',
            no_of_post = ( value.no_of_post !== '' ) ? value.no_of_post : 'unlimited';

        //save the datasource_value
        $('#_foogallery_datasource_value').val(JSON.stringify(value));
        $('#foogallery-datasource-woocommerce-categories').html( value.categories_html );
        $('#foogallery-datasource-woocommerce-sort').html( sort );
        $('#foogallery-datasource-woocommerce-stock').html( stock );
        $('#foogallery-datasource-woocommerce-no_of_post').html( no_of_post );
        $('#foogallery-datasource-woocommerce-price-range').html( price_range );
        $('#foogallery-datasource-woocommerce-exclude').html( value.exclude );
        $('#foogallery-datasource-woocommerce-caption_title_source').html( value.caption_title_source );
        $('#foogallery-datasource-woocommerce-caption_desc_source').html( value.caption_desc_source );

        $container.show();

        FOOGALLERY.showHiddenAreas(false);

        $('.foogallery-attachments-list-container').addClass('hidden');
        $('.foogallery_preview_container').addClass('foogallery-preview-force-refresh');
    });
});

function foogallery_woocommerce_set_selected_categories() {
    var $selected = jQuery('ul.foogallery_woocommerce_categories').find('a.button-primary'),
        taxonomy_values = [],
        html = '';

    jQuery('.foogallery-datasource-modal-insert').removeAttr( 'disabled' );

    //validate if the OK button can be pressed.
    if ( $selected.length > 0 ) {
        $selected.each(function() {
            taxonomy_values.push( jQuery(this).data('termId') );
            html += '<li>' + jQuery(this).text() + '</li>';
        });

    } else {
        html = '';
    }

    //set the selection
    document.foogallery_datasource_woocommerce_temp = {
        "categories" : taxonomy_values,
        "categories_html" : '<ul>' + html + '</ul>'
    };
}