<?php
if ( ! class_exists( 'FooGallery_Sharing_Network_Buffer' ) ) {

	class FooGallery_Sharing_Network_Buffer {

		function __construct() {
			add_filter( 'foogallery_sharing_supported_networks', array( $this, 'add_network' ) );
		}

		public function add_network( $networks ) {
			$networks['buffer'] = array(
				'url_format' => 'http://bufferapp.com/add?text={title}&url={share_url}'
			);

			return $networks;
		}
	}
}