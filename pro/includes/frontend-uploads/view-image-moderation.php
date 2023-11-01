<?php

/**
 * Class Foogallery_FrontEnd_Image_Moderation
 *
 * This class handles image moderation frontend UI.
 *
 * @package foogallery
 */

if ( ! class_exists( 'Foogallery_FrontEnd_Image_Moderation' ) ) {

	/**
	 * Class Foogallery_FrontEnd_Image_Moderation
	 *
	 * This class handles image moderation frontend UI.
	 *
	 * @package foogallery
	 */
	class Foogallery_FrontEnd_Image_Moderation {

		/**
		 * Foogallery_FrontEnd_Image_Moderation constructor.
		 *
		 * Initializes the class and registers necessary actions.
		 */
		public function __construct() {
			if ( is_admin() ) {
				$this->render_moderation_page();
			}
		}

		/**
		 * Render the moderation page.
		 *
		 * This method is responsible for rendering the moderation page where images can be moderated.
		 * It should output the HTML and user interface for image moderation.
		 */
		public function render_moderation_page() {
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

			// Handle filtering by gallery ID.
			$filter_gallery_id = isset( $_POST['filter_gallery_id'] ) ? intval( $_POST['filter_gallery_id'] ) : 0;
			// Get all unique gallery titles from the metadata.
			$gallery_titles = [];
			foreach ( $images_to_moderate as $gallery_id => $images ) {
				$gallery_post = get_post( $gallery_id );
				if ( $gallery_post ) {
					$gallery_title = $gallery_post->post_title;
					$gallery_titles[$gallery_title] = $gallery_id;
				}
			}

			// Sort the gallery titles alphabetically.
			ksort( $gallery_titles );
			?>

			<div class="wrap" id="image-moderation-container">
				

				<section style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
					<h2><?php esc_html_e( 'Image Moderation', 'foogallery' ); ?></h2>
					<!-- Gallery Title filter dropdown -->
					<form method="post" style="">
					<label for="filter_gallery_title"><?php esc_html_e( 'Filter by Gallery Title:', 'foogallery' ); ?></label>
					<select name="filter_gallery_title" id="filter_gallery_title">
						<option value=""><?php esc_html_e( 'All', 'foogallery' ); ?></option>
						<?php
						// Populate the dropdown with available gallery titles.
						foreach ( $gallery_titles as $gallery_title => $gallery_id ) {
							echo '<option value="' . esc_attr( $gallery_title ) . '">' . esc_html( $gallery_title ) . '</option>';
						}
						?>
					</select>
					</form>
				</section>

				<div id="pending-tab" class="tab-content">
					<table id="image-table" class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th style="width: 100px;"><?php esc_html_e( 'Image', 'foogallery' ); ?></th>
								<th><?php esc_html_e( 'Gallery', 'foogallery' ); ?></th>								
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
								if ( $filter_gallery_id === $gallery_id || 0 === $filter_gallery_id ) :
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
												<?php
												// Get the gallery post object.
												$gallery_post = get_post( $gallery_id );
												if ( $gallery_post ) {
													// Generate the URL for the gallery edit page.
													$gallery_edit_url = get_edit_post_link( $gallery_id );

													if ( $gallery_edit_url ) {
														echo '<a href="' . esc_url( $gallery_edit_url ) . '">' . esc_html( $gallery_post->post_title ) . '</a>';
													} else {
														echo esc_html( $gallery_post->post_title );
													}
												} else {
													// Display a fallback value if the gallery post is not found.
													echo esc_html( $gallery_id );
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
											</td>

										</tr>
										<?php
									endforeach;
								endif;
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
				</div>
			
			</div>

			<script>
				const filterDropdown = document.getElementById( 'filter_gallery_title' );
				const tableRows = document.querySelectorAll( '#image-table tbody tr' );

				filterDropdown.addEventListener( 'change', function () {
					const selectedGalleryTitle = this.value.toLowerCase().trim();

					tableRows.forEach(row => {
						const galleryTitleCell = row.querySelector( 'td:nth-child(2)' ); 

						if ( !galleryTitleCell ) {
							return;
						}

						const rowGalleryTitle = galleryTitleCell.textContent.trim().toLowerCase();

						if ( selectedGalleryTitle === '' || rowGalleryTitle.includes( selectedGalleryTitle ) ) {
							row.style.display = '';
						} else {
							row.style.display = 'none';
						}
					});
				});


				// Add event listeners for confirmation dialogs
				const confirmRejectButtons = document.querySelectorAll( '.confirm-reject' );
				confirmRejectButtons.forEach(button => {
					button.addEventListener('click', function (e) {
						e.preventDefault();

						const galleryId = this.getAttribute( 'data-gallery-id' );
						const imageId = this.getAttribute( 'data-image-id' );
						const nonce = this.getAttribute( 'data-nonce' );
						if ( confirm( `Are you sure you want to reject this image?` ) ) {
							const form = document.createElement( 'form' );
							form.method = 'post';
							form.innerHTML = `
								<input type="hidden" name="gallery_id" value="${galleryId}">
								<input type="hidden" name="image_id" value="${imageId}">
								<input type="hidden" name="action" value="reject">
								<input type="hidden" name="moderate_image" value="confirmed_reject">
								<input type="hidden" name="reject_image_nonce" value="${nonce}">
							`;
							document.body.appendChild( form );
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

$image_moderation = new Foogallery_FrontEnd_Image_Moderation();