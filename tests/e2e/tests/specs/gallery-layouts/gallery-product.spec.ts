// File: tests/specs/gallery-product.spec.ts
// Test for creating a Product Gallery layout

import { test } from '@playwright/test';
import { createAndTestGallery } from '../../helpers/gallery-test-helper';

test.describe('Gallery - Product Gallery Layout', () => {
  test('create gallery, add images, publish, and view on page', async ({ page }) => {
    await createAndTestGallery(page, {
      layoutName: 'Product Gallery',
      templateSelector: 'product',
      screenshotPrefix: 'product',
    });
  });
});
