<?php

if ( !class_exists( 'FooGallery_Extension_API' ) ) {

	define('FOOGALLERY_EXTENSIONS_ENDPOINT', 'https://raw.githubusercontent.com/fooplugins/foogallery-extensions/master/README.md');

	class FooGallery_Extension_API {

		public $error_message = false;
		public $extensions = array();

		function get_all() {
			$extensions[] = array(
				'slug'                 => 'albums',
				'title'                => 'Albums',
				'description'          => __( 'Group your galleries into albums. ' ),
				'author'               => 'FooPlugins',
				'author_url'           => 'http://fooplugins.com',
				'thumbnail'            => FOOGALLERY_URL . 'templates/default/thumb.png',
				'tags'                 => array('free', 'functionality'),
				'activated_by_default' => false
			);

			return $extensions;
		}

		private function load_extensions() {
			if ( false === ( $this->extensions = get_transient( 'foogallery_extensions' ) ) ) {
				// It wasn't there, so fetch the data and save the transient
				$response = wp_remote_get( FOOGALLERY_EXTENSIONS_ENDPOINT );

				if( is_wp_error( $response ) ) {
					$this->error_message = $response->get_error_message();
				} else {
					$this->extensions = @json_decode( $response['body'], true );
					set_transient( 'foogallery_extensions', $this->extensions );
				}
			}
		}
	}

}