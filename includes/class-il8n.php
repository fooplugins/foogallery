<?php
/**
 * Foogallery class for enqueuing the FooGallery_il8n variable into the page
 */

if ( ! class_exists( 'FooGallery_il8n' ) ) {
	class FooGallery_il8n {

		function __construct() {
			add_action( 'foogallery_enqueue_script-core', array( $this, 'enqueue_il8n' ), 10, 1 );
			add_action( 'foogallery_dequeue_script-core', array( $this, 'dequeue_core' ) );
		}

		/**
		 * Enqueue the il8n script
		 *
		 * @param $js
		 *
		 * @return void
		 */
		function enqueue_il8n( $js ) {
			global $foogallery_enqueue_il8n;

			if ( $foogallery_enqueue_il8n ) {
				return;
			}

			$il8n = array();

			$imageviewer_prev_entry = foogallery_get_language_array_value( 'language_imageviewer_prev_text', __( 'Prev', 'foogallery' ) );
			if ( $imageviewer_prev_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'template' => array(
						"image-viewer" => array(
							'prev' => esc_html( $imageviewer_prev_entry )
						)
					)
				) );
			}

			$imageviewer_next_entry = foogallery_get_language_array_value( 'language_imageviewer_next_text', __( 'Next', 'foogallery' ) );
			if ( $imageviewer_next_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'template' => array(
						"image-viewer" => array(
							'next' => esc_html( $imageviewer_next_entry )
						)
					)
				) );
			}

			$imageviewer_of_entry = foogallery_get_language_array_value( 'language_imageviewer_of_text', __( 'of', 'foogallery' ) );
			if ( $imageviewer_of_entry !== false ) {
				$il8n = array_merge_recursive( $il8n, array(
					'template' => array(
						"image-viewer" => array(
							'of' => esc_html( $imageviewer_of_entry )
						)
					)
				) );
			}

			$il8n = apply_filters( 'foogallery_il8n', $il8n );

			// Only add the script to the page if there is data to be added.
			if ( count( $il8n ) > 0 ) {
				$script = "var FooGallery_il8n = " . foogallery_json_encode( $il8n ) . ';';
				wp_add_inline_script( 'foogallery-core', $script, 'before' );
			}

			$foogallery_enqueue_il8n = true; // To ensure we do not add multiple times on a page with more than 1 gallery.
		}

		/**
		 * Clears
		 *
		 * @return void
		 */
		function dequeue_core() {
			global $foogallery_enqueue_il8n;
			$foogallery_enqueue_il8n = null; // To ensure we once again add the correct il8n script
		}
	}
}