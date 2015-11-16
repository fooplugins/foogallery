/**
 * FooGallery Masonry Init Code.
 * Only initializes masonry when all images are loaded
 */

/**
 * Small ready function to circumvent external errors blocking jQuery's ready.
 * @param {Function} func - The function to call when the document is ready.
 * @see http://www.dustindiaz.com/smallest-domready-ever
 */
function FooGallery_Masonry_Ready(func) {
    /in/.test(document.readyState) ? setTimeout('FooGallery_Masonry_Ready(' + func + ')', 9) : func()
}

FooGallery_Masonry_Ready(function () {
    jQuery('.foogallery-masonry').each(function() {
        var $gallery = jQuery(this);
        $gallery.imagesLoaded( function() {
            $gallery.removeClass('foogallery-masonry-loading').masonry( $gallery.data('masonry-options') );
        });
    });
});