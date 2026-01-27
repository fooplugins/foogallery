// File: tests/specs/pro-features/captions/captions-custom.spec.ts
// Tests for Custom Caption Templates functionality

import { test, expect } from '@playwright/test';
import {
  navigateToCaptionsTab,
  enableCustomCaptionTemplate,
  createGalleryAndNavigateToPage,
  getCaptionFromGallery,
  CAPTION_SELECTORS,
} from '../../../helpers/captions-test-helper';

test.describe('Custom Caption Templates', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'captions-custom';

  test.beforeEach(async ({ page }) => {
    // Set viewport size
    await page.setViewportSize({ width: 1932, height: 1271 });
  });

  test('enables custom caption type', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Enable Custom Caption Type');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab
    await navigateToCaptionsTab(page, templateSelector);

    // Enable custom caption type
    await page.click(`#FooGallerySettings_${templateSelector}_captions_type1`, { force: true });
    await page.waitForTimeout(300);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-custom-type-enabled.png` });

    // Verify custom type is selected
    const customRadio = page.locator(`#FooGallerySettings_${templateSelector}_captions_type1`);
    await expect(customRadio).toBeChecked();
  });

  test('displays custom template textarea', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Custom Template Textarea');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and enable custom
    await navigateToCaptionsTab(page, templateSelector);
    await page.click(`#FooGallerySettings_${templateSelector}_captions_type1`, { force: true });
    await page.waitForTimeout(300);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-textarea-visible.png` });

    // Verify custom template textarea is visible
    const templateTextarea = page.locator(`#FooGallerySettings_${templateSelector}_caption_custom_template`);
    await expect(templateTextarea).toBeVisible();
  });

  test('uses {{title}} placeholder', async ({ page }) => {
    // Create gallery with custom template using title placeholder
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Title Placeholder',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-03-title-placeholder`,
        imageCount: 3,
      },
      { customTemplate: '{{title}}' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-title-placeholder.png` });

    // Verify gallery is rendered
    const gallery = page.locator(CAPTION_SELECTORS.galleryContainer);
    await expect(gallery).toBeVisible();
  });

  test('uses {{description}} placeholder', async ({ page }) => {
    // Create gallery with custom template using description placeholder
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Description Placeholder',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-04-desc-placeholder`,
        imageCount: 3,
      },
      { customTemplate: '{{description}}' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-desc-placeholder.png` });

    // Verify gallery is rendered
    const gallery = page.locator(CAPTION_SELECTORS.galleryContainer);
    await expect(gallery).toBeVisible();
  });

  test('uses {{alt}} placeholder', async ({ page }) => {
    // Create gallery with custom template using alt placeholder
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Alt Placeholder',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-05-alt-placeholder`,
        imageCount: 3,
      },
      { customTemplate: '{{alt}}' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-alt-placeholder.png` });

    // Verify gallery is rendered
    const gallery = page.locator(CAPTION_SELECTORS.galleryContainer);
    await expect(gallery).toBeVisible();
  });

  test('uses {{ID}} placeholder', async ({ page }) => {
    // Create gallery with custom template using ID placeholder
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test ID Placeholder',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-06-id-placeholder`,
        imageCount: 3,
      },
      { customTemplate: 'Image #{{ID}}' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-id-placeholder.png` });

    // Verify gallery is rendered
    const gallery = page.locator(CAPTION_SELECTORS.galleryContainer);
    await expect(gallery).toBeVisible();
  });

  test('combines multiple placeholders', async ({ page }) => {
    // Create gallery with custom template combining multiple placeholders
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Multiple Placeholders',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-07-multiple`,
        imageCount: 3,
      },
      { customTemplate: '{{title}} - {{alt}}' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-multiple-placeholders.png` });

    // Verify gallery is rendered
    const gallery = page.locator(CAPTION_SELECTORS.galleryContainer);
    await expect(gallery).toBeVisible();
  });

  test('renders custom HTML in caption', async ({ page }) => {
    // Create gallery with custom template containing HTML
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test HTML in Caption',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-08-html`,
        imageCount: 3,
      },
      { customTemplate: '<strong>{{title}}</strong><br><em>{{caption}}</em>' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-html-in-caption.png` });

    // Verify gallery is rendered
    const gallery = page.locator(CAPTION_SELECTORS.galleryContainer);
    await expect(gallery).toBeVisible();
  });

  test('clears caption with empty template', async ({ page }) => {
    // Create gallery with empty custom template
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Empty Template',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-09-empty`,
        imageCount: 3,
      },
      { customTemplate: '' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-09-empty-template.png` });

    // Get caption from gallery
    const caption = await getCaptionFromGallery(page, 0);

    // With empty template, caption should be empty
    expect(caption.title).toBe('');
  });

  test('uses {{postmeta.key}} placeholder', async ({ page }) => {
    // Create gallery with custom template using postmeta placeholder
    // Note: This tests the template syntax - actual value depends on if meta exists
    await createGalleryAndNavigateToPage(
      page,
      {
        galleryName: 'Test Postmeta Placeholder',
        templateSelector,
        screenshotPrefix: `${screenshotPrefix}-10-postmeta`,
        imageCount: 3,
      },
      { customTemplate: '{{title}} {{postmeta.custom_field}}' }
    );

    // Wait for gallery to load
    await page.waitForSelector(CAPTION_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-10-postmeta-placeholder.png` });

    // Verify gallery is rendered
    const gallery = page.locator(CAPTION_SELECTORS.galleryContainer);
    await expect(gallery).toBeVisible();
  });
});
