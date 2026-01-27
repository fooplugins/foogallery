// File: tests/specs/pro-features/hover-effects/hover-presets.spec.ts
// Tests for Hover Effect Presets (12 stylish pre-defined effects)

import { test, expect } from '@playwright/test';
import {
  HOVER_PRESETS,
  HOVER_PRESET_SIZES,
  HoverPresetName,
  HoverPresetSizeName,
  createGalleryWithAppearanceSettings,
  navigateToHoverEffectsTab,
  verifyGalleryHasClass,
  verifyGalleryDoesNotHaveClass,
  waitForGallery,
} from '../../../helpers/appearance-test-helper';

test.describe('Hover Effect Presets', () => {

  test.describe('Admin Settings', () => {
    test('navigates to Hover Effects tab', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Hover Effects Tab Navigation');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToHoverEffectsTab(page, 'default');

      // Verify the tab is active
      const hoverTab = page.locator('div.foogallery-settings-container-default div.foogallery-vertical-tabs > div:nth-of-type(4)');
      await expect(hoverTab).toHaveClass(/foogallery-tab-active/);

      await page.screenshot({ path: 'test-results/hover-presets-tab.png' });
    });

    test('shows hover effect type options', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Hover Effect Type Options');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToHoverEffectsTab(page, 'default');

      // Check that all 3 hover effect type options exist (0-2)
      for (let i = 0; i <= 2; i++) {
        const typeOption = page.locator(`#FooGallerySettings_default_hover_effect_type${i}`);
        await expect(typeOption).toBeVisible();
      }

      await page.screenshot({ path: 'test-results/hover-presets-type-options.png' });
    });

    test('shows all 12 preset options when preset type is selected', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Hover Preset Options');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToHoverEffectsTab(page, 'default');

      // Select preset type (index 2)
      await page.click('#FooGallerySettings_default_hover_effect_type2', { force: true });
      await page.waitForTimeout(500);

      // Check that all 12 preset options exist (0-11)
      for (let i = 0; i <= 11; i++) {
        const presetOption = page.locator(`#FooGallerySettings_default_hover_effect_preset${i}`);
        await expect(presetOption).toBeVisible();
      }

      await page.screenshot({ path: 'test-results/hover-presets-all-options.png' });
    });
  });

  test.describe('Type Selection', () => {
    test('switches to preset hover effect type', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Type Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-type',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'sadie' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
    });
  });

  test.describe('Individual Presets', () => {
    test('applies Brad preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Brad Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-brad',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'brad' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.brad.class);
    });

    test('applies Sadie preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Sadie Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-sadie',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'sadie' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.sadie.class);
    });

    test('applies Layla preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Layla Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-layla',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'layla' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.layla.class);
    });

    test('applies Oscar preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Oscar Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-oscar',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'oscar' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.oscar.class);
    });

    test('applies Sarah preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Sarah Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-sarah',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'sarah' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.sarah.class);
    });

    test('applies Goliath preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Goliath Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-goliath',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'goliath' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.goliath.class);
    });

    test('applies Jazz preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Jazz Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-jazz',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'jazz' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.jazz.class);
    });

    test('applies Lily preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Lily Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-lily',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'lily' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.lily.class);
    });

    test('applies Ming preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Ming Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-ming',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'ming' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.ming.class);
    });

    test('applies Selena preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Selena Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-selena',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'selena' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.selena.class);
    });

    test('applies Steve preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Steve Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-steve',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'steve' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.steve.class);
    });

    test('applies Zoe preset', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Zoe Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-zoe',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'zoe' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.zoe.class);
    });
  });

  test.describe('Preset Sizes', () => {
    test('applies small preset size', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Size Small Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-size-small',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'sadie', presetSize: 'small' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESET_SIZES.small.class);
    });

    test('applies medium preset size', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Size Medium Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-size-medium',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'sadie', presetSize: 'medium' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESET_SIZES.medium.class);
    });

    test('applies large preset size', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Size Large Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-preset-size-large',
          imageCount: 3,
        },
        undefined,
        { type: 'preset', preset: 'sadie', presetSize: 'large' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESET_SIZES.large.class);
    });
  });

  test.describe('Cross-Template Compatibility', () => {
    test('applies preset to justified layout', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Justified Test',
          templateSelector: 'justified',
          screenshotPrefix: 'hover-preset-justified',
          imageCount: 5,
        },
        undefined,
        { type: 'preset', preset: 'layla' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.layla.class);
    });

    test('applies preset to masonry layout', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Preset Masonry Test',
          templateSelector: 'masonry',
          screenshotPrefix: 'hover-preset-masonry',
          imageCount: 5,
        },
        undefined,
        { type: 'preset', preset: 'oscar' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, 'fg-preset');
      await verifyGalleryHasClass(page, HOVER_PRESETS.oscar.class);
    });
  });
});
