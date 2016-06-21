/**
 * FooGallery Masonry Init Code.
 * Only initializes masonry when all images are loaded
 */

/**
 * Small ready function to circumvent external errors blocking jQuery's ready.
 * @param {Function} callback - The function to call when the document is ready.
 */
function FooGallery_Masonry_Ready(callback) {
    if (Function('/*@cc_on return true@*/')() ? document.readyState === "complete" : document.readyState !== "loading") callback($);
    else setTimeout(function () { FooGallery_Masonry_Ready(callback); }, 1);
}

FooGallery_Masonry_Ready(function () {
    jQuery('.foogallery-masonry').each(function() {
        var $gallery = jQuery(this);
        $gallery.imagesLoaded( function() {
            $gallery.removeClass('foogallery-masonry-loading').masonry( $gallery.data('masonry-options') );

            //force a resize event so certain themes can update their layout
            if (window.fireEvent && document.createEventObject) window.fireEvent('onresize', document.createEventObject());
            else if (window.dispatchEvent) window.dispatchEvent(new Event('resize'));
        });
    });
});