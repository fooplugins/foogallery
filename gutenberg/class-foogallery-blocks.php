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

			add_action( 'admin_footer', array( $this, 'render_gallery_modal' ) );
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
				//"<script type='text/javascript'>setTimeout(function(){ jQuery('#foogallery-gallery-{$foogallery_id}').foogallery(); }, 5000);</script>";
		}

		/**
		 * Renders the gallery modal for use in the editor
		 */
		public function render_gallery_modal() {

			?>
			<style>
				.foogallery-modal-reload-container {
					display: inline-block;
					margin-left: 10px;
				}
				.foogallery-modal-reload-container a.button {
					margin-top:10px !important;
				}
				.foogallery-modal-reload-container a span {
					margin-top: 3px;
				}
				.foogallery-modal-reload-container .spinner {
					position: absolute;
					top: 15px;
					display: inline-block;
					margin-left: 5px;
				}

				.foogallery-pile {
					position: relative;
					z-index: 10;
					float: left;
					margin: 10px 20px 30px 20px !important;
				}

				.foogallery-pile .foogallery-gallery-select {
					max-width: 100%;
					vertical-align: bottom;
					border: 8px solid #fff;
					-webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
					-moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
					box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
					overflow: hidden;
					width: 200px;
					height: 200px;
					cursor: pointer;
					background-position: center center;
					background-size: cover !important;
				}

				/* Stacks creted by the use of generated content */
				.foogallery-pile:before, .foogallery-pile:after {
					content: "";
					width: 100%;
					height: 100%;
					position: absolute;
					border: 8px solid #fff;
					left: 0;
					-webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
					-moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
					box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
					-webkit-box-sizing: border-box;
					-moz-box-sizing: border-box;
					box-sizing: border-box;
				}
				/* 1st element in stack (behind image) */
				.foogallery-pile:before {
					top: -3px; z-index: -10;
					-webkit-transform: rotate(2deg);
					-moz-transform: rotate(2deg);
					transform: rotate(2deg);
				}
				/* 2nd element in stack (behind image) */
				.foogallery-pile:after {
					top: -2px; z-index: -20;
					-webkit-transform: rotate(-2deg);
					-moz-transform: rotate(-2deg);
					transform: rotate(-2deg);
				}

				.foogallery-gallery-select.selected {
					border-color: #1E8CBE;
				}

				.foogallery-gallery-select.selected::before {
					content: "\f147";
					display: inline-block;
					font: normal 100px/110px 'dashicons';
					position: absolute;
					color: #FFF;
					top: 40%;
					left: 50%;
					margin-left: -50px;
					margin-top: -50px;
					speak: none;
					-webkit-font-smoothing: antialiased;
					background: #1E8CBE;
					border-radius: 50%;
					width: 100px;
					height: 100px;
					z-index: 4;
				}

				.foogallery-gallery-select-inner {
					opacity: 0.8;
					position: absolute;
					bottom: 8px;
					left:8px;
					right:8px;
					padding: 5px;
					background: #FFF;
					text-align: center;
				}

				.foogallery-gallery-select-inner h3 {
					display: block;
					margin: 0;
				}

				.foogallery-gallery-select-inner span {
					display: block;
					font-size: 0.9em;
				}

				.foogallery-add-gallery {
					background: #444;
				}

				.foogallery-add-gallery span::after {
					background: #ddd;
					-webkit-border-radius: 50%;
					border-radius: 50%;
					display: inline-block;
					content: '\f132';
					-webkit-font-smoothing: antialiased;
					font: normal 75px/115px 'dashicons';
					width: 100px;
					height: 100px;
					vertical-align: middle;
					text-align: center;
					color: #999;
					position: absolute;
					top: 40%;
					left: 50%;
					margin-left: -50px;
					margin-top: -50px;
					padding: 0;
					text-shadow: none;
					z-index: 4;
					text-indent: -4px;
				}

				.foogallery-add-gallery:hover span::after {
					background: #1E8CBE;
					color: #444;
				}

			</style>
			<?php wp_nonce_field( 'foogallery_load_galleries', 'foogallery_load_galleries', false ); ?>
			<div class="foogallery-modal-wrapper" style="display: none;">
				<div class="media-modal wp-core-ui">
					<button type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span></button>
					<div class="media-modal-content">
						<div class="media-frame wp-core-ui hide-menu hide-router foogallery-meta-wrap">
							<div class="media-frame-title">
								<h1>
									<?php _e( 'Choose a gallery to insert', 'foogallery' ); ?>
									<div class="foogallery-modal-reload-container">
										<div class="spinner"></div>
										<a class="foogallery-modal-reload button" href="#"><span class="dashicons dashicons-update"></span> <?php _e( 'Reload', 'foogallery' ); ?></a>
									</div>
								</h1>
							</div>
							<div class="media-frame-content">
								<div class="attachments-browser">
									<ul class="foogallery-attachment-container attachments" style="padding-left: 8px; top: 1em;">
										<div class="foogallery-modal-loading"><?php _e( 'Loading galleries...', 'foogallery' ); ?></div>
									</ul>
									<!-- end .foogallery-meta -->
									<div class="media-sidebar">
										<div class="foogallery-modal-sidebar">
											<h3><?php _e( 'Select A Gallery', 'foogallery' ); ?></h3>
											<p>
												<?php _e( 'Select a gallery by clicking it, and then click the "Insert Gallery" button to insert it into your content.', 'foogallery' ); ?>
											</p>
											<h3><?php _e( 'Add A Gallery', 'foogallery' ); ?></h3>
											<p>
												<?php _e( 'You can add a new gallery by clicking the "Add New Gallery" tile on the left. It will open in a new window.', 'foogallery' ); ?>
											</p>
											<p>
												<?php _e( 'Once you have finished adding a gallery, come back to this dialog and click the "Reload" button to see your newly created gallery.', 'foogallery' ); ?>
											</p>
										</div>
										<!-- end .foogallery-meta-sidebar -->
									</div>
									<!-- end .media-sidebar -->
								</div>
								<!-- end .attachments-browser -->
							</div>
							<!-- end .media-frame-content -->
							<div class="media-frame-toolbar">
								<div class="media-toolbar">
									<div class="media-toolbar-secondary">
										<a href="#" class="foogallery-modal-cancel button media-button button-large button-secondary media-button-insert" title="<?php esc_attr_e( 'Cancel', 'foogallery' ); ?>"><?php _e( 'Cancel', 'foogallery' ); ?></a>
									</div>
									<div class="media-toolbar-primary">
										<a href="#" class="foogallery-modal-insert button media-button button-large button-primary media-button-insert" disabled="disabled"
										   title="<?php esc_attr_e( 'Insert Gallery', 'foogallery' ); ?>"><?php _e( 'Insert Gallery', 'foogallery' ); ?></a>
									</div>
									<!-- end .media-toolbar-primary -->
								</div>
								<!-- end .media-toolbar -->
							</div>
							<!-- end .media-frame-toolbar -->
						</div>
						<!-- end .media-frame -->
					</div>
					<!-- end .media-modal-content -->
				</div>
				<!-- end .media-modal -->
				<div class="media-modal-backdrop"></div>
			</div>
			<?php
		}

	}
}
