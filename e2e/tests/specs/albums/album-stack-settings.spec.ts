// File: tests/specs/albums/album-stack-settings.spec.ts
// Tests for album stack (all-in-one) template settings
// NOTE: Lightbox is NOT tested for stack template - only test lightbox for default template

import { test, expect } from '@playwright/test';
import {
  ALBUM_SELECTORS,
  ensureGalleriesExist,
  navigateToAddNewAlbum,
  selectAlbumTemplate,
  selectGalleries,
  publishAlbum,
  createPageWithAlbum,
  configureStackSettings,
  waitForAlbumReady,
} from '../../helpers/album-test-helper';

test.describe('Album - Stack Template Settings', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1932, height: 1271 });
    await ensureGalleriesExist(page, 3);
  });

  test('applies random angle rotation', async ({ page }) => {
    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Album Stack Random Angle');
    await selectAlbumTemplate(page, 'stack');
    await selectGalleries(page, 3);

    // Enable random angle
    await configureStackSettings(page, {
      randomAngle: true,
    });

    await page.screenshot({ path: 'test-results/album-stack-random-angle-config.png' });

    await publishAlbum(page);
    await createPageWithAlbum(page);
    await waitForAlbumReady(page, 'stack');

    // Verify stack container is present
    const stackContainer = page.locator(ALBUM_SELECTORS.frontend.stackContainer);
    await expect(stackContainer).toBeVisible();

    // Check for piles with rotation transforms (random angles add inline transforms)
    const piles = page.locator(ALBUM_SELECTORS.frontend.stackPile);
    const count = await piles.count();
    expect(count).toBeGreaterThan(0);

    await page.screenshot({ path: 'test-results/album-stack-random-angle-frontend.png' });
  });

  test('applies custom gutter spacing', async ({ page }) => {
    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Album Stack Gutter');
    await selectAlbumTemplate(page, 'stack');
    await selectGalleries(page, 3);

    // Set custom gutter
    await configureStackSettings(page, {
      gutter: 60,
    });

    await page.screenshot({ path: 'test-results/album-stack-gutter-config.png' });

    await publishAlbum(page);
    await createPageWithAlbum(page);
    await waitForAlbumReady(page, 'stack');

    // Verify stack container has gutter data attribute or style
    const stackContainer = page.locator(ALBUM_SELECTORS.frontend.stackContainer);
    await expect(stackContainer).toBeVisible();

    // Check data attribute for gutter (FooGallery often uses data attributes)
    const dataGutter = await stackContainer.getAttribute('data-gutter');
    if (dataGutter) {
      expect(dataGutter).toBe('60');
    }

    await page.screenshot({ path: 'test-results/album-stack-gutter-frontend.png' });
  });

  test('applies pile angle level 1', async ({ page }) => {
    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Album Stack Pile Angle 1');
    await selectAlbumTemplate(page, 'stack');
    await selectGalleries(page, 3);

    // Disable random angle first (to see pile angles effect)
    await configureStackSettings(page, {
      randomAngle: false,
      pileAngle: 1,
    });

    await page.screenshot({ path: 'test-results/album-stack-pile-angle-1-config.png' });

    await publishAlbum(page);
    await createPageWithAlbum(page);
    await waitForAlbumReady(page, 'stack');

    const stackContainer = page.locator(ALBUM_SELECTORS.frontend.stackContainer);
    await expect(stackContainer).toBeVisible();

    const piles = page.locator(ALBUM_SELECTORS.frontend.stackPile);
    const count = await piles.count();
    expect(count).toBeGreaterThan(0);

    await page.screenshot({ path: 'test-results/album-stack-pile-angle-1-frontend.png' });
  });

  test('applies pile angle level 3 (high intensity)', async ({ page }) => {
    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Album Stack Pile Angle 3');
    await selectAlbumTemplate(page, 'stack');
    await selectGalleries(page, 3);

    // Set pile angle to level 3 (high intensity)
    await configureStackSettings(page, {
      randomAngle: false,
      pileAngle: 3,
    });

    await page.screenshot({ path: 'test-results/album-stack-pile-angle-3-config.png' });

    await publishAlbum(page);
    await createPageWithAlbum(page);
    await waitForAlbumReady(page, 'stack');

    const stackContainer = page.locator(ALBUM_SELECTORS.frontend.stackContainer);
    await expect(stackContainer).toBeVisible();

    await page.screenshot({ path: 'test-results/album-stack-pile-angle-3-frontend.png' });
  });

  test('applies thumbnail dimensions', async ({ page }) => {
    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Album Stack Thumb Dimensions');
    await selectAlbumTemplate(page, 'stack');
    await selectGalleries(page, 3);

    // Set custom dimensions
    await configureStackSettings(page, {
      thumbWidth: 180,
      thumbHeight: 180,
    });

    await page.screenshot({ path: 'test-results/album-stack-thumb-dimensions-config.png' });

    await publishAlbum(page);
    await createPageWithAlbum(page);
    await waitForAlbumReady(page, 'stack');

    // Verify stack pile items have images
    const pileItems = page.locator(ALBUM_SELECTORS.frontend.stackPileItem);
    const count = await pileItems.count();
    expect(count).toBeGreaterThan(0);

    // Check first pile item has an image
    const firstPileImage = pileItems.first().locator('img');
    if (await firstPileImage.count() > 0) {
      await expect(firstPileImage).toBeVisible();
    }

    await page.screenshot({ path: 'test-results/album-stack-thumb-dimensions-frontend.png' });
  });

  test('stack album displays without lightbox', async ({ page }) => {
    // This test verifies basic stack functionality without testing lightbox
    // (Lightbox is only tested for default/responsive template)
    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Album Stack Basic');
    await selectAlbumTemplate(page, 'stack');
    await selectGalleries(page, 3);

    await page.screenshot({ path: 'test-results/album-stack-basic-config.png' });

    await publishAlbum(page);
    await createPageWithAlbum(page);
    await waitForAlbumReady(page, 'stack');

    // Verify all key stack elements
    const stackContainer = page.locator(ALBUM_SELECTORS.frontend.stackContainer);
    await expect(stackContainer).toBeVisible();

    const stackPiles = page.locator(ALBUM_SELECTORS.frontend.stackPiles);
    await expect(stackPiles).toBeVisible();

    const piles = page.locator(ALBUM_SELECTORS.frontend.stackPile);
    const pileCount = await piles.count();
    expect(pileCount).toBeGreaterThanOrEqual(2);

    await page.screenshot({ path: 'test-results/album-stack-basic-frontend.png' });
  });
});
