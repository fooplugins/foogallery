// File: tests/specs/pro-features/appearance/themes.spec.ts
// Tests for Gallery Theme settings in Appearance tab

import { test, expect } from '@playwright/test';
import {
  THEMES,
  ThemeName,
  createGalleryWithAppearanceSettings,
  navigateToAppearanceTab,
  verifyGalleryHasClass,
  verifyGalleryDoesNotHaveClass,
  waitForGallery,
} from '../../../helpers/appearance-test-helper';

test.describe('Gallery Theme', () => {

  test.describe('Admin Settings', () => {
    test('shows all theme options', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Theme Options');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToAppearanceTab(page, 'default');

      // Check that all 3 theme options exist (0-2)
      for (let i = 0; i <= 2; i++) {
        const themeOption = page.locator(`#FooGallerySettings_default_theme${i}`);
        await expect(themeOption).toBeVisible();
      }

      await page.screenshot({ path: 'test-results/themes-all-options.png' });
    });

    test('default theme is light', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Theme Default');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToAppearanceTab(page, 'default');

      // Check that "Light" (index 0) is selected by default
      const lightOption = page.locator('#FooGallerySettings_default_theme0');
      await expect(lightOption).toBeChecked();

      await page.screenshot({ path: 'test-results/themes-default-light.png' });
    });
  });

  test.describe('Individual Themes', () => {
    test('applies light theme', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Theme Light Test',
          templateSelector: 'default',
          screenshotPrefix: 'theme-light',
          imageCount: 3,
        },
        { theme: 'light' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, THEMES.light.class);
    });

    test('applies dark theme', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Theme Dark Test',
          templateSelector: 'default',
          screenshotPrefix: 'theme-dark',
          imageCount: 3,
        },
        { theme: 'dark' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, THEMES.dark.class);
    });

    test('applies custom theme', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Theme Custom Test',
          templateSelector: 'default',
          screenshotPrefix: 'theme-custom',
          imageCount: 3,
        },
        { theme: 'custom' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, THEMES.custom.class);
    });
  });

  test.describe('Theme With Other Settings', () => {
    test('combines dark theme with Instagram filter', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Theme Dark With Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'theme-dark-filter',
          imageCount: 3,
        },
        {
          theme: 'dark',
          instagramFilter: 'clarendon',
        }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, THEMES.dark.class);
      await verifyGalleryHasClass(page, 'fg-filter-clarendon');
    });

    test('combines light theme with rounded corners and shadow', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Theme Light Combined Test',
          templateSelector: 'default',
          screenshotPrefix: 'theme-light-combined',
          imageCount: 3,
        },
        {
          theme: 'light',
          roundedCorners: 'medium',
          dropShadow: 'medium',
        }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, THEMES.light.class);
      await verifyGalleryHasClass(page, 'fg-round-medium');
      await verifyGalleryHasClass(page, 'fg-shadow-medium');
    });
  });

  test.describe('Cross-Template Compatibility', () => {
    test('applies dark theme to justified layout', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Theme Justified Test',
          templateSelector: 'justified',
          screenshotPrefix: 'theme-justified-dark',
          imageCount: 5,
        },
        { theme: 'dark' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, THEMES.dark.class);
    });

    test('applies dark theme to masonry layout', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Theme Masonry Test',
          templateSelector: 'masonry',
          screenshotPrefix: 'theme-masonry-dark',
          imageCount: 5,
        },
        { theme: 'dark' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, THEMES.dark.class);
    });
  });
});
