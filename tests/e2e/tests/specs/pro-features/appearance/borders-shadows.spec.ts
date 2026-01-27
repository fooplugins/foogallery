// File: tests/specs/pro-features/appearance/borders-shadows.spec.ts
// Tests for Border Size, Rounded Corners, Drop Shadow, and Inner Shadow settings

import { test, expect } from '@playwright/test';
import {
  BORDER_SIZES,
  ROUNDED_CORNERS,
  DROP_SHADOWS,
  INNER_SHADOWS,
  createGalleryWithAppearanceSettings,
  navigateToAppearanceTab,
  verifyGalleryHasClass,
  verifyGalleryDoesNotHaveClass,
  waitForGallery,
} from '../../../helpers/appearance-test-helper';

test.describe('Border Size', () => {

  test.describe('Admin Settings', () => {
    test('shows all border size options', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Border Size Options');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToAppearanceTab(page, 'default');

      // Check that all 4 border size options exist (0-3)
      for (let i = 0; i <= 3; i++) {
        const borderOption = page.locator(`#FooGallerySettings_default_border_size${i}`);
        await expect(borderOption).toBeVisible();
      }

      await page.screenshot({ path: 'test-results/borders-all-options.png' });
    });
  });

  test.describe('Individual Border Sizes', () => {
    test('applies thin border', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Border Thin Test',
          templateSelector: 'default',
          screenshotPrefix: 'border-thin',
          imageCount: 3,
        },
        { borderSize: 'thin' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, BORDER_SIZES.thin.class);
    });

    test('applies medium border', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Border Medium Test',
          templateSelector: 'default',
          screenshotPrefix: 'border-medium',
          imageCount: 3,
        },
        { borderSize: 'medium' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, BORDER_SIZES.medium.class);
    });

    test('applies thick border', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Border Thick Test',
          templateSelector: 'default',
          screenshotPrefix: 'border-thick',
          imageCount: 3,
        },
        { borderSize: 'thick' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, BORDER_SIZES.thick.class);
    });

    test('removes border when set to none', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Border None Test',
          templateSelector: 'default',
          screenshotPrefix: 'border-none',
          imageCount: 3,
        },
        { borderSize: 'none' }
      );

      await waitForGallery(page);

      // Verify no border classes are present
      for (const [sizeName, size] of Object.entries(BORDER_SIZES)) {
        if (size.class) {
          await verifyGalleryDoesNotHaveClass(page, size.class);
        }
      }
    });
  });
});

test.describe('Rounded Corners', () => {

  test.describe('Admin Settings', () => {
    test('shows all rounded corner options', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Rounded Corners Options');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToAppearanceTab(page, 'default');

      // Check that all 5 rounded corner options exist (0-4)
      for (let i = 0; i <= 4; i++) {
        const cornerOption = page.locator(`#FooGallerySettings_default_rounded_corners${i}`);
        await expect(cornerOption).toBeVisible();
      }

      await page.screenshot({ path: 'test-results/rounded-corners-all-options.png' });
    });
  });

  test.describe('Individual Rounded Corners', () => {
    test('applies small rounded corners', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Rounded Corners Small Test',
          templateSelector: 'default',
          screenshotPrefix: 'rounded-corners-small',
          imageCount: 3,
        },
        { roundedCorners: 'small' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, ROUNDED_CORNERS.small.class);
    });

    test('applies medium rounded corners', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Rounded Corners Medium Test',
          templateSelector: 'default',
          screenshotPrefix: 'rounded-corners-medium',
          imageCount: 3,
        },
        { roundedCorners: 'medium' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, ROUNDED_CORNERS.medium.class);
    });

    test('applies large rounded corners', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Rounded Corners Large Test',
          templateSelector: 'default',
          screenshotPrefix: 'rounded-corners-large',
          imageCount: 3,
        },
        { roundedCorners: 'large' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, ROUNDED_CORNERS.large.class);
    });

    test('applies full rounded corners (circle)', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Rounded Corners Full Test',
          templateSelector: 'default',
          screenshotPrefix: 'rounded-corners-full',
          imageCount: 3,
        },
        { roundedCorners: 'full' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, ROUNDED_CORNERS.full.class);
    });

    test('removes rounded corners when set to none', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Rounded Corners None Test',
          templateSelector: 'default',
          screenshotPrefix: 'rounded-corners-none',
          imageCount: 3,
        },
        { roundedCorners: 'none' }
      );

      await waitForGallery(page);

      // Verify no rounded corner classes are present
      for (const [cornerName, corner] of Object.entries(ROUNDED_CORNERS)) {
        if (corner.class) {
          await verifyGalleryDoesNotHaveClass(page, corner.class);
        }
      }
    });
  });
});

