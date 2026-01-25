# Album E2E Tests

End-to-end tests for FooGallery Pro Album feature.

## Test Files

| File | Tests | Description |
|------|-------|-------------|
| `album-create.spec.ts` | 9 | Album creation and gallery assignment |
| `album-default-settings.spec.ts` | 10 | Default album template settings configuration |
| `album-stack-settings.spec.ts` | 6 | Stack album template settings configuration |
| `album-frontend-default.spec.ts` | 10 | Default album frontend display and behavior |
| `album-frontend-stack.spec.ts` | 6 | Stack album frontend display and behavior |
| `album-navigation.spec.ts` | 5 | Album navigation and gallery transitions |

**Total: 46 tests**

## Running Album Tests

```bash
# Navigate to e2e directory
cd e2e

# Run all album tests
npm run test:album

# Run album tests in headed mode (see browser)
npm run test:album:headed

# Run specific test file
npx playwright test --config=tests/playwright.config.ts tests/specs/albums/album-create.spec.ts
```

## Test Coverage

### Album Creation (`album-create.spec.ts`)
- Create new album
- Add galleries to album
- Remove galleries from album
- Album title configuration
- Album description configuration
- Gallery ordering in album
- Album publishing workflow
- Album shortcode generation
- Album page creation

### Default Template Settings (`album-default-settings.spec.ts`)
- Template selection
- Gallery per row configuration
- Thumbnail size settings
- Hover effects
- Border and shadow options
- Caption display settings
- Alignment options
- Spacing configuration
- Link behavior
- Loading animation

### Stack Template Settings (`album-stack-settings.spec.ts`)
- Stack template selection
- Stack effect configuration
- Stack depth settings
- Gallery thumbnail overlay
- Stack hover behavior
- Stack animation options

### Default Frontend Display (`album-frontend-default.spec.ts`)
- Album renders correctly
- Gallery thumbnails display
- Gallery count display
- Click to open gallery
- Hover effects apply
- Responsive layout
- Caption display
- Gallery ordering
- Empty gallery handling
- Loading states

### Stack Frontend Display (`album-frontend-stack.spec.ts`)
- Stack layout renders
- Stack effect displays
- Gallery thumbnails stack
- Click interaction
- Hover effects
- Stack depth visual

### Album Navigation (`album-navigation.spec.ts`)
- Navigate from album to gallery
- Back navigation to album
- Browser history support
- Deep linking to gallery
- URL parameter handling

## Related Files

- `tests/helpers/album-test-helper.ts` - Helper functions for album tests
- `tests/fixtures/` - Test fixtures and data
