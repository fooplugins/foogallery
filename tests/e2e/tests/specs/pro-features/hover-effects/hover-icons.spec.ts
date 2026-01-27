// File: tests/specs/pro-features/hover-effects/hover-icons.spec.ts
// Tests for Hover Effect Icons and Icon Sizes

import { test, expect } from '@playwright/test';
import {
  HOVER_EFFECT_ICONS,
  HOVER_EFFECT_ICON_SIZES,
  createGalleryWithAppearanceSettings,
  navigateToHoverEffectsTab,
  verifyGalleryHasClass,
  verifyGalleryDoesNotHaveClass,
  waitForGallery,
} from '../../../helpers/appearance-test-helper';

test.describe('Hover Effect Icons', () => {

  test.describe('Admin Settings', () => {
    test('shows all icon size options', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Hover Icon Size Options');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToHoverEffectsTab(page, 'default');

      // Select normal type (index 1)
      await page.click('#FooGallerySettings_default_hover_effect_type1', { force: true });
      await page.waitForTimeout(500);

      // Check that all 5 icon size options exist (0-4)
      for (let i = 0; i <= 4; i++) {
        const sizeOption = page.locator(`#FooGallerySettings_default_hover_effect_icon_size${i}`);
        await expect(sizeOption).toBeVisible();
      }

      await page.screenshot({ path: 'test-results/hover-icons-size-options.png' });
    });
  });

  test.describe('Individual Icons', () => {
    test('applies zoom icon', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Icon Zoom Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-icon-zoom',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', icon: 'zoom' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_ICONS.zoom.class);
    });

    test('applies zoom plus icon', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Icon Zoom Plus Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-icon-zoom-plus',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', icon: 'zoomPlus' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_ICONS.zoomPlus.class);
    });

    test('removes icon when set to none', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Icon None Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-icon-none',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', icon: 'none' }
      );

      await waitForGallery(page);
      // Verify the common icon classes are not present
      await verifyGalleryDoesNotHaveClass(page, 'fg-hover-zoom');
      await verifyGalleryDoesNotHaveClass(page, 'fg-hover-zoom2');
    });
  });

  test.describe('Icon Sizes', () => {
    test('applies default icon size', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Icon Size Default Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-icon-size-default',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', icon: 'zoom', iconSize: 'default' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_ICONS.zoom.class);
      // Default size has no class - verify the other sizes are not present
      await verifyGalleryDoesNotHaveClass(page, 'fg-hover-icon-1-5');
      await verifyGalleryDoesNotHaveClass(page, 'fg-hover-icon-2');
      await verifyGalleryDoesNotHaveClass(page, 'fg-hover-icon-2-5');
      await verifyGalleryDoesNotHaveClass(page, 'fg-hover-icon-3');
    });
  });

  test.describe('Icon With Other Settings', () => {
    test('combines zoom icon with transition and scale', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Icon Combined Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-icon-combined',
          imageCount: 3,
        },
        undefined,
        {
          type: 'normal',
          icon: 'zoom',
          transition: 'fade',
          scale: 'scale',
        }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_ICONS.zoom.class);
      await verifyGalleryHasClass(page, 'fg-hover-fade');
      await verifyGalleryHasClass(page, 'fg-hover-scale');
    });
  });

  test.describe('Cross-Template Compatibility', () => {
    test('applies zoom icon to justified layout', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Icon Justified Test',
          templateSelector: 'justified',
          screenshotPrefix: 'hover-icon-justified',
          imageCount: 5,
        },
        undefined,
        { type: 'normal', icon: 'zoom' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_ICONS.zoom.class);
    });

    test('applies zoom icon to masonry layout', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Icon Masonry Test',
          templateSelector: 'masonry',
          screenshotPrefix: 'hover-icon-masonry',
          imageCount: 5,
        },
        undefined,
        { type: 'normal', icon: 'zoom' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_ICONS.zoom.class);
    });
  });
});
