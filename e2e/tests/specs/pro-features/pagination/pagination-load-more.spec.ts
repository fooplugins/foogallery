// File: tests/specs/pro-features/pagination/pagination-load-more.spec.ts
// Tests for Load More pagination functionality

import { test, expect } from '@playwright/test';
import {
  PAGINATION_SELECTORS,
  createGalleryWithPagination,
  waitForPagination,
  clickLoadMore,
  isLoadMoreVisible,
  getVisibleItemCount,
} from '../../../helpers/pagination-test-helper';

test.describe('Pagination - Load More', () => {

  test.describe('Load More Button Display', () => {
    test('displays Load More button when enabled', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Load More Display Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-loadmore-display',
        imageCount: 15,
        pagingType: 'loadMore',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Verify Load More button is visible
      const loadMoreButton = page.locator(PAGINATION_SELECTORS.loadMoreButton);
      await expect(loadMoreButton).toBeVisible();

      // Verify initial visible count matches page size
      const visibleCount = await getVisibleItemCount(page);
      expect(visibleCount).toBe(5);

      await page.screenshot({ path: 'test-results/pagination-loadmore-button-visible.png' });
    });

    test('hides Load More button when all items are visible', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Load More Small Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-loadmore-small',
        imageCount: 3, // Less than page size
        pagingType: 'loadMore',
        pageSize: 5,
      });

      // Load More should not be visible when total items < page size
      const loadMoreButton = page.locator(PAGINATION_SELECTORS.loadMoreButton);
      await expect(loadMoreButton).not.toBeVisible();

      await page.screenshot({ path: 'test-results/pagination-loadmore-not-needed.png' });
    });
  });

  test.describe('Load More Functionality', () => {
    test('loads more items when clicking Load More', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Load More Click Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-loadmore-click',
        imageCount: 15,
        pagingType: 'loadMore',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Get initial count
      const initialCount = await getVisibleItemCount(page);
      expect(initialCount).toBe(5);

      // Click Load More
      await clickLoadMore(page);

      // Verify more items are visible
      const countAfterClick = await getVisibleItemCount(page);
      expect(countAfterClick).toBe(10);

      await page.screenshot({ path: 'test-results/pagination-loadmore-clicked.png' });
    });

    test('progressively loads all items with multiple clicks', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Load More Progressive Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-loadmore-progressive',
        imageCount: 15,
        pagingType: 'loadMore',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Click 1: 5 -> 10
      await clickLoadMore(page);
      let count = await getVisibleItemCount(page);
      expect(count).toBe(10);

      // Click 2: 10 -> 15
      await clickLoadMore(page);
      count = await getVisibleItemCount(page);
      expect(count).toBe(15);

      // Button should be hidden now (all items loaded)
      const isVisible = await isLoadMoreVisible(page);
      expect(isVisible).toBe(false);

      await page.screenshot({ path: 'test-results/pagination-loadmore-all-loaded.png' });
    });

    test('hides button when all items are loaded', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Load More Complete Test',
        templateSelector: 'masonry',
        screenshotPrefix: 'pagination-loadmore-complete',
        imageCount: 10,
        pagingType: 'loadMore',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Initial state - button visible
      let isVisible = await isLoadMoreVisible(page);
      expect(isVisible).toBe(true);

      // Click to load all items
      await clickLoadMore(page);

      // After loading all, button should be hidden
      isVisible = await isLoadMoreVisible(page);
      expect(isVisible).toBe(false);

      // All 10 items should be visible
      const count = await getVisibleItemCount(page);
      expect(count).toBe(10);

      await page.screenshot({ path: 'test-results/pagination-loadmore-complete-result.png' });
    });
  });

  test.describe('Load More with Different Templates', () => {
    test('works with Masonry template', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Load More Masonry Test',
        templateSelector: 'masonry',
        screenshotPrefix: 'pagination-loadmore-masonry',
        imageCount: 15,
        pagingType: 'loadMore',
        pageSize: 5,
      });

      await waitForPagination(page);

      const initialCount = await getVisibleItemCount(page);
      expect(initialCount).toBe(5);

      await clickLoadMore(page);

      const countAfterClick = await getVisibleItemCount(page);
      expect(countAfterClick).toBe(10);

      await page.screenshot({ path: 'test-results/pagination-loadmore-masonry-result.png' });
    });

    test('works with Grid Pro template', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Load More Grid Pro Test',
        templateSelector: 'foogridpro',
        screenshotPrefix: 'pagination-loadmore-gridpro',
        imageCount: 15,
        pagingType: 'loadMore',
        pageSize: 6,
      });

      await waitForPagination(page);

      const initialCount = await getVisibleItemCount(page);
      expect(initialCount).toBe(6);

      await clickLoadMore(page);

      const countAfterClick = await getVisibleItemCount(page);
      expect(countAfterClick).toBe(12);

      await page.screenshot({ path: 'test-results/pagination-loadmore-gridpro-result.png' });
    });
  });

  test.describe('Load More Page Sizes', () => {
    test('respects custom page size', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Load More Custom Size Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-loadmore-custom-size',
        imageCount: 20,
        pagingType: 'loadMore',
        pageSize: 8,
      });

      await waitForPagination(page);

      // Initial count should match page size
      const initialCount = await getVisibleItemCount(page);
      expect(initialCount).toBe(8);

      // After click, should have 16 items
      await clickLoadMore(page);
      const countAfterClick = await getVisibleItemCount(page);
      expect(countAfterClick).toBe(16);

      await page.screenshot({ path: 'test-results/pagination-loadmore-custom-size-result.png' });
    });
  });
});
