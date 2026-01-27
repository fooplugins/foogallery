// File: tests/specs/gallery-carousel.spec.ts
// Test for creating a Carousel gallery layout with unique carousel navigation

import { test, expect } from '@playwright/test';
import { createGalleryAndNavigateToPage } from '../../helpers/gallery-test-helper';

test.describe('Gallery - Carousel Layout', () => {
  test('create gallery, add images, publish, and test carousel navigation', async ({ page }) => {
    // Create gallery and navigate to page
    await createGalleryAndNavigateToPage(page, {
      layoutName: 'Carousel',
      templateSelector: 'carousel',
      screenshotPrefix: 'carousel',
      imageCount: 5,
    });

    // Wait for carousel to be visible
    const carouselNext = page.locator('button.fg-carousel-next');
    await carouselNext.waitFor({ state: 'visible', timeout: 15000 });

    // Test carousel navigation - click next 5 times
    for (let i = 0; i < 5; i++) {
      await page.locator('button.fg-carousel-next path').click();
      await page.waitForTimeout(300); // Brief pause for animation
    }

    // Screenshot: After navigating forward
    await page.screenshot({ path: 'test-results/carousel-07-navigated-forward.png' });

    // Test carousel navigation - click prev 5 times
    for (let i = 0; i < 5; i++) {
      await page.locator('button.fg-carousel-prev path').click();
      await page.waitForTimeout(300);
    }

    // Screenshot: After navigating backward
    await page.screenshot({ path: 'test-results/carousel-08-navigated-backward.png' });

    // Click on the active item to open lightbox (use .fg-thumb which is the clickable anchor)
    const activeItem = page.locator('div.fg-item-active a.fg-thumb');
    await activeItem.waitFor({ state: 'visible', timeout: 10000 });
    await activeItem.click({ force: true });

    // Wait for lightbox to open
    await page.waitForSelector('.fg-panel-content', { state: 'visible', timeout: 10000 });

    // Screenshot: Lightbox opened
    await page.screenshot({ path: 'test-results/carousel-09-lightbox-open.png' });

    // Navigate in lightbox
    await page.locator('button.fg-panel-button-next > svg').click();
    await page.locator('button.fg-panel-button-prev').click();
    await page.locator('button.fg-panel-button-prev path').click();

    // Screenshot: Lightbox navigation
    await page.screenshot({ path: 'test-results/carousel-10-lightbox-nav.png' });

    // Expand fullscreen
    await page.locator('svg.fg-icon-expand').click();

    // Screenshot: Fullscreen
    await page.screenshot({ path: 'test-results/carousel-11-fullscreen.png' });

    // Shrink back
    await page.locator('svg.fg-icon-shrink').click();

    // Close lightbox - click the button directly with force
    await page.locator('button.fg-panel-button-close').click({ force: true });

    // Wait for lightbox to close
    await page.waitForSelector('.fg-panel-content', { state: 'hidden', timeout: 20000 });

    // Screenshot: Final state
    await page.screenshot({ path: 'test-results/carousel-12-final.png' });
  });
});
