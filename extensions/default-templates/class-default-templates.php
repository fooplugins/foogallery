<?php
/**
 * Class to include and init default templates.
 * The templates are no longer an extension and are built in and included by default
 */

if ( ! class_exists( 'FooGallery_Default_Templates' ) ) {

    define( 'FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL', plugin_dir_url( __FILE__ ) );
    define( 'FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_PATH', plugin_dir_path( __FILE__ ) );

    define( 'FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL', FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_URL . 'shared/' );
    define( 'FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_PATH', FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_PATH . 'shared/' );

    require_once( FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_PATH . 'functions.php' );
    require_once( FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_PATH . 'default/class-default-gallery-template.php' );
    require_once( FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_PATH . 'image-viewer/class-image-viewer-gallery-template.php' );
    require_once( FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_PATH . 'justified/class-justified-gallery-template.php' );
    require_once( FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_PATH . 'masonry/class-masonry-gallery-template.php' );
    require_once( FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_PATH . 'simple-portfolio/class-simple-portfolio-gallery-template.php' );
    require_once( FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_PATH . 'thumbnail/class-thumbnail-gallery-template.php' );

    class FooGallery_Default_Templates {

        function __construct() {
            new FooGallery_Default_Gallery_Template();
            new FooGallery_Image_Viewer_Gallery_Template();
            new FooGallery_Justified_Gallery_Template();
            new FooGallery_Masonry_Gallery_Template();
            new FooGallery_Simple_Portfolio_Gallery_Template();
            new FooGallery_Thumbnail_Gallery_Template();

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        }

        /***
         * Enqueue the assets needed by the default templates
         * @param $hook_suffix
         */
        function enqueue_assets( $hook_suffix ){
            if( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {
                $screen = get_current_screen();

                if ( is_object( $screen ) && FOOGALLERY_CPT_GALLERY == $screen->post_type ){

                    // Register, enqueue scripts and styles here
                    wp_enqueue_style( 'foogallery-core-admin-settings', FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL  . 'css/admin-foogallery.css', array(), FOOGALLERY_VERSION );
                }
            }
        }
    }
}
