# TEST-rest-api

## Scope
REST API endpoints for Gutenberg and integrations.

## Required Tests
- `/foogallery/v1/galleries` returns only galleries user can read.
- Non-readers receive 403.
- Multisite: response is limited to current blog only.

## Test Types
- `WP_UnitTestCase`
- Group: `rest`
