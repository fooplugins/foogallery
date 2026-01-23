// File: tests/specs/pro-features/exif/exif-settings.spec.ts
// Tests for EXIF gallery settings configuration

import { test, expect } from '@playwright/test';
import {
  navigateToExifSettings,
  configureExifSettings,
  isExifTabVisible,
  EXIF_SELECTORS,
  EXIF_ICON_POSITIONS,
  EXIF_ICON_THEMES,
} from '../../../helpers/exif-test-helper';

test.describe('EXIF Settings Configuration', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'exif-settings';

  test.beforeEach(async ({ page }) => {
    // Set viewport size
    await page.setViewportSize({ width: 1932, height: 1271 });
  });

  test('displays EXIF tab in gallery settings', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Tab Visibility');

    // Select template (default template uses FooGallery lightbox)
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Scroll to settings
    const settingsSection = page.locator('#foogallery_settings');
    await settingsSection.scrollIntoViewIfNeeded();
    await page.waitForTimeout(500);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-exif-tab-visible.png` });

    // Verify EXIF tab is visible
    const exifTabVisible = await isExifTabVisible(page, templateSelector);
    expect(exifTabVisible).toBe(true);
  });

  test('enables EXIF feature', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Enabled');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings - enabled
    await configureExifSettings(page, templateSelector, {
      enabled: true,
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-exif-enabled.png` });

    // Verify EXIF is enabled (Enabled radio should be checked)
    // Using specific ID: #FooGallerySettings_default_exif_view_status1
    const enabledRadio = page.locator(`#FooGallerySettings_${templateSelector}_exif_view_status1`);
    await expect(enabledRadio).toBeChecked();
  });

  test('disables EXIF feature', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Disabled');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings - disabled
    await configureExifSettings(page, templateSelector, {
      enabled: false,
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-exif-disabled.png` });

    // Verify EXIF is disabled (Disabled radio should be checked)
    // Using specific ID: #FooGallerySettings_default_exif_view_status0
    const disabledRadio = page.locator(`#FooGallerySettings_${templateSelector}_exif_view_status0`);
    await expect(disabledRadio).toBeChecked();
  });

  test('shows dependent settings when enabled', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Dependent Settings Visible');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Enable EXIF
    await configureExifSettings(page, templateSelector, {
      enabled: true,
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-dependent-settings-visible.png` });

    // Verify dependent settings are visible
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);

    // Icon Position should be visible
    const iconPositionRow = templateContainer.locator('tr').filter({ hasText: 'Thumbnail Icon Position' });
    await expect(iconPositionRow).toBeVisible();

    // Icon Theme should be visible
    const iconThemeRow = templateContainer.locator('tr').filter({ hasText: 'Thumbnail Icon Theme' });
    await expect(iconThemeRow).toBeVisible();

    // Attribute layout should be visible
    const displayLayoutRow = templateContainer.locator('tr').filter({ hasText: 'Attribute layout' });
    await expect(displayLayoutRow).toBeVisible();
  });

  test('hides dependent settings when disabled', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Dependent Settings Hidden');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Disable EXIF
    await configureExifSettings(page, templateSelector, {
      enabled: false,
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-dependent-settings-hidden.png` });

    // Verify dependent settings are hidden
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);

    // Icon Position should be hidden
    const iconPositionRow = templateContainer.locator('tr').filter({ hasText: 'Thumbnail Icon Position' });
    await expect(iconPositionRow).toBeHidden();

    // Icon Theme should be hidden
    const iconThemeRow = templateContainer.locator('tr').filter({ hasText: 'Thumbnail Icon Theme' });
    await expect(iconThemeRow).toBeHidden();

    // Attribute layout should be hidden
    const displayLayoutRow = templateContainer.locator('tr').filter({ hasText: 'Attribute layout' });
    await expect(displayLayoutRow).toBeHidden();
  });

  test('selects icon position - bottom right', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Position Bottom Right');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings with bottom-right icon position (default)
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'bottomRight',
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-icon-position-bottom-right.png` });

    // Verify icon position is set
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const positionSelect = templateContainer.locator(`select[name*="${templateSelector}_exif_icon_position"]`);
    await expect(positionSelect).toHaveValue(EXIF_ICON_POSITIONS.bottomRight);
  });

  test('selects icon position - bottom left', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Position Bottom Left');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings with bottom-left icon position
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'bottomLeft',
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-icon-position-bottom-left.png` });

    // Verify icon position is set
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const positionSelect = templateContainer.locator(`select[name*="${templateSelector}_exif_icon_position"]`);
    await expect(positionSelect).toHaveValue(EXIF_ICON_POSITIONS.bottomLeft);
  });

  test('selects icon position - top right', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Position Top Right');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings with top-right icon position
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'topRight',
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-icon-position-top-right.png` });

    // Verify icon position is set
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const positionSelect = templateContainer.locator(`select[name*="${templateSelector}_exif_icon_position"]`);
    await expect(positionSelect).toHaveValue(EXIF_ICON_POSITIONS.topRight);
  });

  test('selects icon position - top left', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Position Top Left');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings with top-left icon position
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'topLeft',
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-09-icon-position-top-left.png` });

    // Verify icon position is set
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const positionSelect = templateContainer.locator(`select[name*="${templateSelector}_exif_icon_position"]`);
    await expect(positionSelect).toHaveValue(EXIF_ICON_POSITIONS.topLeft);
  });

  test('selects icon position - none', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Position None');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings with no icon position
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'none',
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-10-icon-position-none.png` });

    // Verify icon position is set to empty/none
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const positionSelect = templateContainer.locator(`select[name*="${templateSelector}_exif_icon_position"]`);
    await expect(positionSelect).toHaveValue(EXIF_ICON_POSITIONS.none);
  });

  test('selects icon theme - dark', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Theme Dark');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings with dark theme (default)
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconTheme: 'dark',
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-11-icon-theme-dark.png` });

    // Verify dark theme is selected
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const themeRow = templateContainer.locator('tr').filter({ hasText: 'Thumbnail Icon Theme' });
    const darkRadio = themeRow.locator(`input[type="radio"][value="${EXIF_ICON_THEMES.dark}"]`);
    await expect(darkRadio).toBeChecked();
  });

  test('selects icon theme - light', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Theme Light');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Configure EXIF settings with light theme
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconTheme: 'light',
    });

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-12-icon-theme-light.png` });

    // Verify light theme is selected
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const themeRow = templateContainer.locator('tr').filter({ hasText: 'Thumbnail Icon Theme' });
    const lightRadio = themeRow.locator(`input[type="radio"][value="${EXIF_ICON_THEMES.light}"]`);
    await expect(lightRadio).toBeChecked();
  });

  test('EXIF unavailable without FooGallery lightbox', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Without FooGallery Lightbox');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Scroll to settings and navigate to Lightbox tab
    const settingsSection = page.locator('#foogallery_settings');
    await settingsSection.scrollIntoViewIfNeeded();
    await page.waitForTimeout(500);

    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);

    // Click Lightbox tab
    const lightboxTab = templateContainer.locator('div.foogallery-vertical-tabs > div').filter({ hasText: 'Lightbox' });
    await lightboxTab.click();
    await page.waitForTimeout(500);

    // Change lightbox to "None" using the dropdown
    const lightboxSelect = templateContainer.locator('select[name*="lightbox"]').first();
    const options = await lightboxSelect.locator('option').allTextContents();

    // Find a non-FooGallery lightbox option (like "None" or another lightbox)
    const nonFooGalleryOption = options.find(opt => opt.toLowerCase().includes('none'));
    if (nonFooGalleryOption) {
      await lightboxSelect.selectOption({ label: nonFooGalleryOption });
      await page.waitForTimeout(500);
    }

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-13-no-foogallery-lightbox.png` });

    // Navigate to EXIF tab to check if it shows a warning
    await navigateToExifSettings(page, templateSelector);

    // The EXIF tab should still be visible but show a warning note
    // Check for the "PLEASE NOTE!" warning about lightbox requirement
    const warningNote = templateContainer.locator('h4:has-text("PLEASE NOTE!")').first();
    const exifTabVisible = await isExifTabVisible(page, templateSelector);

    // EXIF tab remains visible but shows warning when FooGallery lightbox is not enabled
    expect(exifTabVisible).toBe(true);
    await expect(warningNote).toBeVisible();
  });

  test('EXIF available with panel_support templates', async ({ page }) => {
    const sliderProTemplate = 'slider';

    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Slider PRO Template');

    // Select Slider PRO template (has panel_support)
    const templateCard = page.locator(`[data-template="${sliderProTemplate}"]`);
    if (await templateCard.isVisible()) {
      await templateCard.click();
      await page.waitForTimeout(500);

      // Scroll to settings
      const settingsSection = page.locator('#foogallery_settings');
      await settingsSection.scrollIntoViewIfNeeded();
      await page.waitForTimeout(500);

      // Screenshot
      await page.screenshot({ path: `test-results/${screenshotPrefix}-14-slider-pro-exif.png` });

      // Verify EXIF tab is visible for panel_support template
      const exifTabVisible = await isExifTabVisible(page, sliderProTemplate);
      expect(exifTabVisible).toBe(true);
    } else {
      // Skip if Slider PRO template is not available
      test.skip();
    }
  });
});
