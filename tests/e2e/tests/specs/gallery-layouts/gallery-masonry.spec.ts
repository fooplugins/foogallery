// File: tests/specs/gallery-masonry.spec.ts
// Test for creating a Masonry gallery layout

import { test } from '@playwright/test';
import { createAndTestGallery } from '../../helpers/gallery-test-helper';

test.describe('Gallery - Masonry Layout', () => {
  test('create gallery, add images, publish, and view on page', async ({ page }) => {
    await createAndTestGallery(page, {
      layoutName: 'Masonry',
      templateSelector: 'masonry',
      screenshotPrefix: 'masonry',
    });
  });
});
