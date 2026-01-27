# Hover Effects E2E Tests

End-to-end tests for FooGallery Pro Hover Effects feature, including preset effects, normal mode customization, and hover icons.

## Test Files

| File | Tests | Description |
|------|-------|-------------|
| `hover-presets.spec.ts` | 21 | Pre-defined hover effect presets (12 presets + sizes) |
| `hover-normal-mode.spec.ts` | 19 | Custom hover effects (color, scale, transitions) |
| `hover-icons.spec.ts` | 8 | Hover icons and icon sizes |

**Total: 48 tests**

## Running Hover Effects Tests

```bash
# Navigate to e2e directory
cd e2e

# Run all hover effects tests
npm run test:hover-effects

# Run hover effects tests in headed mode (see browser)
npm run test:hover-effects:headed

# Run specific test file
npx playwright test --config=tests/playwright.config.ts tests/specs/pro-features/hover-effects/hover-presets.spec.ts
```

## Hover Effect Types

FooGallery supports 3 hover effect types:
1. **None** - No hover effect
2. **Normal** - Custom (icons, captions, color, scaling, transitions)
3. **Preset** - Pre-defined stylish effects (12 presets)

## Test Coverage

### Hover Presets (`hover-presets.spec.ts`)
- Admin tab navigation to Hover Effects
- Hover effect type options visibility
- All 12 preset options when preset type selected
- Individual preset application:
  - Brad, Sadie, Layla, Oscar
  - Sarah, Goliath, Jazz, Lily
  - Ming, Selena, Steve, Zoe
- Preset size options: small, medium, large
- Cross-template compatibility (justified, masonry)

### Hover Normal Mode (`hover-normal-mode.spec.ts`)
- **Type Selection**: normal mode, none (disabled)
- **Color Effects**: colorize, grayscale, none
- **Scale Effects**: scale, zoom, semi-zoom, none
- **Transitions**: instant, fade, slide-up, slide-down, slide-left, slide-right, push
- Combined settings application
- Cross-template compatibility

### Hover Icons (`hover-icons.spec.ts`)
- Icon size options visibility
- Zoom icon application
- Zoom plus icon application
- Icon removal (none)
- Default icon size
- Combined with other hover settings
- Cross-template compatibility

## Frontend Verification

Each test verifies that the appropriate CSS classes are applied to the `.foogallery` container:

| Setting | CSS Class Pattern |
|---------|-------------------|
| Preset Mode | `fg-preset` |
| Preset Name | `fg-{preset-name}` (e.g., `fg-sadie`) |
| Preset Size | `fg-preset-{size}` |
| Color Effect | `fg-hover-colorize`, `fg-hover-grayscale` |
| Scale Effect | `fg-hover-scale`, `fg-hover-zoomed`, `fg-hover-semi-zoomed` |
| Transition | `fg-hover-{transition}` (e.g., `fg-hover-fade`) |
| Icon | `fg-hover-zoom`, `fg-hover-zoom2`, etc. |

## Available Presets

| Preset | Index | CSS Class |
|--------|-------|-----------|
| Brad | 0 | `fg-brad` |
| Sadie | 1 | `fg-sadie` |
| Layla | 2 | `fg-layla` |
| Oscar | 3 | `fg-oscar` |
| Sarah | 4 | `fg-sarah` |
| Goliath | 5 | `fg-goliath` |
| Jazz | 6 | `fg-jazz` |
| Lily | 7 | `fg-lily` |
| Ming | 8 | `fg-ming` |
| Selena | 9 | `fg-selena` |
| Steve | 10 | `fg-steve` |
| Zoe | 11 | `fg-zoe` |

## Related Files

- `tests/helpers/appearance-test-helper.ts` - Helper functions and constants
- `tests/specs/pro-features/appearance/` - Related appearance tests

## Troubleshooting

### Tests failing to find Hover Effects tab
The Hover Effects tab is typically Tab 4 in the gallery settings. Verify the selector matches your FooGallery version.

### Preset not applying
1. Ensure hover effect type is set to "preset" (index 2)
2. Preset settings only appear after selecting preset type
3. Wait for JavaScript to reveal conditional settings

### Icon settings not visible
Icon settings only appear when hover effect type is set to "normal" (index 1). The icon options are radio buttons that may be styled/hidden - the tests use JavaScript clicks to handle this.
