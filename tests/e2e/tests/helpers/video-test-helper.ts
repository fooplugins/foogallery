// File: tests/helpers/video-test-helper.ts
// Helper for video pro feature tests

import { Page, expect, Locator } from '@playwright/test';

// Video selectors - based on FooGallery video feature DOM structure
export const VIDEO_SELECTORS = {
  // Gallery container with video enabled
  galleryWithVideo: '.foogallery.fg-video-default, .foogallery[class*="fg-video-"]',
  // Video icon overlay on thumbnails
  videoIcon: '.fg-video-default, .fg-video-1, .fg-video-2, .fg-video-3, .fg-video-4',
  // Sticky video icon class
  stickyIcon: '.fg-video-sticky',
  // Video item (has video data)
  videoItem: 'a[data-video], a[href*="youtube"], a[href*="vimeo"], a[href^="#foogallery_embed"]',
  // Lightbox video player
  lightboxVideo: '.fg-panel-content iframe, .fg-panel-content video',
  // Attachment modal
  attachmentModal: '.foogallery-img-modal, .media-modal',
  // Video tab in attachment modal
  videoTab: 'label[for="foogallery-tab-video"], #foogallery-tab-video',
  // Video URL input in attachment modal
  videoUrlInput: '#attachment-details-video-url, input[name="foogallery[video_url]"]',
  // Video provider input
  videoProviderInput: '#attachment-details-video-provider, input[name="foogallery[video_provider]"]',
  // Video ID input
  videoIdInput: '#attachment-details-video-id, input[name="foogallery[video_id]"]',
  // Video type input
  videoTypeInput: '#attachment-details-video-type, input[name="foogallery[video_type]"]',
  // Gallery item in admin
  galleryItemAdmin: '.attachment, .foogallery-attachment',
  // Video item indicator in admin
  videoItemAdmin: '.subtype-foogallery',
} as const;

// Video hover icon options
export const VIDEO_HOVER_ICONS = {
  none: '',
  default: 'fg-video-default',
  icon1: 'fg-video-1',
  icon2: 'fg-video-2',
  icon3: 'fg-video-3',
  icon4: 'fg-video-4',
} as const;

// Video size options (width x height)
export const VIDEO_SIZES = {
  '640x360': '640x360',
  '854x480': '854x480',
  '960x540': '960x540',
  '1024x576': '1024x576',
  '1280x720': '1280x720',
  '1366x768': '1366x768',
  '1600x900': '1600x900',
  '1920x1080': '1920x1080',
} as const;

// Video icon size options
export const VIDEO_ICON_SIZES = {
  default: '',
  '1.5x': '48',
  '2x': '64',
  '2.5x': '80',
  '3x': '96',
} as const;

export interface VideoTestOptions {
  galleryName: string;
  templateSelector: string;
  screenshotPrefix: string;
  imageCount?: number;
}

export interface VideoSettingsOptions {
  enabled?: boolean;
  hoverIcon?: keyof typeof VIDEO_HOVER_ICONS;
  stickyIcon?: boolean;
  iconSize?: keyof typeof VIDEO_ICON_SIZES;
  videoSize?: keyof typeof VIDEO_SIZES;
  autoplay?: boolean;
}

export interface VideoUrlOptions {
  videoUrl: string;
  provider?: string;
  videoId?: string;
  videoType?: string;
}

/**
 * Navigate to the Video settings tab in gallery admin
 */
export async function navigateToVideoSettings(page: Page, templateSelector: string): Promise<void> {
  // Scroll down to Gallery Settings section
  const settingsSection = page.locator('#foogallery_settings');
  await settingsSection.scrollIntoViewIfNeeded();
  await page.waitForTimeout(500);

  // Click on Video section tab in Gallery Settings for the selected template
  // The tabs are in a navigation area with clickable spans
  const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);

  // Find the Video tab link - it's in the tab navigation
  const videoTab = templateContainer.getByText('Video', { exact: true }).first();
  await videoTab.scrollIntoViewIfNeeded();
  await videoTab.click({ force: true });
  await page.waitForTimeout(500);

  // Wait for the Video settings table to be visible
  const videoSettingsTable = templateContainer.locator('tr').filter({ hasText: 'Enable Video' });
  await videoSettingsTable.waitFor({ state: 'visible', timeout: 10000 });
}

/**
 * Configure video settings in gallery admin
 */
