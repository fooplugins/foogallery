/**
 * FooGallery Default Responsive Gallery Template Init Code.
 * Only shows the images when all images are finished loading
 */

/**
 * Small ready function to circumvent external errors blocking jQuery's ready.
 * @param {Function} callback - The function to call when the document is ready.
 */
function FooGallery_Default_Ready(callback) {
    if (Function('/*@cc_on return true@*/')() ? document.readyState === "complete" : document.readyState !== "loading") callback($);
    else setTimeout(function () { FooGallery_Default_Ready(callback); }, 1);
}

FooGallery_Default_Ready(function () {
    var galleries = document.querySelectorAll('.foogallery-default-loading'),
        isElement = function(obj){
            return typeof HTMLElement === 'object' ? obj instanceof HTMLElement : obj && typeof obj === 'object' && obj !== null && obj.nodeType === 1 && typeof obj.nodeName === 'string';
        },
        removeClass = function (elements, className) {
            className = className || '';
            var p = className.split(' '), _remove = function(el, classes) {
                for (var i = 0, len = classes.length; i < len; i++) {
                    el.className = el.className.replace(new RegExp('(\\s|^)' + classes[i] + '(\\s|$)'), ' ').replace(/^\s+|\s+$/g, '');
                }
            };
            if (elements.length) {
                for (var i = 0, len = elements.length; i < len; i++) {
                    _remove(elements[i], p);
                }
            } else if (isElement(elements)) {
                _remove(elements, p);
            }
        };
    if (typeof(imagesLoaded) != 'undefined') {
        imagesLoaded(galleries, function () {
            removeClass(galleries, 'foogallery-default-loading');

            //force a resize event so certain themes can update their layout
            if (window.fireEvent && document.createEventObject) window.fireEvent('onresize', document.createEventObject());
            else if (window.dispatchEvent) window.dispatchEvent(new Event('resize'));
        });
    }
});