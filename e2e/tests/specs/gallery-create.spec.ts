// File: tests/specs/gallery-create.spec.ts
// Test for creating a Responsive gallery (default layout)

import { test } from '@playwright/test';
import { createAndTestGallery } from '../helpers/gallery-test-helper';

test.describe('Gallery - Responsive Layout', () => {
  test('create gallery, add images, publish, and view on page', async ({ page }) => {
    await createAndTestGallery(page, {
      layoutName: 'Responsive',
      templateSelector: 'default',
      screenshotPrefix: 'responsive',
    });
  });
});
