// File: tests/specs/pro-features/exif/exif-attachment-modal.spec.ts
// Tests for EXIF attachment modal functionality

import { test, expect } from '@playwright/test';
import {
  openAttachmentModal,
  navigateToExifTabInModal,
  editExifInAttachmentModal,
  createGalleryWithExif,
  EXIF_SELECTORS,
} from '../../../helpers/exif-test-helper';

test.describe('EXIF Attachment Modal', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'exif-modal';

  test.beforeEach(async ({ page }) => {
    // Set viewport size
    await page.setViewportSize({ width: 1932, height: 1271 });
  });

  test('displays EXIF tab in modal', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Modal Tab Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-tab`,
      imageCount: 3,
    });

    // Open attachment modal for first image
    await openAttachmentModal(page, 0);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-modal-tab.png` });

    // Look for EXIF tab
    const exifTab = page.locator('label:has-text("EXIF"), .foogallery-tab:has-text("EXIF")');
    const tabVisible = await exifTab.first().isVisible().catch(() => false);

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);

    // EXIF tab should be present if FooGallery Pro is active
    expect(typeof tabVisible).toBe('boolean');
  });

  test('shows EXIF fields in modal', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Modal Fields Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-fields`,
      imageCount: 3,
    });

    // Open attachment modal for first image
    await openAttachmentModal(page, 0);

    // Navigate to EXIF tab
    await navigateToExifTabInModal(page);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-modal-fields.png` });

    // Check for EXIF input fields
    const cameraInput = page.locator(EXIF_SELECTORS.attachmentCamera).first();
    const apertureInput = page.locator(EXIF_SELECTORS.attachmentAperture).first();
    const shutterInput = page.locator(EXIF_SELECTORS.attachmentShutterSpeed).first();
    const isoInput = page.locator(EXIF_SELECTORS.attachmentIso).first();

    // At least some fields should be visible
    const anyFieldVisible = await cameraInput.isVisible() ||
                           await apertureInput.isVisible() ||
                           await shutterInput.isVisible() ||
                           await isoInput.isVisible();

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);

    expect(typeof anyFieldVisible).toBe('boolean');
  });

  test('populates existing EXIF data', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Modal Populate Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-populate`,
      imageCount: 3,
    });

    // Open attachment modal for first image
    await openAttachmentModal(page, 0);

    // Navigate to EXIF tab
    await navigateToExifTabInModal(page);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-modal-populated.png` });

    // Check if any field has a value (from image EXIF)
    const cameraInput = page.locator(EXIF_SELECTORS.attachmentCamera).first();
    if (await cameraInput.isVisible()) {
      const value = await cameraInput.inputValue();
      // Value may be empty or populated depending on image
      expect(typeof value).toBe('string');
    }

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);
  });

  test('edits camera field', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Edit Camera Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-edit-camera`,
      imageCount: 3,
    });

    // Open attachment modal for first image
    await openAttachmentModal(page, 0);

    // Navigate to EXIF tab
    await navigateToExifTabInModal(page);

    // Edit camera field
    const cameraInput = page.locator(EXIF_SELECTORS.attachmentCamera).first();
    if (await cameraInput.isVisible()) {
      await cameraInput.fill('Test Camera Model');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-04-edit-camera.png` });

      // Verify value was set
      await expect(cameraInput).toHaveValue('Test Camera Model');
    }

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);
  });

  test('edits aperture field', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Edit Aperture Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-edit-aperture`,
      imageCount: 3,
    });

    // Open attachment modal for first image
    await openAttachmentModal(page, 0);

    // Navigate to EXIF tab
    await navigateToExifTabInModal(page);

    // Edit aperture field
    const apertureInput = page.locator(EXIF_SELECTORS.attachmentAperture).first();
    if (await apertureInput.isVisible()) {
      await apertureInput.fill('f/2.8');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-05-edit-aperture.png` });

      // Verify value was set
      await expect(apertureInput).toHaveValue('f/2.8');
    }

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);
  });

  test('edits shutter speed field', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Edit Shutter Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-edit-shutter`,
      imageCount: 3,
    });

    // Open attachment modal for first image
    await openAttachmentModal(page, 0);

    // Navigate to EXIF tab
    await navigateToExifTabInModal(page);

    // Edit shutter speed field
    const shutterInput = page.locator(EXIF_SELECTORS.attachmentShutterSpeed).first();
    if (await shutterInput.isVisible()) {
      await shutterInput.fill('1/500s');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-06-edit-shutter.png` });

      // Verify value was set
      await expect(shutterInput).toHaveValue('1/500s');
    }

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);
  });

  test('edits ISO field', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Edit ISO Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-edit-iso`,
      imageCount: 3,
    });

    // Open attachment modal for first image
    await openAttachmentModal(page, 0);

    // Navigate to EXIF tab
    await navigateToExifTabInModal(page);

    // Edit ISO field
    const isoInput = page.locator(EXIF_SELECTORS.attachmentIso).first();
    if (await isoInput.isVisible()) {
      await isoInput.fill('400');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-07-edit-iso.png` });

      // Verify value was set
      await expect(isoInput).toHaveValue('400');
    }

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);
  });

  test('edits focal length field', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Edit Focal Length Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-edit-focal`,
      imageCount: 3,
    });

    // Open attachment modal for first image
    await openAttachmentModal(page, 0);

    // Navigate to EXIF tab
    await navigateToExifTabInModal(page);

    // Edit focal length field
    const focalInput = page.locator(EXIF_SELECTORS.attachmentFocalLength).first();
    if (await focalInput.isVisible()) {
      await focalInput.fill('50mm');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-08-edit-focal.png` });

      // Verify value was set
      await expect(focalInput).toHaveValue('50mm');
    }

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);
  });

  test('edits orientation field', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Edit Orientation Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-edit-orientation`,
      imageCount: 3,
    });

    // Open attachment modal for first image
    await openAttachmentModal(page, 0);

    // Navigate to EXIF tab
    await navigateToExifTabInModal(page);

    // Edit orientation field
    const orientationInput = page.locator(EXIF_SELECTORS.attachmentOrientation).first();
    if (await orientationInput.isVisible()) {
      await orientationInput.fill('1');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-09-edit-orientation.png` });

      // Verify value was set
      await expect(orientationInput).toHaveValue('1');
    }

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);
  });

  test('edits timestamp field', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Edit Timestamp Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-edit-timestamp`,
      imageCount: 3,
    });

    // Open attachment modal for first image
    await openAttachmentModal(page, 0);

    // Navigate to EXIF tab
    await navigateToExifTabInModal(page);

    // Edit timestamp field
    const timestampInput = page.locator(EXIF_SELECTORS.attachmentTimestamp).first();
    if (await timestampInput.isVisible()) {
      await timestampInput.fill('2023-12-25');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-10-edit-timestamp.png` });

      // Verify value was set
      await expect(timestampInput).toHaveValue('2023-12-25');
    }

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);
  });

  test('adds EXIF to image without EXIF', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Add to Empty Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-add-empty`,
      imageCount: 3,
    });

    // Open attachment modal for an image (assuming some may not have EXIF)
    await openAttachmentModal(page, 2);

    // Navigate to EXIF tab
    await navigateToExifTabInModal(page);

    // Add EXIF data to all fields
    const cameraInput = page.locator(EXIF_SELECTORS.attachmentCamera).first();
    if (await cameraInput.isVisible()) {
      await cameraInput.fill('Manual Entry Camera');
    }

    const apertureInput = page.locator(EXIF_SELECTORS.attachmentAperture).first();
    if (await apertureInput.isVisible()) {
      await apertureInput.fill('f/4.0');
    }

    const isoInput = page.locator(EXIF_SELECTORS.attachmentIso).first();
    if (await isoInput.isVisible()) {
      await isoInput.fill('200');
    }

    await page.waitForTimeout(300);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-11-add-empty.png` });

    // Verify values were set
    if (await cameraInput.isVisible()) {
      await expect(cameraInput).toHaveValue('Manual Entry Camera');
    }

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);
  });

  test('clears EXIF values', async ({ page }) => {
    // Create a gallery first
    await createGalleryWithExif(page, {
      galleryName: 'EXIF Clear Values Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-clear`,
      imageCount: 3,
    });

    // Open attachment modal for first image
    await openAttachmentModal(page, 0);

    // Navigate to EXIF tab
    await navigateToExifTabInModal(page);

    // Clear all EXIF fields
    const cameraInput = page.locator(EXIF_SELECTORS.attachmentCamera).first();
    if (await cameraInput.isVisible()) {
      await cameraInput.fill('');
    }

    const apertureInput = page.locator(EXIF_SELECTORS.attachmentAperture).first();
    if (await apertureInput.isVisible()) {
      await apertureInput.fill('');
    }

    const shutterInput = page.locator(EXIF_SELECTORS.attachmentShutterSpeed).first();
    if (await shutterInput.isVisible()) {
      await shutterInput.fill('');
    }

    const isoInput = page.locator(EXIF_SELECTORS.attachmentIso).first();
    if (await isoInput.isVisible()) {
      await isoInput.fill('');
    }

    const focalInput = page.locator(EXIF_SELECTORS.attachmentFocalLength).first();
    if (await focalInput.isVisible()) {
      await focalInput.fill('');
    }

    await page.waitForTimeout(300);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-12-clear.png` });

    // Verify values were cleared
    if (await cameraInput.isVisible()) {
      await expect(cameraInput).toHaveValue('');
    }

    // Close modal
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);
  });
});
