/**
 * FooGallery Masonry Init Code.
 * Only initializes masonry when all images are loaded
 */
jQuery(function ($) {
    $('.foogallery-masonry').each(function() {
        var $gallery = $(this);
        $gallery.imagesLoaded( function() {
            $gallery.removeClass('foogallery-masonry-loading').masonry( $gallery.data('masonry-options') );
        });
    });
});