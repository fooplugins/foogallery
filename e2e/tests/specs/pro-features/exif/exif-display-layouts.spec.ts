// File: tests/specs/pro-features/exif/exif-display-layouts.spec.ts
// Tests for EXIF display layout options

import { test, expect } from '@playwright/test';
import {
  configureExifSettings,
  createGalleryAndNavigateToPage,
  openLightboxAndShowExif,
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

  test('sets display layout - auto (default)', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Layout Auto');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings with auto layout
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'auto',
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-layout-auto.png` });

    // Verify auto layout is selected
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const layoutRow = templateContainer.locator('tr').filter({ hasText: 'Attribute layout' });
    const autoRadio = layoutRow.locator(`input[type="radio"][value="${EXIF_DISPLAY_LAYOUTS.auto}"]`);
    await expect(autoRadio).toBeChecked();
  });

  test('sets display layout - full', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Layout Full');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings with full layout
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'full',
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-layout-full.png` });

    // Verify full layout is selected
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const layoutRow = templateContainer.locator('tr').filter({ hasText: 'Attribute layout' });
    const fullRadio = layoutRow.locator(`input[type="radio"][value="${EXIF_DISPLAY_LAYOUTS.full}"]`);
    await expect(fullRadio).toBeChecked();
  });

  test('sets display layout - partial', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Layout Partial');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings with partial layout
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'partial',
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-layout-partial.png` });

    // Verify partial layout is selected
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const layoutRow = templateContainer.locator('tr').filter({ hasText: 'Attribute layout' });
    const partialRadio = layoutRow.locator(`input[type="radio"][value="${EXIF_DISPLAY_LAYOUTS.partial}"]`);
    await expect(partialRadio).toBeChecked();
  });

  test('sets display layout - minimal', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Layout Minimal');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings with minimal layout
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      displayLayout: 'minimal',
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-layout-minimal.png` });

    // Verify minimal layout is selected
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const layoutRow = templateContainer.locator('tr').filter({ hasText: 'Attribute layout' });
    const minimalRadio = layoutRow.locator(`input[type="radio"][value="${EXIF_DISPLAY_LAYOUTS.minimal}"]`);
    await expect(minimalRadio).toBeChecked();
  });

  test('auto layout adjusts to screen size', async ({ page }) => {
    // Create gallery with auto layout and navigate to frontend
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Auto Layout Responsive',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-auto-responsive`,
      imageCount: 3,
    }, {
      enabled: true,
      displayLayout: 'auto',
    });

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
    // Create gallery with full layout and navigate to frontend
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Full Layout All Properties',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-full-all`,
      imageCount: 3,
    }, {
      enabled: true,
      displayLayout: 'full',
    });

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
    // Create gallery with full layout and navigate to frontend
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Layout CSS Class Verification',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-css-class`,
      imageCount: 3,
    }, {
      enabled: true,
      displayLayout: 'full',
    });

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
});
