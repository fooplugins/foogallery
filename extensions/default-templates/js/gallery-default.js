/**
 * FooGallery Default Responsive Gallery Template Init Code.
 * Only shows the images when all images are finished loading
 */

/**
 * Small ready function to circumvent external errors blocking jQuery's ready.
 * @param {Function} func - The function to call when the document is ready.
 * @see http://www.dustindiaz.com/smallest-domready-ever
 */
function FooGallery_Default_Ready(func) {
    /in/.test(document.readyState) ? setTimeout('FooGallery_Default_Ready(' + func + ')', 9) : func()
}

FooGallery_Default_Ready(function () {
    jQuery('.foogallery-default').each(function() {
        var $gallery = jQuery(this);
        $gallery.imagesLoaded( function() {
            $gallery.removeClass('foogallery-default-loading');
        });
    });
});