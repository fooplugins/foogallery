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

			if ( !apply_filters( 'foogallery_gutenberg_enabled', true ) ) {
				return;
			}

			//enqueue foogallery dependencies
			wp_enqueue_script( 'masonry' );
			foogallery_enqueue_core_gallery_template_script();
			foogallery_enqueue_core_gallery_template_style();

			$deps = array(
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'foogallery-core',
				'wp-components',
				'wp-editor',
				'underscore'
			);

			$js_url = plugins_url( 'gutenberg/dist/blocks.build.js', dirname( __FILE__ ) );

			// Scripts.
			wp_enqueue_script(
				'foogallery-block-js', // Handle.
				$js_url, // Block.build.js: We register the block here. Built with Webpack.
				$deps, // Dependencies, defined above.
				// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
				true // Enqueue the script in the footer.
			);

			// Styles.
			wp_enqueue_style(
				'foogallery-block-editor-css', // Handle.
				plugins_url( 'gutenberg/dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
				array( 'wp-edit-blocks', 'foogallery-core' ) // Dependency to include the CSS after it.
				// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: filemtime — Gets file modification time.
			);

			$local_data = false;

			if ( function_exists( 'wp_get_jed_locale_data' ) ) {
				$local_data = wp_get_jed_locale_data( 'foogallery' );
			} else if ( function_exists( 'gutenberg_get_jed_locale_data' ) ) {
				$local_data = gutenberg_get_jed_locale_data( 'foogallery' );
			}

			$block_js_data = apply_filters('foogallery_gutenberg_block_js_data', array(
				"editGalleryUrl" => $this->get_edit_gallery_url()
			));

			$inline_script = 'window.FOOGALLERY_BLOCK = ' . json_encode( $block_js_data ) . ';';
			if ( false !== $local_data ) {
				/*
				 * Pass already loaded translations to our JavaScript.
				 *
				 * This happens _before_ our JavaScript runs, afterwards it's too late.
				 */
				$inline_script .= PHP_EOL . 'wp.i18n.setLocaleData( ' . json_encode( $local_data ) . ', "foogallery" );';
			}

			wp_add_inline_script(
				'foogallery-block-js',
				$inline_script,
				'before'
			);
		}

		function get_edit_gallery_url() {
			$post_type_object = get_post_type_object( "foogallery" );
			if ( !$post_type_object )
				return '';

			if ( $post_type_object->_edit_link ) {
				$link = admin_url( $post_type_object->_edit_link . '&action=edit' );
			} else {
				$link = '';
			}

			return apply_filters( 'foogallery_gutenberg_edit_gallery_url', $link );
		}
		
		/**
		 * Register our block and shortcode.
		 */
		function php_block_init() {
			if ( !apply_filters( 'foogallery_gutenberg_enabled', true ) ) {
				return;
			}

			//get out quickly if no Gutenberg
			if ( !function_exists( 'register_block_type' ) ) {
				return;
			}

			// Register our block, and explicitly define the attributes we accept.
			register_block_type(
				'fooplugins/foogallery', array(
				'attributes' => array(
					'id' => array(
						'type' => 'number',
						'default' => 0
					),
				),
				'render_callback' => array( $this, 'render_block' ),
			));
		}

		function render_block( $attributes ) {
			$foogallery_id = $attributes['id'];
			$args = array(
				'id' => $foogallery_id
			);
			//create new instance of template engine
			$engine = new FooGallery_Template_Loader();

			ob_start();

			$engine->render_template( $args );

			$output_string = ob_get_contents();
			ob_end_clean();
			return !empty($output_string) ? $output_string : null;
		}
	}
}
