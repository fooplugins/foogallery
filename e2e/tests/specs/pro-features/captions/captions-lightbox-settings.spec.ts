// File: tests/specs/pro-features/captions/captions-lightbox-settings.spec.ts
// Tests for Lightbox Caption settings configuration

import { test, expect } from '@playwright/test';
import {
  navigateToLightboxInfoTab,
  setLightboxInfoEnabled,
  setLightboxCaptionPosition,
  setLightboxCaptionAlignment,
  setLightboxOverlay,
  setLightboxMobileAutohide,
  CAPTION_SELECTORS,
  LIGHTBOX_POSITIONS,
  LIGHTBOX_INFO_ENABLED,
  CAPTION_ALIGNMENTS,
} from '../../../helpers/captions-test-helper';

test.describe('Lightbox Caption Settings', () => {
  test.describe.configure({ mode: 'serial' });

  const templateSelector = 'default';
  const screenshotPrefix = 'captions-lightbox-settings';

  test.beforeEach(async ({ page }) => {
    // Set viewport size
    await page.setViewportSize({ width: 1932, height: 1271 });
  });

  test('displays lightbox caption settings', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Lightbox Caption Settings Visible');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab
    await navigateToLightboxInfoTab(page, templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-01-lightbox-settings-visible.png` });

    // Verify the lightbox info settings are visible
    const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
    const infoSection = templateContainer.locator(`div.foogallery-tab-content[data-name="${templateSelector}-lightbox-captions"]`);
    await expect(infoSection).toBeVisible();
  });

  test('enables lightbox captions', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Enable Lightbox Captions');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab and enable captions
    await navigateToLightboxInfoTab(page, templateSelector);
    await setLightboxInfoEnabled(page, 'enabled', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-02-lightbox-enabled.png` });

    // Verify enabled is selected
    const enabledRadio = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_info_enabled${LIGHTBOX_INFO_ENABLED.enabled}`);
    await expect(enabledRadio).toBeChecked();
  });

  test('disables lightbox captions', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Disable Lightbox Captions');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab and disable captions
    await navigateToLightboxInfoTab(page, templateSelector);
    await setLightboxInfoEnabled(page, 'disabled', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-03-lightbox-disabled.png` });

    // Verify disabled is selected
    const disabledRadio = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_info_enabled${LIGHTBOX_INFO_ENABLED.disabled}`);
    await expect(disabledRadio).toBeChecked();
  });

  test('hides captions initially (toggle)', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Hidden Lightbox Captions');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab and set hidden
    await navigateToLightboxInfoTab(page, templateSelector);
    await setLightboxInfoEnabled(page, 'hidden', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-04-lightbox-hidden.png` });

    // Verify hidden is selected
    const hiddenRadio = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_info_enabled${LIGHTBOX_INFO_ENABLED.hidden}`);
    await expect(hiddenRadio).toBeChecked();
  });

  test('sets caption position - bottom', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Lightbox Caption Position Bottom');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab and set position
    await navigateToLightboxInfoTab(page, templateSelector);
    await setLightboxCaptionPosition(page, 'bottom', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-05-position-bottom.png` });

    // Verify position is set
    const positionRadio = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_info_position${LIGHTBOX_POSITIONS.bottom}`);
    await expect(positionRadio).toBeChecked();
  });

  test('sets caption position - top', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Lightbox Caption Position Top');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab and set position
    await navigateToLightboxInfoTab(page, templateSelector);
    await setLightboxCaptionPosition(page, 'top', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-06-position-top.png` });

    // Verify position is set
    const positionRadio = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_info_position${LIGHTBOX_POSITIONS.top}`);
    await expect(positionRadio).toBeChecked();
  });

  test('sets caption position - left', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Lightbox Caption Position Left');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab and set position
    await navigateToLightboxInfoTab(page, templateSelector);
    await setLightboxCaptionPosition(page, 'left', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-07-position-left.png` });

    // Verify position is set
    const positionRadio = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_info_position${LIGHTBOX_POSITIONS.left}`);
    await expect(positionRadio).toBeChecked();
  });

  test('sets caption position - right', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Lightbox Caption Position Right');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab and set position
    await navigateToLightboxInfoTab(page, templateSelector);
    await setLightboxCaptionPosition(page, 'right', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-08-position-right.png` });

    // Verify position is set
    const positionRadio = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_info_position${LIGHTBOX_POSITIONS.right}`);
    await expect(positionRadio).toBeChecked();
  });

  test('sets caption alignment', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Lightbox Caption Alignment');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab and set alignment to center
    await navigateToLightboxInfoTab(page, templateSelector);
    await setLightboxCaptionAlignment(page, 'center', templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-09-alignment-center.png` });

    // Verify alignment is set
    const alignmentRadio = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_info_alignment${CAPTION_ALIGNMENTS.center}`);
    await expect(alignmentRadio).toBeChecked();
  });

  test('enables overlay mode', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Lightbox Overlay Mode Enabled');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab and enable overlay
    await navigateToLightboxInfoTab(page, templateSelector);
    await setLightboxOverlay(page, true, templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-10-overlay-enabled.png` });

    // Verify overlay is enabled
    const overlayYesRadio = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_info_overlay0`);
    await expect(overlayYesRadio).toBeChecked();
  });

  test('disables overlay mode', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Lightbox Overlay Mode Disabled');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab and disable overlay
    await navigateToLightboxInfoTab(page, templateSelector);
    await setLightboxOverlay(page, false, templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-11-overlay-disabled.png` });

    // Verify overlay is disabled
    const overlayNoRadio = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_info_overlay1`);
    await expect(overlayNoRadio).toBeChecked();
  });

  test('enables mobile autohide', async ({ page }) => {
    // Navigate to create new gallery
    await page.goto('/wp-admin/post-new.php?post_type=foogallery');
    await page.waitForLoadState('domcontentloaded');

    // Enter gallery title
    await page.locator('#title').fill('Test Lightbox Mobile Autohide');

    // Select template
    const templateCard = page.locator(`[data-template="${templateSelector}"]`);
    await templateCard.waitFor({ state: 'visible', timeout: 10000 });
    await templateCard.click();

    // Navigate to Lightbox Info tab and enable mobile autohide
    await navigateToLightboxInfoTab(page, templateSelector);
    await setLightboxMobileAutohide(page, true, templateSelector);

    // Screenshot
    await page.screenshot({ path: `test-results/${screenshotPrefix}-12-mobile-autohide.png` });

    // Verify mobile autohide is enabled
    const autohideYesRadio = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_info_autohide_mobile0`);
    await expect(autohideYesRadio).toBeChecked();
  });
});
