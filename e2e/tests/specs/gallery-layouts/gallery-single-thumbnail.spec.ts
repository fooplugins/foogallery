// File: tests/specs/gallery-single-thumbnail.spec.ts
// Test for creating a Single Thumbnail gallery layout

import { test, expect } from '@playwright/test';
import { createGalleryAndNavigateToPage } from '../../helpers/gallery-test-helper';

test.describe('Gallery - Single Thumbnail Layout', () => {
  test('create gallery, add images, publish, and test lightbox', async ({ page }) => {
    // Create gallery and navigate to page
    await createGalleryAndNavigateToPage(page, {
      layoutName: 'Single Thumbnail',
      templateSelector: 'thumbnail',
      screenshotPrefix: 'single-thumbnail',
      imageCount: 5,
    });

    // Wait for gallery to be visible and click thumbnail to open lightbox
    const galleryThumb = page.locator('.fg-item a.fg-thumb').first();
    await galleryThumb.waitFor({ state: 'visible', timeout: 15000 });
    await galleryThumb.click({ force: true });

    // Wait for lightbox to open
    await page.waitForSelector('.fg-panel-content', { state: 'visible', timeout: 10000 });

    // Screenshot: Lightbox opened
    await page.screenshot({ path: 'test-results/single-thumbnail-07-lightbox-open.png' });

    // Navigate in lightbox - next
    await page.locator('button.fg-panel-button-next').click();
    await page.locator('button.fg-panel-button-next').click();
    await page.locator('button.fg-panel-button-next').click();
    await page.locator('button.fg-panel-button-next').click();

    // Screenshot: After forward navigation
    await page.screenshot({ path: 'test-results/single-thumbnail-08-lightbox-forward.png' });

    // Navigate in lightbox - prev
    await page.locator('button.fg-panel-button-prev').click();
    await page.locator('button.fg-panel-button-prev path').click();
    await page.locator('button.fg-panel-button-prev > svg').click();
    await page.locator('button.fg-panel-button-prev > svg').click();

    // Screenshot: After backward navigation
    await page.screenshot({ path: 'test-results/single-thumbnail-09-lightbox-backward.png' });

    // Expand fullscreen
    await page.locator('svg.fg-icon-expand').click();

    // Screenshot: Fullscreen
    await page.screenshot({ path: 'test-results/single-thumbnail-10-fullscreen.png' });

    // Shrink back
    await page.locator('svg.fg-icon-shrink').click();

    // Close lightbox - click the button directly with force
    await page.locator('button.fg-panel-button-close').click({ force: true });

    // Wait for lightbox to close
    await page.waitForSelector('.fg-panel-content', { state: 'hidden', timeout: 20000 });

    // Screenshot: Final state
    await page.screenshot({ path: 'test-results/single-thumbnail-11-final.png' });
  });
});
