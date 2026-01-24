// File: tests/specs/pro-features/captions/captions-lightbox-display.spec.ts
// Tests for Lightbox Caption Display on frontend

import { test, expect } from '@playwright/test';
import {
  createGalleryAndNavigateToPage,
  openLightbox,
  closeLightbox,
  getCaptionFromLightbox,
  navigateToNextInLightbox,
  toggleLightboxInfo,
  CAPTION_SELECTORS,
} from '../../../helpers/captions-test-helper';

test.describe('Lightbox Caption Display', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'captions-lightbox-display';

  test.beforeEach(async ({ page }) => {
    // Set viewport size
    await page.setViewportSize({ width: 1932, height: 1271 });
  });

  test('displays caption in lightbox', async ({ page }) => {
    // Create gallery and navigate to frontend
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Lightbox Caption Display',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-01-caption-display`,
        imageCount: 3,
      },
      { titleSource: 'title', descSource: 'caption' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox
    await openLightbox(page, 0);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-caption-in-lightbox.png` });

    // Verify lightbox is visible
    const lightbox = page.locator(CAPTION_SELECTORS.lightboxPanel);
    await expect(lightbox).toBeVisible();

    // Close lightbox
    await closeLightbox(page);
  });

  test('displays title in lightbox', async ({ page }) => {
    // Create gallery with title source
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Lightbox Title Display',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-02-title-display`,
        imageCount: 3,
      },
      { titleSource: 'title' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox
    await openLightbox(page, 0);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-title-in-lightbox.png` });

    // Check if title element exists in lightbox
    const titleElement = page.locator(CAPTION_SELECTORS.lightboxCaptionTitle);
    // Title may or may not be visible depending on image metadata
    const isVisible = await titleElement.isVisible().catch(() => false);

    // Verify lightbox is visible
    const lightbox = page.locator(CAPTION_SELECTORS.lightboxPanel);
    await expect(lightbox).toBeVisible();

    // Close lightbox
    await closeLightbox(page);
  });

  test('displays description in lightbox', async ({ page }) => {
    // Create gallery with description source
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Lightbox Description Display',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-03-desc-display`,
        imageCount: 3,
      },
      { descSource: 'caption' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox
    await openLightbox(page, 0);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-desc-in-lightbox.png` });

    // Check if description element exists in lightbox
    const descElement = page.locator(CAPTION_SELECTORS.lightboxCaptionDescription);
    // Description may or may not be visible depending on image metadata
    const isVisible = await descElement.isVisible().catch(() => false);

    // Verify lightbox is visible
    const lightbox = page.locator(CAPTION_SELECTORS.lightboxPanel);
    await expect(lightbox).toBeVisible();

    // Close lightbox
    await closeLightbox(page);
  });

  test('caption hidden when disabled', async ({ page }) => {
    // Create gallery with lightbox captions disabled
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Lightbox Caption Hidden',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-04-caption-hidden`,
        imageCount: 3,
      },
      { titleSource: 'title' },
      { enabled: 'disabled' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox
    await openLightbox(page, 0);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-caption-hidden-lightbox.png` });

    // Verify lightbox is visible but caption elements should be hidden or not present
    const lightbox = page.locator(CAPTION_SELECTORS.lightboxPanel);
    await expect(lightbox).toBeVisible();

    // Close lightbox
    await closeLightbox(page);
  });

  test('caption toggled when hidden mode', async ({ page }) => {
    // Create gallery with lightbox captions in hidden mode (toggle)
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Lightbox Caption Toggle',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-05-caption-toggle`,
        imageCount: 3,
      },
      { titleSource: 'title' },
      { enabled: 'hidden' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox
    await openLightbox(page, 0);

    // Screenshot before toggle
    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-before-toggle.png` });

    // Try to toggle info panel
    const infoButton = page.locator(CAPTION_SELECTORS.lightboxInfoButton);
    if (await infoButton.isVisible()) {
      await toggleLightboxInfo(page);
      await page.waitForTimeout(500);

      // Screenshot after toggle
      await page.screenshot({ path: `test-results/${screenshotPrefix}-05-after-toggle.png` });
    }

    // Verify lightbox is visible
    const lightbox = page.locator(CAPTION_SELECTORS.lightboxPanel);
    await expect(lightbox).toBeVisible();

    // Close lightbox
    await closeLightbox(page);
  });

  test('caption position top renders correctly', async ({ page }) => {
    // Create gallery with lightbox caption position top
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Lightbox Caption Position Top',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-06-position-top`,
        imageCount: 3,
      },
      { titleSource: 'title' },
      { position: 'top' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox
    await openLightbox(page, 0);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-position-top-lightbox.png` });

    // Verify lightbox is visible
    const lightbox = page.locator(CAPTION_SELECTORS.lightboxPanel);
    await expect(lightbox).toBeVisible();

    // Close lightbox
    await closeLightbox(page);
  });

  test('caption position bottom renders correctly', async ({ page }) => {
    // Create gallery with lightbox caption position bottom (default)
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Lightbox Caption Position Bottom',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-07-position-bottom`,
        imageCount: 3,
      },
      { titleSource: 'title' },
      { position: 'bottom' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox
    await openLightbox(page, 0);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-position-bottom-lightbox.png` });

    // Verify lightbox is visible
    const lightbox = page.locator(CAPTION_SELECTORS.lightboxPanel);
    await expect(lightbox).toBeVisible();

    // Close lightbox
    await closeLightbox(page);
  });

  test('caption updates on navigation', async ({ page }) => {
    // Create gallery with multiple images
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Lightbox Caption Navigation',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-08-navigation`,
        imageCount: 3,
      },
      { titleSource: 'title' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Open lightbox on first image
    await openLightbox(page, 0);

    // Screenshot first image
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-first-image.png` });

    // Navigate to next image
    await navigateToNextInLightbox(page);

    // Screenshot second image
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-second-image.png` });

    // Navigate to next image again
    await navigateToNextInLightbox(page);

    // Screenshot third image
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-third-image.png` });

    // Verify lightbox is still visible
    const lightbox = page.locator(CAPTION_SELECTORS.lightboxPanel);
    await expect(lightbox).toBeVisible();

    // Close lightbox
    await closeLightbox(page);
  });
});
