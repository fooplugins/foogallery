// File: tests/specs/gallery-justified.spec.ts
// Test for creating a Justified gallery layout

import { test } from '@playwright/test';
import { createAndTestGallery } from '../../helpers/gallery-test-helper';

test.describe('Gallery - Justified Layout', () => {
  test('create gallery, add images, publish, and view on page', async ({ page }) => {
    await createAndTestGallery(page, {
      layoutName: 'Justified',
      templateSelector: 'justified',
      screenshotPrefix: 'justified',
    });
  });
});
