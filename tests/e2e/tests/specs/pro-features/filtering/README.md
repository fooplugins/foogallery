# Filtering E2E Tests

End-to-end tests for FooGallery Pro Filtering feature.

## Test Files

| File | Tests | Description |
|------|-------|-------------|
| `filtering-simple.spec.ts` | 5 | Basic tag filtering functionality |
| `filtering-modes.spec.ts` | 5 | Selection modes (Single, OR, AND) |
| `filtering-styles.spec.ts` | 11 | Visual styles (Default, Button, Pill, Dropdown) |
| `filtering-position.spec.ts` | 7 | Filter position (Top, Bottom, Both) |
| `filtering-search.spec.ts` | 12 | Search input functionality |

**Total: 40 tests**

## Running Filtering Tests

```bash
# Navigate to e2e directory
cd e2e

# Run all filtering tests
npm run test:filtering

# Run filtering tests in headed mode (see browser)
npm run test:filtering:headed

# Run specific test file
npx playwright test --config=tests/playwright.config.ts tests/specs/pro-features/filtering/filtering-simple.spec.ts
```

## Test Coverage

### Simple Tag Filtering (`filtering-simple.spec.ts`)
- Filter tags display when enabled
- Filter items when clicking a tag
- Show all items when clicking "All"
- Hide "All" option configuration
- Filtering animation when switching tags

### Selection Modes (`filtering-modes.spec.ts`)
- **Single Mode**: Only one filter selection at a time
- **Multiple OR Mode (Union)**: Multiple selections show items matching ANY tag
- Deselect tag when clicked again in OR mode
- **Multiple AND Mode (Intersect)**: Multiple selections show items matching ALL tags
- AND mode shows fewer items as more filters added

### Visual Styles (`filtering-styles.spec.ts`)
- Default style (no special styling)
- Button style with proper CSS class
- Button block style (full width)
- Pill style with proper CSS class
- Pill block style (full width)
- Dropdown style with select element
- Dropdown filtering functionality
- Dropdown block style (full width)
- Button style filters work correctly
- Pill style filters work correctly

### Filter Position (`filtering-position.spec.ts`)
- Top position (filters above gallery)
- Bottom position (filters below gallery)
- Both positions (filters above and below)
- Both filters stay synchronized
- Button style works at bottom position
- Dropdown style works at both positions

### Search Functionality (`filtering-search.spec.ts`)
- Search input displays when enabled
- Search input hidden when disabled
- Filter gallery when typing in search
- Clear search shows all items
- Search works with filter tags together
- Search position: above-center (default)
- Search position: above-right
- Search position: below-center
- Search position: before-merged
- Search position: after-merged
- Search only mode (no filter tags)

## Related Files

- `tests/helpers/filtering-test-helper.ts` - Helper functions for filtering tests
- `tests/fixtures/` - Test fixtures and data
