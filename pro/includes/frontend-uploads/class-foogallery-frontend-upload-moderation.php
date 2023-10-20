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
    class Foogallery_FrontEnd_Upload_Moderation{

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
        }

        /**
         * Initialize the image moderation logic.
         *
         * This method is responsible for initializing the image moderation logic.
         * It handles various actions related to image moderation based on POST requests.
         */
        public function init() {
            if (isset($_POST['moderate_image'])) {
                $image_id = sanitize_text_field($_POST['image_id']);
                $action = sanitize_text_field($_POST['action']);

                if ($action === 'approve') {
                    $this->handle_approve_action();
                } elseif ($action === 'reject') {
                    $this->handle_reject_action();
                }
            }

            if (isset($_POST['moderate_image']) && $_POST['action'] === 'delete') {
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
            // Verify the nonce
            if (isset($_POST['approve_image_nonce']) && wp_verify_nonce($_POST['approve_image_nonce'], 'approve_image_nonce')) {
                // Get the gallery ID and file name from the form data
                $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;
                $file_name = isset($_POST['image_id']) ? sanitize_text_field($_POST['image_id']) : null;
                    
                if ($gallery_id && $file_name) {
                    // Define the paths                    
                    $random_folder_name = get_post_meta($gallery_id, '_foogallery_frontend_upload', true);

                    $original_folder = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/' . $random_folder_name . '/';

                    $approved_folder = wp_upload_dir()['basedir'] . '/approved_folder/' . $gallery_id . '/';
                    update_post_meta($gallery_id, '_foogallery_frontend_upload_approved', $approved_folder);
                    $metadata_file = $original_folder . 'metadata.json';
                    $new_metadata_file = $approved_folder . 'metadata.json';
            
                     /**
                     * Function to merge attachments with the specific approved image and move it to the approved folder.
                     *
                     * @param int $gallery_id          The ID of the gallery.
                     * @param string $approved_image   The file name of the approved image.
                     * @param string $original_folder  The path to the original folder.
                     * @param string $approved_folder  The path to the approved folder.
                     * @param string $metadata_file    The path to the original metadata file.
                     * @param string $new_metadata_file The path to the new metadata file.
                     */
                    function merge_attachments_with_uploaded_images($gallery_id, $approved_image, $original_folder, $approved_folder, $metadata_file, $new_metadata_file) {
                        // Get the existing attachments for the gallery
                        $existing_attachments = get_post_meta($gallery_id, FOOGALLERY_META_ATTACHMENTS, true);
            
                        // Get the uploaded image's file name from metadata
                        $uploaded_images = array();
            
                        if (file_exists($metadata_file)) {
                            global $wp_filesystem;
                            $metadata = @json_decode( $wp_filesystem->get_contents( $metadata_file), true );
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
                            $new_metadata = @json_decode( $wp_filesystem->get_contents( $new_metadata_file ), true );
                        } else {
                            $new_metadata = ['items' => []];
                        }
            
                        foreach ($uploaded_images as $image) {
                            $new_metadata['items'][] = $image;
                        }
            
                        file_put_contents($new_metadata_file, json_encode($new_metadata, JSON_PRETTY_PRINT));
            
                        // Remove the image and its metadata from the original metadata JSON file
                        if (file_exists($metadata_file)) {
                            $metadata = @json_decode( $wp_filesystem->get_contents( $metadata_file), true );
                            $metadata['items'] = array_filter($metadata['items'], function ($item) use ($approved_image) {
                                return $item['file'] !== $approved_image;
                            });
            
                            file_put_contents($metadata_file, json_encode($metadata, JSON_PRETTY_PRINT));
                        }
            
                        // Merge the existing attachments with the uploaded images
                        $merged_attachments = array_merge($existing_attachments, $uploaded_images);
            
                        // Update the gallery's attachments with the merged array
                        update_post_meta($gallery_id, FOOGALLERY_META_ATTACHMENTS, $merged_attachments);
            
                        echo '<div class="notice notice-success"><p>' . __('Image approved and added to the gallery successfully.', 'foogallery') . '</p></div>';
                    }
            
                    // Call the function with the required parameters
                    merge_attachments_with_uploaded_images($gallery_id, $file_name, $original_folder, $approved_folder, $metadata_file, $new_metadata_file);       
                }
                
            } else {                
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

            // Verify the nonce
            if (isset($_POST['reject_image_nonce']) && wp_verify_nonce($_POST['reject_image_nonce'], 'reject_image_nonce')) {

                // Get the gallery ID and file name from the form data
                $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;
                $file_name = isset($_POST['image_id']) ? sanitize_text_field($_POST['image_id']) : null;

                if ($gallery_id && $file_name) {
                    // Delete the image file from the server
                    $random_folder_name = get_post_meta($gallery_id, '_foogallery_frontend_upload', true);                    
                    $user_folder = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/' . $random_folder_name . '/';

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
                    echo '<div class="notice notice-success"><p>' . __('Image successfully rejected', 'foogallery') . '</p></div>';
                }
            } else {
                echo '<div class="notice notice-error"><p>' . __('Security check failed. Please try again.', 'foogallery') . '</p></div>';
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
            
            // Verify the nonce
            if (isset($_POST['delete_image_nonce']) && wp_verify_nonce($_POST['delete_image_nonce'], 'delete_image_nonce')) {
                $image_id = sanitize_text_field($_POST['image_id']);
                $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;
                
                if ($gallery_id && $image_id) {
                    // Define the approved folder path
                    $approved_folder = wp_upload_dir()['basedir'] . '/approved_folder/' . $gallery_id . '/';
                    
                    // Delete the image file from the approved folder
                    $deleted = unlink($approved_folder . $image_id);
            
                    if ($deleted) {
                        // Remove the metadata entry for the deleted image
                        $metadata_file = $approved_folder . 'metadata.json';
                        if (file_exists($metadata_file)) {
                            $existing_metadata = @json_decode( $wp_filesystem->get_contents( $metadata_file ), true );
                            $existing_metadata['items'] = array_filter($existing_metadata['items'], function ($item) use ($image_id) {
                                return $item['file'] !== $image_id;
                            });
                            file_put_contents($metadata_file, json_encode($existing_metadata, JSON_PRETTY_PRINT));
                        }
            
                        // Show a success message
                        echo '<div class="notice notice-success"><p>' . __('Image successfully deleted', 'foogallery') . '</p></div>';
                    }
                }
            } else {
                echo '<div class="notice notice-error"><p>' . __('Security check failed. Please try again.', 'foogallery') . '</p></div>';
            }
        }

    }
}