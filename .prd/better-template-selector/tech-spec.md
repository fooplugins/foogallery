# FooGallery Template Selection Enhancement - Technical Specification

## Overview
This document provides technical specifications for implementing the enhanced template selection feature for FooGallery.

## Current Architecture

### Template Registration
Templates are registered using the `foogallery_gallery_templates` filter:

```php
add_filter('foogallery_gallery_templates', array($this, 'add_template'));

function add_template($gallery_templates) {
    $gallery_templates[] = array(
        'slug' => 'masonry',
        'name' => __('Masonry', 'foogallery'),
        'preview_support' => true,
        'fields' => array(/* field definitions */)
    );
    return $gallery_templates;
}
```

### Template Selector
Located in `includes/admin/class-gallery-metabox-settings-helper.php`:
- Renders as a hidden select element
- Moved to metabox header via JavaScript
- Controls visibility of template settings

### Preview System
Located in `includes/admin/class-gallery-metabox-items.php`:
- Uses AJAX to generate live previews
- Only works for templates with `preview_support` set to true

## Proposed Enhancements

### 1. Extended Template Data Structure

```php
$template = array(
    'slug'              => 'masonry',
    'name'              => __('Masonry', 'foogallery'),
    'preview_support'   => true,
    // NEW EXTENDED FIELDS
    'preview_image'     => plugins_url('assets/previews/masonry.jpg', FOOGALLERY_FILE),
    'description'       => __('Balanced grid layout with dynamic sizing', 'foogallery'),
    'category'          => 'grid',  // 'grid', 'slider', 'single', 'viewer', etc.
    'featured'          => true,
    'pro'               => false,
    'tags'              => array('responsive', 'modern', 'popular'),
    'fields'            => array(/* existing fields */)
);
```

### 2. Template Data Enhancement Hook

Add a new filter to extend template data:

```php
// In functions.php or template registration
function extend_template_data($templates) {
    foreach ($templates as &$template) {
        // Add default preview image if none exists
        if (!isset($template['preview_image'])) {
            $template['preview_image'] = FOOGALLERY_URL . 'assets/previews/default.jpg';
        }
        
        // Add default description if none exists
        if (!isset($template['description'])) {
            $template['description'] = __('No description available', 'foogallery');
        }
        
        // Set defaults for new fields
        if (!isset($template['category'])) {
            $template['category'] = 'general';
        }
        
        if (!isset($template['featured'])) {
            $template['featured'] = false;
        }
        
        if (!isset($template['pro'])) {
            $template['pro'] = false;
        }
        
        if (!isset($template['tags'])) {
            $template['tags'] = array();
        }
    }
    return $templates;
}

add_filter('foogallery_gallery_templates', 'extend_template_data');
```

### 3. Visual Template Selector Component

#### PHP Component
```php
// includes/admin/class-gallery-template-selector.php
class FooGallery_Admin_Template_Selector {
    
    public function render_visual_selector($current_template, $all_templates) {
        $output = '<div class="foogallery-template-selector-grid">';
        
        foreach ($all_templates as $template) {
            $is_current = ($template['slug'] === $current_template);
            $css_classes = 'foogallery-template-card';
            if ($is_current) {
                $css_classes .= ' foogallery-template-card-current';
            }
            if ($template['featured']) {
                $css_classes .= ' foogallery-template-card-featured';
            }
            if ($template['pro']) {
                $css_classes .= ' foogallery-template-card-pro';
            }
            
            $output .= '<div class="' . esc_attr($css_classes) . '" data-template="' . esc_attr($template['slug']) . '">';
            $output .= '<div class="foogallery-template-preview">';
            $output .= '<img src="' . esc_url($template['preview_image']) . '" alt="' . esc_attr($template['name']) . '">';
            $output .= '</div>';
            $output .= '<div class="foogallery-template-info">';
            $output .= '<h4>' . esc_html($template['name']) . '</h4>';
            $output .= '<p>' . esc_html($template['description']) . '</p>';
            $output .= '<div class="foogallery-template-actions">';
            
            if ($template['preview_support']) {
                $output .= '<button class="button foogallery-template-preview-btn" data-template="' . esc_attr($template['slug']) . '">' . __('Preview', 'foogallery') . '</button>';
            }
            
            $output .= '<button class="button foogallery-template-select-btn" data-template="' . esc_attr($template['slug']) . '">' . ($is_current ? __('Current', 'foogallery') : __('Select', 'foogallery')) . '</button>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        return $output;
    }
}
```

