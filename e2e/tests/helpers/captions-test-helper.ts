// File: tests/helpers/captions-test-helper.ts
// Helper for Captions pro feature tests

import { Page, expect, Locator } from '@playwright/test';

// Caption selectors - based on FooGallery Caption feature DOM structure
export const CAPTION_SELECTORS = {
  // Admin - Attachment Modal
  attachmentModal: '#foogallery-image-edit-modal',
  attachmentTitle: '#attachment-details-two-column-title',
  attachmentCaption: '#attachment-details-two-column-caption',
  attachmentDescription: '#attachment-details-two-column-description',
  attachmentAltText: '#attachment-details-two-column-alt-text',
  attachmentCustomUrl: '#attachments-foogallery-custom-url',
  attachmentCustomClass: '#attachments-foogallery-custom-class',
  attachmentSaveButton: '#attachments-data-save-btn',
  attachmentCloseButton: '#foogallery-image-edit-modal button.media-modal-close > span',

  // Admin - Tab Navigation
  appearanceTab: 'div.foogallery-settings-container-default div:nth-of-type(5) > span.foogallery-tab-text',
  lightboxTab: 'div.foogallery-settings-container-default div.foogallery-vertical-tabs > div:nth-of-type(2)',
  lightboxInfoSubtab: 'div.foogallery-tab-active div:nth-of-type(4) > span',
  captionsTab: 'div.foogallery-vertical-tabs > div:nth-of-type(5)',

  // Admin - Caption Alignment
  captionAlignmentDefault: '#FooGallerySettings_default_caption_alignment0',
  captionAlignmentLeft: '#FooGallerySettings_default_caption_alignment1',
  captionAlignmentCenter: '#FooGallerySettings_default_caption_alignment2',
  captionAlignmentRight: '#FooGallerySettings_default_caption_alignment3',

  // Admin - Caption Type
  captionTypeDefault: '#FooGallerySettings_default_captions_type0',
  captionTypeCustom: '#FooGallerySettings_default_captions_type1',

  // Admin - Caption Title Source
  captionTitleSourceDefault: '#FooGallerySettings_default_caption_title_source0',
  captionTitleSourceNone: '#FooGallerySettings_default_caption_title_source1',
  captionTitleSourceTitle: '#FooGallerySettings_default_caption_title_source2',
  captionTitleSourceCaption: '#FooGallerySettings_default_caption_title_source3',
  captionTitleSourceAlt: '#FooGallerySettings_default_caption_title_source4',
  captionTitleSourceDesc: '#FooGallerySettings_default_caption_title_source5',

  // Admin - Caption Description Source
  captionDescSourceDefault: '#FooGallerySettings_default_caption_desc_source0',
  captionDescSourceNone: '#FooGallerySettings_default_caption_desc_source1',
  captionDescSourceTitle: '#FooGallerySettings_default_caption_desc_source2',
  captionDescSourceCaption: '#FooGallerySettings_default_caption_desc_source3',
  captionDescSourceAlt: '#FooGallerySettings_default_caption_desc_source4',
  captionDescSourceDesc: '#FooGallerySettings_default_caption_desc_source5',

  // Admin - Length Limiting
  captionLimitNone: '#FooGallerySettings_default_captions_limit_length0',
  captionLimitChars: '#FooGallerySettings_default_captions_limit_length1',
  captionLimitClamp: '#FooGallerySettings_default_captions_limit_length2',
  captionTitleLength: '#FooGallerySettings_default_caption_title_length',
  captionDescLength: '#FooGallerySettings_default_caption_desc_length',
  captionTitleClamp: '#FooGallerySettings_default_caption_title_clamp',
  captionDescClamp: '#FooGallerySettings_default_caption_desc_clamp',

  // Admin - Custom Template
  captionCustomTemplate: '#FooGallerySettings_default_caption_custom_template',

  // Admin - Lightbox Info Enabled
  lightboxInfoEnabled: '#FooGallerySettings_default_lightbox_info_enabled0',
  lightboxInfoDisabled: '#FooGallerySettings_default_lightbox_info_enabled1',
  lightboxInfoHidden: '#FooGallerySettings_default_lightbox_info_enabled2',

  // Admin - Lightbox Position
  lightboxPositionBottom: '#FooGallerySettings_default_lightbox_info_position0',
  lightboxPositionTop: '#FooGallerySettings_default_lightbox_info_position1',
  lightboxPositionLeft: '#FooGallerySettings_default_lightbox_info_position2',
  lightboxPositionRight: '#FooGallerySettings_default_lightbox_info_position3',

  // Admin - Lightbox Alignment
  lightboxAlignmentDefault: '#FooGallerySettings_default_lightbox_info_alignment0',
  lightboxAlignmentLeft: '#FooGallerySettings_default_lightbox_info_alignment1',
  lightboxAlignmentCenter: '#FooGallerySettings_default_lightbox_info_alignment2',
  lightboxAlignmentRight: '#FooGallerySettings_default_lightbox_info_alignment3',
  lightboxAlignmentJustify: '#FooGallerySettings_default_lightbox_info_alignment4',

  // Admin - Lightbox Overlay
  lightboxOverlayYes: '#FooGallerySettings_default_lightbox_info_overlay0',
  lightboxOverlayNo: '#FooGallerySettings_default_lightbox_info_overlay1',

  // Admin - Lightbox Mobile Autohide
  lightboxMobileAutohideYes: '#FooGallerySettings_default_lightbox_info_autohide_mobile0',
  lightboxMobileAutohideNo: '#FooGallerySettings_default_lightbox_info_autohide_mobile1',

  // Admin - Lightbox Caption Override
  lightboxCaptionOverrideNone: '#FooGallerySettings_default_lightbox_caption_override0',
  lightboxCaptionOverride: '#FooGallerySettings_default_lightbox_caption_override1',
  lightboxCaptionCustom: '#FooGallerySettings_default_lightbox_caption_override2',

  // Admin - Lightbox Override Sources
  lightboxOverrideTitleDefault: '#FooGallerySettings_default_lightbox_caption_override_title0',
  lightboxOverrideTitleNone: '#FooGallerySettings_default_lightbox_caption_override_title1',
  lightboxOverrideTitleTitle: '#FooGallerySettings_default_lightbox_caption_override_title2',
  lightboxOverrideTitleCaption: '#FooGallerySettings_default_lightbox_caption_override_title3',
  lightboxOverrideTitleAlt: '#FooGallerySettings_default_lightbox_caption_override_title4',
  lightboxOverrideTitleDesc: '#FooGallerySettings_default_lightbox_caption_override_title5',
  lightboxOverrideDescDefault: '#FooGallerySettings_default_lightbox_caption_override_desc0',
  lightboxOverrideDescNone: '#FooGallerySettings_default_lightbox_caption_override_desc1',
  lightboxOverrideDescTitle: '#FooGallerySettings_default_lightbox_caption_override_desc2',
  lightboxOverrideDescCaption: '#FooGallerySettings_default_lightbox_caption_override_desc3',
  lightboxOverrideDescAlt: '#FooGallerySettings_default_lightbox_caption_override_desc4',
  lightboxOverrideDescDesc: '#FooGallerySettings_default_lightbox_caption_override_desc5',

  // Admin - Lightbox Custom Template
  lightboxCaptionCustomTemplate: '#FooGallerySettings_default_lightbox_caption_custom_template',

  // Frontend - Gallery
  galleryContainer: '.foogallery',
  galleryItem: '.fg-item',
  itemAnchor: '.fg-item a.fg-thumb',
  caption: 'figcaption.fg-caption',
  captionInner: '.fg-caption-inner',
  captionTitle: '.fg-caption-title',
  captionDesc: '.fg-caption-desc',

  // Frontend - Lightbox
  lightboxPanel: '.fg-panel',
  lightboxCaptionTitle: 'div.fg-media-caption-title',
  lightboxCaptionDescription: 'div.fg-media-caption-description',
  lightboxInfoButton: 'button.fg-panel-button-info > svg',
  lightboxCloseButton: 'button.fg-panel-button-close > svg',
  lightboxNextButton: 'button.fg-panel-button-next > svg',
  lightboxPrevButton: 'button.fg-panel-button-prev > svg',
} as const;

