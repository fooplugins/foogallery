<?php

/**
 * Class Foogallery_FrontEnd_Upload_Moderation
 *
 * This class handles image moderation logic.
 *
 * @package foogallery
 */

if ( ! class_exists( 'Foogallery_FrontEnd_Upload_Moderation' ) ) {

	/**
	 * Class Foogallery_FrontEnd_Upload_Moderation
	 *
	 * This class handles image moderation logic.
	 *
	 * @package foogallery
	 */
	class Foogallery_FrontEnd_Upload_Moderation {

		/**
		 * Foogallery_FrontEnd_Upload_Moderation constructor.
		 *
		 * Initializes the class and registers necessary actions.
		 */
		public function __construct() {
			$this->get_wp_filesystem();
			if ( is_admin() ) {
				add_action( 'init', array( $this, 'init' ) );
			}
		}

		/**
		 * Retrieves the WordPress Filesystem API for file operations and ensures its availability.
		 *
		 * This function checks if the global WordPress `$wp_filesystem` variable is available. If not, it includes
		 * the necessary file management functions and initializes the WordPress Filesystem API to make it accessible.
		 *
		 * @return WP_Filesystem_Base|null The WordPress Filesystem object on success, or null on failure.
		 */
		private function get_wp_filesystem() {
			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
			return $wp_filesystem;
		}

		/**
		 * Initialize the image moderation logic.
		 *
		 * This method is responsible for initializing the image moderation logic.
		 * It handles various actions related to image moderation based on POST requests.
		 */
		public function init() {
			if ( isset( $_POST['moderate_image'] ) ) {
				$image_id = sanitize_text_field( $_POST['image_id'] );
				$action = sanitize_text_field( $_POST['action'] );

				if ( 'approve' ===  $action  ) {
					$this->handle_approve_action();
				} elseif ( $action === 'reject' ) {
					$this->handle_reject_action();
				}
			}

			if ( isset( $_POST['moderate_image'] ) && $_POST['action'] === 'delete' ) {
				$this->handle_delete_action();
			}        

		}

		/**
		 * Handle the "Approve" action for an image.
		 *
		 * This method is responsible for processing the "Approve" action for an image.
		 * It moves the image to the approved folder and updates the metadata accordingly.
		 *
		 * @private
		 */
		private function handle_approve_action() {
			// Verify the nonce.
			if (isset($_POST['approve_image_nonce']) && wp_verify_nonce($_POST['approve_image_nonce'], 'approve_image_nonce')) {
				// Get the gallery ID from the form data.
				$gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;

				if ($gallery_id) {
					// Define the paths.
					$original_folder = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/';
					$approved_folder = wp_upload_dir()['basedir'] . '/approved_folder/' . $gallery_id . '/';

					// Read the metadata from the metadata.json file.
					$metadata_file = $original_folder . 'metadata.json';
					
					if (file_exists($metadata_file)) {
						$metadata_content = file_get_contents($metadata_file);
						$metadata = json_decode($metadata_content, true);

						if ($metadata) {
							// Get the file name (identifier) from the JSON data.
							$file_name = isset($_POST['image_id']) ? sanitize_text_field($_POST['image_id']) : null;

							if ($file_name) {
								// Search for the specific image in the JSON data.
								foreach ($metadata['items'] as $item) {
									if (isset($item['file']) && $item['file']) {
										$image_filename = isset($item['file']) ? sanitize_file_name($item['file']) : '';
										$base_url       = site_url();
										$image_url = $base_url . '/wp-content/uploads/users_uploads/' . $gallery_id . '/' . $image_filename;
										$attachment_data = array(
											'url' => $image_url,
											'title' => $item['caption'],
											'caption' => $item['caption'],
											'alt' => $item['alt'],
											'description' => $item['description'],
											'custom_url' => $item['custom_url'],
											'custom_target' => $item['custom_target'],
										);
										
										$attachment_id = foogallery_import_attachment($attachment_data);

										
										if ($attachment_id) {
											// Add the attachment to the gallery.
											$existing_attachments = get_post_meta($gallery_id, FOOGALLERY_META_ATTACHMENTS, true);
											$existing_attachments[] = $attachment_id;
											update_post_meta($gallery_id, FOOGALLERY_META_ATTACHMENTS, $existing_attachments);

											// Delete the image from the original folder.
											if (isset($item['file']) && $item['file']) {
												$image_filename = sanitize_file_name($item['file']);
												$original_image_path = $original_folder . $image_filename;
												
												if (file_exists($original_image_path)) {
													unlink($original_image_path);
												}
											}

											// Remove the entry from the metadata.json file.
											$new_metadata = ['items' => []];
											foreach ($metadata['items'] as $existing_item) {
												if (isset($existing_item['file']) && $existing_item['file'] !== $item['file']) {
													$new_metadata['items'][] = $existing_item;
												}
											}

											// Update the metadata.json file.
											$metadata_json = json_encode($new_metadata);
											file_put_contents($metadata_file, $metadata_json);

											// Display a success message.
											echo '<div class="notice notice-success"><p>' . __('Image approved and added to the gallery successfully.', 'foogallery') . '</p></div>';
										} else {
											// Display an error message.
											echo '<div class="notice notice-error"><p>' . __('Error importing image. Please try again.', 'foogallery') . '</p></div>';
										}
									}
								}
							} else {
								// Display an error message if the file name is not provided in the form data.
								echo '<div class="notice notice-error"><p>' . __('Invalid request. Please try again.', 'foogallery') . '</p></div>';
							}
						}
					} else {
						// Display an error message if the metadata.json file doesn't exist.
						echo '<div class="notice notice-error"><p>' . __('Metadata file not found. Please try again.', 'foogallery') . '</p></div>';
					}
				} else {
					// Display an error message if the gallery ID is not provided in the form data.
					echo '<div class="notice notice-error"><p>' . __('Invalid request. Please try again.', 'foogallery') . '</p></div>';
				}
			} else {
				// Display an error message if the nonce verification fails.
				echo '<div class="notice notice-error"><p>' . __('Security check failed. Please try again.', 'foogallery') . '</p></div>';
			}
		}

		/**
		 * Handle the "Reject" action for an image.
		 *
		 * This method is responsible for processing the "Reject" action for an image.
		 * It removes the image from the metadata and shows a success message.
		 *
		 * @private
		 */
		private function handle_reject_action() {
			global $wp_filesystem;

			// Verify the nonce.
			if ( isset( $_POST['reject_image_nonce'] ) && wp_verify_nonce( $_POST['reject_image_nonce'], 'reject_image_nonce' ) ) {

				// Get the gallery ID and file name from the form data.
				$gallery_id = isset( $_POST['gallery_id'] ) ? intval( $_POST['gallery_id'] ) : null;
				$file_name = isset( $_POST['image_id'] ) ? sanitize_text_field( $_POST['image_id'] ) : null;

				if ( $gallery_id && $file_name ) {
					// Delete the image file from the server.
					$random_folder_name = get_post_meta( $gallery_id, '_foogallery_frontend_upload', true );                    
					$user_folder = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/';

					// Remove the metadata entry for the rejected image.
					$metadata_file = $user_folder . 'metadata.json';
					if ( file_exists( $metadata_file ) ) {
						$existing_metadata = @json_decode( $wp_filesystem->get_contents( $metadata_file ), true);
						$existing_metadata['items'] = array_filter( $existing_metadata['items'], function ( $item ) use ( $file_name ) {
							return $item['file'] !== $file_name;
						});
						file_put_contents( $metadata_file, json_encode( $existing_metadata, JSON_PRETTY_PRINT ) );
					}

					// Show a success message.
					echo '<div class="notice notice-success"><p>' . __( 'Image successfully rejected', 'foogallery' ) . '</p></div>';
				}
			} else {
				echo '<div class="notice notice-error"><p>' . __( 'Security check failed. Please try again.', 'foogallery' ) . '</p></div>';
			}
		}
		
		/**
		 * Handle the "Delete" action for an image.
		 *
		 * This method is responsible for processing the "Delete" action for an image.
		 * It deletes the image file and updates the metadata accordingly.
		 *
		 * @private
		 */
		private function handle_delete_action() {
			global $wp_filesystem;
			
			// Verify the nonce.
			if ( isset( $_POST['delete_image_nonce'] ) && wp_verify_nonce( $_POST['delete_image_nonce'], 'delete_image_nonce' ) ) {
				$image_id = sanitize_text_field( $_POST['image_id'] );
				$gallery_id = isset( $_POST['gallery_id'] ) ? intval( $_POST['gallery_id'] ) : null;
				
				if ( $gallery_id && $image_id ) {
					// Define the approved folder path.
					$approved_folder = wp_upload_dir()['basedir'] . '/approved_folder/' . $gallery_id . '/';
					
					// Delete the image file from the approved folder.
					$deleted = unlink( $approved_folder . $image_id );
			
					if ( $deleted ) {
						// Remove the metadata entry for the deleted image.
						$metadata_file = $approved_folder . 'metadata.json';
						if ( file_exists( $metadata_file ) ) {
							$existing_metadata = @json_decode( $wp_filesystem->get_contents( $metadata_file ), true );
							$existing_metadata['items'] = array_filter( $existing_metadata['items'], function ( $item ) use ( $image_id ) {
								return $item['file'] !== $image_id;
							});
							file_put_contents( $metadata_file, json_encode( $existing_metadata, JSON_PRETTY_PRINT ) );
						}
			
						// Show a success message.
						echo '<div class="notice notice-success"><p>' . __( 'Image successfully deleted', 'foogallery') . '</p></div>';
					}
				}
			} else {
				echo '<div class="notice notice-error"><p>' . __( 'Security check failed. Please try again.', 'foogallery' ) . '</p></div>';
			}
		}

	}
}