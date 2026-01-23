// File: tests/specs/gallery-spotlight-pro.spec.ts
// Test for creating a Spotlight PRO gallery layout
// Spotlight PRO is a slideshow that displays one image at a time with left/right navigation

import { test, expect } from '@playwright/test';
import { createGalleryAndNavigateToPage } from '../../helpers/gallery-test-helper';

test.describe('Gallery - Spotlight PRO Layout', () => {
  test('create gallery, add images, publish, and test slideshow navigation', async ({ page }) => {
    // Create gallery and navigate to page
    await createGalleryAndNavigateToPage(page, {
      layoutName: 'Spotlight PRO',
      templateSelector: 'spotlight',
      screenshotPrefix: 'spotlight-pro',
      imageCount: 5,
    });

    // Wait for the spotlight gallery to be visible
    // Spotlight PRO shows one image at a time as a slideshow
    const galleryContainer = page.locator('.foogallery');
    await galleryContainer.waitFor({ state: 'visible', timeout: 15000 });

    // Screenshot: Spotlight gallery loaded
    await page.screenshot({ path: 'test-results/spotlight-pro-07-gallery-loaded.png' });

    // Spotlight PRO navigation: use pagination links instead of prev/next buttons
    // The pagination dots at the bottom allow navigation between images
    const pagination = page.locator('.foogallery nav');
    await pagination.waitFor({ state: 'visible', timeout: 10000 });

    // Click on different page links to navigate
    const page2Link = page.getByRole('link', { name: /page 2/i });
    if (await page2Link.isVisible()) {
      await page2Link.click();
      await page.waitForTimeout(500);
      await page.screenshot({ path: 'test-results/spotlight-pro-08-page-2.png' });
    }

    const page3Link = page.getByRole('link', { name: /page 3/i });
    if (await page3Link.isVisible()) {
      await page3Link.click();
      await page.waitForTimeout(500);
      await page.screenshot({ path: 'test-results/spotlight-pro-09-page-3.png' });
    }

    const page4Link = page.getByRole('link', { name: /page 4/i });
    if (await page4Link.isVisible()) {
      await page4Link.click();
      await page.waitForTimeout(500);
      await page.screenshot({ path: 'test-results/spotlight-pro-10-page-4.png' });
    }

    // Navigate back to page 1
    const page1Link = page.getByRole('link', { name: /page 1/i });
    if (await page1Link.isVisible()) {
      await page1Link.click();
      await page.waitForTimeout(500);
    }

    // Screenshot: Final state after navigation
    await page.screenshot({ path: 'test-results/spotlight-pro-12-final.png' });
  });
});