// Caption source options
export const CAPTION_SOURCES = {
  default: 0,
  none: 1,
  title: 2,
  caption: 3,
  alt: 4,
  desc: 5,
} as const;

// Caption alignment options
export const CAPTION_ALIGNMENTS = {
  default: 0,
  left: 1,
  center: 2,
  right: 3,
  justify: 4,
} as const;

// Caption alignment CSS classes
export const CAPTION_ALIGNMENT_CLASSES = {
  left: 'fg-c-l',
  center: 'fg-c-c',
  right: 'fg-c-r',
  justify: 'fg-c-j',
} as const;

// Lightbox position options
export const LIGHTBOX_POSITIONS = {
  bottom: 0,
  top: 1,
  left: 2,
  right: 3,
} as const;

// Lightbox info enabled options
export const LIGHTBOX_INFO_ENABLED = {
  enabled: 0,
  disabled: 1,
  hidden: 2,
} as const;

export interface CaptionTestOptions {
  galleryName: string;
  templateSelector: string;
  screenshotPrefix: string;
  imageCount?: number;
}

export interface CaptionSettingsOptions {
  titleSource?: keyof typeof CAPTION_SOURCES;
  descSource?: keyof typeof CAPTION_SOURCES;
  alignment?: keyof typeof CAPTION_ALIGNMENTS;
  customTemplate?: string;
  limitMode?: 'none' | 'chars' | 'clamp';
  titleLength?: number;
  descLength?: number;
  titleClamp?: number;
  descClamp?: number;
}

