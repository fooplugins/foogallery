<?php

 global $wp_filesystem;
 if (empty($wp_filesystem)) {
	 require_once ABSPATH . '/wp-admin/includes/file.php';
	 WP_Filesystem();
 }
 
// Include the necessary file for admin gallery metaboxes.
require_once FOOGALLERY_PATH . 'includes/admin/class-gallery-metaboxes.php';

if ( ! class_exists( 'FrontEnd_Upload_FooGallery_Admin_Gallery_MetaBoxes' ) ) {
    require_once FOOGALLERY_PATH . 'pro/includes/frontend-uploads/class-frontend-upload-foogallery-admin-gallery-metaboxes.php';
}

// Initialize the classes.
if ( class_exists('FooGallery_Admin_Gallery_MetaBoxes') ) {
    new FrontEnd_Upload_FooGallery_Admin_Gallery_MetaBoxes();
}

if ( class_exists( 'Foogallery_FrontEnd_Upload_Shortcode' ) ) {
    new Foogallery_FrontEnd_Upload_Shortcode();
}

// Add a sub-menu to the FooGallery menu
function add_image_moderation_submenu() {
    $parent_slug = foogallery_admin_menu_parent_slug();
    
    add_submenu_page(
        $parent_slug,
        'Moderation',
        'Moderation',
        'manage_options',
        'image-moderation',
        'render_image_moderation_page'
    );
}
add_action('admin_menu', 'add_image_moderation_submenu');

// Callback function to render the page content
function render_image_moderation_page() {
    require_once FOOGALLERY_PATH . 'pro/includes/frontend-uploads/image-moderation.php';
}

/**
 * @package foogallery
 *
 * FooGallery Image Upload Form Shortcode Class
 *
 * This class handles the rendering of an image upload form shortcode
 * and the processing of uploaded images.
 */

