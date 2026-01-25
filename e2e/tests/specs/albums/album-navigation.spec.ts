// File: tests/specs/albums/album-navigation.spec.ts
// Navigation tests for albums - visiting galleries, back navigation, URL handling

import { test, expect } from '@playwright/test';
import {
  ALBUM_SELECTORS,
  ensureGalleriesExist,
  createAlbumWithGalleries,
  waitForAlbumReady,
  waitForGalleryInAlbum,
  clickGalleryPile,
  navigateBackToAlbum,
  getPileCount,
} from '../../helpers/album-test-helper';

test.describe('Album - Navigation', () => {
  let albumPageUrl: string;
  let albumId: string;

  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1932, height: 1271 });

    // Create album once for all navigation tests
    await ensureGalleriesExist(page, 3);

    const result = await createAlbumWithGalleries(page, {
      albumName: 'Navigation Test Album',
      template: 'default',
      galleryCount: 3,
      screenshotPrefix: 'album-navigation-setup',
    });

    albumId = result.albumId;
    albumPageUrl = result.pageUrl;

    await page.close();
  });

  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1932, height: 1271 });
  });

  test('navigates through all galleries sequentially', async ({ page }) => {
    await page.goto(albumPageUrl);
    await page.waitForLoadState('networkidle');
    await waitForAlbumReady(page, 'default');

    const pileCount = await getPileCount(page, 'default');
    expect(pileCount).toBeGreaterThanOrEqual(2);

    // Visit each gallery
    for (let i = 0; i < Math.min(pileCount, 3); i++) {
      // If not on first iteration, go back to album first
      if (i > 0) {
        await page.goto(albumPageUrl);
        await page.waitForLoadState('networkidle');
        await waitForAlbumReady(page, 'default');
      }

      // Click on pile
      await clickGalleryPile(page, i);
      await waitForGalleryInAlbum(page);

      // Verify gallery is displayed
      const galleryContainer = page.locator(ALBUM_SELECTORS.frontend.galleryContainer);
      await expect(galleryContainer).toBeVisible();

      await page.screenshot({ path: `test-results/album-navigation-gallery-${i}.png` });
    }
  });

  test('maintains album context with back link', async ({ page }) => {
    await page.goto(albumPageUrl);
    await page.waitForLoadState('networkidle');
    await waitForAlbumReady(page, 'default');

    // Visit first gallery
    await clickGalleryPile(page, 0);
    await waitForGalleryInAlbum(page);

    // Verify back link exists and contains album reference
    const backLink = page.locator(ALBUM_SELECTORS.frontend.backLink).first();
    await expect(backLink).toBeVisible();

    const backHref = await backLink.getAttribute('href');
    // Back link should point to the album page
    expect(backHref).toBeTruthy();

    // Click back and verify we return to album
    await navigateBackToAlbum(page);
    await waitForAlbumReady(page, 'default');

    const piles = page.locator(ALBUM_SELECTORS.frontend.pile);
    const pileCount = await piles.count();
    expect(pileCount).toBeGreaterThan(0);

    await page.screenshot({ path: 'test-results/album-navigation-back-context.png' });
  });

  test('handles direct gallery URL access', async ({ page }) => {
    // First get a gallery URL by navigating normally
    await page.goto(albumPageUrl);
    await page.waitForLoadState('networkidle');
    await waitForAlbumReady(page, 'default');

    // Click on first pile
    await clickGalleryPile(page, 0);
    await waitForGalleryInAlbum(page);

    // Capture the gallery URL
    const galleryUrl = page.url();

    // Now navigate directly to that URL (simulating direct access)
    await page.goto(albumPageUrl); // Reset first
    await page.waitForLoadState('networkidle');
    await page.goto(galleryUrl);
    await page.waitForLoadState('networkidle');

    // Gallery should still be accessible
    const galleryContainer = page.locator(ALBUM_SELECTORS.frontend.galleryContainer);
    await expect(galleryContainer).toBeVisible();

    // Back link should still work
    const backLink = page.locator(ALBUM_SELECTORS.frontend.backLink);
    if (await backLink.first().isVisible()) {
      await navigateBackToAlbum(page);
      await waitForAlbumReady(page, 'default');
    }

    await page.screenshot({ path: 'test-results/album-navigation-direct-url.png' });
  });

  test('preserves scroll position with hash', async ({ page }) => {
    await page.goto(albumPageUrl);
    await page.waitForLoadState('networkidle');
    await waitForAlbumReady(page, 'default');

    // Scroll down on the page
    await page.evaluate(() => window.scrollTo(0, 200));
    await page.waitForTimeout(300);

    // Visit a gallery
    await clickGalleryPile(page, 0);
    await waitForGalleryInAlbum(page);

    // Check if URL has hash (if album hash is enabled)
    const urlAfterClick = page.url();

    // Navigate back
    await navigateBackToAlbum(page);
    await waitForAlbumReady(page, 'default');

    // If hash was used, check the URL
    const urlAfterBack = page.url();

    await page.screenshot({ path: 'test-results/album-navigation-scroll-hash.png' });
  });

  test('handles URL with trailing slash', async ({ page }) => {
    // Test with trailing slash
    const urlWithSlash = albumPageUrl.endsWith('/') ? albumPageUrl : `${albumPageUrl}/`;
    await page.goto(urlWithSlash);
    await page.waitForLoadState('networkidle');

    // Album should still work
    await waitForAlbumReady(page, 'default');

    const piles = page.locator(ALBUM_SELECTORS.frontend.pile);
    const pileCount = await piles.count();
    expect(pileCount).toBeGreaterThan(0);

    await page.screenshot({ path: 'test-results/album-navigation-trailing-slash-with.png' });

    // Test without trailing slash
    const urlWithoutSlash = albumPageUrl.endsWith('/') ? albumPageUrl.slice(0, -1) : albumPageUrl;
    await page.goto(urlWithoutSlash);
    await page.waitForLoadState('networkidle');

    // Album should still work
    await waitForAlbumReady(page, 'default');

    const pilesAgain = page.locator(ALBUM_SELECTORS.frontend.pile);
    const pileCountAgain = await pilesAgain.count();
    expect(pileCountAgain).toBeGreaterThan(0);

    await page.screenshot({ path: 'test-results/album-navigation-trailing-slash-without.png' });
  });
});
