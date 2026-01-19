# TEST-admin-ajax

## Scope
Authenticated admin-ajax endpoints and permission checks.

## Required Tests
- `foogallery_tinymce_load_info` requires nonce + `edit_post`.
- Attachment modal endpoints enforce `edit_post`/taxonomy caps.
- Gallery preview endpoint enforces `edit_post`.
- Datasource modal endpoint enforces `edit_post`.
- Settings endpoints require `manage_options`.
- Cache clear endpoints require `edit_post`.
- Override thumbnail removal requires `edit_post`.

## Test Types
- `WP_Ajax_UnitTestCase`
- Group: `ajax`
