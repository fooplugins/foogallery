<?php
// Check if the form is submitted for image moderation
if (isset($_POST['moderate_image'])) {
    $image_id = sanitize_text_field($_POST['image_id']);
    $action = sanitize_text_field($_POST['action']);
    
    if ($action === 'approve') {
        // Get the gallery ID and file name from the form data
        $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;
        $file_name = isset($_POST['image_id']) ? sanitize_text_field($_POST['image_id']) : null;
        
        if ($gallery_id && $file_name) {
            // Define the paths
            $original_folder = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/';
            $approved_folder = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/approved_uploads/';
            $metadata_file = $original_folder . 'metadata.json';
            $new_metadata_file = $approved_folder . 'metadata.json';
            
            // Function to retrieve and merge attachments with the specific approved image
            function merge_attachment_with_uploaded_images($gallery_id, $approved_image, $original_folder, $approved_folder, $metadata_file, $new_metadata_file) {
                // Get the existing attachments for the gallery
                $existing_attachments = get_post_meta($gallery_id, FOOGALLERY_META_ATTACHMENTS, true);
                
                // Get the uploaded image's file name from metadata
                $uploaded_images = array();
                
                if (file_exists($metadata_file)) {
                    global $wp_filesystem;
                    $metadata = @json_decode($wp_filesystem->get_contents($metadata_file), true);
                    if (isset($metadata['items']) && is_array($metadata['items'])) {
                        foreach ($metadata['items'] as $item) {
                            if (isset($item['file']) && $item['file'] === $approved_image) {
                                $uploaded_images[] = $item;
                            }
                        }
                    }
                }
                
                // Move the file to the approved folder
                if (!file_exists($approved_folder)) {
                    mkdir($approved_folder, 0755, true);
                }
                
                if (file_exists($original_folder . $approved_image)) {
                    rename($original_folder . $approved_image, $approved_folder . $approved_image);
                }
                
                // Update the metadata JSON file in the new folder
                if (file_exists($new_metadata_file)) {
                    $new_metadata = @json_decode($wp_filesystem->get_contents($new_metadata_file), true);
                } else {
                    $new_metadata = ['items' => []];
                }
                
                foreach ($uploaded_images as $image) {
                    $new_metadata['items'][] = $image;
                }
                
                file_put_contents($new_metadata_file, json_encode($new_metadata, JSON_PRETTY_PRINT));
                
                // Remove the image and its metadata from the original metadata JSON file
                if (file_exists($metadata_file)) {
                    $metadata = @json_decode($wp_filesystem->get_contents($metadata_file), true);
                    $metadata['items'] = array_filter($metadata['items'], function ($item) use ($approved_image) {
                        return $item['file'] !== $approved_image;
                    });
                    
                    file_put_contents($metadata_file, json_encode($metadata, JSON_PRETTY_PRINT));
                }
                
                // Merge the existing attachments with the uploaded images
                $merged_attachments = array_merge($existing_attachments, $uploaded_images);
                
                // Update the gallery's attachments with the merged array
                update_post_meta($gallery_id, FOOGALLERY_META_ATTACHMENTS, $merged_attachments);
                
                // echo '<div class="notice notice-success"><p>' . __('Image approved and added to the gallery successfully.', 'foogallery') . '</p></div>';
            }
            
            // Call the function with the required parameters
            merge_attachment_with_uploaded_images($gallery_id, $file_name, $original_folder, $approved_folder, $metadata_file, $new_metadata_file);
        }
    } elseif ($action === 'reject') {
        // Get the gallery ID and file name from the form data
        $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;
        $file_name = isset($_POST['image_id']) ? sanitize_text_field($_POST['image_id']) : null;
        
        if ($gallery_id && $file_name) {
            // Delete the image file from the server
            $user_folder = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/';
            $deleted = unlink($user_folder . $file_name);
            
            if ($deleted) {
                // Remove the metadata entry for the rejected image
                $metadata_file = $user_folder . 'metadata.json';
                if (file_exists($metadata_file)) {
                    $existing_metadata = @json_decode($wp_filesystem->get_contents($metadata_file), true);
                    $existing_metadata['items'] = array_filter($existing_metadata['items'], function ($item) use ($file_name) {
                        return $item['file'] !== $file_name;
                    });
                    file_put_contents($metadata_file, json_encode($existing_metadata, JSON_PRETTY_PRINT));
                }
                
                // Show a success message
                // echo '<div class="notice notice-success"><p>' . __('Image successfully rejected', 'foogallery') . '</p></div>';
            }
        }
    }
}

