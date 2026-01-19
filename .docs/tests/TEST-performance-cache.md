# TEST-performance-cache

## Scope
Caching and thumbnail generation behavior.

## Required Tests
- HTML cache is created when enabled and invalidated on gallery update.
- Cache clear actions remove stored cache entries.
- Thumbnail generation fallback uses full-size when thumbs fail.

## Test Types
- `WP_UnitTestCase`
- Group: `performance`