#### JavaScript Controller
```javascript
// js/admin-template-selector.js
(function($, window, document) {
    'use strict';
    
    window.FooGalleryTemplateSelector = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Template selection
            $(document).on('click', '.foogallery-template-select-btn', function(e) {
                e.preventDefault();
                var templateSlug = $(this).data('template');
                FooGalleryTemplateSelector.selectTemplate(templateSlug);
            });
            
            // Template preview
            $(document).on('click', '.foogallery-template-preview-btn', function(e) {
                e.preventDefault();
                var templateSlug = $(this).data('template');
                FooGalleryTemplateSelector.previewTemplate(templateSlug);
            });
        },
        
        selectTemplate: function(templateSlug) {
            // Update the hidden select element
            $('#FooGallerySettings_GalleryTemplate').val(templateSlug).trigger('change');
            
            // Update UI to show selected template
            $('.foogallery-template-card').removeClass('foogallery-template-card-current');
            $('.foogallery-template-card[data-template="' + templateSlug + '"]').addClass('foogallery-template-card-current');
            
            // Update button text
            $('.foogallery-template-select-btn').text('Select');
            $('.foogallery-template-select-btn[data-template="' + templateSlug + '"]').text('Current');
        },
        
        previewTemplate: function(templateSlug) {
            // Show preview modal
            FooGalleryTemplateSelector.showPreviewModal(templateSlug);
        },
        
        showPreviewModal: function(templateSlug) {
            // Create and show modal with template preview
            var modal = $('<div class="foogallery-template-preview-modal"><div class="foogallery-template-preview-content"></div></div>');
            $('body').append(modal);
            
            // Load preview via AJAX
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'foogallery_template_preview',
                    template: templateSlug,
                    nonce: $('#foogallery_preview').val()
                },
                success: function(response) {
                    modal.find('.foogallery-template-preview-content').html(response);
                    modal.show();
                }
            });
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        FooGalleryTemplateSelector.init();
    });
    
})(jQuery, window, document);
```

### 4. Preview Modal AJAX Handler

```php
// In includes/admin/class-gallery-metabox-items.php
class FooGallery_Admin_Gallery_MetaBox_Items {
    
    function __construct() {
        // Existing code...
        add_action('wp_ajax_foogallery_template_preview', array($this, 'ajax_template_preview'));
    }
    
    public function ajax_template_preview() {
        if (!check_ajax_referer('foogallery_preview', 'nonce', false)) {
            wp_die('Invalid nonce');
        }
        
        $template = sanitize_text_field($_POST['template']);
        
        // Check that the template supports previews
        $gallery_template = foogallery_get_gallery_template($template);
        if (!isset($gallery_template['preview_support']) || true !== $gallery_template['preview_support']) {
            wp_die('Preview not supported for this template');
        }
        
        // Generate placeholder preview content
        $this->generate_template_preview($template);
        
        wp_die();
    }
    
    private function generate_template_preview($template) {
        // Create a temporary gallery object for preview
        $gallery = new FooGallery();
        $gallery->gallery_template = $template;
        
        // Use default attachments or generate sample attachments
        $attachments = $this->get_sample_attachments();
        
        // Output a simplified preview of the template
        echo '<div class="foogallery-template-preview-wrapper">';
        echo '<h3>' . esc_html($gallery_template['name']) . ' Preview</h3>';
        
        // Render simplified template preview
        switch ($template) {
            case 'masonry':
                $this->render_masonry_preview($attachments);
                break;
            case 'slider':
                $this->render_slider_preview($attachments);
                break;
            default:
                $this->render_generic_preview($attachments);
        }
        
        echo '<div class="foogallery-template-preview-actions">';
        echo '<button class="button button-primary foogallery-apply-template-btn" data-template="' . esc_attr($template) . '">' . __('Apply Template', 'foogallery') . '</button>';
        echo '<button class="button foogallery-close-preview-btn">' . __('Close', 'foogallery') . '</button>';
        echo '</div>';
        echo '</div>';
    }
    
    private function get_sample_attachments() {
        // Return sample attachments for preview
        return array(
            (object) array('ID' => 1, 'url' => 'https://via.placeholder.com/300x200', 'caption' => 'Sample Image 1'),
            (object) array('ID' => 2, 'url' => 'https://via.placeholder.com/300x300', 'caption' => 'Sample Image 2'),
            (object) array('ID' => 3, 'url' => 'https://via.placeholder.com/300x150', 'caption' => 'Sample Image 3'),
        );
    }
    
    private function render_masonry_preview($attachments) {
        echo '<div class="foogallery-masonry-preview">';
        foreach ($attachments as $attachment) {
            echo '<div class="foogallery-item" style="width: 150px; height: ' . rand(100, 200) . 'px; background: #eee; margin: 5px; display: inline-block;">';
            echo '<div style="padding: 10px; text-align: center;">' . esc_html($attachment->caption) . '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    // Additional preview renderers...
}
```

