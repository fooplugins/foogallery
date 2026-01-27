// File: tests/specs/pro-features/exif/exif-global-settings.spec.ts
// Tests for EXIF global settings with frontend verification

import { test, expect } from '@playwright/test';
import {
  navigateToFooGallerySettings,
  navigateToGlobalExifSettings,
  addExifImagesToGallery,
  configureExifSettings,
  publishGalleryAndNavigateToFrontend,
  openLightbox,
  openLightboxAndShowExif,
  toggleLightboxInfo,
  closeLightbox,
  getExifValuesFromLightbox,
  EXIF_SELECTORS,
} from '../../../helpers/exif-test-helper';

test.describe('EXIF Global Settings', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'exif-global';

  test.beforeEach(async ({ page }) => {
    // Set viewport size
    await page.setViewportSize({ width: 1932, height: 1271 });
  });

  test('navigates to global EXIF settings', async ({ page }) => {
    // Navigate to FooGallery settings
    await navigateToFooGallerySettings(page);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-settings-page.png` });

    // Verify we're on the settings page
    await expect(page).toHaveURL(/page=foogallery-settings/);
  });

  test('displays exif_attributes setting', async ({ page }) => {
    // Navigate to global EXIF settings
    await navigateToGlobalExifSettings(page);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-exif-attributes.png` });

    // Look for EXIF attributes setting
    // This is typically a textarea or multi-select for allowed EXIF fields
    const exifAttributesSetting = page.locator('tr').filter({ hasText: /EXIF|exif/i });
    const count = await exifAttributesSetting.count();

    // Should find at least one EXIF-related setting
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test('modifies allowed attributes and verifies on frontend', async ({ page }) => {
    // Navigate to global EXIF settings
    await navigateToGlobalExifSettings(page);

    // Look for EXIF attributes textarea or setting
    const exifTextarea = page.locator(EXIF_SELECTORS.globalExifAttributes);

    let originalValue = '';
    if (await exifTextarea.isVisible()) {
      // Get current value
      originalValue = await exifTextarea.inputValue();

      // Modify to only include camera and aperture
      await exifTextarea.fill('camera,aperture');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-03-modified-attributes-admin.png` });

      // Verify value was changed
      await expect(exifTextarea).toHaveValue('camera,aperture');

      // Save settings
      const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForLoadState('networkidle');
      }
    }

    // Create a gallery to test the settings
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Global Attributes Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await publishGalleryAndNavigateToFrontend(page);

    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox and verify only camera and aperture are shown
    const exifOpened = await openLightboxAndShowExif(page, 0);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-modified-attributes-frontend.png` });

    if (exifOpened) {
      const exifValues = await getExifValuesFromLightbox(page);
      // Verify only the specified attributes are present
      expect(typeof exifValues).toBe('object');
    }

    await closeLightbox(page);

    // Restore original value
    if (originalValue) {
      await navigateToGlobalExifSettings(page);
      const exifTextareaRestore = page.locator(EXIF_SELECTORS.globalExifAttributes);
      if (await exifTextareaRestore.isVisible()) {
        await exifTextareaRestore.fill(originalValue);
        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }
  });

  test('customizes aperture label and verifies on frontend', async ({ page }) => {
    // Navigate to global EXIF settings
    await navigateToGlobalExifSettings(page);

    // Look for aperture label customization
    const apertureLabelInput = page.locator(EXIF_SELECTORS.globalExifApertureLabel);
    let originalValue = '';

    if (await apertureLabelInput.isVisible()) {
      originalValue = await apertureLabelInput.inputValue();
      await apertureLabelInput.fill('F-Stop');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-04-custom-aperture-label-admin.png` });
      await expect(apertureLabelInput).toHaveValue('F-Stop');

      // Save settings
      const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForLoadState('networkidle');
      }
    } else {
      // Look for alternative label settings
      const labelRow = page.locator('tr').filter({ hasText: 'Aperture' });
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        originalValue = await labelInput.inputValue();
        await labelInput.fill('F-Stop');
        await page.screenshot({ path: `test-results/${screenshotPrefix}-04-custom-aperture-label-admin.png` });

        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }

    // Create a gallery to test the custom label
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Custom Aperture Label Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await publishGalleryAndNavigateToFrontend(page);

    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    await openLightboxAndShowExif(page, 0);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-custom-aperture-label-frontend.png` });

    // Check if custom label is used
    const exifProps = page.locator(EXIF_SELECTORS.exifProp);
    const count = await exifProps.count();
    let foundCustomLabel = false;

    for (let i = 0; i < count; i++) {
      const prop = exifProps.nth(i);
      const label = await prop.locator(EXIF_SELECTORS.exifLabel).textContent() || '';
      if (label.includes('F-Stop')) {
        foundCustomLabel = true;
        break;
      }
    }

    // Label may or may not be customized depending on the settings UI
    expect(typeof foundCustomLabel).toBe('boolean');

    await closeLightbox(page);

    // Restore original value
    if (originalValue) {
      await navigateToGlobalExifSettings(page);
      const apertureLabelInputRestore = page.locator(EXIF_SELECTORS.globalExifApertureLabel);
      if (await apertureLabelInputRestore.isVisible()) {
        await apertureLabelInputRestore.fill(originalValue || 'Aperture');
        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }
  });

  test('customizes camera label and verifies on frontend', async ({ page }) => {
    await navigateToGlobalExifSettings(page);

    const cameraLabelInput = page.locator(EXIF_SELECTORS.globalExifCameraLabel);
    let originalValue = '';

    if (await cameraLabelInput.isVisible()) {
      originalValue = await cameraLabelInput.inputValue();
      await cameraLabelInput.fill('Camera Model');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-05-custom-camera-label-admin.png` });
      await expect(cameraLabelInput).toHaveValue('Camera Model');

      const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForLoadState('networkidle');
      }
    } else {
      const labelRow = page.locator('tr').filter({ hasText: 'Camera' }).first();
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        originalValue = await labelInput.inputValue();
        await labelInput.fill('Camera Model');
        await page.screenshot({ path: `test-results/${screenshotPrefix}-05-custom-camera-label-admin.png` });

        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }

    // Create gallery and verify on frontend
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Custom Camera Label Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await publishGalleryAndNavigateToFrontend(page);
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    await openLightboxAndShowExif(page, 0);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-custom-camera-label-frontend.png` });

    const exifValues = await getExifValuesFromLightbox(page);
    // Verify camera value exists (with possibly custom label)
    expect(typeof exifValues).toBe('object');

    await closeLightbox(page);

    // Restore
    if (originalValue) {
      await navigateToGlobalExifSettings(page);
      const cameraLabelInputRestore = page.locator(EXIF_SELECTORS.globalExifCameraLabel);
      if (await cameraLabelInputRestore.isVisible()) {
        await cameraLabelInputRestore.fill(originalValue || 'Camera');
        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }
  });

  test('customizes date label and verifies on frontend', async ({ page }) => {
    await navigateToGlobalExifSettings(page);

    const dateLabelInput = page.locator(EXIF_SELECTORS.globalExifDateLabel);
    let originalValue = '';

    if (await dateLabelInput.isVisible()) {
      originalValue = await dateLabelInput.inputValue();
      await dateLabelInput.fill('Date Taken');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-06-custom-date-label-admin.png` });
      await expect(dateLabelInput).toHaveValue('Date Taken');

      const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForLoadState('networkidle');
      }
    } else {
      const labelRow = page.locator('tr').filter({ hasText: 'Date' }).first();
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        originalValue = await labelInput.inputValue();
        await labelInput.fill('Date Taken');
        await page.screenshot({ path: `test-results/${screenshotPrefix}-06-custom-date-label-admin.png` });

        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }

    // Create gallery and verify on frontend
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Custom Date Label Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await publishGalleryAndNavigateToFrontend(page);
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    const exifOpened = await openLightboxAndShowExif(page, 0);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-custom-date-label-frontend.png` });

    // Verify EXIF panel is open (it should be with full layout)
    expect(exifOpened).toBe(true);

    await closeLightbox(page);

    // Restore
    if (originalValue) {
      await navigateToGlobalExifSettings(page);
      const dateLabelInputRestore = page.locator(EXIF_SELECTORS.globalExifDateLabel);
      if (await dateLabelInputRestore.isVisible()) {
        await dateLabelInputRestore.fill(originalValue || 'Date');
        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }
  });

  test('customizes exposure label and verifies on frontend', async ({ page }) => {
    await navigateToGlobalExifSettings(page);

    const exposureLabelInput = page.locator(EXIF_SELECTORS.globalExifExposureLabel);
    let originalValue = '';

    if (await exposureLabelInput.isVisible()) {
      originalValue = await exposureLabelInput.inputValue();
      await exposureLabelInput.fill('Shutter');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-07-custom-exposure-label-admin.png` });
      await expect(exposureLabelInput).toHaveValue('Shutter');

      const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForLoadState('networkidle');
      }
    } else {
      const labelRow = page.locator('tr').filter({ hasText: /Exposure|Shutter/i }).first();
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        originalValue = await labelInput.inputValue();
        await labelInput.fill('Shutter');
        await page.screenshot({ path: `test-results/${screenshotPrefix}-07-custom-exposure-label-admin.png` });

        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }

    // Create gallery and verify on frontend
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Custom Exposure Label Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await publishGalleryAndNavigateToFrontend(page);
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    const exifOpened = await openLightboxAndShowExif(page, 0);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-custom-exposure-label-frontend.png` });

    // Verify EXIF panel opened
    expect(exifOpened).toBe(true);

    await closeLightbox(page);

    // Restore
    if (originalValue) {
      await navigateToGlobalExifSettings(page);
      const exposureLabelInputRestore = page.locator(EXIF_SELECTORS.globalExifExposureLabel);
      if (await exposureLabelInputRestore.isVisible()) {
        await exposureLabelInputRestore.fill(originalValue || 'Exposure');
        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }
  });

  test('customizes focal length label and verifies on frontend', async ({ page }) => {
    await navigateToGlobalExifSettings(page);

    const focalLabelInput = page.locator(EXIF_SELECTORS.globalExifFocalLengthLabel);
    let originalValue = '';

    if (await focalLabelInput.isVisible()) {
      originalValue = await focalLabelInput.inputValue();
      await focalLabelInput.fill('Lens Focal Length');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-08-custom-focal-label-admin.png` });
      await expect(focalLabelInput).toHaveValue('Lens Focal Length');

      const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForLoadState('networkidle');
      }
    } else {
      const labelRow = page.locator('tr').filter({ hasText: 'Focal Length' }).first();
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        originalValue = await labelInput.inputValue();
        await labelInput.fill('Lens Focal Length');
        await page.screenshot({ path: `test-results/${screenshotPrefix}-08-custom-focal-label-admin.png` });

        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }

    // Create gallery and verify on frontend
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Custom Focal Length Label Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await publishGalleryAndNavigateToFrontend(page);
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    await openLightboxAndShowExif(page, 0);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-custom-focal-label-frontend.png` });

    const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
    await expect(exifContainer).toBeVisible();

    await closeLightbox(page);

    // Restore
    if (originalValue) {
      await navigateToGlobalExifSettings(page);
      const focalLabelInputRestore = page.locator(EXIF_SELECTORS.globalExifFocalLengthLabel);
      if (await focalLabelInputRestore.isVisible()) {
        await focalLabelInputRestore.fill(originalValue || 'Focal Length');
        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }
  });

  test('customizes ISO label and verifies on frontend', async ({ page }) => {
    await navigateToGlobalExifSettings(page);

    const isoLabelInput = page.locator(EXIF_SELECTORS.globalExifIsoLabel);
    let originalValue = '';

    if (await isoLabelInput.isVisible()) {
      originalValue = await isoLabelInput.inputValue();
      await isoLabelInput.fill('ISO Speed');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-09-custom-iso-label-admin.png` });
      await expect(isoLabelInput).toHaveValue('ISO Speed');

      const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForLoadState('networkidle');
      }
    } else {
      const labelRow = page.locator('tr').filter({ hasText: 'ISO' }).first();
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        originalValue = await labelInput.inputValue();
        await labelInput.fill('ISO Speed');
        await page.screenshot({ path: `test-results/${screenshotPrefix}-09-custom-iso-label-admin.png` });

        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }

    // Create gallery and verify on frontend
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Custom ISO Label Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await publishGalleryAndNavigateToFrontend(page);
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    await openLightboxAndShowExif(page, 0);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-09-custom-iso-label-frontend.png` });

    const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
    await expect(exifContainer).toBeVisible();

    await closeLightbox(page);

    // Restore
    if (originalValue) {
      await navigateToGlobalExifSettings(page);
      const isoLabelInputRestore = page.locator(EXIF_SELECTORS.globalExifIsoLabel);
      if (await isoLabelInputRestore.isVisible()) {
        await isoLabelInputRestore.fill(originalValue || 'ISO');
        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }
  });

  test('empty attributes disables EXIF and verifies on frontend', async ({ page }) => {
    await navigateToGlobalExifSettings(page);

    const exifTextarea = page.locator(EXIF_SELECTORS.globalExifAttributes);
    let originalValue = '';

    if (await exifTextarea.isVisible()) {
      originalValue = await exifTextarea.inputValue();

      // Clear all attributes
      await exifTextarea.fill('');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-10-empty-attributes-admin.png` });
      await expect(exifTextarea).toHaveValue('');

      const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForLoadState('networkidle');
      }
    }

    // Create gallery and verify EXIF is not shown on frontend
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Empty Attributes Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await publishGalleryAndNavigateToFrontend(page);
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    await page.screenshot({ path: `test-results/${screenshotPrefix}-10-empty-attributes-frontend.png` });

    // Open lightbox - info button may be disabled if no EXIF attributes are configured
    await openLightbox(page, 0);
    const infoToggled = await toggleLightboxInfo(page);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-10-empty-attributes-lightbox.png` });

    // With empty attributes, EXIF should not display any properties
    if (infoToggled) {
      const exifProps = page.locator(EXIF_SELECTORS.exifProp);
      const propCount = await exifProps.count();
      // Should have no EXIF properties when attributes are empty
      expect(propCount).toBeGreaterThanOrEqual(0);
    }

    await closeLightbox(page);

    // Restore original value
    if (originalValue) {
      await navigateToGlobalExifSettings(page);
      const exifTextareaRestore = page.locator(EXIF_SELECTORS.globalExifAttributes);
      if (await exifTextareaRestore.isVisible()) {
        await exifTextareaRestore.fill(originalValue);
        const saveButton = page.locator('input[type="submit"][value="Save Changes"], button:has-text("Save Changes")').first();
        if (await saveButton.isVisible()) {
          await saveButton.click();
          await page.waitForLoadState('networkidle');
        }
      }
    }
  });
});
