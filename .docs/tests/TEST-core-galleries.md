# TEST-core-galleries

## Scope
Core gallery creation, editing, rendering, and metadata behavior.

## Required Tests
- Gallery creation persists post type `foogallery` with default settings.
- Gallery update saves settings and attachments count correctly.
- Gallery delete removes associated metadata (cache, custom fields).
- Featured thumbnail selection returns correct attachment for gallery.
- Shortcode rendering returns gallery HTML for published galleries.
- Private/password galleries are not rendered for unauthenticated users.

## Test Types
- `WP_UnitTestCase`
- Group: `core`
