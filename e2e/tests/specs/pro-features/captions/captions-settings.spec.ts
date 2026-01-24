// File: tests/specs/pro-features/captions/captions-settings.spec.ts
// Tests for Caption settings configuration in gallery admin

import { test, expect } from '@playwright/test';
import {
  navigateToCaptionsTab,
  setCaptionTitleSource,
  setCaptionDescSource,
  setCaptionAlignment,
  isCaptionsTabVisible,
  CAPTION_SELECTORS,
  CAPTION_SOURCES,
  CAPTION_ALIGNMENTS,
} from '../../../helpers/captions-test-helper';

test.describe('Caption Settings Configuration', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'captions-settings';

  test.beforeEach(async ({ page }) => {
    // Set viewport size
    await page.setViewportSize({ width: 1932, height: 1271 });
  });

  test('displays Captions section in gallery settings', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Captions Tab Visibility');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Scroll to settings
    const settingsSection = page.locator('#foogallery_settings');
    await settingsSection.scrollIntoViewIfNeeded();
    await page.waitForTimeout(500);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-captions-tab-visible.png` });

    // Verify Captions tab is visible
    const captionsTabVisible = await isCaptionsTabVisible(page, templateSelector);
    expect(captionsTabVisible).toBe(true);
  });

  test('selects caption title source - title', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Caption Title Source - Title');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set title source
    await navigateToCaptionsTab(page, templateSelector);
    await setCaptionTitleSource(page, 'title', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-title-source-title.png` });

    // Verify title source is set to "title"
    const titleRadio = page.locator(`#FooGallerySettings_${templateSelector}_caption_title_source${CAPTION_SOURCES.title}`);
    await expect(titleRadio).toBeChecked();
  });

  test('selects caption title source - caption', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Caption Title Source - Caption');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set title source
    await navigateToCaptionsTab(page, templateSelector);
    await setCaptionTitleSource(page, 'caption', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-title-source-caption.png` });

    // Verify title source is set to "caption"
    const captionRadio = page.locator(`#FooGallerySettings_${templateSelector}_caption_title_source${CAPTION_SOURCES.caption}`);
    await expect(captionRadio).toBeChecked();
  });

  test('selects caption title source - alt', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Caption Title Source - Alt');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set title source
    await navigateToCaptionsTab(page, templateSelector);
    await setCaptionTitleSource(page, 'alt', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-title-source-alt.png` });

    // Verify title source is set to "alt"
    const altRadio = page.locator(`#FooGallerySettings_${templateSelector}_caption_title_source${CAPTION_SOURCES.alt}`);
    await expect(altRadio).toBeChecked();
  });

  test('selects caption title source - description', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Caption Title Source - Description');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set title source
    await navigateToCaptionsTab(page, templateSelector);
    await setCaptionTitleSource(page, 'desc', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-title-source-desc.png` });

    // Verify title source is set to "desc"
    const descRadio = page.locator(`#FooGallerySettings_${templateSelector}_caption_title_source${CAPTION_SOURCES.desc}`);
    await expect(descRadio).toBeChecked();
  });

  test('selects caption title source - none', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Caption Title Source - None');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set title source
    await navigateToCaptionsTab(page, templateSelector);
    await setCaptionTitleSource(page, 'none', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-title-source-none.png` });

    // Verify title source is set to "none"
    const noneRadio = page.locator(`#FooGallerySettings_${templateSelector}_caption_title_source${CAPTION_SOURCES.none}`);
    await expect(noneRadio).toBeChecked();
  });

  test('selects caption description source', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Caption Description Source');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set description source to "caption"
    await navigateToCaptionsTab(page, templateSelector);
    await setCaptionDescSource(page, 'caption', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-desc-source-caption.png` });

    // Verify description source is set
    const descRadio = page.locator(`#FooGallerySettings_${templateSelector}_caption_desc_source${CAPTION_SOURCES.caption}`);
    await expect(descRadio).toBeChecked();
  });

  test('selects caption alignment - left', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Caption Alignment - Left');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set alignment
    await navigateToCaptionsTab(page, templateSelector);
    await setCaptionAlignment(page, 'left', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-alignment-left.png` });

    // Verify alignment is set
    const alignmentRadio = page.locator(`#FooGallerySettings_${templateSelector}_caption_alignment${CAPTION_ALIGNMENTS.left}`);
    await expect(alignmentRadio).toBeChecked();
  });

  test('selects caption alignment - center', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Caption Alignment - Center');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set alignment
    await navigateToCaptionsTab(page, templateSelector);
    await setCaptionAlignment(page, 'center', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-09-alignment-center.png` });

    // Verify alignment is set
    const alignmentRadio = page.locator(`#FooGallerySettings_${templateSelector}_caption_alignment${CAPTION_ALIGNMENTS.center}`);
    await expect(alignmentRadio).toBeChecked();
  });

  test('selects caption alignment - right', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Caption Alignment - Right');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set alignment
    await navigateToCaptionsTab(page, templateSelector);
    await setCaptionAlignment(page, 'right', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-10-alignment-right.png` });

    // Verify alignment is set
    const alignmentRadio = page.locator(`#FooGallerySettings_${templateSelector}_caption_alignment${CAPTION_ALIGNMENTS.right}`);
    await expect(alignmentRadio).toBeChecked();
  });

  test('selects caption alignment - justify', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Caption Alignment - Justify');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab and set alignment
    await navigateToCaptionsTab(page, templateSelector);
    await setCaptionAlignment(page, 'justify', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-11-alignment-justify.png` });

    // Verify alignment is set
    const alignmentRadio = page.locator(`#FooGallerySettings_${templateSelector}_caption_alignment${CAPTION_ALIGNMENTS.justify}`);
    await expect(alignmentRadio).toBeChecked();
  });

  test('enables custom caption type', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Custom Caption Type');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Captions tab
    await navigateToCaptionsTab(page, templateSelector);

    // Click custom caption type radio
    await page.click(`#FooGallerySettings_${templateSelector}_captions_type1`, { force: true });
    await page.waitForTimeout(300);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-12-custom-type.png` });

    // Verify custom type is selected
    const customRadio = page.locator(`#FooGallerySettings_${templateSelector}_captions_type1`);
    await expect(customRadio).toBeChecked();

    // Verify custom template textarea is visible
    const templateTextarea = page.locator(`#FooGallerySettings_${templateSelector}_caption_custom_template`);
    await expect(templateTextarea).toBeVisible();
  });
});
