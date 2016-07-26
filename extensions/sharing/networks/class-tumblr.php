<?php
if ( ! class_exists( 'FooGallery_Sharing_Network_Digg' ) ) {

	class FooGallery_Sharing_Network_Digg {

		function __construct() {
			add_filter( 'foogallery_sharing_supported_networks', array( $this, 'add_network' ) );
		}

		public function add_network( $networks ) {
			$networks['tumblr'] = array(
				'url_format' => 'https://www.tumblr.com/widgets/share/tool?canonicalUrl={share_url}&title={title}&caption={description}'
			);

			return $networks;
		}
	}
}