test.describe('Drop Shadow', () => {

  test.describe('Admin Settings', () => {
    test('shows all drop shadow options', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Drop Shadow Options');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToAppearanceTab(page, 'default');

      // Check that all 5 drop shadow options exist (0-4)
      for (let i = 0; i <= 4; i++) {
        const shadowOption = page.locator(`#FooGallerySettings_default_drop_shadow${i}`);
        await expect(shadowOption).toBeVisible();
      }

      await page.screenshot({ path: 'test-results/drop-shadow-all-options.png' });
    });
  });

  test.describe('Individual Drop Shadows', () => {
    test('applies outline shadow', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Drop Shadow Outline Test',
          templateSelector: 'default',
          screenshotPrefix: 'drop-shadow-outline',
          imageCount: 3,
        },
        { dropShadow: 'outline' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, DROP_SHADOWS.outline.class);
    });

    test('applies small drop shadow', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Drop Shadow Small Test',
          templateSelector: 'default',
          screenshotPrefix: 'drop-shadow-small',
          imageCount: 3,
        },
        { dropShadow: 'small' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, DROP_SHADOWS.small.class);
    });

    test('applies medium drop shadow', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Drop Shadow Medium Test',
          templateSelector: 'default',
          screenshotPrefix: 'drop-shadow-medium',
          imageCount: 3,
        },
        { dropShadow: 'medium' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, DROP_SHADOWS.medium.class);
    });

    test('applies large drop shadow', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Drop Shadow Large Test',
          templateSelector: 'default',
          screenshotPrefix: 'drop-shadow-large',
          imageCount: 3,
        },
        { dropShadow: 'large' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, DROP_SHADOWS.large.class);
    });

    test('removes drop shadow when set to none', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Drop Shadow None Test',
          templateSelector: 'default',
          screenshotPrefix: 'drop-shadow-none',
          imageCount: 3,
        },
        { dropShadow: 'none' }
      );

      await waitForGallery(page);

      // Verify no drop shadow classes are present
      for (const [shadowName, shadow] of Object.entries(DROP_SHADOWS)) {
        if (shadow.class) {
          await verifyGalleryDoesNotHaveClass(page, shadow.class);
        }
      }
    });
  });
});

test.describe('Inner Shadow', () => {

  test.describe('Admin Settings', () => {
    test('shows all inner shadow options', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Inner Shadow Options');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToAppearanceTab(page, 'default');

      // Check that all 5 inner shadow options exist (0-4)
      for (let i = 0; i <= 4; i++) {
        const shadowOption = page.locator(`#FooGallerySettings_default_inner_shadow${i}`);
        await expect(shadowOption).toBeVisible();
      }

      await page.screenshot({ path: 'test-results/inner-shadow-all-options.png' });
    });
  });

  test.describe('Individual Inner Shadows', () => {
    test('applies outline inner shadow', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Inner Shadow Outline Test',
          templateSelector: 'default',
          screenshotPrefix: 'inner-shadow-outline',
          imageCount: 3,
        },
        { innerShadow: 'outline' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INNER_SHADOWS.outline.class);
    });

    test('applies small inner shadow', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Inner Shadow Small Test',
          templateSelector: 'default',
          screenshotPrefix: 'inner-shadow-small',
          imageCount: 3,
        },
        { innerShadow: 'small' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INNER_SHADOWS.small.class);
    });

    test('applies medium inner shadow', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Inner Shadow Medium Test',
          templateSelector: 'default',
          screenshotPrefix: 'inner-shadow-medium',
          imageCount: 3,
        },
        { innerShadow: 'medium' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INNER_SHADOWS.medium.class);
    });

    test('applies large inner shadow', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Inner Shadow Large Test',
          templateSelector: 'default',
          screenshotPrefix: 'inner-shadow-large',
          imageCount: 3,
        },
        { innerShadow: 'large' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INNER_SHADOWS.large.class);
    });

    test('removes inner shadow when set to none', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Inner Shadow None Test',
          templateSelector: 'default',
          screenshotPrefix: 'inner-shadow-none',
          imageCount: 3,
        },
        { innerShadow: 'none' }
      );

      await waitForGallery(page);

      // Verify no inner shadow classes are present
      for (const [shadowName, shadow] of Object.entries(INNER_SHADOWS)) {
        if (shadow.class) {
          await verifyGalleryDoesNotHaveClass(page, shadow.class);
        }
      }
    });
  });
});

test.describe('Combined Appearance Settings', () => {
  test('applies multiple appearance settings together', async ({ page }) => {
    await createGalleryWithAppearanceSettings(
      page,
      {
        galleryName: 'Combined Appearance Test',
        templateSelector: 'default',
        screenshotPrefix: 'combined-appearance',
        imageCount: 3,
      },
      {
        borderSize: 'medium',
        roundedCorners: 'medium',
        dropShadow: 'medium',
      }
    );

    await waitForGallery(page);
    await verifyGalleryHasClass(page, BORDER_SIZES.medium.class);
    await verifyGalleryHasClass(page, ROUNDED_CORNERS.medium.class);
    await verifyGalleryHasClass(page, DROP_SHADOWS.medium.class);
  });
});