// Initialize an array to store gallery IDs and metadata
$images_to_moderate = array();

// Get the base directory for uploads
$upload_dir = wp_upload_dir();
$user_uploads_dir = $upload_dir['basedir'] . '/users_uploads/';

// Check if the user uploads directory exists
if (is_dir($user_uploads_dir)) {
    // Get a list of directories inside the user uploads directory
    $directories = glob($user_uploads_dir . '*', GLOB_ONLYDIR);
    
    foreach ($directories as $directory) {
        // Extract the gallery ID from the directory name
        $gallery_id = intval(basename($directory));
        $metadata_file = $directory . '/metadata.json';
        
        // Check if the metadata file exists
        if (file_exists($metadata_file)) {
            global $wp_filesystem;
            
            // Read and decode the JSON metadata file
            $metadata_contents = $wp_filesystem->get_contents($metadata_file);
            
            if ($metadata_contents !== false) {
                $metadata = json_decode($metadata_contents, true);
                
                if ($metadata !== null && isset($metadata['items'])) {
                    // Store the metadata in the images_to_moderate array
                    $images_to_moderate[$gallery_id] = $metadata['items'];
                } else {
                    // Handle JSON decoding failure or missing 'items' key
                    echo '<div class="notice notice-error"><p>Invalid or missing metadata in file: ' . esc_html($metadata_file) . '</p></div>';
                }
            } else {
                // Handle file read failure
                echo '<div class="notice notice-error"><p>Failed to read metadata file: ' . esc_html($metadata_file) . '</p></div>';
            }
        }
    }
}

/**
 * Class FooGallery_FrontEnd_Upload_MetaBoxes
 *
 * @package fooplugins
 */
class FooGallery_FrontEnd_Upload_MetaBoxes extends FooGallery_Admin_Gallery_MetaBoxes {
    private $gallery_id;

    /**
     * Constructor for the FooGallery_FrontEnd_Upload_MetaBoxes class.
     * Initializes the necessary actions and filters.
     */
    public function __construct() {
        parent::__construct();
        $this->gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;

        // Hook to save metadata checkboxes and settings.
        add_action('save_post', array($this, 'save_metadata_checkboxes'));
        add_action('save_post', array($this, 'save_frontend_upload_metabox_settings'));
    }

    /**
     * Add meta boxes to the gallery post type.
     *
     * @param WP_Post $post The current post object.
     */
    public function add_meta_boxes_to_gallery($post) {
        parent::add_meta_boxes_to_gallery($post);

        add_meta_box(
            'custom_metabox_id',
            __('Front End Upload', 'foogallery'),
            array($this, 'render_frontend_upload_metabox'),
            FOOGALLERY_CPT_GALLERY,
            'normal',
            'low'
        );
    }

    /**
     * Save metadata checkboxes when the gallery post is saved.
     *
     * @param int $post_id The ID of the saved post.
     */
    public function save_metadata_checkboxes($post_id) {
        if (get_post_type($post_id) === FOOGALLERY_CPT_GALLERY) {
            // Update post meta for the metadata checkboxes.
            $metafields = array('caption', 'description', 'alt', 'custom_url', 'custom_target');
            foreach ($metafields as $metafield) {
                $metafield_value = isset($_POST["display_$metafield"]) ? 'on' : 'off';
                update_post_meta($post_id, "_display_$metafield", $metafield_value);
            }
        }
    }

