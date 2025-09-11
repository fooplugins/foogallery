( function( $ ) {
    /**
     * @param $scope The Widget wrapper element as a jQuery element
     * @param $ The jQuery alias
     */
    var FooGalleryWidgetHandler = function( $scope, $ ) {
        if ( FooGallery ) {
            FooGallery.load();
        }
    };

    // Make sure you run this code under Elementor.
    $( window ).on( 'elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction( 'frontend/element_ready/foogallery.default', FooGalleryWidgetHandler );
    } );

    // Helper: pull gallery_id from whatever model shape Elementor gives us.
    function getGalleryIdFromModel(model) {
        if (!model) return null;

        // Elementor >=3.x
        if (typeof model.getSetting === 'function') {
            return model.getSetting('gallery_id');
        }

        // Fallbacks for older shapes
        const settings = model.get && model.get('settings');
        if (settings && typeof settings.get === 'function') return settings.get('gallery_id');
        return settings && settings.gallery_id ? settings.gallery_id : null;
    }

    function openEdit(galleryId) {
        if (!galleryId) {
            return;
        }
        window.open(FooGalleryElementor.editUrlBase + galleryId, '_blank');
    }

    function openCreate() {
        window.open(FooGalleryElementor.newUrl, '_blank');
    }

    // Wait until the editor is ready, then bind listeners.
    $( window ).on( 'elementor/frontend/init', function() {
        const channel = elementor.channels.editor;

        // Fired by your BUTTON controls' "event" property.
        channel.on('foogallery:edit', function (panel, data) {
            // Try to get the current element model from event payload, then fallbacks.
            const galleryId = (window.elementor?.getPanelView?.()?.getCurrentPageView?.()?.model?.getSetting?.('gallery_id')) ?? null;
            // if galleryId is not null
            if ( galleryId ) {
                openEdit(galleryId);
            }
        });

        channel.on('foogallery:add', function () {
            openCreate();
        });
    });

} )( jQuery );