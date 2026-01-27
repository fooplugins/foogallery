// File: tests/helpers/pagination-test-helper.ts
// Helper for pagination pro feature tests

import { Page, expect } from '@playwright/test';

// Pagination selectors based on class-foogallery-paging.php and class-foogallery-pro-paging.php
// Updated based on Chrome DevTools recording of actual FooGallery output
export const PAGINATION_SELECTORS = {
  // Container - different paging types use different structures:
  // - Numbered pagination: ul.fg-pages
  // - Load More: nav containing button.fg-load-more
  // - Dots: nav containing dots
  container: 'ul.fg-pages, nav:has(button.fg-load-more), nav:has(.fg-paging-dot)',

  // Dots paging
  dots: '.fg-paging-dot',
  dotsActive: '.fg-paging-dot.fg-active',

  // Load More
  loadMoreContainer: '.fg-paging-container.fg-ph-load-more',
  loadMoreButton: '.fg-paging-load-more button, .fg-load-more',

  // Infinite scroll uses no visible controls

  // Numbered pagination - from foogallery.js class definitions:
  // item: "fg-page-item", button: "fg-page-button", link: "fg-page-link"
  // first/prev/next/last: "fg-page-first/prev/next/last", selected: "fg-selected"
  paginationContainer: 'ul.fg-pages',
  firstButton: 'li.fg-page-item.fg-page-first',
  prevButton: 'li.fg-page-item.fg-page-prev',
  nextButton: 'li.fg-page-item.fg-page-next',
  lastButton: 'li.fg-page-item.fg-page-last',
  // Page numbers are items WITHOUT the button class
  pageNumber: 'li.fg-page-item:not(.fg-page-button)',
  // Active/selected page uses fg-selected class
  pageActive: 'li.fg-page-item.fg-selected',
  prevMore: 'li.fg-page-item.fg-page-prev-more',
  nextMore: 'li.fg-page-item.fg-page-next-more',

  // Gallery items
  galleryItem: '.fg-item',
  hiddenItem: '.fg-item.fg-hidden',
} as const;

export interface PaginationTestOptions {
  galleryName: string;
  templateSelector: string;
  screenshotPrefix: string;
  imageCount?: number;
  pagingType: '' | 'dots' | 'pagination' | 'infinite' | 'loadMore';
  pageSize?: number;
  pagingPosition?: '' | 'top' | 'bottom' | 'both';
  showFirstLast?: boolean;
  showPrevNext?: boolean;
}

/**
 * Navigate to gallery edit page and configure paging settings
 */
export async function configureGalleryPaging(page: Page, options: PaginationTestOptions): Promise<void> {
  const {
    templateSelector,
    pagingType,
    pageSize = 5,
    pagingPosition = 'bottom',
    showFirstLast = true,
    showPrevNext = true,
  } = options;

  // Click on Paging section tab in Gallery Settings for the selected template
  const settingsSection = page.locator('#foogallery_settings');
  await settingsSection.scrollIntoViewIfNeeded();
  await page.waitForTimeout(500);

  // Each template has its own settings container
  const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
  const pagingTab = templateContainer.getByText('Paging', { exact: true }).first();
  await pagingTab.scrollIntoViewIfNeeded();
  await pagingTab.click({ force: true });
  await page.waitForTimeout(500);

  // Select paging type using text label (more robust)
  const pagingTypeLabels: Record<string, string> = {
    '': 'None',
    'dots': 'Dots',
    'pagination': 'Numbered',
    'infinite': 'Infinite Scroll',
    'loadMore': 'Load More',
  };
  const pagingLabel = pagingTypeLabels[pagingType] || 'None';

  // Find the Paging Type row and click the radio by text
  const pagingTypeRow = templateContainer.locator('tr').filter({ hasText: 'Paging Type' });
  const pagingOption = pagingTypeRow.getByText(pagingLabel, { exact: true });
  await pagingOption.click();
  await page.waitForTimeout(300);

  // Set page size - scoped to template container to avoid strict mode violations
  if (pagingType !== '') {
    const pageSizeInput = templateContainer.locator('input[name*="paging_size"]');
    await pageSizeInput.fill(String(pageSize));
  }

  // Set position for dots and pagination
  if (pagingType === 'dots' || pagingType === 'pagination') {
    if (pagingPosition !== 'bottom') {
      const positionRadio = templateContainer.locator(`input[name*="paging_position"][value="${pagingPosition}"]`);
      await positionRadio.click();
    }
  }

  // Configure numbered pagination options
  if (pagingType === 'pagination') {
    if (!showFirstLast) {
      const firstLastRadio = templateContainer.locator('input[name*="paging_showFirstLast"][value="false"]');
      await firstLastRadio.click();
    }
    if (!showPrevNext) {
      const prevNextRadio = templateContainer.locator('input[name*="paging_showPrevNext"][value="false"]');
      await prevNextRadio.click();
    }
  }
}

