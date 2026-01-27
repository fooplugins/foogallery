// File: tests/helpers/gallery-test-helper.ts
// Shared helper for gallery layout tests

import { Page, expect } from '@playwright/test';

export interface GalleryTestOptions {
  layoutName: string;        // Display name: "Justified", "Masonry", etc.
  templateSelector: string;  // data-template value: "justified", "masonry", etc.
  screenshotPrefix: string;  // Prefix for screenshot files
  imageCount?: number;       // Number of images to add (default: 3)
}

/**
 * Creates a gallery with the specified layout, adds images, publishes,
 * creates a page, and navigates to view it. Returns control to caller
 * for custom page interactions.
 */
export async function createGalleryAndNavigateToPage(page: Page, options: GalleryTestOptions): Promise<void> {
  const { layoutName, templateSelector, screenshotPrefix, imageCount = 5 } = options;

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

  // Click "Add New" submenu (3rd item in FooGallery menu)
  await page.locator('#menu-posts-foogallery li:nth-of-type(3) > a').click();
  await page.waitForLoadState('domcontentloaded');

  // Verify we're on the Add New Gallery page
  await expect(page).toHaveURL(/post-new\.php\?post_type=foogallery/);

  // Screenshot: Add New Gallery page
  await page.screenshot({ path: `test-results/${screenshotPrefix}-01-add-new.png` });

  // Enter gallery title
  await page.locator('#title').fill(`Test Gallery ${layoutName}`);

  // Select the template (click on the template card)
  const templateCard = page.locator(`[data-template="${templateSelector}"]`);
  await templateCard.waitFor({ state: 'visible', timeout: 10000 });
  await templateCard.click();

  // Verify template is selected (should have 'selected' class)
  await expect(templateCard).toHaveClass(/selected/);

  // Screenshot: Template selected
  await page.screenshot({ path: `test-results/${screenshotPrefix}-02-template-selected.png` });

  // Click "Add From Media Library" to add images from the pre-loaded media library
  await page.locator('text=Add From Media Library').click();
  await page.waitForLoadState('networkidle');

  // Wait for the media modal to appear
  const modal = page.locator('.media-modal:visible');
  await modal.waitFor({ state: 'visible', timeout: 10000 });

  // Click "Media Library" tab to see existing images
  const mediaLibraryTab = modal.locator('.media-menu-item').filter({ hasText: 'Media Library' });
  await mediaLibraryTab.click();

  // Select images
  const attachments = modal.locator('.attachment');
  await attachments.first().waitFor({ state: 'visible', timeout: 10000 });

  // Click images to select them
  for (let i = 0; i < imageCount; i++) {
    await attachments.nth(i).click();
  }

  // Click "Add to Gallery" button
  const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
  await addButton.click();
  await page.waitForLoadState('networkidle');

  // Screenshot: Gallery with images added
  await page.screenshot({ path: `test-results/${screenshotPrefix}-03-images-added.png` });

  // Click Publish button to save the gallery
  await page.locator('#publish').click();
  await page.waitForLoadState('networkidle');

  // Wait for the publish to complete (URL should contain post= and action=edit)
  await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

  // Screenshot: Gallery published
  await page.screenshot({ path: `test-results/${screenshotPrefix}-04-published.png` });

  // Extract the gallery post ID from the URL for later use
  const url = page.url();
  const postIdMatch = url.match(/post=(\d+)/);
  const galleryId = postIdMatch ? postIdMatch[1] : null;
  console.log(`Gallery created with ID: ${galleryId}`);

  // Click "Create Gallery Page" button to create a page with this gallery
  await page.locator('#foogallery_create_page').click();
  await page.waitForLoadState('networkidle');
  await page.waitForLoadState('domcontentloaded');

  // Screenshot: Page created (shown in Gallery Usage)
  await page.screenshot({ path: `test-results/${screenshotPrefix}-05-page-created.png` });

  // Find the View link - it's in the sidebar "Gallery Usage" section
  // Wait longer as the page creation can take time to complete and update the DOM
  const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
  await viewLink.waitFor({ state: 'visible', timeout: 30000 });
  const viewUrl = await viewLink.getAttribute('href');

  if (viewUrl) {
    await page.goto(viewUrl);
    await page.waitForLoadState('networkidle');
  }

  // Screenshot: Gallery displayed on page
  await page.screenshot({ path: `test-results/${screenshotPrefix}-06-page-view.png` });
}

/**
 * Creates a gallery with the specified layout, adds images, publishes,
 * creates a page, and tests the lightbox functionality.
 */