if ( ! class_exists( 'Foogallery_FrontEnd_Upload_Shortcode' ) ) {

	// Include the necessary file.
	require_once FOOGALLERY_PATH . 'pro/includes/frontend-uploads/class-frontend-upload-foogallery-admin-gallery-metaboxes.php';

	/**
	 * Class Foogallery_FrontEnd_Upload_Shortcode
	 *
	 * This class handles the rendering of the Foogallery Upload form shortcode and
	 * the processing of uploaded images.
	 */
	class Foogallery_FrontEnd_Upload_Shortcode {

		/**
		 * Constructor for the FooGallery_Image_Upload class.
		 *
		 * Initializes the necessary actions and filters.
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ) );
			add_shortcode( 'foogallery_upload', array( $this, 'render_image_upload_form' ) );
			add_action( 'init', array( $this, 'handle_image_upload' ) );
		}

		/**
		 * Function to enqueue scripts and styles.
		 */
		public function frontend_enqueue_scripts() {
			// Check if the page contains the [foogallery_upload] shortcode.
			if ( has_shortcode( get_post()->post_content, 'foogallery_upload' ) ) {
				$directory = plugin_dir_url( __FILE__ );
				wp_enqueue_style( 'frontend-uploads', $directory . 'foogallery-frontend-uploads.css', array(), '1.0' );
			}
		}


		/**
		 * Render the image upload form shortcode.
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string Rendered HTML output.
		 */
		public function render_image_upload_form( $atts ) {
			global $gallery_id;
			$gallery_id = isset( $atts['id'] ) ? intval( $atts['id'] ) : null;
			$output = '';

			// Check if the gallery_id attribute is provided.
			if ( ! $gallery_id ) {
				$output = __( 'Gallery ID not specified.', 'foogallery' );
			} else {
				$metafields = array( 'caption', 'description', 'alt', 'custom_url', 'custom_target' );
				$attributes = array();

				foreach ( $metafields as $metafield ) {
					$option_name = "_display_$metafield";
					$display_setting = get_post_meta( $gallery_id, $option_name, true );
					// Add the display setting as a data attribute.
					$attributes["data-display-$metafield"] = $display_setting;
				}



				ob_start();
				?>
				<form method="post" enctype="multipart/form-data">
					<div style="max-width: 500px; max-height: 200px; border: 1px dashed #999; text-align: center; padding: 20px; margin-top: 10px;">
						<input type="hidden" name="gallery_id" value="<?php echo esc_attr($gallery_id); ?>" />
						<input type="file" name="foogallery_images[]" id="image-upload" accept="image/*" multiple style="display: none;" />
						<label for="image-upload" style="cursor: pointer;">
							<p><?php esc_html_e( 'Click to browse or drag & drop image(s) here', 'foogallery' ); ?></p>
						</label>
					</div>
					
					<div class="foogallery-upload-popup-overlay" id="popup">
						<div class="foogallery-upload-popup-content">
							<span class="foogallery-upload-close-button" id="close-popup" style="font-size: 40px; color: white;">&times;</span>
							<div class="foogallery-upload-popup-inner">
								<div class="foogallery-upload-left-column">
									<div class="foogallery-upload-image-grid" id="uploaded-images">
										<!-- Uploaded images displayed here -->
									</div>
									<div style="margin-top: 10px;">
										<input type="submit" class="foogallery-image-upload-button" name="foogallery_image_upload" value="Upload Images" />
									</div>
								</div>
								<div class="foogallery-upload-right-column">
									<div id="metadata-container" style="padding: 5px 7px;" <?php foreach ($attributes as $key => $value) { echo "$key=\"$value\" "; } ?>>
										<!-- Metadata input fields added here dynamically -->
									</div>									
								</div>
							</div>
						</div>
					</div>				
				</form>

				<style>
					.foogallery-upload-popup-inner {
						display: flex;
						flex-direction: column;
						width: 100%;
					}
					.foogallery-upload-left-column {
						width: 100%;
					}

					.foogallery-upload-right-column {
						display: none;
						width: 100%;
					}

					.foogallery-upload-image-grid {
						display: flex;						
						flex-direction: column;
					}

					.foogallery-image-upload-button {
						background-color: #0073e6;
						color: #fff;
						border: none;
						padding: 10px 20px;
						font-size: 16px;
						cursor: pointer;
						border-radius: 4px;
						transition: background-color 0.3s ease;
						box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
						display: block;
						margin: 0 auto;
					}

					.foogallery-image-upload-button:hover {
						background-color: #0056b3;
					}
				</style>

				<script>
					const imageUploadInput = document.getElementById('image-upload');
					const metadataContainer = document.getElementById('metadata-container');
					const popup = document.getElementById('popup');
					const closePopupButton = document.getElementById('close-popup');
					const uploadedImagesContainer = document.getElementById('uploaded-images');
					const uploadForm = document.querySelector('form');

					imageUploadInput.addEventListener('change', function () {
						if (this.files.length > 0) {
							addMetadataFields(this.files.length);
							displayPopup();
							displayUploadedImages(this.files);
						}
					});

					
					document.addEventListener('dragover', function (e) {
						e.preventDefault();
						e.stopPropagation();
					});

					document.addEventListener('drop', function (e) {
						e.preventDefault();
						e.stopPropagation();

						if (e.dataTransfer.files.length > 0) {
							addMetadataFields(e.dataTransfer.files.length);
							displayPopup();
							displayUploadedImages(e.dataTransfer.files);
						}
					});

					closePopupButton.addEventListener('click', function () {
						closePopup();
					});

					function addMetadataFields(numImages) {
						metadataContainer.innerHTML = '';

						for (let i = 0; i < numImages; i++) {
							const metadataFields = `
								<div class="metadata-fields" style="margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9;">
									<h4 style="margin: 0;">Image ${i + 1}</h4>
									${metadataContainer.getAttribute('data-display-caption') === 'on' ? `
										<div class="metadata-field" style="margin-bottom: 10px;  padding: 10px;">
											<label for="caption_${i}" style="display: block; font-weight: bold; margin-bottom: 5px;">Caption:</label>
											<input type="text" class="metadata-input" style="width: 100%; height: 20px; padding: 5px; border: 1px solid #ccc; border-radius: 2px;" name="caption[]" id="caption_${i}" />
										</div>` : ''}
									
									${metadataContainer.getAttribute('data-display-description') === 'on' ? `
										<div class="metadata-field" style="margin-bottom: 7px; padding: 10px;">
											<label for="description_${i}" style="display: block; font-weight: bold; margin-bottom: 5px;">Description:</label>
											<textarea class="metadata-textarea" style="width: 100%; height: 80px;  padding: 5px; border: 1px solid #ccc; resize: vertical; border-radius: 3px;" name="description[]" id="description_${i}"></textarea>
										</div>` : ''}
									
									${metadataContainer.getAttribute('data-display-alt') === 'on' ? `
										<div class="metadata-field" style="margin-bottom: 7px; padding: 10px;">
											<label for="alt_${i}" style="display: block; font-weight: bold; margin-bottom: 5px;">Alt Text:</label>
											<input type="text" class="metadata-input" style="width: 100%; height: 20px; padding: 5px; border: 1px solid #ccc; border-radius: 2px;" name="alt[]" id="alt_${i}" />
										</div>` : ''}
									
									${metadataContainer.getAttribute('data-display-custom_url') === 'on' ? `
										<div class="metadata-field" style="margin-bottom: 7px; padding: 10px;">
											<label for="custom_url_${i}" style="display: block; font-weight: bold; margin-bottom: 5px;">Custom URL:</label>
											<input type="text" class="metadata-input" style="width: 100%; height: 20px; padding: 5px; border: 1px solid #ccc; border-radius: 2px;" name="custom_url[]" id="custom_url_${i}" />
										</div>` : ''}
									
									${metadataContainer.getAttribute('data-display-custom_target') === 'on' ? `
										<div class="metadata-field" style="margin-bottom: 7px; padding: 10px;">
											<label for="custom_target_${i}" style="display: block; font-weight: bold; margin-bottom: 5px;">Custom Target:</label>
											<input type="text" class="metadata-input" style="width: 100%; height: 20px; padding: 5px; border: 1px solid #ccc; border-radius: 2px;" name="custom_target[]" id="custom_target_${i}" />
										</div>` : ''}
								</div>
							`;
							metadataContainer.innerHTML += metadataFields;
						}
					}

					function displayPopup() {
						popup.style.display = 'flex';
					}

					function closePopup() {
						popup.style.display = 'none';
					}

					function displayUploadedImages(files) {
						uploadedImagesContainer.innerHTML = '';

						for (let i = 0; i < files.length; i++) {
							const file = files[i];
							if (file.type.startsWith('image/')) {
							const metadataFields = `
								<div class="image-metadata" style="display: flex; flex-direction: row; align-items: center; margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9;">
									<div class="image-preview" style="margin-right: 20px; width: 100%;">
										<img style="width: 100%; height: 100%; object-fit: cover;" src="${URL.createObjectURL(file)}"  alt="Image Preview" />
									</div>
									<div class="metadata-fields" style="width: 100%;">
										${metadataContainer.getAttribute('data-display-caption') === 'on' ? `
											<div class="metadata-field" style="margin-bottom: 7px; padding: 10px;">
												<label for="caption_${i}" style="display: block; font-weight: bold; margin-bottom: 5px;">Caption:</label>
												<input type="text" class="metadata-input" style="width: 100%; height: 20px; padding: 5px; border: 1px solid #ccc; border-radius: 2px;" name="caption[]" id="caption_${i}" />
											</div>` : ''}
										
										${metadataContainer.getAttribute('data-display-description') === 'on' ? `
											<div class="metadata-field" style="margin-bottom: 7px; padding: 10px;">
												<label for="description_${i}" style="display: block; font-weight: bold; margin-bottom: 5px;">Description:</label>
												<textarea class="metadata-textarea" style="width: 100%; height: 40px;  padding: 5px; border: 1px solid #ccc; resize: vertical; border-radius: 3px;" name="description[]" id="description_${i}"></textarea>
											</div>` : ''}
										
										${metadataContainer.getAttribute('data-display-alt') === 'on' ? `
											<div class="metadata-field" style="margin-bottom: 7px; padding: 10px;">
												<label for="alt_${i}" style="display: block; font-weight: bold; margin-bottom: 5px;">Alt Text:</label>
												<input type="text" class="metadata-input" style="width: 100%; height: 20px; padding: 5px; border: 1px solid #ccc; border-radius: 2px;" name="alt[]" id="alt_${i}" />
											</div>` : ''}
										
										${metadataContainer.getAttribute('data-display-custom_url') === 'on' ? `
											<div class="metadata-field" style="margin-bottom: 7px; padding: 10px;">
												<label for="custom_url_${i}" style="display: block; font-weight: bold; margin-bottom: 5px;">Custom URL:</label>
												<input type="text" class="metadata-input" style="width: 100%; height: 20px; padding: 5px; border: 1px solid #ccc; border-radius: 2px;" name="custom_url[]" id="custom_url_${i}" />
											</div>` : ''}
										
										${metadataContainer.getAttribute('data-display-custom_target') === 'on' ? `
											<div class="metadata-field" style="margin-bottom: 7px; padding: 10px;">
												<label for="custom_target_${i}" style="display: block; font-weight: bold; margin-bottom: 5px;">Custom Target:</label>
												<input type="text" class="metadata-input" style="width: 100%; height: 20px; padding: 5px; border: 1px solid #ccc; border-radius: 2px;" name="custom_target[]" id="custom_target_${i}" />
											</div>` : ''}
									</div>
								</div>
							`;
							uploadedImagesContainer.innerHTML += metadataFields;
							}
						}
					}

					uploadForm.addEventListener('submit', function (e) {
						e.preventDefault();
					});

					setTimeout(function () {
						document.querySelector(".success-message").style.display = "none";
					}, 3000);
				</script>

				<?php
				$output .= ob_get_clean();
			}

			return $output;
		}

		/**
		 * Handle the uploaded images.
		 */
		public function handle_image_upload() {
			global $gallery_id;

			// Check if the form was submitted.
			if ( isset( $_POST['foogallery_image_upload'] ) ) {
				// Get the gallery ID from the form data.
				$gallery_id = isset( $_POST['gallery_id'] ) ? intval( $_POST['gallery_id'] ) : null;

				// Check if files were uploaded.
				if ( isset( $_FILES['foogallery_images'] ) ) {
					$uploaded_files = $_FILES['foogallery_images'];

					// User folder to store the uploaded images.
					$user_folder = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/';

					// Create the user folder if it doesn't exist.
					if ( ! file_exists( $user_folder ) ) {
						if ( wp_mkdir_p( $user_folder ) ) {
							chmod( $user_folder, 0755 );
						} else {
							echo '<div class="error-message" style="color: red; text-align: center;">' . __( 'Error creating the user folder.', 'foogallery' ) . '</div>';
							return;
						}
					}

					foreach ( $uploaded_files['name'] as $key => $filename ) {
						// Check if the file is an image.
						if ( $uploaded_files['type'][$key] && strpos( $uploaded_files['type'][$key], 'image/' ) === 0 ) {
							// Generate a unique file name for the uploaded image in the user folder.
							$unique_filename = wp_unique_filename( $user_folder, $filename );
							$user_file = $user_folder . $unique_filename;

							// Move the uploaded file to the user folder.
							if ( move_uploaded_file( $uploaded_files['tmp_name'][$key], $user_file ) ) {

								$image_metadata = array(
									"file" => $unique_filename,
									"gallery_id" => $gallery_id,
									"caption" => isset($_POST['caption'][$key]) ? sanitize_text_field($_POST['caption'][$key]) : "",
									"description" => isset($_POST['description'][$key]) ? sanitize_text_field($_POST['description'][$key]) : "",
									"alt" => isset($_POST['alt'][$key]) ? sanitize_text_field($_POST['alt'][$key]) : "",
									"custom_url" => isset($_POST['custom_url'][$key]) ? esc_url($_POST['custom_url'][$key]) : "",
									"custom_target" => isset($_POST['custom_target'][$key]) ? sanitize_text_field($_POST['custom_target'][$key]) : ""
								);

								global $wp_filesystem;
								$metadata_file = $user_folder . 'metadata.json';
								$existing_metadata = file_exists($metadata_file) ? @json_decode( $wp_filesystem->get_contents( $metadata_file ), true ) : array("items" => array());
								
								// Add the new image's metadata to the array.
								$existing_metadata["items"][] = $image_metadata;

								// Encode the metadata as JSON and save it to the metadata file.
								file_put_contents( $metadata_file, json_encode( $existing_metadata, JSON_PRETTY_PRINT ) );
							} else {
								echo '<div class="error-message" style="color: red; text-align: center;">' . __( 'Error moving the file(s).', 'foogallery' ) . '</div>';
							}
						} else {
							echo '<div class="error-message" style="color: red; text-align: center;">' . __( 'File is not an image.', 'foogallery' ) . '</div>';
						}
					}

					echo '<div class="success-message" style="color: green; text-align: center;">' . __( 'Image(s) successfully uploaded and awaiting moderation.', 'foogallery' ) . '</div>';
				} else {
					echo '<div class="error-message" style="color: red; text-align: center;">' . __( 'No files uploaded or an error occurred.', 'foogallery' ) . '</div>';
				}
			}
		}

	}
}
