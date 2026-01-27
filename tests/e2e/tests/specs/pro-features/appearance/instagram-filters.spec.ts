// File: tests/specs/pro-features/appearance/instagram-filters.spec.ts
// Tests for Instagram Filters feature in Appearance settings

import { test, expect } from '@playwright/test';
import {
  INSTAGRAM_FILTERS,
  InstagramFilterName,
  createGalleryWithAppearanceSettings,
  navigateToAppearanceTab,
  verifyGalleryHasClass,
  verifyGalleryDoesNotHaveClass,
  waitForGallery,
} from '../../../helpers/appearance-test-helper';

test.describe('Instagram Filters', () => {

  test.describe('Admin Settings', () => {
    test('navigates to Appearance tab', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      // Navigate to Add New Gallery
      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      // Enter gallery title
      await page.locator('#title').fill('Test Appearance Tab Navigation');

      // Select default template
      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      // Navigate to Appearance tab
      await navigateToAppearanceTab(page, 'default');

      // Verify the tab is active
      const appearanceTab = page.locator('div.foogallery-settings-container-default div.foogallery-vertical-tabs > div:nth-of-type(3)');
      await expect(appearanceTab).toHaveClass(/foogallery-tab-active/);

      await page.screenshot({ path: 'test-results/instagram-filters-appearance-tab.png' });
    });

    test('shows all 12 Instagram filter options plus none', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Instagram Filter Options');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToAppearanceTab(page, 'default');

      // Check that all 13 filter options exist (0-12)
      for (let i = 0; i <= 12; i++) {
        const filterOption = page.locator(`#FooGallerySettings_default_instagram${i}`);
        await expect(filterOption).toBeVisible();
      }

      await page.screenshot({ path: 'test-results/instagram-filters-all-options.png' });
    });

    test('default filter is none', async ({ page }) => {
      await page.setViewportSize({ width: 1932, height: 1271 });

      await page.goto('/wp-admin/post-new.php?post_type=foogallery');
      await page.waitForLoadState('domcontentloaded');

      await page.locator('#title').fill('Test Instagram Filter Default');

      const templateCard = page.locator('[data-template="default"]');
      await templateCard.waitFor({ state: 'visible', timeout: 10000 });
      await templateCard.click();

      await navigateToAppearanceTab(page, 'default');

      // Check that "None" (index 0) is selected by default
      const noneOption = page.locator('#FooGallerySettings_default_instagram0');
      await expect(noneOption).toBeChecked();

      await page.screenshot({ path: 'test-results/instagram-filters-default-none.png' });
    });
  });

  test.describe('Individual Filters', () => {
    test('applies 1977 filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram 1977 Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-1977',
          imageCount: 3,
        },
        { instagramFilter: '1977' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS['1977'].class);
    });

    test('applies Amaro filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram Amaro Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-amaro',
          imageCount: 3,
        },
        { instagramFilter: 'amaro' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.amaro.class);
    });

    test('applies Brannan filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram Brannan Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-brannan',
          imageCount: 3,
        },
        { instagramFilter: 'brannan' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.brannan.class);
    });

    test('applies Clarendon filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram Clarendon Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-clarendon',
          imageCount: 3,
        },
        { instagramFilter: 'clarendon' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.clarendon.class);
    });

    test('applies Earlybird filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram Earlybird Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-earlybird',
          imageCount: 3,
        },
        { instagramFilter: 'earlybird' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.earlybird.class);
    });

    test('applies Lo-Fi filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram Lo-Fi Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-lofi',
          imageCount: 3,
        },
        { instagramFilter: 'lofi' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.lofi.class);
    });

    test('applies PopRocket filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram PopRocket Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-poprocket',
          imageCount: 3,
        },
        { instagramFilter: 'poprocket' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.poprocket.class);
    });

    test('applies Reyes filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram Reyes Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-reyes',
          imageCount: 3,
        },
        { instagramFilter: 'reyes' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.reyes.class);
    });

    test('applies Toaster filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram Toaster Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-toaster',
          imageCount: 3,
        },
        { instagramFilter: 'toaster' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.toaster.class);
    });

    test('applies Walden filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram Walden Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-walden',
          imageCount: 3,
        },
        { instagramFilter: 'walden' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.walden.class);
    });

    test('applies X-Pro 2 filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram X-Pro 2 Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-xpro2',
          imageCount: 3,
        },
        { instagramFilter: 'xpro2' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.xpro2.class);
    });

    test('applies Xtreme filter to gallery', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram Xtreme Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-xtreme',
          imageCount: 3,
        },
        { instagramFilter: 'xtreme' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.xtreme.class);
    });
  });

  test.describe('Filter Removal', () => {
    test('gallery has no filter class when set to none', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram No Filter Test',
          templateSelector: 'default',
          screenshotPrefix: 'instagram-filter-none',
          imageCount: 3,
        },
        { instagramFilter: 'none' }
      );

      await waitForGallery(page);

      // Verify no filter classes are present
      for (const [filterName, filter] of Object.entries(INSTAGRAM_FILTERS)) {
        if (filter.class) {
          await verifyGalleryDoesNotHaveClass(page, filter.class);
        }
      }
    });
  });

  test.describe('Cross-Template Compatibility', () => {
    test('applies filter to justified layout', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram Filter Justified Test',
          templateSelector: 'justified',
          screenshotPrefix: 'instagram-filter-justified',
          imageCount: 5,
        },
        { instagramFilter: 'clarendon' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.clarendon.class);
    });

    test('applies filter to masonry layout', async ({ page }) => {
      await createGalleryWithAppearanceSettings(
        page,
        {
          galleryName: 'Instagram Filter Masonry Test',
          templateSelector: 'masonry',
          screenshotPrefix: 'instagram-filter-masonry',
          imageCount: 5,
        },
        { instagramFilter: 'walden' }
      );

      await waitForGallery(page);
      await verifyGalleryHasClass(page, INSTAGRAM_FILTERS.walden.class);
    });
  });
});