export async function configureVideoSettings(page: Page, templateSelector: string, options: VideoSettingsOptions): Promise<void> {
  const {
    enabled = true,
    hoverIcon,
    stickyIcon,
    iconSize,
    videoSize,
    autoplay
  } = options;

  // Navigate to Video tab first
  await navigateToVideoSettings(page, templateSelector);

  const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);

  // Enable/disable video
  if (!enabled) {
    // Click "Disabled" radio button
    const disabledRadio = templateContainer.locator(`input[name*="${templateSelector}_video_enabled"][value="disabled"]`);
    await disabledRadio.click();
    await page.waitForTimeout(300);
    return; // No need to configure other settings if disabled
  } else {
    // Ensure "Enabled" is selected (empty value means enabled)
    const enabledRadio = templateContainer.locator(`input[name*="${templateSelector}_video_enabled"][value=""]`);
    await enabledRadio.click();
    await page.waitForTimeout(300);
  }

  // Set hover icon if specified
  if (hoverIcon !== undefined) {
    // Video hover icons use htmlicon type - the radio buttons are hidden
    // Click on the container element that contains the icon
    const iconValue = VIDEO_HOVER_ICONS[hoverIcon];
    // Use JavaScript to click the container for the radio button
    await page.evaluate((iconVal) => {
      // Find the radio input with the matching value
      const radio = document.querySelector(`input[name*="video_hover_icon"][value="${iconVal}"]`) as HTMLInputElement;
      if (radio) {
        // Click the parent container (which has the visual representation)
        const container = radio.closest('div[data-foogallery-value]') || radio.parentElement;
        if (container) {
          (container as HTMLElement).click();
        } else {
          radio.click();
        }
      }
    }, iconValue);
    await page.waitForTimeout(300);
  }

  // Set sticky icon if specified
  if (stickyIcon !== undefined) {
    // Sticky icon uses standard radio buttons with Yes/No labels
    const stickyRow = templateContainer.locator('tr').filter({ hasText: 'Sticky Video Icon' });
    const stickyLabel = stickyIcon ? 'Yes' : 'No';
    const stickyOption = stickyRow.locator('td').getByText(stickyLabel, { exact: true });
    await stickyOption.click();
    await page.waitForTimeout(300);
  }

  // Set icon size if specified
  if (iconSize !== undefined) {
    const sizeValue = VIDEO_ICON_SIZES[iconSize];
    const sizeRadio = templateContainer.locator(`input[name*="${templateSelector}_video_icon_size"][value="${sizeValue}"]`);
    await sizeRadio.click();
    await page.waitForTimeout(300);
  }

  // Set lightbox video size if specified
  if (videoSize !== undefined) {
    const sizeSelect = templateContainer.locator(`select[name*="${templateSelector}_video_size"]`);
    await sizeSelect.selectOption(VIDEO_SIZES[videoSize]);
    await page.waitForTimeout(300);
  }

  // Set autoplay if specified
  if (autoplay !== undefined) {
    // Autoplay uses standard radio buttons with Yes/No labels
    const autoplayRow = templateContainer.locator('tr').filter({ hasText: 'Lightbox Autoplay' });
    const autoplayLabel = autoplay ? 'Yes' : 'No';
    const autoplayOption = autoplayRow.locator('td').getByText(autoplayLabel, { exact: true });
    await autoplayOption.click();
    await page.waitForTimeout(300);
  }
}

/**
 * Open the "Add From Another Source" dropdown menu
 * Based on Chrome DevTools recording
 */
export async function openAddSourceDropdown(page: Page): Promise<void> {
  // Click the button in foogallery-items-add section
  const addButton = page.locator('div.foogallery-items-add button.button-primary');
  await addButton.click();
  await page.waitForTimeout(300);
}

/**
 * Import videos using the import option from dropdown
 * Based on Chrome DevTools recording for self-hosted videos
 */
export async function importVideosFromMediaLibrary(page: Page, videoCount: number = 1): Promise<void> {
  // Open the "Add From Another Source" dropdown
  await openAddSourceDropdown(page);

  // Click on "Import" menu item
  await page.locator('#menu-item-import').click();
  await page.waitForTimeout(500);

  // Wait for the media frame to be ready
  await page.waitForSelector('.media-frame-content', { state: 'visible', timeout: 10000 });

  // Click Select Videos button if present
  const selectVideosBtn = page.locator('div.media-frame-content button');
  if (await selectVideosBtn.count() > 0) {
    await selectVideosBtn.click();
    await page.waitForTimeout(500);
  }

  // Select videos from the library
  const videoItems = page.locator('.attachments-browser .attachment');
  await videoItems.first().waitFor({ state: 'visible', timeout: 10000 });

  // Select the specified number of videos
  for (let i = 0; i < videoCount; i++) {
    await videoItems.nth(i).click();
    await page.waitForTimeout(200);
  }

  // Click Select button
  const selectButton = page.locator('button.media-button-select');
  await selectButton.click();
  await page.waitForTimeout(500);

  // Click Import button to add videos to gallery
  const importButton = page.locator('button.media-button-import');
  if (await importButton.isVisible()) {
    await importButton.click();
    await page.waitForTimeout(500);
  }
}

