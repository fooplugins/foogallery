<?php
if ( ! class_exists( 'FooGallery_Sharing_Network_Delicious' ) ) {

	class FooGallery_Sharing_Network_Delicious {

		function __construct() {
			add_filter( 'foogallery_sharing_supported_networks', array( $this, 'add_network' ) );
			add_filter( 'foogallery_sharing_generate_share_url-delicious', array( $this, 'add_query_args' ) );
		}

		public function add_network( $networks ) {
			$networks['delicious'] = array(
				'ua_regex'  => '/Java\/1\.8\.0_40/i',
				'url_format' => 'https://delicious.com/save?v=5&provider={provider}&noui&jump=close&url={share_url}&title={title}'
			);

			return $networks;
		}

		public function add_query_args( $query_args ) {
			$query_args['provider'] = ''; //Not sure if this is needed
			return $query_args;
		}
	}

}