<?php

 global $wp_filesystem;
 if (empty($wp_filesystem)) {
     require_once ABSPATH . '/wp-admin/includes/file.php';
     WP_Filesystem();
 }

 if ( ! class_exists( 'Foogallery_FrontEnd_Image_Moderation' ) ) {

    /**
     * Class Foogallery_FrontEnd_Image_Moderation
     *
     * This class handles image moderation functionality in the frontend.
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
            add_action('init', array($this, 'init'));
            $this->renderModerationPage();
        }
    
        /**
         * Initialize the image moderation functionality.
         *
         * This method is responsible for initializing the image moderation functionality.
         * It handles various actions related to image moderation based on POST requests.
         */
        public function init() {
            if (isset($_POST['moderate_image'])) {
                $image_id = sanitize_text_field($_POST['image_id']);
                $action = sanitize_text_field($_POST['action']);
    
                if ($action === 'approve') {
                    $this->handleApproveAction();
                } elseif ($action === 'reject') {
                    $this->handleRejectAction();
                }
            }
    
            if (isset($_POST['moderate_image']) && $_POST['action'] === 'delete') {
                $this->handleDeleteAction();
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
        private function handleApproveAction() {
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
        }
    
        /**
         * Handle the "Reject" action for an image.
         *
         * This method is responsible for processing the "Reject" action for an image.
         * It removes the image from the metadata and shows a success message.
         *
         * @private
         */
        private function handleRejectAction() {
            global $wp_filesystem;
            // Get the gallery ID and file name from the form data
            $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;
            $file_name = isset($_POST['image_id']) ? sanitize_text_field($_POST['image_id']) : null;
        
            if ($gallery_id && $file_name) {
                // Delete the image file from the server
                $user_folder = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/';
        
                {
                    // Remove the metadata entry for the rejected image
                    $metadata_file = $user_folder . 'metadata.json';
                    if (file_exists($metadata_file)) {
                        $existing_metadata = @json_decode( $wp_filesystem->get_contents( $metadata_file ), true );
                        $existing_metadata['items'] = array_filter($existing_metadata['items'], function ($item) use ($file_name) {
                            return $item['file'] !== $file_name;
                        });
                        file_put_contents($metadata_file, json_encode($existing_metadata, JSON_PRETTY_PRINT));
                    }
        
                    // Show a success message                
                    echo '<div class="notice notice-success"><p>' . __('Image successfully rejected', 'foogallery') . '</p></div>';
                }
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
        private function handleDeleteAction() {
            global $wp_filesystem;
            $image_id = sanitize_text_field($_POST['image_id']);
            $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;
            
            if ($gallery_id && $image_id) {
                // Define the approved folder path
                $approved_folder = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/approved_uploads/';
                
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
        }        
    
        /**
         * Render the moderation page.
         *
         * This method is responsible for rendering the moderation page where images can be moderated.
         * It should output the HTML and user interface for image moderation.
         */
        public function renderModerationPage() {
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
    
    
            // Handle filtering by gallery ID
            $filter_gallery_id = isset($_POST['filter_gallery_id']) ? intval($_POST['filter_gallery_id']) : 0;
            ?>
            
            <div class="wrap" id="image-moderation-container">
                <h2><?php esc_html_e( 'Image Moderation', 'foogallery' ); ?></h2>
    
                <section style="display: flex; justify-content:space-between; align-items:center;">
    
                    <ul class="nav-tabs" style="display: flex;">
                        <li><a href="#pending-tab" class="tab-label" style="margin-right: 5px; text-decoration: none;"><?php esc_html_e( 'Pending', 'foogallery' ); ?> |</a></li>
                        <li><a href="#approved-tab" class="tab-label" style="text-decoration: none;"><?php esc_html_e( 'Approved', 'foogallery' ); ?></a></li>
                    </ul>
    
    
                    <!-- Gallery Title filter dropdown -->
                    <form method="post" style="margin-bottom: 20px;">
                        <label for="filter_gallery_title">Filter by Gallery Title:</label>
                        <select name="filter_gallery_title" id="filter_gallery_title">
                            <option value=""><?php esc_html_e('All', 'foogallery'); ?></option>
                            <?php
                            // Populate the dropdown with available gallery titles
                            foreach ($images_to_moderate as $gallery_id => $images) {
                                $gallery_title = get_the_title($gallery_id);
                                echo '<option value="' . esc_attr($gallery_title) . '">' . esc_html($gallery_title) . '</option>';
                            }
                            ?>
                        </select>
                        <input type="submit" name="filter_images_by_title" value="Filter" hidden>
                    </form>
                </section>
    
                <div id="pending-tab" class="tab-content">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Gallery', 'foogallery' ); ?></th>
                                <th><?php esc_html_e( 'Image', 'foogallery' ); ?></th>
                                <th><?php esc_html_e( 'Metadata', 'foogallery' ); ?></th>
                                <th><?php esc_html_e( 'User', 'foogallery' ); ?></th>
                                <th><?php esc_html_e( 'Action', 'foogallery' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        // Initialize an array to store user IDs for each image
                        $image_uploaders = array();
                        foreach ($images_to_moderate as $gallery_id => $images) : ?>
                            <?php if ($filter_gallery_id === 0 || $filter_gallery_id === $gallery_id) : ?>
                                <?php foreach ($images as $image) : 
                                    // Get the gallery ID and image file name
                                    $gallery_id = intval($gallery_id);
                                    $file_name = sanitize_text_field($image['file']);
                                    
                                    // Check if the 'uploaded_by' field is set in the image's metadata
                                    if (isset($image['uploaded_by'])) {
                                        $uploader_id = intval($image['uploaded_by']);
                                        
                                        // Store the uploader's ID in the array
                                        $image_uploaders["$gallery_id-$file_name"] = $uploader_id;
                                    } else {
                                        // Handle cases where 'uploaded_by' field is not set
                                        $image_uploaders["$gallery_id-$file_name"] = ''; // or any other handling you prefer
                                    }
    
                                    ?>
                                    <tr>
                                        <td>
                                            <?php
                                            // Get the gallery post object
                                            $gallery_post = get_post($gallery_id);
                                            if ($gallery_post) {
                                                // Generate the URL for the gallery edit page
                                                $gallery_edit_url = get_edit_post_link($gallery_id);
    
                                                if ($gallery_edit_url) {
                                                    echo '<a href="' . esc_url($gallery_edit_url) . '">' . esc_html($gallery_post->post_title) . '</a>';
                                                } else {
                                                    echo esc_html($gallery_post->post_title);
                                                }
                                            } else {
                                                // Display a fallback value if the gallery post is not found
                                                echo esc_html($gallery_id);
                                            }
                                            ?>
                                        </td>
    
                                        <td>
                                            <?php
                                            // Retrieve the image URL from the JSON data
                                            $image_filename = isset($image['file']) ? sanitize_file_name($image['file']) : '';
                                            $base_url = site_url();
    
                                            // Construct the complete image URL
                                            $image_url = $base_url . '/wp-content/uploads/users_uploads/' . $gallery_id . '/' . $image_filename;
    
                                            // Display the image if the URL is not empty
                                            if (!empty($image_url)) {
                                                echo '<img style="width: 150px; height: 150px;" src="' . esc_url($image_url) . '" alt="' . esc_attr($image['alt']) . '" />';
                                            }
                                            ?>
                                        </td>
    
                                        <td>
                                            <p><strong><?php esc_html_e('Caption:', 'foogallery'); ?></strong> <?php echo esc_html($image['caption']); ?></p>
                                            <p><strong><?php esc_html_e('Description:', 'foogallery'); ?></strong> <?php echo esc_html($image['description']); ?></p>
                                            <p><strong><?php esc_html_e('Alt Text:', 'foogallery'); ?></strong><?php echo esc_html($image['alt']); ?></p>
                                            <p><strong><?php esc_html_e('Custom URL:', 'foogallery'); ?></strong> <?php echo esc_url($image['custom_url']); ?></p>
                                            <p><strong><?php esc_html_e('Custom Target:', 'foogallery'); ?></strong> <?php echo esc_html($image['custom_target']); ?></p>
                                        </td>
    
                                        <td>
                                            <?php
                                            // Get the gallery ID and image file name
                                            $gallery_id = intval($gallery_id);
                                            $file_name = sanitize_text_field($image['file']);
                                            
                                            // Create a unique identifier for this image (gallery_id-file_name)
                                            $image_identifier = "$gallery_id-$file_name";
                                            
                                            // Get the user ID who uploaded this image from the array
                                            $uploader_id = isset($image_uploaders[$image_identifier]) ? $image_uploaders[$image_identifier] : '';
    
                                            // Display the uploader's username or other information
                                            if (!empty($uploader_id)) {
                                                $uploader_info = get_userdata($uploader_id);
                                                if ($uploader_info) {
                                                    echo esc_html($uploader_info->display_name);
                                                } else {
                                                    echo esc_html__('Unknown User', 'foogallery');
                                                }
                                            } else {
                                                echo esc_html__('N/A', 'foogallery');
                                            }
                                            ?>
                                        </td>
    
                                        <td>
                                            <button class="confirm-approve button button-small button-primary" data-gallery-id="<?php echo esc_attr($gallery_id); ?>" data-image-id="<?php echo esc_attr($image['file']); ?>">Approve Image</button>
                                            <button class="confirm-reject button button-small" data-gallery-id="<?php echo esc_attr($gallery_id); ?>" data-image-id="<?php echo esc_attr($image['file']); ?>">Reject Image</button>
                                        </td>
    
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
    
                <div id="approved-tab" class="tab-content">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Gallery', 'foogallery' ); ?></th>
                                <th><?php esc_html_e( 'Image', 'foogallery' ); ?></th>
                                <th><?php esc_html_e( 'Metadata', 'foogallery' ); ?></th>
                                <th><?php esc_html_e( 'User', 'foogallery' ); ?></th>
                                <th><?php esc_html_e( 'Action', 'foogallery' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($images_to_moderate as $gallery_id => $images) : ?>
                                <?php if ($filter_gallery_id === 0 || $filter_gallery_id === $gallery_id) : ?>
                                    <?php
                                    // Define the path to the approved folder and metadata file
                                    $approved_folder = wp_upload_dir()['basedir'] . '/users_uploads/' . $gallery_id . '/approved_uploads/';
                                    $metadata_file = $approved_folder . 'metadata.json';
                                    ?>
                                    <?php if (file_exists($metadata_file)) : ?>
                                        <?php
                                        // Read metadata from the JSON file
                                        $metadata = @json_decode( $wp_filesystem->get_contents( $metadata_file ), true );
    
                                        // Check if the JSON data is correctly decoded
                                        if (isset($metadata['items']) && is_array($metadata['items'])) :
                                        ?>
                                        <?php foreach ($metadata['items'] as $item) : ?>
                                            <tr>
                                                <td>
                                                    <?php
                                                    // Retrieve the gallery title based on the gallery ID
                                                    $gallery_title = get_the_title($gallery_id);
                                                    $gallery_edit_url = get_edit_post_link($gallery_id);
    
                                                    if ($gallery_edit_url) {
                                                        echo '<a href="' . esc_url($gallery_edit_url) . '">' . esc_html($gallery_title) . '</a>';
                                                    } else {
                                                        echo esc_html($gallery_title);
                                                    }
                                                    ?>
                                                </td>
    
                                                <td>
                                                    <?php
                                                    // Retrieve the image URL from the JSON data
                                                    $image_filename = isset($item['file']) ? sanitize_file_name($item['file']) : '';
                                                    $base_url = site_url();
    
                                                    // Construct the complete image URL
                                                    $image_url = $base_url . '/wp-content/uploads/users_uploads/' . $gallery_id . '/approved_uploads/' . $image_filename;
    
                                                    // Display the image if the URL is not empty
                                                    if (!empty($image_url)) {
                                                        echo '<img style="width: 150px; height: 150px;" src="' . esc_url($image_url) . '" alt="' . esc_attr($item['alt']) . '" />';
                                                    }
                                                    ?>
                                                </td>
    
                                                <td>
                                                    <p><strong><?php esc_html_e('Caption:', 'foogallery'); ?></strong> <?php echo esc_html($item['caption']); ?></p>
                                                    <p><strong><?php esc_html_e('Description:', 'foogallery'); ?></strong> <?php echo esc_html($item['description']); ?></p>
                                                    <p><strong><?php esc_html_e('Alt Text:', 'foogallery'); ?></strong><?php echo esc_html($item['alt']); ?></p>
                                                    <p><strong><?php esc_html_e('Custom URL:', 'foogallery'); ?></strong> <?php echo esc_url($item['custom_url']); ?></p>
                                                    <p><strong><?php esc_html_e('Custom Target:', 'foogallery'); ?></strong> <?php echo esc_html($item['custom_target']); ?></p>
                                                </td>
    
                                                <td>
                                                    <?php
                                                    // Get the gallery ID and image file name
                                                    $gallery_id = intval($gallery_id);
                                                    $file_name = sanitize_text_field($image['file']);
                                                    
                                                    // Create a unique identifier for this image (gallery_id-file_name)
                                                    $image_identifier = "$gallery_id-$file_name";
                                                    
                                                    // Get the user ID who uploaded this image from the array
                                                    $uploader_id = isset($image_uploaders[$image_identifier]) ? $image_uploaders[$image_identifier] : '';
    
                                                    // Display the uploader's username or other information
                                                    if (!empty($uploader_id)) {
                                                        $uploader_info = get_userdata($uploader_id);
                                                        if ($uploader_info) {
                                                            echo esc_html($uploader_info->display_name);
                                                        } else {
                                                            echo esc_html__('Unknown User', 'foogallery');
                                                        }
                                                    } else {
                                                        echo esc_html__('N/A', 'foogallery');
                                                    }
                                                    ?>
                                                </td>
    
                                                <td>
                                                    <button class="confirm-delete button button-small" data-gallery-id="<?php echo esc_attr($gallery_id); ?>" data-image-id="<?php echo esc_attr($item['file']); ?>">Delete Image</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
            </div>
    
            <script>
                const filterDropdown = document.getElementById('filter_gallery_title');
                const tableRows = document.querySelectorAll('.wp-list-table tbody tr');
                const tabs = document.querySelectorAll('.nav-tabs a');
                const tabContents = document.querySelectorAll('.tab-content');
                const tabLabels = document.querySelectorAll('.tab-label');
    
                filterDropdown.addEventListener('change', function () {
                    const selectedGalleryTitle = this.value.toLowerCase().trim();
    
                    tableRows.forEach(row => {
                        const galleryTitleCell = row.querySelector('td:first-child');
    
                        if (!galleryTitleCell) {
                            return;
                        }
    
                        const rowGalleryTitle = galleryTitleCell.textContent.trim().toLowerCase();
    
                        if (selectedGalleryTitle === '' || rowGalleryTitle.includes(selectedGalleryTitle)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
    
    
                function hideAllTabs() {
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                    });
    
                    tabLabels.forEach(label => {
                        label.classList.remove('active');
                    });
                }
    
                tabs.forEach(tab => {
                    tab.addEventListener('click', function (e) {
                        e.preventDefault();
    
                        hideAllTabs();
    
                        const target = this.getAttribute('href').replace('#', '');
                        const selectedTab = document.getElementById(target);
                        selectedTab.classList.add('active');
    
                        this.classList.add('active');
                    });
                });
    
                // Add event listeners for confirmation dialogs
                const confirmRejectButtons = document.querySelectorAll('.confirm-reject');
                confirmRejectButtons.forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
    
                        const galleryId = this.getAttribute('data-gallery-id');
                        const imageId = this.getAttribute('data-image-id');
    
                        if (confirm(`Are you sure you want to reject this image?`)) {
                            const form = document.createElement('form');
                            form.method = 'post';
                            form.innerHTML = `
                                <input type="hidden" name="gallery_id" value="${galleryId}">
                                <input type="hidden" name="image_id" value="${imageId}">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="moderate_image" value="confirmed_reject">
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
    
                        if (confirm(`Are you sure you want to approve this image?`)) {
                            const form = document.createElement('form');
                            form.method = 'post';
                            form.innerHTML = `
                                <input type="hidden" name="gallery_id" value="${galleryId}">
                                <input type="hidden" name="image_id" value="${imageId}">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="moderate_image" value="confirmed_approve">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
    
                const confirmDeleteButtons = document.querySelectorAll('.confirm-delete');
                confirmDeleteButtons.forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
    
                        const galleryId = this.getAttribute('data-gallery-id');
                        const imageId = this.getAttribute('data-image-id');
    
                        if (confirm(`Are you sure you want to delete this image?`)) {
                            const form = document.createElement('form');
                            form.method = 'post';
                            form.innerHTML = `
                                <input type="hidden" name="gallery_id" value="${galleryId}">
                                <input type="hidden" name="image_id" value="${imageId}">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="moderate_image" value="confirmed_delete">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
    
                const initialTab = document.getElementById('pending-tab');
                const initialTabLabel = document.querySelector('.tab-label[href="#pending-tab"]');
                initialTab.classList.add('active');
                initialTabLabel.classList.add('active');
            </script>
    
    
            <style>
                .tab-content {
                    display: none;
                }
    
                .tab-content.active {
                    display: block;
                }
    
                .tab-label.active {
                    color: black;
                }
    
                .tab-label:not(.active) {
                    color: blue;
                }
            </style>
            <?php
        }
    }
 }

// Instantiate the class
$imageModeration = new Foogallery_FrontEnd_Image_Moderation();

