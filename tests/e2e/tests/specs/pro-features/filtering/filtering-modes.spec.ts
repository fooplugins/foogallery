// File: tests/specs/pro-features/filtering/filtering-modes.spec.ts
// Tests for filtering selection modes: Single, Multiple OR, Multiple AND

import { test, expect } from '@playwright/test';
import {
  FILTERING_SELECTORS,
  createGalleryWithFiltering,
  waitForFiltering,
  clickFilterTag,
  getFilterTags,
  isFilterSelected,
  getVisibleItemCount,
} from '../../../helpers/filtering-test-helper';

test.describe('Filtering - Selection Modes', () => {

  test.describe('Single Mode', () => {
    test('allows only one filter selection at a time', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Single Mode Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-mode-single',
        imageCount: 10,
        filteringType: 'simple',
        filteringMode: 'single',
      });

      await waitForFiltering(page);

      const filterTags = await getFilterTags(page);
      const tags = filterTags.filter(tag => tag !== 'All');

      if (tags.length >= 2) {
        // Click first tag
        await clickFilterTag(page, tags[0]);
        let isFirstSelected = await isFilterSelected(page, tags[0]);
        expect(isFirstSelected).toBe(true);

        // Click second tag
        await clickFilterTag(page, tags[1]);

        // Verify only second tag is selected (single mode)
        isFirstSelected = await isFilterSelected(page, tags[0]);
        const isSecondSelected = await isFilterSelected(page, tags[1]);

        expect(isFirstSelected).toBe(false);
        expect(isSecondSelected).toBe(true);

        await page.screenshot({ path: 'test-results/filtering-mode-single-result.png' });
      }
    });
  });

  test.describe('Multiple OR Mode (Union)', () => {
    test('allows multiple filter selections with OR logic', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter OR Mode Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-mode-or',
        imageCount: 10,
        filteringType: 'simple',
        filteringMode: 'union',
        filteringStyle: 'button', // Dropdown doesn't support multiple mode
      });

      await waitForFiltering(page);

      const filterTags = await getFilterTags(page);
      const tags = filterTags.filter(tag => tag !== 'All');

      if (tags.length >= 2) {
        // Click first tag
        await clickFilterTag(page, tags[0]);
        const countAfterFirst = await getVisibleItemCount(page);

        // Click second tag (should add to selection)
        await clickFilterTag(page, tags[1]);

        // Both should be selected in OR mode
        const isFirstSelected = await isFilterSelected(page, tags[0]);
        const isSecondSelected = await isFilterSelected(page, tags[1]);

        expect(isFirstSelected).toBe(true);
        expect(isSecondSelected).toBe(true);

        // Visible count should be >= first selection (OR includes more)
        const countAfterSecond = await getVisibleItemCount(page);
        expect(countAfterSecond).toBeGreaterThanOrEqual(countAfterFirst);

        await page.screenshot({ path: 'test-results/filtering-mode-or-result.png' });
      }
    });

    test('deselects tag when clicked again in OR mode', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter OR Deselect Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-mode-or-deselect',
        imageCount: 10,
        filteringType: 'simple',
        filteringMode: 'union',
        filteringStyle: 'button',
      });

      await waitForFiltering(page);

      const filterTags = await getFilterTags(page);
      const tags = filterTags.filter(tag => tag !== 'All');

      if (tags.length >= 1) {
        // Click tag to select
        await clickFilterTag(page, tags[0]);
        let isSelected = await isFilterSelected(page, tags[0]);
        expect(isSelected).toBe(true);

        // Click same tag to deselect
        await clickFilterTag(page, tags[0]);
        isSelected = await isFilterSelected(page, tags[0]);
        expect(isSelected).toBe(false);

        await page.screenshot({ path: 'test-results/filtering-mode-or-deselect-result.png' });
      }
    });
  });

  test.describe('Multiple AND Mode (Intersect)', () => {
    test('allows multiple filter selections with AND logic', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter AND Mode Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-mode-and',
        imageCount: 10,
        filteringType: 'simple',
        filteringMode: 'intersect',
        filteringStyle: 'button', // Dropdown doesn't support multiple mode
      });

      await waitForFiltering(page);

      const filterTags = await getFilterTags(page);
      const tags = filterTags.filter(tag => tag !== 'All');

      if (tags.length >= 2) {
        // Click first tag
        await clickFilterTag(page, tags[0]);
        const countAfterFirst = await getVisibleItemCount(page);

        // Click second tag (should intersect)
        await clickFilterTag(page, tags[1]);

        // Both should be selected in AND mode
        const isFirstSelected = await isFilterSelected(page, tags[0]);
        const isSecondSelected = await isFilterSelected(page, tags[1]);

        expect(isFirstSelected).toBe(true);
        expect(isSecondSelected).toBe(true);

        // Visible count should be <= first selection (AND is more restrictive)
        const countAfterSecond = await getVisibleItemCount(page);
        expect(countAfterSecond).toBeLessThanOrEqual(countAfterFirst);

        await page.screenshot({ path: 'test-results/filtering-mode-and-result.png' });
      }
    });

    test('shows fewer or equal items as more AND filters added', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter AND Restrictive Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-mode-and-restrict',
        imageCount: 10,
        filteringType: 'simple',
        filteringMode: 'intersect',
        filteringStyle: 'pill',
      });

      await waitForFiltering(page);

      const filterTags = await getFilterTags(page);
      const tags = filterTags.filter(tag => tag !== 'All');

      if (tags.length >= 3) {
        // Track visible counts as we add more filters
        const counts: number[] = [];

        // Click first tag
        await clickFilterTag(page, tags[0]);
        counts.push(await getVisibleItemCount(page));

        // Click second tag
        await clickFilterTag(page, tags[1]);
        counts.push(await getVisibleItemCount(page));

        // Click third tag
        await clickFilterTag(page, tags[2]);
        counts.push(await getVisibleItemCount(page));

        // Verify each subsequent count is <= previous (AND logic)
        for (let i = 1; i < counts.length; i++) {
          expect(counts[i]).toBeLessThanOrEqual(counts[i - 1]);
        }

        await page.screenshot({ path: 'test-results/filtering-mode-and-restrict-result.png' });
      }
    });
  });
});
