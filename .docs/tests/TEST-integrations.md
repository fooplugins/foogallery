# TEST-integrations

## Scope
Third-party compatibility layers and optional extensions.

## Required Tests
- Elementor refresh galleries AJAX requires `edit_posts`.
- WooCommerce product variations AJAX returns data only for valid nonce and published products.
- Freemius hooks do not error when plugin is loaded.

## Test Types
- `WP_Ajax_UnitTestCase` + `WP_UnitTestCase`
- Group: `integration`
