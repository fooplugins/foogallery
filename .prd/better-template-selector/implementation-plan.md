# FooGallery Template Selection Enhancement - Implementation Plan

## Overview
This document outlines the step-by-step implementation plan for enhancing the gallery template selection experience in FooGallery.

## Phase 1: Preparation and Setup (1-2 days)

### 1.1 Asset Directory Structure
First, create the necessary directory structure for preview images:

```
foogallery/
├── assets/
│   ├── previews/
│   │   ├── masonry.jpg
│   │   ├── slider.jpg
│   │   ├── justified.jpg
│   │   ├── ...
│   │   └── default.jpg
│   └── ...
├── includes/
│   └── admin/
│       └── class-gallery-template-selector.php
├── js/
│   └── admin-template-selector.js
├── css/
│   └── admin-template-selector.css
└── ...
```

### 1.2 Preview Image Creation
Create or source preview images for all existing templates:
- Masonry template preview
- Slider template preview
- Justified template preview
- Simple Portfolio preview
- All other core templates
- Default placeholder image

### 1.3 Development Environment Setup
- Ensure development environment is ready
- Create feature branch: `feature/template-selection-enhancement`
- Set up local testing environment

## Phase 2: Template Data Extension (2-3 days)

### 2.1 Extend Template Registration System
Modify the template registration to accept new metadata fields in `includes/functions.php`:

```php
/**
 * Extend gallery template data with preview information
 */
function foogallery_extend_template_data($templates) {
    foreach ($templates as &$template) {
        // Add preview image path if not set
        if (!isset($template['preview_image'])) {
            $template_slug = $template['slug'];
            $preview_path = FOOGALLERY_URL . 'assets/previews/' . $template_slug . '.jpg';
            
            // Check if specific preview exists, otherwise use default
            if (file_exists(FOOGALLERY_PATH . 'assets/previews/' . $template_slug . '.jpg')) {
                $template['preview_image'] = $preview_path;
            } else {
                $template['preview_image'] = FOOGALLERY_URL . 'assets/previews/default.jpg';
            }
        }
        
        // Add default description if not set
        if (!isset($template['description'])) {
            $template['description'] = __('No description available', 'foogallery');
        }
        
        // Add default category
        if (!isset($template['category'])) {
            $template['category'] = 'general';
        }
        
        // Add default featured status
        if (!isset($template['featured'])) {
            $template['featured'] = false;
        }
        
        // Add default pro status
        if (!isset($template['pro'])) {
            $template['pro'] = false;
        }
        
        // Add default tags
        if (!isset($template['tags'])) {
            $template['tags'] = array();
        }
    }
    return $templates;
}
add_filter('foogallery_gallery_templates', 'foogallery_extend_template_data');
```

### 2.2 Create Template Selector Class
Create `includes/admin/class-gallery-template-selector.php`:

```php
<?php
/**
 * Class FooGallery_Admin_Template_Selector
 * Handles the visual template selector UI
 */
class FooGallery_Admin_Template_Selector {
    
    /**
     * Render the visual template selector grid
     * 
     * @param string $current_template The currently selected template
     * @param array $all_templates All available templates
     * @return string HTML output
     */
    public function render_visual_selector($current_template, $all_templates) {
        // Implementation details
    }
    
    /**
     * Render a single template card
     * 
     * @param array $template Template data
     * @param bool $is_current Whether this is the currently selected template
     * @return string HTML output
     */
    private function render_template_card($template, $is_current) {
        // Implementation details
    }
}
```

## Phase 3: Visual Selector UI Implementation (3-4 days)

### 3.1 CSS Styling
Create `css/admin-template-selector.css` with responsive grid layout:

```css
.foogallery-template-selector-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

/* Additional CSS classes for cards, previews, etc. */
```

### 3.2 JavaScript Controller
Create `js/admin-template-selector.js`:

```javascript
(function($, window, document) {
    'use strict';
    
    window.FooGalleryTemplateSelector = {
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Event binding for template selection and preview
        },
        
        selectTemplate: function(templateSlug) {
            // Handle template selection
        },
        
        previewTemplate: function(templateSlug) {
            // Handle template preview
        }
    };
    
    $(document).ready(function() {
        FooGalleryTemplateSelector.init();
    });
    
})(jQuery, window, document);
```

### 3.3 PHP Template Rendering
Implement the render methods in the template selector class.

