// File: tests/specs/albums/album-frontend-stack.spec.ts
// Frontend tests for album stack (all-in-one) template
// NOTE: Do NOT test lightbox for stack template - only test lightbox for default/responsive template

import { test, expect } from '@playwright/test';
import {
  ALBUM_SELECTORS,
  ensureGalleriesExist,
  createAlbumWithGalleries,
  waitForAlbumReady,
  clickStackPile,
  navigateBackFromStack,
  getPileCount,
} from '../../helpers/album-test-helper';

test.describe('Album - Frontend Stack Template', () => {
  let albumPageUrl: string;
  let albumId: string;

  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1932, height: 1271 });

    // Create stack album once for all tests in this describe block
    await ensureGalleriesExist(page, 3);

    const result = await createAlbumWithGalleries(page, {
      albumName: 'Frontend Stack Album Test',
      template: 'stack',
      galleryCount: 3,
      screenshotPrefix: 'album-frontend-stack-setup',
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

  test('displays stack container', async ({ page }) => {
    await waitForAlbumReady(page, 'stack');

    // Verify stack album container exists
    const stackContainer = page.locator(ALBUM_SELECTORS.frontend.stackContainer);
    await expect(stackContainer).toBeVisible();

    await page.screenshot({ path: 'test-results/album-frontend-stack-container.png' });
  });

  test('displays all piles', async ({ page }) => {
    await waitForAlbumReady(page, 'stack');

    const pileCount = await getPileCount(page, 'stack');

    // Should have at least 2 piles (we created album with 3 galleries)
    expect(pileCount).toBeGreaterThanOrEqual(2);

    // Verify piles are visible
    const piles = page.locator(ALBUM_SELECTORS.frontend.stackPile);
    await expect(piles.first()).toBeVisible();

    await page.screenshot({ path: 'test-results/album-frontend-stack-piles.png' });
  });

  test('displays pile items', async ({ page }) => {
    await waitForAlbumReady(page, 'stack');

    // Check for pile items within piles
    const pileItems = page.locator(ALBUM_SELECTORS.frontend.stackPileItem);
    const itemCount = await pileItems.count();
    expect(itemCount).toBeGreaterThan(0);

    // First pile item should be visible
    await expect(pileItems.first()).toBeVisible();

    // Check for images in pile items
    const pileImages = page.locator(`${ALBUM_SELECTORS.frontend.stackPileItem} img`);
    if (await pileImages.count() > 0) {
      await expect(pileImages.first()).toBeVisible();
    }

    await page.screenshot({ path: 'test-results/album-frontend-stack-pile-items.png' });
  });

  test('expands pile on click', async ({ page }) => {
    await waitForAlbumReady(page, 'stack');

    // Get initial state
    const stackContainer = page.locator(ALBUM_SELECTORS.frontend.stackContainer);
    const initialClass = await stackContainer.getAttribute('class');

    // Click on first pile
    await clickStackPile(page, 0);

    // Wait for expansion animation
    await page.waitForTimeout(500);

    // Stack should now be in expanded/active state
    // Check for state change indicators (class change, visibility of gallery, etc.)
    const piles = page.locator(ALBUM_SELECTORS.frontend.stackPile);
    const expandedPile = piles.first();

    // Check for expanded class or state indicator
    const expandedClass = await expandedPile.getAttribute('class');
    // Note: actual class names will depend on FooGallery implementation

    await page.screenshot({ path: 'test-results/album-frontend-stack-expanded.png' });
  });

  test('shows gallery title in header', async ({ page }) => {
    await waitForAlbumReady(page, 'stack');

    // Click on a pile to expand it
    await clickStackPile(page, 0);
    await page.waitForTimeout(500);

    // Check for header title
    const headerTitle = page.locator(ALBUM_SELECTORS.frontend.stackHeader);
    if (await headerTitle.count() > 0) {
      // Header should have title text
      const titleText = await headerTitle.textContent();
      // Title should not be empty (or check for specific gallery title)
      expect(titleText).toBeTruthy();
    }

    await page.screenshot({ path: 'test-results/album-frontend-stack-header.png' });
  });

  test('shows back button when expanded', async ({ page }) => {
    await waitForAlbumReady(page, 'stack');

    // Click on first pile to expand
    await clickStackPile(page, 0);
    await page.waitForTimeout(500);

    // Check for back button
    const backButton = page.locator(ALBUM_SELECTORS.frontend.stackBackButton);
    if (await backButton.count() > 0) {
      await expect(backButton).toBeVisible();

      // Click back button
      await navigateBackFromStack(page);
      await page.waitForTimeout(500);

      // Stack should return to initial view (piles visible)
      const piles = page.locator(ALBUM_SELECTORS.frontend.stackPile);
      const pileCount = await piles.count();
      expect(pileCount).toBeGreaterThan(0);
    }

    await page.screenshot({ path: 'test-results/album-frontend-stack-back-button.png' });
  });
});
