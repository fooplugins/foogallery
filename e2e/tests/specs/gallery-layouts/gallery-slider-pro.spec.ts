// File: tests/specs/gallery-slider-pro.spec.ts
// Test for creating a Slider PRO gallery layout with unique panel and thumbnail navigation

import { test, expect } from '@playwright/test';
import { createGalleryAndNavigateToPage } from '../../helpers/gallery-test-helper';

test.describe('Gallery - Slider PRO Layout', () => {
  test('create gallery, add images, publish, and test slider navigation', async ({ page }) => {
    // Create gallery and navigate to page
    await createGalleryAndNavigateToPage(page, {
      layoutName: 'Slider PRO',
      templateSelector: 'slider',
      screenshotPrefix: 'slider-pro',
      imageCount: 4,
    });

    // Slider PRO opens directly in panel view
    // Wait for the panel content to be visible
    const panelContent = page.locator('div.fg-panel-content');
    await panelContent.waitFor({ state: 'visible', timeout: 15000 });

    // Click on the main image in the panel
    const panelImage = page.locator('div.fg-panel-content img').first();
    await panelImage.waitFor({ state: 'visible', timeout: 10000 });
    await panelImage.click();

    // Screenshot: Panel view
    await page.screenshot({ path: 'test-results/slider-pro-07-panel-view.png' });

    // Navigate using next/prev buttons
    await page.locator('button.fg-panel-button-next').click();
    await page.locator('button.fg-panel-button-next').click();
    await page.locator('button.fg-panel-button-next').click();
    await page.locator('button.fg-panel-button-next').click();

    // Screenshot: After forward navigation
    await page.screenshot({ path: 'test-results/slider-pro-08-forward-nav.png' });

    // Navigate back
    await page.locator('button.fg-panel-button-prev > svg').click();
    await page.locator('button.fg-panel-button-prev > svg').click();
    await page.locator('button.fg-panel-button-prev > svg').click();
    await page.locator('button.fg-panel-button-prev > svg').click();

    // Screenshot: After backward navigation
    await page.screenshot({ path: 'test-results/slider-pro-09-backward-nav.png' });

    // Test thumbnail navigation (unique to Slider PRO)
    // Click on thumbnail figures directly (overlays only visible on hover)
    const thumbs = page.locator('.fg-panel-thumbs figure.fg-panel-thumb');
    await thumbs.first().waitFor({ state: 'visible', timeout: 10000 });

    // Click 3rd thumbnail
    await thumbs.nth(2).click({ force: true });

    // Screenshot: After thumb click
    await page.screenshot({ path: 'test-results/slider-pro-10-thumb-nav-3.png' });

    // Click 2nd thumbnail
    await thumbs.nth(1).click({ force: true });

    // Screenshot: After thumb click
    await page.screenshot({ path: 'test-results/slider-pro-11-thumb-nav-2.png' });

    // Click 1st thumbnail
    await thumbs.nth(0).click({ force: true });

    // Screenshot: After thumb click
    await page.screenshot({ path: 'test-results/slider-pro-12-thumb-nav-1.png' });

    // Click maximize button (unique to Slider PRO)
    await page.locator('button.fg-panel-button-maximize > svg').click();

    // Screenshot: Maximized
    await page.screenshot({ path: 'test-results/slider-pro-13-maximized.png' });

    // Navigate in maximized view
    await page.locator('button.fg-panel-button-next').click();
    await page.locator('button.fg-panel-button-next').click();
    await page.locator('button.fg-panel-button-next').click();

    // Navigate back
    await page.locator('button.fg-panel-button-prev').click();
    await page.locator('button.fg-panel-button-prev').click();
    await page.locator('button.fg-panel-button-prev').click();

    // Screenshot: After maximized navigation
    await page.screenshot({ path: 'test-results/slider-pro-14-maximized-nav.png' });

    // Click maximize again to restore
    await page.locator('button.fg-panel-button-maximize > svg').click();

    // Screenshot: Final state (restored)
    await page.screenshot({ path: 'test-results/slider-pro-15-final.png' });
  });
});