## Phase 4: Preview Modal System (2-3 days)

### 4.1 AJAX Handler
Add AJAX handler in `includes/admin/class-gallery-metabox-items.php`:

```php
public function ajax_template_preview() {
    // Handle AJAX preview requests
}
```

### 4.2 Preview Generation
Create functions to generate template previews with sample data.

### 4.3 Modal UI
Implement modal popup for previews with apply functionality.

## Phase 5: Integration with Existing System (2 days)

### 5.1 Asset Enqueuing
Add proper enqueuing of CSS/JS files:

```php
function enqueue_template_selector_assets($hook_suffix) {
    // Enqueue CSS and JS for template selector
}
add_action('admin_enqueue_scripts', 'enqueue_template_selector_assets');
```

### 5.2 Backward Compatibility
Ensure existing dropdown still works:

```php
// Feature flag to enable/disable visual selector
$enable_visual_selector = apply_filters('foogallery_enable_visual_template_selector', true);
```

### 5.3 Template Selection Integration
Connect the visual selector with the existing template selection system.

## Phase 6: Testing and Debugging (2-3 days)

### 6.1 Unit Testing
Test each component individually:
- Template data extension
- Visual selector rendering
- Preview generation
- AJAX handling

### 6.2 Integration Testing
Test with all existing templates:
- Core templates
- PRO templates
- Third-party extension templates

### 6.3 Browser Compatibility Testing
Test on various browsers and devices.

## Phase 7: Documentation and Examples (1 day)

### 7.1 Developer Documentation
Create documentation for template authors on how to extend their templates:
- How to add preview images
- How to provide descriptions
- How to mark templates as featured or PRO

### 7.2 User Documentation
Create user-facing documentation on how to use the new selector.

### 7.3 Examples
Create examples for common template extensions.

## Phase 8: Beta Testing and Feedback (1-2 weeks)

### 8.1 Internal Testing
Conduct internal testing with team members.

### 8.2 Beta Release
Release to selected beta testers.

### 8.3 Feedback Collection
Gather feedback and make necessary adjustments.

## Phase 9: Release Preparation (1 week)

### 9.1 Final Testing
Conduct final round of testing with all fixes implemented.

### 9.2 Performance Optimization
Optimize for performance and ensure minimal impact on load times.

### 9.3 Release Notes
Prepare comprehensive release notes.

## Timeline Summary

| Phase | Duration | Description |
|-------|----------|-------------|
| Preparation | 1-2 days | Asset creation, environment setup |
| Template Data | 2-3 days | Extend template registration system |
| Visual Selector UI | 3-4 days | Implement CSS, JS, and PHP components |
| Preview Modal | 2-3 days | Create preview system and modal UI |
| Integration | 2 days | Connect with existing system |
| Testing | 2-3 days | Comprehensive testing |
| Documentation | 1 day | Create documentation and examples |
| Beta Testing | 1-2 weeks | Collect feedback |
| Release Prep | 1 week | Final optimization and preparation |

**Total Estimated Time: 4-5 weeks**

## Risk Mitigation

### 1. Template Compatibility
- Maintain backward compatibility with existing templates
- Provide fallbacks for templates without preview images
- Test with all known template extensions

### 2. Performance Impact
- Optimize image assets for web use
- Implement lazy loading where appropriate
- Cache preview responses when possible

### 3. Browser Compatibility
- Test on all supported browsers
- Ensure responsive design works on all devices
- Provide graceful degradation for older browsers

### 4. User Adoption
- Keep existing dropdown as fallback option
- Provide clear documentation and tutorials
- Gather user feedback during beta testing

## Success Criteria

### 1. Technical Requirements
- [ ] All existing templates work without modification
- [ ] Visual selector works on all supported browsers
- [ ] Preview system generates accurate representations
- [ ] Backward compatibility maintained
- [ ] Performance impact minimal (<5% load time increase)

### 2. User Experience Requirements
- [ ] Template selection process is faster and more intuitive
- [ ] Users can easily preview templates before applying
- [ ] Template information is clear and accessible
- [ ] Visual design is consistent with WordPress admin

### 3. Business Requirements
- [ ] Reduced support tickets related to template selection
- [ ] Increased template adoption rates
- [ ] Positive feedback in user surveys
- [ ] Successful integration with PRO templates
