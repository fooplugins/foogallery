# FooGallery Template Selection Enhancement - Product Requirements Document

## Overview
This document outlines the requirements for enhancing the gallery template selection experience in FooGallery. The current template selection uses a simple dropdown, which doesn't give users a good preview of what each template looks like before selecting it.

## Problem Statement
Users find it difficult to choose the right gallery template because:
1. The current dropdown selector provides no visual preview of templates
2. Users must apply a template and refresh the page to see how it looks
3. There's no way to quickly compare different templates
4. Template descriptions are minimal and not easily discoverable

## Goals
1. Provide visual previews of gallery templates before selection
2. Enable one-click template switching with live previews
3. Improve template organization for easier discovery
4. Offer better template information and descriptions
5. Maintain backward compatibility with existing templates

## Non-Goals
1. Changing the underlying template system architecture
2. Modifying how templates are registered or defined
3. Adding new template types (outside of extending current system)

## Success Metrics
- Increase in template switch rate by 30%
- Reduction in support tickets related to template selection by 25%
- Positive feedback on template selection experience in user surveys

## User Stories

### As a content editor
I want to visually preview gallery templates before selecting one so that I can make an informed decision.

### As a site builder
I want to quickly compare different gallery templates so that I can choose the best one for my content.

### As a designer
I want clear information about each template so that I can recommend the most appropriate one to my clients.

## Technical Requirements

### Current Implementation Details
The current template selector works by:
1. Rendering a hidden `select` element with all available templates
2. Moving this selector to the metabox header
3. Showing/hiding template settings based on selection
4. Using a preview system (already exists) for templates that support it

### New Feature Requirements

#### 1. Visual Template Grid
- Replace the simple dropdown with a grid of template cards
- Each card should display:
  - Template name
  - Preview image or illustration
  - Short description
  - Template category/label (e.g., "PRO", "Free", "Bundled")
  - Preview button
  
#### 2. Template Preview Modal
- When a user clicks "Preview" on a template card:
  - Show a modal with a live preview of the gallery using that template
  - Include template settings in the preview if applicable
  - Allow one-click application of the template
  
#### 3. Enhanced Template Data
Templates should include extended metadata:
```php
$template = array(
    'slug'        => 'masonry',
    'name'        => __('Masonry', 'foogallery'),
    'preview_support' => true,
    // NEW FIELDS FOR ENHANCED SELECTION
    'preview_image' => 'path/to/preview.jpg',  // Visual preview
    'description' => 'Balanced grid with dynamic item sizing',  // Extended description
    'category'    => 'popular',  // For better organization
    'featured'    => true,  // Highlight recommended templates
    'pro'         => true,  // Indicates if it's a PRO template
    'tags'        => array('grid', 'responsive', 'modern')  // For filtering
);
```

### Implementation Plan

#### Phase 1: Template Data Enhancement
1. Extend the template registration system to accept new metadata fields
2. Add backward compatibility for existing templates
3. Create default preview images for core templates
4. Add filtering capabilities by category/tags

#### Phase 2: Visual Template Selector UI
1. Create new template grid component
2. Implement card-based layout with visual previews
3. Add search and filtering capabilities
4. Implement responsive design for all screen sizes

#### Phase 3: Preview Modal System
1. Create modal component for template previews
2. Integrate with existing gallery preview system
3. Add one-click template application feature
4. Implement loading states and error handling

#### Phase 4: Integration with Existing System
1. Ensure backward compatibility with current template selector
2. Integrate with existing settings/metabox system
3. Add proper hooks and filters for extensions

### Technical Specifications

#### Template Registration Extension
```php
// Extend existing filter to accept new fields
$gallery_templates = apply_filters('foogallery_gallery_templates', array());

// New template structure with extended fields
$template = array(
    'slug' => 'masonry',
    'name' => 'Masonry',
    'preview_support' => true,
    'preview_image' => plugins_url('preview.jpg', __FILE__),
    'description' => 'Balanced grid layout with dynamic sizing',
    'category' => 'grid',
    'featured' => true,
    'pro' => false,
    'tags' => array('responsive', 'modern', 'popular'),
    'fields' => array(/* existing fields */)
);
```

#### JavaScript Components
```javascript
// Template Grid Component
class TemplateGrid {
    constructor(templates, container) {
        this.templates = templates;
        this.container = container;
        this.render();
    }
    
    render() {
        // Render template cards grid
    }
    
    filterByCategory(category) {
        // Filter templates by category
    }
}

// Template Preview Modal
class TemplatePreviewModal {
    constructor(template) {
        this.template = template;
        this.show();
    }
    
    show() {
        // Show modal with template preview
    }
    
    applyTemplate() {
        // Apply template to gallery
    }
}
```

## Backward Compatibility
All enhancements must maintain full backward compatibility with:
1. Existing template registration system
2. Current template selector dropdown
3. All existing gallery templates
4. Extension templates

## Testing Requirements

### Functional Testing
1. Verify all existing templates still work with new selector
2. Confirm template preview system works correctly
3. Ensure one-click template application functions properly
4. Test responsive behavior on different screen sizes

### Compatibility Testing
1. Test with all core gallery templates
2. Test with PRO templates (if available)
3. Test with third-party extension templates
4. Verify backward compatibility with dropdown selector

## Rollout Plan

### Phase 1: Development and Internal Testing
- Implement template data enhancement
- Create visual selector components
- Internal testing and quality assurance

### Phase 2: Beta Testing
- Release to select group of beta testers
- Gather feedback on usability and functionality
- Make necessary adjustments based on feedback

### Phase 3: General Release
- Full release to all users
- Update documentation
- Announce feature in release notes

## Future Enhancements
1. AI-powered template recommendations based on content
2. Template usage analytics to suggest popular templates
3. User ratings and reviews for templates
4. Template import/export functionality
