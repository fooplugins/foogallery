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
		const IMPORT_JOB_TTL = 21600; // 6 hours
		const IMPORT_JOB_USER_META_KEY = 'foogallery_import_export_last_job';

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
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error(
					array( 'message' => __( 'You do not have permission to import galleries.', 'foogallery' ) ),
					403
				);
			}

			check_ajax_referer( 'foogallery_gallery_import' );

			$mode = 'start';
			if ( isset( $_POST['mode'] ) ) {
				$mode = sanitize_key( wp_unslash( $_POST['mode'] ) );
			}

			if ( 'status' === $mode ) {
				$this->ajax_import_status();
			} elseif ( 'delete' === $mode ) {
				$this->ajax_import_delete();
			} elseif ( 'step' === $mode ) {
				$this->ajax_import_step();
			} else {
				$this->ajax_import_start();
			}
		}

		private function ajax_import_status() {
			$job_id = $this->get_last_import_job_id();
			if ( empty( $job_id ) ) {
				wp_send_json_success( array( 'has_job' => false ) );
			}

			$job = $this->load_import_job( $job_id );
			if ( false === $job ) {
				$this->clear_last_import_job_id();
				wp_send_json_success( array( 'has_job' => false ) );
			}

			$data              = $this->format_import_job_progress( $job_id, $job );
			$data['has_job']   = true;
			$data['can_delete'] = true;
			wp_send_json_success( $data );
		}

		private function ajax_import_delete() {
			$job_id = '';
			if ( isset( $_POST['job_id'] ) ) {
				$job_id = sanitize_text_field( wp_unslash( $_POST['job_id'] ) );
			}
			if ( empty( $job_id ) ) {
				$job_id = $this->get_last_import_job_id();
			}

			if ( empty( $job_id ) ) {
				wp_send_json_success( array( 'deleted' => false ) );
			}

			$this->delete_import_job( $job_id );
			$this->clear_last_import_job_id( $job_id );

			wp_send_json_success( array( 'deleted' => true ) );
		}

		private function ajax_import_start() {
			if ( ! isset( $_POST['data'] ) || empty( $_POST['data'] ) ) {
				wp_send_json_error( array( 'message' => __( 'No import data provided!', 'foogallery' ) ), 400 );
			}

			$galleries = json_decode( wp_unslash( $_POST['data'] ), true );
			if ( null === $galleries || ! is_array( $galleries ) ) {
				wp_send_json_error( array( 'message' => __( 'The import data could not be interpreted.', 'foogallery' ) ), 400 );
			}

			$job_id = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'foogallery_import_', true );
			$job    = $this->build_import_job( $galleries );

			$this->save_import_job( $job_id, $job );
			$this->set_last_import_job_id( $job_id );

			$data = $this->run_import_step_with_lock( $job_id );
			wp_send_json_success( $data );
		}

		private function ajax_import_step() {
			if ( ! isset( $_POST['job_id'] ) || empty( $_POST['job_id'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Missing import job ID.', 'foogallery' ) ), 400 );
			}

			$job_id = sanitize_text_field( wp_unslash( $_POST['job_id'] ) );
			$job    = $this->load_import_job( $job_id );
			if ( false === $job ) {
				wp_send_json_error( array( 'message' => __( 'Import job not found or expired.', 'foogallery' ) ), 404 );
			}

			$this->set_last_import_job_id( $job_id );

			$data = $this->run_import_step_with_lock( $job_id );
			wp_send_json_success( $data );
		}

		private function run_import_step_with_lock( $job_id ) {
			$job = $this->load_import_job( $job_id );
			if ( false === $job ) {
				return array(
					'job_id'   => $job_id,
					'complete' => true,
					'message'  => __( 'Import job not found or expired.', 'foogallery' ),
				);
			}

			$now = time();
			if ( isset( $job['lock_until'] ) && intval( $job['lock_until'] ) > $now ) {
				return array_merge( $this->format_import_job_progress( $job_id, $job ), array(
					'busy'    => true,
					'message' => __( 'Import is busy, retrying...', 'foogallery' ),
				) );
			}

			$job['lock_until'] = $now + 20;
			$this->save_import_job( $job_id, $job );

			$result = $this->run_import_step( $job );

			unset( $job['lock_until'] );
			$this->save_import_job( $job_id, $job );

			return array_merge( $this->format_import_job_progress( $job_id, $job ), $result );
		}

		private function build_import_job( array $galleries ) {
			$attachment_tasks = array();

			foreach ( $galleries as $gallery_index => $gallery ) {
				if ( ! is_array( $gallery ) ) {
					continue;
				}

				if ( ! isset( $gallery['datasource_name'] ) || 'media_library' !== $gallery['datasource_name'] ) {
					continue;
				}

				if ( ! isset( $gallery['attachments'] ) || ! is_array( $gallery['attachments'] ) ) {
					continue;
				}

				foreach ( $gallery['attachments'] as $old_attachment_id => $attachment ) {
					$attachment_tasks[] = array(
						'g'  => $gallery_index,
						'id' => (string) $old_attachment_id,
					);
				}
			}

			$total = count( $attachment_tasks ) + count( $galleries );

			return array(
				'created_at'        => time(),
				'stage'             => count( $attachment_tasks ) > 0 ? 'attachments' : 'galleries',
				'galleries'         => $galleries,
				'attachment_tasks'  => $attachment_tasks,
				'attachment_cursor' => 0,
				'gallery_cursor'    => 0,
				'new_attachments'   => array(),
				'done'              => 0,
				'total'             => $total,
			);
		}

		private function run_import_step( array &$job ) {
			$stage = isset( $job['stage'] ) ? $job['stage'] : 'attachments';

			if ( 'done' === $stage ) {
				return array(
					'complete' => true,
					'message'  => __( 'Import complete.', 'foogallery' ),
				);
			}

			if ( 'attachments' === $stage ) {
				$cursor = intval( $job['attachment_cursor'] );
				$tasks  = isset( $job['attachment_tasks'] ) && is_array( $job['attachment_tasks'] ) ? $job['attachment_tasks'] : array();

				if ( $cursor >= count( $tasks ) ) {
					$job['stage'] = 'galleries';
					return array(
						'complete' => false,
						'message'  => __( 'Attachment import finished. Creating galleries...', 'foogallery' ),
					);
				}

				$task = $tasks[ $cursor ];
				$job['attachment_cursor'] = $cursor + 1;
				$job['done']              = intval( $job['done'] ) + 1;

				$gallery_index = isset( $task['g'] ) ? intval( $task['g'] ) : -1;
				$old_id        = isset( $task['id'] ) ? (string) $task['id'] : '';

				if ( $gallery_index < 0 || empty( $old_id ) || ! isset( $job['galleries'][ $gallery_index ] ) ) {
					return array(
						'complete' => false,
						'message'  => __( 'Skipping invalid attachment task.', 'foogallery' ),
					);
				}

				$gallery = $job['galleries'][ $gallery_index ];
				if ( ! isset( $gallery['attachments'][ $old_id ] ) || ! is_array( $gallery['attachments'][ $old_id ] ) ) {
					return array(
						'complete' => false,
						'message'  => __( 'Skipping missing attachment data.', 'foogallery' ),
					);
				}

				$attachment = $gallery['attachments'][ $old_id ];
				$url        = isset( $attachment['url'] ) ? $attachment['url'] : '';
				if ( empty( $url ) ) {
					return array(
						'complete' => false,
						'message'  => __( 'Skipping attachment with missing URL.', 'foogallery' ),
					);
				}

				$imported_attachment_id = $this->find_attachment( $url );
				if ( 0 === $imported_attachment_id ) {
					$imported_attachment_id = foogallery_import_attachment( $attachment );
					if ( is_wp_error( $imported_attachment_id ) ) {
						return array(
							'complete' => false,
							'message'  => sprintf( __( 'Attachment import failed: %s', 'foogallery' ), $imported_attachment_id->get_error_message() ),
						);
					}

					$job['new_attachments'][ $old_id ] = intval( $imported_attachment_id );
					return array(
						'complete' => false,
						'message'  => sprintf( __( 'Imported attachment: %s', 'foogallery' ), $url ),
					);
				}

				$job['new_attachments'][ $old_id ] = intval( $imported_attachment_id );
				return array(
					'complete' => false,
					'message'  => sprintf( __( 'Attachment already exists: %s', 'foogallery' ), $url ),
				);
			}

			$cursor   = intval( $job['gallery_cursor'] );
			$galleries = isset( $job['galleries'] ) && is_array( $job['galleries'] ) ? $job['galleries'] : array();

			if ( $cursor >= count( $galleries ) ) {
				$job['stage'] = 'done';
				return array(
					'complete' => true,
					'message'  => __( 'Import complete.', 'foogallery' ),
				);
			}

			$gallery = $galleries[ $cursor ];
			$job['gallery_cursor'] = $cursor + 1;
			$job['done']           = intval( $job['done'] ) + 1;

			if ( ! is_array( $gallery ) ) {
				return array(
					'complete' => false,
					'message'  => __( 'Skipping invalid gallery.', 'foogallery' ),
				);
			}

			$datasource_name = isset( $gallery['datasource_name'] ) ? $gallery['datasource_name'] : '';
			$gallery_id      = isset( $gallery['ID'] ) ? $gallery['ID'] : '';
			$name            = isset( $gallery['name'] ) ? $gallery['name'] : '';
			$template        = isset( $gallery['template'] ) ? $gallery['template'] : '';

			$search_gallery = FooGallery::get_by_id( $gallery_id );
			if ( false !== $search_gallery && $search_gallery->ID === intval( $gallery_id ) && $search_gallery->gallery_template === $template && $search_gallery->name === $name && 'publish' === $search_gallery->post_status ) {
				return array(
					'complete' => false,
					'message'  => sprintf( __( 'Gallery already exists so skipping: %s', 'foogallery' ), $name ),
				);
			}

			$gallery_data = array(
				'post_title'  => $name,
				'post_status' => 'publish',
				'post_type'   => FOOGALLERY_CPT_GALLERY,
				'meta_input'  => array(
					FOOGALLERY_META_TEMPLATE   => $template,
					FOOGALLERY_META_SETTINGS   => isset( $gallery['settings'] ) ? $gallery['settings'] : array(),
					FOOGALLERY_META_SORT       => isset( $gallery['sorting'] ) ? $gallery['sorting'] : array(),
					FOOGALLERY_META_DATASOURCE => $datasource_name,
					FOOGALLERY_META_RETINA     => isset( $gallery['retina'] ) ? $gallery['retina'] : '',
					FOOGALLERY_META_CUSTOM_CSS => isset( $gallery['custom_css'] ) ? $gallery['custom_css'] : '',
				),
			);

			if ( 'media_library' === $datasource_name ) {
				$new_attachment_ids = array();
				if ( isset( $gallery['attachment_ids'] ) && is_array( $gallery['attachment_ids'] ) ) {
					foreach ( $gallery['attachment_ids'] as $old_attachment_id ) {
						$key = (string) $old_attachment_id;
						if ( isset( $job['new_attachments'][ $key ] ) ) {
							$new_attachment_ids[] = $job['new_attachments'][ $key ];
						}
					}
				}
				$gallery_data['meta_input'][ FOOGALLERY_META_ATTACHMENTS ] = $new_attachment_ids;
			} else {
				if ( isset( $gallery['datasource_value'] ) ) {
					$gallery_data['meta_input'][ FOOGALLERY_META_DATASOURCE_VALUE ] = $gallery['datasource_value'];
				}
			}

			$new_gallery_id = wp_insert_post( $gallery_data, true );
			if ( is_wp_error( $new_gallery_id ) ) {
				return array(
					'complete' => false,
					'message'  => sprintf( __( 'Error creating gallery "%1$s": %2$s', 'foogallery' ), $name, $new_gallery_id->get_error_message() ),
				);
			}

			return array(
				'complete' => false,
				'message'  => sprintf( __( 'Created gallery "%1$s" (ID: %2$s)', 'foogallery' ), $name, $new_gallery_id ),
			);
		}

		private function format_import_job_progress( $job_id, array $job ) {
			$done  = isset( $job['done'] ) ? intval( $job['done'] ) : 0;
			$total = isset( $job['total'] ) ? intval( $job['total'] ) : 0;
			$stage = isset( $job['stage'] ) ? $job['stage'] : 'attachments';

			$percent = 0;
			if ( $total > 0 ) {
				$percent = (int) floor( ( $done / $total ) * 100 );
				if ( $percent > 100 ) {
					$percent = 100;
				}
			}

			return array(
				'job_id'   => $job_id,
				'stage'    => $stage,
				'done'     => $done,
				'total'    => $total,
				'percent'  => $percent,
				'complete' => 'done' === $stage,
			);
		}

		private function get_last_import_job_id() {
			$user_id = get_current_user_id();
			if ( $user_id <= 0 ) {
				return '';
			}
			$job_id = get_user_meta( $user_id, self::IMPORT_JOB_USER_META_KEY, true );
			return is_string( $job_id ) ? $job_id : '';
		}

		private function set_last_import_job_id( $job_id ) {
			$user_id = get_current_user_id();
			if ( $user_id <= 0 ) {
				return;
			}
			update_user_meta( $user_id, self::IMPORT_JOB_USER_META_KEY, $job_id );
		}

		private function clear_last_import_job_id( $job_id_to_clear = '' ) {
			$user_id = get_current_user_id();
			if ( $user_id <= 0 ) {
				return;
			}

			if ( empty( $job_id_to_clear ) ) {
				delete_user_meta( $user_id, self::IMPORT_JOB_USER_META_KEY );
				return;
			}

			$current = $this->get_last_import_job_id();
			if ( $current === $job_id_to_clear ) {
				delete_user_meta( $user_id, self::IMPORT_JOB_USER_META_KEY );
			}
		}

		private function get_import_job_transient_key( $job_id ) {
			return 'foogallery_import_job_' . $job_id;
		}

		private function load_import_job( $job_id ) {
			$job = get_transient( $this->get_import_job_transient_key( $job_id ) );
			if ( ! is_array( $job ) ) {
				return false;
			}
			return $job;
		}

		private function save_import_job( $job_id, array $job ) {
			set_transient( $this->get_import_job_transient_key( $job_id ), $job, self::IMPORT_JOB_TTL );
		}

		private function delete_import_job( $job_id ) {
			delete_transient( $this->get_import_job_transient_key( $job_id ) );
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
					echo foogallery_generate_export_json( $galleries ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON output
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
