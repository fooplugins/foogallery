# Pagination E2E Tests

End-to-end tests for FooGallery Pro Pagination feature.

## Test Files

| File | Tests | Description |
|------|-------|-------------|
| `pagination-numbered.spec.ts` | 16 | Numbered pagination (page numbers) |
| `pagination-infinite.spec.ts` | 9 | Infinite scroll pagination |
| `pagination-load-more.spec.ts` | 8 | Load More button pagination |

**Total: 33 tests**

## Running Pagination Tests

```bash
# Navigate to e2e directory
cd e2e

# Run all pagination tests
npm run test:pagination

# Run pagination tests in headed mode (see browser)
npm run test:pagination:headed

# Run specific test file
npx playwright test --config=tests/playwright.config.ts tests/specs/pro-features/pagination/pagination-numbered.spec.ts
```

## Test Coverage

### Numbered Pagination (`pagination-numbered.spec.ts`)
- Pagination controls display
- Page numbers render correctly
- Navigate to next page
- Navigate to previous page
- Navigate to specific page number
- First page button
- Last page button
- Current page highlight
- Page count display
- Items per page setting
- Page changes URL parameter
- Pagination position (top/bottom/both)
- Pagination with filtering
- Pagination state persistence
- Empty page handling
- Single page hides pagination

### Infinite Scroll (`pagination-infinite.spec.ts`)
- Infinite scroll enabled
- Items load on scroll
- Loading indicator display
- All items eventually load
- Scroll position maintained
- Works with filtering
- Works with different layouts
- Loading threshold configuration
- Infinite scroll disabled state

### Load More Button (`pagination-load-more.spec.ts`)
- Load More button displays
- Click loads more items
- Button text customization
- Loading state indicator
- Button hides when all loaded
- Items per load configuration
- Works with filtering
- Load More button position

## Related Files

- `tests/helpers/pagination-test-helper.ts` - Helper functions for pagination tests
- `tests/fixtures/` - Test fixtures and data
