<?php
/**
 * FooGallery Gutenberg Functionality
 * Date: 28/10/2018
 */

require_once FOOGALLERY_PATH . 'gutenberg/class-foogallery-blocks.php';
require_once FOOGALLERY_PATH . 'gutenberg/class-foogallery-rest-routes.php';

if ( ! class_exists( 'FooGallery_Gutenberg' ) ) {

	/**
	 * Class FooGallery_Gutenberg
	 */
	class FooGallery_Gutenberg {

		/**
		 * FooGallery_Gutenberg constructor.
		 */
		public function __construct() {
			new FooGallery_Blocks();
			new FooGallery_Rest_Routes();

			add_filter( 'foogallery_find_galleries_in_post', array( $this, 'find_galleries_in_post' ), 10, 2 );
		}

		/**
		 * Use the built-in Block Parser to find all foogallery blocks in post content.
		 *
		 * @param array   $galleries the galleries found in the post.
		 * @param WP_Post $post the post we are checking.
		 */
		public function find_galleries_in_post( $galleries, $post ) {
			if ( ! class_exists( 'WP_Block_Parser' ) ) {
				return $galleries;
			}

			if ( ! is_object( $post ) ) {
				return $galleries;
			}

			$parser = new WP_Block_Parser();
			$blocks = $parser->parse( $post->post_content );

			foreach ( $blocks as $block ) {
				if ( array_key_exists( 'blockName', $block ) && 'fooplugins/foogallery' === $block['blockName'] ) {
					if ( array_key_exists( 'id', $block['attrs'] ) ) {
						$galleries[] = $block['attrs']['id'];
					}
				}
			}

			return $galleries;
		}
	}
}