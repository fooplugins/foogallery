# FooGallery Template Extension Example

This document shows how to extend an existing FooGallery template to take advantage of the new visual template selection features.

## Example: Extending the Masonry Template

Here's how you would modify an existing gallery template (like the Masonry template) to include preview images and enhanced metadata:

```php
<?php
/**
 * Extended Masonry Gallery Template for FooGallery
 */

class Extended_FooGallery_Masonry_Template {
    
    function __construct() {
        add_filter('foogallery_gallery_templates', array($this, 'extend_masonry_template'));
    }
    
    /**
     * Extend the Masonry template with preview features
     */
    function extend_masonry_template($gallery_templates) {
        // Find the Masonry template in the array
        foreach ($gallery_templates as &$template) {
            if ($template['slug'] === 'masonry') {
                // Add preview image
                $template['preview_image'] = plugins_url('assets/previews/masonry-extended.jpg', __FILE__);
                
                // Add detailed description
                $template['description'] = __('The Masonry template creates a balanced grid layout with dynamic item sizing that works great for galleries with images of different aspect ratios.', 'foogallery');
                
                // Add category
                $template['category'] = 'grid';
                
                // Mark as featured template
                $template['featured'] = true;
                
                // Add tags for filtering
                $template['tags'] = array('grid', 'responsive', 'popular', 'masonry');
                
                // If this is a PRO template, mark it as such
                // $template['pro'] = true;
                
                break;
            }
        }
        
        return $gallery_templates;
    }
}

// Initialize the extension
new Extended_FooGallery_Masonry_Template();
```

## Example: Creating a New Template with Preview Features

Here's how to create a completely new template that takes full advantage of the visual selection features:

```php
<?php
/**
 * New Gallery Template with Full Preview Support
 */

class FooGallery_New_Template_Example {
    
    const TEMPLATE_SLUG = 'new-template-example';
    
    function __construct() {
        add_filter('foogallery_gallery_templates', array($this, 'register_new_template'));
    }
    
    /**
     * Register the new template with all preview features
     */
    function register_new_template($gallery_templates) {
        $gallery_templates[] = array(
            // Basic template info (required)
            'slug' => self::TEMPLATE_SLUG,
            'name' => __('New Template Example', 'foogallery'),
            
            // Preview features (newly added)
            'preview_image' => plugins_url('assets/previews/new-template-example.jpg', __FILE__),
            'description' => __('A modern gallery template with innovative layout and interaction features.', 'foogallery'),
            'category' => 'modern',
            'featured' => false,
            'pro' => false, // Set to true for PRO templates
            'tags' => array('modern', 'interactive', 'creative'),
            
            // Existing template features
            'preview_support' => true,
            'common_fields_support' => true,
            'lazyload_support' => true,
            'paging_support' => true,
            'thumbnail_dimensions' => true,
            'fields' => array(
                // Template configuration fields
                array(
                    'id'      => 'thumbnail_size',
                    'title'   => __('Thumbnail Size', 'foogallery'),
                    'desc'    => __('Choose the size of your thumbnails.', 'foogallery'),
                    'type'    => 'thumb_size',
                    'default' => array(
                        'width' => 300,
                        'height' => 200,
                        'crop' => true
                    )
                ),
                // Additional fields...
            )
        );
        
        return $gallery_templates;
    }
}

// Initialize the new template
new FooGallery_New_Template_Example();
```

## Template Registration Best Practices

When extending templates with preview features, follow these best practices:

### 1. Preview Images
- Use high-quality but optimized images (recommended size: 400x300px)
- Maintain consistent styling across all preview images
- Place preview images in the `assets/previews/` directory
- Use the template slug as the filename (e.g., `masonry.jpg`)

### 2. Descriptions
- Keep descriptions concise but informative (1-2 sentences)
- Highlight the unique benefits of the template
- Use proper internationalization functions

### 3. Categories
Use standardized categories:
- `'grid'` - For grid-based layouts
- `'slider'` - For slider/carousel templates
- `'single'` - For single image display templates
- `'viewer'` - For detailed image viewing templates
- `'modern'` - For contemporary designs
- `'classic'` - For traditional layouts

### 4. Tags
Use meaningful tags for better discoverability:
- Functionality tags: `'responsive'`, `'filtering'`, `'paging'`
- Style tags: `'minimal'`, `'colorful'`, `'dark'`, `'light'`
- Content tags: `'portfolio'`, `'photography'`, `'ecommerce'`

## Filter for Customizing Templates

You can also use filters to modify any template's preview features:

```php
/**
 * Modify template preview features using filters
 */
function customize_template_previews($templates) {
    foreach ($templates as &$template) {
        // Add preview support to all templates that don't have it
        if (!isset($template['preview_image'])) {
            $template['preview_image'] = FOOGALLERY_URL . 'assets/previews/default.jpg';
        }
        
        // Add common tags based on template features
        if (!isset($template['tags'])) {
            $template['tags'] = array();
        }
        
        // Add tags based on existing features
        if (isset($template['paging_support']) && $template['paging_support']) {
            $template['tags'][] = 'paging';
        }
        
        if (isset($template['lazyload_support']) && $template['lazyload_support']) {
            $template['tags'][] = 'performance';
        }
    }
    
    return $templates;
}
add_filter('foogallery_gallery_templates', 'customize_template_previews');
```

## For Theme Developers

If you're creating a theme that includes custom gallery templates, you can integrate with the preview system:

```php
/**
 * Register theme-specific gallery template with preview features
 */
function mytheme_register_gallery_template() {
    $templates = apply_filters('foogallery_gallery_templates', array());
    
    $templates[] = array(
        'slug' => 'mytheme-custom-gallery',
        'name' => __('MyTheme Custom Gallery', 'mytheme'),
        'preview_image' => get_stylesheet_directory_uri() . '/images/foogallery-preview.jpg',
        'description' => __('A custom gallery template designed specifically for MyTheme.', 'mytheme'),
        'category' => 'theme',
        'featured' => true,
        'tags' => array('theme', 'custom', 'branded'),
        'fields' => array(
            // Template fields...
        )
    );
    
    return $templates;
}
add_filter('foogallery_gallery_templates', 'mytheme_register_gallery_template');
```

This approach allows theme developers to create visually rich template selection experiences that integrate seamlessly with FooGallery's enhanced template selector.