/**
 * Import YouTube video via URL
 * Based on Chrome DevTools recording
 */
export async function importYouTubeVideo(page: Page, videoUrl: string): Promise<void> {
  // Open the "Add From Another Source" dropdown
  await openAddSourceDropdown(page);

  // Click on Import menu item (handles both YouTube and other sources)
  await page.locator('#menu-item-import').click();
  await page.waitForTimeout(500);

  // Enter the YouTube URL in the query input - use type() to trigger input events
  const urlInput = page.locator('div.fgi-region-query input');
  await urlInput.waitFor({ state: 'visible', timeout: 10000 });
  await urlInput.click();
  await urlInput.type(videoUrl, { delay: 10 }); // Type with small delay to trigger events
  await page.waitForTimeout(500);

  // Wait for the import button to become enabled (video is being validated)
  const importButton = page.locator('button.media-button-import');
  await importButton.waitFor({ state: 'visible', timeout: 10000 });

  // Wait for button to be enabled (not disabled)
  await page.waitForFunction(
    () => {
      const btn = document.querySelector('button.media-button-import') as HTMLButtonElement;
      return btn && !btn.disabled;
    },
    { timeout: 30000 }
  );

  // Click import button
  await importButton.click();
  await page.waitForTimeout(2000); // Wait for video to be fetched and added

  // Close the import modal by clicking Done button
  await page.locator('div.media-frame-tab-panel button.button-primary').click();
  await page.waitForTimeout(500);
}

/**
 * Import multiple YouTube videos via URLs
 * Based on Chrome DevTools recording
 */
export async function importMultipleYouTubeVideos(page: Page, videoUrls: string[]): Promise<void> {
  // Open the "Add From Another Source" dropdown
  await openAddSourceDropdown(page);

  // Click on Import menu item
  await page.locator('#menu-item-import').click();
  await page.waitForTimeout(500);

  for (let i = 0; i < videoUrls.length; i++) {
    const urlInput = page.locator('div.fgi-region-query input');
    await urlInput.waitFor({ state: 'visible', timeout: 10000 });

    // Clear the input first (for subsequent videos)
    await urlInput.click();
    await urlInput.clear();

    // Type URL to trigger input events
    await urlInput.type(videoUrls[i], { delay: 10 });
    await page.waitForTimeout(500);

    // Wait for the import button to become enabled
    await page.waitForFunction(
      () => {
        const btn = document.querySelector('button.media-button-import') as HTMLButtonElement;
        return btn && !btn.disabled;
      },
      { timeout: 30000 }
    );

    // Click import button
    await page.locator('button.media-button-import').click();
    await page.waitForTimeout(2000); // Wait for video to be fetched

    // If not the last video, click "Add Another" button to continue
    if (i < videoUrls.length - 1) {
      await page.locator('div.media-frame-tab-panel button.button-secondary').click();
      await page.waitForTimeout(500);
    }
  }

  // Close the import modal by clicking Done button
  await page.locator('div.media-frame-tab-panel button.button-primary').click();
  await page.waitForTimeout(500);
}

/**
 * Import Vimeo video via URL
 * Uses the same import flow as YouTube
 */
export async function importVimeoVideo(page: Page, videoUrl: string): Promise<void> {
  // Open the "Add From Another Source" dropdown
  await openAddSourceDropdown(page);

  // Click on Import menu item
  await page.locator('#menu-item-import').click();
  await page.waitForTimeout(500);

  // Enter the Vimeo URL in the query input - use type() to trigger events
  const urlInput = page.locator('div.fgi-region-query input');
  await urlInput.waitFor({ state: 'visible', timeout: 10000 });
  await urlInput.click();
  await urlInput.type(videoUrl, { delay: 10 });
  await page.waitForTimeout(500);

  // Wait for the import button to become enabled
  await page.waitForFunction(
    () => {
      const btn = document.querySelector('button.media-button-import') as HTMLButtonElement;
      return btn && !btn.disabled;
    },
    { timeout: 30000 }
  );

  // Click import button
  await page.locator('button.media-button-import').click();
  await page.waitForTimeout(2000); // Wait for video to be fetched

  // Close the import modal by clicking Done button
  await page.locator('div.media-frame-tab-panel button.button-primary').click();
  await page.waitForTimeout(500);
}