/**
 * Create a gallery with pagination enabled and navigate to view it
 */
export async function createGalleryWithPagination(page: Page, options: PaginationTestOptions): Promise<string> {
  const { galleryName, templateSelector, screenshotPrefix, imageCount = 5 } = options;

  await page.setViewportSize({ width: 1932, height: 1271 });

  // Navigate to Add New Gallery
  await page.goto('/wp-admin/post-new.php?post_type=foogallery');
  await page.waitForLoadState('domcontentloaded');

  // Enter gallery title
  await page.locator('#title').fill(galleryName);

  // Select template
  const templateCard = page.locator(`[data-template="${templateSelector}"]`);
  await templateCard.waitFor({ state: 'visible', timeout: 10000 });
  await templateCard.click();
  await expect(templateCard).toHaveClass(/selected/);

  // Add images from media library
  await page.locator('text=Add From Media Library').click();
  await page.waitForLoadState('networkidle');

  const modal = page.locator('.media-modal:visible');
  await modal.waitFor({ state: 'visible', timeout: 10000 });

  const mediaLibraryTab = modal.locator('.media-menu-item').filter({ hasText: 'Media Library' });
  await mediaLibraryTab.click();

  const attachments = modal.locator('.attachment');
  await attachments.first().waitFor({ state: 'visible', timeout: 10000 });

  // Get actual count of available images
  const availableCount = await attachments.count();
  const imagesToSelect = Math.min(imageCount, availableCount);

  for (let i = 0; i < imagesToSelect; i++) {
    await attachments.nth(i).click();
  }

  const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
  await addButton.click();
  await page.waitForLoadState('networkidle');

  // Configure paging
  await configureGalleryPaging(page, options);

  // Screenshot after configuration
  await page.screenshot({ path: `test-results/${screenshotPrefix}-configured.png` });

  // Publish gallery
  await page.locator('#publish').click();
  await page.waitForLoadState('networkidle');
  await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

  // Extract gallery ID
  const url = page.url();
  const postIdMatch = url.match(/post=(\d+)/);
  const galleryId = postIdMatch ? postIdMatch[1] : '';

  // Create gallery page and navigate to it
  await page.locator('#foogallery_create_page').click();
  await page.waitForLoadState('networkidle');

  const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
  await viewLink.waitFor({ state: 'visible', timeout: 30000 });
  const viewUrl = await viewLink.getAttribute('href');

  if (viewUrl) {
    await page.goto(viewUrl);
    await page.waitForLoadState('networkidle');
  }

  await page.screenshot({ path: `test-results/${screenshotPrefix}-frontend.png` });

  return galleryId;
}

/**
 * Get visible gallery items count
 */
export async function getVisibleItemCount(page: Page): Promise<number> {
  const items = page.locator(`${PAGINATION_SELECTORS.galleryItem}:not(.fg-hidden)`);
  return await items.count();
}

