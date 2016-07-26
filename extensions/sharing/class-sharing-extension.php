<?php
/**
 * FooGallery Social Sharing Extension
 * Date: 26/07/2016
 */
if ( ! class_exists( 'FooGallery_Sharing_Extension' ) ) {

	define( 'FOOGALLERY_SHARING_PATH', plugin_dir_path( __FILE__ ) );
	define( 'FOOGALLERY_SHARING_URL', plugin_dir_url( __FILE__ ) );
	define( 'FOOGALLERY_SHARING_PARAM', 'fooshare' );

	class FooGallery_Sharing_Extension {

		function __construct() {
			require_once( FOOGALLERY_SHARING_PATH . 'functions.php' );
			$this->setup_networks();

			if ( is_admin() ) {
				//add some global settings for albums
				add_filter( 'foogallery_admin_settings_override', array($this, 'add_sharing_settings' ) );
			} else {
//				new FooGallery_Sharing_Crawlers();
//				new FooGallery_Sharing_Networks();
			}

			add_action( 'template_redirect', array($this, 'listen_for_sharing') );
			add_action( 'template_redirect', array($this, 'listen_for_crawler') );

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

		function listen_for_sharing() {
			global $wp_query;

			//make sure we are dealing with a share
			if ( empty($wp_query->query_vars[FOOGALLERY_SHARING_PARAM]) ) {
				return;
			}



		}

		function listen_for_crawler() {
			global $wp_query;

			//make sure we are dealing with a share
			if ( empty($wp_query->query_vars[FOOGALLERY_SHARING_PARAM]) ) {
				return;
			}

			if ( strtolower( $_SERVER['REQUEST_METHOD'] ) === 'get' ) { // crawlers only make GET requests
				foreach ( foogallery_sharing_supported_networks() as $name => $attributes ) {
					if ( array_key_exists( 'ua_regex', $attributes ) ) {
						//check for a user agent match
						if ( preg_match( $attributes['ua_regex'], $_SERVER['HTTP_USER_AGENT'] ) ) {
							// then check if an id can be extracted from the request
							$id = foogallery_sharing_extract_share_request();
							if ( $id !== false ) {
								do_action( 'foogallery_sharing_crawler_handle_request', $id, $this );
								return;
							}
						}
					}
				}
			}
		}
	}
}
