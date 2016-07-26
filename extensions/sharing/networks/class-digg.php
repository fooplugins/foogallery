<?php
if ( ! class_exists( 'FooGallery_Sharing_Network_Digg' ) ) {

	class FooGallery_Sharing_Network_Digg {

		function __construct() {
			add_filter( 'foogallery_sharing_supported_networks', array( $this, 'add_network' ) );
		}

		public function add_network( $networks ) {
			$networks['digg'] = array(
				'url_format' => 'http://digg.com/submit?url={share_url}&title={title}'
			);

			return $networks;
		}
	}
}