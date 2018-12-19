<?php
/**
 * FooGallery Gutenberg Functionality
 * Date: 28/10/2018
 */

require_once( FOOGALLERY_PATH . 'gutenberg/class-foogallery-blocks.php' );
require_once( FOOGALLERY_PATH . 'gutenberg/class-foogallery-rest-routes.php' );

if ( ! class_exists( 'FooGallery_Gutenberg' ) ) {

	class FooGallery_Gutenberg {

		function __construct() {
			new FooGallery_Blocks();
			new FooGallery_Rest_Routes();

			add_action( 'foogallery_attach_gallery_to_post', array( $this, 'attach_gallery_to_post' ), 10, 2 );
		}

		/**
		 * Use the built-in Block Parser to "attach" a gallery to a post
		 *
		 * @param $post_id
		 * @param $post
		 */
		function attach_gallery_to_post( $post_id, $post ) {
			if ( !class_exists( 'WP_Block_Parser' ) ) {
				return;
			}

			if ( !is_object( $post ) ) {
				return;
			}

			$parser = new WP_Block_Parser();
			$blocks = $parser->parse( $post->post_content );

			foreach ( $blocks as $block ) {
				if ( array_key_exists( 'id', $block['attrs'] ) ) {
					$gallery_id = $block['attrs']['id'];

					add_post_meta( $post_id, FOOGALLERY_META_POST_USAGE, $gallery_id, false );

					do_action( 'foogallery_attach_gallery_to_post', $post_id, $gallery_id );
				}
			}
		}
	}
}