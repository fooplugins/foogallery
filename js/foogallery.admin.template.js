jQuery(document).ready(function($) {
    //append a message to the metabox header.
    var $metabox_heading = $('#foogallery_template .hndle'),
        message = $('.foogallery-template-card-selector').data('metabox-message'),
        $message = $('<div class="foogallery-gallery-template-metabox-message">' + message + '</div>');

    $message.appendTo( $metabox_heading );
    
    // Handle template card selection
    $(".foogallery-template-card").on("click", function() {
        var $card = $(this);
        var template = $card.data("template");
        
        // Update visual selection
        $(".foogallery-template-card").removeClass("selected");
        $card.addClass("selected");
        
        // Update hidden input
        $("#FooGallerySettings_GalleryTemplate").val(template);

        FOOGALLERY.galleryTemplateChanged(true);
    });
});