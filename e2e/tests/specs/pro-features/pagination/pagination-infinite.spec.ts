// File: tests/specs/pro-features/pagination/pagination-infinite.spec.ts
// Tests for Infinite Scroll pagination functionality

import { test, expect } from '@playwright/test';
import {
  PAGINATION_SELECTORS,
  createGalleryWithPagination,
  scrollToBottom,
  getVisibleItemCount,
} from '../../../helpers/pagination-test-helper';

test.describe('Pagination - Infinite Scroll', () => {

  test.describe('Initial State', () => {
    test('displays initial page of items', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Infinite Scroll Initial Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-infinite-initial',
        imageCount: 15,
        pagingType: 'infinite',
        pageSize: 5,
      });

      // Verify initial visible count matches page size
      const visibleCount = await getVisibleItemCount(page);
      expect(visibleCount).toBe(5);

      // No visible pagination controls for infinite scroll
      const loadMoreButton = page.locator(PAGINATION_SELECTORS.loadMoreButton);
      await expect(loadMoreButton).not.toBeVisible();

      await page.screenshot({ path: 'test-results/pagination-infinite-initial-result.png' });
    });

    test('shows all items when total is less than page size', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Infinite Scroll Small Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-infinite-small',
        imageCount: 3,
        pagingType: 'infinite',
        pageSize: 5,
      });

      // All items should be visible
      const visibleCount = await getVisibleItemCount(page);
      expect(visibleCount).toBe(3);

      await page.screenshot({ path: 'test-results/pagination-infinite-small-result.png' });
    });
  });

  test.describe('Scroll Trigger', () => {
    test('loads more items when scrolling to bottom', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Infinite Scroll Trigger Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-infinite-scroll',
        imageCount: 15,
        pagingType: 'infinite',
        pageSize: 5,
      });

      // Verify initial count
      const initialCount = await getVisibleItemCount(page);
      expect(initialCount).toBe(5);

      // Scroll to bottom to trigger loading
      await scrollToBottom(page);

      // Verify more items loaded
      const countAfterScroll = await getVisibleItemCount(page);
      expect(countAfterScroll).toBeGreaterThan(initialCount);

      await page.screenshot({ path: 'test-results/pagination-infinite-scroll-result.png' });
    });

    test('progressively loads items with multiple scrolls', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Infinite Scroll Progressive Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-infinite-progressive',
        imageCount: 20,
        pagingType: 'infinite',
        pageSize: 5,
      });

      // Initial: 5 items
      let count = await getVisibleItemCount(page);
      expect(count).toBe(5);

      // FooGallery loads items based on viewport, not exact page increments
      // Its recursive checkBounds() may load multiple pages at once
      // First scroll should load more items (may load multiple pages at once)
      await scrollToBottom(page);
      count = await getVisibleItemCount(page);
      expect(count).toBeGreaterThan(5);

      // Continue scrolling until all items visible
      let previousCount = count;
      let maxScrolls = 5;
      while (count < 20 && maxScrolls > 0) {
        await scrollToBottom(page);
        count = await getVisibleItemCount(page);
        if (count === previousCount) break; // No new items loaded
        previousCount = count;
        maxScrolls--;
      }

      // All items should eventually be visible
      expect(count).toBe(20);

      await page.screenshot({ path: 'test-results/pagination-infinite-progressive-result.png' });
    });

    test('stops loading when all items are visible', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Infinite Scroll Complete Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-infinite-complete',
        imageCount: 10,
        pagingType: 'infinite',
        pageSize: 5,
      });

      // Initial: 5 items
      let count = await getVisibleItemCount(page);
      expect(count).toBe(5);

      // Scroll to load all
      await scrollToBottom(page);
      count = await getVisibleItemCount(page);
      expect(count).toBe(10);

      // Additional scroll should not break anything
      await scrollToBottom(page);
      count = await getVisibleItemCount(page);
      expect(count).toBe(10); // Still 10, no more to load

      await page.screenshot({ path: 'test-results/pagination-infinite-complete-result.png' });
    });
  });

  test.describe('Infinite Scroll with Different Templates', () => {
    test('works with Masonry template', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Infinite Scroll Masonry Test',
        templateSelector: 'masonry',
        screenshotPrefix: 'pagination-infinite-masonry',
        imageCount: 15,
        pagingType: 'infinite',
        pageSize: 5,
      });

      const initialCount = await getVisibleItemCount(page);
      expect(initialCount).toBe(5);

      await scrollToBottom(page);

      const countAfterScroll = await getVisibleItemCount(page);
      expect(countAfterScroll).toBeGreaterThan(initialCount);

      await page.screenshot({ path: 'test-results/pagination-infinite-masonry-result.png' });
    });

    test('works with Grid Pro template', async ({ page }) => {
      // Set a smaller viewport to ensure scrolling is required
      await page.setViewportSize({ width: 1280, height: 600 });

      await createGalleryWithPagination(page, {
        galleryName: 'Infinite Scroll Grid Pro Test',
        templateSelector: 'foogridpro',
        screenshotPrefix: 'pagination-infinite-gridpro',
        imageCount: 18,
        pagingType: 'infinite',
        pageSize: 6,
      });

      const initialCount = await getVisibleItemCount(page);
      expect(initialCount).toBe(6);

      // Grid Pro may need multiple scroll attempts due to its layout
      // Try scrolling multiple times with different methods
      let count = initialCount;
      let previousCount = 0;
      let scrollAttempts = 0;
      const maxAttempts = 5;

      while (count < 18 && scrollAttempts < maxAttempts && count !== previousCount) {
        previousCount = count;
        scrollAttempts++;

        // Method 1: Window scroll
        await page.evaluate(() => {
          window.scrollTo(0, document.body.scrollHeight);
        });
        await page.waitForTimeout(1000);

        // Method 2: Scroll the gallery into view and beyond
        const gallery = page.locator('.foogallery');
        await gallery.evaluate((el) => {
          el.scrollIntoView({ behavior: 'instant', block: 'end' });
          // Also trigger scroll event on the element itself
          el.dispatchEvent(new Event('scroll', { bubbles: true }));
        });
        await page.waitForTimeout(1000);

        // Method 3: Mouse wheel simulation at the bottom of the page
        await page.mouse.wheel(0, 1000);
        await page.waitForTimeout(1000);

        count = await getVisibleItemCount(page);
      }

      // Verify that some items were loaded (may load all at once or in batches)
      expect(count).toBeGreaterThan(initialCount);

      await page.screenshot({ path: 'test-results/pagination-infinite-gridpro-result.png' });
    });
  });

  test.describe('Infinite Scroll Edge Cases', () => {
    test('handles rapid scrolling', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Infinite Scroll Rapid Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-infinite-rapid',
        imageCount: 20,
        pagingType: 'infinite',
        pageSize: 5,
      });

      // Rapid scroll multiple times
      for (let i = 0; i < 5; i++) {
        await page.evaluate(() => {
          window.scrollTo(0, document.body.scrollHeight);
        });
        await page.waitForTimeout(300); // Short delay
      }

      // Wait for any pending loads
      await page.waitForTimeout(2000);

      // All items should eventually be visible
      const count = await getVisibleItemCount(page);
      expect(count).toBe(20);

      await page.screenshot({ path: 'test-results/pagination-infinite-rapid-result.png' });
    });

    test('works with different page sizes', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Infinite Scroll Custom Size Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-infinite-custom-size',
        imageCount: 24,
        pagingType: 'infinite',
        pageSize: 8,
      });

      // Initial count should match custom page size
      const initialCount = await getVisibleItemCount(page);
      expect(initialCount).toBe(8);

      await scrollToBottom(page);

      // Should have 16 after scroll
      const countAfterScroll = await getVisibleItemCount(page);
      expect(countAfterScroll).toBe(16);

      await page.screenshot({ path: 'test-results/pagination-infinite-custom-size-result.png' });
    });
  });
});
