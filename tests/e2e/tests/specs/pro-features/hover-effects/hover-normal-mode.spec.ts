// File: tests/specs/pro-features/hover-effects/hover-normal-mode.spec.ts
// Tests for Hover Effect Normal Mode (custom icons, captions, color, scaling, transitions)

import { test, expect } from '@playwright/test';
import {
  HOVER_EFFECT_COLORS,
  HOVER_EFFECT_SCALES,
  HOVER_EFFECT_TRANSITIONS,
  createGalleryWithAppearanceSettings,
  verifyGalleryHasClass,
  verifyGalleryDoesNotHaveClass,
  waitForGallery,
} from '../../../helpers/appearance-test-helper';

test.describe('Hover Effect - Normal Mode', () => {

  test.describe('Type Selection', () => {
    test('switches to normal hover effect type', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Normal Type Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-normal-type',
          imageCount: 3,
        },
        undefined,
        { type: 'normal' }
      );

      await waitForGallery(page);
      // Normal mode doesn't add a specific class, but should not have fg-preset
      await verifyGalleryDoesNotHaveClass(page, 'fg-preset');
    });

    test('disables hover effects when set to none', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover None Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-none',
          imageCount: 3,
        },
        undefined,
        { type: 'none' }
      );

      await waitForGallery(page);
      await verifyGalleryDoesNotHaveClass(page, 'fg-preset');
      await verifyGalleryDoesNotHaveClass(page, 'fg-hover-normal');
    });
  });

  test.describe('Color Effects', () => {
    test('applies colorize effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Color Colorize Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-color-colorize',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', color: 'colorize' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_COLORS.colorize.class);
    });

    test('applies grayscale effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Color Grayscale Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-color-grayscale',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', color: 'grayscale' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_COLORS.grayscale.class);
    });

    test('removes color effect when set to none', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Color None Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-color-none',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', color: 'none' }
      );

      await waitForGallery(page);
      for (const [colorName, color] of Object.entries(HOVER_EFFECT_COLORS)) {
        if (color.class) {
          await verifyGalleryDoesNotHaveClass(page, color.class);
        }
      }
    });
  });

  test.describe('Scale Effects', () => {
    test('applies scale effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Scale Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-scale',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', scale: 'scale' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_SCALES.scale.class);
    });

    test('applies zoom effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Zoom Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-zoom',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', scale: 'zoom' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_SCALES.zoom.class);
    });

    test('applies semi-zoom effect', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Semi Zoom Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-semi-zoom',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', scale: 'semiZoom' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_SCALES.semiZoom.class);
    });

    test('removes scale effect when set to none', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Scale None Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-scale-none',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', scale: 'none' }
      );

      await waitForGallery(page);
      for (const [scaleName, scale] of Object.entries(HOVER_EFFECT_SCALES)) {
        if (scale.class) {
          await verifyGalleryDoesNotHaveClass(page, scale.class);
        }
      }
    });
  });

  test.describe('Transitions', () => {
    test('applies instant transition', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Transition Instant Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-transition-instant',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', transition: 'instant' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_TRANSITIONS.instant.class);
    });

    test('applies fade transition', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Transition Fade Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-transition-fade',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', transition: 'fade' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_TRANSITIONS.fade.class);
    });

    test('applies slide-up transition', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Transition Slide Up Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-transition-slide-up',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', transition: 'slideUp' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_TRANSITIONS.slideUp.class);
    });

    test('applies slide-down transition', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Transition Slide Down Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-transition-slide-down',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', transition: 'slideDown' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_TRANSITIONS.slideDown.class);
    });

    test('applies slide-left transition', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Transition Slide Left Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-transition-slide-left',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', transition: 'slideLeft' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_TRANSITIONS.slideLeft.class);
    });

    test('applies slide-right transition', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Transition Slide Right Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-transition-slide-right',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', transition: 'slideRight' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_TRANSITIONS.slideRight.class);
    });

    test('applies push transition', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Transition Push Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-transition-push',
          imageCount: 3,
        },
        undefined,
        { type: 'normal', transition: 'push' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_TRANSITIONS.push.class);
    });
  });

  test.describe('Combined Settings', () => {
    test('applies multiple hover effect settings together', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Combined Test',
          templateSelector: 'default',
          screenshotPrefix: 'hover-combined',
          imageCount: 3,
        },
        undefined,
        {
          type: 'normal',
          color: 'grayscale',
          scale: 'scale',
          transition: 'fade',
        }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_COLORS.grayscale.class);
      await verifyGalleryHasClass(page, HOVER_EFFECT_SCALES.scale.class);
      await verifyGalleryHasClass(page, HOVER_EFFECT_TRANSITIONS.fade.class);
    });
  });

  test.describe('Cross-Template Compatibility', () => {
    test('applies normal mode settings to justified layout', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Normal Justified Test',
          templateSelector: 'justified',
          screenshotPrefix: 'hover-normal-justified',
          imageCount: 5,
        },
        undefined,
        {
          type: 'normal',
          scale: 'zoom',
          transition: 'slideUp',
        }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_SCALES.zoom.class);
      await verifyGalleryHasClass(page, HOVER_EFFECT_TRANSITIONS.slideUp.class);
    });

    test('applies normal mode settings to masonry layout', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Hover Normal Masonry Test',
          templateSelector: 'masonry',
          screenshotPrefix: 'hover-normal-masonry',
          imageCount: 5,
        },
        undefined,
        {
          type: 'normal',
          color: 'colorize',
          transition: 'fade',
        }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, HOVER_EFFECT_COLORS.colorize.class);
      await verifyGalleryHasClass(page, HOVER_EFFECT_TRANSITIONS.fade.class);
    });
  });
});
