// File: tests/helpers/exif-test-helper.ts
// Helper for EXIF pro feature tests

import { Page, expect, Locator } from '@playwright/test';

// EXIF selectors - based on FooGallery EXIF feature DOM structure
export const EXIF_SELECTORS = {
  // Admin - Gallery Settings (EXIF Tab)
  // EXIF tab is the 11th tab in vertical tabs navigation
  exifTab: 'div.foogallery-vertical-tabs > div:nth-of-type(11)',
  // Radio buttons for enabling/disabling EXIF - use specific IDs
  exifViewDisabled: '#FooGallerySettings_default_exif_view_status0',
  exifViewEnabled: '#FooGallerySettings_default_exif_view_status1',
  // Display layout radio buttons
  exifDisplayLayoutAuto: '#FooGallerySettings_default_exif_display_layout0',
  exifDisplayLayoutFull: '#FooGallerySettings_default_exif_display_layout1',
  exifDisplayLayoutPartial: '#FooGallerySettings_default_exif_display_layout2',
  exifDisplayLayoutMinimal: '#FooGallerySettings_default_exif_display_layout3',
  // Icon position and theme
  exifIconPosition: 'select[name*="exif_icon_position"]',
  exifIconThemeLight: 'input[name*="exif_icon_theme"][value="fg-exif-light"]',
  exifIconThemeDark: 'input[name*="exif_icon_theme"][value="fg-exif-dark"]',

  // Frontend - Gallery
  galleryContainer: '.foogallery',
  galleryItem: '.fg-item',
  itemWithExif: '.fg-item-exif',
  itemAnchor: '.fg-item a.fg-thumb',
  dataExifAttr: '[data-exif]',

  // Gallery EXIF position classes
  galleryExifBottomRight: '.fg-exif-bottom-right',
  galleryExifBottomLeft: '.fg-exif-bottom-left',
  galleryExifTopRight: '.fg-exif-top-right',
  galleryExifTopLeft: '.fg-exif-top-left',

  // Gallery EXIF theme classes
  galleryExifLight: '.fg-exif-light',
  galleryExifDark: '.fg-exif-dark',

  // Frontend - Lightbox
  lightboxPanel: '.fg-panel',
  lightboxInfoButton: 'button.fg-panel-button-info',
  exifContainer: '.fg-media-caption-exif',
  exifProp: '.fg-media-caption-exif-prop',
  exifIcon: '.fg-media-caption-exif-icon',
  exifContent: '.fg-media-caption-exif-content',
  exifLabel: '.fg-media-caption-exif-label',
  exifValue: '.fg-media-caption-exif-value',
  exifTooltip: '.fg-media-caption-exif-tooltip',
  lightboxClose: 'button.fg-panel-button-close',
  lightboxNext: 'button.fg-panel-button-next',
  lightboxPrev: 'button.fg-panel-button-prev',

  // Layout-specific containers
  exifContainerAuto: '.fg-media-caption-exif-auto',
  exifContainerFull: '.fg-media-caption-exif-full',
  exifContainerPartial: '.fg-media-caption-exif-partial',
  exifContainerMinimal: '.fg-media-caption-exif-minimal',

  // Attachment Modal - EXIF Tab
  // Based on recording selectors
  attachmentExifTab: 'div.foogallery-image-edit-meta div:nth-of-type(6) > label, #foogallery-panel-exif',
  attachmentCamera: '#attachment-details-two-column-camera',
  attachmentAperture: '#attachment-details-two-column-aperture',
  attachmentShutterSpeed: '#attachment-details-two-column-shutter-speed',
  attachmentIso: '#attachment-details-two-column-iso',
  attachmentFocalLength: '#attachment-details-two-column-focal-length',
  attachmentOrientation: '#attachment-details-two-column-orientation',
  attachmentTimestamp: '#attachment-details-two-column-created-timestamp',

  // Global Settings
  globalExifAttributes: 'textarea[name*="exif_attributes"]',
  globalExifApertureLabel: 'input[name*="exif_aperture_label"]',
  globalExifCameraLabel: 'input[name*="exif_camera_label"]',
  globalExifDateLabel: 'input[name*="exif_date_label"]',
  globalExifExposureLabel: 'input[name*="exif_exposure_label"]',
  globalExifFocalLengthLabel: 'input[name*="exif_focal_length_label"]',
  globalExifIsoLabel: 'input[name*="exif_iso_label"]',
} as const;

// EXIF icon position options - actual option values (not display text)
export const EXIF_ICON_POSITIONS = {
  bottomRight: 'fg-exif-bottom-right',
  bottomLeft: 'fg-exif-bottom-left',
  topRight: 'fg-exif-top-right',
  topLeft: 'fg-exif-top-left',
  none: '',
} as const;

// EXIF icon theme options
export const EXIF_ICON_THEMES = {
  light: 'fg-exif-light',
  dark: 'fg-exif-dark',
} as const;

// EXIF display layout options
export const EXIF_DISPLAY_LAYOUTS = {
  auto: 'auto',
  full: 'full',
  partial: 'partial',
  minimal: 'minimal',
} as const;

// EXIF field IDs
export const EXIF_FIELDS = {
  camera: 'camera',
  aperture: 'aperture',
  createdTimestamp: 'created_timestamp',
  shutterSpeed: 'shutter_speed',
  focalLength: 'focal_length',
  iso: 'iso',
  orientation: 'orientation',
} as const;

