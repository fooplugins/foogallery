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
    var galleries = document.querySelectorAll('.foogallery-default'),
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
    imagesLoaded(galleries, function() {
        removeClass(galleries, 'foogallery-default-loading');
    });
});