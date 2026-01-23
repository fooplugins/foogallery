// File: tests/specs/pro-features/pagination/pagination-numbered.spec.ts
// Tests for Numbered Pagination functionality

import { test, expect } from '@playwright/test';
import {
  PAGINATION_SELECTORS,
  createGalleryWithPagination,
  waitForPagination,
  clickNextPage,
  clickPrevPage,
  clickFirstPage,
  clickLastPage,
  clickPageNumber,
  getCurrentPageNumber,
  getVisibleItemCount,
  verifyPaginationPosition,
} from '../../../helpers/pagination-test-helper';

test.describe('Pagination - Numbered', () => {

  test.describe('Pagination Display', () => {
    test('displays numbered pagination controls', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Display Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-display',
        imageCount: 20,
        pagingType: 'pagination',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Verify pagination container is visible
      const pagingContainer = page.locator(PAGINATION_SELECTORS.container);
      await expect(pagingContainer).toBeVisible();

      // Verify page numbers exist
      const pageNumbers = page.locator(PAGINATION_SELECTORS.pageNumber);
      const count = await pageNumbers.count();
      expect(count).toBeGreaterThan(0);

      // Verify first page is active
      const activePage = page.locator(PAGINATION_SELECTORS.pageActive);
      await expect(activePage).toBeVisible();

      await page.screenshot({ path: 'test-results/pagination-numbered-display-result.png' });
    });

    test('shows navigation buttons (First, Prev, Next, Last)', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Nav Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-nav',
        imageCount: 20,
        pagingType: 'pagination',
        pageSize: 5,
        showFirstLast: true,
        showPrevNext: true,
      });

      await waitForPagination(page);

      // Verify all navigation buttons are visible
      const firstButton = page.locator(PAGINATION_SELECTORS.firstButton);
      const prevButton = page.locator(PAGINATION_SELECTORS.prevButton);
      const nextButton = page.locator(PAGINATION_SELECTORS.nextButton);
      const lastButton = page.locator(PAGINATION_SELECTORS.lastButton);

      await expect(firstButton).toBeVisible();
      await expect(prevButton).toBeVisible();
      await expect(nextButton).toBeVisible();
      await expect(lastButton).toBeVisible();

      await page.screenshot({ path: 'test-results/pagination-numbered-nav-result.png' });
    });

    test('hides First/Last buttons when disabled', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination No First Last Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-no-firstlast',
        imageCount: 20,
        pagingType: 'pagination',
        pageSize: 5,
        showFirstLast: false,
        showPrevNext: true,
      });

      await waitForPagination(page);

      // First/Last should not be visible
      const firstButton = page.locator(PAGINATION_SELECTORS.firstButton);
      const lastButton = page.locator(PAGINATION_SELECTORS.lastButton);

      await expect(firstButton).not.toBeVisible();
      await expect(lastButton).not.toBeVisible();

      // Prev/Next should still be visible
      const prevButton = page.locator(PAGINATION_SELECTORS.prevButton);
      const nextButton = page.locator(PAGINATION_SELECTORS.nextButton);

      await expect(prevButton).toBeVisible();
      await expect(nextButton).toBeVisible();

      await page.screenshot({ path: 'test-results/pagination-numbered-no-firstlast-result.png' });
    });
  });

  test.describe('Navigation Functionality', () => {
    test('navigates to next page', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Next Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-next',
        imageCount: 15,
        pagingType: 'pagination',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Start on page 1
      let currentPage = await getCurrentPageNumber(page);
      expect(currentPage).toBe(1);

      // Click Next
      await clickNextPage(page);

      // Should be on page 2
      currentPage = await getCurrentPageNumber(page);
      expect(currentPage).toBe(2);

      await page.screenshot({ path: 'test-results/pagination-numbered-next-result.png' });
    });

    test('navigates to previous page', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Prev Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-prev',
        imageCount: 15,
        pagingType: 'pagination',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Go to page 2 first
      await clickNextPage(page);
      let currentPage = await getCurrentPageNumber(page);
      expect(currentPage).toBe(2);

      // Click Previous
      await clickPrevPage(page);

      // Should be back on page 1
      currentPage = await getCurrentPageNumber(page);
      expect(currentPage).toBe(1);

      await page.screenshot({ path: 'test-results/pagination-numbered-prev-result.png' });
    });

    test('navigates to first page', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination First Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-first',
        imageCount: 20,
        pagingType: 'pagination',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Go to page 3
      await clickNextPage(page);
      await clickNextPage(page);
      let currentPage = await getCurrentPageNumber(page);
      expect(currentPage).toBe(3);

      // Click First
      await clickFirstPage(page);

      // Should be on page 1
      currentPage = await getCurrentPageNumber(page);
      expect(currentPage).toBe(1);

      await page.screenshot({ path: 'test-results/pagination-numbered-first-result.png' });
    });

    test('navigates to last page', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Last Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-last',
        imageCount: 20,
        pagingType: 'pagination',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Start on page 1
      let currentPage = await getCurrentPageNumber(page);
      expect(currentPage).toBe(1);

      // Click Last (should go to page 4 with 20 items / 5 per page)
      await clickLastPage(page);

      // Should be on last page
      currentPage = await getCurrentPageNumber(page);
      expect(currentPage).toBe(4);

      await page.screenshot({ path: 'test-results/pagination-numbered-last-result.png' });
    });

    test('navigates by clicking page number', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Page Click Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-pageclick',
        imageCount: 20,
        pagingType: 'pagination',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Click page 3 directly
      await clickPageNumber(page, 3);

      const currentPage = await getCurrentPageNumber(page);
      expect(currentPage).toBe(3);

      await page.screenshot({ path: 'test-results/pagination-numbered-pageclick-result.png' });
    });
  });

  test.describe('Button States', () => {
    test('disables Prev and First on first page', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination First Page State Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-firstpage-state',
        imageCount: 15,
        pagingType: 'pagination',
        pageSize: 5,
      });

      await waitForPagination(page);

      // On first page, Prev and First should be disabled
      const prevButton = page.locator(PAGINATION_SELECTORS.prevButton);
      const firstButton = page.locator(PAGINATION_SELECTORS.firstButton);

      await expect(prevButton).toHaveClass(/fg-disabled/);
      await expect(firstButton).toHaveClass(/fg-disabled/);

      await page.screenshot({ path: 'test-results/pagination-numbered-firstpage-state-result.png' });
    });

    test('disables Next and Last on last page', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Last Page State Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-lastpage-state',
        imageCount: 15,
        pagingType: 'pagination',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Go to last page
      await clickLastPage(page);

      // On last page, Next and Last should be disabled
      const nextButton = page.locator(PAGINATION_SELECTORS.nextButton);
      const lastButton = page.locator(PAGINATION_SELECTORS.lastButton);

      await expect(nextButton).toHaveClass(/fg-disabled/);
      await expect(lastButton).toHaveClass(/fg-disabled/);

      await page.screenshot({ path: 'test-results/pagination-numbered-lastpage-state-result.png' });
    });

    test('enables all buttons on middle page', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Middle Page State Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-middlepage-state',
        imageCount: 15,
        pagingType: 'pagination',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Go to middle page (page 2)
      await clickNextPage(page);

      // All buttons should be enabled
      const prevButton = page.locator(PAGINATION_SELECTORS.prevButton);
      const firstButton = page.locator(PAGINATION_SELECTORS.firstButton);
      const nextButton = page.locator(PAGINATION_SELECTORS.nextButton);
      const lastButton = page.locator(PAGINATION_SELECTORS.lastButton);

      await expect(prevButton).not.toHaveClass(/fg-disabled/);
      await expect(firstButton).not.toHaveClass(/fg-disabled/);
      await expect(nextButton).not.toHaveClass(/fg-disabled/);
      await expect(lastButton).not.toHaveClass(/fg-disabled/);

      await page.screenshot({ path: 'test-results/pagination-numbered-middlepage-state-result.png' });
    });
  });

  test.describe('Pagination Position', () => {
    test('displays at top position', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Top Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-top',
        imageCount: 15,
        pagingType: 'pagination',
        pageSize: 5,
        pagingPosition: 'top',
      });

      await waitForPagination(page);
      await verifyPaginationPosition(page, 'top');

      await page.screenshot({ path: 'test-results/pagination-numbered-top-result.png' });
    });

    test('displays at bottom position', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Bottom Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-bottom',
        imageCount: 15,
        pagingType: 'pagination',
        pageSize: 5,
        pagingPosition: 'bottom',
      });

      await waitForPagination(page);
      await verifyPaginationPosition(page, 'bottom');

      await page.screenshot({ path: 'test-results/pagination-numbered-bottom-result.png' });
    });

    test('displays at both positions', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Both Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-both',
        imageCount: 15,
        pagingType: 'pagination',
        pageSize: 5,
        pagingPosition: 'both',
      });

      await waitForPagination(page);
      await verifyPaginationPosition(page, 'both');

      await page.screenshot({ path: 'test-results/pagination-numbered-both-result.png' });
    });
  });

  test.describe('Items Per Page', () => {
    test('displays correct number of items per page', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Items Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-items',
        imageCount: 15,
        pagingType: 'pagination',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Page 1: 5 items
      let count = await getVisibleItemCount(page);
      expect(count).toBe(5);

      // Page 2: 5 items
      await clickNextPage(page);
      count = await getVisibleItemCount(page);
      expect(count).toBe(5);

      // Page 3: 5 items
      await clickNextPage(page);
      count = await getVisibleItemCount(page);
      expect(count).toBe(5);

      await page.screenshot({ path: 'test-results/pagination-numbered-items-result.png' });
    });

    test('handles uneven item distribution on last page', async ({ page }) => {
      await createGalleryWithPagination(page, {
        galleryName: 'Numbered Pagination Uneven Test',
        templateSelector: 'justified',
        screenshotPrefix: 'pagination-numbered-uneven',
        imageCount: 12, // 12 items / 5 per page = 3 pages (5, 5, 2)
        pagingType: 'pagination',
        pageSize: 5,
      });

      await waitForPagination(page);

      // Go to last page
      await clickLastPage(page);

      // Last page should have 2 items
      const count = await getVisibleItemCount(page);
      expect(count).toBe(2);

      await page.screenshot({ path: 'test-results/pagination-numbered-uneven-result.png' });
    });
  });
});