export interface ExifTestOptions {
  galleryName: string;
  templateSelector: string;
  screenshotPrefix: string;
  imageCount?: number;
  searchForImages?: string[];  // Optional: search for specific images by name
}

export interface ExifSettingsOptions {
  enabled?: boolean;
  iconPosition?: keyof typeof EXIF_ICON_POSITIONS;
  iconTheme?: 'light' | 'dark';
  displayLayout?: keyof typeof EXIF_DISPLAY_LAYOUTS;
}

export interface ExifData {
  camera?: string;
  aperture?: string;
  shutterSpeed?: string;
  iso?: string;
  focalLength?: string;
  orientation?: string;
  createdTimestamp?: string;
}

// Sample EXIF test images (paths relative to WordPress uploads)
export const EXIF_TEST_IMAGES = {
  canon40D: {
    filename: 'Canon_40D.jpg',
    expected: {
      camera: 'Canon EOS 40D',
      aperture: '7.1',
      shutterSpeed: '1/160',
      iso: '100',
      focalLength: '135',
      orientation: '1',
    },
  },
  canonPowerShot: {
    filename: 'Canon_PowerShot_S40.jpg',
    expected: {
      camera: 'Canon PowerShot S40',
      aperture: '4.9',
      shutterSpeed: '1/500',
      iso: '100',
      focalLength: '21.3125',
      orientation: '1',
    },
  },
  nikonD70: {
    filename: 'Nikon_D70.jpg',
    expected: {
      camera: 'NIKON D70',
      aperture: '7.1',
      shutterSpeed: '1/2500',
      iso: '200',
      focalLength: '70',
      orientation: '1',
    },
  },
  panasonicDMC: {
    filename: 'Panasonic_DMC-FZ30.jpg',
    expected: {
      camera: 'DMC-FZ30',
      aperture: '2.8',
      shutterSpeed: '1/500',
      iso: '80',
      focalLength: '7.4',
      orientation: '1',
    },
  },
  noExif: {
    filename: 'no_exif.jpg',
    expected: {},
  },
  emptyExif: {
    filename: 'empty_exif.jpg',
    expected: {},
  },
  landscape1: {
    filename: 'landscape_1.jpg',
    expected: {
      orientation: '1',
    },
  },
} as const;

/**
 * Navigate to the EXIF settings tab in gallery admin
 * Uses text-based selector for reliability instead of fragile positional index
 */
export async function navigateToExifSettings(page: Page, templateSelector: string): Promise<void> {
  // Scroll down to Gallery Settings section
  const settingsSection = page.locator('#foogallery_settings');
  await settingsSection.scrollIntoViewIfNeeded();
  await page.waitForTimeout(500);

  // Click on EXIF section tab in Gallery Settings for the selected template
  // Use text-based selector for reliability
  const templateContainer = page.locator(`div.foogallery-settings-container-${templateSelector}`);

  // Try text-based selector first (most reliable)
  let exifTab = templateContainer.locator('div.foogallery-vertical-tabs > div').filter({ hasText: /^EXIF$/ });

  if (await exifTab.count() === 0) {
    // Fallback: try with just "EXIF" text
    exifTab = templateContainer.locator('div.foogallery-vertical-tabs > div:has-text("EXIF")');
  }

  if (await exifTab.count() === 0) {
    // Final fallback: positional selector (EXIF is typically 11th or 12th tab)
    // Tab order: General, Lightbox, Appearance, Hover Effects, Captions, Paging, Filtering, Video, Protection, Ecommerce, EXIF, Advanced
    exifTab = templateContainer.locator('div.foogallery-vertical-tabs > div:nth-of-type(11)');
  }

  await exifTab.scrollIntoViewIfNeeded();
  await exifTab.click();
  await page.waitForTimeout(500);

  // Wait for the EXIF tab content to be active
  await page.waitForTimeout(300);
}

/**
 * Configure EXIF settings in gallery admin
 * Based on recording selectors for EXIF radio buttons
 */
