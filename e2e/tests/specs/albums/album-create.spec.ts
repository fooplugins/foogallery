// File: tests/specs/albums/album-create.spec.ts
// Tests for basic album creation flows

import { test, expect } from '@playwright/test';
import {
  ALBUM_SELECTORS,
  ensureGalleriesExist,
  navigateToAddNewAlbum,
  selectAlbumTemplate,
  selectGalleries,
  publishAlbum,
  getAlbumShortcode,
  createPageWithAlbum,
  navigateToAlbumsList,
  navigateToEditAlbum,
  updateAlbum,
  albumExistsInList,
  deselectGallery,
} from '../../helpers/album-test-helper';

test.describe('Album - Create', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1932, height: 1271 });
    // Ensure we have galleries to work with
    await ensureGalleriesExist(page, 3);
  });

  test('creates album with default template', async ({ page }) => {
    await navigateToAddNewAlbum(page);

    // Enter title
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Test Album Default');

    // Template should default to Responsive/Default
    const templateSelect = page.locator(ALBUM_SELECTORS.admin.templateSelect);
    await expect(templateSelect).toHaveValue(/default|responsive/i);

    // Select galleries
    await selectGalleries(page, 2);

    // Take screenshot before publish
    await page.screenshot({ path: 'test-results/album-create-default-configured.png' });

    // Publish
    const albumId = await publishAlbum(page);
    expect(albumId).toBeTruthy();

    // Verify we're on the edit page (indicates successful publish)
    await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

    // Verify album title persisted
    await expect(page.locator(ALBUM_SELECTORS.admin.titleInput)).toHaveValue('Test Album Default');

    await page.screenshot({ path: 'test-results/album-create-default-published.png' });
  });

  test('creates album with stack template', async ({ page }) => {
    await navigateToAddNewAlbum(page);

    // Enter title
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Test Album Stack');

    // Select Stack template
    await selectAlbumTemplate(page, 'stack');

    // Verify template changed
    const templateSelect = page.locator(ALBUM_SELECTORS.admin.templateSelect);
    const value = await templateSelect.inputValue();
    expect(value).toMatch(/stack/i);

    // Select galleries
    await selectGalleries(page, 2);

    await page.screenshot({ path: 'test-results/album-create-stack-configured.png' });

    // Publish
    const albumId = await publishAlbum(page);
    expect(albumId).toBeTruthy();

    // Verify we're on the edit page (indicates successful publish)
    await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

    await page.screenshot({ path: 'test-results/album-create-stack-published.png' });
  });

  test('displays shortcode after publish', async ({ page }) => {
    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Test Album Shortcode');
    await selectGalleries(page, 2);

    const albumId = await publishAlbum(page);

    // Get shortcode
    const shortcode = await getAlbumShortcode(page);

    // Verify shortcode format
    expect(shortcode).toMatch(/\[foogallery-album id="\d+"\]/);
    expect(shortcode).toContain(albumId);

    await page.screenshot({ path: 'test-results/album-create-shortcode.png' });
  });

  test('creates page with album shortcode', async ({ page }) => {
    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Test Album With Page');
    await selectGalleries(page, 2);

    await publishAlbum(page);

    // Create page with album
    const pageUrl = await createPageWithAlbum(page);
    expect(pageUrl).toBeTruthy();

    // Verify we're on the frontend page
    await expect(page).toHaveURL(/test-album-with-page/i);

    // Verify album container is visible
    const albumContainer = page.locator(ALBUM_SELECTORS.frontend.albumContainer);
    await expect(albumContainer).toBeVisible();

    await page.screenshot({ path: 'test-results/album-create-page.png' });
  });

  test('shows album in admin list', async ({ page }) => {
    const albumName = `Test Album List ${Date.now()}`;

    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill(albumName);
    await selectGalleries(page, 2);
    await publishAlbum(page);

    // Navigate to albums list
    await navigateToAlbumsList(page);

    // Check album appears in list
    const albumExists = await albumExistsInList(page, albumName);
    expect(albumExists).toBe(true);

    await page.screenshot({ path: 'test-results/album-create-list.png' });
  });

  test('edits existing album', async ({ page }) => {
    // Create an album first
    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Test Album Edit Original');
    await selectGalleries(page, 2);
    const albumId = await publishAlbum(page);

    // Navigate to edit page
    await navigateToEditAlbum(page, albumId);

    // Update title
    const titleInput = page.locator(ALBUM_SELECTORS.admin.titleInput);
    await titleInput.fill('Test Album Edit Updated');

    // Update
    await updateAlbum(page);

    // Verify update was successful - check for notice or verify title persisted
    // Note: WordPress may not always add message=1 to URL on update
    await page.waitForTimeout(500);

    // Verify new title persisted
    await expect(titleInput).toHaveValue('Test Album Edit Updated');

    await page.screenshot({ path: 'test-results/album-edit-updated.png' });
  });

  test('removes gallery from album', async ({ page }) => {
    // First ensure we have enough galleries
    await ensureGalleriesExist(page, 3);

    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Test Album Remove Gallery');

    // Select 3 galleries
    await selectGalleries(page, 3);

    const albumId = await publishAlbum(page);

    // Count selected galleries before removal
    const selectedGalleries = page.locator(`${ALBUM_SELECTORS.admin.galleryItem}.selected`);
    const initialCount = await selectedGalleries.count();
    expect(initialCount).toBeGreaterThanOrEqual(2);

    // Deselect one gallery (click to toggle off)
    await deselectGallery(page, 0);

    // Update album
    await updateAlbum(page);

    // Verify count decreased
    const newCount = await selectedGalleries.count();
    expect(newCount).toBeLessThan(initialCount);

    await page.screenshot({ path: 'test-results/album-remove-gallery.png' });
  });

  test('adds gallery to existing album', async ({ page }) => {
    // First ensure we have enough galleries
    await ensureGalleriesExist(page, 4);

    // Create album with 2 galleries
    await navigateToAddNewAlbum(page);
    await page.locator(ALBUM_SELECTORS.admin.titleInput).fill('Test Album Add Gallery');
    await selectGalleries(page, 2);
    const albumId = await publishAlbum(page);

    // Count initial selection
    const selectedGalleries = page.locator(`${ALBUM_SELECTORS.admin.galleryItem}.selected`);
    const initialCount = await selectedGalleries.count();

    // Navigate back to edit
    await navigateToEditAlbum(page, albumId);

    // Select an additional gallery (click on unselected one)
    const unselectedGalleries = page.locator(`${ALBUM_SELECTORS.admin.galleryItem}:not(.selected):not([data-selected="true"])`);
    const unselectedCount = await unselectedGalleries.count();
    if (unselectedCount > 0) {
      await unselectedGalleries.first().click();
      await page.waitForTimeout(200);
    }

    // Update album
    await updateAlbum(page);

    // Verify count increased
    const newCount = await selectedGalleries.count();
    expect(newCount).toBeGreaterThan(initialCount);

    await page.screenshot({ path: 'test-results/album-add-gallery.png' });
  });
});
