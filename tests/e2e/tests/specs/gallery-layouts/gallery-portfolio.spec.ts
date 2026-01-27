// File: tests/specs/gallery-portfolio.spec.ts
// Test for creating a Portfolio gallery layout

import { test } from '@playwright/test';
import { createAndTestGallery } from '../../helpers/gallery-test-helper';

test.describe('Gallery - Portfolio Layout', () => {
  test('create gallery, add images, publish, and view on page', async ({ page }) => {
    await createAndTestGallery(page, {
      layoutName: 'Portfolio',
      templateSelector: 'simple_portfolio',
      screenshotPrefix: 'portfolio',
    });
  });
});
