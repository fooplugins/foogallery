<?php
if ( ! class_exists( 'FooGallery_Sharing_Network_Pinterest' ) ) {

	class FooGallery_Sharing_Network_Pinterest {
		function __construct() {
			add_filter( 'foogallery_sharing_supported_networks', array( $this, 'add_network' ) );
			add_filter( 'foogallery_sharing_generate_share_url-pinterest', array( $this, 'add_query_args' ) );
		}

		public function add_network( $networks ) {
			$networks['pinterest'] = array(
				'ua_regex'  => '/Pinterest/i',
				'url_format' => 'https://pinterest.com/pin/create/bookmarklet/?media={content_url}&url={share_url}&is_video={is_video}&description={title}'
			);

			return $networks;
		}

		public function add_query_args( $query_args ) {
			$query_args['is_video'] = $query_args['content_type'] === 'video';
			return $query_args;
		}
	}

}