export async function configureExifSettings(page: Page, templateSelector: string, options: ExifSettingsOptions): Promise<void> {
  const {
    enabled = true,
    iconPosition,
    iconTheme,
    displayLayout,
  } = options;

  // Navigate to EXIF tab first
  await navigateToExifSettings(page, templateSelector);

  // Enable/disable EXIF using specific radio button IDs
  // Based on recording: #FooGallerySettings_default_exif_view_status0 (Disabled), #FooGallerySettings_default_exif_view_status1 (Enabled)
  // Use force:true because the radio buttons may have CSS that makes them technically hidden
  if (enabled) {
    // Click "Enabled" radio button
    const enabledRadio = page.locator(`#FooGallerySettings_${templateSelector}_exif_view_status1`);
    await enabledRadio.click({ force: true });
    await page.waitForTimeout(300);
  } else {
    // Click "Disabled" radio button
    const disabledRadio = page.locator(`#FooGallerySettings_${templateSelector}_exif_view_status0`);
    await disabledRadio.click({ force: true });
    await page.waitForTimeout(300);
    return; // No need to configure other settings if disabled
  }

  // Set display layout if specified using specific radio button IDs
  // Based on recording: #FooGallerySettings_default_exif_display_layout{0-3}
  if (displayLayout !== undefined) {
    let layoutIndex: string;
    switch (displayLayout) {
      case 'auto':
        layoutIndex = '0';
        break;
      case 'full':
        layoutIndex = '1';
        break;
      case 'partial':
        layoutIndex = '2';
        break;
      case 'minimal':
        layoutIndex = '3';
        break;
      default:
        layoutIndex = '0';
    }
    const layoutRadio = page.locator(`#FooGallerySettings_${templateSelector}_exif_display_layout${layoutIndex}`);
    await layoutRadio.click({ force: true });
    await page.waitForTimeout(300);
  }

  // Set icon position if specified
  if (iconPosition !== undefined) {
    const positionSelect = page.locator(`select[name*="${templateSelector}_exif_icon_position"]`);
    await positionSelect.selectOption(EXIF_ICON_POSITIONS[iconPosition], { force: true });
    await page.waitForTimeout(300);
  }

  // Set icon theme if specified
  if (iconTheme !== undefined) {
    const themeValue = iconTheme === 'light' ? 'fg-exif-light' : 'fg-exif-dark';
    const themeRadio = page.locator(`input[name*="${templateSelector}_exif_icon_theme"][value="${themeValue}"]`);
    await themeRadio.click({ force: true });
    await page.waitForTimeout(300);
  }
}

/**
 * Create a gallery with EXIF-enabled images
 */
