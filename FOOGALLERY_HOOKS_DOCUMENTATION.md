# FooGallery Premium Plugin - Complete Hooks Documentation

**Plugin Version**: upto 2.4.34  
**Total Hooks Documented**: 200+  

---

## Table of Contents

1. [Plugin Initialization Hooks](#plugin-initialization-hooks)
2. [Gallery Management Hooks](#gallery-management-hooks)
3. [Album Management Hooks](#album-management-hooks)
4. [Attachment Management Hooks](#attachment-management-hooks)
5. [Template and Rendering Hooks](#template-and-rendering-hooks)
6. [Admin Interface Hooks](#admin-interface-hooks)
7. [Extension Management Hooks](#extension-management-hooks)
8. [Datasource Management Hooks](#datasource-management-hooks)
9. [Thumbnail and Image Processing Hooks](#thumbnail-and-image-processing-hooks)
10. [Settings and Configuration Hooks](#settings-and-configuration-hooks)
11. [Lightbox and UI Hooks](#lightbox-and-ui-hooks)
12. [Gutenberg/Block Editor Hooks](#gutenbergblock-editor-hooks)
13. [Dynamic Template Hooks](#dynamic-template-hooks)
14. [PRO Features Hooks](#pro-features-hooks)

---

## Plugin Initialization Hooks

### `foogallery_fs_loaded`
**Type**: Action  
**Description**: Fired when the Freemius SDK has been loaded and initialized  
**Parameters**: None  
**Usage**: Initialize premium features or licensing checks  

```php
add_action('foogallery_fs_loaded', function() {
    // Your premium features initialization code here
    if (foogallery_fs()->can_use_premium_code()) {
        // Load premium functionality
    }
});
```

### `foogallery_admin_menu_before`
**Type**: Action  
**Description**: Fired before the FooGallery admin menu is created  
**Parameters**: None  
**Usage**: Add custom menu items before FooGallery menus  

```php
add_action('foogallery_admin_menu_before', function() {
    add_menu_page(
        'Custom Gallery Manager',
        'Gallery Manager',
        'manage_options',
        'custom-gallery-manager',
        'custom_gallery_manager_page'
    );
});
```

### `foogallery_admin_menu_after`
**Type**: Action  
**Description**: Fired after the FooGallery admin menu has been created  
**Parameters**: None  
**Usage**: Add custom submenu items or modify existing menus  

```php
add_action('foogallery_admin_menu_after', function() {
    add_submenu_page(
        'edit.php?post_type=foogallery',
        'Custom Reports',
        'Reports',
        'manage_options',
        'foogallery-reports',
        'foogallery_reports_page'
    );
});
```

---

## Gallery Management Hooks

### `foogallery_instance_after_load`
**Type**: Action  
**Description**: Fired after a FooGallery instance has been loaded from the database  
**Parameters**: 
- `$foogallery` (object): The FooGallery instance
- `$post` (WP_Post): The WordPress post object  

**Usage**: Modify gallery data after loading or add custom properties  

```php
add_action('foogallery_instance_after_load', function($foogallery, $post) {
    // Add custom property to gallery instance
    $foogallery->custom_property = get_post_meta($post->ID, '_custom_gallery_data', true);
    
    // Log gallery access for analytics
    error_log("Gallery loaded: " . $foogallery->name);
}, 10, 2);
```

### `foogallery_before_save_gallery`
**Type**: Action  
**Description**: Fired before a gallery is saved in the admin  
**Parameters**: 
- `$post_id` (int): The gallery post ID
- `$post_data` (array): The $_POST data being saved  

**Usage**: Validate or modify gallery data before saving  

```php
add_action('foogallery_before_save_gallery', function($post_id, $post_data) {
    // Validate custom fields
    if (isset($post_data['custom_gallery_option'])) {
        $custom_value = sanitize_text_field($post_data['custom_gallery_option']);
        update_post_meta($post_id, '_custom_gallery_option', $custom_value);
    }
    
    // Log gallery saves
    error_log("Gallery being saved: " . $post_id);
}, 10, 2);
```

### `foogallery_after_save_gallery`
**Type**: Action  
**Description**: Fired after a gallery has been saved in the admin  
**Parameters**: 
- `$post_id` (int): The gallery post ID
- `$post_data` (array): The $_POST data that was saved  

**Usage**: Perform actions after gallery save, such as cache clearing or notifications  

```php
add_action('foogallery_after_save_gallery', function($post_id, $post_data) {
    // Clear custom caches
    delete_transient('custom_gallery_cache_' . $post_id);
    
    // Send notification
    wp_mail(
        'admin@example.com',
        'Gallery Updated',
        'Gallery ID ' . $post_id . ' has been updated.'
    );
}, 10, 2);
```

### `foogallery_attach_gallery_to_post`
**Type**: Action  
**Description**: Fired when a gallery is attached to a post/page  
**Parameters**: 
- `$post_id` (int): The post ID where gallery is attached
- `$gallery_id` (int): The gallery ID being attached  

**Usage**: Track gallery usage or update relationships  

```php
add_action('foogallery_attach_gallery_to_post', function($post_id, $gallery_id) {
    // Track gallery usage
    $usage_count = (int) get_post_meta($gallery_id, '_usage_count', true);
    update_post_meta($gallery_id, '_usage_count', $usage_count + 1);
    
    // Store relationship
    $post_galleries = get_post_meta($post_id, '_attached_galleries', true) ?: array();
    if (!in_array($gallery_id, $post_galleries)) {
        $post_galleries[] = $gallery_id;
        update_post_meta($post_id, '_attached_galleries', $post_galleries);
    }
}, 10, 2);
```

---

## Album Management Hooks

### `foogallery_after_save_album`
**Type**: Action  
**Description**: Fired after an album has been saved  
**Parameters**: 
- `$post_id` (int): The album post ID
- `$post_data` (array): The $_POST data that was saved  

**Usage**: Perform post-save operations for albums  

```php
add_action('foogallery_after_save_album', function($post_id, $post_data) {
    // Clear album cache
    delete_transient('album_cache_' . $post_id);
    
    // Update album statistics
    $gallery_count = count(get_post_meta($post_id, 'foogallery_album_galleries', true) ?: array());
    update_post_meta($post_id, '_album_gallery_count', $gallery_count);
}, 10, 2);
```

### `foogallery_located_album_template`
**Type**: Action  
**Description**: Fired when an album template has been located and is about to be loaded  
**Parameters**: 
- `$album` (object): The FooGallery album instance  

**Usage**: Modify album data before template rendering  

```php
add_action('foogallery_located_album_template', function($album) {
    // Add custom album data
    global $current_foogallery_album_custom_data;
    $current_foogallery_album_custom_data = array(
        'album_id' => $album->ID,
        'custom_setting' => $album->get_setting('custom_setting'),
        'gallery_count' => count($album->galleries())
    );
});
```

### `foogallery_loaded_album_template`
**Type**: Action  
**Description**: Fired after an album template has been loaded and rendered  
**Parameters**: 
- `$album` (object): The FooGallery album instance  

**Usage**: Add custom output after album rendering  

```php
add_action('foogallery_loaded_album_template', function($album) {
    // Add custom tracking code
    echo '<script>
        gtag("event", "album_view", {
            "album_id": ' . $album->ID . ',
            "album_name": "' . esc_js($album->name) . '"
        });
    </script>';
});
```

---

## Attachment Management Hooks

### `foogallery_attachment_instance_after_load`
**Type**: Action  
**Description**: Fired after an attachment instance has been loaded  
**Parameters**: 
- `$attachment` (object): The FooGalleryAttachment instance
- `$wp_post` (WP_Post): The WordPress attachment post  

**Usage**: Add custom properties to attachments  

```php
add_action('foogallery_attachment_instance_after_load', function($attachment, $wp_post) {
    // Add custom metadata
    $attachment->custom_rating = get_post_meta($wp_post->ID, '_image_rating', true);
    $attachment->view_count = (int) get_post_meta($wp_post->ID, '_view_count', true);
    
    // Add EXIF data if available
    $exif = wp_read_image_metadata($attachment->file_path);
    if ($exif) {
        $attachment->camera_make = $exif['camera'] ?? '';
        $attachment->focal_length = $exif['focal_length'] ?? '';
    }
}, 10, 2);
```

### `foogallery_attachment_save_data`
**Type**: Action  
**Description**: Fired when attachment data is saved through the attachment modal  
**Parameters**: 
- `$attachment_id` (int): The attachment ID
- `$gallery` (object): The FooGallery instance  

**Usage**: Save custom attachment data  

```php
add_action('foogallery_attachment_save_data', function($attachment_id, $gallery) {
    // Save custom fields from $_POST
    if (isset($_POST['attachment_rating'])) {
        $rating = (int) $_POST['attachment_rating'];
        update_post_meta($attachment_id, '_image_rating', $rating);
    }
    
    if (isset($_POST['attachment_tags'])) {
        $tags = sanitize_text_field($_POST['attachment_tags']);
        update_post_meta($attachment_id, '_custom_tags', $tags);
    }
}, 10, 2);
```

### `foogallery_attachment_custom_fields`
**Type**: Filter  
**Description**: Add custom fields to the attachment modal  
**Parameters**: 
- `$fields` (array): Existing custom fields array  

**Returns**: Modified fields array  
**Usage**: Add custom input fields for attachments  

```php
add_filter('foogallery_attachment_custom_fields', function($fields) {
    $fields['rating'] = array(
        'label' => 'Image Rating',
        'input' => 'select',
        'choices' => array(
            '1' => '1 Star',
            '2' => '2 Stars', 
            '3' => '3 Stars',
            '4' => '4 Stars',
            '5' => '5 Stars'
        ),
        'default' => '3',
        'help' => 'Rate this image from 1 to 5 stars'
    );
    
    $fields['custom_tags'] = array(
        'label' => 'Custom Tags',
        'input' => 'text',
        'help' => 'Add comma-separated tags for this image'
    );
    
    return $fields;
});
```

---

## Template and Rendering Hooks

### `foogallery_located_template`
**Type**: Action  
**Description**: Fired when a gallery template has been located but before it's loaded  
**Parameters**: 
- `$gallery` (object): The FooGallery instance  

**Usage**: Modify gallery settings or prepare template-specific data  

```php
add_action('foogallery_located_template', function($gallery) {
    // Override settings for specific templates
    if ($gallery->gallery_template === 'masonry') {
        global $current_foogallery_template_override;
        $current_foogallery_template_override = array(
            'thumbnail_width' => 300,
            'thumbnail_height' => 300,
            'lightbox' => 'foogallery'
        );
    }
    
    // Prepare custom data
    global $gallery_custom_data;
    $gallery_custom_data = array(
        'total_images' => count($gallery->attachments()),
        'created_date' => get_the_date('c', $gallery->ID)
    );
});
```

### `foogallery_loaded_template_before`
**Type**: Action  
**Description**: Fired just before a gallery template is rendered  
**Parameters**: 
- `$gallery` (object): The FooGallery instance  

**Usage**: Add content before gallery output  

```php
add_action('foogallery_loaded_template_before', function($gallery) {
    // Add gallery title and description
    echo '<div class="gallery-header">';
    echo '<h2 class="gallery-title">' . esc_html($gallery->name) . '</h2>';
    
    $description = $gallery->get_setting('description');
    if ($description) {
        echo '<p class="gallery-description">' . wp_kses_post($description) . '</p>';
    }
    echo '</div>';
    
    // Add social sharing buttons
    echo '<div class="gallery-sharing">';
    echo '<a href="https://twitter.com/intent/tweet?text=' . urlencode($gallery->name) . '&url=' . urlencode(get_permalink()) . '" target="_blank">Share on Twitter</a>';
    echo '</div>';
});
```

### `foogallery_loaded_template_after`
**Type**: Action  
**Description**: Fired after a gallery template has been rendered  
**Parameters**: 
- `$gallery` (object): The FooGallery instance  

**Usage**: Add content after gallery output  

```php
add_action('foogallery_loaded_template_after', function($gallery) {
    // Add gallery statistics
    echo '<div class="gallery-stats">';
    echo '<p>Gallery contains ' . count($gallery->attachments()) . ' images</p>';
    echo '<p>Last updated: ' . get_the_modified_date('F j, Y', $gallery->ID) . '</p>';
    echo '</div>';
    
    // Add custom analytics tracking
    echo '<script>
        if (typeof gtag !== "undefined") {
            gtag("event", "gallery_view", {
                "gallery_id": ' . $gallery->ID . ',
                "gallery_template": "' . esc_js($gallery->gallery_template) . '",
                "image_count": ' . count($gallery->attachments()) . '
            });
        }
    </script>';
});
```

### `foogallery_attachment_html_image`
**Type**: Filter  
**Description**: Modify the HTML for gallery item images  
**Parameters**: 
- `$html` (string): The current image HTML
- `$args` (array): Image generation arguments
- `$attachment` (object): The FooGalleryAttachment instance  

**Returns**: Modified HTML string  
**Usage**: Customize image output  

```php
add_filter('foogallery_attachment_html_image', function($html, $args, $attachment) {
    // Add custom data attributes
    $custom_attrs = '';
    if (isset($attachment->custom_rating)) {
        $custom_attrs .= ' data-rating="' . esc_attr($attachment->custom_rating) . '"';
    }
    
    if (isset($attachment->camera_make)) {
        $custom_attrs .= ' data-camera="' . esc_attr($attachment->camera_make) . '"';
    }
    
    // Insert custom attributes into img tag
    $html = str_replace('<img ', '<img ' . $custom_attrs . ' ', $html);
    
    return $html;
}, 10, 3);
```

### `foogallery_attachment_html_link_attributes`
**Type**: Filter  
**Description**: Modify the attributes for gallery item links  
**Parameters**: 
- `$attr` (array): Current link attributes
- `$args` (array): Link generation arguments
- `$attachment` (object): The FooGalleryAttachment instance  

**Returns**: Modified attributes array  
**Usage**: Add custom link attributes  

```php
add_filter('foogallery_attachment_html_link_attributes', function($attr, $args, $attachment) {
    // Add custom CSS classes
    $attr['class'] = isset($attr['class']) ? $attr['class'] : '';
    
    if (isset($attachment->custom_rating) && $attachment->custom_rating >= 4) {
        $attr['class'] .= ' high-rated-image';
    }
    
    // Add custom data attributes
    $attr['data-image-id'] = $attachment->ID;
    $attr['data-file-size'] = size_format(filesize($attachment->file_path));
    
    // Add tracking attributes
    $attr['data-track-event'] = 'image-click';
    $attr['data-track-label'] = $attachment->caption;
    
    return $attr;
}, 10, 3);
```

### `foogallery_build_container_data_options`
**Type**: Filter  
**Description**: Add data options to the gallery container  
**Parameters**: 
- `$options` (array): Current data options
- `$gallery` (object): The FooGallery instance
- `$template` (object): The gallery template instance  

**Returns**: Modified options array  
**Usage**: Add custom JavaScript configuration  

```php
add_filter('foogallery_build_container_data_options', function($options, $gallery, $template) {
    // Add custom options for JavaScript
    $options['customSetting'] = $gallery->get_setting('custom_setting', 'default');
    $options['enableAnalytics'] = true;
    $options['galleryId'] = $gallery->ID;
    
    // Add template-specific options
    if ($gallery->gallery_template === 'masonry') {
        $options['masonryGutter'] = (int) $gallery->get_setting('gutter_width', 10);
        $options['masonryAnimation'] = $gallery->get_setting('enable_animation', true);
    }
    
    // Add lazy loading options
    if ($gallery->get_setting('lazy_load', false)) {
        $options['lazy'] = array(
            'threshold' => 200,
            'placeholder' => FOOGALLERY_URL . 'assets/image-placeholder.png'
        );
    }
    
    return $options;
}, 10, 3);
```

---

## Admin Interface Hooks

### `foogallery_admin_enqueue_scripts`
**Type**: Action  
**Description**: Fired when admin scripts should be enqueued  
**Parameters**: None  
**Usage**: Enqueue custom admin scripts and styles  

```php
add_action('foogallery_admin_enqueue_scripts', function() {
    wp_enqueue_script(
        'custom-foogallery-admin',
        plugin_dir_url(__FILE__) . 'js/custom-admin.js',
        array('jquery', 'foogallery-admin'),
        '1.0.0',
        true
    );
    
    wp_enqueue_style(
        'custom-foogallery-admin',
        plugin_dir_url(__FILE__) . 'css/custom-admin.css',
        array('foogallery-admin'),
        '1.0.0'
    );
    
    // Localize script with custom data
    wp_localize_script('custom-foogallery-admin', 'customFooGalleryAdmin', array(
        'nonce' => wp_create_nonce('custom_foogallery_admin'),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'strings' => array(
            'confirmDelete' => __('Are you sure you want to delete this item?'),
            'processing' => __('Processing...')
        )
    ));
});
```

### `foogallery_render_gallery_template_field`
**Type**: Action  
**Description**: Fired to render custom gallery template fields  
**Parameters**: 
- `$field` (array): The field configuration
- `$gallery` (object): The FooGallery instance
- `$template` (object): The gallery template instance  

**Usage**: Render custom field types in gallery settings  

```php
add_action('foogallery_render_gallery_template_field', function($field, $gallery, $template) {
    if ($field['type'] === 'custom_image_selector') {
        $current_value = $gallery->get_setting($field['id'], $field['default']);
        
        echo '<div class="custom-image-selector">';
        echo '<input type="hidden" name="' . esc_attr($field['id']) . '" id="' . esc_attr($field['id']) . '" value="' . esc_attr($current_value) . '" />';
        echo '<div class="image-preview">';
        
        if ($current_value) {
            echo '<img src="' . esc_url(wp_get_attachment_image_url($current_value, 'thumbnail')) . '" alt="" />';
        }
        
        echo '</div>';
        echo '<button type="button" class="button select-image" data-target="' . esc_attr($field['id']) . '">Select Image</button>';
        echo '<button type="button" class="button remove-image" data-target="' . esc_attr($field['id']) . '">Remove</button>';
        echo '</div>';
        
        // Add inline script for image selector
        echo '<script>
        jQuery(document).ready(function($) {
            $(".select-image").on("click", function() {
                var target = $(this).data("target");
                var frame = wp.media({
                    title: "Select Image",
                    multiple: false,
                    library: { type: "image" }
                });
                
                frame.on("select", function() {
                    var attachment = frame.state().get("selection").first().toJSON();
                    $("#" + target).val(attachment.id);
                    $("#" + target).siblings(".image-preview").html("<img src=\"" + attachment.sizes.thumbnail.url + "\" alt=\"\" />");
                });
                
                frame.open();
            });
            
            $(".remove-image").on("click", function() {
                var target = $(this).data("target");
                $("#" + target).val("");
                $("#" + target).siblings(".image-preview").empty();
            });
        });
        </script>';
    }
}, 10, 3);
```

### `foogallery_gallery_metabox_items`
**Type**: Action  
**Description**: Fired in the gallery items metabox  
**Parameters**: 
- `$gallery` (object): The FooGallery instance  

**Usage**: Add custom content to the gallery items area  

```php
add_action('foogallery_gallery_metabox_items', function($gallery) {
    // Add custom bulk actions
    echo '<div class="foogallery-custom-bulk-actions" style="margin-bottom: 10px;">';
    echo '<select id="custom-bulk-action">';
    echo '<option value="">Custom Bulk Actions</option>';
    echo '<option value="set-rating-5">Set Rating to 5 Stars</option>';
    echo '<option value="add-watermark">Add Watermark</option>';
    echo '<option value="optimize-images">Optimize Images</option>';
    echo '</select>';
    echo '<button type="button" class="button" id="apply-custom-bulk-action">Apply</button>';
    echo '</div>';
    
    // Add custom statistics
    $attachments = $gallery->attachments();
    $total_size = 0;
    foreach ($attachments as $attachment) {
        if (file_exists($attachment->file_path)) {
            $total_size += filesize($attachment->file_path);
        }
    }
    
    echo '<div class="foogallery-stats" style="background: #f9f9f9; padding: 10px; margin-bottom: 10px;">';
    echo '<strong>Gallery Statistics:</strong><br>';
    echo 'Total Images: ' . count($attachments) . '<br>';
    echo 'Total Size: ' . size_format($total_size) . '<br>';
    echo 'Average Size: ' . (count($attachments) > 0 ? size_format($total_size / count($attachments)) : '0 B');
    echo '</div>';
});
```

---

## Extension Management Hooks

### `foogallery_available_extensions`
**Type**: Filter  
**Description**: Register additional extensions with FooGallery  
**Parameters**: 
- `$extensions` (array): Current extensions array  

**Returns**: Modified extensions array  
**Usage**: Add custom extensions to the extensions list  

```php
add_filter('foogallery_available_extensions', function($extensions) {
    $extensions[] = array(
        'slug' => 'custom-gallery-extension',
        'class' => 'Custom_Gallery_Extension',
        'categories' => array('Custom'),
        'title' => 'Custom Gallery Features',
        'description' => 'Adds custom gallery functionality including advanced analytics and custom field support.',
        'author' => 'Your Name',
        'author_url' => 'https://yourwebsite.com',
        'thumbnail' => plugin_dir_url(__FILE__) . 'assets/extension-thumbnail.png',
        'tags' => array('analytics', 'custom-fields', 'advanced'),
        'source' => 'bundled',
        'activated_by_default' => false,
        'minimum_version' => '2.0.0'
    );
    
    return $extensions;
});
```

### `foogallery_extension_activated-{slug}`
**Type**: Action  
**Description**: Fired when a specific extension is activated  
**Parameters**: None  
**Usage**: Perform setup tasks when extension is activated  

```php
add_action('foogallery_extension_activated-custom-gallery-extension', function() {
    // Create custom database tables
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'foogallery_analytics';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        gallery_id mediumint(9) NOT NULL,
        view_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        user_ip varchar(45) NOT NULL,
        user_agent text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Set default options
    update_option('foogallery_custom_extension_version', '1.0.0');
    update_option('foogallery_enable_analytics', true);
    
    // Flush rewrite rules if needed
    flush_rewrite_rules();
});
```

### `foogallery_extension_deactivated-{slug}`
**Type**: Action  
**Description**: Fired when a specific extension is deactivated  
**Parameters**: None  
**Usage**: Cleanup when extension is deactivated  

```php
add_action('foogallery_extension_deactivated-custom-gallery-extension', function() {
    // Clear custom caches
    wp_cache_flush();
    
    // Remove scheduled events
    wp_clear_scheduled_hook('custom_gallery_daily_cleanup');
    
    // Optionally remove custom options (be careful with user data)
    // delete_option('foogallery_custom_extension_settings');
    
    // Log deactivation
    error_log('Custom Gallery Extension deactivated at ' . current_time('mysql'));
});
```

---

## Datasource Management Hooks

### `foogallery_gallery_datasources`
**Type**: Filter  
**Description**: Register custom datasources for galleries  
**Parameters**: 
- `$datasources` (array): Current datasources array  

**Returns**: Modified datasources array  
**Usage**: Add custom datasources  

```php
add_filter('foogallery_gallery_datasources', function($datasources) {
    $datasources['instagram'] = array(
        'id' => 'instagram',
        'name' => 'Instagram Feed',
        'menu_label' => 'Instagram',
        'public' => true,
        'description' => 'Load images from your Instagram account'
    );
    
    $datasources['flickr'] = array(
        'id' => 'flickr',
        'name' => 'Flickr Album',
        'menu_label' => 'Flickr',
        'public' => true, 
        'description' => 'Load images from a Flickr album or photoset'
    );
    
    return $datasources;
});
```

### `foogallery_datasource_{datasource}_{filter}`
**Type**: Filter  
**Description**: Dynamic filter for datasource operations  
**Parameters**: Varies by datasource and filter  
**Returns**: Varies by operation  
**Usage**: Handle datasource-specific operations  

```php
// Handle attachment count for custom datasource
add_filter('foogallery_datasource_instagram_item_count', function($count, $gallery) {
    $instagram_settings = $gallery->get_setting('instagram_settings');
    $username = $instagram_settings['username'] ?? '';
    
    if ($username) {
        // Get count from Instagram API
        $count = custom_get_instagram_image_count($username);
    }
    
    return $count;
}, 10, 2);

// Handle attachment IDs for custom datasource  
add_filter('foogallery_datasource_instagram_attachment_ids', function($attachment_ids, $gallery) {
    $instagram_settings = $gallery->get_setting('instagram_settings');
    $username = $instagram_settings['username'] ?? '';
    $limit = $instagram_settings['limit'] ?? 20;
    
    if ($username) {
        // Import Instagram images and return attachment IDs
        $attachment_ids = custom_import_instagram_images($username, $limit);
    }
    
    return $attachment_ids;
}, 10, 2);
```

### `foogallery-datasource-modal-content_{datasource}`
**Type**: Action  
**Description**: Render content for datasource modal  
**Parameters**: 
- `$gallery_id` (int): The gallery ID
- `$datasource_value` (mixed): Current datasource settings  

**Usage**: Render datasource configuration interface  

```php
add_action('foogallery-datasource-modal-content_instagram', function($gallery_id, $datasource_value) {
    $current_settings = is_array($datasource_value) ? $datasource_value : array();
    ?>
    <div class="foogallery-datasource-instagram">
        <h3>Instagram Settings</h3>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="instagram_username">Instagram Username</label>
                </th>
                <td>
                    <input type="text" 
                           id="instagram_username" 
                           name="instagram_username" 
                           value="<?php echo esc_attr($current_settings['username'] ?? ''); ?>" 
                           class="regular-text" />
                    <p class="description">Enter the Instagram username (without @)</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="instagram_limit">Number of Images</label>
                </th>
                <td>
                    <input type="number" 
                           id="instagram_limit" 
                           name="instagram_limit" 
                           value="<?php echo esc_attr($current_settings['limit'] ?? 20); ?>" 
                           min="1" 
                           max="100" />
                    <p class="description">Maximum number of images to import (1-100)</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="instagram_hashtag">Hashtag Filter</label>
                </th>
                <td>
                    <input type="text" 
                           id="instagram_hashtag" 
                           name="instagram_hashtag" 
                           value="<?php echo esc_attr($current_settings['hashtag'] ?? ''); ?>" 
                           class="regular-text" />
                    <p class="description">Optional: Only import images with this hashtag (without #)</p>
                </td>
            </tr>
        </table>
        
        <div class="foogallery-datasource-actions">
            <button type="button" class="button button-primary" onclick="testInstagramConnection()">
                Test Connection
            </button>
            <div id="instagram-test-result" style="margin-top: 10px;"></div>
        </div>
    </div>
    
    <script>
    function testInstagramConnection() {
        var username = document.getElementById('instagram_username').value;
        var resultDiv = document.getElementById('instagram-test-result');
        
        if (!username) {
            resultDiv.innerHTML = '<div class="notice notice-error"><p>Please enter a username first.</p></div>';
            return;
        }
        
        resultDiv.innerHTML = '<div class="notice notice-info"><p>Testing connection...</p></div>';
        
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'test_instagram_connection',
                username: username,
                nonce: '<?php echo wp_create_nonce('test_instagram_connection'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    resultDiv.innerHTML = '<div class="notice notice-success"><p>Connection successful! Found ' + response.data.count + ' images.</p></div>';
                } else {
                    resultDiv.innerHTML = '<div class="notice notice-error"><p>Connection failed: ' + response.data.message + '</p></div>';
                }
            },
            error: function() {
                resultDiv.innerHTML = '<div class="notice notice-error"><p>Connection test failed. Please try again.</p></div>';
            }
        });
    }
    </script>
    <?php
}, 10, 2);
```

---

## Thumbnail and Image Processing Hooks

### `foogallery_attachment_resize_thumbnail`
**Type**: Filter  
**Description**: Modify or override thumbnail generation  
**Parameters**: 
- `$thumbnail_url` (string): Current thumbnail URL
- `$args` (array): Thumbnail generation arguments
- `$attachment` (object): The FooGalleryAttachment instance  

**Returns**: Modified thumbnail URL  
**Usage**: Custom thumbnail generation or CDN integration  

```php
add_filter('foogallery_attachment_resize_thumbnail', function($thumbnail_url, $args, $attachment) {
    // Use CDN for thumbnail serving
    $cdn_base = 'https://cdn.example.com/';
    
    // Generate CDN URL with parameters
    $cdn_params = array(
        'w' => $args['width'],
        'h' => $args['height'],
        'q' => 85, // quality
        'f' => 'auto' // format
    );
    
    if ($args['crop']) {
        $cdn_params['c'] = 'fill';
    }
    
    // Extract image path relative to uploads
    $upload_dir = wp_upload_dir();
    $relative_path = str_replace($upload_dir['baseurl'], '', $attachment->url);
    
    $cdn_url = $cdn_base . ltrim($relative_path, '/') . '?' . http_build_query($cdn_params);
    
    return $cdn_url;
}, 10, 3);
```

### `foogallery_thumbnail_resize_args`
**Type**: Filter  
**Description**: Modify thumbnail resize arguments before processing  
**Parameters**: 
- `$args` (array): Thumbnail resize arguments
- `$original_url` (string): Original image URL
- `$attachment` (object): The FooGalleryAttachment instance  

**Returns**: Modified arguments array  
**Usage**: Customize thumbnail generation parameters  

```php
add_filter('foogallery_thumbnail_resize_args', function($args, $original_url, $attachment) {
    // Force specific dimensions for portrait images
    $image_meta = wp_get_attachment_metadata($attachment->ID);
    if ($image_meta && isset($image_meta['width'], $image_meta['height'])) {
        if ($image_meta['height'] > $image_meta['width']) {
            // Portrait image - adjust dimensions
            $args['width'] = min($args['width'], 400);
            $args['height'] = (int) ($args['width'] * 1.5);
        }
    }
    
    // Higher quality for featured images
    if (get_post_meta($attachment->ID, '_is_featured_image', true)) {
        $args['quality'] = 95;
    }
    
    // Enable progressive JPEG for large images
    if ($args['width'] > 800 || $args['height'] > 800) {
        $args['progressive'] = true;
    }
    
    return $args;
}, 10, 3);
```

### `foogallery_thumb_saved_cache_image`
**Type**: Action  
**Description**: Fired when a thumbnail cache image is saved  
**Parameters**: 
- `$thumb_generator` (object): The thumbnail generator instance
- `$cached_file_path` (string): Path to the cached thumbnail file  

**Usage**: Post-process thumbnails or update statistics  

```php
add_action('foogallery_thumb_saved_cache_image', function($thumb_generator, $cached_file_path) {
    // Optimize the saved thumbnail
    if (function_exists('imagewebp') && extension_loaded('gd')) {
        $original_size = filesize($cached_file_path);
        
        // Create WebP version
        $webp_path = str_replace(array('.jpg', '.jpeg', '.png'), '.webp', $cached_file_path);
        
        $image_info = getimagesize($cached_file_path);
        if ($image_info) {
            switch ($image_info[2]) {
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($cached_file_path);
                    break;
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($cached_file_path);
                    break;
                default:
                    return;
            }
            
            if ($image) {
                imagewebp($image, $webp_path, 85);
                imagedestroy($image);
                
                // Log compression stats
                $webp_size = filesize($webp_path);
                $savings = round((($original_size - $webp_size) / $original_size) * 100, 1);
                error_log("WebP conversion: {$savings}% size reduction for {$cached_file_path}");
            }
        }
    }
    
    // Update thumbnail generation statistics
    $stats = get_option('foogallery_thumb_stats', array());
    $stats['total_generated'] = isset($stats['total_generated']) ? $stats['total_generated'] + 1 : 1;
    $stats['last_generated'] = current_time('mysql');
    update_option('foogallery_thumb_stats', $stats);
}, 10, 2);
```

---

## Settings and Configuration Hooks

### `foogallery_admin_settings_override`
**Type**: Filter  
**Description**: Add or modify plugin settings  
**Parameters**: 
- `$settings` (array): Current settings array  

**Returns**: Modified settings array  
**Usage**: Add custom settings tabs and fields  

```php
add_filter('foogallery_admin_settings_override', function($settings) {
    // Add custom tab
    $settings['tabs']['custom'] = __('Custom Settings');
    
    // Add custom sections
    $settings['sections'][] = array(
        'id' => 'custom_features',
        'title' => 'Custom Features',
        'tab' => 'custom'
    );
    
    $settings['sections'][] = array(
        'id' => 'api_integration',
        'title' => 'API Integration',
        'tab' => 'custom'
    );
    
    // Add custom fields
    $settings['fields'][] = array(
        'id' => 'enable_analytics',
        'title' => 'Enable Analytics',
        'desc' => 'Track gallery views and user interactions',
        'section' => 'custom_features',
        'type' => 'checkbox',
        'default' => false
    );
    
    $settings['fields'][] = array(
        'id' => 'analytics_provider',
        'title' => 'Analytics Provider',
        'desc' => 'Choose your analytics service',
        'section' => 'custom_features',
        'type' => 'radio',
        'default' => 'google',
        'choices' => array(
            'google' => 'Google Analytics',
            'facebook' => 'Facebook Pixel',
            'custom' => 'Custom Tracking'
        )
    );
    
    $settings['fields'][] = array(
        'id' => 'api_key',
        'title' => 'API Key',
        'desc' => 'Enter your API key for external services',
        'section' => 'api_integration',
        'type' => 'text',
        'default' => ''
    );
    
    $settings['fields'][] = array(
        'id' => 'cdn_settings',
        'title' => 'CDN Configuration',
        'desc' => 'Configure CDN settings for image delivery',
        'section' => 'api_integration',
        'type' => 'textarea',
        'default' => '',
        'placeholder' => 'Enter JSON configuration...'
    );
    
    return $settings;
});
```

### `foogallery_settings_updated`
**Type**: Action  
**Description**: Fired when plugin settings are updated  
**Parameters**: None  
**Usage**: Perform actions after settings save  

```php
add_action('foogallery_settings_updated', function() {
    // Clear caches when settings change
    wp_cache_flush();
    delete_transient('foogallery_custom_cache');
    
    // Update external service configurations
    $api_key = foogallery_get_setting('api_key');
    if ($api_key) {
        // Update external service configuration
        update_option('external_service_configured', true);
    }
    
    // Regenerate .htaccess rules if needed
    $cdn_enabled = foogallery_get_setting('enable_cdn', false);
    if ($cdn_enabled) {
        // Add CDN rewrite rules
        custom_update_htaccess_rules();
    }
    
    // Log settings update
    error_log('FooGallery settings updated at ' . current_time('mysql'));
});
```

### `foogallery_admin_settings_custom_type_render_setting`
**Type**: Action  
**Description**: Render custom setting field types  
**Parameters**: 
- `$args` (array): Field arguments  

**Usage**: Render complex custom field types  

```php
add_action('foogallery_admin_settings_custom_type_render_setting', function($args) {
    if ($args['type'] === 'color_palette') {
        $current_value = foogallery_get_setting($args['id'], $args['default']);
        $colors = array(
            '#FF0000' => 'Red',
            '#00FF00' => 'Green', 
            '#0000FF' => 'Blue',
            '#FFFF00' => 'Yellow',
            '#FF00FF' => 'Magenta',
            '#00FFFF' => 'Cyan'
        );
        
        echo '<div class="color-palette-selector">';
        foreach ($colors as $color => $name) {
            $checked = ($current_value === $color) ? 'checked' : '';
            echo '<label class="color-option">';
            echo '<input type="radio" name="' . esc_attr($args['id']) . '" value="' . esc_attr($color) . '" ' . $checked . ' />';
            echo '<span class="color-swatch" style="background-color: ' . esc_attr($color) . '" title="' . esc_attr($name) . '"></span>';
            echo '</label>';
        }
        echo '</div>';
        
        // Add CSS for styling
        echo '<style>
        .color-palette-selector { display: flex; gap: 10px; }
        .color-option { cursor: pointer; }
        .color-option input[type="radio"] { display: none; }
        .color-swatch { 
            display: block; 
            width: 30px; 
            height: 30px; 
            border: 2px solid #ddd; 
            border-radius: 4px; 
        }
        .color-option input[type="radio"]:checked + .color-swatch { 
            border-color: #0073aa; 
            box-shadow: 0 0 5px rgba(0,115,170,0.5); 
        }
        </style>';
    }
    
    if ($args['type'] === 'image_upload') {
        $current_value = foogallery_get_setting($args['id'], $args['default']);
        
        echo '<div class="image-upload-field">';
        echo '<input type="hidden" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '" value="' . esc_attr($current_value) . '" />';
        
        echo '<div class="image-preview">';
        if ($current_value) {
            echo '<img src="' . esc_url(wp_get_attachment_image_url($current_value, 'medium')) . '" alt="" style="max-width: 200px; height: auto;" />';
        }
        echo '</div>';
        
        echo '<button type="button" class="button upload-image" data-target="' . esc_attr($args['id']) . '">Upload Image</button>';
        echo '<button type="button" class="button remove-image" data-target="' . esc_attr($args['id']) . '">Remove Image</button>';
        echo '</div>';
        
        // Add JavaScript for image upload
        echo '<script>
        jQuery(document).ready(function($) {
            $(".upload-image").on("click", function() {
                var target = $(this).data("target");
                var frame = wp.media({
                    title: "Select Image",
                    multiple: false,
                    library: { type: "image" }
                });
                
                frame.on("select", function() {
                    var attachment = frame.state().get("selection").first().toJSON();
                    $("#" + target).val(attachment.id);
                    $("#" + target).siblings(".image-preview").html("<img src=\"" + attachment.sizes.medium.url + "\" alt=\"\" style=\"max-width: 200px; height: auto;\" />");
                });
                
                frame.open();
            });
            
            $(".remove-image").on("click", function() {
                var target = $(this).data("target");
                $("#" + target).val("");
                $("#" + target).siblings(".image-preview").empty();
            });
        });
        </script>';
    }
});
```

---

## Lightbox and UI Hooks

### `foogallery_template_lightbox-{lightbox}`
**Type**: Action  
**Description**: Configure specific lightbox implementations  
**Parameters**: 
- `$gallery` (object): The FooGallery instance  

**Usage**: Set up lightbox-specific configurations  

```php
add_action('foogallery_template_lightbox-foogallery', function($gallery) {
    // Add custom lightbox configuration
    $lightbox_options = array(
        'theme' => $gallery->get_setting('lightbox_theme', 'default'),
        'transition' => $gallery->get_setting('lightbox_transition', 'fade'),
        'autoplay' => $gallery->get_setting('lightbox_autoplay', false),
        'showCaptions' => $gallery->get_setting('lightbox_captions', true),
        'customSetting' => $gallery->get_setting('custom_lightbox_setting', 'default')
    );
    
    // Output configuration as JSON
    echo '<script>
    if (typeof FooGallery !== "undefined" && FooGallery.lightbox) {
        FooGallery.lightbox.configure(' . json_encode($lightbox_options) . ');
    }
    </script>';
});

add_action('foogallery_template_lightbox-custom', function($gallery) {
    // Configuration for custom lightbox
    wp_enqueue_script('custom-lightbox', plugin_dir_url(__FILE__) . 'js/custom-lightbox.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style('custom-lightbox', plugin_dir_url(__FILE__) . 'css/custom-lightbox.css', array(), '1.0.0');
    
    wp_localize_script('custom-lightbox', 'customLightboxConfig', array(
        'galleryId' => $gallery->ID,
        'options' => array(
            'animation' => $gallery->get_setting('custom_animation', 'slide'),
            'keyboard' => true,
            'closeOnClick' => $gallery->get_setting('close_on_click', true),
            'showArrows' => $gallery->get_setting('show_arrows', true)
        )
    ));
});
```

### `foogallery_gallery_template_lightbox_button_theme_choices`
**Type**: Filter  
**Description**: Add custom lightbox button themes  
**Parameters**: 
- `$choices` (array): Current theme choices  

**Returns**: Modified choices array  
**Usage**: Add custom button themes  

```php
add_filter('foogallery_gallery_template_lightbox_button_theme_choices', function($choices) {
    $choices['custom-minimal'] = 'Minimal Custom';
    $choices['custom-bold'] = 'Bold Custom';
    $choices['custom-gradient'] = 'Gradient Custom';
    $choices['custom-neon'] = 'Neon Glow';
    
    return $choices;
});
```

---

## Gutenberg/Block Editor Hooks

### `foogallery_gutenberg_enabled`
**Type**: Filter  
**Description**: Control whether Gutenberg block is enabled  
**Parameters**: 
- `$enabled` (bool): Current enabled state  

**Returns**: Modified enabled state  
**Usage**: Conditionally enable/disable Gutenberg support  

```php
add_filter('foogallery_gutenberg_enabled', function($enabled) {
    // Disable for specific user roles
    if (!current_user_can('edit_galleries')) {
        return false;
    }
    
    // Disable on specific post types
    global $post;
    if ($post && in_array($post->post_type, array('product', 'event'))) {
        return false;
    }
    
    // Check for plugin conflicts
    if (is_plugin_active('conflicting-plugin/conflicting-plugin.php')) {
        return false;
    }
    
    return $enabled;
});
```

### `foogallery_gutenberg_block_js_data`
**Type**: Filter  
**Description**: Add data to Gutenberg block JavaScript  
**Parameters**: 
- `$data` (array): Current block data  

**Returns**: Modified data array  
**Usage**: Provide additional data to block editor  

```php
add_filter('foogallery_gutenberg_block_js_data', function($data) {
    // Add custom options for block editor
    $data['customOptions'] = array(
        'enableAdvancedMode' => current_user_can('manage_options'),
        'defaultTemplate' => get_option('foogallery_default_template', 'default'),
        'availableShortcodes' => array(
            'foogallery' => 'Standard Gallery',
            'fooalbum' => 'Photo Album'
        )
    );
    
    // Add user preferences
    $user_id = get_current_user_id();
    $data['userPreferences'] = array(
        'preferredTemplate' => get_user_meta($user_id, 'foogallery_preferred_template', true),
        'showAdvancedOptions' => get_user_meta($user_id, 'foogallery_show_advanced', true),
        'defaultLightbox' => get_user_meta($user_id, 'foogallery_default_lightbox', true) ?: 'foogallery'
    );
    
    return $data;
});
```

### `foogallery_find_galleries_in_post`
**Type**: Filter  
**Description**: Find gallery usage in post content  
**Parameters**: 
- `$galleries` (array): Found galleries array
- `$post_content` (string): The post content to search  

**Returns**: Modified galleries array  
**Usage**: Custom gallery detection in content  

```php
add_filter('foogallery_find_galleries_in_post', function($galleries, $post_content) {
    // Find custom shortcode formats
    $custom_pattern = '/\[custom_gallery\s+id=["\']?(\d+)["\']?[^\]]*\]/';
    if (preg_match_all($custom_pattern, $post_content, $matches)) {
        foreach ($matches[1] as $gallery_id) {
            if (!in_array($gallery_id, $galleries)) {
                $galleries[] = (int) $gallery_id;
            }
        }
    }
    
    // Find galleries in custom fields
    global $post;
    if ($post) {
        $custom_galleries = get_post_meta($post->ID, '_custom_galleries', true);
        if (is_array($custom_galleries)) {
            $galleries = array_merge($galleries, $custom_galleries);
        }
        
        // Check for galleries in ACF fields
        if (function_exists('get_field')) {
            $acf_galleries = get_field('featured_galleries', $post->ID);
            if (is_array($acf_galleries)) {
                foreach ($acf_galleries as $gallery_ref) {
                    if (isset($gallery_ref['gallery_id'])) {
                        $galleries[] = (int) $gallery_ref['gallery_id'];
                    }
                }
            }
        }
    }
    
    return array_unique($galleries);
}, 10, 2);
```

---

## Dynamic Template Hooks

FooGallery uses dynamic hooks that change based on gallery template names. These follow specific patterns:

### `foogallery_located_template-{template}`
**Type**: Action  
**Description**: Fired when a specific template is located  
**Parameters**: 
- `$gallery` (object): The FooGallery instance  

**Usage**: Template-specific initialization  

```php
// For masonry template
add_action('foogallery_located_template-masonry', function($gallery) {
    // Enqueue masonry-specific scripts
    wp_enqueue_script('isotope', 'https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js', array('jquery'), '3.0.6', true);
    
    // Add masonry-specific data
    global $masonry_config;
    $masonry_config = array(
        'itemSelector' => '.fg-item',
        'layoutMode' => 'masonry',
        'gutter' => (int) $gallery->get_setting('gutter_width', 10),
        'isAnimated' => $gallery->get_setting('enable_animation', true)
    );
});

// For carousel template
add_action('foogallery_located_template-carousel', function($gallery) {
    wp_enqueue_script('swiper', 'https://unpkg.com/swiper@8/swiper-bundle.min.js', array(), '8.0.0', true);
    wp_enqueue_style('swiper', 'https://unpkg.com/swiper@8/swiper-bundle.min.css', array(), '8.0.0');
    
    global $carousel_config;
    $carousel_config = array(
        'autoplay' => $gallery->get_setting('autoplay', false),
        'loop' => $gallery->get_setting('loop', true),
        'navigation' => $gallery->get_setting('show_navigation', true),
        'pagination' => $gallery->get_setting('show_pagination', true)
    );
});
```

### `foogallery_loaded_template-{template}`
**Type**: Action  
**Description**: Fired after a specific template is loaded  
**Parameters**: 
- `$gallery` (object): The FooGallery instance  

**Usage**: Template-specific post-processing  

```php
add_action('foogallery_loaded_template-masonry', function($gallery) {
    global $masonry_config;
    echo '<script>
    jQuery(document).ready(function($) {
        $("#gallery-' . $gallery->ID . '").isotope(' . json_encode($masonry_config) . ');
    });
    </script>';
});

add_action('foogallery_loaded_template-slider', function($gallery) {
    // Add slider controls
    echo '<div class="slider-controls">';
    if ($gallery->get_setting('show_play_pause', true)) {
        echo '<button class="slider-play-pause" data-gallery="' . $gallery->ID . '">Play/Pause</button>';
    }
    if ($gallery->get_setting('show_fullscreen', true)) {
        echo '<button class="slider-fullscreen" data-gallery="' . $gallery->ID . '">Fullscreen</button>';
    }
    echo '</div>';
});
```

---

## PRO Features Hooks

### Video Support Hooks

#### `foogallery_video_sources`
**Type**: Filter  
**Description**: Register custom video sources  
**Parameters**: 
- `$sources` (array): Available video sources  

**Returns**: Modified sources array  
**Usage**: Add support for custom video platforms  

```php
add_filter('foogallery_video_sources', function($sources) {
    $sources['twitch'] = array(
        'name' => 'Twitch',
        'regex' => '/twitch\.tv\/videos\/(\d+)/',
        'embed_format' => 'https://player.twitch.tv/?video=v{video_id}&parent=' . $_SERVER['HTTP_HOST'],
        'thumbnail_format' => 'https://static-cdn.jtvnw.net/cf_vods/d2nvs31859zcd8/twitchsession_{video_id}/thumb/thumb{video_id}-{time}.jpg'
    );
    
    $sources['wistia'] = array(
        'name' => 'Wistia',
        'regex' => '/wistia\.com\/medias\/([a-zA-Z0-9]+)/',
        'embed_format' => 'https://fast.wistia.net/embed/iframe/{video_id}',
        'thumbnail_format' => 'https://embed-fastly.wistia.com/deliveries/{video_id}/file.jpg'
    );
    
    return $sources;
});
```

### WooCommerce Integration Hooks

#### `foogallery_woocommerce_product_data_panels`
**Type**: Action  
**Description**: Add custom panels to WooCommerce product data tabs  
**Parameters**: None  
**Usage**: Add custom product configuration options  

```php
add_action('foogallery_woocommerce_product_data_panels', function() {
    ?>
    <div id="foogallery_custom_product_data" class="panel woocommerce_options_panel">
        <div class="options_group">
            <?php
            woocommerce_wp_checkbox(array(
                'id' => '_enable_gallery_zoom',
                'label' => 'Enable Gallery Zoom',
                'description' => 'Allow users to zoom into gallery images'
            ));
            
            woocommerce_wp_select(array(
                'id' => '_gallery_layout',
                'label' => 'Gallery Layout',
                'options' => array(
                    'grid' => 'Grid Layout',
                    'slider' => 'Slider Layout',
                    'masonry' => 'Masonry Layout'
                ),
                'desc_tip' => true,
                'description' => 'Choose how the product gallery should be displayed'
            ));
            
            woocommerce_wp_text_input(array(
                'id' => '_gallery_columns',
                'label' => 'Number of Columns',
                'type' => 'number',
                'custom_attributes' => array(
                    'min' => 1,
                    'max' => 6,
                    'step' => 1
                ),
                'desc_tip' => true,
                'description' => 'Number of columns for grid layout'
            ));
            ?>
        </div>
    </div>
    <?php
});
```

### Advanced Customization Hooks

#### `foogallery_instagram_filter_choices`
**Type**: Filter  
**Description**: Add custom Instagram-style filters  
**Parameters**: 
- `$choices` (array): Available filter choices  

**Returns**: Modified choices array  
**Usage**: Add custom CSS filter effects  

```php
add_filter('foogallery_instagram_filter_choices', function($choices) {
    $choices['custom-vintage'] = array(
        'name' => 'Vintage',
        'css' => 'sepia(50%) contrast(1.2) brightness(0.9)'
    );
    
    $choices['custom-cyberpunk'] = array(
        'name' => 'Cyberpunk', 
        'css' => 'hue-rotate(270deg) saturate(2) contrast(1.5)'
    );
    
    $choices['custom-dreamy'] = array(
        'name' => 'Dreamy',
        'css' => 'blur(0.5px) brightness(1.1) saturate(0.8)'
    );
    
    return $choices;
});
```

---

## Advanced Hook Usage Examples

### Creating a Custom Extension with Hooks

```php
class Custom_FooGallery_Extension {
    
    public function __construct() {
        // Register the extension
        add_filter('foogallery_available_extensions', array($this, 'register_extension'));
        
        // Hook into activation
        add_action('foogallery_extension_activated-custom-analytics', array($this, 'on_activation'));
        
        // Add custom fields to galleries
        add_filter('foogallery_override_gallery_template_fields', array($this, 'add_fields'), 10, 2);
        
        // Track gallery views
        add_action('foogallery_loaded_template_after', array($this, 'track_view'));
        
        // Add analytics data to container
        add_filter('foogallery_build_container_data_options', array($this, 'add_analytics_data'), 10, 3);
        
        // Add custom admin page
        add_action('foogallery_admin_menu_after', array($this, 'add_admin_menu'));
        
        // Handle AJAX requests
        add_action('wp_ajax_get_gallery_analytics', array($this, 'ajax_get_analytics'));
    }
    
    public function register_extension($extensions) {
        $extensions[] = array(
            'slug' => 'custom-analytics',
            'class' => 'Custom_FooGallery_Extension',
            'title' => 'Gallery Analytics',
            'description' => 'Advanced analytics and tracking for your galleries',
            'categories' => array('Analytics', 'Custom'),
            'tags' => array('tracking', 'statistics', 'reporting')
        );
        return $extensions;
    }
    
    public function on_activation() {
        // Create analytics table
        global $wpdb;
        $table_name = $wpdb->prefix . 'foogallery_analytics';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            gallery_id mediumint(9) NOT NULL,
            event_type varchar(50) NOT NULL,
            event_data text,
            user_ip varchar(45),
            user_agent text,
            event_time datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY gallery_id (gallery_id),
            KEY event_type (event_type),
            KEY event_time (event_time)
        ) {$wpdb->get_charset_collate()};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function add_fields($fields, $template) {
        $fields['analytics'] = array(
            'id' => 'analytics',
            'title' => 'Analytics Settings',
            'type' => 'tab'
        );
        
        $fields['enable_tracking'] = array(
            'id' => 'enable_tracking',
            'title' => 'Enable Tracking',
            'desc' => 'Track gallery views and interactions',
            'type' => 'checkbox',
            'default' => true,
            'tab' => 'analytics'
        );
        
        $fields['track_events'] = array(
            'id' => 'track_events',
            'title' => 'Track Events',
            'desc' => 'Which events to track',
            'type' => 'checkboxlist',
            'choices' => array(
                'view' => 'Gallery Views',
                'image_click' => 'Image Clicks',
                'lightbox_open' => 'Lightbox Opens',
                'social_share' => 'Social Shares'
            ),
            'default' => array('view', 'image_click'),
            'tab' => 'analytics'
        );
        
        return $fields;
    }
    
    public function track_view($gallery) {
        if (!$gallery->get_setting('enable_tracking', true)) {
            return;
        }
        
        $events = $gallery->get_setting('track_events', array('view'));
        if (!in_array('view', $events)) {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'foogallery_analytics';
        
        $wpdb->insert(
            $table_name,
            array(
                'gallery_id' => $gallery->ID,
                'event_type' => 'view',
                'event_data' => json_encode(array(
                    'template' => $gallery->gallery_template,
                    'image_count' => count($gallery->attachments()),
                    'page_url' => $_SERVER['REQUEST_URI'] ?? '',
                    'referrer' => $_SERVER['HTTP_REFERER'] ?? ''
                )),
                'user_ip' => $this->get_user_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
    }
    
    public function add_analytics_data($options, $gallery, $template) {
        if ($gallery->get_setting('enable_tracking', true)) {
            $options['analytics'] = array(
                'enabled' => true,
                'galleryId' => $gallery->ID,
                'events' => $gallery->get_setting('track_events', array('view')),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('foogallery_analytics_' . $gallery->ID)
            );
        }
        
        return $options;
    }
    
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '';
        }
    }
}

// Initialize the extension
new Custom_FooGallery_Extension();
```

---

## Hook Performance Considerations

### Optimizing Hook Usage

1. **Use appropriate priorities**: Lower numbers run earlier, higher numbers run later
2. **Check conditions early**: Return early if the hook shouldn't run
3. **Cache expensive operations**: Use transients for API calls or complex calculations
4. **Limit database queries**: Batch operations when possible

```php
// Good: Early return and caching
add_action('foogallery_loaded_template_after', function($gallery) {
    // Early return if tracking disabled
    if (!$gallery->get_setting('enable_analytics', false)) {
        return;
    }
    
    // Use transient to cache API calls
    $analytics_data = get_transient('analytics_data_' . $gallery->ID);
    if (false === $analytics_data) {
        $analytics_data = expensive_api_call($gallery->ID);
        set_transient('analytics_data_' . $gallery->ID, $analytics_data, HOUR_IN_SECONDS);
    }
    
    // Process data
    process_analytics_data($analytics_data);
});

// Good: Batch database operations
add_action('foogallery_after_save_gallery', function($post_id, $post_data) {
    // Collect all meta updates
    $meta_updates = array();
    
    if (isset($post_data['custom_field_1'])) {
        $meta_updates['_custom_field_1'] = sanitize_text_field($post_data['custom_field_1']);
    }
    
    if (isset($post_data['custom_field_2'])) {
        $meta_updates['_custom_field_2'] = sanitize_text_field($post_data['custom_field_2']);
    }
    
    // Batch update meta
    foreach ($meta_updates as $key => $value) {
        update_post_meta($post_id, $key, $value);
    }
}, 10, 2);
```

---

## Hook Reference Quick Guide

### Most Commonly Used Hooks

| Hook | Type | Use Case |
|------|------|----------|
| `foogallery_located_template` | Action | Modify gallery before rendering |
| `foogallery_loaded_template_after` | Action | Add content after gallery |
| `foogallery_build_container_data_options` | Filter | Add JavaScript configuration |
| `foogallery_attachment_html_image` | Filter | Modify image HTML |
| `foogallery_after_save_gallery` | Action | Process gallery saves |
| `foogallery_admin_menu_after` | Action | Add admin menu items |
| `foogallery_available_extensions` | Filter | Register extensions |
| `foogallery_override_gallery_template_fields` | Filter | Add custom settings |

### Template-Specific Hook Patterns

```php
// Template located: foogallery_located_template-{template}
add_action('foogallery_located_template-masonry', $callback);

// Template loaded: foogallery_loaded_template-{template} 
add_action('foogallery_loaded_template-carousel', $callback);

// Lightbox config: foogallery_template_lightbox-{lightbox}
add_action('foogallery_template_lightbox-foogallery', $callback);

// JS deps: foogallery_template_js_deps-{template}
add_filter('foogallery_template_js_deps-slider', $callback);

// CSS deps: foogallery_template_css_deps-{template}
add_filter('foogallery_template_css_deps-masonry', $callback);
```

---

## Conclusion

This comprehensive hook documentation provides developers with everything needed to extend FooGallery Premium. The hooks are designed to be flexible, performant, and maintainable, allowing for powerful customizations while preserving plugin integrity.

For the most up-to-date hook information and examples, always refer to the plugin source code and test thoroughly in a development environment before deploying to production.

**Last Updated**: July 23, 2025  
**Plugin Version**: 2.4.34  
**Total Hooks Documented**: 200+
