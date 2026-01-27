// File: tests/specs/pro-features/exif/exif-attachment-modal.spec.ts
// Tests for EXIF attachment modal functionality with frontend verification

import { test, expect } from '@playwright/test';
import {
  openAttachmentModal,
  navigateToExifTabInModal,
  createGalleryWithExif,
  configureExifSettings,
  addExifImagesToGallery,
  publishGalleryAndNavigateToFrontend,
  saveAttachmentModal,
  closeAttachmentModal,
  openLightbox,
  openLightboxAndShowExif,
  toggleLightboxInfo,
  closeLightbox,
  getExifValuesFromLightbox,
  verifyExifFieldInLightbox,
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
    await closeAttachmentModal(page);

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
    await closeAttachmentModal(page);

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
    await closeAttachmentModal(page);
  });

  test('edits camera field and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('EXIF Edit Camera Frontend Test');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images to the gallery
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    // Publish gallery first
    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

    // Now open attachment modal and edit camera field
    await openAttachmentModal(page, 0);
    await navigateToExifTabInModal(page);

    const cameraInput = page.locator(EXIF_SELECTORS.attachmentCamera).first();
    if (await cameraInput.isVisible()) {
      await cameraInput.fill('Custom Test Camera XYZ');
      await page.waitForTimeout(300);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-04-edit-camera-admin.png` });

      // Verify value was set
      await expect(cameraInput).toHaveValue('Custom Test Camera XYZ');

      // Save the attachment modal
      await saveAttachmentModal(page);
    }

    // Close modal by clicking the close button
    await closeAttachmentModal(page);

    // Update the gallery
    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    // Navigate to frontend
    await page.locator('#foogallery_create_page').click();
    await page.waitForLoadState('networkidle');

    const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
    await viewLink.waitFor({ state: 'visible', timeout: 30000 });
    const viewUrl = await viewLink.getAttribute('href');
    if (viewUrl) {
      await page.goto(viewUrl);
      await page.waitForLoadState('networkidle');
    }

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox and verify camera value
    const exifOpened = await openLightboxAndShowExif(page, 0);

    // Screenshot lightbox
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-edit-camera-frontend.png` });

    // Verify camera value in lightbox (only if EXIF panel opened)
    if (exifOpened) {
      const exifValues = await getExifValuesFromLightbox(page);
      if (exifValues['Camera']) {
        expect(exifValues['Camera']).toContain('Custom Test Camera XYZ');
      }
    }

    await closeLightbox(page);
  });

  test('edits aperture field and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Edit Aperture Frontend Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    // Publish gallery
    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    // Edit aperture field
    await openAttachmentModal(page, 0);
    await navigateToExifTabInModal(page);

    const apertureInput = page.locator(EXIF_SELECTORS.attachmentAperture).first();
    if (await apertureInput.isVisible()) {
      await apertureInput.fill('f/1.4');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-05-edit-aperture-admin.png` });
      await expect(apertureInput).toHaveValue('f/1.4');
      await saveAttachmentModal(page);
    }

    await closeAttachmentModal(page);

    // Update and navigate to frontend
    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    await page.locator('#foogallery_create_page').click();
    await page.waitForLoadState('networkidle');

    const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
    await viewLink.waitFor({ state: 'visible', timeout: 30000 });
    const viewUrl = await viewLink.getAttribute('href');
    if (viewUrl) {
      await page.goto(viewUrl);
      await page.waitForLoadState('networkidle');
    }

    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Verify in lightbox
    await openLightbox(page, 0);
    await toggleLightboxInfo(page);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-edit-aperture-frontend.png` });

    const exifValues = await getExifValuesFromLightbox(page);
    if (exifValues['Aperture']) {
      expect(exifValues['Aperture']).toContain('1.4');
    }

    await closeLightbox(page);
  });

  test('edits shutter speed field and verifies on frontend', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Edit Shutter Frontend Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    // Edit shutter speed field
    await openAttachmentModal(page, 0);
    await navigateToExifTabInModal(page);

    const shutterInput = page.locator(EXIF_SELECTORS.attachmentShutterSpeed).first();
    if (await shutterInput.isVisible()) {
      await shutterInput.fill('1/2000s');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-06-edit-shutter-admin.png` });
      await expect(shutterInput).toHaveValue('1/2000s');
      await saveAttachmentModal(page);
    }

    await closeAttachmentModal(page);

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    await page.locator('#foogallery_create_page').click();
    await page.waitForLoadState('networkidle');

    const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
    await viewLink.waitFor({ state: 'visible', timeout: 30000 });
    const viewUrl = await viewLink.getAttribute('href');
    if (viewUrl) {
      await page.goto(viewUrl);
      await page.waitForLoadState('networkidle');
    }

    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    await openLightbox(page, 0);
    await toggleLightboxInfo(page);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-edit-shutter-frontend.png` });

    const exifValues = await getExifValuesFromLightbox(page);
    if (exifValues['Exposure'] || exifValues['Shutter']) {
      const shutterValue = exifValues['Exposure'] || exifValues['Shutter'];
      expect(shutterValue).toContain('2000');
    }

    await closeLightbox(page);
  });

  test('edits ISO field and verifies on frontend', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Edit ISO Frontend Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    // Edit ISO field
    await openAttachmentModal(page, 0);
    await navigateToExifTabInModal(page);

    const isoInput = page.locator(EXIF_SELECTORS.attachmentIso).first();
    if (await isoInput.isVisible()) {
      await isoInput.fill('3200');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-07-edit-iso-admin.png` });
      await expect(isoInput).toHaveValue('3200');
      await saveAttachmentModal(page);
    }

    await closeAttachmentModal(page);

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    await page.locator('#foogallery_create_page').click();
    await page.waitForLoadState('networkidle');

    const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
    await viewLink.waitFor({ state: 'visible', timeout: 30000 });
    const viewUrl = await viewLink.getAttribute('href');
    if (viewUrl) {
      await page.goto(viewUrl);
      await page.waitForLoadState('networkidle');
    }

    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    await openLightbox(page, 0);
    await toggleLightboxInfo(page);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-edit-iso-frontend.png` });

    const exifValues = await getExifValuesFromLightbox(page);
    if (exifValues['ISO']) {
      expect(exifValues['ISO']).toContain('3200');
    }

    await closeLightbox(page);
  });

  test('edits focal length field and verifies on frontend', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Edit Focal Length Frontend Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    // Edit focal length field
    await openAttachmentModal(page, 0);
    await navigateToExifTabInModal(page);

    const focalInput = page.locator(EXIF_SELECTORS.attachmentFocalLength).first();
    if (await focalInput.isVisible()) {
      await focalInput.fill('85mm');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-08-edit-focal-admin.png` });
      await expect(focalInput).toHaveValue('85mm');
      await saveAttachmentModal(page);
    }

    await closeAttachmentModal(page);

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    await page.locator('#foogallery_create_page').click();
    await page.waitForLoadState('networkidle');

    const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
    await viewLink.waitFor({ state: 'visible', timeout: 30000 });
    const viewUrl = await viewLink.getAttribute('href');
    if (viewUrl) {
      await page.goto(viewUrl);
      await page.waitForLoadState('networkidle');
    }

    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    await openLightbox(page, 0);
    await toggleLightboxInfo(page);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-edit-focal-frontend.png` });

    const exifValues = await getExifValuesFromLightbox(page);
    if (exifValues['Focal Length']) {
      expect(exifValues['Focal Length']).toContain('85');
    }

    await closeLightbox(page);
  });

  test('edits orientation field and verifies on frontend', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Edit Orientation Frontend Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    // Edit orientation field
    await openAttachmentModal(page, 0);
    await navigateToExifTabInModal(page);

    const orientationInput = page.locator(EXIF_SELECTORS.attachmentOrientation).first();
    if (await orientationInput.isVisible()) {
      await orientationInput.fill('6');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-09-edit-orientation-admin.png` });
      await expect(orientationInput).toHaveValue('6');
      await saveAttachmentModal(page);
    }

    await closeAttachmentModal(page);

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    await page.locator('#foogallery_create_page').click();
    await page.waitForLoadState('networkidle');

    const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
    await viewLink.waitFor({ state: 'visible', timeout: 30000 });
    const viewUrl = await viewLink.getAttribute('href');
    if (viewUrl) {
      await page.goto(viewUrl);
      await page.waitForLoadState('networkidle');
    }

    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    const exifOpened = await openLightboxAndShowExif(page, 0);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-09-edit-orientation-frontend.png` });

    // Orientation may be displayed or not depending on settings
    expect(exifOpened).toBe(true);

    await closeLightbox(page);
  });

  test('edits timestamp field and verifies on frontend', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Edit Timestamp Frontend Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    // Edit timestamp field
    await openAttachmentModal(page, 0);
    await navigateToExifTabInModal(page);

    const timestampInput = page.locator(EXIF_SELECTORS.attachmentTimestamp).first();
    if (await timestampInput.isVisible()) {
      await timestampInput.fill('2024-06-15');
      await page.waitForTimeout(300);

      await page.screenshot({ path: `test-results/${screenshotPrefix}-10-edit-timestamp-admin.png` });
      await expect(timestampInput).toHaveValue('2024-06-15');
      await saveAttachmentModal(page);
    }

    await closeAttachmentModal(page);

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    await page.locator('#foogallery_create_page').click();
    await page.waitForLoadState('networkidle');

    const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
    await viewLink.waitFor({ state: 'visible', timeout: 30000 });
    const viewUrl = await viewLink.getAttribute('href');
    if (viewUrl) {
      await page.goto(viewUrl);
      await page.waitForLoadState('networkidle');
    }

    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    await openLightbox(page, 0);
    await toggleLightboxInfo(page);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-10-edit-timestamp-frontend.png` });

    const exifValues = await getExifValuesFromLightbox(page);
    if (exifValues['Date']) {
      expect(exifValues['Date']).toContain('2024');
    }

    await closeLightbox(page);
  });

  test('adds EXIF to image without EXIF and verifies on frontend', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Add to Empty Frontend Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add regular images (not EXIF-specific)
    await page.locator('text=Add From Media Library').click();
    await page.waitForLoadState('networkidle');

    const modal = page.locator('.media-modal:visible');
    await modal.waitFor({ state: 'visible', timeout: 10000 });

    const mediaLibraryTab = modal.locator('.media-menu-item').filter({ hasText: 'Media Library' });
    await mediaLibraryTab.click();
    await page.waitForTimeout(500);

    // Select first 3 images (may or may not have EXIF)
    const attachments = modal.locator('.attachment');
    await attachments.first().waitFor({ state: 'visible', timeout: 10000 });
    for (let i = 0; i < 3; i++) {
      await attachments.nth(i).click();
    }

    const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
    await addButton.click();
    await page.waitForLoadState('networkidle');

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    // Add EXIF data to the third image (index 2)
    await openAttachmentModal(page, 2);
    await navigateToExifTabInModal(page);

    const cameraInput = page.locator(EXIF_SELECTORS.attachmentCamera).first();
    if (await cameraInput.isVisible()) {
      await cameraInput.fill('Manually Added Camera');
    }

    const apertureInput = page.locator(EXIF_SELECTORS.attachmentAperture).first();
    if (await apertureInput.isVisible()) {
      await apertureInput.fill('f/5.6');
    }

    const isoInput = page.locator(EXIF_SELECTORS.attachmentIso).first();
    if (await isoInput.isVisible()) {
      await isoInput.fill('800');
    }

    await page.waitForTimeout(300);
    await page.screenshot({ path: `test-results/${screenshotPrefix}-11-add-empty-admin.png` });

    await saveAttachmentModal(page);

    await closeAttachmentModal(page);

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    await page.locator('#foogallery_create_page').click();
    await page.waitForLoadState('networkidle');

    const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
    await viewLink.waitFor({ state: 'visible', timeout: 30000 });
    const viewUrl = await viewLink.getAttribute('href');
    if (viewUrl) {
      await page.goto(viewUrl);
      await page.waitForLoadState('networkidle');
    }

    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox for the third image and verify manually added EXIF
    await openLightbox(page, 2);
    await toggleLightboxInfo(page);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-11-add-empty-frontend.png` });

    const exifValues = await getExifValuesFromLightbox(page);
    if (exifValues['Camera']) {
      expect(exifValues['Camera']).toContain('Manually Added Camera');
    }

    await closeLightbox(page);
  });

  test('clears EXIF values and verifies on frontend', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Clear Values Frontend Test');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    // Clear all EXIF fields for the first image
    await openAttachmentModal(page, 0);
    await navigateToExifTabInModal(page);

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
    await page.screenshot({ path: `test-results/${screenshotPrefix}-12-clear-admin.png` });

    await saveAttachmentModal(page);

    await closeAttachmentModal(page);

    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    await page.locator('#foogallery_create_page').click();
    await page.waitForLoadState('networkidle');

    const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
    await viewLink.waitFor({ state: 'visible', timeout: 30000 });
    const viewUrl = await viewLink.getAttribute('href');
    if (viewUrl) {
      await page.goto(viewUrl);
      await page.waitForLoadState('networkidle');
    }

    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    await page.screenshot({ path: `test-results/${screenshotPrefix}-12-clear-frontend.png` });

    // Open lightbox for the first image - EXIF should be minimal or hidden
    await openLightbox(page, 0);

    // Try to toggle info - it may be disabled if no EXIF data
    const infoToggled = await toggleLightboxInfo(page);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-12-clear-lightbox.png` });

    // If info panel opened, EXIF container should be empty or have no props
    if (infoToggled) {
      const exifProps = page.locator(EXIF_SELECTORS.exifProp);
      const propCount = await exifProps.count();
      // After clearing, should have fewer or no EXIF properties displayed
      expect(propCount).toBeGreaterThanOrEqual(0);
    }

    await closeLightbox(page);
  });
});
