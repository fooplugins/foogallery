jQuery(function ($) {

    $('.foogallery-container').each(function() {
        var $video_gallery = $(this),
            fooVideoPlayer;
        if( $( '[data-foo-video-type="youtube"]' ).length ){
            // has you tube videos.
            var tag = document.createElement('script');

            tag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            onYouTubeIframeAPIReady = function(){};
            onPlayerReady = function(event) {
               event.target.playVideo();
            }

            var done = false;
            onPlayerStateChange = function(event) {
                if (event.data == YT.PlayerState.PLAYING && !done) {
                    setTimeout(stopVideo, 6000);
                    done = true;
                }
            }
        }
        var fooVideoPlayer;
        if ( 'function' == typeof  $video_gallery.imagesLoaded ) {
            $video_gallery.imagesLoaded( function() {
                fooVideo();
            });
        }else{
            fooVideo();
        }


        function fooVideo() {
            // setup events
            $video_gallery.on('foobox.afterResize', function(e) {
                if( typeof fooVideoPlayer !== 'undefined' && typeof fooVideoPlayer.destroy !== 'function' ){ // only for video, youtube does it already.
                    fooVideoPlayer.width( e.fb.size.width ).height( e.fb.size.height );
                }
            });

            $video_gallery.on('foobox.close foobox.next foobox.previous', function(e) {
                if( fooVideoPlayer ){
                    if( typeof fooVideoPlayer.destroy === 'function' ){
                        fooVideoPlayer.destroy();
                    }else{
                        fooVideoPlayer.parent().css('overflow', '');
                        fooVideoPlayer.remove();
                    }
                }
            });
            // trigger a new player
            $video_gallery.on('foobox.afterLoad', function(e) {
                var item = e.fb.item,
                    parent = item.element.parent();
                if( ! item.element.hasClass('foo-video') ){
                    return;
                }
                var videoID = item.element.data( 'foo-video-id' );
                var type = item.element.data('foo-video-type' );
                var currentSpot = $('.fbx-item-current > img');
                if( type === 'youtube' ){
                    fooVideoPlayer = new YT.Player( currentSpot[0], {
                        videoId: videoID,
                        events: {
                            'onReady': onPlayerReady,
                            'onStateChange': onPlayerStateChange
                        }
                    } );
                }
                if( type === 'vimeo' ){
                    fooVideoPlayer = $('<iframe src="https://player.vimeo.com/video/' + videoID + '?autoplay=1&color=000000&title=0&byline=0&portrait=0" style="overflow:hidden;" width="100%" height="' + currentSpot.innerHeight() + '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>');
                    currentSpot.parent().css('overflow', 'hidden');
                    currentSpot.replaceWith( fooVideoPlayer );
                }
            });
        }

    });

    // default handler
    $( document ).on('click', '.foo-video:not(.fbx-link)', function( e ){
        var clicked = $(this),
            type = clicked.data( 'foo-video-type' ),
            videoID = clicked.data( 'foo-video-id' );
        if (type && videoID) {
            e.preventDefault();

            if( type === 'youtube' ){
                document.location = 'https://www.youtube.com/watch?v=' + videoID;
            } else if( type === 'vimeo' ){
                document.location = 'https://vimeo.com/' + videoID;
            }
        }
    });
});
