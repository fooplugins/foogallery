<?php
if ( ! class_exists( 'FooGallery_Sharing_Network_Google' ) ) {

	class FooGallery_Sharing_Network_Google {

		function __construct() {
			add_filter( 'foogallery_sharing_supported_networks', array( $this, 'add_network' ) );
		}

		public function add_network( $networks ) {
			$networks['google'] = array(
				'ua_regex'  => '/(developers|www)\.google.com\/((\+\/web\/|webmasters\/tools\/rich)snippet(s)?|structured-data\/testing-tool)/i',
				'url_format' => 'https://plus.google.com/share?url={share_url}'
			);

			return $networks;
		}
	}

}