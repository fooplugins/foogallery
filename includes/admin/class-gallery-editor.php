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

			// Ajax calls for showing all galleries in the modal
			add_action('wp_ajax_foogallery_load_galleries', array($this, 'ajax_galleries_html'));
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
				.foogallery-pile .foogallery-gallery-select { max-width: 100%; height: auto; vertical-align: bottom; border: 8px solid #fff;
					-webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
					-moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
					box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
				}

				/* Stacks creted by the use of generated content */
				.foogallery-pile:before, .foogallery-pile:after { content: ""; width: 100%; height: 100%; position: absolute; border: 8px solid #fff; left: 0;
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
					font: 400 40px/1 dashicons;
					position: absolute;
					color: #FFF;
					top: 80px;
					lefT: 80px;
					speak: none;
					-webkit-font-smoothing: antialiased;
					background: #1E8CBE;
					border-radius: 50%;
				}

			</style>
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
												<?php _e( 'You can add a new gallery by clicking the "Add New Gallery" button below. It will open in a new window.', 'foogallery' ); ?>
											</p>
											<p>
												<?php _e( 'Once you have finished adding a gallery, come back to this dialog and click the "Reload" button to see your newly created gallery.', 'foogallery' ); ?>
											</p>
											<a target="_blank" href="<?php echo admin_url( 'post-new.php?post_type=foogallery' ); ?>"
											   class="button media-button button-large button-primary"
											   title="<?php esc_attr_e( 'Add New Gallery', 'foogallery' ); ?>"><?php _e( 'Add New Gallery', 'foogallery' ); ?></a>
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
			echo $this->get_galleries_html_for_modal();
			die();
		}

		function get_galleries_html_for_modal() {
			$galleries = foogallery_get_all_galleries();

			ob_start();

			if ( false === $galleries ) {
			?>

			<?php
			}

			foreach ( $galleries as $gallery ) {
				$background_style = $gallery->attachment_image_src( array(200, 200) );
				if ( $background_style ) {
					$background_style = 'background: url(' . $background_style . ') no-repeat';
				}
				$images = $gallery->image_count();
				?>
	<li class="foogallery-pile">
		<div class="foogallery-gallery-select attachment-preview landscape" data-foogallery-id="<?php echo $gallery->ID; ?>">
			<div class="thumbnail" style="display: table; <?php echo $background_style; ?>">
				<div
					style="display: table-cell; vertical-align: middle; text-align: center;">
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
			return ob_get_clean();
		}
	}
}