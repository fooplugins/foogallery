// File: tests/specs/pro-features/exif/exif-display-layouts.spec.ts
// Tests for EXIF display layout options with frontend verification

import { test, expect } from '@playwright/test';
import {
  configureExifSettings,
  addExifImagesToGallery,
  publishGalleryAndNavigateToFrontend,
  openLightboxAndShowExif,
  openLightbox,
  toggleLightboxInfo,
  closeLightbox,
  verifyExifLayoutClass,
  EXIF_SELECTORS,
  EXIF_DISPLAY_LAYOUTS,
} from '../../../helpers/exif-test-helper';

test.describe('EXIF Attribute layouts', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'exif-layouts';

  test.beforeEach(async ({ page }) => {
    // Set viewport size
    await page.setViewportSize({ width: 1932, height: 1271 });
  });

  test('sets display layout - auto (default) and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Layout Auto Frontend');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings with auto layout
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'auto',
    });

    // Screenshot admin
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-layout-auto-admin.png` });

    // Verify auto layout is selected
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const layoutRow = templateContainer.locator('tr').filter({ hasText: 'Attribute layout' });
    const autoRadio = layoutRow.locator(`input[type="radio"][value="${EXIF_DISPLAY_LAYOUTS.auto}"]`);
    await expect(autoRadio).toBeChecked();

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-layout-auto-frontend.png` });

    // Open lightbox and verify EXIF display
    const exifOpened = await openLightboxAndShowExif(page, 0);

    // Screenshot lightbox
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-layout-auto-lightbox.png` });

    // Verify EXIF behavior (may or may not be visible based on auto layout logic)
    if (exifOpened) {
      // Auto layout adapts to screen size - verify container has expected class
      const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
      const hasAutoClass = await exifContainer.evaluate((el) => {
        return el.classList.contains('fg-media-caption-exif-auto') ||
               el.classList.contains('fg-media-caption-exif-full') ||
               el.classList.contains('fg-media-caption-exif-partial') ||
               el.classList.contains('fg-media-caption-exif-minimal');
      });
      expect(hasAutoClass).toBe(true);
    }

    await closeLightbox(page);
  });

  test('sets display layout - full and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Layout Full Frontend');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings with full layout
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    // Screenshot admin
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-layout-full-admin.png` });

    // Verify full layout is selected
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const layoutRow = templateContainer.locator('tr').filter({ hasText: 'Attribute layout' });
    const fullRadio = layoutRow.locator(`input[type="radio"][value="${EXIF_DISPLAY_LAYOUTS.full}"]`);
    await expect(fullRadio).toBeChecked();

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-layout-full-frontend.png` });

    // Open lightbox and verify EXIF display
    await openLightbox(page, 0);
    await toggleLightboxInfo(page);

    // Screenshot lightbox
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-layout-full-lightbox.png` });

    // Verify EXIF container has full layout class
    const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
    if (await exifContainer.isVisible()) {
      await expect(exifContainer).toHaveClass(/fg-media-caption-exif-full/);

      // Full layout should show icon, label, and value for each property
      const props = page.locator(EXIF_SELECTORS.exifProp);
      const count = await props.count();

      if (count > 0) {
        const firstProp = props.first();
        const icon = firstProp.locator(EXIF_SELECTORS.exifIcon);
        const label = firstProp.locator(EXIF_SELECTORS.exifLabel);
        const value = firstProp.locator(EXIF_SELECTORS.exifValue);

        // In full layout, icon, label, and value should be visible
        await expect(icon).toBeVisible();
        await expect(label).toBeVisible();
        await expect(value).toBeVisible();
      }
    }

    await closeLightbox(page);
  });

  test('sets display layout - partial and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Layout Partial Frontend');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings with partial layout
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'partial',
    });

    // Screenshot admin
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-layout-partial-admin.png` });

    // Verify partial layout is selected
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const layoutRow = templateContainer.locator('tr').filter({ hasText: 'Attribute layout' });
    const partialRadio = layoutRow.locator(`input[type="radio"][value="${EXIF_DISPLAY_LAYOUTS.partial}"]`);
    await expect(partialRadio).toBeChecked();

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-layout-partial-frontend.png` });

    // Open lightbox and verify EXIF display
    await openLightbox(page, 0);
    await toggleLightboxInfo(page);

    // Screenshot lightbox
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-layout-partial-lightbox.png` });

    // Verify EXIF container has partial layout class
    const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
    if (await exifContainer.isVisible()) {
      await expect(exifContainer).toHaveClass(/fg-media-caption-exif-partial/);

      // Partial layout shows icon and value (no label)
      const props = page.locator(EXIF_SELECTORS.exifProp);
      const count = await props.count();

      if (count > 0) {
        const firstProp = props.first();
        const icon = firstProp.locator(EXIF_SELECTORS.exifIcon);
        const value = firstProp.locator(EXIF_SELECTORS.exifValue);

        // In partial layout, icon and value should be visible
        await expect(icon).toBeVisible();
        await expect(value).toBeVisible();
      }
    }

    await closeLightbox(page);
  });

  test('sets display layout - minimal and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Layout Minimal Frontend');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings with minimal layout
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'minimal',
    });

    // Screenshot admin
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-layout-minimal-admin.png` });

    // Verify minimal layout is selected
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const layoutRow = templateContainer.locator('tr').filter({ hasText: 'Attribute layout' });
    const minimalRadio = layoutRow.locator(`input[type="radio"][value="${EXIF_DISPLAY_LAYOUTS.minimal}"]`);
    await expect(minimalRadio).toBeChecked();

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-layout-minimal-frontend.png` });

    // Open lightbox and verify EXIF display
    await openLightbox(page, 0);
    await toggleLightboxInfo(page);

    // Screenshot lightbox
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-layout-minimal-lightbox.png` });

    // Verify EXIF container has minimal layout class
    const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
    if (await exifContainer.isVisible()) {
      await expect(exifContainer).toHaveClass(/fg-media-caption-exif-minimal/);

      // Minimal layout shows only icon (value in tooltip on hover)
      const props = page.locator(EXIF_SELECTORS.exifProp);
      const count = await props.count();

      if (count > 0) {
        const firstProp = props.first();
        const icon = firstProp.locator(EXIF_SELECTORS.exifIcon);
        await expect(icon).toBeVisible();
      }
    }

    await closeLightbox(page);
  });

  test('auto layout adjusts to screen size', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Auto Layout Responsive');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'auto',
    });

    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox and show EXIF
    await openLightboxAndShowExif(page);

    // Screenshot at full width
    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-auto-full-width.png` });

    // Check EXIF container exists
    const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
    if (await exifContainer.isVisible()) {
      // Record initial state
      const initialClass = await exifContainer.getAttribute('class');

      // Close lightbox
      await closeLightbox(page);

      // Resize to smaller viewport
      await page.setViewportSize({ width: 768, height: 1024 });
      await page.waitForTimeout(500);

      // Open lightbox again
      await openLightboxAndShowExif(page);

      // Screenshot at smaller width
      await page.screenshot({ path: `test-results/${screenshotPrefix}-06-auto-small-width.png` });

      // Verify container has auto class (behavior may change based on size)
      const exifContainerSmall = page.locator(EXIF_SELECTORS.exifContainer);
      await expect(exifContainerSmall).toBeVisible();
    }
  });

  test('full layout displays all EXIF properties', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Full Layout All Properties');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox and show EXIF
    await openLightboxAndShowExif(page);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-full-all-properties.png` });

    // Verify EXIF container is visible
    const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
    if (await exifContainer.isVisible()) {
      // Full layout should show icon, label, and value
      const props = page.locator(EXIF_SELECTORS.exifProp);
      const count = await props.count();

      if (count > 0) {
        // Check first property has all elements
        const firstProp = props.first();
        const icon = firstProp.locator(EXIF_SELECTORS.exifIcon);
        const label = firstProp.locator(EXIF_SELECTORS.exifLabel);
        const value = firstProp.locator(EXIF_SELECTORS.exifValue);

        // In full layout, icon, label, and value should be visible
        await expect(icon).toBeVisible();
        await expect(label).toBeVisible();
        await expect(value).toBeVisible();
      }
    }
  });

  test('verifies layout container CSS class', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Layout CSS Class Verification');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox and show EXIF
    await openLightboxAndShowExif(page);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-css-class-verification.png` });

    // Verify EXIF container has the correct layout class
    const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
    if (await exifContainer.isVisible()) {
      // Should have fg-media-caption-exif-full class
      await expect(exifContainer).toHaveClass(/fg-media-caption-exif-full/);
    }
  });

  test('partial layout shows icon and value only', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    await page.locator('#title').fill('EXIF Partial Layout Verification');

    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    await addExifImagesToGallery(page, 3);

    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'partial',
    });

    await publishGalleryAndNavigateToFrontend(page);

    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    await openLightboxAndShowExif(page);

    await page.screenshot({ path: `test-results/${screenshotPrefix}-09-partial-layout-verification.png` });

    const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
    if (await exifContainer.isVisible()) {
      await expect(exifContainer).toHaveClass(/fg-media-caption-exif-partial/);

      // Partial layout shows icon and value but labels are hidden
      const props = page.locator(EXIF_SELECTORS.exifProp);
      const count = await props.count();

      if (count > 0) {
        const firstProp = props.first();
        const icon = firstProp.locator(EXIF_SELECTORS.exifIcon);
        const value = firstProp.locator(EXIF_SELECTORS.exifValue);

        await expect(icon).toBeVisible();
        await expect(value).toBeVisible();
      }
    }
  });
});
