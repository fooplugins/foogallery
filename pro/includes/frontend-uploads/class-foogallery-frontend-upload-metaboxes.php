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
                'custom_metabox_id_frontend_upload',
                __('Front End Upload', 'foogallery'),
                array($this, 'render_frontend_upload_metabox'),
                FOOGALLERY_CPT_GALLERY,
                'normal',
                'low'
            );
            
            add_meta_box(
                'custom_metabox_id_image_moderation',
                __('Images Awaiting Moderation', 'foogallery'),
                array($this, 'render_image_moderation_metabox'),
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

                // Save the maximum image size setting.
                if (isset($_POST['max_image_size'])) {
                    update_post_meta($post_id, '_max_image_size', sanitize_text_field($_POST['max_image_size']));
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
                    <label for="max_images_allowed" class="foogallery-upload-settings-input-label"><?php esc_html_e('Maximum Images Allowed:', 'foogallery');?></label>
                    <input type="number" id="max_images_allowed" name="max_images_allowed" value="<?php echo esc_attr($max_images_allowed); ?>" class="foogallery-upload-settings-input-field" />

                    <label for="max_image_size" class="foogallery-upload-settings-input-label"><?php esc_html_e('Maximum Image Size (in MB):', 'foogallery');?></label>
                    <input type="number" id="max_image_size" name="max_image_size" value="<?php echo esc_attr($max_image_size); ?>" class="foogallery-upload-settings-input-field" />

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
                <?php
            } else {
                // No ID found.
                echo esc_html__('No ID found in the shortcode.', 'foogallery');
            }
        }

        /**
         * Render the Image Moderation metabox.
         *
         * @param WP_Post $post The current post object.
         */
        public function render_image_moderation_metabox($post) {
            $gallery = $this->get_gallery($post);
            $shortcode = $gallery->shortcode();
            // Use preg_match to find the ID within the shortcode.
            if (preg_match('/\[foogallery id="(\d+)"\]/', $shortcode, $matches)) {
                $gallery_id = $matches[1];
                ?>
                
                <div id="image-moderation">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Image', 'foogallery'); ?></th>
                                <th><?php esc_html_e('Metadata', 'foogallery'); ?></th>
                                <th><?php esc_html_e('Action', 'foogallery'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Retrieve images associated with the gallery for approval or rejection.
                            $images_to_moderate = $this->get_images_to_moderate($gallery_id);
                            foreach ($images_to_moderate as $image) {
                                ?>
                                <tr>
                                    <td>
                                        <img style="width: 100px; height: 100px;" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
                                    </td>
                                    <td>
                                        <!-- Display metadata here -->
                                        <p><strong><?php esc_html_e('Caption:', 'foogallery'); ?></strong> <?php echo esc_html($image['caption']); ?></p>
                                        <p><strong><?php esc_html_e('Description:', 'foogallery'); ?></strong> <?php echo esc_html($image['description']); ?></p>
                                        <p><strong><?php esc_html_e('Alt Text:', 'foogallery'); ?></strong> <?php echo esc_html($image['alt']); ?></p>
                                        <p><strong><?php esc_html_e('Custom URL:', 'foogallery'); ?></strong> <?php echo esc_url($image['custom_url']); ?></p>
                                        <p><strong><?php esc_html_e('Custom Target:', 'foogallery'); ?></strong> <?php echo esc_html($image['custom_target']); ?></p>
                                    </td>
                                    <td>
                                        <button class="approve-image button button-primary" data-image-id="<?php echo esc_attr($image['id']); ?>" name="approve_image_nonce" data-nonce="<?php echo wp_create_nonce('approve_image_nonce'); ?>"><?php esc_html_e('Approve', 'foogallery'); ?></button>
                                        <button class="reject-image button button-small" data-image-id="<?php echo esc_attr($image['id']); ?>" name="reject_image_nonce" data-nonce="<?php echo wp_create_nonce('reject_image_nonce'); ?>">
                                            <?php esc_html_e('Reject Image', 'foogallery'); ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

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
                            <input type="hidden" name="${action}_image_nonce" value="${nonceValues[action]}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }

                    // Define nonce values for both actions
                    const nonceValues = {
                        approve: 'approve_image_nonce',
                        reject: 'reject_image_nonce'
                    };

                </script>

                
                <?php
            } else {
                echo esc_html__('No ID found in the shortcode.', 'foogallery');
            }           
        }

        // Function to retrieve images associated with the gallery for approval or rejection
        private function get_images_to_moderate($gallery_id) {
            $images = array();

            // Get the random subfolder name from the postmeta array
            $random_folder_name = get_post_meta($gallery_id, '_foogallery_frontend_upload', true);
            $metadata_file = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/' . $random_folder_name . '/metadata.json';
            
            if (file_exists($metadata_file)) {
                global $wp_filesystem;
                $metadata = @json_decode($wp_filesystem->get_contents($metadata_file), true);

                if (isset($metadata['items']) && is_array($metadata['items'])) {
                    foreach ($metadata['items'] as $item) {
                        // Add images to the array for moderation
                        $image = array(
                            'id' => sanitize_text_field($item['file']),
                            'alt' => sanitize_text_field($item['alt']),
                            'url' => site_url("/wp-content/uploads/users_uploads/$gallery_id/$random_folder_name/{$item['file']}"),
                            'caption' => sanitize_text_field($item['caption']),
                            'description' => sanitize_text_field($item['description']),
                            'custom_url' => esc_url($item['custom_url']),
                            'custom_target' => sanitize_text_field($item['custom_target']),
                        );

                        $images[] = $image;
                    }
                }
            }

            return $images;
        }

    }

}
$custom_foogallery_meta_boxes = new FooGallery_FrontEnd_Upload_MetaBoxes();