/**
 * Open attachment modal for a specific gallery item (for viewing/editing metadata)
 */
export async function openAttachmentModal(page: Page, itemIndex: number = 0): Promise<void> {
  // Find the gallery item in admin and click to open modal
  const galleryItems = page.locator('.foogallery-attachments-list .attachment, #foogallery_items .attachment');
  await galleryItems.nth(itemIndex).click();
  await page.waitForTimeout(500);

  // Wait for the FooGallery-specific modal to open
  await page.waitForSelector('.foogallery-img-modal, .media-modal-content', { state: 'visible', timeout: 10000 });
}

/**
 * Navigate to Video tab in the attachment modal (if it exists)
 */
export async function navigateToVideoTabInModal(page: Page): Promise<void> {
  // Try different selectors for the video tab
  const videoTabSelectors = [
    'label[for="foogallery-tab-video"]',
    '#foogallery-tab-video',
    '.foogallery-tabs label:has-text("Video")',
    'a[href="#foogallery-video-tab"]',
    '.media-menu-item:has-text("Video")'
  ];

  for (const selector of videoTabSelectors) {
    const tab = page.locator(selector).first();
    if (await tab.isVisible()) {
      await tab.click();
      await page.waitForTimeout(300);
      return;
    }
  }

  // If no video tab found, the modal might not support video editing
  console.log('Video tab not found in attachment modal');
}

/**
 * Set video URL on an attachment via the modal
 * Note: FooGallery may use import flow instead of modal editing
 */
export async function setVideoUrlOnAttachment(page: Page, itemIndex: number, options: VideoUrlOptions): Promise<void> {
  const { videoUrl, provider, videoId, videoType } = options;

  // Open the attachment modal
  await openAttachmentModal(page, itemIndex);

  // Try to navigate to Video tab
  await navigateToVideoTabInModal(page);

  // Try to fill in video URL if input exists
  const urlInput = page.locator(VIDEO_SELECTORS.videoUrlInput).first();
  if (await urlInput.isVisible()) {
    await urlInput.fill(videoUrl);
    await page.waitForTimeout(300);

    // Fill in provider if specified
    if (provider) {
      const providerInput = page.locator(VIDEO_SELECTORS.videoProviderInput).first();
      if (await providerInput.isVisible()) {
        await providerInput.fill(provider);
      }
    }

    // Fill in video ID if specified
    if (videoId) {
      const idInput = page.locator(VIDEO_SELECTORS.videoIdInput).first();
      if (await idInput.isVisible()) {
        await idInput.fill(videoId);
      }
    }

    // Fill in video type if specified
    if (videoType) {
      const typeInput = page.locator(VIDEO_SELECTORS.videoTypeInput).first();
      if (await typeInput.isVisible()) {
        await typeInput.fill(videoType);
      }
    }
  }

  // Close the modal
  await page.keyboard.press('Escape');
  await page.waitForTimeout(500);
}

/**
 * Clear video URL from an attachment
 */
export async function clearVideoUrlFromAttachment(page: Page, itemIndex: number): Promise<void> {
  await openAttachmentModal(page, itemIndex);
  await navigateToVideoTabInModal(page);

  // Clear the video URL if visible
  const urlInput = page.locator(VIDEO_SELECTORS.videoUrlInput).first();
  if (await urlInput.isVisible()) {
    await urlInput.fill('');
  }

  // Close modal
  await page.keyboard.press('Escape');
  await page.waitForTimeout(500);
}

/**
 * Verify video icon is displayed on frontend gallery
 */
export async function verifyVideoIconOnFrontend(page: Page, options: {
  visible: boolean;
  iconClass?: string;
  sticky?: boolean;
}): Promise<void> {
  const { visible, iconClass, sticky } = options;

  if (visible) {
    // Check that gallery has video icon class
    if (iconClass) {
      const iconLocator = page.locator(`.foogallery.${iconClass}`);
      await expect(iconLocator).toBeVisible();
    }

    // Check sticky icon class if specified
    if (sticky !== undefined) {
      const stickyLocator = page.locator('.foogallery.fg-video-sticky');
      if (sticky) {
        await expect(stickyLocator).toBeVisible();
      } else {
        await expect(stickyLocator).toHaveCount(0);
      }
    }
  } else {
    // Video icons should not be visible
    const galleryWithVideo = page.locator(VIDEO_SELECTORS.galleryWithVideo);
    await expect(galleryWithVideo).toHaveCount(0);
  }
}

