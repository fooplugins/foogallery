<?php
if ( ! class_exists( 'FooGallery_Sharing_Network_Reddit' ) ) {

	class FooGallery_Sharing_Network_Reddit {

		function __construct() {
			add_filter( 'foogallery_sharing_supported_networks', array( $this, 'add_network' ) );
		}

		public function add_network( $networks ) {
			$networks['reddit'] = array(
				'url_format' => 'http://reddit.com/submit?url={share_url}&title={title}'
			);

			return $networks;
		}
	}
}