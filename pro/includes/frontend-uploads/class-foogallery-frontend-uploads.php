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
                $upload_success_transient = get_transient('foogallery_upload_success_' . $gallery_id);
                if ($upload_success_transient) {
                    $output = 'Image uploaded successfully.';
                    delete_transient('foogallery_upload_success_' . $gallery_id);
                }                
                ob_start();
                ?>
                <form method="post" enctype="multipart/form-data">
                    <div style="max-width: 500px; max-height: 200px; border: 1px dashed #999; text-align: center; padding: 20px; margin-top: 10px;">
                        <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gallery_id); ?>" />
                        <input type="file" name="foogallery_image" id="image-upload" accept="image/*" style="display: none;" />
                        <label for="image-upload" style="cursor: pointer;">
                            <p>Click to <span style="text-decoration: underline;">browse</span> or drag & drop image here</p>
                        </label>
                    </div>

                    <!-- Pop-up content -->
                    <div class="popup-overlay" id="popup">
                        <div class="popup-content">
                            <span class="close-button" id="close-popup">&times;</span>
                            <div class="popup-inner">
                                <div class="left-column">
                                    <div class="image-grid" id="uploaded-images">
                                        <!-- Image displayed here -->
                                    </div>
                                </div>
                                <div class="right-column">
                                    <div>
                                        <label for="caption">Caption:</label>
                                        <input type="text" name="caption" id="caption" />
                                    </div>
                                    <div>
                                        <label for="description">Description:</label>
                                        <textarea name="description" id="description"></textarea>
                                    </div>
                                    <div>
                                        <label for="alt">Alt Text:</label>
                                        <input type="text" name="alt" id="alt" />
                                    </div>
                                    <div>
                                        <label for="custom_url">Custom URL:</label>
                                        <input type="text" name="custom_url" id="custom_url" />
                                    </div>
                                    <div>
                                        <label for="custom_target">Custom Target:</label>
                                        <input type="text" name="custom_target" id="custom_target" />
                                    </div>
                                    <div style="margin-top: 10px;">
                                        <input type="submit" name="foogallery_image_upload" value="Upload Image" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>               


                <script>
                    const imageUploadInput = document.getElementById('image-upload');
                    const popup = document.getElementById('popup');
                    const closePopupButton = document.getElementById('close-popup');
                    const uploadedImagesContainer = document.getElementById('uploaded-images');

                    imageUploadInput.addEventListener('change', function () {
                        if (this.files.length > 0) {
                            displayPopup();
                            displayUploadedImages(this.files);
                        }
                    });

                    closePopupButton.addEventListener('click', function () {
                        closePopup();
                    });

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
            if (isset($_POST['foogallery_image_upload'])) {
                $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;

                // Check if the gallery_id is valid.
                if (!$gallery_id) {
                    echo 'Gallery ID not specified.';
                    return;
                }

                // Handle image upload and metadata here.
                if (isset($_FILES['foogallery_image']) && !empty($_FILES['foogallery_image']['tmp_name'])) {
                    $uploaded_file = $_FILES['foogallery_image']['tmp_name'];

                    // Specify the server folder where you want to save the image.
                    $upload_folder = plugin_dir_path(__FILE__) . 'uploads/' . $gallery_id . '/';

                    // Create the folder if it doesn't exist.
                    if (!file_exists($upload_folder)) {
                        mkdir($upload_folder, 0755, true);
                    }

                    // Generate a unique filename or use the original filename.
                    $filename = wp_unique_filename($upload_folder, $_FILES['foogallery_image']['name']);

                    // Move the uploaded file to the server folder.
                    move_uploaded_file($uploaded_file, $upload_folder . $filename);

                    // Create metadata array.
                    $metadata = array(
                        'items' => array(
                            array(
                                'file' => $filename,
                                'caption' => sanitize_text_field($_POST['caption']),
                                'description' => sanitize_text_field($_POST['description']),
                                'alt' => sanitize_text_field($_POST['alt']),
                                'custom_url' => esc_url($_POST['custom_url']),
                                'custom_target' => sanitize_text_field($_POST['custom_target']),
                            )
                        )
                    );

                    // Encode metadata as JSON.
                    $metadata_json = json_encode($metadata, JSON_PRETTY_PRINT);

                    // Save metadata to metadata.json file.
                    file_put_contents($upload_folder . 'metadata.json', $metadata_json);

                    // TODO: Populate the gallery dynamically
                    // TODO: Moderate uploaded images
                }
            }
        }
    }
}
