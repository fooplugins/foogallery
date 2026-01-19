# TEST-security-multisite

## Scope
Cross-site access protections in multisite installs.

## Required Tests
- AJAX endpoints deny access to galleries/attachments from another blog.
- REST `/foogallery/v1/galleries` returns only current blog galleries.
- Gallery metadata endpoints reject cross-site IDs even with valid nonce.

## Test Types
- `WP_UnitTestCase` + `WP_Ajax_UnitTestCase`
- Group: `multisite`