### 5. CSS Styling

```css
/* CSS for template selector grid */
.foogallery-template-selector-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.foogallery-template-card {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.foogallery-template-card:hover {
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.foogallery-template-card-current {
    border-color: #0073aa;
    box-shadow: 0 0 0 1px #0073aa;
}

.foogallery-template-card-featured::after {
    content: "FEATURED";
    position: absolute;
    top: 10px;
    right: 10px;
    background: #0073aa;
    color: white;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 10px;
}

.foogallery-template-card-pro::after {
    content: "PRO";
    position: absolute;
    top: 10px;
    right: 10px;
    background: #d54e21;
    color: white;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 10px;
}

.foogallery-template-preview img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 3px;
}

.foogallery-template-info h4 {
    margin: 10px 0 5px;
    font-size: 16px;
}

.foogallery-template-info p {
    font-size: 13px;
    color: #666;
    margin: 5px 0;
}

.foogallery-template-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
}

.foogallery-template-preview-btn,
.foogallery-template-select-btn {
    flex: 1;
    text-align: center;
}

/* CSS for preview modal */
.foogallery-template-preview-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.8);
    z-index: 100000;
    display: none;
}

.foogallery-template-preview-content {
    background: #fff;
    width: 80%;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    border-radius: 4px;
    max-height: 80vh;
    overflow-y: auto;
}

.foogallery-template-preview-actions {
    margin-top: 20px;
    text-align: right;
}
```

## Integration Points

### 1. Admin Enqueue
```php
// In class-gallery-metabox-settings.php or similar
function enqueue_template_selector_assets($hook_suffix) {
    if (in_array($hook_suffix, array('post.php', 'post-new.php'))) {
        $screen = get_current_screen();
        if (is_object($screen) && FOOGALLERY_CPT_GALLERY == $screen->post_type) {
            wp_enqueue_script('foogallery-template-selector', FOOGALLERY_URL . 'js/admin-template-selector.js', array('jquery'), FOOGALLERY_VERSION);
            wp_enqueue_style('foogallery-template-selector', FOOGALLERY_URL . 'css/admin-template-selector.css', array(), FOOGALLERY_VERSION);
        }
    }
}
add_action('admin_enqueue_scripts', 'enqueue_template_selector_assets');
```

### 2. Template Selector Rendering
```php
// In class-gallery-metabox-settings-helper.php
class FooGallery_Admin_Gallery_MetaBox_Settings_Helper {
    
    public function render_gallery_settings() {
        // Existing code...
        
        // Add visual template selector
        $selector = new FooGallery_Admin_Template_Selector();
        echo $selector->render_visual_selector($this->current_gallery_template, $this->gallery_templates);
        
        // Keep existing settings rendering...
        foreach ($this->gallery_templates as $template) {
            // Existing rendering code
        }
    }
}
```

## Migration Path

### 1. Backward Compatibility
- Maintain existing dropdown selector for backward compatibility
- Add visual selector alongside existing one
- Ensure all existing templates work without modification
- Provide default preview images for existing templates

### 2. Feature Flags
```php
// Allow disabling the visual selector via filter
if (apply_filters('foogallery_enable_visual_template_selector', true)) {
    // Render visual selector
} else {
    // Render traditional dropdown
}
```

## Error Handling and Edge Cases

### 1. Missing Preview Images
- Provide default placeholder images
- Log warnings for missing preview images
- Allow fallback to simple template names

### 2. AJAX Failures
- Gracefully handle preview loading failures
- Provide error messages in UI
- Fallback to template selection without preview

### 3. Compatibility Issues
- Test with all existing templates
- Ensure PRO templates work correctly
- Verify third-party template compatibility

## Performance Considerations

### 1. Image Optimization
- Use appropriately sized preview images
- Implement lazy loading for preview images
- Compress preview images for faster loading

### 2. AJAX Performance
- Cache preview responses where appropriate
- Minimize data transfer in preview requests
- Implement loading states for better UX

## Testing Plan

### 1. Unit Tests
- Test template data extension functionality
- Verify backward compatibility with existing templates
- Ensure proper error handling

### 2. Integration Tests
- Test with all core gallery templates
- Verify compatibility with PRO templates
- Test third-party extension templates

### 3. User Acceptance Testing
- Verify visual design meets requirements
- Confirm usability improvements
- Validate performance under various conditions

## Deployment Plan

### 1. Version Release
- Include in next minor version release
- Provide clear migration documentation
- Offer support for template authors to update their templates

### 2. Communication
- Announce feature in release notes
- Update documentation with new template registration requirements
- Provide examples for template authors

### 3. Monitoring
- Track template selection metrics
- Monitor for compatibility issues
- Gather user feedback for future improvements
