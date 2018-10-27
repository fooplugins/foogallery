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

			add_action( 'init', array( $this, 'php_block_init' ) );
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

			foogallery_enqueue_core_gallery_template_script();

			// Scripts.
			wp_enqueue_script(
				'foogallery-block-js', // Handle.
				$js_url, // Block.build.js: We register the block here. Built with Webpack.
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'foogallery-core' ), // Dependencies, defined above.
				// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
				true // Enqueue the script in the footer.
			);

			foogallery_enqueue_core_gallery_template_style();

			// Styles.
			wp_enqueue_style(
				'foogallery-block-editor-css', // Handle.
				plugins_url( 'gutenberg/dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
				array( 'wp-edit-blocks', 'foogallery-core' ) // Dependency to include the CSS after it.
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

		/**
		 * Register our block and shortcode.
		 */
		function php_block_init() {
			if ( function_exists( 'register_block_type' ) ) {
				// Register our block, and explicitly define the attributes we accept.
				register_block_type(
					'foogallery/responsive-gallery', array(
					'attributes' => array(
						'foo' => array(
							'type' => 'string',
						),
					),
					'render_callback' => array( $this, 'render_block' ),
				));
			}
		}

		function render_block( $attributes ) {
			$foogallery_id = $attributes['foo'];
			$args = array(
				'id' => $foogallery_id
			);
			//create new instance of template engine
			$engine = new FooGallery_Template_Loader();

			ob_start();

			$engine->render_template( $args );

			$output_string = ob_get_contents();
			ob_end_clean();
			return $output_string;
				//"<script type='text/javascript'>setTimeout(function(){ jQuery('#foogallery-gallery-{$foogallery_id}').foogallery(); }, 5000);</script>";
		}
	}
}
