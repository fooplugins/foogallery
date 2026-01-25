# Captions E2E Tests

End-to-end tests for FooGallery Pro Captions feature.

## Test Files

| File | Tests | Description |
|------|-------|-------------|
| `captions-settings.spec.ts` | 13 | Caption settings configuration in gallery admin |
| `captions-frontend.spec.ts` | 12 | Caption display on gallery frontend |
| `captions-length.spec.ts` | 8 | Caption length and truncation settings |
| `captions-custom.spec.ts` | 10 | Custom caption text and formatting |
| `captions-lightbox-override.spec.ts` | 10 | Lightbox caption override settings |
| `captions-lightbox-display.spec.ts` | 8 | Caption display in lightbox |
| `captions-lightbox-settings.spec.ts` | 12 | Lightbox-specific caption configuration |

**Total: 73 tests**

## Running Caption Tests

```bash
# Navigate to e2e directory
cd e2e

# Run all caption tests
npm run test:captions

# Run caption tests in headed mode (see browser)
npm run test:captions:headed

# Run specific test file
npx playwright test --config=tests/playwright.config.ts tests/specs/pro-features/captions/captions-settings.spec.ts
```

## Test Coverage

### Caption Settings (`captions-settings.spec.ts`)
- Caption source selection (title, caption, alt, description)
- Caption position options (below, above, overlay)
- Caption alignment settings
- Caption visibility toggle
- Caption animation effects
- Caption background opacity
- Caption font size
- Caption text color
- Caption padding
- Caption hover behavior
- Caption display mode
- Caption HTML support
- Caption template variables

### Frontend Display (`captions-frontend.spec.ts`)
- Caption renders on thumbnail
- Caption position applies correctly
- Caption text matches source
- Caption hover effects work
- Caption overlay positioning
- Caption responsive behavior
- Caption with long text
- Caption with special characters
- Caption with HTML entities
- Multiple captions in gallery
- Caption fallback when empty
- Caption styling inheritance

### Caption Length (`captions-length.spec.ts`)
- Maximum length setting
- Truncation with ellipsis
- Word boundary truncation
- No truncation when disabled
- Length applies to title
- Length applies to description
- Custom truncation indicator
- Length preserves HTML

### Custom Captions (`captions-custom.spec.ts`)
- Custom caption per image
- Caption override title
- Caption override description
- Custom caption HTML
- Custom caption template
- Empty custom caption
- Reset to default caption
- Custom caption in modal
- Custom caption persistence
- Custom caption on frontend

### Lightbox Override (`captions-lightbox-override.spec.ts`)
- Override caption source for lightbox
- Different caption in lightbox vs thumbnail
- Lightbox-specific caption template
- Override caption position
- Override caption visibility
- Override applies to all images
- Per-image lightbox override
- Override with custom text
- Override reset behavior
- Override inheritance

### Lightbox Display (`captions-lightbox-display.spec.ts`)
- Caption shows in lightbox
- Caption position in lightbox
- Caption animation in lightbox
- Caption updates on navigation
- Caption with long text in lightbox
- Caption hide/show toggle
- Caption overlay in lightbox
- Caption styling in lightbox

### Lightbox Settings (`captions-lightbox-settings.spec.ts`)
- Lightbox caption enabled
- Lightbox caption disabled
- Caption auto-hide timer
- Caption show on hover
- Caption always visible
- Caption background settings
- Caption text formatting
- Caption position options
- Caption animation options
- Caption max width
- Caption padding in lightbox
- Caption z-index handling

## Related Files

- `tests/helpers/captions-test-helper.ts` - Helper functions for caption tests
- `tests/fixtures/` - Test fixtures and data