/**
 * Click the Load More button
 */
export async function clickLoadMore(page: Page): Promise<void> {
  const loadMoreButton = page.locator(PAGINATION_SELECTORS.loadMoreButton);
  await loadMoreButton.click();
  await page.waitForTimeout(1000); // Wait for items to load
}

/**
 * Check if Load More button is visible
 */
export async function isLoadMoreVisible(page: Page): Promise<boolean> {
  const loadMoreButton = page.locator(PAGINATION_SELECTORS.loadMoreButton);
  return await loadMoreButton.isVisible();
}

/**
 * Click page number in numbered pagination
 */
export async function clickPageNumber(page: Page, pageNum: number): Promise<void> {
  // Click the anchor inside the li.fg-page element
  const pageButton = page.locator(`${PAGINATION_SELECTORS.pageNumber} a`).nth(pageNum - 1);
  await pageButton.click();
  await page.waitForTimeout(500);
}

/**
 * Click next page button
 */
export async function clickNextPage(page: Page): Promise<void> {
  // Click the anchor inside the li.fg-page-next element
  const nextButton = page.locator(`${PAGINATION_SELECTORS.nextButton} a`);
  await nextButton.click();
  await page.waitForTimeout(500);
}

/**
 * Click previous page button
 */
export async function clickPrevPage(page: Page): Promise<void> {
  // Click the anchor inside the li.fg-page-prev element
  const prevButton = page.locator(`${PAGINATION_SELECTORS.prevButton} a`);
  await prevButton.click();
  await page.waitForTimeout(500);
}

/**
 * Click first page button
 */
export async function clickFirstPage(page: Page): Promise<void> {
  // Click the anchor inside the li.fg-page-first element
  const firstButton = page.locator(`${PAGINATION_SELECTORS.firstButton} a`);
  await firstButton.click();
  await page.waitForTimeout(500);
}

/**
 * Click last page button
 */
export async function clickLastPage(page: Page): Promise<void> {
  // Click the anchor inside the li.fg-page-last element
  const lastButton = page.locator(`${PAGINATION_SELECTORS.lastButton} a`);
  await lastButton.click();
  await page.waitForTimeout(500);
}

/**
 * Get current active page number
 */
export async function getCurrentPageNumber(page: Page): Promise<number> {
  // Get the text content of the anchor inside the active page li
  const activePage = page.locator(`${PAGINATION_SELECTORS.pageActive} a`);
  const text = await activePage.textContent();
  return parseInt(text || '1', 10);
}

/**
 * Scroll to bottom of page to trigger infinite scroll
 */
export async function scrollToBottom(page: Page): Promise<void> {
  await page.evaluate(() => {
    window.scrollTo(0, document.body.scrollHeight);
  });
  await page.waitForTimeout(1500); // Wait for items to load
}

/**
 * Wait for pagination container to be visible
 */
export async function waitForPagination(page: Page): Promise<void> {
  await page.waitForSelector(PAGINATION_SELECTORS.container, { state: 'visible', timeout: 10000 });
}

/**
 * Verify pagination is at expected position
 */
export async function verifyPaginationPosition(page: Page, position: 'top' | 'bottom' | 'both'): Promise<void> {
  const gallery = page.locator('.foogallery');
  const pagingContainers = page.locator(PAGINATION_SELECTORS.container);

  if (position === 'both') {
    await expect(pagingContainers).toHaveCount(2);
  } else {
    await expect(pagingContainers).toHaveCount(1);

    const galleryBox = await gallery.boundingBox();
    const pagingBox = await pagingContainers.first().boundingBox();

    if (galleryBox && pagingBox) {
      if (position === 'top') {
        expect(pagingBox.y).toBeLessThan(galleryBox.y);
      } else {
        expect(pagingBox.y).toBeGreaterThan(galleryBox.y);
      }
    }
  }
}
