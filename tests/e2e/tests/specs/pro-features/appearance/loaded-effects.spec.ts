// File: tests/specs/pro-features/appearance/loaded-effects.spec.ts
// Tests for Loaded Effects feature in Appearance settings

import { test, expect } from '@playwright/test';
import {
  LOADED_EFFECTS,
  LoadedEffectName,
  createGalleryWithAppearanceSettings,
  navigateToAppearanceTab,
  verifyGalleryHasClass,
  verifyGalleryDoesNotHaveClass,
  waitForGallery,
} from '../../../helpers/appearance-test-helper';

test.describe('Loaded Effects', () => {

  test.describe('Admin Settings', () => {
    test('shows all 10 loaded effect options plus none', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Loaded Effect Options');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToAppearanceTab(page, 'default');

      // Check that all 11 effect options exist (0-10)
      for (let i = 0; i <= 10; i++) {
        const effectOption = page.locator(`#FooGallerySettings_default_loaded_effect${i}`);
        await expect(effectOption).toBeVisible();
      }

      await page.screenshot({ path: 'test-results/loaded-effects-all-options.png' });
    });

    test('default effect is fade-in', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Loaded Effect Default');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToAppearanceTab(page, 'default');

      // Check that "Fade In" (index 1) is selected by default
      const fadeInOption = page.locator('#FooGallerySettings_default_loaded_effect1');
      await expect(fadeInOption).toBeChecked();

      await page.screenshot({ path: 'test-results/loaded-effects-default-fadein.png' });
    });
  });

  test.describe('Individual Effects', () => {
    test('applies fade-in effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect Fade In Test',
          templateSelector: 'default',
          screenshotPrefix: 'loaded-effect-fade-in',
          imageCount: 3,
        },
        { loadedEffect: 'fadeIn' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, LOADED_EFFECTS.fadeIn.class);
    });

    test('applies slide-up effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect Slide Up Test',
          templateSelector: 'default',
          screenshotPrefix: 'loaded-effect-slide-up',
          imageCount: 3,
        },
        { loadedEffect: 'slideUp' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, LOADED_EFFECTS.slideUp.class);
    });

    test('applies slide-down effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect Slide Down Test',
          templateSelector: 'default',
          screenshotPrefix: 'loaded-effect-slide-down',
          imageCount: 3,
        },
        { loadedEffect: 'slideDown' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, LOADED_EFFECTS.slideDown.class);
    });

    test('applies slide-left effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect Slide Left Test',
          templateSelector: 'default',
          screenshotPrefix: 'loaded-effect-slide-left',
          imageCount: 3,
        },
        { loadedEffect: 'slideLeft' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, LOADED_EFFECTS.slideLeft.class);
    });

    test('applies slide-right effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect Slide Right Test',
          templateSelector: 'default',
          screenshotPrefix: 'loaded-effect-slide-right',
          imageCount: 3,
        },
        { loadedEffect: 'slideRight' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, LOADED_EFFECTS.slideRight.class);
    });

    test('applies scale-up effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect Scale Up Test',
          templateSelector: 'default',
          screenshotPrefix: 'loaded-effect-scale-up',
          imageCount: 3,
        },
        { loadedEffect: 'scaleUp' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, LOADED_EFFECTS.scaleUp.class);
    });

    test('applies swing-down effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect Swing Down Test',
          templateSelector: 'default',
          screenshotPrefix: 'loaded-effect-swing-down',
          imageCount: 3,
        },
        { loadedEffect: 'swingDown' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, LOADED_EFFECTS.swingDown.class);
    });

    test('applies drop effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect Drop Test',
          templateSelector: 'default',
          screenshotPrefix: 'loaded-effect-drop',
          imageCount: 3,
        },
        { loadedEffect: 'drop' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, LOADED_EFFECTS.drop.class);
    });

    test('applies fly effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect Fly Test',
          templateSelector: 'default',
          screenshotPrefix: 'loaded-effect-fly',
          imageCount: 3,
        },
        { loadedEffect: 'fly' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, LOADED_EFFECTS.fly.class);
    });

    test('applies flip effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect Flip Test',
          templateSelector: 'default',
          screenshotPrefix: 'loaded-effect-flip',
          imageCount: 3,
        },
        { loadedEffect: 'flip' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, LOADED_EFFECTS.flip.class);
    });
  });

  test.describe('Effect Removal', () => {
    test('removes effect when set to none', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect None Test',
          templateSelector: 'default',
          screenshotPrefix: 'loaded-effect-none',
          imageCount: 3,
        },
        { loadedEffect: 'none' }
      );

      await waitForGallery(page);

      // Verify no loaded effect classes are present
      for (const [effectName, effect] of Object.entries(LOADED_EFFECTS)) {
        if (effect.class) {
          await verifyGalleryDoesNotHaveClass(page, effect.class);
        }
      }
    });
  });

  test.describe('Cross-Template Compatibility', () => {
    test('applies effect to justified layout', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Loaded Effect Justified Test',
          templateSelector: 'justified',
          screenshotPrefix: 'loaded-effect-justified',
          imageCount: 5,
        },
        { loadedEffect: 'scaleUp' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, LOADED_EFFECTS.scaleUp.class);
    });
  });
});
