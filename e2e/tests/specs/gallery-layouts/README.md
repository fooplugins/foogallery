# Gallery Layouts E2E Tests

End-to-end tests for FooGallery gallery layout templates.

## Test Files

| File | Tests | Description |
|------|-------|-------------|
| `gallery-justified.spec.ts` | 1 | Justified gallery layout |
| `gallery-masonry.spec.ts` | 1 | Masonry gallery layout |
| `gallery-carousel.spec.ts` | 1 | Carousel gallery layout |
| `gallery-grid-pro.spec.ts` | 1 | Grid Pro gallery layout |
| `gallery-image-viewer.spec.ts` | 1 | Image Viewer gallery layout |
| `gallery-polaroid-pro.spec.ts` | 1 | Polaroid Pro gallery layout |
| `gallery-portfolio.spec.ts` | 1 | Portfolio gallery layout |
| `gallery-product.spec.ts` | 1 | Product gallery layout |
| `gallery-single-thumbnail.spec.ts` | 1 | Single Thumbnail gallery layout |
| `gallery-slider-pro.spec.ts` | 1 | Slider Pro gallery layout |
| `gallery-spotlight-pro.spec.ts` | 1 | Spotlight Pro gallery layout |

**Total: 11 tests**

## Running Gallery Layout Tests

```bash
# Navigate to e2e directory
cd e2e

# Run all gallery layout tests
npx playwright test --config=tests/playwright.config.ts tests/specs/gallery-layouts/

# Run gallery layout tests in headed mode (see browser)
CLEANUP_AFTER_TESTS=false npx playwright test --config=tests/playwright.config.ts --headed tests/specs/gallery-layouts/

# Run specific layout test
npx playwright test --config=tests/playwright.config.ts tests/specs/gallery-layouts/gallery-masonry.spec.ts
```

## Test Coverage

### Justified Layout (`gallery-justified.spec.ts`)
- Gallery renders with justified layout
- Images fill container width
- Row heights are consistent
- Responsive behavior

### Masonry Layout (`gallery-masonry.spec.ts`)
- Gallery renders with masonry layout
- Variable height items
- Column-based arrangement
- Responsive columns

### Carousel Layout (`gallery-carousel.spec.ts`)
- Gallery renders as carousel
- Navigation arrows work
- Slide transitions
- Autoplay functionality

### Grid Pro Layout (`gallery-grid-pro.spec.ts`)
- Gallery renders in grid
- Configurable columns
- Equal sizing
- Hover effects

### Image Viewer Layout (`gallery-image-viewer.spec.ts`)
- Large image display
- Thumbnail strip
- Navigation between images
- Zoom functionality

### Polaroid Pro Layout (`gallery-polaroid-pro.spec.ts`)
- Polaroid frame effect
- Rotation effects
- Stacking behavior
- Caption positioning

### Portfolio Layout (`gallery-portfolio.spec.ts`)
- Portfolio grid display
- Category filtering
- Project descriptions
- Lightbox integration

### Product Layout (`gallery-product.spec.ts`)
- Product image display
- Thumbnail gallery
- Zoom on hover
- Product details

### Single Thumbnail Layout (`gallery-single-thumbnail.spec.ts`)
- Single image display
- Click to open gallery
- Lightbox navigation
- Image count indicator

### Slider Pro Layout (`gallery-slider-pro.spec.ts`)
- Full-width slider
- Slide transitions
- Navigation controls
- Progress indicators

### Spotlight Pro Layout (`gallery-spotlight-pro.spec.ts`)
- Featured image spotlight
- Thumbnail navigation
- Transition effects
- Caption display

## Related Files

- `tests/helpers/gallery-layout-helper.ts` - Helper functions for layout tests
- `tests/fixtures/` - Test fixtures and data
