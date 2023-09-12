<?php
/**
 * FooGallery Image Upload Form Shortcode Class
 */
if ( ! class_exists( 'FooGallery_Image_Upload_Form_Shortcode' ) ) {

    class FooGallery_Image_Upload_Form_Shortcode {

        function __construct() {
            add_action( 'wp_enqueue_scripts', array($this,'frontendEnqueueScripts') );
            add_shortcode('foogallery_image_upload_form', array($this, 'render_image_upload_form'));
            add_action('init', array($this, 'handle_image_upload'));
        }
    
        /**
         * Function to enqueue scripts and styles.
         */
        public function frontendEnqueueScripts() {
            $directory = plugin_dir_url(__FILE__);
        
            wp_enqueue_style('frontend-uploads', $directory . 'foogallery-frontend-uploads.css', array(), '1.0');
        }    

        /**
         * Render the image upload form shortcode
         * @param $atts
         * @return string
         */
        function render_image_upload_form($atts) {
            $gallery_id = isset($atts['gallery_id']) ? intval($atts['gallery_id']) : null;
            $output = '';

            // Check if the gallery_id attribute is provided
            if ( ! $gallery_id) {
                $output = 'Gallery ID not specified.';
            } else {
                ob_start();
                ?>
                <form method="post" enctype="multipart/form-data">
                    <div style="max-width: 500px; max-height: 200px; border: 1px dashed #999; text-align: center; padding: 20px; margin-top: 10px;">
                        <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gallery_id); ?>" />
                        <input type="file" name="foogallery_images[]" id="image-upload" accept="image/*" multiple style="display: none;" />
                        <label for="image-upload" style="cursor: pointer;">
                            <p>Click to <span style="text-decoration: underline;">browse</span> or drag & drop image(s) here</p>
                        </label>
                    </div>
                    
                    <div class="popup-overlay" id="popup">
                        <div class="popup-content">
                            <span class="close-button" id="close-popup">&times;</span>
                            <div class="popup-inner">
                                <div class="left-column">
                                    <div class="image-grid" id="uploaded-images">
                                        <!-- Uploaded images displayed here -->
                                    </div>
                                </div>
                                <div class="right-column">
                                    <div id="metadata-container">
                                        <!-- Metadata input fields added here dynamically -->
                                    </div>
                                    <div style="margin-top: 10px;">
                                        <input type="submit" name="foogallery_image_upload" value="Upload Images" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>

                <script>
                    const imageUploadInput = document.getElementById('image-upload');
                    const metadataContainer = document.getElementById('metadata-container');
                    const popup = document.getElementById('popup');
                    const closePopupButton = document.getElementById('close-popup');
                    const uploadedImagesContainer = document.getElementById('uploaded-images');

                    imageUploadInput.addEventListener('change', function () {
                        if (this.files.length > 0) {
                            addMetadataFields(this.files.length);
                            displayPopup();
                            displayUploadedImages(this.files);
                        }
                    });

                    closePopupButton.addEventListener('click', function () {
                        closePopup();
                    });

                    function addMetadataFields(numImages) {
                        metadataContainer.innerHTML = '';

                        for (let i = 0; i < numImages; i++) {
                            const metadataFields = `
                                <div class="metadata-fields" style="margin-bottom: 10px;">
                                    <label for="caption_${i}">Caption:</label>
                                    <input type="text" name="caption[]" id="caption_${i}" />

                                    <label for="description_${i}">Description:</label>
                                    <textarea name="description[]" id="description_${i}"></textarea>

                                    <label for="alt_${i}">Alt Text:</label>
                                    <input type="text" name="alt[]" id="alt_${i}" />

                                    <label for="custom_url_${i}">Custom URL:</label>
                                    <input type="text" name="custom_url[]" id="custom_url_${i}" />

                                    <label for="custom_target_${i}">Custom Target:</label>
                                    <input type="text" name="custom_target[]" id="custom_target_${i}" />
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

                        for (const file of files) {
                            if (file.type.startsWith('image/')) {
                                const img = document.createElement('img');
                                img.src = URL.createObjectURL(file);
                                uploadedImagesContainer.appendChild(img);
                            }
                        }
                    }
                </script>
                <?php
                $output .= ob_get_clean();
            }

            return $output;
        }

        /**
         * Handle the image upload
         */
        function handle_image_upload() {
            // Check if the form was submitted
            if (isset($_POST['foogallery_image_upload'])) {
                // Get the gallery ID from the form data
                $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;

                // Check if files were uploaded
                if (isset($_FILES['foogallery_images'])) {
                    $uploaded_files = $_FILES['foogallery_images'];

                    // server folder to save the uploaded images
                    $upload_dir = wp_upload_dir(); // Get the default WordPress uploads directory
                    $gallery_folder = $upload_dir['basedir'] . '/users_uploads/' . $gallery_id . '/';

                    // Create the gallery folder if it doesn't exist
                    if (!file_exists($gallery_folder)) {
                        wp_mkdir_p($gallery_folder);
                    }

                    // Loop through uploaded files
                    foreach ($uploaded_files['name'] as $key => $filename) {
                        // Check if the file is an image
                        if ($uploaded_files['type'][$key] && strpos($uploaded_files['type'][$key], 'image/') === 0) {
                            // Generate a unique file name for the uploaded image
                            $unique_filename = wp_unique_filename($gallery_folder, $filename);
                            $target_file = $gallery_folder . $unique_filename;

                            // Move the uploaded file to the target directory
                            if (move_uploaded_file($uploaded_files['tmp_name'][$key], $target_file)) {
                                // Create an array to store image metadata
                                $image_metadata = array(
                                    "file" => $unique_filename,
                                    "caption" => isset($_POST['caption'][$key]) ? sanitize_text_field($_POST['caption'][$key]) : "",
                                    "description" => isset($_POST['description'][$key]) ? sanitize_text_field($_POST['description'][$key]) : "",
                                    "alt" => isset($_POST['alt'][$key]) ? sanitize_text_field($_POST['alt'][$key]) : "",
                                    "custom_url" => isset($_POST['custom_url'][$key]) ? esc_url($_POST['custom_url'][$key]) : "",
                                    "custom_target" => isset($_POST['custom_target'][$key]) ? sanitize_text_field($_POST['custom_target'][$key]) : ""
                                );

                                // Load existing metadata if it exists.
                                $metadata_file = $gallery_folder . 'metadata.json';
                                $existing_metadata = file_exists($metadata_file) ? json_decode(file_get_contents($metadata_file), true) : array("items" => array());

                                // Add the new image's metadata to the array.
                                $existing_metadata["items"][] = $image_metadata;

                                // Encode the metadata as JSON and save it to the metadata file.
                                file_put_contents($metadata_file, json_encode($existing_metadata, JSON_PRETTY_PRINT));
                            } else {
                                echo 'Error uploading the file.';
                            }
                        } else {
                            echo 'File is not an image.';
                        }
                    }

                    // Display a success message.
                    set_transient('foogallery_upload_success_' . $gallery_id, true, 10);
                    exit();
                } else {
                    echo 'No files uploaded or an error occurred.';
                }
            }
        }
    }
}
?>
