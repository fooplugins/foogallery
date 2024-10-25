<?php

/*
 * FooGallery Admin Gallery MetaBoxes class
 */

if ( ! class_exists( 'FooGallery_Admin_Gallery_Editor' ) ) {

	class FooGallery_Admin_Gallery_Editor {

		/**
		 * Primary class constructor.
		 */
		public function __construct() {
			//adds a media button to the editor
			add_action( 'media_buttons', array( $this, 'add_media_button') );

			//add a tinymce plugin
			add_action( 'admin_head', array( $this, 'add_tinymce_plugin' ) );

			// Ajax calls for showing all galleries in the modal
			add_action( 'wp_ajax_foogallery_load_galleries', array( $this, 'ajax_galleries_html' ) );

			add_action( 'wp_ajax_foogallery_tinymce_load_info', array( $this, 'ajax_get_gallery_info' ) );
		}

		private function should_hide_editor_button() {
			return 'on' == foogallery_get_setting( 'hide_editor_button' );
		}

		/**
		 * Adds a gallery insert button into the editor
		 *
		 * @param string $editor_id the instance id of the current editor
		 *
		 * @return string $buttons    the amended media buttons
		 */
		public function add_media_button( $editor_id ) {

			if ( $this->should_hide_editor_button() ) {
				return;
			}

			//render the gallery modal
			add_action( 'admin_footer', array( $this, 'render_gallery_modal' ) );
			add_action( 'wp_footer', array( $this, 'render_gallery_modal' ) );

			$url = FOOGALLERY_URL . 'css/admin-foogallery-gallery-piles.css';
			wp_enqueue_style( 'admin-foogallery-gallery-piles', $url, array(), FOOGALLERY_VERSION );

			$foogallery = FooGallery_Plugin::get_instance();
			$foogallery->register_and_enqueue_js( 'admin-foogallery-editor.js' );

			?>
				<button type="button" class="button foogallery-modal-trigger"
					title="<?php esc_attr_e( sprintf( __( 'Add Gallery From %s', 'foogallery' ), foogallery_plugin_name() ) ); ?>"
					style="padding-left: .4em;"
					data-editor="<?php esc_attr_e( $editor_id ); ?>">
					<span class="wp-media-buttons-icon dashicons dashicons-format-gallery"></span> <?php echo sprintf( __( 'Add %s', 'foogallery' ), foogallery_plugin_name() ); ?>
				</button>
			<?php
		}

		/**
		 * Adds our custom plugin to the tinyMCE editor
		 */
		public function add_tinymce_plugin() {

			// get out if we do not want to add the button
			if ( $this->should_hide_editor_button() ) {
				return;
			}

			// check user permissions
			if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
				return;
			}
			// check if WYSIWYG is enabled
			if ( 'true' == get_user_option( 'rich_editing' ) ) {
				add_filter( 'mce_external_plugins',  array( $this, 'add_tinymce_js' ) );
				add_filter( 'mce_css',  array( $this, 'add_tinymce_css' ) );
				add_action( 'admin_footer', array( $this, 'render_tinymce_nonce') );
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
			if ( ! empty( $mce_css ) ) {
				$mce_css .= ',';
			}

			$mce_css .= FOOGALLERY_URL . 'css/admin-tinymce.css'; // . urlencode( '?v=' + FOOGALLERY_VERSION );

			return $mce_css;
		}

		/**
		 * Renders a nonce field that is used in our AJAX calls for the visual editor
		 */
		public function render_tinymce_nonce() {
			?>
			<input id="foogallery-timnymce-action-nonce" type="hidden" value="<?php esc_url( wp_create_nonce( 'foogallery-timymce-nonce' ) ); ?>" />
			<?php
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
				$img_src = foogallery_find_featured_attachment_thumbnail_src( $gallery, array(
					'width' => get_option( 'thumbnail_size_w' ),
					'height' => get_option( 'thumbnail_size_h' ),
					'force_use_original_thumb' => true
				) );
				$images = $gallery->image_count();
				$title = empty( $gallery->name ) ?
					sprintf( __( '%s #%s', 'foogallery' ), foogallery_plugin_name(), $gallery->ID ) :
					$gallery->name;
				?>
                <li class="foogallery-pile">
                    <div class="foogallery-gallery-select" data-foogallery-id="<?php echo $gallery->ID; ?>">
                        <div style="display: table;">
                            <div style="display: table-cell; vertical-align: middle; text-align: center;">
                                <img src="<?php echo $img_src; ?>"/>
                                <h3>
									<?php echo esc_html( $title ); ?>
                                    <span><?php echo $images; ?></span>
                                </h3>
                            </div>
                        </div>
                    </div>
                </li>
				<?php } ?>
				<li class="foogallery-pile">
					<div class="foogallery-gallery-select foogallery-add-gallery">
						<a href="<?php echo esc_url( foogallery_admin_add_gallery_url() ); ?>" target="_blank" class="thumbnail" style="display: table;">
							<span></span>
							<div class="foogallery-gallery-select-inner" >
								<h3><?php _e( 'Add New Gallery', 'foogallery' ); ?></h3>
							</div>
						</a>
					</div>
				</li>
			<?php

			return ob_get_clean();
		}

		function ajax_get_gallery_info() {

			$nonce = sanitize_text_field( safe_get_from_request( 'nonce' ) );

			wp_verify_nonce( $nonce, 'foogallery-timymce-nonce' );

			$id = intval( safe_get_from_request( 'foogallery_id' ) );

			$gallery = FooGallery::get_by_id( $id );

			$image_src = foogallery_find_featured_attachment_thumbnail_src( $gallery, array(
				'width' => get_option( 'thumbnail_size_w' ),
				'height' => get_option( 'thumbnail_size_h' ),
				'force_use_original_thumb' => true
			) );

			$json_array = array(
				'id'    => $id,
				'name'  => $gallery->name,
				'count' => $gallery->image_count(),
				'src'   => $image_src,
			);

			header( 'Content-type: application/json' );
			echo json_encode( $json_array );

			die();
		}
	}
}
