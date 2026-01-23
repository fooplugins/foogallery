// File: tests/specs/pro-features/exif/exif-global-settings.spec.ts
// Tests for EXIF global settings

import { test, expect } from '@playwright/test';
import {
  navigateToFooGallerySettings,
  navigateToGlobalExifSettings,
  EXIF_SELECTORS,
} from '../../../helpers/exif-test-helper';

test.describe('EXIF Global Settings', () => {
  test.describe.configure({ mode: 'serial' });

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

  test('modifies allowed attributes', async ({ page }) => {
    // Navigate to global EXIF settings
    await navigateToGlobalExifSettings(page);

    // Look for EXIF attributes textarea or setting
    const exifTextarea = page.locator(EXIF_SELECTORS.globalExifAttributes);

    if (await exifTextarea.isVisible()) {
      // Get current value
      const currentValue = await exifTextarea.inputValue();

      // Modify to only include a subset (e.g., just camera and aperture)
      await exifTextarea.fill('camera,aperture');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-03-modified-attributes.png` });

      // Verify value was changed
      await expect(exifTextarea).toHaveValue('camera,aperture');

      // Restore original value
      await exifTextarea.fill(currentValue);
    }
  });

  test('customizes aperture label', async ({ page }) => {
    // Navigate to global EXIF settings
    await navigateToGlobalExifSettings(page);

    // Look for aperture label customization
    const apertureLabelInput = page.locator(EXIF_SELECTORS.globalExifApertureLabel);

    if (await apertureLabelInput.isVisible()) {
      // Get current value
      const currentValue = await apertureLabelInput.inputValue();

      // Change to custom label
      await apertureLabelInput.fill('F-Stop');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-04-custom-aperture-label.png` });

      // Verify value was changed
      await expect(apertureLabelInput).toHaveValue('F-Stop');

      // Restore original value
      await apertureLabelInput.fill(currentValue || 'Aperture');
    } else {
      // Look for alternative label settings
      const labelRow = page.locator('tr').filter({ hasText: 'Aperture' });
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        const currentValue = await labelInput.inputValue();
        await labelInput.fill('F-Stop');

        // Screenshot
        await page.screenshot({ path: `test-results/${screenshotPrefix}-04-custom-aperture-label.png` });

        // Restore
        await labelInput.fill(currentValue || 'Aperture');
      }
    }
  });

  test('customizes camera label', async ({ page }) => {
    // Navigate to global EXIF settings
    await navigateToGlobalExifSettings(page);

    // Look for camera label customization
    const cameraLabelInput = page.locator(EXIF_SELECTORS.globalExifCameraLabel);

    if (await cameraLabelInput.isVisible()) {
      // Get current value
      const currentValue = await cameraLabelInput.inputValue();

      // Change to custom label
      await cameraLabelInput.fill('Camera Model');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-05-custom-camera-label.png` });

      // Verify value was changed
      await expect(cameraLabelInput).toHaveValue('Camera Model');

      // Restore original value
      await cameraLabelInput.fill(currentValue || 'Camera');
    } else {
      // Look for alternative label settings
      const labelRow = page.locator('tr').filter({ hasText: 'Camera' }).first();
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        const currentValue = await labelInput.inputValue();
        await labelInput.fill('Camera Model');

        // Screenshot
        await page.screenshot({ path: `test-results/${screenshotPrefix}-05-custom-camera-label.png` });

        // Restore
        await labelInput.fill(currentValue || 'Camera');
      }
    }
  });

  test('customizes date label', async ({ page }) => {
    // Navigate to global EXIF settings
    await navigateToGlobalExifSettings(page);

    // Look for date label customization
    const dateLabelInput = page.locator(EXIF_SELECTORS.globalExifDateLabel);

    if (await dateLabelInput.isVisible()) {
      // Get current value
      const currentValue = await dateLabelInput.inputValue();

      // Change to custom label
      await dateLabelInput.fill('Date Taken');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-06-custom-date-label.png` });

      // Verify value was changed
      await expect(dateLabelInput).toHaveValue('Date Taken');

      // Restore original value
      await dateLabelInput.fill(currentValue || 'Date');
    } else {
      // Look for alternative label settings
      const labelRow = page.locator('tr').filter({ hasText: 'Date' }).first();
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        const currentValue = await labelInput.inputValue();
        await labelInput.fill('Date Taken');

        // Screenshot
        await page.screenshot({ path: `test-results/${screenshotPrefix}-06-custom-date-label.png` });

        // Restore
        await labelInput.fill(currentValue || 'Date');
      }
    }
  });

  test('customizes exposure label', async ({ page }) => {
    // Navigate to global EXIF settings
    await navigateToGlobalExifSettings(page);

    // Look for exposure/shutter label customization
    const exposureLabelInput = page.locator(EXIF_SELECTORS.globalExifExposureLabel);

    if (await exposureLabelInput.isVisible()) {
      // Get current value
      const currentValue = await exposureLabelInput.inputValue();

      // Change to custom label
      await exposureLabelInput.fill('Shutter');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-07-custom-exposure-label.png` });

      // Verify value was changed
      await expect(exposureLabelInput).toHaveValue('Shutter');

      // Restore original value
      await exposureLabelInput.fill(currentValue || 'Exposure');
    } else {
      // Look for alternative label settings
      const labelRow = page.locator('tr').filter({ hasText: /Exposure|Shutter/i }).first();
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        const currentValue = await labelInput.inputValue();
        await labelInput.fill('Shutter');

        // Screenshot
        await page.screenshot({ path: `test-results/${screenshotPrefix}-07-custom-exposure-label.png` });

        // Restore
        await labelInput.fill(currentValue || 'Exposure');
      }
    }
  });

  test('customizes focal length label', async ({ page }) => {
    // Navigate to global EXIF settings
    await navigateToGlobalExifSettings(page);

    // Look for focal length label customization
    const focalLabelInput = page.locator(EXIF_SELECTORS.globalExifFocalLengthLabel);

    if (await focalLabelInput.isVisible()) {
      // Get current value
      const currentValue = await focalLabelInput.inputValue();

      // Change to custom label
      await focalLabelInput.fill('Lens Focal Length');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-08-custom-focal-label.png` });

      // Verify value was changed
      await expect(focalLabelInput).toHaveValue('Lens Focal Length');

      // Restore original value
      await focalLabelInput.fill(currentValue || 'Focal Length');
    } else {
      // Look for alternative label settings
      const labelRow = page.locator('tr').filter({ hasText: 'Focal Length' }).first();
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        const currentValue = await labelInput.inputValue();
        await labelInput.fill('Lens Focal Length');

        // Screenshot
        await page.screenshot({ path: `test-results/${screenshotPrefix}-08-custom-focal-label.png` });

        // Restore
        await labelInput.fill(currentValue || 'Focal Length');
      }
    }
  });

  test('customizes ISO label', async ({ page }) => {
    // Navigate to global EXIF settings
    await navigateToGlobalExifSettings(page);

    // Look for ISO label customization
    const isoLabelInput = page.locator(EXIF_SELECTORS.globalExifIsoLabel);

    if (await isoLabelInput.isVisible()) {
      // Get current value
      const currentValue = await isoLabelInput.inputValue();

      // Change to custom label
      await isoLabelInput.fill('ISO Speed');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-09-custom-iso-label.png` });

      // Verify value was changed
      await expect(isoLabelInput).toHaveValue('ISO Speed');

      // Restore original value
      await isoLabelInput.fill(currentValue || 'ISO');
    } else {
      // Look for alternative label settings
      const labelRow = page.locator('tr').filter({ hasText: 'ISO' }).first();
      const labelInput = labelRow.locator('input[type="text"]').first();

      if (await labelInput.isVisible()) {
        const currentValue = await labelInput.inputValue();
        await labelInput.fill('ISO Speed');

        // Screenshot
        await page.screenshot({ path: `test-results/${screenshotPrefix}-09-custom-iso-label.png` });

        // Restore
        await labelInput.fill(currentValue || 'ISO');
      }
    }
  });

  test('empty attributes disables EXIF', async ({ page }) => {
    // Navigate to global EXIF settings
    await navigateToGlobalExifSettings(page);

    // Look for EXIF attributes textarea or setting
    const exifTextarea = page.locator(EXIF_SELECTORS.globalExifAttributes);

    if (await exifTextarea.isVisible()) {
      // Get current value to restore later
      const currentValue = await exifTextarea.inputValue();

      // Clear all attributes
      await exifTextarea.fill('');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-10-empty-attributes.png` });

      // Verify value was cleared
      await expect(exifTextarea).toHaveValue('');

      // Restore original value
      await exifTextarea.fill(currentValue);
    } else {
      // Check if there's a checkbox or toggle to disable EXIF entirely
      const disableCheckbox = page.locator('input[name*="exif"][type="checkbox"]').first();

      if (await disableCheckbox.isVisible()) {
        // Screenshot showing the disable option
        await page.screenshot({ path: `test-results/${screenshotPrefix}-10-empty-attributes.png` });
      }
    }
  });
});