/**
 * Verify video plays in lightbox
 */
export async function verifyVideoInLightbox(page: Page): Promise<void> {
  // Check for iframe (YouTube/Vimeo) or video element (self-hosted)
  const videoContent = page.locator('.fg-panel-content iframe, .fg-panel-content video');
  await expect(videoContent).toBeVisible({ timeout: 10000 });
}

/**
 * Get count of images and videos in gallery (from admin)
 */
export async function getMediaCounts(page: Page): Promise<{ images: number; videos: number }> {
  // Look for the count text in the gallery editor
  // Format: "X images, Y videos" or "X images" or "X videos"
  const countText = await page.locator('.foogallery-image-count, .attachment-count').textContent() || '';

  const imageMatch = countText.match(/(\d+)\s*images?/i);
  const videoMatch = countText.match(/(\d+)\s*videos?/i);

  return {
    images: imageMatch ? parseInt(imageMatch[1], 10) : 0,
    videos: videoMatch ? parseInt(videoMatch[1], 10) : 0,
  };
}

/**
 * Sample test video URLs for testing
 */
export const TEST_VIDEOS = {
  // YouTube test video
  youtube: {
    url: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    provider: 'youtube',
    id: 'dQw4w9WgXcQ',
  },
  // Vimeo test video
  vimeo: {
    url: 'https://vimeo.com/148751763',
    provider: 'vimeo',
    id: '148751763',
  },
  // Self-hosted (path will be set during test setup)
  selfHosted: {
    url: '', // Will be populated after WordPress import
    provider: 'self-hosted',
    id: '',
  },
} as const;

/**
 * Create a gallery with video-enabled images for testing
 */
export async function createGalleryWithVideos(page: Page, options: VideoTestOptions): Promise<string> {
  const { galleryName, templateSelector, screenshotPrefix, imageCount = 3 } = options;

  // Set viewport size
  await page.setViewportSize({ width: 1932, height: 1271 });

  // Navigate to WordPress admin dashboard
  await page.goto('/wp-admin/index.php');
  await page.waitForLoadState('domcontentloaded');

  // Click on FooGallery menu in sidebar
  await page.locator('#menu-posts-foogallery div.wp-menu-name').click();
  await page.waitForLoadState('domcontentloaded');

  // Click "Add New" submenu
  await page.locator('#menu-posts-foogallery li:nth-of-type(3) > a').click();
  await page.waitForLoadState('domcontentloaded');

  // Enter gallery title
  await page.locator('#title').fill(galleryName);

  // Select the template
  const templateCard = page.locator(`[data-template="${templateSelector}"]`);
  await templateCard.waitFor({ state: 'visible', timeout: 10000 });
  await templateCard.click();

  // Screenshot: Template selected
  await page.screenshot({ path: `test-results/${screenshotPrefix}-01-template-selected.png` });

  // Click "Add From Media Library"
  await page.locator('text=Add From Media Library').click();
  await page.waitForLoadState('networkidle');

  // Wait for modal and select images
  const modal = page.locator('.media-modal:visible');
  await modal.waitFor({ state: 'visible', timeout: 10000 });

  const mediaLibraryTab = modal.locator('.media-menu-item').filter({ hasText: 'Media Library' });
  await mediaLibraryTab.click();

  const attachments = modal.locator('.attachment');
  await attachments.first().waitFor({ state: 'visible', timeout: 10000 });

  for (let i = 0; i < imageCount; i++) {
    await attachments.nth(i).click();
  }

  // Click "Add to Gallery"
  const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
  await addButton.click();
  await page.waitForLoadState('networkidle');

  // Screenshot: Images added
  await page.screenshot({ path: `test-results/${screenshotPrefix}-02-images-added.png` });

  // Extract gallery ID after publish (for later reference)
  await page.locator('#publish').click();
  await page.waitForLoadState('networkidle');
  await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

  const url = page.url();
  const postIdMatch = url.match(/post=(\d+)/);
  const galleryId = postIdMatch ? postIdMatch[1] : '';

  // Screenshot: Published
  await page.screenshot({ path: `test-results/${screenshotPrefix}-03-published.png` });

  return galleryId;
}
