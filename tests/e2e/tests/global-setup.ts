// File: tests/global-setup.ts
// Global setup for FooGallery Premium E2E Testing

import { chromium, FullConfig } from '@playwright/test';
import * as dotenv from 'dotenv';
import * as path from 'path';
import * as fs from 'fs';

// Load environment variables
dotenv.config({ path: path.resolve(__dirname, '../.env') });

const baseURL = process.env.WP_BASE_URL || 'http://localhost:8080';
const adminUser = process.env.WORDPRESS_ADMIN_USER || 'admin';
const adminPassword = process.env.WORDPRESS_ADMIN_PASSWORD || 'admin';

/**
 * Poll the WordPress site until it responds
 */
async function waitForWordPress(url: string, maxAttempts = 60): Promise<void> {
  console.log(`\n[Global Setup] Waiting for WordPress at ${url}...`);

  for (let attempt = 1; attempt <= maxAttempts; attempt++) {
    try {
      const response = await fetch(url, {
        method: 'GET',
        signal: AbortSignal.timeout(5000),
      });

      if (response.ok || response.status === 302) {
        console.log(`[Global Setup] WordPress is ready! (attempt ${attempt})`);
        return;
      }
    } catch (error) {
      // Connection refused or timeout - keep trying
    }

    if (attempt % 10 === 0) {
      console.log(`[Global Setup] Still waiting... (attempt ${attempt}/${maxAttempts})`);
    }

    await new Promise(resolve => setTimeout(resolve, 2000));
  }

  throw new Error(`WordPress not available at ${url} after ${maxAttempts} attempts`);
}

/**
 * Global setup function - runs once before all tests
 */
async function globalSetup(config: FullConfig): Promise<void> {
  console.log('\n========================================');
  console.log('[Global Setup] Starting FooGallery E2E Test Setup');
  console.log('========================================\n');

  // Ensure auth directory exists
  const authDir = path.resolve(__dirname, '../.auth');
  if (!fs.existsSync(authDir)) {
    fs.mkdirSync(authDir, { recursive: true });
  }

  // Wait for WordPress to be ready
  await waitForWordPress(baseURL);

  // Launch browser for setup
  const browser = await chromium.launch();
  const context = await browser.newContext({
    ignoreHTTPSErrors: true,
  });
  const page = await context.newPage();

  try {
    // Step 1: Navigate to WordPress login
    console.log('[Global Setup] Navigating to WordPress login...');
    await page.goto(`${baseURL}/wp-login.php`, { waitUntil: 'networkidle' });

    // Step 2: Perform login
    console.log(`[Global Setup] Logging in as ${adminUser}...`);
    await page.fill('#user_login', adminUser);
    await page.fill('#user_pass', adminPassword);
    await page.click('#wp-submit');

    // Wait for dashboard to load
    await page.waitForURL('**/wp-admin/**', { timeout: 30000 });
    console.log('[Global Setup] Login successful!');

    // Step 3: Verify FooGallery menu exists
    console.log('[Global Setup] Verifying FooGallery is active...');

    // Look for FooGallery in the admin menu
    const fooGalleryMenu = page.locator('#menu-posts-foogallery, [href*="foogallery"]').first();

    try {
      await fooGalleryMenu.waitFor({ state: 'visible', timeout: 10000 });
      console.log('[Global Setup] FooGallery menu found!');
    } catch {
      console.warn('[Global Setup] WARNING: FooGallery menu not immediately visible');
      // Take a screenshot for debugging
      await page.screenshot({ path: path.join(authDir, 'setup-debug.png') });
    }

    // Step 4: Save authentication state
    console.log('[Global Setup] Saving authentication state...');
    await context.storageState({ path: path.join(authDir, 'admin.json') });
    console.log('[Global Setup] Auth state saved to .auth/admin.json');

  } catch (error) {
    console.error('[Global Setup] Setup failed:', error);

    // Save screenshot on failure
    await page.screenshot({
      path: path.join(authDir, 'setup-failure.png'),
      fullPage: true
    });

    throw error;
  } finally {
    await browser.close();
  }

  console.log('\n========================================');
  console.log('[Global Setup] Setup Complete!');
  console.log('========================================\n');
}

export default globalSetup;
