<?php
if ( ! class_exists( 'FooGallery_Sharing_Network_Facebook' ) ) {

	define( 'FOOGALLERY_SHARING_SETTING_FACEBOOK_ID', 'sharing_facebook_appid' );

	class FooGallery_Sharing_Network_Facebook {

		function __construct() {
			add_filter( 'foogallery_sharing_supported_networks', array( $this, 'add_network' ) );
			add_filter( 'foogallery_sharing_generate_share_url-facebook', array( $this, 'add_query_args' ) );
			add_filter( 'foogallery_sharing_output_meta_tags-facebook', array( $this, 'add_meta' ), 10, 2 );

			if (is_admin()) {
				add_filter( 'foogallery_admin_settings_override', array($this, 'add_sharing_settings' ) );
			}
		}

		public function add_network( $networks ) {
			$networks['facebook'] = array(
				'ua_regex'   => '/(facebookexternalhit|facebot)/i',
				'url_format' => 'https://www.facebook.com/dialog/share?app_id={app_id}&display=popup&href={share_url}'
			);

			return $networks;
		}

		public function add_query_args( $query_args ) {
			$query_args['app_id'] = foogallery_get_setting( FOOGALLERY_SHARING_SETTING_FACEBOOK_ID );
			return $query_args;
		}

		public function add_meta( $meta_props, $share_info ) {
			$meta_props['property="fb:app_id"'] = foogallery_get_setting( FOOGALLERY_SHARING_SETTING_FACEBOOK_ID );
			return $meta_props;
		}

		function add_sharing_settings( $settings ) {

			$settings['settings'][] = array(
				'id'      => FOOGALLERY_SHARING_SETTING_FACEBOOK_ID,
				'title'   => __( 'Facebook App ID', 'foogallery' ),
				'type'    => 'text',
				'default' => '966242223397117',
				'desc'    => __( 'Provide your own Facebook App ID. The default Facebook App ID is used.', 'foogallery' ),
				'tab'     => 'sharing'
			);

			return $settings;
		}
	}

}