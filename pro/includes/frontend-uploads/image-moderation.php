<?php

// Include shortcode class
if (!class_exists('FooGallery_Image_Upload_Form_Shortcode')) {
    require_once FOOGALLERY_PATH . 'pro/includes/frontend-uploads/class-foogallery-frontend-uploads.php';
}

// Initialize  class
$foogallery_image_upload = new FooGallery_Image_Upload_Form_Shortcode();

// Check if the form is submitted for image moderation
if (isset($_POST['moderate_image'])) {
    $image_id = sanitize_text_field($_POST['image_id']);
    $action = sanitize_text_field($_POST['action']);

    if ($action === 'approve') {      
        // Get the gallery ID and file name from the form data
        $gallery_id = isset($_POST['gallery_id']) ? intval($_POST['gallery_id']) : null;
        $file_name = isset($_POST['image_id']) ? sanitize_text_field($_POST['image_id']) : null;
            
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
                function merge_attachments_with_uploaded_images($gallery_id, $approved_image, $original_folder, $approved_folder, $metadata_file, $new_metadata_file) {
                    // Get the existing attachments for the gallery
                    $existing_attachments = get_post_meta($gallery_id, FOOGALLERY_META_ATTACHMENTS, true);
        
                    // Get the uploaded image's file name from metadata
                    $uploaded_images = array();
        
                    if (file_exists($metadata_file)) {
                        $metadata = json_decode(file_get_contents($metadata_file), true);
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
                        $new_metadata = json_decode(file_get_contents($new_metadata_file), true);
                    } else {
                        $new_metadata = ['items' => []];
                    }
        
                    foreach ($uploaded_images as $image) {
                        $new_metadata['items'][] = $image;
                    }
        
                    file_put_contents($new_metadata_file, json_encode($new_metadata, JSON_PRETTY_PRINT));
        
                    // Remove the image and its metadata from the original metadata JSON file
                    if (file_exists($metadata_file)) {
                        $metadata = json_decode(file_get_contents($metadata_file), true);
                        $metadata['items'] = array_filter($metadata['items'], function ($item) use ($approved_image) {
                            return $item['file'] !== $approved_image;
                        });
        
                        file_put_contents($metadata_file, json_encode($metadata, JSON_PRETTY_PRINT));
                    }
        
                    // Merge the existing attachments with the uploaded images
                    $merged_attachments = array_merge($existing_attachments, $uploaded_images);
        
                    // Update the gallery's attachments with the merged array
                    update_post_meta($gallery_id, FOOGALLERY_META_ATTACHMENTS, $merged_attachments);
        
                    echo '<div class="notice notice-success"><p>' . __('Image approved and added to the gallery successfully.', 'foogallery') . 'p></div>';
                }
        
                // Call the function with the required parameters
                merge_attachments_with_uploaded_images($gallery_id, $file_name, $original_folder, $approved_folder, $metadata_file, $new_metadata_file);       
            } 
        }
    }        
     elseif ($action === 'reject') {
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
                    $existing_metadata = json_decode(file_get_contents($metadata_file), true);
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
}

// Initialize an array to store gallery IDs
$gallery_ids = array();

// Iterate through the users' upload folders
$upload_dir = wp_upload_dir();
$user_uploads_dir = $upload_dir['basedir'] . '/users_uploads/';

if (is_dir($user_uploads_dir)) {
    $directories = glob($user_uploads_dir . '*', GLOB_ONLYDIR);
    foreach ($directories as $directory) {
        $gallery_id = basename($directory);
        $gallery_id = intval($gallery_id);
        $metadata_file = $directory . '/metadata.json';

        if (file_exists($metadata_file)) {
            $metadata = json_decode(file_get_contents($metadata_file), true);
            $images_to_moderate[$gallery_id] = $metadata['items'];
        }
    }
}

// Handle filtering by gallery ID
$filter_gallery_id = isset($_POST['filter_gallery_id']) ? intval($_POST['filter_gallery_id']) : 0;

?>


<!-- HTML for the Moderation Page -->
<div class="wrap" id="image-moderation-container">
    <h2>Image Moderation</h2>

    <!-- Gallery ID filter dropdown -->
    <form method="post" style="margin-bottom: 20px;">
        <label for="filter_gallery_id">Filter by Gallery ID:</label>
        <select name="filter_gallery_id" id="filter_gallery_id">
            <option value="0">All</option>
            <?php foreach ($images_to_moderate as $gallery_id => $images) : ?>
                <option value="<?php echo esc_attr($gallery_id); ?>" <?php selected($filter_gallery_id, $gallery_id); ?>>
                    <?php echo esc_html($gallery_id); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" name="filter_images" value="Filter" hidden>
    </form>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Gallery ID</th>
                <th>Image</th>
                <th>Metadata</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($images_to_moderate as $gallery_id => $images) : ?>
                <?php if ($filter_gallery_id === 0 || $filter_gallery_id === $gallery_id) : ?>
                    <?php foreach ($images as $image) : ?>
                        <tr>
                            <td><?php echo esc_html($gallery_id); ?></td>
                            <td><img src="<?php echo esc_url($image['file']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" /></td>
                            <td>
                                <p><strong>Caption:</strong> <?php echo esc_html($image['caption']); ?></p>
                                <p><strong>Description:</strong> <?php echo esc_html($image['description']); ?></p>
                                <p><strong>Alt Text:</strong> <?php echo esc_html($image['alt']); ?></p>
                                <p><strong>Custom URL:</strong> <?php echo esc_url($image['custom_url']); ?></p>
                                <p><strong>Custom Target:</strong> <?php echo esc_html($image['custom_target']); ?></p>
                            </td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="gallery_id" value="<?php echo esc_attr($gallery_id); ?>">
                                    <input type="hidden" name="image_id" value="<?php echo esc_attr($image['file']); ?>">
                                    <select name="action">
                                        <option value="approve">Approve</option>
                                        <option value="reject">Reject</option>
                                    </select>
                                    <input type="submit" name="moderate_image" value="Submit">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>    
    const filterDropdown = document.getElementById('filter_gallery_id');

    filterDropdown.addEventListener('change', function () {        
        const form = filterDropdown.closest('form');
        form.submit();
    });
</script>

<?php