export interface LightboxCaptionSettingsOptions {
  enabled?: 'enabled' | 'disabled' | 'hidden';
  position?: keyof typeof LIGHTBOX_POSITIONS;
  alignment?: keyof typeof CAPTION_ALIGNMENTS;
  overlay?: boolean;
  mobileAutohide?: boolean;
  overrideMode?: 'none' | 'override' | 'custom';
  overrideTitleSource?: keyof typeof CAPTION_SOURCES;
  overrideDescSource?: keyof typeof CAPTION_SOURCES;
  customTemplate?: string;
}

export interface AttachmentCaptionData {
  title?: string;
  caption?: string;
  description?: string;
  altText?: string;
}

/**
 * Navigate to the Captions section in gallery settings (Appearance tab)
 * Captions is the 5th tab in the vertical tabs navigation
 */
export async function navigateToCaptionsTab(page: Page, templateSelector: string = 'default'): Promise<void> {
  // Scroll to Gallery Settings section
  const settingsSection = page.locator('#foogallery_settings');
  await settingsSection.scrollIntoViewIfNeeded();
  await page.waitForTimeout(500);

  // Click on Captions tab - it's the 5th tab in vertical tabs
  const captionsTab = page.locator(`div.foogallery-settings-container-${templateSelector} div.foogallery-vertical-tabs > div:nth-of-type(5)`);
  await captionsTab.scrollIntoViewIfNeeded();
  await captionsTab.click();
  await page.waitForTimeout(300);
}

/**
 * Navigate to the Lightbox tab in gallery settings
 */
export async function navigateToLightboxTab(page: Page, templateSelector: string = 'default'): Promise<void> {
  // Scroll to Gallery Settings section
  const settingsSection = page.locator('#foogallery_settings');
  await settingsSection.scrollIntoViewIfNeeded();
  await page.waitForTimeout(500);

  // Click on Lightbox tab - it's the 2nd tab in vertical tabs
  const lightboxTab = page.locator(`div.foogallery-settings-container-${templateSelector} div.foogallery-vertical-tabs > div:nth-of-type(2)`);
  await lightboxTab.scrollIntoViewIfNeeded();
  await lightboxTab.click();
  await page.waitForTimeout(300);
}

/**
 * Navigate to the Lightbox Info/Captions subtab
 */
export async function navigateToLightboxInfoTab(page: Page, templateSelector: string = 'default'): Promise<void> {
  // First navigate to Lightbox tab
  await navigateToLightboxTab(page, templateSelector);

  // Then click on the Info subtab (4th subtab in lightbox settings)
  const infoSubtab = page.locator(`div.foogallery-settings-container-${templateSelector} div.foogallery-tab-active div:nth-of-type(4) > span`);
  await infoSubtab.click();
  await page.waitForTimeout(300);
}

/**
 * Set caption title source
 */