export async function createGalleryWithExif(page: Page, options: ExifTestOptions, imageNames?: string[]): Promise<string> {
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

  // Select images - if specific image names provided, search and select those
  if (imageNames && imageNames.length > 0) {
    for (const imageName of imageNames) {
      // Search for the specific image
      const searchInput = modal.locator('input[type="search"]');
      await searchInput.fill(imageName);
      await page.waitForTimeout(1000);

      // Select the first matching attachment
      const attachment = modal.locator('.attachment').first();
      await attachment.click();
      await page.waitForTimeout(200);

      // Clear search for next image
      await searchInput.clear();
      await page.waitForTimeout(500);
    }
  } else {
    // Select first N images
    for (let i = 0; i < imageCount; i++) {
      await attachments.nth(i).click();
    }
  }

  // Click "Add to Gallery"
  const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
  await addButton.click();
  await page.waitForLoadState('networkidle');

  // Screenshot: Images added
  await page.screenshot({ path: `test-results/${screenshotPrefix}-02-images-added.png` });

  // Extract gallery ID after publish
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

/**
 * Open lightbox and show EXIF panel
 * Returns true if EXIF panel was opened, false if info button was disabled
 */
export async function openLightboxAndShowExif(page: Page, itemIndex: number = 0): Promise<boolean> {
  // Click on gallery item to open lightbox
  const galleryItem = page.locator(EXIF_SELECTORS.itemAnchor).nth(itemIndex);
  await galleryItem.waitFor({ state: 'visible', timeout: 15000 });
  await galleryItem.click({ force: true });

  // Wait for lightbox to open
  await page.waitForSelector(EXIF_SELECTORS.lightboxPanel, { state: 'visible', timeout: 10000 });
  await page.waitForTimeout(1000); // Allow lightbox animation to complete

  // Click info button to show EXIF panel
  const infoButton = page.locator(EXIF_SELECTORS.lightboxInfoButton);
  if (await infoButton.isVisible()) {
    // Check if button is enabled (not disabled)
    const isDisabled = await infoButton.getAttribute('disabled');
    const ariaDisabled = await infoButton.getAttribute('aria-disabled');

    if (isDisabled === 'disabled' || ariaDisabled === 'true') {
      // Info button is disabled - image has no EXIF data
      console.log('Info button is disabled - image may not have EXIF data');
      return false;
    }

    // Click the info button with force to ensure it triggers
    await infoButton.click({ force: true });
    await page.waitForTimeout(1000); // Wait for panel animation

    // Try to wait for EXIF container to become visible
    try {
      await page.waitForSelector(EXIF_SELECTORS.exifContainer, { state: 'visible', timeout: 5000 });
      return true;
    } catch {
      // EXIF container didn't become visible - try clicking again
      // Sometimes the first click toggles a different state
      await infoButton.click({ force: true });
      await page.waitForTimeout(1000);

      // Check again if EXIF container is now visible
      const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
      return await exifContainer.isVisible();
    }
  }

  return false;
}

/**
 * Get EXIF values from lightbox panel
 */
export async function getExifValuesFromLightbox(page: Page): Promise<Record<string, string>> {
  const exifValues: Record<string, string> = {};

  // Wait for EXIF container to be visible
  const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
  if (!(await exifContainer.isVisible())) {
    return exifValues;
  }

  // Get all EXIF properties
  const exifProps = page.locator(EXIF_SELECTORS.exifProp);
  const count = await exifProps.count();

  for (let i = 0; i < count; i++) {
    const prop = exifProps.nth(i);
    const label = await prop.locator(EXIF_SELECTORS.exifLabel).textContent() || '';
    const value = await prop.locator(EXIF_SELECTORS.exifValue).textContent() || '';

    if (label && value) {
      exifValues[label.trim()] = value.trim();
    }
  }

  return exifValues;
}

/**
 * Verify data-exif attribute on gallery item
 */
export async function verifyDataExifAttribute(page: Page, itemIndex: number = 0): Promise<object | null> {
  const galleryItem = page.locator(EXIF_SELECTORS.itemAnchor).nth(itemIndex);
  const dataExif = await galleryItem.getAttribute('data-exif');

  if (dataExif) {
    try {
      return JSON.parse(dataExif);
    } catch {
      return null;
    }
  }

  return null;
}

/**
 * Open attachment modal for a specific gallery item
 * Based on recording: li:nth-of-type(1) a.info > span
 * The info link is only visible on hover
 */
export async function openAttachmentModal(page: Page, itemIndex: number = 0): Promise<void> {
  // The gallery items are in a list
  const listItem = page.locator(`#foogallery_items li:nth-of-type(${itemIndex + 1})`);
  const listItemCount = await listItem.count();

  if (listItemCount > 0) {
    // Scroll into view and hover to reveal the info link
    await listItem.scrollIntoViewIfNeeded();
    await listItem.hover();
    await page.waitForTimeout(300);

    // Now click the info link
    const infoLink = listItem.locator('a.info');
    if (await infoLink.isVisible({ timeout: 2000 }).catch(() => false)) {
      await infoLink.click();
      await page.waitForTimeout(500);
    } else {
      // Fallback: click on the thumbnail div
      const thumbnail = listItem.locator('.attachment-preview, .thumbnail');
      if (await thumbnail.isVisible()) {
        await thumbnail.click();
        await page.waitForTimeout(500);
      }
    }
  } else {
    // Fallback: try clicking on the attachment directly
    const galleryItems = page.locator('#foogallery_items .attachment');
    const itemCount = await galleryItems.count();
    if (itemCount > itemIndex) {
      const item = galleryItems.nth(itemIndex);
      await item.scrollIntoViewIfNeeded();
      await item.hover();
      await page.waitForTimeout(300);
      await item.click();
      await page.waitForTimeout(500);
    }
  }

  // Wait for the attachment details modal to open
  // This is the WordPress attachment details modal with FooGallery tabs
  await page.waitForSelector('.edit-attachment-frame, .attachment-details, .media-modal-content', { state: 'visible', timeout: 10000 });
}

/**
 * Navigate to EXIF tab in the attachment modal
 * The EXIF tab is visible as a tab button in the Edit Attachment Details modal
 */
export async function navigateToExifTabInModal(page: Page): Promise<void> {
  // Try different selectors for the EXIF tab
  const exifTabSelectors = [
    // Tab button in the attachment details modal header
    'button:has-text("EXIF")',
    '.attachment-details button:has-text("EXIF")',
    // Based on recording - EXIF tab in the tabs row
    'div.foogallery-image-edit-meta div:nth-of-type(6) > label',
    '#foogallery-panel-exif',
    'label[for="foogallery-tab-exif"]',
    '.foogallery-tabs label:has-text("EXIF")',
    '.media-menu-item:has-text("EXIF")',
  ];

  for (const selector of exifTabSelectors) {
    const tab = page.locator(selector).first();
    if (await tab.isVisible({ timeout: 2000 }).catch(() => false)) {
      await tab.click();
      await page.waitForTimeout(300);
      return;
    }
  }

  console.log('EXIF tab not found in attachment modal');
}

/**
 * Edit EXIF data in the attachment modal
 */
export async function editExifInAttachmentModal(page: Page, itemIndex: number, exifData: Partial<ExifData>): Promise<void> {
  // Open the attachment modal
  await openAttachmentModal(page, itemIndex);

  // Navigate to EXIF tab
  await navigateToExifTabInModal(page);

  // Fill in EXIF fields
  if (exifData.camera !== undefined) {
    const cameraInput = page.locator(EXIF_SELECTORS.attachmentCamera).first();
    if (await cameraInput.isVisible()) {
      await cameraInput.fill(exifData.camera);
    }
  }

  if (exifData.aperture !== undefined) {
    const apertureInput = page.locator(EXIF_SELECTORS.attachmentAperture).first();
    if (await apertureInput.isVisible()) {
      await apertureInput.fill(exifData.aperture);
    }
  }

  if (exifData.shutterSpeed !== undefined) {
    const shutterInput = page.locator(EXIF_SELECTORS.attachmentShutterSpeed).first();
    if (await shutterInput.isVisible()) {
      await shutterInput.fill(exifData.shutterSpeed);
    }
  }

  if (exifData.iso !== undefined) {
    const isoInput = page.locator(EXIF_SELECTORS.attachmentIso).first();
    if (await isoInput.isVisible()) {
      await isoInput.fill(exifData.iso);
    }
  }

  if (exifData.focalLength !== undefined) {
    const focalInput = page.locator(EXIF_SELECTORS.attachmentFocalLength).first();
    if (await focalInput.isVisible()) {
      await focalInput.fill(exifData.focalLength);
    }
  }

  if (exifData.orientation !== undefined) {
    const orientationInput = page.locator(EXIF_SELECTORS.attachmentOrientation).first();
    if (await orientationInput.isVisible()) {
      await orientationInput.fill(exifData.orientation);
    }
  }

  if (exifData.createdTimestamp !== undefined) {
    const timestampInput = page.locator(EXIF_SELECTORS.attachmentTimestamp).first();
    if (await timestampInput.isVisible()) {
      await timestampInput.fill(exifData.createdTimestamp);
    }
  }

  // Close the modal
  await closeAttachmentModal(page);
}

/**
 * Close the FooGallery attachment modal reliably
 */
export async function closeAttachmentModal(page: Page): Promise<void> {
  // Try clicking the close button first (most reliable)
  const closeButton = page.locator('#foogallery-image-edit-modal .media-modal-close, #foogallery-image-edit-modal button.media-modal-close');
  if (await closeButton.isVisible()) {
    await closeButton.click({ force: true });
    await page.waitForTimeout(1000);
  } else {
    // Fallback to Escape key
    await page.keyboard.press('Escape');
    await page.waitForTimeout(1000);
  }

  // Wait for the modal to be hidden
  const modal = page.locator('#foogallery-image-edit-modal');
  await modal.waitFor({ state: 'hidden', timeout: 10000 }).catch(() => {
    // Modal might not exist anymore, that's OK
  });
}

/**
 * Verify gallery has EXIF CSS classes
 */
export async function verifyGalleryExifClasses(page: Page, position: string, theme: string): Promise<void> {
  const gallery = page.locator(EXIF_SELECTORS.galleryContainer);

  // Check position class
  if (position) {
    await expect(gallery).toHaveClass(new RegExp(`fg-exif-${position}`));
  }

  // Check theme class
  if (theme) {
    await expect(gallery).toHaveClass(new RegExp(`fg-exif-${theme}`));
  }
}

/**
 * Verify items have EXIF class
 */
export async function verifyItemsHaveExifClass(page: Page, expectedWithExif: number[], expectedWithoutExif: number[]): Promise<void> {
  const items = page.locator(EXIF_SELECTORS.galleryItem);

  for (const index of expectedWithExif) {
    const item = items.nth(index);
    await expect(item).toHaveClass(/fg-item-exif/);
  }

  for (const index of expectedWithoutExif) {
    const item = items.nth(index);
    await expect(item).not.toHaveClass(/fg-item-exif/);
  }
}

/**
 * Close lightbox
 */
export async function closeLightbox(page: Page): Promise<void> {
  const closeButton = page.locator(EXIF_SELECTORS.lightboxClose);
  if (await closeButton.isVisible()) {
    await closeButton.click();
    await page.waitForSelector(EXIF_SELECTORS.lightboxPanel, { state: 'hidden', timeout: 10000 });
  }
}

/**
 * Navigate to next image in lightbox
 */
export async function navigateToNextInLightbox(page: Page): Promise<void> {
  const nextButton = page.locator(EXIF_SELECTORS.lightboxNext);
  await nextButton.click();
  await page.waitForTimeout(500);
}

/**
 * Navigate to previous image in lightbox
 */
export async function navigateToPrevInLightbox(page: Page): Promise<void> {
  const prevButton = page.locator(EXIF_SELECTORS.lightboxPrev);
  await prevButton.click();
  await page.waitForTimeout(500);
}

/**
 * Verify EXIF display layout CSS class
 */
export async function verifyExifLayoutClass(page: Page, layout: keyof typeof EXIF_DISPLAY_LAYOUTS): Promise<void> {
  const exifContainer = page.locator(EXIF_SELECTORS.exifContainer);
  await expect(exifContainer).toHaveClass(new RegExp(`fg-media-caption-exif-${layout}`));
}

/**
 * Navigate to FooGallery global settings page
 */
export async function navigateToFooGallerySettings(page: Page): Promise<void> {
  await page.goto('/wp-admin/edit.php?post_type=foogallery&page=foogallery-settings');
  await page.waitForLoadState('domcontentloaded');
}

/**
 * Navigate to EXIF tab in global settings
 */
export async function navigateToGlobalExifSettings(page: Page): Promise<void> {
  await navigateToFooGallerySettings(page);

  // Click on Images tab which contains EXIF settings
  const imagesTab = page.locator('a[href*="tab=images"], .nav-tab:has-text("Images")');
  if (await imagesTab.isVisible()) {
    await imagesTab.click();
    await page.waitForTimeout(500);
  }
}

/**
 * Create gallery and navigate to frontend page
 */
export async function createGalleryAndNavigateToPage(page: Page, options: ExifTestOptions, exifSettings?: ExifSettingsOptions): Promise<void> {
  const { galleryName, templateSelector, screenshotPrefix, imageCount = 3, searchForImages } = options;

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

  // If specific images are requested, search for them
  if (searchForImages && searchForImages.length > 0) {
    const searchInput = modal.locator('input[type="search"]');
    for (const imageName of searchForImages) {
      await searchInput.fill(imageName);
      await page.waitForTimeout(1000);
      // Select the first matching attachment
      const attachment = modal.locator('.attachment').first();
      if (await attachment.isVisible()) {
        await attachment.click();
      }
      // Clear search for next image
      await searchInput.clear();
      await page.waitForTimeout(500);
    }
  } else {
    // For EXIF tests, search for images with REAL EXIF data (camera metadata)
    // These are titled "EXIF - Canon_40D", "EXIF - Nikon_D70", etc.
    const searchInput = modal.locator('input[type="search"]');
    if (await searchInput.isVisible()) {
      let selectedCount = 0;

      // Search for specific camera names to find images with actual EXIF data
      const cameraSearchTerms = ['Canon_40D', 'Canon_PowerShot', 'Nikon_D70', 'Panasonic_DMC'];

      for (const searchTerm of cameraSearchTerms) {
        if (selectedCount >= imageCount) break;

        await searchInput.fill(searchTerm);
        await page.waitForTimeout(1000);

        const searchAttachments = modal.locator('.attachment');
        const count = await searchAttachments.count();

        for (let i = 0; i < count && selectedCount < imageCount; i++) {
          const attachment = searchAttachments.nth(i);
          if (await attachment.isVisible()) {
            const isSelected = await attachment.evaluate((el) => el.classList.contains('selected'));
            if (!isSelected) {
              await attachment.click();
              selectedCount++;
            }
          }
        }
      }

      // Fallback: if we didn't find enough images with camera names, try any images
      if (selectedCount < imageCount) {
        await searchInput.clear();
        await page.waitForTimeout(500);
        const allAttachments = modal.locator('.attachment');
        await allAttachments.first().waitFor({ state: 'visible', timeout: 5000 });
        const count = await allAttachments.count();

        for (let i = 0; i < count && selectedCount < imageCount; i++) {
          const attachment = allAttachments.nth(i);
          if (await attachment.isVisible()) {
            const isSelected = await attachment.evaluate((el) => el.classList.contains('selected'));
            if (!isSelected) {
              await attachment.click();
              selectedCount++;
            }
          }
        }
      }
    } else {
      // No search input, just select first N images
      for (let i = 0; i < imageCount; i++) {
        await attachments.nth(i).click();
      }
    }
  }

  // Click "Add to Gallery"
  const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
  await addButton.click();
  await page.waitForLoadState('networkidle');

  // Configure EXIF settings if provided
  if (exifSettings) {
    await configureExifSettings(page, templateSelector, exifSettings);
  }

  // Publish gallery
  await page.locator('#publish').click();
  await page.waitForLoadState('networkidle');
  await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

  // Screenshot: Published
  await page.screenshot({ path: `test-results/${screenshotPrefix}-published.png` });

  // Click "Create Gallery Page" button
  await page.locator('#foogallery_create_page').click();
  await page.waitForLoadState('networkidle');
  await page.waitForLoadState('domcontentloaded');

  // Find the View link and navigate to frontend
  const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
  await viewLink.waitFor({ state: 'visible', timeout: 30000 });
  const viewUrl = await viewLink.getAttribute('href');

  if (viewUrl) {
    await page.goto(viewUrl);
    await page.waitForLoadState('networkidle');
  }

  // Screenshot: Gallery on frontend
  await page.screenshot({ path: `test-results/${screenshotPrefix}-frontend.png` });
}

/**
 * Check if EXIF tab is visible in gallery settings
 */
export async function isExifTabVisible(page: Page, templateSelector: string): Promise<boolean> {
  const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
  // EXIF is the 11th tab in vertical tabs, but we can also look for the text
  const exifTab = templateContainer.locator('div.foogallery-vertical-tabs > div').filter({ hasText: 'EXIF' });
  return await exifTab.isVisible();
}

/**
 * Add EXIF images to the current gallery from the media library.
 * Searches for images tagged with 'EXIF' or specific EXIF test image names.
 */
export async function addExifImagesToGallery(
  page: Page,
  imageCount: number = 3,
  imageNames?: string[]
): Promise<void> {
  // Click "Add From Media Library"
  await page.locator('text=Add From Media Library').click();
  await page.waitForLoadState('networkidle');

  // Wait for modal
  const modal = page.locator('.media-modal:visible');
  await modal.waitFor({ state: 'visible', timeout: 10000 });

  // Switch to Media Library tab
  const mediaLibraryTab = modal.locator('.media-menu-item').filter({ hasText: 'Media Library' });
  await mediaLibraryTab.click();
  await page.waitForTimeout(500);

  const attachments = modal.locator('.attachment');
  await attachments.first().waitFor({ state: 'visible', timeout: 10000 });

  // If specific image names are provided, search for them
  if (imageNames && imageNames.length > 0) {
    const searchInput = modal.locator('input[type="search"]');
    for (const imageName of imageNames) {
      await searchInput.fill(imageName);
      await page.waitForTimeout(1000);
      const attachment = modal.locator('.attachment').first();
      if (await attachment.isVisible()) {
        await attachment.click();
      }
      await searchInput.clear();
      await page.waitForTimeout(500);
    }
  } else {
    // Search for EXIF test images with REAL EXIF data
    // These are titled "EXIF - Canon_40D", "EXIF - Nikon_D70", etc.
    // NOT "EXIF test image: no_exif" which has no EXIF data
    const searchInput = modal.locator('input[type="search"]');
    if (await searchInput.isVisible()) {
      let selectedCount = 0;

      // Search for specific camera names to find images with actual EXIF data
      // These images have embedded camera metadata: Canon, Nikon, Panasonic
      const cameraSearchTerms = ['Canon_40D', 'Canon_PowerShot', 'Nikon_D70', 'Panasonic_DMC'];

      for (const searchTerm of cameraSearchTerms) {
        if (selectedCount >= imageCount) break;

        await searchInput.fill(searchTerm);
        await page.waitForTimeout(1000);

        const attachments = modal.locator('.attachment');
        const count = await attachments.count();

        for (let i = 0; i < count && selectedCount < imageCount; i++) {
          const attachment = attachments.nth(i);
          if (await attachment.isVisible()) {
            // Check if not already selected
            const isSelected = await attachment.evaluate((el) => el.classList.contains('selected'));
            if (!isSelected) {
              await attachment.click();
              selectedCount++;
            }
          }
        }
      }

      // If we didn't find enough camera-specific images, try "EXIF -" (with hyphen)
      // This matches "EXIF - Canon_40D" but not "EXIF test image: no_exif"
      if (selectedCount < imageCount) {
        await searchInput.fill('EXIF -');
        await page.waitForTimeout(1000);

        const attachments = modal.locator('.attachment');
        const count = await attachments.count();

        for (let i = 0; i < count && selectedCount < imageCount; i++) {
          const attachment = attachments.nth(i);
          if (await attachment.isVisible()) {
            const isSelected = await attachment.evaluate((el) => el.classList.contains('selected'));
            if (!isSelected) {
              await attachment.click();
              selectedCount++;
            }
          }
        }
      }

      // Final fallback: select any available images
      if (selectedCount === 0) {
        await searchInput.clear();
        await page.waitForTimeout(500);

        const anyAttachments = modal.locator('.attachment');
        const count = await anyAttachments.count();

        for (let i = 0; i < count && selectedCount < imageCount; i++) {
          const attachment = anyAttachments.nth(i);
          if (await attachment.isVisible()) {
            await attachment.click();
            selectedCount++;
          }
        }
      }
    }
  }

  // Click "Add to Gallery"
  const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
  await addButton.click();
  await page.waitForLoadState('networkidle');
}

/**
 * Publish the current gallery and navigate to the frontend page.
 * Returns the frontend URL.
 */
export async function publishGalleryAndNavigateToFrontend(page: Page): Promise<string> {
  // Publish gallery
  await page.locator('#publish').click();
  await page.waitForLoadState('networkidle');
  await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

  // Click "Create Gallery Page" button
  await page.locator('#foogallery_create_page').click();
  await page.waitForLoadState('networkidle');
  await page.waitForLoadState('domcontentloaded');

  // Find the View link and navigate to frontend
  const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
  await viewLink.waitFor({ state: 'visible', timeout: 30000 });
  const viewUrl = await viewLink.getAttribute('href') || '';

  if (viewUrl) {
    await page.goto(viewUrl);
    await page.waitForLoadState('networkidle');
  }

  return viewUrl;
}

/**
 * Create a gallery with EXIF images, configure settings, and verify on frontend.
 */
export async function createExifGalleryWithFrontendVerification(
  page: Page,
  options: {
    galleryName: string;
    templateSelector?: string;
    exifEnabled?: boolean;
    iconPosition?: 'bottomRight' | 'bottomLeft' | 'topRight' | 'topLeft' | 'none';
    iconTheme?: 'dark' | 'light';
    displayLayout?: 'auto' | 'full' | 'partial' | 'minimal';
    imageCount?: number;
    imageNames?: string[];
  }
): Promise<{ frontendUrl: string; galleryId: string }> {
  const {
    galleryName,
    templateSelector = 'default',
    exifEnabled = true,
    iconPosition,
    iconTheme,
    displayLayout,
    imageCount = 3,
    imageNames,
  } = options;

  // Set viewport size
  await page.setViewportSize({ width: 1932, height: 1271 });

  // Navigate to create new gallery
  await page.goto('/wp-admin/post-new.php?post_type=foogallery');
  await page.waitForLoadState('domcontentloaded');

  // Enter gallery title
  await page.locator('#title').fill(galleryName);

  // Select template
  const templateCard = page.locator(`[data-template="${templateSelector}"]`);
  await templateCard.waitFor({ state: 'visible', timeout: 10000 });
  await templateCard.click();

  // Add EXIF images
  await addExifImagesToGallery(page, imageCount, imageNames);

  // Configure EXIF settings
  await configureExifSettings(page, templateSelector, {
    enabled: exifEnabled,
    iconPosition,
    iconTheme,
    displayLayout,
  });

  // Publish and navigate to frontend
  const frontendUrl = await publishGalleryAndNavigateToFrontend(page);

  // Extract gallery ID from the URL
  const url = page.url();
  const postIdMatch = url.match(/post=(\d+)/) || url.match(/\/(\d+)\/?$/);
  const galleryId = postIdMatch ? postIdMatch[1] : '';

  return { frontendUrl, galleryId };
}

/**
 * Verify EXIF display in the lightbox.
 * Opens the lightbox, toggles info panel, and checks EXIF values.
 */
export async function verifyExifInLightbox(
  page: Page,
  expectedFields?: {
    camera?: string;
    aperture?: string;
    iso?: string;
    shutterSpeed?: string;
    focalLength?: string;
    date?: string;
  }
): Promise<{ visible: boolean; values: Record<string, string> }> {
  // Open lightbox
  const opened = await openLightboxAndShowExif(page, 0);

  if (!opened) {
    return { visible: false, values: {} };
  }

  // Get EXIF values
  const values = await getExifValuesFromLightbox(page);

  // If expected fields are provided, verify them
  if (expectedFields) {
    if (expectedFields.camera && values['Camera']) {
      expect(values['Camera']).toContain(expectedFields.camera);
    }
    if (expectedFields.aperture && values['Aperture']) {
      expect(values['Aperture']).toContain(expectedFields.aperture);
    }
    if (expectedFields.iso && values['ISO']) {
      expect(values['ISO']).toContain(expectedFields.iso);
    }
  }

  // Close lightbox
  await closeLightbox(page);

  return { visible: true, values };
}

/**
 * Toggle the info panel in the lightbox (open or close EXIF display).
 */
export async function toggleLightboxInfo(page: Page): Promise<boolean> {
  const infoButton = page.locator(EXIF_SELECTORS.lightboxInfoButton);
  if (await infoButton.isVisible()) {
    const isDisabled = await infoButton.getAttribute('disabled');
    const ariaDisabled = await infoButton.getAttribute('aria-disabled');

    if (isDisabled !== 'disabled' && ariaDisabled !== 'true') {
      await infoButton.click();
      await page.waitForTimeout(500);
      return true;
    }
  }
  return false;
}

/**
 * Save changes in the attachment modal.
 * CRITICAL: Always call this before closing the modal to persist EXIF edits.
 */
export async function saveAttachmentModal(page: Page): Promise<void> {
  const saveButton = page.locator('#attachments-data-save-btn');
  if (await saveButton.isVisible()) {
    await saveButton.click();
    await page.waitForTimeout(1000);
  }
}

/**
 * Verify gallery has specific EXIF icon position class on the frontend.
 */
export async function verifyExifIconPositionOnFrontend(
  page: Page,
  position: 'bottomRight' | 'bottomLeft' | 'topRight' | 'topLeft' | 'none'
): Promise<boolean> {
  const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
  await gallery.waitFor({ state: 'visible', timeout: 15000 });

  if (position === 'none') {
    // When position is none, no position class should be present
    const hasBottomRight = await gallery.evaluate((el) => el.classList.contains('fg-exif-bottom-right'));
    const hasBottomLeft = await gallery.evaluate((el) => el.classList.contains('fg-exif-bottom-left'));
    const hasTopRight = await gallery.evaluate((el) => el.classList.contains('fg-exif-top-right'));
    const hasTopLeft = await gallery.evaluate((el) => el.classList.contains('fg-exif-top-left'));
    return !hasBottomRight && !hasBottomLeft && !hasTopRight && !hasTopLeft;
  }

  const positionClass = EXIF_ICON_POSITIONS[position];
  const hasClass = await gallery.evaluate((el, cls) => el.classList.contains(cls), positionClass);
  return hasClass;
}

/**
 * Verify gallery has specific EXIF icon theme class on the frontend.
 */
export async function verifyExifIconThemeOnFrontend(
  page: Page,
  theme: 'dark' | 'light'
): Promise<boolean> {
  const gallery = page.locator(EXIF_SELECTORS.galleryContainer);
  await gallery.waitFor({ state: 'visible', timeout: 15000 });

  const themeClass = EXIF_ICON_THEMES[theme];
  const hasClass = await gallery.evaluate((el, cls) => el.classList.contains(cls), themeClass);
  return hasClass;
}

/**
 * Check if EXIF icon is visible on gallery items (on hover).
 */
export async function verifyExifIconVisibleOnItem(page: Page, itemIndex: number = 0): Promise<boolean> {
  const items = page.locator(EXIF_SELECTORS.galleryItem);
  const item = items.nth(itemIndex);

  if (!await item.isVisible()) {
    return false;
  }

  // Check if item has EXIF class
  const hasExifClass = await item.evaluate((el) => el.classList.contains('fg-item-exif'));
  return hasExifClass;
}

/**
 * Open an image in the lightbox by clicking on a gallery item.
 */
export async function openLightbox(page: Page, itemIndex: number = 0): Promise<void> {
  const galleryItem = page.locator(EXIF_SELECTORS.itemAnchor).nth(itemIndex);
  await galleryItem.waitFor({ state: 'visible', timeout: 15000 });
  await galleryItem.click({ force: true });
  await page.waitForSelector(EXIF_SELECTORS.lightboxPanel, { state: 'visible', timeout: 10000 });
  await page.waitForTimeout(500);
}

/**
 * Verify EXIF values are displayed in the lightbox with specific field/value.
 */
export async function verifyExifFieldInLightbox(
  page: Page,
  fieldLabel: string,
  expectedValue?: string
): Promise<{ found: boolean; value: string }> {
  const exifProps = page.locator(EXIF_SELECTORS.exifProp);
  const count = await exifProps.count();

  for (let i = 0; i < count; i++) {
    const prop = exifProps.nth(i);
    const label = await prop.locator(EXIF_SELECTORS.exifLabel).textContent() || '';

    if (label.trim().toLowerCase().includes(fieldLabel.toLowerCase())) {
      const value = await prop.locator(EXIF_SELECTORS.exifValue).textContent() || '';

      if (expectedValue) {
        expect(value.trim()).toContain(expectedValue);
      }

      return { found: true, value: value.trim() };
    }
  }

  return { found: false, value: '' };
}
