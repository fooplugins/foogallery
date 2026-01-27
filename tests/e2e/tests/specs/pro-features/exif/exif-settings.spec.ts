// File: tests/specs/pro-features/exif/exif-settings.spec.ts
// Tests for EXIF gallery settings configuration with frontend verification

import { test, expect } from '@playwright/test';
import {
  navigateToExifSettings,
  configureExifSettings,
  isExifTabVisible,
  addExifImagesToGallery,
  publishGalleryAndNavigateToFrontend,
  openLightbox,
  openLightboxAndShowExif,
  toggleLightboxInfo,
  closeLightbox,
  verifyExifIconPositionOnFrontend,
  verifyExifIconThemeOnFrontend,
  verifyExifIconVisibleOnItem,
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

  test('enables EXIF feature and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Enabled Frontend');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images to the gallery
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings - enabled with bottom-right position and full display layout
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'bottomRight',
      displayLayout: 'full',
    });

    // Screenshot admin settings
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-exif-enabled-admin.png` });

    // Verify EXIF is enabled (Enabled radio should be checked)
    const enabledRadio = page.locator(`#FooGallerySettings_${templateSelector}_exif_view_status1`);
    await expect(enabledRadio).toBeChecked();

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-exif-enabled-frontend.png` });

    // Verify gallery has EXIF position class
    const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
    await expect(gallery).toHaveClass(/fg-exif-bottom-right/);

    // Verify items with EXIF have the fg-item-exif class
    const itemsWithExif = page.locator(EXIF_SELECTORS.itemWithExif);
    const exifItemCount = await itemsWithExif.count();
    expect(exifItemCount).toBeGreaterThan(0);

    // Open lightbox and verify EXIF info is available
    const exifOpened = await openLightboxAndShowExif(page, 0);
    expect(exifOpened).toBe(true);

    // Verify EXIF container is visible (openLightboxAndShowExif already waits for it)
    const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
    const isVisible = await exifContainer.isVisible();
    expect(isVisible).toBe(true);

    // Screenshot lightbox with EXIF
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-exif-enabled-lightbox.png` });

    await closeLightbox(page);
  });

  test('disables EXIF feature and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Disabled Frontend');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images to the gallery
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings - disabled
    await configureExifSettings(page, templateSelector, {
      enabled: false,
    });

    // Screenshot admin settings
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-exif-disabled-admin.png` });

    // Verify EXIF is disabled (Disabled radio should be checked)
    const disabledRadio = page.locator(`#FooGallerySettings_${templateSelector}_exif_view_status0`);
    await expect(disabledRadio).toBeChecked();

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-exif-disabled-frontend.png` });

    // Verify gallery does NOT have EXIF position classes
    const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
    const hasBottomRight = await gallery.evaluate((el) => el.classList.contains('fg-exif-bottom-right'));
    const hasBottomLeft = await gallery.evaluate((el) => el.classList.contains('fg-exif-bottom-left'));
    const hasTopRight = await gallery.evaluate((el) => el.classList.contains('fg-exif-top-right'));
    const hasTopLeft = await gallery.evaluate((el) => el.classList.contains('fg-exif-top-left'));
    expect(hasBottomRight || hasBottomLeft || hasTopRight || hasTopLeft).toBe(false);

    // Verify items do NOT have fg-item-exif class when EXIF is disabled
    const itemsWithExif = page.locator(EXIF_SELECTORS.itemWithExif);
    const exifItemCount = await itemsWithExif.count();
    expect(exifItemCount).toBe(0);
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

  test('selects icon position - bottom right and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Position Bottom Right');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images to the gallery
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings with bottom-right icon position
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'bottomRight',
    });

    // Screenshot admin
    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-icon-position-bottom-right-admin.png` });

    // Verify icon position is set in admin
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const positionSelect = templateContainer.locator(`select[name*="${templateSelector}_exif_icon_position"]`);
    await expect(positionSelect).toHaveValue(EXIF_ICON_POSITIONS.bottomRight);

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-icon-position-bottom-right-frontend.png` });

    // Verify gallery has fg-exif-bottom-right class
    const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
    await expect(gallery).toHaveClass(/fg-exif-bottom-right/);
  });

  test('selects icon position - bottom left and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Position Bottom Left');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images to the gallery
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings with bottom-left icon position
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'bottomLeft',
    });

    // Screenshot admin
    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-icon-position-bottom-left-admin.png` });

    // Verify icon position is set in admin
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const positionSelect = templateContainer.locator(`select[name*="${templateSelector}_exif_icon_position"]`);
    await expect(positionSelect).toHaveValue(EXIF_ICON_POSITIONS.bottomLeft);

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-icon-position-bottom-left-frontend.png` });

    // Verify gallery has fg-exif-bottom-left class
    const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
    await expect(gallery).toHaveClass(/fg-exif-bottom-left/);
  });

  test('selects icon position - top right and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Position Top Right');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images to the gallery
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings with top-right icon position
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'topRight',
    });

    // Screenshot admin
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-icon-position-top-right-admin.png` });

    // Verify icon position is set in admin
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const positionSelect = templateContainer.locator(`select[name*="${templateSelector}_exif_icon_position"]`);
    await expect(positionSelect).toHaveValue(EXIF_ICON_POSITIONS.topRight);

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-icon-position-top-right-frontend.png` });

    // Verify gallery has fg-exif-top-right class
    const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
    await expect(gallery).toHaveClass(/fg-exif-top-right/);
  });

  test('selects icon position - top left and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Position Top Left');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images to the gallery
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings with top-left icon position
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'topLeft',
    });

    // Screenshot admin
    await page.screenshot({ path: `test-results/${screenshotPrefix}-09-icon-position-top-left-admin.png` });

    // Verify icon position is set in admin
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const positionSelect = templateContainer.locator(`select[name*="${templateSelector}_exif_icon_position"]`);
    await expect(positionSelect).toHaveValue(EXIF_ICON_POSITIONS.topLeft);

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-09-icon-position-top-left-frontend.png` });

    // Verify gallery has fg-exif-top-left class
    const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
    await expect(gallery).toHaveClass(/fg-exif-top-left/);
  });

  test('selects icon position - none and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Position None');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images to the gallery
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings with no icon position and full display layout
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'none',
      displayLayout: 'full',
    });

    // Screenshot admin
    await page.screenshot({ path: `test-results/${screenshotPrefix}-10-icon-position-none-admin.png` });

    // Verify icon position is set to empty/none in admin
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const positionSelect = templateContainer.locator(`select[name*="${templateSelector}_exif_icon_position"]`);
    await expect(positionSelect).toHaveValue(EXIF_ICON_POSITIONS.none);

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-10-icon-position-none-frontend.png` });

    // Verify gallery does NOT have any position classes
    const hasNoPositionClasses = await verifyExifIconPositionOnFrontend(page, 'none');
    expect(hasNoPositionClasses).toBe(true);

    // But EXIF should still work in lightbox
    const exifOpened = await openLightboxAndShowExif(page, 0);
    expect(exifOpened).toBe(true);

    const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
    const isVisible = await exifContainer.isVisible();
    expect(isVisible).toBe(true);

    await closeLightbox(page);
  });

  test('selects icon theme - dark and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Theme Dark');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images to the gallery
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings with dark theme
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'bottomRight',
      iconTheme: 'dark',
    });

    // Screenshot admin
    await page.screenshot({ path: `test-results/${screenshotPrefix}-11-icon-theme-dark-admin.png` });

    // Verify dark theme is selected in admin
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const themeRow = templateContainer.locator('tr').filter({ hasText: 'Thumbnail Icon Theme' });
    const darkRadio = themeRow.locator(`input[type="radio"][value="${EXIF_ICON_THEMES.dark}"]`);
    await expect(darkRadio).toBeChecked();

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-11-icon-theme-dark-frontend.png` });

    // Verify gallery has fg-exif-dark class
    const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
    await expect(gallery).toHaveClass(/fg-exif-dark/);
  });

  test('selects icon theme - light and verifies on frontend', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test EXIF Icon Theme Light');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Add EXIF images to the gallery
    await addExifImagesToGallery(page, 3);

    // Configure EXIF settings with light theme
    await configureExifSettings(page, templateSelector, {
      enabled: true,
      iconPosition: 'bottomRight',
      iconTheme: 'light',
    });

    // Screenshot admin
    await page.screenshot({ path: `test-results/${screenshotPrefix}-12-icon-theme-light-admin.png` });

    // Verify light theme is selected in admin
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const themeRow = templateContainer.locator('tr').filter({ hasText: 'Thumbnail Icon Theme' });
    const lightRadio = themeRow.locator(`input[type="radio"][value="${EXIF_ICON_THEMES.light}"]`);
    await expect(lightRadio).toBeChecked();

    // Publish and navigate to frontend
    await publishGalleryAndNavigateToFrontend(page);

    // Wait for gallery to load
    await page.waitForSelector(EXIF_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

    // Screenshot frontend
    await page.screenshot({ path: `test-results/${screenshotPrefix}-12-icon-theme-light-frontend.png` });

    // Verify gallery has fg-exif-light class
    const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
    await expect(gallery).toHaveClass(/fg-exif-light/);
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
