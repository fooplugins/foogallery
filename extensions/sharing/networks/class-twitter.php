<?php
if ( ! class_exists( 'FooGallery_Sharing_Network_Twitter' ) ) {

	define( 'FOOGALLERY_SHARING_SETTING_TWITTER_SITE', 'sharing_twitter_site' );

	class FooGallery_Sharing_Network_Twitter {

		function __construct() {
			add_filter( 'foogallery_sharing_supported_networks', array( $this, 'add_network' ) );
			add_filter( 'foogallery_sharing_output_meta_tags-twitter', array( $this, 'add_meta' ), 10, 2 );

			if (is_admin()) {
				add_filter( 'foogallery_admin_settings_override', array($this, 'add_sharing_settings' ) );
			}
		}

		public function add_network( $networks ) {
			$networks['twitter'] = array(
				'ua_regex'  => '/twitterbot/i',
				'url_format' => 'https://twitter.com/share?url={share_url}&text={title}'
			);

			return $networks;
		}

		public function map_meta_props( $meta_props ) {
			$meta_props->card = $meta_props->content_type === 'video' ? 'player' : 'photo';
			$meta_props->site = foogallery_get_setting( FOOGALLERY_SHARING_SETTING_TWITTER_SITE );
			return $meta_props;
		}

		function add_sharing_settings( $settings ) {

			$settings['settings'][] = array(
				'id'      => FOOGALLERY_SHARING_SETTING_TWITTER_SITE,
				'title'   => __( 'Twitter Site', 'foogallery' ),
				'type'    => 'text',
				'default' => '',
				'desc'    => __( 'Your site handle that will be used when sharing to Twitter.', 'foogallery' ),
				'tab'     => 'sharing'
			);

			return $settings;
		}

		public function add_meta( $meta_props, $share_info ) {
			$meta_props['name="twitter:card"'] = $meta_props->content_type === 'video' ? 'player' : 'photo';
			$meta_props['name="twitter:site"'] = foogallery_get_setting( FOOGALLERY_SHARING_SETTING_TWITTER_SITE );
			$meta_props['name="twitter:image"'] = $share_info['image'];
			$meta_props['name="twitter:title"'] = $share_info['title'];
			$meta_props['name="twitter:description"'] = $share_info['description'];
			return $meta_props;
		}
	}
}