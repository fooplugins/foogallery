# TEST-templates-rendering

## Scope
Template selection, rendering, and output escaping.

## Required Tests
- Rendering a gallery with each core template returns non-empty HTML.
- Template settings are applied (e.g., thumbnail size and captions).
- Output is escaped for captions and titles (no raw HTML injection).

## Test Types
- `WP_UnitTestCase`
- Group: `render`
