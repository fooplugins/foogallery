/**
 * FooGallery Default Responsive Gallery Template Init Code.
 * Only shows the images when all images are finished loading
 */
jQuery(function ($) {
    $('.foogallery-default').each(function() {
        var $gallery = $(this);
        $gallery.imagesLoaded( function() {
            $gallery.removeClass('foogallery-default-loading');
        });
    });
});