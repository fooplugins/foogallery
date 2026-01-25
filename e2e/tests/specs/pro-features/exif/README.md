# EXIF E2E Tests

End-to-end tests for FooGallery Pro EXIF metadata display feature.

## Test Files

| File | Tests | Description |
|------|-------|-------------|
| `exif-settings.spec.ts` | 14 | EXIF settings configuration in gallery admin |
| `exif-frontend.spec.ts` | 10 | EXIF display on gallery frontend |
| `exif-display-layouts.spec.ts` | 9 | EXIF layout options (auto, full, partial, minimal) |
| `exif-lightbox.spec.ts` | 14 | EXIF display in lightbox |
| `exif-attachment-modal.spec.ts` | 12 | EXIF editing in attachment modal |
| `exif-global-settings.spec.ts` | 10 | Global EXIF settings configuration |

**Total: 69 tests**

## Running EXIF Tests

```bash
# Navigate to e2e directory
cd e2e

# Run all EXIF tests
npm run test:exif

# Run EXIF tests in headed mode (see browser)
npm run test:exif:headed

# Run specific test file
npx playwright test --config=tests/playwright.config.ts tests/specs/pro-features/exif/exif-settings.spec.ts
```

## Prerequisites

### Test Images with EXIF Data
Sample images with EXIF metadata are located in `e2e/test-assets/images/exif/`. These images contain camera, aperture, shutter speed, ISO, and other EXIF properties for testing.

## Test Coverage

### EXIF Settings (`exif-settings.spec.ts`)
- EXIF enabled/disabled toggle
- Display layout selection
- Attribute selection (camera, aperture, ISO, etc.)
- Icon style options
- Position configuration
- Background opacity
- Font size settings
- Show on hover option
- Always visible option
- Custom attribute labels
- Attribute ordering
- Template-specific settings
- Settings persistence
- Settings reset

### Frontend Display (`exif-frontend.spec.ts`)
- EXIF info button visible
- EXIF panel toggle
- Camera value display
- Aperture value display
- ISO value display
- Shutter speed display
- Focal length display
- Date/time display
- Orientation display
- EXIF hidden when disabled

### Display Layouts (`exif-display-layouts.spec.ts`)
- Auto layout (responsive)
- Full layout (icon + label + value)
- Partial layout (icon + value)
- Minimal layout (icon only with tooltip)
- Layout switching
- Auto layout screen size adaptation
- Full layout all properties
- Layout container CSS class
- Partial layout verification

### Lightbox Display (`exif-lightbox.spec.ts`)
- Info button in lightbox
- EXIF panel opens on click
- EXIF properties in panel
- Aperture value correctly formatted
- Camera value correctly formatted
- Date value correctly formatted
- Shutter speed correctly formatted
- Focal length correctly formatted
- ISO correctly formatted
- Orientation correctly formatted
- EXIF updates during navigation
- EXIF hidden for images without EXIF
- EXIF panel closes
- Tooltip shows value on hover (minimal layout)

### Attachment Modal (`exif-attachment-modal.spec.ts`)
- EXIF tab displays in modal
- EXIF fields visible in modal
- Existing EXIF data populated
- Edit camera field with frontend verification
- Edit aperture field with frontend verification
- Edit shutter speed field with frontend verification
- Edit ISO field with frontend verification
- Edit focal length field with frontend verification
- Edit orientation field with frontend verification
- Edit timestamp field with frontend verification
- Add EXIF to image without EXIF
- Clear EXIF values with frontend verification

### Global Settings (`exif-global-settings.spec.ts`)
- Navigate to global EXIF settings
- Display exif_attributes setting
- Modify allowed attributes with frontend verification
- Customize aperture label with frontend verification
- Customize camera label with frontend verification
- Customize date label with frontend verification
- Customize exposure label with frontend verification
- Customize focal length label with frontend verification
- Customize ISO label with frontend verification
- Empty attributes disables EXIF with frontend verification

## Related Files

- `tests/helpers/exif-test-helper.ts` - Helper functions for EXIF tests
- `test-assets/images/exif/` - Test images with EXIF metadata
- `docker/scripts/setup-wordpress.sh` - EXIF image import during setup
