<?php

/*
 * FooGallery Admin Gallery MetaBoxes class
 */

if ( !class_exists( 'FooGallery_Admin_Gallery_Editor' ) ) {

	class FooGallery_Admin_Gallery_Editor {

		/**
		 * Primary class constructor.
		 */
		public function __construct() {
			//adds a media button to the editor
			add_filter( 'media_buttons_context', array($this, 'add_media_button') );

			//add a tinymce plugin
			add_action( 'admin_head', array($this, 'add_tinymce_plugin') );

			// Ajax calls for showing all galleries in the modal
			add_action( 'wp_ajax_foogallery_load_galleries', array($this, 'ajax_galleries_html') );
		}

		/**
		 * Adds a gallery insert button into the editor
		 *
		 * @param string $buttons the existing media buttons
		 *
		 * @return string $buttons    the amended media buttons
		 */
		public function add_media_button($buttons) {

			//render the gallery modal
			add_action( 'admin_footer', array($this, 'render_gallery_modal') );

			$foogallery = FooGallery_Plugin::get_instance();

			$foogallery->register_and_enqueue_js( 'admin-foogallery-editor.js' );

			$buttons .= '<a href="#" class="button foogallery-modal-trigger" title="' . esc_attr__( 'Add Gallery From FooGallery', 'foogallery' ) . '" style="padding-left: .4em;">';
			$buttons .= '<span class="wp-media-buttons-icon dashicons dashicons-format-gallery"></span> ' . __( 'Add FooGallery', 'foogallery' ) . '</a>';

			return $buttons;
		}

		/**
		 * Adds our custom plugin to the tinyMCE editor
		 */
		public function add_tinymce_plugin() {
			// check user permissions
			if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
				return;
			}
			// check if WYSIWYG is enabled
			if ( 'true' == get_user_option( 'rich_editing' ) ) {
				add_filter( 'mce_external_plugins',  array($this, 'add_tinymce_js' ) );
				add_filter( 'mce_css',  array($this, 'add_tinymce_css' ) );
			}
		}

		/**
		 * Include a plugin script into the editor
		 * @param $plugin_array
		 *
		 * @return mixed
		 */
		public function add_tinymce_js( $plugin_array ) {
			$plugin_array['foogallery'] = FOOGALLERY_URL . 'js/admin-tinymce.js';
			return $plugin_array;
		}

		/**
		 * Include a plugin script into the editor
		 * @param $mce_css
		 *
		 * @return string
		 */
		public function add_tinymce_css( $mce_css ) {
			if ( ! empty( $mce_css ) )
				$mce_css .= ',';

			$mce_css .= FOOGALLERY_URL . 'css/admin-tinymce.css';

			return $mce_css;
		}



		/**
		 * Renders the gallery modal for use in the editor
		 */
		public function render_gallery_modal() {

			?>
			<style>
				.foogallery-modal-reload-container {
					display: inline-block;
				}
				.foogallery-modal-reload-container a.button {
					margin-top:18px !important;
				}
				.foogallery-modal-reload-container a span {
					margin-top: 3px;
				}
				.foogallery-modal-reload-container .spinner {
					position: absolute;
					top: 20px;
					display: inline-block;
					margin-left: 5px;
				}
				.foogallery-pile {
					position: relative;
					z-index: 10;
					float: left;
					margin: 10px 20px 30px 20px !important;
				}
				/* Image styles */
				.foogallery-pile .foogallery-gallery-select {
					max-width: 100%;
					vertical-align: bottom;
					border: 8px solid #fff;
					-webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
					-moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
					box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
					overflow: hidden;
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

				.foogallery-pile img {
					height: 100%;
				}

				.foogallery-pile h3 {
					background: #fff;
					position: absolute;
					display: block;
					bottom: 0px;
					padding: 5px;
					width: 100%;
					box-sizing: border-box;
					margin: 0;
					opacity: 0.8;
				}

				.foogallery-pile h3 span {
					display: block;
					font-size: 0.6em;
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
					<a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>
					<div class="media-modal-content">
						<div class="media-frame wp-core-ui hide-menu hide-router foogallery-meta-wrap">
							<div class="media-frame-title">
								<h1>
									<?php _e( 'Choose A Gallery To Insert', 'foogallery' ); ?>
									<div class="foogallery-modal-reload-container">
										<div class="spinner"></div>
										<a class="foogallery-modal-reload button" href="#"><span class="dashicons dashicons-update"></span> <?php _e('Reload', 'foogallery'); ?></a>
									</div>
								</h1>
							</div>
							<div class="media-frame-content">
								<div class="attachments-browser">
									<ul class="foogallery-attachment-container attachments" style="padding-left: 8px; top: 1em;">
										<div class="foogallery-modal-loading"><?php _e('Loading galleries. Please wait...', 'foogallery'); ?></div>
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

		function ajax_galleries_html() {
			if ( check_admin_referer( 'foogallery_load_galleries', 'foogallery_load_galleries' ) ) {
				echo $this->get_galleries_html_for_modal();
			}
			die();
		}

		function get_galleries_html_for_modal() {
			$galleries = foogallery_get_all_galleries();

			ob_start();

			foreach ( $galleries as $gallery ) {
				$img_src = $gallery->attachment_image_src( array(200, 200) );
				$images = $gallery->image_count();
				?>
	<li class="foogallery-pile">
		<div class="foogallery-gallery-select attachment-preview landscape" data-foogallery-id="<?php echo $gallery->ID; ?>">
			<div class="thumbnail" style="display: table;">
				<div style="display: table-cell; vertical-align: middle; text-align: center;">
					<img src="<?php echo $img_src; ?>" />
					<?php

					$title = empty( $gallery->name ) ?
						sprintf( __( 'FooGallery #%s', 'foogallery' ), $gallery->ID ) :
						$gallery->name;

					?>
					<h3><?php echo $title; ?>
						<span><?php echo $images; ?></span>
						<code>[foogallery id="<?php echo $gallery->ID; ?>"]</code>
					</h3>
				</div>
			</div>
		</div>
	</li>
<?php		}
			?>
			<li class="foogallery-pile">
				<div class="foogallery-gallery-select attachment-preview landscape foogallery-add-gallery">
					<a href="<?php echo foogallery_admin_add_gallery_url(); ?>" target="_blank" class="thumbnail" style="display: table;">
						<div style="display: table-cell; vertical-align: middle; text-align: center;">
							<span></span>
							<h3><?php _e('Add New Gallery', 'foogallery'); ?></h3>
						</div>
					</a>
				</div>
			</li>
			<?php

			return ob_get_clean();
		}
	}
}