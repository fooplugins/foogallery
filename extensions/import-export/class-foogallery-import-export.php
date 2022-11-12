<?php
/**
 * FooGallery - Import Export Class
 */

if ( ! class_exists( 'FooGallery_Import_Export' ) ) {

	require_once plugin_dir_path( __FILE__ ) . 'functions.php';

	/**
	 * Class FooGallery_Import_Export
	 */
	class FooGallery_Import_Export {

		/**
		 * Wire up everything we need to run the extension
		 */
		public function __construct() {
			if ( is_admin() ) {
				//add_action( 'add_meta_boxes_foogallery', array( $this, 'add_export_metabox' ) );
				add_action( 'foogallery_admin_menu_after', array( $this, 'add_import_export_menu' ) );
				add_action( 'wp_ajax_foogallery_gallery_export', array( $this, 'ajax_generate_export' ) );
				add_action( 'wp_ajax_foogallery_gallery_import', array( $this, 'ajax_import_galleries' ) );
			}
		}

		/**
		 * Output a log message
		 *
		 * @param string $message The message to show.
		 * @param bool   $line_break If a line break should also be output.
		 */
		public function log_message( $message, $line_break = true ) {
			echo esc_html( $message );
			if ( $line_break ) {
				echo '<br />';
			}
		}

		/**
		 * Import galleries
		 */
		public function ajax_import_galleries() {
			if ( check_admin_referer( 'foogallery_gallery_import' ) ) {
				if ( isset( $_POST['data'] ) ) {
					if ( empty( $_POST['data'] ) ) {
						echo esc_html( __( 'No import data provided!', 'foogallery' ) );
					} else {

						$galleries = json_decode( wp_unslash( $_POST['data'] ), true );

						if ( null === $galleries ) {
							echo esc_html( __( 'The import data could not be interpreted.', 'foogallery' ) );
						} else {
							$gallery_count    = 0;
							$attachment_count = 0;
							$new_attachments  = array();
							foreach ( $galleries as $gallery ) {
								$datasource_name = $gallery['datasource_name'];
								$gallery_id      = $gallery['ID'];
								$name            = $gallery['name'];
								$template        = $gallery['template'];

								$this->log_message( sprintf( __( 'Import started for gallery ID : %1$s; Name : "%2$s"; Template : %3$s.', 'foogallery' ), $gallery_id, $name, $template ) );

								// Check if we have already imported the gallery.
								$search_gallery = FooGallery::get_by_id( $gallery_id );
								// A match is made if the ID, name and template are the same, and also it's published!
								if ( $search_gallery->ID === intval( $gallery_id ) && $search_gallery->gallery_template === $template && $search_gallery->name === $name && 'publish' === $search_gallery->post_status ) {
									$this->log_message( __( 'Gallery already exists so skipping.', 'foogallery' ) );
									continue;
								}

								$media_library_datasource = 'media_library' === $datasource_name;

								if ( $media_library_datasource ) {
									// We need to import attachments!
									foreach ( $gallery['attachments'] as $attachment_id => $attachment ) {
										$this->log_message( sprintf( __( 'Importing attachment from URL : %s ... ', 'foogallery' ), $attachment['url'] ), false );
										// Try to find the image.
										$imported_attachment_id = $this->find_attachment( $attachment['url'] );
										if ( 0 === $imported_attachment_id ) {
											// Import the attachment into the media library.

											$imported_attachment_id = foogallery_import_attachment( $attachment );
											if ( is_wp_error( $imported_attachment_id ) ) {
												$this->log_message( sprintf( __( 'error : %s.', 'foogallery' ), $imported_attachment_id->get_error_message() ) );
												$imported_attachment_id = 0;
											} else {
												$this->log_message( sprintf( __( 'success! ID : %s.', 'foogallery' ), $imported_attachment_id ) );
												$attachment_count ++;
											}
										} else {
											$this->log_message( __( 'already exists so skipping.', 'foogallery' ) );
										}
										if ( !is_wp_error( $imported_attachment_id ) && intval( $imported_attachment_id ) > 0 ) {
											$new_attachments[ $attachment_id ] = $imported_attachment_id;
										}
									}
								}

								$gallery_data = array(
									'post_title'  => $name,
									'post_status' => 'publish',
									'post_type'   => FOOGALLERY_CPT_GALLERY,
									'meta_input'  => array(
										FOOGALLERY_META_TEMPLATE   => $template,
										FOOGALLERY_META_SETTINGS   => $gallery['settings'],
										FOOGALLERY_META_SORT       => $gallery['sorting'],
										FOOGALLERY_META_DATASOURCE => $datasource_name,
										FOOGALLERY_META_RETINA     => $gallery['retina'],
										FOOGALLERY_META_CUSTOM_CSS => $gallery['custom_css'],
									),
								);

								if ( $media_library_datasource ) {
									$new_attachment_ids = array();
									foreach ( $gallery['attachment_ids'] as $old_attachment_id ) {
										if ( array_key_exists( $old_attachment_id, $new_attachments ) ) {
											$new_attachment_ids[] = $new_attachments[ $old_attachment_id ];
										}
									}
									$gallery_data['meta_input'][ FOOGALLERY_META_ATTACHMENTS ] = $new_attachment_ids;
								} else {
									$gallery_data['meta_input'][ FOOGALLERY_META_DATASOURCE_VALUE ] = $gallery['datasource_value'];
								}

								$new_gallery_id = wp_insert_post( $gallery_data, true );

								if ( ! is_wp_error( $new_gallery_id ) ) {
									$gallery_count ++;
									$this->log_message( sprintf( __( 'Success! Gallery successfully created. Gallery ID : %s.', 'foogallery' ), $new_gallery_id ) );
								} else {
									$this->log_message( sprintf( __( 'Error! Could not create gallery. Error : %s.', 'foogallery' ), $new_gallery_id->get_error_message() ) );
								}
							}
						}
					}
				}
			}
			wp_die();
		}

		public function find_attachment( $url ) {
			$imported_attachment_id = attachment_url_to_postid( $url );
			if ( $imported_attachment_id === 0 ) {
				$found_attachments = get_posts( array(
					'post_type'  => 'attachment',
					'meta_key'   => '_foogallery_imported_from',
					'meta_value' => $url,
				) );

				if ( is_array( $found_attachments ) && count( $found_attachments ) > 0 ) {
					$imported_attachment_id = $found_attachments[0]->ID;
				}
			}

			return $imported_attachment_id;
		}

		/**
		 * Generate the export data
		 */
		public function ajax_generate_export() {
			if ( check_admin_referer( 'foogallery_gallery_export' ) ) {
				if ( isset( $_POST['galleries'] ) ) {
					$galleries = array_map( 'sanitize_text_field', wp_unslash( $_POST['galleries'] ) );
					echo foogallery_generate_export_json( $galleries );
				}
			}
			die();
		}

		/**
		 * Registers the test menu and page
		 */
		public function add_import_export_menu() {
			foogallery_add_submenu_page(
				__( 'Import / Export', 'foogallery' ),
				'manage_options',
				'foogallery_import_export',
				array( $this, 'render_import_export_page' )
			);
		}

		/**
		 * Renders Import / Export page
		 */
		public function render_import_export_page() {
			require_once 'class-foogallery-export-view-helper.php';
			require_once 'class-foogallery-import-view-helper.php';
			require_once 'view-import-export.php';
		}

		/**
		 * Add a metabox to the gallery for exporting
		 *
		 * @param WP_Post $post the post we are dealing with.
		 */
		public function add_export_metabox( $post ) {
			add_meta_box(
				'foogallery_export',
				__( 'Export', 'foogallery' ),
				array( $this, 'render_export_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'low'
			);
		}

		/**
		 * Render the export metabox on the gallery edit page
		 *
		 * @param WP_Post $post the post we are dealing with.
		 */
		public function render_export_metabox( $post ) {
			$export = foogallery_generate_export_json( $post->ID );
			?>
			<style>
				.foogallery_metabox_export {
					width: 100%;
					height: 50em;
				}
			</style>
			<p>
				<?php echo esc_html( __( 'Below is a JSON export of your gallery.', 'foogallery' ) ); ?>
			</p>
			<table id="table_styling" class="form-table">
				<tbody>
				<tr>
					<td>
						<textarea class="foogallery_metabox_export" type="text"><?php echo esc_html( $export ); ?></textarea>
					</td>
				</tr>
				</tbody>
			</table>
			<?php
		}
	}
}