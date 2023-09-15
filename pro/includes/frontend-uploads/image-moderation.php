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

    // TODO:: Implement  moderation logic here
    if ($action === 'approve') {
        // Approve images published to galleries
    } elseif ($action === 'reject') {
        // delete images from the server.     
    }

    // Redirect back to the moderation page
    wp_redirect(admin_url('admin.php?page=foogallery-image-moderation'));

}

// Initialize an array to store gallery IDs
$gallery_ids = array();

// Iterate through the users upload folders
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

?>

<!-- HTML for the Moderation Page -->
<div class="wrap">
    <h2>Image Moderation</h2>
    
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
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
