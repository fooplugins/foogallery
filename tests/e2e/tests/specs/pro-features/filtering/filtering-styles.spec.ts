// File: tests/specs/pro-features/filtering/filtering-styles.spec.ts
// Tests for filtering visual styles: Default, Button, Pill, Dropdown

import { test, expect } from '@playwright/test';
import {
  FILTERING_SELECTORS,
  createGalleryWithFiltering,
  waitForFiltering,
  clickFilterTag,
  selectDropdownFilter,
  getFilterTags,
  getVisibleItemCount,
} from '../../../helpers/filtering-test-helper';

test.describe('Filtering - Visual Styles', () => {

  test.describe('Default Style', () => {
    test('displays filters with default styling', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Default Style Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-style-default',
        imageCount: 10,
        filteringType: 'simple',
        filteringStyle: '',
      });

      await waitForFiltering(page);

      const filterContainer = page.locator(FILTERING_SELECTORS.container);
      await expect(filterContainer).toBeVisible();

      // Should NOT have button, pill, or dropdown classes
      await expect(filterContainer).not.toHaveClass(/fg-style-button/);
      await expect(filterContainer).not.toHaveClass(/fg-style-pill/);
      await expect(filterContainer).not.toHaveClass(/fg-style-dropdown/);

      await page.screenshot({ path: 'test-results/filtering-style-default-result.png' });
    });
  });

  test.describe('Button Style', () => {
    test('displays filters as buttons', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Button Style Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-style-button',
        imageCount: 10,
        filteringType: 'simple',
        filteringStyle: 'button',
      });

      await waitForFiltering(page);

      const filterContainer = page.locator(FILTERING_SELECTORS.container);
      await expect(filterContainer).toBeVisible();
      await expect(filterContainer).toHaveClass(/fg-style-button/);

      await page.screenshot({ path: 'test-results/filtering-style-button-result.png' });
    });

    test('displays filters as button blocks (full width)', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Button Block Style Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-style-button-block',
        imageCount: 10,
        filteringType: 'simple',
        filteringStyle: 'button-block',
      });

      await waitForFiltering(page);

      const filterContainer = page.locator(FILTERING_SELECTORS.container);
      await expect(filterContainer).toBeVisible();
      await expect(filterContainer).toHaveClass(/fg-style-button-block/);

      await page.screenshot({ path: 'test-results/filtering-style-button-block-result.png' });
    });
  });

  test.describe('Pill Style', () => {
    test('displays filters as pills', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Pill Style Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-style-pill',
        imageCount: 10,
        filteringType: 'simple',
        filteringStyle: 'pill',
      });

      await waitForFiltering(page);

      const filterContainer = page.locator(FILTERING_SELECTORS.container);
      await expect(filterContainer).toBeVisible();
      await expect(filterContainer).toHaveClass(/fg-style-pill/);

      await page.screenshot({ path: 'test-results/filtering-style-pill-result.png' });
    });

    test('displays filters as pill blocks (full width)', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Pill Block Style Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-style-pill-block',
        imageCount: 10,
        filteringType: 'simple',
        filteringStyle: 'pill-block',
      });

      await waitForFiltering(page);

      const filterContainer = page.locator(FILTERING_SELECTORS.container);
      await expect(filterContainer).toBeVisible();
      await expect(filterContainer).toHaveClass(/fg-style-pill-block/);

      await page.screenshot({ path: 'test-results/filtering-style-pill-block-result.png' });
    });
  });

  test.describe('Dropdown Style', () => {
    test('displays filters as a dropdown', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Dropdown Style Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-style-dropdown',
        imageCount: 10,
        filteringType: 'simple',
        filteringStyle: 'dropdown',
      });

      await waitForFiltering(page);

      const filterContainer = page.locator(FILTERING_SELECTORS.container);
      await expect(filterContainer).toBeVisible();
      await expect(filterContainer).toHaveClass(/fg-style-dropdown/);

      // Verify dropdown select element exists
      const dropdown = page.locator(FILTERING_SELECTORS.dropdown);
      await expect(dropdown).toBeVisible();

      await page.screenshot({ path: 'test-results/filtering-style-dropdown-result.png' });
    });

    test('filters gallery when selecting from dropdown', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Dropdown Select Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-style-dropdown-select',
        imageCount: 10,
        filteringType: 'simple',
        filteringStyle: 'dropdown',
      });

      await waitForFiltering(page);

      const dropdown = page.locator(FILTERING_SELECTORS.dropdown);
      await expect(dropdown).toBeVisible();

      // Get dropdown options
      const options = await dropdown.locator('option').allTextContents();
      const filterOption = options.find(opt => opt !== 'All' && opt.trim() !== '');

      if (filterOption) {
        const initialCount = await getVisibleItemCount(page);

        // Select the filter option
        await selectDropdownFilter(page, filterOption);

        await page.screenshot({ path: 'test-results/filtering-style-dropdown-selected.png' });
      }
    });

    test('displays filters as dropdown block (full width)', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Dropdown Block Style Test',
        templateSelector: 'justified',
        screenshotPrefix: 'filtering-style-dropdown-block',
        imageCount: 10,
        filteringType: 'simple',
        filteringStyle: 'dropdown-block',
      });

      await waitForFiltering(page);

      const filterContainer = page.locator(FILTERING_SELECTORS.container);
      await expect(filterContainer).toBeVisible();
      await expect(filterContainer).toHaveClass(/fg-style-dropdown-block/);

      await page.screenshot({ path: 'test-results/filtering-style-dropdown-block-result.png' });
    });
  });

  test.describe('Style Functionality', () => {
    test('button style filters work correctly', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Button Functionality Test',
        templateSelector: 'masonry',
        screenshotPrefix: 'filtering-style-button-func',
        imageCount: 10,
        filteringType: 'simple',
        filteringStyle: 'button',
      });

      await waitForFiltering(page);

      const filterTags = await getFilterTags(page);
      const firstTag = filterTags.find(tag => tag !== 'All');

      if (firstTag) {
        const initialCount = await getVisibleItemCount(page);

        await clickFilterTag(page, firstTag);

        // Verify filter container has button style
        const filterContainer = page.locator(FILTERING_SELECTORS.container);
        await expect(filterContainer).toHaveClass(/fg-style-button/);

        await page.screenshot({ path: 'test-results/filtering-style-button-func-result.png' });
      }
    });

    test('pill style filters work correctly', async ({ page }) => {
      await createGalleryWithFiltering(page, {
        galleryName: 'Filter Pill Functionality Test',
        templateSelector: 'masonry',
        screenshotPrefix: 'filtering-style-pill-func',
        imageCount: 10,
        filteringType: 'simple',
        filteringStyle: 'pill',
      });

      await waitForFiltering(page);

      const filterTags = await getFilterTags(page);
      const firstTag = filterTags.find(tag => tag !== 'All');

      if (firstTag) {
        await clickFilterTag(page, firstTag);

        // Verify filter container has pill style
        const filterContainer = page.locator(FILTERING_SELECTORS.container);
        await expect(filterContainer).toHaveClass(/fg-style-pill/);

        await page.screenshot({ path: 'test-results/filtering-style-pill-func-result.png' });
      }
    });
  });
});