export async function createAndTestGallery(page: Page, options: GalleryTestOptions): Promise<void> {
  const { layoutName, templateSelector, screenshotPrefix } = options;

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

  // Click "Add New" submenu (3rd item in FooGallery menu)
  await page.locator('#menu-posts-foogallery li:nth-of-type(3) > a').click();
  await page.waitForLoadState('domcontentloaded');

  // Verify we're on the Add New Gallery page
  await expect(page).toHaveURL(/post-new\.php\?post_type=foogallery/);

  // Screenshot: Add New Gallery page
  await page.screenshot({ path: `test-results/${screenshotPrefix}-01-add-new.png` });

  // Enter gallery title
  await page.locator('#title').fill(`Test Gallery ${layoutName}`);

  // Select the template (click on the template card)
  const templateCard = page.locator(`[data-template="${templateSelector}"]`);
  await templateCard.waitFor({ state: 'visible', timeout: 10000 });
  await templateCard.click();

  // Verify template is selected (should have 'selected' class)
  await expect(templateCard).toHaveClass(/selected/);

  // Screenshot: Template selected
  await page.screenshot({ path: `test-results/${screenshotPrefix}-02-template-selected.png` });

  // Click "Add From Media Library" to add images from the pre-loaded media library
  await page.locator('text=Add From Media Library').click();
  await page.waitForLoadState('networkidle');

  // Wait for the media modal to appear
  const modal = page.locator('.media-modal:visible');
  await modal.waitFor({ state: 'visible', timeout: 10000 });

  // Click "Media Library" tab to see existing images
  const mediaLibraryTab = modal.locator('.media-menu-item').filter({ hasText: 'Media Library' });
  await mediaLibraryTab.click();

  // Select multiple images (the first 3 sample images)
  const attachments = modal.locator('.attachment');
  await attachments.first().waitFor({ state: 'visible', timeout: 10000 });

  // Click first 3 images to select them
  for (let i = 0; i < 3; i++) {
    await attachments.nth(i).click();
  }

  // Click "Add to Gallery" button
  const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
  await addButton.click();
  await page.waitForLoadState('networkidle');

  // Screenshot: Gallery with images added
  await page.screenshot({ path: `test-results/${screenshotPrefix}-03-images-added.png` });

  // Click Publish button to save the gallery
  await page.locator('#publish').click();
  await page.waitForLoadState('networkidle');

  // Wait for the publish to complete (URL should contain post= and action=edit)
  await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

  // Screenshot: Gallery published
  await page.screenshot({ path: `test-results/${screenshotPrefix}-04-published.png` });

  // Extract the gallery post ID from the URL for later use
  const url = page.url();
  const postIdMatch = url.match(/post=(\d+)/);
  const galleryId = postIdMatch ? postIdMatch[1] : null;
  console.log(`Gallery created with ID: ${galleryId}`);

  // Click "Create Gallery Page" button to create a page with this gallery
  await page.locator('#foogallery_create_page').click();
  await page.waitForLoadState('networkidle');
  await page.waitForLoadState('domcontentloaded');

  // Screenshot: Page created (shown in Gallery Usage)
  await page.screenshot({ path: `test-results/${screenshotPrefix}-05-page-created.png` });

  // Find the View link - it's in the sidebar "Gallery Usage" section
  // Wait longer as the page creation can take time to complete and update the DOM
  const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
  await viewLink.waitFor({ state: 'visible', timeout: 30000 });
  const viewUrl = await viewLink.getAttribute('href');

  if (viewUrl) {
    await page.goto(viewUrl);
    await page.waitForLoadState('networkidle');
  }

  // Screenshot: Gallery displayed on page
  await page.screenshot({ path: `test-results/${screenshotPrefix}-06-page-view.png` });

  // Wait for gallery images to be visible and click to open lightbox
  const galleryItem = page.locator('.fg-item a.fg-thumb').first();
  await galleryItem.waitFor({ state: 'visible', timeout: 15000 });

  // Click on the first gallery item to open lightbox (force to bypass caption overlay)
  await galleryItem.click({ force: true });

  // Wait for lightbox to open
  await page.waitForSelector('.fg-panel-content', { state: 'visible', timeout: 10000 });

  // Screenshot: Lightbox opened
  await page.screenshot({ path: `test-results/${screenshotPrefix}-07-lightbox-open.png` });

  // Click inside the lightbox panel area
  await page.locator('div.fg-panel-content > div.fg-panel-area-inner').click();

  // Navigate to next image using the next button
  await page.locator('button.fg-panel-button-next > svg').click();

  // Screenshot: Lightbox navigation
  await page.screenshot({ path: `test-results/${screenshotPrefix}-08-lightbox-next.png` });

  // Navigate to next image again
  await page.locator('button.fg-panel-button-next > svg').click();

  // Click fullscreen/expand button
  await page.locator('svg.fg-icon-expand').click();

  // Screenshot: Lightbox fullscreen
  await page.screenshot({ path: `test-results/${screenshotPrefix}-09-lightbox-fullscreen.png` });

  // Click shrink button to exit fullscreen
  await page.locator('svg.fg-icon-shrink').click();

  // Close the lightbox
  await page.locator('button.fg-panel-button-close path').click();

  // Wait for lightbox to close
  await page.waitForSelector('.fg-panel-content', { state: 'hidden', timeout: 10000 });

  // Screenshot: Final state after lightbox closed
  await page.screenshot({ path: `test-results/${screenshotPrefix}-10-final.png` });
}
