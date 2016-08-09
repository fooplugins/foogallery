<?php
/**
 * FooGallery Social Sharing Extension
 * Date: 26/07/2016
 */
if ( ! class_exists( 'FooGallery_Sharing_Extension' ) ) {

	define( 'FOOGALLERY_SHARING_PATH', plugin_dir_path( __FILE__ ) );
	define( 'FOOGALLERY_SHARING_URL', plugin_dir_url( __FILE__ ) );
	define( 'FOOGALLERY_SHARING_PARAM', 'fooshare' );
    define( 'FOOGALLERY_SHARING_ARG', '__foo');

	class FooGallery_Sharing_Extension {

		function __construct() {
			require_once( FOOGALLERY_SHARING_PATH . 'functions.php' );
			$this->setup_networks();
            $this->setup_listeners();

			if ( is_admin() ) {
				//add some global settings for albums
				add_filter( 'foogallery_admin_settings_override', array($this, 'add_sharing_settings' ) );
			} else {
//				new FooGallery_Sharing_Crawlers();
//				new FooGallery_Sharing_Networks();
			}




//			add_filter( 'foogallery_defaults', array( $this, 'apply_album_defaults' ) );
//			add_action( 'foogallery_extension_activated-albums', array( $this, 'flush_rewrite_rules' ) );
//			add_filter( 'foogallery_alter_album_template_field', array( $this, 'alter_gallery_template_field' ), 10, 2 );
		}

		function setup_networks() {
			//include supported network class files
			require_once( FOOGALLERY_SHARING_PATH . '/networks/class-delicious.php' );
			require_once( FOOGALLERY_SHARING_PATH . '/networks/class-facebook.php' );
			require_once( FOOGALLERY_SHARING_PATH . '/networks/class-google.php' );
			require_once( FOOGALLERY_SHARING_PATH . '/networks/class-linkedin.php' );
			require_once( FOOGALLERY_SHARING_PATH . '/networks/class-pinterest.php' );
			require_once( FOOGALLERY_SHARING_PATH . '/networks/class-twitter.php' );

			//instantiate them all!
			new FooGallery_Sharing_Network_Delicious();
			new FooGallery_Sharing_Network_Facebook();
			new FooGallery_Sharing_Network_Google();
			new FooGallery_Sharing_Network_Linkedin();
			new FooGallery_Sharing_Network_Pinterest();
			new FooGallery_Sharing_Network_Twitter();
		}

		function setup_listeners() {
            require_once( FOOGALLERY_SHARING_PATH . '/listeners/class-listener-sharer.php' );
            require_once( FOOGALLERY_SHARING_PATH . '/listeners/class-listener-crawler.php' );

            new FooGallery_Sharing_Listener_Crawler();
            new FooGallery_Sharing_Listener_Sharer();
        }

		function add_sharing_settings( $settings ) {
			$settings['tabs']['sharing'] = __( 'Social Sharing', 'foogallery' );

			$settings['settings'][] = array(
				'id'      => 'sharing_author',
				'title'   => __( 'Schema.org Author', 'foogallery' ),
				'type'    => 'text',
				'default' => '',
				'desc'    => __( 'An optional author name that is used when generating the schema.org markup.', 'foogallery' ),
				'tab'     => 'sharing'
			);

			return $settings;
		}
	}
}