    function save_frontend_upload_metabox_settings($post_id) {        
        if (get_post_type($post_id) === FOOGALLERY_CPT_GALLERY) {
            // Save the maximum images allowed setting.
            if (isset($_POST['max_images_allowed'])) {
                update_post_meta($post_id, '_max_images_allowed', sanitize_text_field($_POST['max_images_allowed']));
            }
    
            if (isset($_POST['logged_in_users_only'])) {
                update_post_meta($post_id, '_logged_in_users_only', 'on');
            } else {
                delete_post_meta($post_id, '_logged_in_users_only');
            }
        }
    }

    /**
     * Render the frontend upload metabox.
     *
     * @param WP_Post $post The current post object.
     */
    public function render_frontend_upload_metabox($post) {
        $gallery = $this->get_gallery($post);
        $shortcode = $gallery->shortcode();

        // Use preg_match to find the ID within the shortcode.
        if (preg_match('/\[foogallery id="(\d+)"\]/', $shortcode, $matches)) {
            $gallery_id = $matches[1];
            ?>
            <p class="" style="display: flex; justify-content:center; align-items:center;" >
                <input style="border: 0; padding: 7px 10px;" type="text" id="Upload_Form_copy_shortcode" size="<?php echo strlen($shortcode) + 2; ?>" value="<?php echo esc_attr(htmlspecialchars('[foogallery_upload id="' . $gallery_id . '"]')); ?>" readonly="readonly" />
                <input type="hidden" id="gallery_id" value="<?php echo esc_attr($gallery_id); ?>" />
            </p>

            <p>
                <?php esc_html_e('Paste the above shortcode into a post or page to show the Image Upload Form.', 'foogallery'); ?>
            </p>

            <div id="metadata-settings">
                <?php
                // Retrieve existing values from the database
                $max_images_allowed = get_post_meta($post->ID, '_max_images_allowed', true);
                $max_image_size = get_post_meta($post->ID, '_max_image_size', true);

                // Output the HTML for the fields
                ?>
                <h3><?php esc_html_e('Upload Form Settings.', 'foogallery'); ?></h3>
                <label for="max_images_allowed" class="foogallery-upload-settings-input-label">Maximum Images Allowed:</label>
                <input type="number" id="max_images_allowed" name="max_images_allowed" value="<?php echo esc_attr($max_images_allowed); ?>" class="foogallery-upload-settings-input-field" />

                <label for="max_image_size" class="foogallery-upload-settings-input-label">Maximum Image Size (in KB):</label>
                <input type="number" id="max_image_size" name="max_image_size" step="100" value="<?php echo esc_attr($max_image_size); ?>" class="foogallery-upload-settings-input-field" />
                <?php
                $logged_in_users_only = get_post_meta($post->ID, '_logged_in_users_only', true);
                ?>
                <p>
                    <label for="logged-in-users-only">
                        <input type="checkbox" id="logged-in-users-only" name="logged_in_users_only" <?php checked($logged_in_users_only, 'on'); ?> />
                        <?php esc_html_e('Only logged-in users can upload', 'foogallery'); ?>
                    </label>
                </p>   

                <h4><?php esc_html_e('Check to display the metadata fields in the upload form.', 'foogallery'); ?></h4>
                <?php
                $metafields = array('caption', 'description', 'alt', 'custom_url', 'custom_target');
                foreach ($metafields as $metafield) {
                    $option_name = "_display_$metafield";
                    $metafield_value = get_post_meta($gallery_id, $option_name, true);
                    ?>
                    <label>
                        <input type="checkbox" id="display_<?php echo esc_attr($metafield); ?>" name="display_<?php echo esc_attr($metafield); ?>" <?php checked($metafield_value, 'on'); ?> />
                        <?php esc_html_e("Display $metafield", 'foogallery'); ?>
                    </label>
                    <br />
                <?php }?>
 
            </div>

            <div id="image-moderation">
                <h4><?php esc_html_e('Images Awaiting Moderation', 'foogallery'); ?></h4>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Image', 'foogallery'); ?></th>
                            <th><?php esc_html_e('Action', 'foogallery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Retrieve images associated with the gallery for approval or rejection.
                        $images_to_moderate = $this->getImagesToModerate($gallery_id);
                        foreach ($images_to_moderate as $image) {
                            ?>
                            <tr>
                                <td>
                                    <img style="width: 100px; height: 100px;" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
                                </td>
                                <td>
                                    <button class="approve-image button button-primary" data-image-id="<?php echo esc_attr($image['id']); ?>"><?php esc_html_e('Approve', 'foogallery'); ?></button>
                                    <button class="reject-image button" data-image-id="<?php echo esc_attr($image['id']); ?>"><?php esc_html_e('Reject', 'foogallery'); ?></button>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <style>
                .foogallery-upload-settings-input-label {
                    display: block;
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                .foogallery-upload-settings-input-field {
                    width: 50%;
                    padding: 10px;
                    margin-bottom: 15px;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                    font-size: 16px;
                }
                .foogallery-upload-settings-input-field:focus {
                    outline: none;
                    border-color: #007BFF;
                    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
                }

            </style>

            <script>
                // Add event listeners for image moderation actions
                const approveImageButtons = document.querySelectorAll('.approve-image');
                approveImageButtons.forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
                        const imageId = this.getAttribute('data-image-id');
                        if (confirm(`Are you sure you want to approve this image?`)) {
                            // Submit the approval form
                            submitModerationForm('approve', imageId);
                        }
                    });
                });

                const rejectImageButtons = document.querySelectorAll('.reject-image');
                rejectImageButtons.forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
                        const imageId = this.getAttribute('data-image-id');
                        if (confirm(`Are you sure you want to reject this image?`)) {
                            // Submit the rejection form
                            submitModerationForm('reject', imageId);
                        }
                    });
                });

                // Function to submit the moderation form
				function submitModerationForm(action, imageId) {
					const galleryId = document.getElementById('gallery_id').value;
					const currentPageUrl = window.location.href;
					const form = document.createElement('form');
					form.method = 'post';
					form.action = currentPageUrl;
					form.innerHTML = `
						<input type="hidden" name="gallery_id" value="${galleryId}">
						<input type="hidden" name="image_id" value="${imageId}">
						<input type="hidden" name="action" value="${action}">
						<input type="hidden" name="moderate_image" value="confirmed_${action}">
					`;
					document.body.appendChild(form);
					form.submit();
				}

            </script>
            <?php
        } else {
            // No ID found.
            echo esc_html__('No ID found in the shortcode.', 'foogallery');
        }
    }

    // Function to retrieve images associated with the gallery for approval or rejection
    private function getImagesToModerate($gallery_id) {
        $images = array();

        // Get the metadata for the gallery
        $metadata_file = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/metadata.json';

        if (file_exists($metadata_file)) {
            global $wp_filesystem;
            $metadata = @json_decode($wp_filesystem->get_contents($metadata_file), true);

            if (isset($metadata['items']) && is_array($metadata['items'])) {
                foreach ($metadata['items'] as $item) {
                    // Add images to the array for moderation
                    $image = array(
                        'id' => sanitize_text_field($item['file']),
                        'alt' => sanitize_text_field($item['alt']),
                        'url' => site_url("/wp-content/uploads/users_uploads/$gallery_id/{$item['file']}"),
                    );
                    $images[] = $image;
                }
            }
        }

        return $images;
    }
}

$custom_foogallery_meta_boxes = new FooGallery_FrontEnd_Upload_MetaBoxes();
