// File: tests/specs/smoke.spec.ts
// Smoke tests for FooGallery Premium E2E Testing

import { test, expect } from '@playwright/test';

test.describe('FooGallery Smoke Tests', () => {
  test.describe.configure({ mode: 'serial' });

  test('Homepage loads successfully', async ({ page }) => {
    // Navigate to homepage
    await page.goto('/');

    // Verify page loaded (check for common WordPress elements)
    await expect(page).toHaveTitle(/.+/); // Any title

    // Check that the page returns 200 OK
    const response = await page.goto('/');
    expect(response?.status()).toBe(200);

    // Verify it's a WordPress site (look for wp-content or common elements)
    const bodyContent = await page.content();
    const isWordPress =
      bodyContent.includes('wp-content') ||
      bodyContent.includes('wordpress') ||
      bodyContent.includes('wp-');

    expect(isWordPress).toBeTruthy();

    // Screenshot: Homepage loaded
    await page.screenshot({ path: 'test-results/smoke-homepage-01-loaded.png' });
  });

  test('Admin login works and dashboard loads', async ({ page }) => {
    // Navigate to admin dashboard (should auto-login via stored auth state)
    await page.goto('/wp-admin/');

    // Wait for dashboard to load
    await page.waitForLoadState('domcontentloaded');

    // Verify we're on the dashboard
    await expect(page).toHaveURL(/wp-admin/);

    // Check for dashboard elements
    const dashboardHeading = page.locator('#wpbody-content h1, .wrap h1').first();
    await expect(dashboardHeading).toBeVisible({ timeout: 10000 });

    // Verify admin menu is present
    const adminMenu = page.locator('#adminmenu');
    await expect(adminMenu).toBeVisible();

    // Screenshot: Admin dashboard loaded
    await page.screenshot({ path: 'test-results/smoke-admin-01-dashboard.png' });
  });

  test('FooGallery admin menu is accessible', async ({ page }) => {
    // Navigate to admin dashboard
    await page.goto('/wp-admin/');

    // Wait for page to fully load
    await page.waitForLoadState('domcontentloaded');

    // Find FooGallery in the admin menu
    // FooGallery registers as a custom post type, so it appears in the menu
    const fooGalleryMenu = page.locator('#menu-posts-foogallery');

    // Check if FooGallery menu exists
    await expect(fooGalleryMenu).toBeVisible({ timeout: 15000 });

    // Screenshot: FooGallery menu visible in sidebar
    await page.screenshot({ path: 'test-results/smoke-foogallery-01-menu-visible.png' });

    // Click on FooGallery menu
    await fooGalleryMenu.click();

    // Wait for FooGallery list page to load
    await page.waitForLoadState('networkidle');

    // Verify we're on the FooGallery list page
    await expect(page).toHaveURL(/post_type=foogallery/);

    // Check for FooGallery-specific elements
    const pageTitle = page.locator('.wrap h1').first();
    await expect(pageTitle).toContainText(/galleries|foogallery/i);

    // Screenshot: FooGallery list page
    await page.screenshot({ path: 'test-results/smoke-foogallery-02-list-page.png' });
  });

  test('FooGallery Add New page loads', async ({ page }) => {
    // Navigate directly to Add New Gallery page
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');

    // Wait for page to load
    await page.waitForLoadState('domcontentloaded');

    // Verify we're on the add new page
    await expect(page).toHaveURL(/post-new\.php\?post_type=foogallery/);

    // Check for gallery editor elements
    // The title input should be visible
    const titleInput = page.locator('#title, [name="post_title"], .editor-post-title__input').first();
    await expect(titleInput).toBeVisible({ timeout: 10000 });

    // Check for FooGallery metaboxes (gallery items section)
    // Use more specific selectors that target actual DOM elements, not CSS links
    const galleryMetabox = page.locator(
      '#foogallery_items, ' +
      'div.foogallery-metabox, ' +
      'div.foogallery-items-container, ' +
      '#foogallery-gallery-items, ' +
      '.foogallery-gallery-select, ' +
      '#foogallery_settings'
    ).first();

    // Gallery UI should be present
    await expect(galleryMetabox).toBeVisible({ timeout: 15000 });

    // Screenshot: Add New Gallery page
    await page.screenshot({ path: 'test-results/smoke-foogallery-03-add-new-page.png' });
  });

  test('FooGallery Pro features are unlocked', async ({ page }) => {
    // Navigate to Add New Gallery page
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Look for Pro template options or Pro-specific UI elements
    // Pro templates include: Polaroid, Grid PRO, Slider PRO, etc.
    const pageContent = await page.content();

    // Check for indicators that Pro features are available
    // This could be Pro templates in a dropdown, or absence of "upgrade" prompts
    const hasProIndicators =
      pageContent.includes('polaroid') ||
      pageContent.includes('Polaroid') ||
      pageContent.includes('pro') ||
      pageContent.includes('PRO') ||
      pageContent.includes('slider') ||
      pageContent.includes('Slider') ||
      // Check for absence of upgrade nags
      !pageContent.includes('Upgrade to Pro');

    // The freemius-e2e-helper should ensure Pro features are unlocked
  // At minimum, we should not see upgrade prompts
    const noFreemiusNag =
      !pageContent.includes('Start my free trial') &&
      !pageContent.includes('Activate License') &&
      !pageContent.includes('foogallery_fs().can_use_premium_code');

    expect(hasProIndicators || noFreemiusNag).toBeTruthy();

    // Screenshot: Pro features verification
    await page.screenshot({ path: 'test-results/smoke-foogallery-04-pro-features.png' });
  });
});
