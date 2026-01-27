# Appearance E2E Tests

End-to-end tests for FooGallery Pro Appearance settings feature, including Instagram Filters, Loaded Effects, Borders, Shadows, and Themes.

## Test Files

| File | Tests | Description |
|------|-------|-------------|
| `instagram-filters.spec.ts` | 18 | Instagram filter effects (12 filters + none) |
| `loaded-effects.spec.ts` | 14 | Image loading animations (10 effects + none) |
| `borders-shadows.spec.ts` | 24 | Border sizes, rounded corners, drop/inner shadows |
| `themes.spec.ts` | 9 | Gallery theme options (light, dark, custom) |

**Total: 65 tests**

## Running Appearance Tests

```bash
# Navigate to e2e directory
cd e2e

# Run all appearance tests
npm run test:appearance

# Run appearance tests in headed mode (see browser)
npm run test:appearance:headed

# Run specific test file
npx playwright test --config=tests/playwright.config.ts tests/specs/pro-features/appearance/instagram-filters.spec.ts
```

## Test Coverage

### Instagram Filters (`instagram-filters.spec.ts`)
- Admin tab navigation
- All 12 filter options visibility
- Default filter is none
- Individual filter application:
  - 1977, Amaro, Brannan, Clarendon
  - Earlybird, Lo-Fi, PopRocket, Reyes
  - Toaster, Walden, X-Pro 2, Xtreme
- Filter removal verification
- Cross-template compatibility (justified, masonry)

### Loaded Effects (`loaded-effects.spec.ts`)
- All 10 effect options visibility
- Default effect is fade-in
- Individual effect application:
  - Fade In, Slide Up, Slide Down
  - Slide Left, Slide Right, Scale Up
  - Swing Down, Drop, Fly, Flip
- Effect removal verification
- Cross-template compatibility

### Borders & Shadows (`borders-shadows.spec.ts`)
- **Border Size**: thin, medium, thick, none
- **Rounded Corners**: small, medium, large, full (circle), none
- **Drop Shadow**: outline, small, medium, large, none
- **Inner Shadow**: outline, small, medium, large, none
- Combined settings application

### Themes (`themes.spec.ts`)
- All theme options visibility
- Default theme is light
- Light, dark, and custom theme application
- Theme combined with other settings
- Cross-template compatibility

## Frontend Verification

Each test verifies that the appropriate CSS class is applied to the `.foogallery` container:

| Setting | CSS Class Pattern |
|---------|-------------------|
| Instagram Filter | `fg-filter-{name}` |
| Loaded Effect | `fg-loaded-{name}` |
| Border Size | `fg-border-{size}` |
| Rounded Corners | `fg-round-{size}` |
| Drop Shadow | `fg-shadow-{size}` |
| Inner Shadow | `fg-shadow-inset-{size}` |
| Theme | `fg-light`, `fg-dark`, `fg-custom` |

## Related Files

- `tests/helpers/appearance-test-helper.ts` - Helper functions and constants
- `tests/specs/pro-features/hover-effects/` - Related hover effect tests

## Troubleshooting

### Tests failing to find Appearance tab
The Appearance tab is typically Tab 3 in the gallery settings. Verify the selector matches your FooGallery version.

### Filter class not applied
1. Ensure the gallery is published before checking frontend
2. Wait for page load after navigation
3. Check that the template supports the filter feature
