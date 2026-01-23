// File: tests/specs/pro-features/video/video-frontend.spec.ts
// Tests for video gallery frontend display
// Based on Chrome DevTools recording

import { test, expect } from '@playwright/test';
import {
  configureVideoSettings,
  importYouTubeVideo,
  TEST_VIDEOS,
} from '../../../helpers/video-test-helper';

test.describe('Video Frontend Display', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'video-frontend';
  let galleryPageUrl = '';

  // Create a gallery with video for all tests
  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1760, height: 1246 });

    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Video Frontend');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Import a YouTube video
    await importYouTubeVideo(page, TEST_VIDEOS.youtube.url);

    // Configure video settings - enable with icon2 and sticky
    await configureVideoSettings(page, templateSelector, {
      enabled: true,
      hoverIcon: 'icon2',
      stickyIcon: true,
    });

    // Publish gallery
    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

    // Create gallery page
    await page.locator('#foogallery_create_page').click();
    await page.waitForLoadState('networkidle');

    // Get the view URL using the span > a selector from recording
    const viewLink = page.locator('span.view > a').first();
    await viewLink.waitFor({ state: 'visible', timeout: 30000 });
    galleryPageUrl = await viewLink.getAttribute('href') || '';

    await page.close();
  });

  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1760, height: 1246 });
  });

  test('displays video thumbnail in gallery', async ({ page }) => {
    // Navigate to gallery page
    await page.goto(galleryPageUrl);
    await page.waitForLoadState('networkidle');

    // Screenshot: Gallery with video thumbnail
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-video-thumbnail.png` });

    // Check that gallery is visible
    const gallery = page.locator('.foogallery');
    await expect(gallery).toBeVisible();

    // Check that there's at least one gallery item
    const galleryItem = page.locator('.fg-item').first();
    await expect(galleryItem).toBeVisible();
  });

  test('applies video icon style class', async ({ page }) => {
    // Navigate to gallery page
    await page.goto(galleryPageUrl);
    await page.waitForLoadState('networkidle');

    // Screenshot: Video icon style
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-icon-style.png` });

    // The gallery should have video-related classes
    const gallery = page.locator('.foogallery');
    const galleryClasses = await gallery.getAttribute('class') || '';

    // Should have some video-related class (fg-video-*)
    const hasVideoClass = galleryClasses.includes('fg-video-');
    expect(hasVideoClass).toBe(true);
  });

  test('shows sticky icon when enabled', async ({ page }) => {
    // Navigate to gallery page
    await page.goto(galleryPageUrl);
    await page.waitForLoadState('networkidle');

    // Screenshot: Sticky icon visible
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-sticky-icon.png` });

    // We enabled sticky icon, so gallery should have fg-video-sticky class
    const gallery = page.locator('.foogallery');
    const galleryClasses = await gallery.getAttribute('class') || '';

    // Should have sticky video class
    expect(galleryClasses).toContain('fg-video-sticky');
  });

  test('hides video styling when disabled', async ({ browser }) => {
    // Create a new gallery with video disabled
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1760, height: 1246 });

    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Video Disabled Frontend');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Import a YouTube video (even with video disabled, we need content)
    await importYouTubeVideo(page, TEST_VIDEOS.youtube.url);

    // Configure video settings - DISABLED
    await configureVideoSettings(page, templateSelector, {
      enabled: false,
    });

    // Publish gallery
    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

    // Create gallery page
    await page.locator('#foogallery_create_page').click();
    await page.waitForLoadState('networkidle');

    // Get the view URL and navigate
    const viewLink = page.locator('span.view > a').first();
    await viewLink.waitFor({ state: 'visible', timeout: 30000 });
    const viewUrl = await viewLink.getAttribute('href');

    if (viewUrl) {
      await page.goto(viewUrl);
      await page.waitForLoadState('networkidle');
    }

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-video-disabled.png` });

    // Gallery should NOT have sticky video icon class when video is disabled
    const gallery = page.locator('.foogallery');
    const galleryClasses = await gallery.getAttribute('class') || '';

    // Should not have sticky icon class when video is disabled
    expect(galleryClasses).not.toContain('fg-video-sticky');

    await page.close();
  });

  test('displays gallery in list view correctly', async ({ page }) => {
    // Navigate to gallery list in admin
    await page.goto('/wp-admin/edit.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Screenshot: Gallery list
    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-gallery-list.png` });

    // Find the "Test Video Frontend" gallery in the list
    const galleryRow = page.locator('tr').filter({ hasText: 'Test Video Frontend' });
    await expect(galleryRow).toBeVisible();

    // The gallery should be listed
    const galleryTitle = galleryRow.locator('.row-title, .column-title a').first();
    await expect(galleryTitle).toContainText('Test Video Frontend');
  });
});
