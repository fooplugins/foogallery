<?php
if ( ! class_exists( 'FooGallery_Sharing_Network_Linkedin' ) ) {

	class FooGallery_Sharing_Network_Linkedin {
		function __construct() {
			add_filter( 'foogallery_sharing_supported_networks', array( $this, 'add_network' ) );
		}

		public function add_network( $networks ) {
			$networks['linked_in'] = array(
				'ua_regex'  => '/LinkedInBot/i',
				'url_format' => 'https://linkedin.com/shareArticle?mini=true&url={share_url}&title={title}&summary={description}'
			);

			return $networks;
		}
	}

}