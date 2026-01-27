// File: tests/specs/gallery-image-viewer.spec.ts
// Test for creating an Image Viewer gallery layout with unique viewer navigation

import { test, expect } from '@playwright/test';
import { createGalleryAndNavigateToPage } from '../../helpers/gallery-test-helper';

test.describe('Gallery - Image Viewer Layout', () => {
  test('create gallery, add images, publish, and test viewer navigation', async ({ page }) => {
    // Create gallery and navigate to page
    await createGalleryAndNavigateToPage(page, {
      layoutName: 'Image Viewer',
      templateSelector: 'image-viewer',
      screenshotPrefix: 'image-viewer',
      imageCount: 4,
    });

    // Wait for gallery to be visible
    const galleryThumb = page.locator('.fg-item a.fg-thumb').first();
    await galleryThumb.waitFor({ state: 'visible', timeout: 15000 });

    // Click thumbnail to open lightbox
    await galleryThumb.click({ force: true });

    // Wait for lightbox to open
    await page.waitForSelector('.fg-panel-content', { state: 'visible', timeout: 10000 });

    // Screenshot: Lightbox opened
    await page.screenshot({ path: 'test-results/image-viewer-07-lightbox-open.png' });

    // Navigate in lightbox
    await page.locator('button.fg-panel-button-next path').click();
    await page.locator('button.fg-panel-button-next > svg').click();
    await page.locator('button.fg-panel-button-next > svg').click();

    // Navigate back
    await page.locator('button.fg-panel-button-prev > svg').click();
    await page.locator('button.fg-panel-button-prev > svg').click();
    await page.locator('button.fg-panel-button-prev > svg').click();

    // Screenshot: After lightbox navigation
    await page.screenshot({ path: 'test-results/image-viewer-08-lightbox-nav.png' });

    // Toggle fullscreen
    await page.locator('button.fg-panel-button-fullscreen').click();

    // Screenshot: Fullscreen
    await page.screenshot({ path: 'test-results/image-viewer-09-fullscreen.png' });

    // Shrink back
    await page.locator('svg.fg-icon-shrink').click();

    // Close lightbox - use Escape key which is more reliable
    await page.keyboard.press('Escape');

    // Wait for lightbox to close with a shorter timeout
    await page.waitForSelector('.fg-panel-content', { state: 'hidden', timeout: 15000 });

    // Screenshot: After lightbox closed
    await page.screenshot({ path: 'test-results/image-viewer-10-lightbox-closed.png' });

    // Test the unique Image Viewer navigation (fiv-next/fiv-prev)
    const fivNext = page.locator('button.fiv-next > span');
    await fivNext.waitFor({ state: 'visible', timeout: 10000 });

    // Navigate using viewer controls
    await fivNext.click();
    await fivNext.click();
    await fivNext.click();
    await fivNext.click();

    // Screenshot: After viewer forward navigation
    await page.screenshot({ path: 'test-results/image-viewer-11-viewer-forward.png' });

    // Navigate back using viewer controls
    await page.locator('button.fiv-prev > span').click();
    await page.locator('button.fiv-prev > span').click();
    await page.locator('button.fiv-prev > span').click();
    await page.locator('button.fiv-prev > span').click();

    // Screenshot: Final state
    await page.screenshot({ path: 'test-results/image-viewer-12-final.png' });
  });
});
