<?php
/**
 * Class FooGallery_FrontEnd_Upload_MetaBoxes
 *
 * @package foogallery
 */

if ( ! class_exists( 'FooGallery_FrontEnd_Upload_MetaBoxes' ) ) {
	/**
	 * Class FooGallery_FrontEnd_Upload_MetaBoxes
	 *
	 * @package foogallery
	 */
	class FooGallery_FrontEnd_Upload_MetaBoxes extends FooGallery_Admin_Gallery_MetaBoxes {
		/**
		 * The ID of the gallery.
		 *
		 * @var int
		 */
		private $gallery_id;

		/**
		 * Foogallery_FrontEnd_Upload_Moderation constructor.
		 *
		 * Initializes the class and registers necessary actions.
		 */
		public function __construct() {
			parent::__construct();
			$this->gallery_id = isset( $_POST['gallery_id'] ) ? intval( $_POST['gallery_id'] ) : null;

			// Hook to save upload form settings.
			add_action( 'save_post', array( $this, 'save_frontend_upload_metabox_settings' ) );
		}

		/**
		 * Add meta boxes to the gallery post type.
		 *
		 * @param WP_Post $post The current post object.
		 */
		public function add_meta_boxes_to_gallery( $post ) {
			parent::add_meta_boxes_to_gallery( $post );

			add_meta_box(
				'custom_metabox_id_frontend_upload',
				__( 'Front End Upload', 'foogallery' ),
				array( $this, 'render_frontend_upload_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'low'
			);

			add_meta_box(
				'custom_metabox_id_image_moderation',
				__( 'Images Awaiting Moderation', 'foogallery' ),
				array( $this, 'render_image_moderation_metabox' ),
				FOOGALLERY_CPT_GALLERY,
				'normal',
				'low'
			);
		}

		/**
		 * Save upload form settings when the gallery post is saved.
		 *
		 * @param int $post_id The ID of the saved post.
		 */
		public function save_frontend_upload_metabox_settings( $post_id ) {
			if ( get_post_type( $post_id ) === FOOGALLERY_CPT_GALLERY ) {
				// Update post meta for the metadata checkboxes.
				$upload_settings = array();
				$metafields = array( 'caption', 'description', 'alt', 'custom_url', 'custom_target' );
				foreach ( $metafields as $metafield ) {
					$metafield_value = isset( $_POST["display_$metafield"] ) ? sanitize_text_field( $_POST["display_$metafield"] ) : 'no';
					update_post_meta( $post_id, "_display_$metafield", $metafield_value );
					$upload_settings["_display_$metafield"] = $metafield_value;
				}

				// Save the maximum images allowed setting.
				if (isset( $_POST['max_images_allowed'] ) ) {
					update_post_meta( $post_id, '_max_images_allowed', sanitize_text_field( $_POST['max_images_allowed'] ) );
					$upload_settings['_max_images_allowed'] = sanitize_text_field( $_POST['max_images_allowed'] );
				}

				// Save the maximum image size setting.
				if ( isset( $_POST['max_image_size'] ) ) {
					update_post_meta( $post_id, '_max_image_size', sanitize_text_field( $_POST['max_image_size'] ) );
					$upload_settings['_max_image_size'] = sanitize_text_field( $_POST['max_image_size'] );
				}

				if ( isset( $_POST['logged_in_users_only'] ) && $_POST['logged_in_users_only'] === 'yes' ) {
					update_post_meta( $post_id, '_logged_in_users_only', 'yes' );
					$upload_settings['_logged_in_users_only'] = 'yes';
				} else {
					update_post_meta( $post_id, '_logged_in_users_only', 'no' );
					$upload_settings['_logged_in_users_only'] = 'no';
				}

				// Serialize and save the upload settings as an array.
				update_post_meta( $post_id, '_foogallery_frontend_upload', serialize( $upload_settings ) );
			}
		}

		/**
		 * Render the frontend upload metabox with updated styling and tooltips.
		 *
		 * @param WP_Post $post The current post object.
		 */
		public function render_frontend_upload_metabox( $post ) {
			$gallery   = $this->get_gallery( $post );
			$shortcode = $gallery->shortcode();

			// Use preg_match to find the ID within the shortcode.
			if ( preg_match( '/\[foogallery id="(\d+)"\]/', $shortcode, $matches ) ) {
				$gallery_id = $matches[1];
				?>

				<div id="metadata-settings">
					<?php
					// Retrieve existing values from the database.
					$max_images_allowed = get_post_meta( $post->ID, '_max_images_allowed', true );
					$max_image_size     = get_post_meta( $post->ID, '_max_image_size', true );

					// Output the HTML for the fields.
					?>
					<div class="foogallery-frontend-upload-inner">
						<div class="foogallery-frontend-upload-inner-section">
							<label for="copy_upload_form_shortcode" style="display: flex; align-items: center;">                            
								<?php esc_html_e( 'Copy shortcode', 'foogallery' ); ?>
							</label>
							<span data-balloon-length="large" data-balloon-pos="right" data-balloon="<?php esc_attr_e( 'Paste the above shortcode into a post or page to show the Image Upload Form.', 'foogallery' ); ?>"><i class="dashicons dashicons-editor-help"></i></span>						
						</div>
						<div style="width: 50%;">
							<input style="width: 250px; text-align:center;  border: 1px solid #ccc; padding: 7px; font-size: 14px;" type="text" id="Upload_Form_copy_shortcode" size="<?php echo esc_attr( strlen( $shortcode ) + 2 ); ?>" value="<?php echo esc_attr( htmlspecialchars( '[foogallery_upload id="' . $gallery_id . '"]' ) ); ?>" readonly="readonly" />
						</div>                 

					</div>
					<div class="foogallery-frontend-upload-inner">
						<div class="foogallery-frontend-upload-inner-section">
							<label for="max_images_allowed" style="display: flex; align-items: center;">                            
								<?php esc_html_e( 'Maximum Images Allowed', 'foogallery' ); ?>
							</label>
							<span data-balloon-length="large" data-balloon-pos="right" data-balloon="<?php esc_attr_e( 'Enter the maximum number of images allowed for upload.', 'foogallery' ); ?>"><i class="dashicons dashicons-editor-help"></i></span>							                                                    
						</div>
						<div style="width: 50%;">
							<input style="width: 250px; border: 1px solid #ccc; padding: 7px; font-size: 14px;" type="number" id="max_images_allowed" name="max_images_allowed" value="<?php echo esc_attr( $max_images_allowed ); ?>" style=" padding: 5px; font-size: 14px;" />
						</div>                 

					</div>

					<div class="foogallery-frontend-upload-inner">
						<div class="foogallery-frontend-upload-inner-section">
							<label for="max_image_size" style="display: flex; align-items: center;">                                
								<?php esc_html_e( 'Maximum Image Size (mb)', 'foogallery' ); ?>
							</label>
							<span data-balloon-length="large" data-balloon-pos="right" data-balloon="<?php esc_attr_e( 'Set the maximum image size (in MB) for uploaded images.', 'foogallery' ); ?>"><i class="dashicons dashicons-editor-help"></i></span>							
						</div> 
						<div style="width: 50%;">
							<input style="width: 250px;  border: 1px solid #ccc; padding: 7px; font-size: 14px;" type="number" id="max_image_size" name="max_image_size" value="<?php echo esc_attr( $max_image_size ); ?>" style="padding: 5px; font-size: 14px;" />
						</div>               

					</div>

					<?php
					$logged_in_users_only = get_post_meta( $post->ID, '_logged_in_users_only', true );
					?>
					<div class="foogallery-frontend-upload-inner">
						<div class="foogallery-frontend-upload-inner-section">
							<label for="logged-in-users-yes" style="display: flex; align-items: center;">
								<?php esc_html_e( 'Only logged-in users can upload', 'foogallery' ); ?>
							</label>
							<span data-balloon-length="large" data-balloon-pos="right" data-balloon="<?php esc_attr_e( 'Restrict front end Image uploads to logged-in users only.', 'foogallery' ); ?>"><i class="dashicons dashicons-editor-help"></i></span>							
						</div>
						<div style="display: flex; justify-content: space-between; align-items: center; width: 50%;">
							<div style="width: 50%;">
								<input type="radio" id="logged-in-users-yes" name="logged_in_users_only" value="yes" <?php checked( $logged_in_users_only, 'yes' ); ?> />
								<label for="logged-in-users-yes"><?php esc_html_e( 'Enabled', 'foogallery' ); ?></label>
							</div>

							<div style="width: 50%;">
								<input type="radio" id="logged-in-users-no" name="logged_in_users_only" value="no" <?php checked( $logged_in_users_only, 'no' ); ?> />
								<label for="logged-in-users-no"><?php esc_html_e( 'Disabled', 'foogallery' ); ?></label>
							</div>
						</div>
					</div>					

					<?php
					$metafields = array( 'caption', 'description', 'alt', 'custom_url', 'custom_target' );

					foreach ( $metafields as $metafield ) {
						$option_name     = "_display_$metafield";
						$metafield_value = get_post_meta( $gallery_id, $option_name, true );
						?>
						<div class="foogallery-frontend-upload-inner">
							<div class="foogallery-frontend-upload-inner-section">
								<label for="display_<?php echo esc_attr( $metafield ); ?>" style="display: flex; align-items: center;">
									<?php echo esc_html( 'Display ' . $metafield ); ?>
								</label>
								<span data-balloon-length="large" data-balloon-pos="right" data-balloon="<?php echo esc_attr( sprintf( __( 'Display the %s field in the front end upload form', 'foogallery' ), $metafield ) ); ?>"><i class="dashicons dashicons-editor-help"></i></span>								
							</div>
							<div style="display: flex; justify-content: space-between; align-items: center; width: 50%;">
								<div style="width: 50%;">
									<input type="radio" id="display_<?php echo esc_attr( $metafield ); ?>" name="display_<?php echo esc_attr( $metafield ); ?>" value="yes" <?php checked( $metafield_value, 'yes' ); ?> /> Yes
								</div>
								<div style="width: 50%;">
									<input type="radio" id="display_<?php echo esc_attr( $metafield ); ?>" name="display_<?php echo esc_attr( $metafield ); ?>" value="no" <?php checked( $metafield_value, 'no' ); ?> /> No
								</div>						

							</div>                            
						</div>
						<?php
					}
					?>

				</div>
				<style>
					.foogallery-frontend-upload-inner {
						align-items: center;
						display: flex;
						margin-bottom: 15px;
						padding: 5px 3px;
						width: 100%;						
					}
					.foogallery-frontend-upload-inner-section {
						align-items: center;
						display: flex;
						width: 50%;
					}
					.foogallery-upload-settings-input-label {
						display: block;
						font-weight: bold;
						margin-bottom: 5px;
					}
					.foogallery-frontend-upload-help {
						align-items: center;
						background-color: black;
						border-radius: 50%;
						color: white;
						cursor: pointer;
						display: flex;
						font-weight: bold;
						height: 15px;
						justify-content: center;
						margin-left: 7px;
						width: 15px;
					}

				</style>
				<script>
					jQuery(function($) {
						var shortcodeInput = document.querySelector( '#Upload_Form_copy_shortcode' );
						shortcodeInput.addEventListener( 'click', function () {
							try {
								// select the contents
								shortcodeInput.select();
								//copy the selection
								document.execCommand( 'copy' );
								//show the copied message
								$( '.foogallery-shortcode-message' ).remove();
								$(shortcodeInput).after( '<p class="foogallery-shortcode-message">Shortcode copied to clipboard :)</p>');
							} catch( err ) {
								console.log( 'Oops, unable to copy!' );
							}
						}, false);
					});			
				</script>
				<?php
			} else {
				// No ID found.
				echo esc_html__( 'No ID found in the shortcode.', 'foogallery' );
			}
		}

		/**
		 * Render the Image Moderation metabox.
		 *
		 * @param WP_Post $post The current post object.
		 */
		public function render_image_moderation_metabox( $post ) {
			// Initialize an array to store gallery IDs and metadata.
			$images_to_moderate = array();

			// Get the base directory for uploads.
			$upload_dir       = wp_upload_dir();
			$user_uploads_dir = $upload_dir['basedir'] . '/users_uploads/';

			// Check if the user uploads directory exists.
			if ( is_dir( $user_uploads_dir ) ) {
				// Get a list of directories inside the user uploads directory.
				$directories = glob( $user_uploads_dir . '*', GLOB_ONLYDIR );

				foreach ( $directories as $directory ) {
					// Extract the gallery ID from the directory name.
					$gallery_id = intval( basename( $directory ) );
					$metadata_file = $directory . '/metadata.json';

					// Check if the metadata file exists.
					if ( file_exists( $metadata_file ) ) {
						global $wp_filesystem;

						// Read and decode the JSON metadata file.
						$metadata_contents = $wp_filesystem->get_contents( $metadata_file );

						if ( false !== $metadata_contents ) {
							$metadata = json_decode( $metadata_contents, true );

							if ( isset( $metadata['items'] ) && null !== $metadata ) {
								// Store the metadata in the images_to_moderate array.
								$images_to_moderate[ $gallery_id ] = $metadata['items'];
							} else {
								// Handle JSON decoding failure or missing 'items' key.
								echo '<div class="notice notice-error"><p>' . esc_html( __( 'Invalid or missing metadata in file:', 'foogallery' ) ) . ' ' . esc_html( $metadata_file ) . '</p></div>';
							}
						} else {
							// Handle file read failure.
							echo '<div class="notice notice-error"><p>' . esc_html( __( 'Failed to read metadata file:', 'foogallery' ) ) . ' ' . esc_html( $metadata_file ) . '</p></div>';

						}
					}
				}
			}

			?>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 100px;"><?php esc_html_e( 'Image', 'foogallery' ); ?></th>							
						<th><?php esc_html_e( 'Metadata', 'foogallery' ); ?></th>
						<th><?php esc_html_e( 'User', 'foogallery' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					// Initialize an array to store user IDs for each image.
					$image_uploaders = array();
					$images_found = false;
					foreach ( $images_to_moderate as $gallery_id => $images ) :
						
						foreach ( $images as $image ) :
							// Get the gallery ID and image file name.
							$images_found = true;
							$gallery_id = intval( $gallery_id );										
							$file_name  = sanitize_text_field( $image['file'] );

							// Check if the 'uploaded_by' field is set in the image's metadata.
							if ( isset( $image['uploaded_by'] ) ) {
								$uploader_id = intval( $image['uploaded_by'] );

								// Store the uploader's ID in the array.
								$image_uploaders[ "$gallery_id-$file_name" ] = $uploader_id;
							} else {
								// Handle cases where 'uploaded_by' field is not set.
								$image_uploaders[ "$gallery_id-$file_name" ] = '';
							}
							?>
							<tr class="image-row">
								<td>
									<?php
									// Retrieve the image URL from the JSON data.
									$image_filename = isset( $image['file'] ) ? sanitize_file_name( $image['file'] ) : '';
									$base_url       = site_url();

									// Construct the complete image URL.
									$image_url = $base_url . '/wp-content/uploads/users_uploads/' . $gallery_id . '/' . $image_filename;

									// Display the image if the URL is not empty.
									if ( ! empty( $image_url ) ) {
										echo '<img style="width: 100px; height: 100px;" src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image['alt'] ) . '" />';
									}
									?>
								</td>									

								<td>
									<p><strong><?php esc_html_e( 'Caption:', 'foogallery' ); ?></strong> <?php echo esc_html( $image['caption'] ); ?></p>
									<p><strong><?php esc_html_e( 'Description:', 'foogallery' ); ?></strong> <?php echo esc_html( $image['description'] ); ?></p>
									<p><strong><?php esc_html_e( 'Alt Text: ', 'foogallery' ); ?></strong><?php echo esc_html( $image['alt'] ); ?></p>
									<p><strong><?php esc_html_e( 'Custom URL: ', 'foogallery' ); ?></strong> <?php echo esc_url( $image['custom_url'] ); ?></p>
									<p><strong><?php esc_html_e( 'Custom Target: ', 'foogallery' ); ?></strong> <?php echo esc_html( $image['custom_target'] ); ?></p>
								</td>

								<td>
									<?php
									// Get the gallery ID and image file name.
									$gallery_id = intval( $gallery_id );
									$file_name  = sanitize_text_field( $image['file'] );

									// Create a unique identifier for this image (gallery_id-file_name).
									$image_identifier = "$gallery_id-$file_name";

									// Get the user ID who uploaded this image from the array.
									$uploader_id = isset( $image_uploaders[ $image_identifier ] ) ? $image_uploaders[ $image_identifier ] : '';

									// Display the uploader's username.
									if ( ! empty( $uploader_id ) ) {
										$uploader_info = get_userdata( $uploader_id );
										if ( $uploader_info ) {
											echo esc_html( $uploader_info->display_name );
										} else {
											echo esc_html__( 'Unknown User', 'foogallery' );
										}
									} else {
										echo esc_html__( 'N/A', 'foogallery' );
									}
									?>
									<div class="image-actions">
										<span style="display: inline-block; text-decoration: none; color: #0073aa; cursor: pointer; font-size: 12px; margin-right: 6px;">
											<div class="confirm-approve" data-gallery-id="<?php echo esc_attr( $gallery_id ); ?>" data-image-id="<?php echo esc_attr( $image['file'] ); ?>" name="approve_image_nonce" data-nonce="<?php echo esc_attr( wp_create_nonce( 'approve_image_nonce' ) ); ?>"><?php esc_html_e( 'Approve', 'foogallery' ); ?></div>
										</span>
										|
										<span style="display: inline-block; text-decoration: none; color: #a00; cursor: pointer; font-size: 12px; margin-left: 6px;">
											<div class="confirm-reject" data-gallery-id="<?php echo esc_attr( $gallery_id ); ?>" data-image-id="<?php echo esc_attr( $image['file'] ); ?>" name="reject_image_nonce" data-nonce="<?php echo esc_attr( wp_create_nonce( 'reject_image_nonce' ) ); ?>"><?php esc_html_e( 'Reject', 'foogallery' ); ?></div>
										</span>
									</div>
								</td>

							</tr>
							<?php
						endforeach;
						
					endforeach;
					?>
				</tbody>
				<?php
				// Check if no images were found and display a message.
				if ( ! $images_found ) {
					echo '<tr><td colspan="4" style="text-align: center;">' . esc_html__( 'There are no images awaiting moderation', 'foogallery' ) . '</td></tr>';
				}
				?>
			</table>

			<script>
				
				// Add event listeners for confirmation dialogs
				const confirmRejectButtons = document.querySelectorAll('.confirm-reject');
				confirmRejectButtons.forEach(button => {
					button.addEventListener('click', function (e) {
						e.preventDefault();

						const galleryId = this.getAttribute('data-gallery-id');
						const imageId = this.getAttribute('data-image-id');
						const nonce = this.getAttribute('data-nonce');
						if (confirm(`Are you sure you want to reject this image?`)) {
							const form = document.createElement('form');
							form.method = 'post';
							form.innerHTML = `
								<input type="hidden" name="gallery_id" value="${galleryId}">
								<input type="hidden" name="image_id" value="${imageId}">
								<input type="hidden" name="action" value="reject">
								<input type="hidden" name="moderate_image" value="confirmed_reject">
								<input type="hidden" name="reject_image_nonce" value="${nonce}">
							`;
							document.body.appendChild(form);
							form.submit();
						}
					});
				});


				// Add event listeners for confirmation dialogs for "Approve"
				const confirmApproveButtons = document.querySelectorAll('.confirm-approve');
				confirmApproveButtons.forEach(button => {
					button.addEventListener('click', function (e) {
						e.preventDefault();

						const galleryId = this.getAttribute('data-gallery-id');
						const imageId = this.getAttribute('data-image-id');
						const nonce = this.getAttribute('data-nonce');

						if (confirm(`Are you sure you want to approve this image?`)) {
							const form = document.createElement('form');
							form.method = 'post';
							form.innerHTML = `
								<input type="hidden" name="gallery_id" value="${galleryId}">
								<input type="hidden" name="image_id" value="${imageId}">
								<input type="hidden" name="action" value="approve">
								<input type="hidden" name="moderate_image" value="confirmed_approve">
								<input type="hidden" name="approve_image_nonce" value="${nonce}">
							`;
							document.body.appendChild(form);
							form.submit();
						}
					});
				});			

			</script>

			<style>
				.image-actions {
					display: none;
				}

				tr:hover .image-actions {
					display: flex;
				}				
			</style>
			<?php
		}

	}

}
$custom_foogallery_meta_boxes = new FooGallery_FrontEnd_Upload_MetaBoxes();