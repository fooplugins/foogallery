// File: tests/specs/albums/album-frontend-default.spec.ts
// Frontend tests for album default (responsive) template
// NOTE: Lightbox testing is done HERE for the responsive/default template only

import { test, expect } from '@playwright/test';
import {
  ALBUM_SELECTORS,
  ensureGalleriesExist,
  createAlbumWithGalleries,
  waitForAlbumReady,
  waitForGalleryInAlbum,
  clickGalleryPile,
  navigateBackToAlbum,
  openLightbox,
  closeLightbox,
  lightboxNext,
  getPileCount,
  getPileTitles,
} from '../../helpers/album-test-helper';

test.describe('Album - Frontend Default Template', () => {
  let albumPageUrl: string;
  let albumId: string;

  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1932, height: 1271 });

    // Create album once for all tests in this describe block
    await ensureGalleriesExist(page, 3);

    const result = await createAlbumWithGalleries(page, {
      albumName: 'Frontend Default Album Test',
      template: 'default',
      galleryCount: 3,
      screenshotPrefix: 'album-frontend-default-setup',
    });

    albumId = result.albumId;
    albumPageUrl = result.pageUrl;

    await page.close();
  });

  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1932, height: 1271 });
    // Navigate to the album page before each test
    await page.goto(albumPageUrl);
    await page.waitForLoadState('networkidle');
  });

  test('displays album container', async ({ page }) => {
    await waitForAlbumReady(page, 'default');

    // Verify album container exists
    const albumContainer = page.locator(ALBUM_SELECTORS.frontend.albumContainer);
    await expect(albumContainer).toBeVisible();

    // The container should have an album-specific ID or class
    const containerHTML = await albumContainer.innerHTML();
    expect(containerHTML.length).toBeGreaterThan(0);

    await page.screenshot({ path: 'test-results/album-frontend-default-container.png' });
  });

  test('displays gallery piles with correct count', async ({ page }) => {
    await waitForAlbumReady(page, 'default');

    const pileCount = await getPileCount(page, 'default');

    // Should have 3 piles (we created album with 3 galleries)
    expect(pileCount).toBeGreaterThanOrEqual(2);

    await page.screenshot({ path: 'test-results/album-frontend-default-piles.png' });
  });

  test('displays gallery thumbnails in piles', async ({ page }) => {
    await waitForAlbumReady(page, 'default');

    // Check for images inside piles
    const pileImages = page.locator('.foogallery-pile img, .foogallery-pile-inner img');
    const imageCount = await pileImages.count();
    expect(imageCount).toBeGreaterThan(0);

    // First image should be visible
    await expect(pileImages.first()).toBeVisible();

    await page.screenshot({ path: 'test-results/album-frontend-default-thumbnails.png' });
  });

  test('displays gallery titles', async ({ page }) => {
    await waitForAlbumReady(page, 'default');

    // Check for gallery titles in piles (h3 by default)
    const pileTitles = page.locator(ALBUM_SELECTORS.frontend.pileTitle);
    const titleCount = await pileTitles.count();
    expect(titleCount).toBeGreaterThan(0);

    // Get title texts
    const titles = await getPileTitles(page);
    expect(titles.length).toBeGreaterThan(0);
    expect(titles[0]).toBeTruthy();

    await page.screenshot({ path: 'test-results/album-frontend-default-titles.png' });
  });

  test('displays image counts in piles', async ({ page }) => {
    await waitForAlbumReady(page, 'default');

    // Image count is typically shown in a span
    const pileCounts = page.locator(ALBUM_SELECTORS.frontend.pileCount);
    const countElements = await pileCounts.count();

    // If image counts are displayed, verify they show numbers
    if (countElements > 0) {
      const firstCount = await pileCounts.first().textContent();
      // Count text typically contains a number
      expect(firstCount).toBeTruthy();
    }

    await page.screenshot({ path: 'test-results/album-frontend-default-counts.png' });
  });

  test('navigates to gallery on pile click', async ({ page }) => {
    await waitForAlbumReady(page, 'default');

    const initialUrl = page.url();

    // Click on first gallery pile
    await clickGalleryPile(page, 0);

    // Wait for gallery to load
    await waitForGalleryInAlbum(page);

    // Verify URL changed (gallery should be shown)
    const newUrl = page.url();
    expect(newUrl).not.toBe(initialUrl);

    // Verify gallery container is visible
    const galleryContainer = page.locator(ALBUM_SELECTORS.frontend.galleryContainer);
    await expect(galleryContainer).toBeVisible();

    // Verify gallery items are visible
    const galleryItems = page.locator(ALBUM_SELECTORS.frontend.galleryItem);
    const itemCount = await galleryItems.count();
    expect(itemCount).toBeGreaterThan(0);

    await page.screenshot({ path: 'test-results/album-frontend-default-gallery-view.png' });
  });

  test('displays back link on gallery page', async ({ page }) => {
    await waitForAlbumReady(page, 'default');

    // Navigate to a gallery
    await clickGalleryPile(page, 0);
    await waitForGalleryInAlbum(page);

    // Check for back link
    const backLink = page.locator(ALBUM_SELECTORS.frontend.backLink);
    await expect(backLink.first()).toBeVisible();

    await page.screenshot({ path: 'test-results/album-frontend-default-back-link.png' });
  });

  test('returns to album via back link', async ({ page }) => {
    await waitForAlbumReady(page, 'default');

    const originalUrl = page.url();

    // Navigate to gallery
    await clickGalleryPile(page, 0);
    await waitForGalleryInAlbum(page);

    // Click back link
    await navigateBackToAlbum(page);

    // Wait for album to be ready again
    await waitForAlbumReady(page, 'default');

    // Verify we're back at the album
    const albumContainer = page.locator(ALBUM_SELECTORS.frontend.albumContainer);
    await expect(albumContainer).toBeVisible();

    const piles = page.locator(ALBUM_SELECTORS.frontend.pile);
    const pileCount = await piles.count();
    expect(pileCount).toBeGreaterThan(0);

    await page.screenshot({ path: 'test-results/album-frontend-default-back-to-album.png' });
  });

  test('opens lightbox on image click', async ({ page }) => {
    await waitForAlbumReady(page, 'default');

    // Navigate to gallery first
    await clickGalleryPile(page, 0);
    await waitForGalleryInAlbum(page);

    // Open lightbox by clicking an image
    await openLightbox(page, 0);

    // Verify lightbox is open
    const lightboxContent = page.locator(ALBUM_SELECTORS.frontend.lightboxContent);
    await expect(lightboxContent).toBeVisible();

    // Verify lightbox has content
    const lightboxImage = page.locator('.fg-panel-content img, .fg-panel-media img');
    if (await lightboxImage.count() > 0) {
      await expect(lightboxImage.first()).toBeVisible();
    }

    await page.screenshot({ path: 'test-results/album-frontend-default-lightbox-open.png' });
  });

  test('closes lightbox correctly', async ({ page }) => {
    await waitForAlbumReady(page, 'default');

    // Navigate to gallery
    await clickGalleryPile(page, 0);
    await waitForGalleryInAlbum(page);

    // Open lightbox
    await openLightbox(page, 0);

    // Verify lightbox is open
    const lightboxContent = page.locator(ALBUM_SELECTORS.frontend.lightboxContent);
    await expect(lightboxContent).toBeVisible();

    // Close lightbox
    await closeLightbox(page);

    // Verify lightbox is closed
    await expect(lightboxContent).toBeHidden();

    // Gallery should still be visible
    const galleryContainer = page.locator(ALBUM_SELECTORS.frontend.galleryContainer);
    await expect(galleryContainer).toBeVisible();

    await page.screenshot({ path: 'test-results/album-frontend-default-lightbox-closed.png' });
  });
});