export async function setCaptionTitleSource(
  page: Page,
  source: keyof typeof CAPTION_SOURCES,
  templateSelector: string = 'default'
): Promise<void> {
  const sourceIndex = CAPTION_SOURCES[source];
  const selector = `#FooGallerySettings_${templateSelector}_caption_title_source${sourceIndex}`;
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set caption description source
 */
export async function setCaptionDescSource(
  page: Page,
  source: keyof typeof CAPTION_SOURCES,
  templateSelector: string = 'default'
): Promise<void> {
  const sourceIndex = CAPTION_SOURCES[source];
  const selector = `#FooGallerySettings_${templateSelector}_caption_desc_source${sourceIndex}`;
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set caption alignment
 */
export async function setCaptionAlignment(
  page: Page,
  alignment: keyof typeof CAPTION_ALIGNMENTS,
  templateSelector: string = 'default'
): Promise<void> {
  const alignmentIndex = CAPTION_ALIGNMENTS[alignment];
  const selector = `#FooGallerySettings_${templateSelector}_caption_alignment${alignmentIndex}`;
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Enable custom caption template
 */
export async function enableCustomCaptionTemplate(
  page: Page,
  template: string,
  templateSelector: string = 'default'
): Promise<void> {
  // Click "Custom" radio button for caption type
  const customTypeSelector = `#FooGallerySettings_${templateSelector}_captions_type1`;
  await page.click(customTypeSelector, { force: true });
  await page.waitForTimeout(300);

  // Fill in the custom template textarea
  const templateInput = page.locator(`#FooGallerySettings_${templateSelector}_caption_custom_template`);
  await templateInput.fill(template);
  await page.waitForTimeout(200);
}

/**
 * Set length limiting mode and values
 */
export async function setLengthLimiting(
  page: Page,
  mode: 'none' | 'chars' | 'clamp',
  options?: {
    titleLength?: number;
    descLength?: number;
    titleClamp?: number;
    descClamp?: number;
  },
  templateSelector: string = 'default'
): Promise<void> {
  const modeIndex = mode === 'none' ? 0 : mode === 'chars' ? 1 : 2;
  const modeSelector = `#FooGallerySettings_${templateSelector}_captions_limit_length${modeIndex}`;
  await page.click(modeSelector, { force: true });
  await page.waitForTimeout(300);

  if (options) {
    if (mode === 'chars') {
      if (options.titleLength !== undefined) {
        await page.fill(`#FooGallerySettings_${templateSelector}_caption_title_length`, String(options.titleLength));
      }
      if (options.descLength !== undefined) {
        await page.fill(`#FooGallerySettings_${templateSelector}_caption_desc_length`, String(options.descLength));
      }
    }

    if (mode === 'clamp') {
      if (options.titleClamp !== undefined) {
        await page.fill(`#FooGallerySettings_${templateSelector}_caption_title_clamp`, String(options.titleClamp));
      }
      if (options.descClamp !== undefined) {
        await page.fill(`#FooGallerySettings_${templateSelector}_caption_desc_clamp`, String(options.descClamp));
      }
    }
  }
}

/**
 * Configure caption settings
 */
export async function configureCaptionSettings(
  page: Page,
  templateSelector: string,
  options: CaptionSettingsOptions
): Promise<void> {
  // Navigate to captions tab first
  await navigateToCaptionsTab(page, templateSelector);

  if (options.titleSource !== undefined) {
    await setCaptionTitleSource(page, options.titleSource, templateSelector);
  }

  if (options.descSource !== undefined) {
    await setCaptionDescSource(page, options.descSource, templateSelector);
  }

  if (options.alignment !== undefined) {
    await setCaptionAlignment(page, options.alignment, templateSelector);
  }

  if (options.customTemplate !== undefined) {
    await enableCustomCaptionTemplate(page, options.customTemplate, templateSelector);
  }

  if (options.limitMode !== undefined) {
    await setLengthLimiting(page, options.limitMode, {
      titleLength: options.titleLength,
      descLength: options.descLength,
      titleClamp: options.titleClamp,
      descClamp: options.descClamp,
    }, templateSelector);
  }
}

/**
 * Set lightbox info enabled state
 */
export async function setLightboxInfoEnabled(
  page: Page,
  enabled: 'enabled' | 'disabled' | 'hidden',
  templateSelector: string = 'default'
): Promise<void> {
  const enabledIndex = LIGHTBOX_INFO_ENABLED[enabled];
  const selector = `#FooGallerySettings_${templateSelector}_lightbox_info_enabled${enabledIndex}`;
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set lightbox caption position
 */
export async function setLightboxCaptionPosition(
  page: Page,
  position: keyof typeof LIGHTBOX_POSITIONS,
  templateSelector: string = 'default'
): Promise<void> {
  const positionIndex = LIGHTBOX_POSITIONS[position];
  const selector = `#FooGallerySettings_${templateSelector}_lightbox_info_position${positionIndex}`;
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set lightbox caption alignment
 */
export async function setLightboxCaptionAlignment(
  page: Page,
  alignment: keyof typeof CAPTION_ALIGNMENTS,
  templateSelector: string = 'default'
): Promise<void> {
  const alignmentIndex = CAPTION_ALIGNMENTS[alignment];
  const selector = `#FooGallerySettings_${templateSelector}_lightbox_info_alignment${alignmentIndex}`;
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set lightbox overlay mode
 */
export async function setLightboxOverlay(
  page: Page,
  overlay: boolean,
  templateSelector: string = 'default'
): Promise<void> {
  const selector = `#FooGallerySettings_${templateSelector}_lightbox_info_overlay${overlay ? 0 : 1}`;
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set lightbox mobile autohide
 */
export async function setLightboxMobileAutohide(
  page: Page,
  autohide: boolean,
  templateSelector: string = 'default'
): Promise<void> {
  const selector = `#FooGallerySettings_${templateSelector}_lightbox_info_autohide_mobile${autohide ? 0 : 1}`;
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set lightbox caption override mode
 */
export async function setLightboxCaptionOverride(
  page: Page,
  mode: 'none' | 'override' | 'custom',
  templateSelector: string = 'default'
): Promise<void> {
  const modeIndex = mode === 'none' ? 0 : mode === 'override' ? 1 : 2;
  const selector = `#FooGallerySettings_${templateSelector}_lightbox_caption_override${modeIndex}`;
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set lightbox override title source
 */
export async function setLightboxOverrideTitleSource(
  page: Page,
  source: keyof typeof CAPTION_SOURCES,
  templateSelector: string = 'default'
): Promise<void> {
  const sourceIndex = CAPTION_SOURCES[source];
  const selector = `#FooGallerySettings_${templateSelector}_lightbox_caption_override_title${sourceIndex}`;
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set lightbox override description source
 */
export async function setLightboxOverrideDescSource(
  page: Page,
  source: keyof typeof CAPTION_SOURCES,
  templateSelector: string = 'default'
): Promise<void> {
  const sourceIndex = CAPTION_SOURCES[source];
  const selector = `#FooGallerySettings_${templateSelector}_lightbox_caption_override_desc${sourceIndex}`;
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set lightbox custom caption template
 */
export async function setLightboxCustomTemplate(
  page: Page,
  template: string,
  templateSelector: string = 'default'
): Promise<void> {
  const templateInput = page.locator(`#FooGallerySettings_${templateSelector}_lightbox_caption_custom_template`);
  await templateInput.fill(template);
  await page.waitForTimeout(200);
}

/**
 * Configure lightbox caption settings
 */
export async function configureLightboxCaptionSettings(
  page: Page,
  templateSelector: string,
  options: LightboxCaptionSettingsOptions
): Promise<void> {
  // Navigate to lightbox info tab first
  await navigateToLightboxInfoTab(page, templateSelector);

  if (options.enabled !== undefined) {
    await setLightboxInfoEnabled(page, options.enabled, templateSelector);
  }

  if (options.position !== undefined) {
    await setLightboxCaptionPosition(page, options.position, templateSelector);
  }

  if (options.alignment !== undefined) {
    await setLightboxCaptionAlignment(page, options.alignment, templateSelector);
  }

  if (options.overlay !== undefined) {
    await setLightboxOverlay(page, options.overlay, templateSelector);
  }

  if (options.mobileAutohide !== undefined) {
    await setLightboxMobileAutohide(page, options.mobileAutohide, templateSelector);
  }

  if (options.overrideMode !== undefined) {
    await setLightboxCaptionOverride(page, options.overrideMode, templateSelector);

    if (options.overrideMode === 'override') {
      if (options.overrideTitleSource !== undefined) {
        await setLightboxOverrideTitleSource(page, options.overrideTitleSource, templateSelector);
      }
      if (options.overrideDescSource !== undefined) {
        await setLightboxOverrideDescSource(page, options.overrideDescSource, templateSelector);
      }
    }

    if (options.overrideMode === 'custom' && options.customTemplate !== undefined) {
      await setLightboxCustomTemplate(page, options.customTemplate, templateSelector);
    }
  }
}

/**
 * Open attachment modal for a gallery item
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
      // Fallback: click on the thumbnail
      const thumbnail = listItem.locator('.attachment-preview, .thumbnail');
      if (await thumbnail.isVisible()) {
        await thumbnail.click();
        await page.waitForTimeout(500);
      }
    }
  }

  // Wait for the attachment details modal to open
  await page.waitForSelector(CAPTION_SELECTORS.attachmentModal, { state: 'visible', timeout: 10000 });
}

/**
 * Edit attachment caption data in modal
 */
export async function editAttachmentCaptions(page: Page, data: AttachmentCaptionData): Promise<void> {
  if (data.title !== undefined) {
    await page.fill(CAPTION_SELECTORS.attachmentTitle, data.title);
  }
  if (data.caption !== undefined) {
    await page.fill(CAPTION_SELECTORS.attachmentCaption, data.caption);
  }
  if (data.description !== undefined) {
    await page.fill(CAPTION_SELECTORS.attachmentDescription, data.description);
  }
  if (data.altText !== undefined) {
    await page.fill(CAPTION_SELECTORS.attachmentAltText, data.altText);
  }

  // CRITICAL: Must save before closing
  await page.click(CAPTION_SELECTORS.attachmentSaveButton);
  await page.waitForTimeout(500);
}

/**
 * Close attachment modal
 */
export async function closeAttachmentModal(page: Page): Promise<void> {
  await page.click(CAPTION_SELECTORS.attachmentCloseButton);
  await page.waitForTimeout(300);
}

/**
 * Get caption from frontend gallery
 */
export async function getCaptionFromGallery(page: Page, itemIndex: number = 0): Promise<{ title: string; desc: string }> {
  const item = page.locator(CAPTION_SELECTORS.galleryItem).nth(itemIndex);
  const titleEl = item.locator(CAPTION_SELECTORS.captionTitle);
  const descEl = item.locator(CAPTION_SELECTORS.captionDesc);

  const title = (await titleEl.isVisible()) ? (await titleEl.textContent()) || '' : '';
  const desc = (await descEl.isVisible()) ? (await descEl.textContent()) || '' : '';

  return { title: title.trim(), desc: desc.trim() };
}

/**
 * Get caption from lightbox
 */
export async function getCaptionFromLightbox(page: Page): Promise<{ title: string; desc: string }> {
  const titleEl = page.locator(CAPTION_SELECTORS.lightboxCaptionTitle);
  const descEl = page.locator(CAPTION_SELECTORS.lightboxCaptionDescription);

  const title = (await titleEl.isVisible()) ? (await titleEl.textContent()) || '' : '';
  const desc = (await descEl.isVisible()) ? (await descEl.textContent()) || '' : '';

  return { title: title.trim(), desc: desc.trim() };
}

/**
 * Open lightbox by clicking gallery item
 */
export async function openLightbox(page: Page, itemIndex: number = 0): Promise<void> {
  await page.locator(CAPTION_SELECTORS.itemAnchor).nth(itemIndex).click({ force: true });
  await page.waitForSelector(CAPTION_SELECTORS.lightboxPanel, { state: 'visible', timeout: 10000 });
  await page.waitForTimeout(500);
}

/**
 * Close lightbox
 */
export async function closeLightbox(page: Page): Promise<void> {
  await page.click(CAPTION_SELECTORS.lightboxCloseButton);
  await page.waitForSelector(CAPTION_SELECTORS.lightboxPanel, { state: 'hidden', timeout: 5000 });
}

/**
 * Navigate to next image in lightbox
 */
export async function navigateToNextInLightbox(page: Page): Promise<void> {
  await page.click(CAPTION_SELECTORS.lightboxNextButton);
  await page.waitForTimeout(500);
}

/**
 * Navigate to previous image in lightbox
 */
export async function navigateToPrevInLightbox(page: Page): Promise<void> {
  await page.click(CAPTION_SELECTORS.lightboxPrevButton);
  await page.waitForTimeout(500);
}

/**
 * Toggle lightbox info panel
 */
export async function toggleLightboxInfo(page: Page): Promise<void> {
  await page.click(CAPTION_SELECTORS.lightboxInfoButton);
  await page.waitForTimeout(300);
}

/**
 * Verify caption alignment class on frontend
 */
export async function verifyCaptionAlignment(
  page: Page,
  alignment: 'left' | 'center' | 'right' | 'justify'
): Promise<boolean> {
  const expectedClass = CAPTION_ALIGNMENT_CLASSES[alignment];
  const gallery = page.locator(CAPTION_SELECTORS.galleryContainer);
  const hasClass = await gallery.evaluate((el, cls) => el.classList.contains(cls), expectedClass);
  return hasClass;
}

/**
 * Get data attributes from gallery item anchor
 */
export async function getDataAttributes(page: Page, itemIndex: number = 0): Promise<{
  captionTitle: string | null;
  captionDesc: string | null;
  lightboxTitle: string | null;
  lightboxDesc: string | null;
}> {
  const anchor = page.locator(CAPTION_SELECTORS.itemAnchor).nth(itemIndex);
  return {
    captionTitle: await anchor.getAttribute('data-caption-title'),
    captionDesc: await anchor.getAttribute('data-caption-desc'),
    lightboxTitle: await anchor.getAttribute('data-lightbox-title'),
    lightboxDesc: await anchor.getAttribute('data-lightbox-description'),
  };
}

/**
 * Create a gallery with images and navigate to edit page
 */
export async function createGalleryWithImages(
  page: Page,
  options: CaptionTestOptions
): Promise<string> {
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

  // Select first N images
  for (let i = 0; i < imageCount; i++) {
    await attachments.nth(i).click();
  }

  // Click "Add to Gallery"
  const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
  await addButton.click();
  await page.waitForLoadState('networkidle');

  // Screenshot: Images added
  await page.screenshot({ path: `test-results/${screenshotPrefix}-02-images-added.png` });

  // Publish gallery
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
 * Create gallery and navigate to frontend page
 */
export async function createGalleryAndNavigateToPage(
  page: Page,
  options: CaptionTestOptions,
  captionSettings?: CaptionSettingsOptions,
  lightboxSettings?: LightboxCaptionSettingsOptions
): Promise<void> {
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

  // Select first N images
  for (let i = 0; i < imageCount; i++) {
    await attachments.nth(i).click();
  }

  // Click "Add to Gallery"
  const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
  await addButton.click();
  await page.waitForLoadState('networkidle');

  // Configure caption settings if provided
  if (captionSettings) {
    await configureCaptionSettings(page, templateSelector, captionSettings);
  }

  // Configure lightbox caption settings if provided
  if (lightboxSettings) {
    await configureLightboxCaptionSettings(page, templateSelector, lightboxSettings);
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
 * Check if Captions tab is visible in gallery settings
 */
export async function isCaptionsTabVisible(page: Page, templateSelector: string = 'default'): Promise<boolean> {
  const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
  const captionsTab = templateContainer.locator(`div.foogallery-vertical-tabs > div[data-name="${templateSelector}-captions"]`);
  return await captionsTab.isVisible();
}

/**
 * Verify caption HTML structure on frontend
 */
export async function verifyCaptionStructure(page: Page, itemIndex: number = 0): Promise<{
  hasFigcaption: boolean;
  hasInner: boolean;
  hasTitle: boolean;
  hasDesc: boolean;
}> {
  const item = page.locator(CAPTION_SELECTORS.galleryItem).nth(itemIndex);

  return {
    hasFigcaption: await item.locator(CAPTION_SELECTORS.caption).isVisible(),
    hasInner: await item.locator(CAPTION_SELECTORS.captionInner).isVisible(),
    hasTitle: await item.locator(CAPTION_SELECTORS.captionTitle).isVisible(),
    hasDesc: await item.locator(CAPTION_SELECTORS.captionDesc).isVisible(),
  };
}

/**
 * Get CSS variable value from gallery element
 */
export async function getCssVariable(page: Page, variableName: string): Promise<string> {
  const gallery = page.locator(CAPTION_SELECTORS.galleryContainer);
  return await gallery.evaluate((el, varName) => {
    return getComputedStyle(el).getPropertyValue(varName).trim();
  }, variableName);
}
