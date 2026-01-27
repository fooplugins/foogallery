// File: tests/specs/pro-features/filtering/filtering-position.spec.ts
// Tests for filtering position: Top, Bottom, Both

import { test, expect } from '@playwright/test';
import {
  FILTERING_SELECTORS,
  createGalleryWithFiltering,
  waitForFiltering,
  clickFilterTag,
  getFilterTags,
} from '../../../helpers/filtering-test-helper';

test.describe('Filtering - Position', () => {

  test.describe('Top Position', () => {
    test('displays filters above the gallery', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Top Position Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-position-top',
        imageCount: 10,
        filteringType: 'simple',
        filteringPosition: 'top',
      });

      await waitForFiltering(page);

      // Get gallery and filter container positions
      const gallery = page.locator('.foogallery').first();
      const filterContainer = page.locator(FILTERING_SELECTORS.container).first();

      await expect(filterContainer).toBeVisible();

      const galleryBox = await gallery.boundingBox();
      const filterBox = await filterContainer.boundingBox();

      // Filter should be above gallery (lower Y value)
      if (galleryBox && filterBox) {
        expect(filterBox.y).toBeLessThan(galleryBox.y);
      }

      await page.screenshot({ path: 'test-results/filtering-position-top-result.png' });
    });
  });

  test.describe('Bottom Position', () => {
    test('displays filters below the gallery', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Bottom Position Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-position-bottom',
        imageCount: 10,
        filteringType: 'simple',
        filteringPosition: 'bottom',
      });

      await waitForFiltering(page);

      // Get gallery and filter container positions
      const gallery = page.locator('.foogallery').first();
      const filterContainer = page.locator(FILTERING_SELECTORS.container).first();

      await expect(filterContainer).toBeVisible();

      const galleryBox = await gallery.boundingBox();
      const filterBox = await filterContainer.boundingBox();

      // Filter should be below gallery (higher Y value)
      if (galleryBox && filterBox) {
        expect(filterBox.y).toBeGreaterThan(galleryBox.y);
      }

      await page.screenshot({ path: 'test-results/filtering-position-bottom-result.png' });
    });
  });

  test.describe('Both Positions', () => {
    test('displays filters above and below the gallery', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Both Position Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-position-both',
        imageCount: 10,
        filteringType: 'simple',
        filteringPosition: 'both',
      });

      await waitForFiltering(page);

      // Should have two filter containers
      const filterContainers = page.locator(FILTERING_SELECTORS.container);
      await expect(filterContainers).toHaveCount(2);

      const gallery = page.locator('.foogallery').first();
      const topFilter = filterContainers.first();
      const bottomFilter = filterContainers.last();

      await expect(topFilter).toBeVisible();
      await expect(bottomFilter).toBeVisible();

      const galleryBox = await gallery.boundingBox();
      const topFilterBox = await topFilter.boundingBox();
      const bottomFilterBox = await bottomFilter.boundingBox();

      if (galleryBox && topFilterBox && bottomFilterBox) {
        // Top filter above gallery
        expect(topFilterBox.y).toBeLessThan(galleryBox.y);
        // Bottom filter below gallery
        expect(bottomFilterBox.y).toBeGreaterThan(galleryBox.y);
      }

      await page.screenshot({ path: 'test-results/filtering-position-both-result.png' });
    });

    test('both filters stay synchronized when clicking', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Both Sync Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-position-both-sync',
        imageCount: 10,
        filteringType: 'simple',
        filteringPosition: 'both',
      });

      await waitForFiltering(page);

      const filterContainers = page.locator(FILTERING_SELECTORS.container);
      await expect(filterContainers).toHaveCount(2);

      // Get filter tags from top filter
      const topFilterTags = filterContainers.first().locator(FILTERING_SELECTORS.tagItem);
      const filterTags = await topFilterTags.locator(FILTERING_SELECTORS.tagLink).allTextContents();
      const firstTag = filterTags.find(tag => tag !== 'All');

      if (firstTag) {
        // Click tag in TOP filter
        const topTag = filterContainers.first().locator(FILTERING_SELECTORS.tagItem).filter({ hasText: firstTag });
        await topTag.click();
        await page.waitForTimeout(500);

        // Verify BOTH top and bottom filters show the tag as selected
        const topSelectedTag = filterContainers.first().locator(`${FILTERING_SELECTORS.tagItem}.fg-selected`).filter({ hasText: firstTag });
        const bottomSelectedTag = filterContainers.last().locator(`${FILTERING_SELECTORS.tagItem}.fg-selected`).filter({ hasText: firstTag });

        await expect(topSelectedTag).toBeVisible();
        await expect(bottomSelectedTag).toBeVisible();

        await page.screenshot({ path: 'test-results/filtering-position-both-sync-top.png' });

        // Now click "All" in BOTTOM filter
        const bottomAllTag = filterContainers.last().locator(FILTERING_SELECTORS.tagAll);
        await bottomAllTag.click();
        await page.waitForTimeout(500);

        // Verify BOTH filters show "All" as selected
        const topAllSelected = filterContainers.first().locator(`${FILTERING_SELECTORS.tagAll}.fg-selected`);
        const bottomAllSelected = filterContainers.last().locator(`${FILTERING_SELECTORS.tagAll}.fg-selected`);

        await expect(topAllSelected).toBeVisible();
        await expect(bottomAllSelected).toBeVisible();

        await page.screenshot({ path: 'test-results/filtering-position-both-sync-bottom.png' });
      }
    });
  });

  test.describe('Position with Different Styles', () => {
    test('button style works at bottom position', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Bottom Button Style Test',
        templateSelector: 'masonry',
        screenshotPrefix: 'filtering-position-bottom-button',
        imageCount: 10,
        filteringType: 'simple',
        filteringPosition: 'bottom',
        filteringStyle: 'button',
      });

      await waitForFiltering(page);

      const filterContainer = page.locator(FILTERING_SELECTORS.container);
      await expect(filterContainer).toBeVisible();
      await expect(filterContainer).toHaveClass(/fg-style-button/);

      await page.screenshot({ path: 'test-results/filtering-position-bottom-button-result.png' });
    });

    test('dropdown style works at both positions', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Both Dropdown Style Test',
        templateSelector: 'masonry',
        screenshotPrefix: 'filtering-position-both-dropdown',
        imageCount: 10,
        filteringType: 'simple',
        filteringPosition: 'both',
        filteringStyle: 'dropdown',
      });

      await waitForFiltering(page);

      const filterContainers = page.locator(FILTERING_SELECTORS.container);
      await expect(filterContainers).toHaveCount(2);

      // Both should have dropdown style
      const topDropdown = filterContainers.first().locator(FILTERING_SELECTORS.dropdown);
      const bottomDropdown = filterContainers.last().locator(FILTERING_SELECTORS.dropdown);

      await expect(topDropdown).toBeVisible();
      await expect(bottomDropdown).toBeVisible();

      await page.screenshot({ path: 'test-results/filtering-position-both-dropdown-result.png' });
    });
  });
});
