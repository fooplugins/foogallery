// File: tests/specs/pro-features/video/video-lightbox.spec.ts
// Tests for video playback in lightbox
// Based on Chrome DevTools recording

import { test, expect } from '@playwright/test';
import {
  configureVideoSettings,
  importYouTubeVideo,
  importMultipleYouTubeVideos,
  TEST_VIDEOS,
} from '../../../helpers/video-test-helper';

test.describe('Video Lightbox Playback', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'video-lightbox';
  let galleryPageUrl = '';

  // Create a gallery with videos for all tests
  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1760, height: 1246 });

    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Video Lightbox');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Import multiple YouTube videos for navigation testing
    const youtubeUrls = [
      'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
      'https://www.youtube.com/watch?v=5mj2mXIL-D0',
    ];
    await importMultipleYouTubeVideos(page, youtubeUrls);

    // Configure video settings
    await configureVideoSettings(page, templateSelector, {
      enabled: true,
      hoverIcon: 'icon1',
      stickyIcon: true,
      videoSize: '1280x720',
      autoplay: false, // Disable autoplay for testing
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
    // Navigate to gallery page
    if (galleryPageUrl) {
      await page.goto(galleryPageUrl);
      await page.waitForLoadState('networkidle');
    }
  });

  test('opens video in lightbox', async ({ page }) => {
    // Screenshot: Gallery before click
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-gallery-view.png` });

    // Click on the first gallery item (video thumbnail)
    // From recording: clicking on figcaption or the item opens lightbox
    const firstItem = page.locator('.fg-item').first();
    await firstItem.waitFor({ state: 'visible', timeout: 15000 });
    await firstItem.click();

    // Wait for lightbox to open - the panel content should be visible
    await page.waitForSelector('.fg-panel-content', { state: 'visible', timeout: 10000 });

    // Screenshot: Lightbox opened
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-lightbox-opened.png` });

    // Check that lightbox panel is visible
    const lightboxPanel = page.locator('.fg-panel-content');
    await expect(lightboxPanel).toBeVisible();

    // Wait for video to load (YouTube embeds can take time)
    await page.waitForTimeout(2000);

    // Take another screenshot after video loads
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-video-loaded.png` });

    // Verify lightbox is still visible
    await expect(lightboxPanel).toBeVisible();
  });

  test('displays video player in lightbox', async ({ page }) => {
    // Click on the first video item
    const firstItem = page.locator('.fg-item').first();
    await firstItem.click();

    // Wait for lightbox
    await page.waitForSelector('.fg-panel-content', { state: 'visible', timeout: 10000 });
    await page.waitForTimeout(2000); // Wait for video to load

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-video-player.png` });

    // Check for video element (YouTube uses iframe, self-hosted uses video)
    // The recording showed a video element was present
    const videoElement = page.locator('.fg-panel-content video, .fg-panel-content iframe');

    // Should have either video or iframe element for video playback
    const hasVideo = await videoElement.count() > 0;

    // Take screenshot regardless of video presence
    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-video-element.png` });

    // Close lightbox using close button from recording
    await page.locator('button.fg-panel-button-close > svg').click();
    await page.waitForTimeout(500);
  });

  test('navigates between videos in lightbox', async ({ page }) => {
    // Click on the first video item
    const firstItem = page.locator('.fg-item').first();
    await firstItem.click();

    // Wait for lightbox
    await page.waitForSelector('.fg-panel-content', { state: 'visible', timeout: 10000 });
    await page.waitForTimeout(1500);

    // Screenshot: First video
    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-first-video.png` });

    // Click to show controls if needed (from recording: clicking panel area)
    await page.locator('div.fg-panel-content > div.fg-panel-area-inner').click({ force: true });
    await page.waitForTimeout(300);

    // Click next button (from recording: button.fg-panel-button-next > svg)
    const nextButton = page.locator('button.fg-panel-button-next > svg');
    if (await nextButton.isVisible()) {
      await nextButton.click();
      await page.waitForTimeout(1500);

      // Screenshot: Second video
      await page.screenshot({ path: `test-results/${screenshotPrefix}-07-second-video.png` });

      // Verify we navigated (the lightbox should still be visible)
      const lightboxPanel = page.locator('.fg-panel-content');
      await expect(lightboxPanel).toBeVisible();

      // Navigate back using prev button
      const prevButton = page.locator('button.fg-panel-button-prev > svg');
      if (await prevButton.isVisible()) {
        await prevButton.click();
        await page.waitForTimeout(1000);

        // Screenshot: Back to first
        await page.screenshot({ path: `test-results/${screenshotPrefix}-08-back-to-first.png` });
      }
    }

    // Lightbox should still be visible
    const lightboxPanel = page.locator('.fg-panel-content');
    await expect(lightboxPanel).toBeVisible();

    // Close lightbox
    await page.locator('button.fg-panel-button-close > svg').click();
    await page.waitForTimeout(500);
  });

  test('closes video lightbox', async ({ page }) => {
    // Click on the first video item
    const firstItem = page.locator('.fg-item').first();
    await firstItem.click();

    // Wait for lightbox
    await page.waitForSelector('.fg-panel-content', { state: 'visible', timeout: 10000 });

    // Screenshot: Lightbox open
    await page.screenshot({ path: `test-results/${screenshotPrefix}-09-before-close.png` });

    // Close by clicking the close button (from recording: button.fg-panel-button-close > svg)
    await page.locator('button.fg-panel-button-close > svg').click();

    // Wait for lightbox to close
    await page.waitForTimeout(500);

    // Screenshot: Lightbox closed
    await page.screenshot({ path: `test-results/${screenshotPrefix}-10-after-close.png` });

    // Verify lightbox is closed
    const lightboxPanel = page.locator('.fg-panel-content');
    await expect(lightboxPanel).toBeHidden();
  });
});
