// File: tests/specs/gallery-polaroid-pro.spec.ts
// Test for creating a Polaroid PRO gallery layout

import { test } from '@playwright/test';
import { createAndTestGallery } from '../../helpers/gallery-test-helper';

test.describe('Gallery - Polaroid PRO Layout', () => {
  test('create gallery, add images, publish, and view on page', async ({ page }) => {
    await createAndTestGallery(page, {
      layoutName: 'Polaroid PRO',
      templateSelector: 'polaroid_new',
      screenshotPrefix: 'polaroid-pro',
    });
  });
});
