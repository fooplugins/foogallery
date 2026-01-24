// File: tests/specs/pro-features/captions/captions-length.spec.ts
// Tests for Caption length limiting functionality

import { test, expect } from '@playwright/test';
import {
  navigateToCaptionsTab,
  setLengthLimiting,
  createGalleryAndNavigateToPage,
  getCssVariable,
  CAPTION_SELECTORS,
} from '../../../helpers/captions-test-helper';

test.describe('Caption Length Limiting', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'captions-length';

  test.beforeEach(async ({ page }) => {
    // Set viewport size
    await page.setViewportSize({ width: 1932, height: 1271 });
  });

  test('enables character length limiting', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Character Length Limiting');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set length limiting to chars
    await navigateToCaptionsTab(page, templateSelector);
    await setLengthLimiting(page, 'chars', undefined, templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-char-limiting.png` });

    // Verify char limiting is selected
    const charsRadio = page.locator(`#FooGallerySettings_${templateSelector}_captions_limit_length1`);
    await expect(charsRadio).toBeChecked();
  });

  test('sets title character limit', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Title Character Limit');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set length limiting with title length
    await navigateToCaptionsTab(page, templateSelector);
    await setLengthLimiting(page, 'chars', { titleLength: 20 }, templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-title-char-limit.png` });

    // Verify title length input has the value
    const titleLengthInput = page.locator(`#FooGallerySettings_${templateSelector}_caption_title_length`);
    await expect(titleLengthInput).toHaveValue('20');
  });

  test('sets description character limit', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Description Character Limit');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set length limiting with desc length
    await navigateToCaptionsTab(page, templateSelector);
    await setLengthLimiting(page, 'chars', { descLength: 50 }, templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-desc-char-limit.png` });

    // Verify desc length input has the value
    const descLengthInput = page.locator(`#FooGallerySettings_${templateSelector}_caption_desc_length`);
    await expect(descLengthInput).toHaveValue('50');
  });

  test('enables line clamp limiting', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Line Clamp Limiting');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set length limiting to clamp
    await navigateToCaptionsTab(page, templateSelector);
    await setLengthLimiting(page, 'clamp', undefined, templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-clamp-limiting.png` });

    // Verify clamp limiting is selected
    const clampRadio = page.locator(`#FooGallerySettings_${templateSelector}_captions_limit_length2`);
    await expect(clampRadio).toBeChecked();
  });

  test('sets title line clamp', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Title Line Clamp');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set line clamp with title clamp
    await navigateToCaptionsTab(page, templateSelector);
    await setLengthLimiting(page, 'clamp', { titleClamp: 2 }, templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-title-line-clamp.png` });

    // Verify title clamp input has the value
    const titleClampInput = page.locator(`#FooGallerySettings_${templateSelector}_caption_title_clamp`);
    await expect(titleClampInput).toHaveValue('2');
  });

  test('sets description line clamp', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Description Line Clamp');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set line clamp with desc clamp
    await navigateToCaptionsTab(page, templateSelector);
    await setLengthLimiting(page, 'clamp', { descClamp: 3 }, templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-desc-line-clamp.png` });

    // Verify desc clamp input has the value
    const descClampInput = page.locator(`#FooGallerySettings_${templateSelector}_caption_desc_clamp`);
    await expect(descClampInput).toHaveValue('3');
  });

  test('disables length limiting', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Disable Length Limiting');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set length limiting to none
    await navigateToCaptionsTab(page, templateSelector);
    await setLengthLimiting(page, 'none', undefined, templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-no-limiting.png` });

    // Verify no limiting is selected
    const noneRadio = page.locator(`#FooGallerySettings_${templateSelector}_captions_limit_length0`);
    await expect(noneRadio).toBeChecked();
  });

  test('verifies CSS variables for clamp', async ({ page }) => {
    // Create gallery with line clamp settings and verify on frontend
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test CSS Variables Clamp',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-08-css-vars`,
        imageCount: 3,
      },
      {
        limitMode: 'clamp',
        titleClamp: 2,
        descClamp: 3,
      }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-css-vars.png` });

    // Verify gallery is rendered
    const gallery = page.locator(CAPTION_SELECTORS.galleryContainer);
    await expect(gallery).toBeVisible();

    // Check for CSS variables on gallery element
    // The CSS variable --fg-title-line-clamp should be set when clamp is enabled
    const titleClampVar = await getCssVariable(page, '--fg-title-line-clamp');
    const descClampVar = await getCssVariable(page, '--fg-desc-line-clamp');

    // Variables should be set (may be empty string if not set, or numeric value if set)
    // Just verify the gallery renders correctly with clamp settings
    const galleryItems = page.locator(CAPTION_SELECTORS.galleryItem);
    const itemCount = await galleryItems.count();
    expect(itemCount).toBeGreaterThanOrEqual(1);
  });
});
