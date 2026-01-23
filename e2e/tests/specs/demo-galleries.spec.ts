// File: tests/specs/demo-galleries.spec.ts
// Test for creating FooGallery demo galleries and populating media library

import { test, expect } from '@playwright/test';

test.describe('FooGallery Demo Galleries', () => {
  test('create demo galleries and images', async ({ page }) => {
    // Set viewport size
    await page.setViewportSize({
      width: 1932,
      height: 1271
    });

    // Navigate to WordPress admin dashboard
    await page.goto('/wp-admin/index.php');
    await page.waitForLoadState('domcontentloaded');

    // Click on FooGallery menu in sidebar
    await page.locator('#menu-posts-foogallery div.wp-menu-name').click();
    await page.waitForLoadState('domcontentloaded');

    // Verify we're on the FooGallery list page
    await expect(page).toHaveURL(/post_type=foogallery/);

    // Screenshot: FooGallery list page
    await page.screenshot({ path: 'test-results/demo-galleries-01-foogallery-list.png' });

    // Click on Help submenu (9th item in FooGallery menu)
    await page.locator('#menu-posts-foogallery li:nth-of-type(9) > a').click();
    await page.waitForLoadState('domcontentloaded');

    // Verify we're on the Help page
    await expect(page).toHaveURL(/page=foogallery-help/);

    // Screenshot: Help page before creating demos
    await page.screenshot({ path: 'test-results/demo-galleries-02-help-page.png' });

    // Click the "Create Demo Content" button
    // This button creates sample galleries and downloads images to media library
    await page.locator('button:nth-of-type(1) > span.fgah-create-demos-text').click();

    // Wait for demo content creation to complete
    // This process downloads images from external sources, so it may take a while
    await page.waitForLoadState('networkidle', { timeout: 120000 });

    // Wait for the success message to appear
    // After demo content is created, a success message with "Done!" button appears
    const doneButton = page.locator('button:has-text("Done!"), a:has-text("Done!"), .fgah-done-button').first();
    await expect(doneButton).toBeVisible({ timeout: 120000 });

    // Screenshot: Demo content created successfully
    await page.screenshot({ path: 'test-results/demo-galleries-03-demos-created.png' });

    // Click the Done button to go back to galleries
    await doneButton.click();
    await page.waitForLoadState('networkidle');

    // If we're not redirected, navigate to galleries list manually
    const currentUrl = page.url();
    if (!currentUrl.includes('post_type=foogallery') || currentUrl.includes('foogallery-help')) {
      await page.goto('/wp-admin/edit.php?post_type=foogallery');
      await page.waitForLoadState('networkidle');
    }

    // Verify we're on the galleries list
    await expect(page).toHaveURL(/post_type=foogallery/);

    // Verify that demo galleries were created by checking the list isn't empty
    const galleryRows = page.locator('table.wp-list-table tbody tr');
    await expect(galleryRows.first()).toBeVisible({ timeout: 10000 });

    // Screenshot: Final galleries list with demo galleries
    await page.screenshot({ path: 'test-results/demo-galleries-04-final-galleries-list.png' });
  });
});
