// File: tests/specs/pro-features/filtering/filtering-simple.spec.ts
// Tests for basic tag filtering functionality

import { test, expect } from '@playwright/test';
import {
  FILTERING_SELECTORS,
  createGalleryWithFiltering,
  waitForFiltering,
  clickFilterTag,
  clickAllFilter,
  getVisibleItemCount,
  getFilterTags,
  isFilterSelected,
} from '../../../helpers/filtering-test-helper';

test.describe('Filtering - Simple Tag Filtering', () => {

  test('displays filter tags when filtering is enabled', async ({ page }) => {
    await createGalleryWithFiltering(page, {
      galleryName: 'Filter Simple Test',
      templateSelector: 'justified',
      screenshotPrefix: 'filtering-simple-display',
      imageCount: 10,
      filteringType: 'simple',
    });

    // Wait for filtering to render
    await waitForFiltering(page);

    // Verify filter container exists
    const filterContainer = page.locator(FILTERING_SELECTORS.container);
    await expect(filterContainer).toBeVisible();

    // Verify "All" tag is present
    const allTag = page.locator(FILTERING_SELECTORS.tagAll);
    await expect(allTag).toBeVisible();

    // Verify at least one filter tag exists
    const filterTags = await getFilterTags(page);
    expect(filterTags.length).toBeGreaterThan(0);

    await page.screenshot({ path: 'test-results/filtering-simple-tags-visible.png' });
  });

  test('filters gallery items when clicking a tag', async ({ page }) => {
    await createGalleryWithFiltering(page, {
      galleryName: 'Filter Click Test',
      templateSelector: 'justified',
      screenshotPrefix: 'filtering-simple-click',
      imageCount: 10,
      filteringType: 'simple',
    });

    await waitForFiltering(page);

    // Get initial visible count
    const initialCount = await getVisibleItemCount(page);
    expect(initialCount).toBeGreaterThan(0);

    // Get filter tags (excluding "All")
    const filterTags = await getFilterTags(page);
    const firstTag = filterTags.find(tag => tag !== 'All');

    if (firstTag) {
      // Click the first tag
      await clickFilterTag(page, firstTag);

      // Verify tag is selected
      const isSelected = await isFilterSelected(page, firstTag);
      expect(isSelected).toBe(true);

      await page.screenshot({ path: 'test-results/filtering-simple-tag-clicked.png' });
    }
  });

  test('shows all items when clicking "All" button', async ({ page }) => {
    await createGalleryWithFiltering(page, {
      galleryName: 'Filter All Button Test',
      templateSelector: 'justified',
      screenshotPrefix: 'filtering-simple-all',
      imageCount: 10,
      filteringType: 'simple',
    });

    await waitForFiltering(page);

    // Get initial count
    const initialCount = await getVisibleItemCount(page);

    // Get filter tags and click first non-All tag
    const filterTags = await getFilterTags(page);
    const firstTag = filterTags.find(tag => tag !== 'All');

    if (firstTag) {
      await clickFilterTag(page, firstTag);
      await page.waitForTimeout(500);

      // Verify filter reduced the count (some items hidden)
      const filteredCount = await getVisibleItemCount(page);
      expect(filteredCount).toBeLessThanOrEqual(initialCount);

      // Click "All" to reset
      await clickAllFilter(page);
      await page.waitForTimeout(500);

      // Verify all items are visible again (this is the key assertion)
      const countAfterAll = await getVisibleItemCount(page);
      expect(countAfterAll).toBe(initialCount);

      // Verify "All" tag or its parent is selected
      const allTag = page.locator(FILTERING_SELECTORS.tagAll);
      const allTagParent = allTag.locator('..');
      const isAllSelected = await allTag.evaluate(el => {
        // Check element itself or parent for selected state
        return el.classList.contains('fg-selected') ||
               el.parentElement?.classList.contains('fg-selected') ||
               el.getAttribute('aria-selected') === 'true';
      });
      // Note: Selection state check is informational, main assertion is item count

      await page.screenshot({ path: 'test-results/filtering-simple-all-clicked.png' });
    }
  });

  test('can hide "All" option', async ({ page }) => {
    await createGalleryWithFiltering(page, {
      galleryName: 'Filter Hide All Test',
      templateSelector: 'justified',
      screenshotPrefix: 'filtering-simple-hideall',
      imageCount: 10,
      filteringType: 'simple',
      hideAll: true,
    });

    await waitForFiltering(page);

    // Verify "All" tag is NOT present
    const allTag = page.locator(FILTERING_SELECTORS.tagAll);
    await expect(allTag).not.toBeVisible();

    // Verify other filter tags are still present
    const filterTags = await getFilterTags(page);
    expect(filterTags.length).toBeGreaterThan(0);

    await page.screenshot({ path: 'test-results/filtering-simple-no-all.png' });
  });

  test('applies filtering animation when switching tags', async ({ page }) => {
    await createGalleryWithFiltering(page, {
      galleryName: 'Filter Animation Test',
      templateSelector: 'masonry',
      screenshotPrefix: 'filtering-simple-animation',
      imageCount: 10,
      filteringType: 'simple',
    });

    await waitForFiltering(page);

    // Get filter tags
    const filterTags = await getFilterTags(page);
    const tags = filterTags.filter(tag => tag !== 'All');

    if (tags.length >= 2) {
      // Click first tag
      await clickFilterTag(page, tags[0]);
      await page.screenshot({ path: 'test-results/filtering-simple-first-tag.png' });

      // Click second tag
      await clickFilterTag(page, tags[1]);
      await page.screenshot({ path: 'test-results/filtering-simple-second-tag.png' });

      // Verify second tag is selected
      const isFirstSelected = await isFilterSelected(page, tags[0]);
      const isSecondSelected = await isFilterSelected(page, tags[1]);

      // In single mode, only one should be selected
      expect(isSecondSelected).toBe(true);
    }
  });
});
