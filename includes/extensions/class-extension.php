<?php
/**
 * FooGallery Extension Class that holds all information about an extension
 *
 * Date: 19/03/2017
 */
if ( ! class_exists( 'FooGallery_Extension' ) ) {

	class FooGallery_Extension extends stdClass {

		/**
		 * private constructor
		 *
		 * @param array $array
		 */
		private function __construct( $array = null ) {
			if ( $array !== null ) {
				$this->load( $array );
			}
		}

		private function convertToObject( $array, $parent = null ) {
			$object = ( null === $parent ) ? $this : new stdClass();
			foreach ( $array as $key => $value ) {
				if ( is_array( $value ) ) {
					$value = convertToObject( $value, $object );
				}
				$object->$key = $value;
			}
			return $object;
		}

		function load( $array ) {
			$this->convertToObject( $array );
//			'slug' => 'foobox',
//				'class' => 'FooGallery_FooBox_Extension',
//				'categories' => array( 'Featured', 'Premium' ),
//				'file' => 'foobox.php',
//				'title' => 'FooBox PRO',
//				'description' => 'The best lightbox for WordPress just got even better!',
//				'price' => '$27',
//				'author' => 'FooPlugins',
//				'author_url' => 'https://fooplugins.com',
//				'thumbnail' => '/assets/extension_bg.png',
//				'tags' => array( 'premium', 'lightbox', ),
//				'source' => 'fooplugins',
//				'download_button' =>
//					array(
//						'text' => 'Buy - $27',
//						'target' => '_blank',
//						'href' => 'https://fooplugins.com/plugins/foobox',
//						'confirm' => false,
//					),
//				'activated_by_default' => true,
//				'minimum_version' => '2.3.2',
		}
	}
}