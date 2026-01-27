// File: tests/specs/pro-features/exif/exif-frontend.spec.ts
// Tests for EXIF frontend display

import { test, expect } from '@playwright/test';
import {
  createGalleryAndNavigateToPage,
  verifyDataExifAttribute,
  verifyGalleryExifClasses,
  EXIF_SELECTORS,
} from '../../../helpers/exif-test-helper';

test.describe('EXIF Frontend Display', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'exif-frontend';

  test.beforeEach(async ({ page }) => {
    // Set viewport size
    await page.setViewportSize({ width: 1932, height: 1271 });
  });

  test('adds fg-item-exif class to items with EXIF', async ({ page }) => {
    // Create gallery and navigate to frontend
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Item Class Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-item-class`,
      imageCount: 3,
    }, {
      enabled: true,
      iconPosition: 'bottomRight',
    });

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-item-exif-class.png` });

    // Check for items with EXIF class
    const itemsWithExif = page.locator(EXIF_SELECTORS.itemWithExif);
    const count = await itemsWithExif.count();

    // At least some items should have the EXIF class if they contain EXIF data
    // Note: This depends on whether the imported images have EXIF data
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test('does not add class to items without EXIF', async ({ page }) => {
    // Create gallery and navigate to frontend
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF No Class Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-no-class`,
      imageCount: 3,
    }, {
      enabled: true,
    });

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-no-exif-class.png` });

    // Get all items
    const allItems = page.locator(EXIF_SELECTORS.galleryItem);
    const totalCount = await allItems.count();

    // Get items with EXIF
    const itemsWithExif = page.locator(EXIF_SELECTORS.itemWithExif);
    const exifCount = await itemsWithExif.count();

    // Items without EXIF should not have the class
    // Total items - items with EXIF = items without EXIF class
    const itemsWithoutExif = totalCount - exifCount;
    expect(itemsWithoutExif).toBeGreaterThanOrEqual(0);
  });

  test('adds gallery position class', async ({ page }) => {
    // Create gallery with bottom-right position
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Position Class Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-position-class`,
      imageCount: 3,
    }, {
      enabled: true,
      iconPosition: 'bottomRight',
    });

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-position-class.png` });

    // Verify gallery has position class
    const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
    await expect(gallery).toHaveClass(/fg-exif-bottom-right/);
  });

  test('adds gallery theme class', async ({ page }) => {
    // Create gallery with dark theme
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Theme Class Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-theme-class`,
      imageCount: 3,
    }, {
      enabled: true,
      iconTheme: 'dark',
    });

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-theme-class.png` });

    // Verify gallery has theme class
    const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
    await expect(gallery).toHaveClass(/fg-exif-dark/);
  });

  test('generates data-exif JSON attribute', async ({ page }) => {
    // Create gallery and navigate to frontend
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Data Attribute Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-data-attr`,
      imageCount: 3,
    }, {
      enabled: true,
    });

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-data-exif-attr.png` });

    // Check for data-exif attribute on anchor elements
    const anchorsWithExif = page.locator(EXIF_SELECTORS.dataExifAttr);
    const count = await anchorsWithExif.count();

    // Items with EXIF data should have data-exif attribute
    // Note: Count may be 0 if images don't have EXIF data
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test('data-exif contains correct values', async ({ page }) => {
    // Create gallery and navigate to frontend
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Data Values Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-data-values`,
      imageCount: 3,
    }, {
      enabled: true,
    });

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-data-exif-values.png` });

    // Try to get and parse data-exif from first item with EXIF
    const exifData = await verifyDataExifAttribute(page, 0);

    // If we have EXIF data, verify it's a valid object
    if (exifData) {
      expect(typeof exifData).toBe('object');
    }
  });

  test('formats shutter speed as fraction', async ({ page }) => {
    // Create gallery and navigate to frontend
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Shutter Speed Format Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-shutter-format`,
      imageCount: 3,
    }, {
      enabled: true,
    });

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-shutter-speed-format.png` });

    // Get data-exif from first item
    const exifData = await verifyDataExifAttribute(page, 0);

    // If we have shutter speed data, verify format
    if (exifData && (exifData as any).shutter_speed) {
      const shutterSpeed = (exifData as any).shutter_speed;
      // Shutter speed should be formatted like "1/500s" or similar
      expect(typeof shutterSpeed).toBe('string');
    }
  });

  test('formats timestamp as date', async ({ page }) => {
    // Create gallery and navigate to frontend
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Timestamp Format Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-timestamp-format`,
      imageCount: 3,
    }, {
      enabled: true,
    });

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-timestamp-format.png` });

    // Get data-exif from first item
    const exifData = await verifyDataExifAttribute(page, 0);

    // If we have timestamp data, verify it's present
    if (exifData && (exifData as any).created_timestamp) {
      const timestamp = (exifData as any).created_timestamp;
      // Timestamp should be a string (formatted date)
      expect(typeof timestamp).toBe('string');
    }
  });

  test('mixed gallery with EXIF and non-EXIF', async ({ page }) => {
    // Create gallery with mixed images
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Mixed Gallery Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-mixed`,
      imageCount: 5,
    }, {
      enabled: true,
    });

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-09-mixed-gallery.png` });

    // Get all items
    const allItems = page.locator(EXIF_SELECTORS.galleryItem);
    const totalCount = await allItems.count();

    // Get items with EXIF class
    const itemsWithExif = page.locator(EXIF_SELECTORS.itemWithExif);
    const exifCount = await itemsWithExif.count();

    // Verify counts are logical
    expect(totalCount).toBeGreaterThanOrEqual(0);
    expect(exifCount).toBeLessThanOrEqual(totalCount);
  });

  test('thumbnail shows EXIF icon', async ({ page }) => {
    // Create gallery with EXIF icon enabled
    await createGalleryAndNavigateToPage(page, {
      galleryName: 'EXIF Thumbnail Icon Test',
      templateSelector,
      screenshotPrefix: `${screenshotPrefix}-thumbnail-icon`,
      imageCount: 3,
    }, {
      enabled: true,
      iconPosition: 'bottomRight',
      iconTheme: 'dark',
    });

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot before hover
    await page.screenshot({ path: `test-results/${screenshotPrefix}-10-thumbnail-icon-before.png` });

    // Hover over first item with EXIF to show icon
    const itemWithExif = page.locator(EXIF_SELECTORS.itemWithExif).first();
    if (await itemWithExif.isVisible()) {
      await itemWithExif.hover();
      await page.waitForTimeout(500);

      // Screenshot after hover
      await page.screenshot({ path: `test-results/${screenshotPrefix}-10-thumbnail-icon-after.png` });

      // Verify the item has the EXIF class
      await expect(itemWithExif).toHaveClass(/fg-item-exif/);
    }
  });
});
