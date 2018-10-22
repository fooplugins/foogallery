<?php
/**
 * FooGallery Blocks Initializer
 *
 * Enqueue CSS/JS of all the FooGallery blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

if ( ! class_exists( 'FooGallery_Blocks' ) ) {
	class FooGallery_Blocks {

		function __construct() {
			//Frontend block assets.
			add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );

			//Backend editor block assets.
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		}

		/**
		 * Enqueue Gutenberg block assets for backend editor.
		 *
		 * `wp-blocks`: includes block type registration and related functions.
		 * `wp-element`: includes the WordPress Element abstraction for describing the structure of your blocks.
		 * `wp-i18n`: To internationalize the block's text.
		 *
		 * @since 1.0.0
		 */
		function enqueue_block_editor_assets() {

			$js_url = plugins_url( 'gutenberg/dist/blocks.build.js', dirname( __FILE__ ) );

			// Scripts.
			wp_enqueue_script(
				'foogallery-block-js', // Handle.
				$js_url, // Block.build.js: We register the block here. Built with Webpack.
				array( 'wp-blocks', 'wp-i18n', 'wp-element' ), // Dependencies, defined above.
				// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
				true // Enqueue the script in the footer.
			);

			// Styles.
			wp_enqueue_style(
				'foogallery-block-editor-css', // Handle.
				plugins_url( 'gutenberg/dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
				array( 'wp-edit-blocks' ) // Dependency to include the CSS after it.
			// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: filemtime — Gets file modification time.
			);
		}

		/**
		 * Enqueue Gutenberg block assets for both frontend + backend.
		 *
		 * `wp-blocks`: includes block type registration and related functions.
		 *
		 * @since 1.0.0
		 */
		function enqueue_block_assets() {
			// Styles.
			wp_enqueue_style(
				'foogallery-block-css',
				plugins_url( 'gutenberg/dist/blocks.style.build.css', dirname( __FILE__ ) ),
				array( 'wp-blocks' )
			);
		}
	}
}
