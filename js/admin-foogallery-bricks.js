( function( $ ) {
    'use strict';
    
    // Helper function to get gallery ID from bricksData
    function getGalleryIdFromBricksData() {
        if ( typeof bricksData !== 'undefined' && bricksData && bricksData.loadData && bricksData.loadData.content ) {
            for ( var i = 0; i < bricksData.loadData.content.length; i++ ) {
                var item = bricksData.loadData.content[i];
                if ( item.name === 'foogallery' && item.settings && item.settings.gallery_id ) {
                    return item.settings.gallery_id;
                }
            }
        }
        return null;
    }

    // Handle add gallery label click
    $( document ).on( 'click', '.theme-bricks label[for="gallery_add"] span', function( e ) {
        e.preventDefault();
        window.open( FooGalleryBricks.newUrl, '_blank' );
    });

    // Handle edit gallery label click
    $( document ).on( 'click', '.theme-bricks label[for="gallery_edit"] span', function( e ) {
        e.preventDefault();
        var galleryId = getGalleryIdFromBricksData();
        if ( galleryId ) {
            window.open( FooGalleryBricks.editUrlBase + galleryId, '_blank' );
        } else {
            alert( 'Please select a gallery first!' );
        }
    });


} )( jQuery );