// File: tests/helpers/album-test-helper.ts
// Shared helper for album E2E tests

import { Page, expect } from '@playwright/test';

// Album selectors based on recordings and codebase analysis
export const ALBUM_SELECTORS = {
  admin: {
    // Menu navigation
    albumsMenu: '#menu-posts-foogallery li:nth-of-type(6) > a',
    addNewButton: 'div.wrap > a',
    titleInput: '#title',
    publishButton: '#publish',
    updateButton: '#publish',
    shortcodeInput: '#foogallery_copy_shortcode',
    createPageButton: '#foogallery_create_page',

    // Template & galleries
    templateSelect: '#FooGallerySettings_AlbumTemplate',
    galleryList: '.foogallery-album-gallery-list',
    galleryItem: 'div.foogallery-gallery-select[data-foogallery-id]',

    // Default template settings
    defaultThumbWidth: '#FooGallerySettings_default_thumbnail_dimensions_width',
    defaultThumbHeight: '#FooGallerySettings_default_thumbnail_dimensions_height',
    defaultTitleBgRow: 'tr.foogallery_template_field-default-title_bg',
    defaultTitleFontColorRow: 'tr.foogallery_template_field-default-title_font_color',
    defaultAlignment: '#FooGallerySettings_default_alignment',
    defaultGalleryLinkPretty: '#FooGallerySettings_default_gallery_link0',
    defaultGalleryLinkQuery: '#FooGallerySettings_default_gallery_link1',
    defaultLinkFormatPretty: '#FooGallerySettings_default_gallery_link_format0',
    defaultLinkFormatQuery: '#FooGallerySettings_default_gallery_link_format1',
    defaultAlbumHashYes: '#FooGallerySettings_default_album_hash0',
    defaultAlbumHashNo: '#FooGallerySettings_default_album_hash1',
    defaultTitleSize: '#FooGallerySettings_default_gallery_title_size',

    // Stack template settings
    stackLightbox: '#FooGallerySettings_stack_lightbox',
    stackRandomAngleYes: '#FooGallerySettings_stack_random_angle1',
    stackRandomAngleNo: '#FooGallerySettings_stack_random_angle0',
    stackThumbWidth: '#FooGallerySettings_stack_thumbnail_dimensions_width',
    stackThumbHeight: '#FooGallerySettings_stack_thumbnail_dimensions_height',
    stackGutter: '#FooGallerySettings_stack_gutter',
    stackPileAnglesRow: 'tr.foogallery_template_field-stack-pile_angles',
    stackPileAngles1: '#FooGallerySettings_stack_pile_angles0',
    stackPileAngles2: '#FooGallerySettings_stack_pile_angles2',
    stackPileAngles3: '#FooGallerySettings_stack_pile_angles3',
  },
  frontend: {
    // Default template
    albumContainer: '.foogallery-album-gallery-list',
    galleryList: '.foogallery-album-gallery-list',
    pile: '.foogallery-pile',
    pileInner: '.foogallery-pile-inner',
    pileTitle: '.foogallery-pile h3',
    pileCount: '.foogallery-pile span',
    backLink: '.foogallery-album-back a, .foogallery-album-header a',

    // Stack template
    stackContainer: '.foogallery-stack-album',
    stackPiles: '.fg-piles',
    stackPile: '.fg-pile',
    stackPileItem: '.fg-pile-item',
    stackHeader: '.fg-header-title',
    stackBackButton: '.fg-header-back',

    // Gallery (when viewing inside album)
    galleryContainer: '.foogallery',
    galleryItem: '.fg-item',
    galleryImage: '.fg-item a.fg-thumb',

    // Lightbox
    lightboxPanel: '.fg-panel',
    lightboxContent: '.fg-panel-content',
    lightboxClose: 'button.fg-panel-button-close',
    lightboxNext: 'button.fg-panel-button-next',
    lightboxPrev: 'button.fg-panel-button-prev',
  },
} as const;

export interface AlbumTestOptions {
  albumName: string;
  template?: 'default' | 'stack';
  galleryCount?: number;
  screenshotPrefix?: string;
}

export interface DefaultTemplateSettings {
  thumbWidth?: number;
  thumbHeight?: number;
  alignment?: 'left' | 'center' | 'right';
  galleryLink?: 'pretty' | 'querystring';
  linkFormat?: 'pretty' | 'querystring';
  albumHash?: boolean;
  titleSize?: 'h2' | 'h3' | 'h4' | 'h5' | 'h6';
}

export interface StackTemplateSettings {
  randomAngle?: boolean;
  thumbWidth?: number;
  thumbHeight?: number;
  gutter?: number;
  pileAngle?: 1 | 2 | 3;
}

/**
 * Ensure at least the specified number of galleries exist.
 * Returns array of gallery IDs.
 */
export async function ensureGalleriesExist(page: Page, count: number = 3): Promise<string[]> {
  await page.goto('/wp-admin/edit.php?post_type=foogallery');
  await page.waitForLoadState('domcontentloaded');

  const rows = page.locator('table.wp-list-table tbody tr:not(.no-items)');
  const existing = await rows.count();

  if (existing >= count) {
    // Return IDs of first N galleries
    const ids: string[] = [];
    for (let i = 0; i < count; i++) {
      const href = await rows.nth(i).locator('a.row-title').getAttribute('href');
      const match = href?.match(/post=(\d+)/);
      if (match) ids.push(match[1]);
    }
    return ids;
  }

  // Create missing galleries
  const idsNeeded = count - existing;
  const ids: string[] = [];

  // First collect existing IDs
  for (let i = 0; i < existing; i++) {
    const href = await rows.nth(i).locator('a.row-title').getAttribute('href');
    const match = href?.match(/post=(\d+)/);
    if (match) ids.push(match[1]);
  }

  // Create new galleries
  for (let i = 0; i < idsNeeded; i++) {
    const newId = await createSimpleGallery(page, `Test Gallery ${existing + i + 1}`);
    if (newId) ids.push(newId);
  }

  return ids;
}

/**
 * Create a simple gallery with images for use in albums
 */
async function createSimpleGallery(page: Page, name: string): Promise<string | null> {
  await page.goto('/wp-admin/post-new.php?post_type=foogallery');
  await page.waitForLoadState('domcontentloaded');

  // Enter title
  await page.locator('#title').fill(name);

  // Select default template
  const templateCard = page.locator('[data-template="default"]');
  await templateCard.waitFor({ state: 'visible', timeout: 10000 });
  await templateCard.click();

  // Add images from media library
  await page.locator('text=Add From Media Library').click();
  await page.waitForLoadState('networkidle');

  const modal = page.locator('.media-modal:visible');
  await modal.waitFor({ state: 'visible', timeout: 10000 });

  const mediaLibraryTab = modal.locator('.media-menu-item').filter({ hasText: 'Media Library' });
  await mediaLibraryTab.click();

  const attachments = modal.locator('.attachment');
  await attachments.first().waitFor({ state: 'visible', timeout: 10000 });

  // Select 3 images
  for (let i = 0; i < 3; i++) {
    const attachment = attachments.nth(i);
    if (await attachment.count() > 0) {
      await attachment.click();
    }
  }

  const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
  await addButton.click();
  await page.waitForLoadState('networkidle');

  // Publish
  await page.locator('#publish').click();
  await page.waitForLoadState('networkidle');

  // Extract ID from URL
  const url = page.url();
  const match = url.match(/post=(\d+)/);
  return match ? match[1] : null;
}

/**
 * Navigate to Albums admin and click Add New
 */
export async function navigateToAddNewAlbum(page: Page): Promise<void> {
  await page.goto('/wp-admin/edit.php?post_type=foogallery-album');
  await page.waitForLoadState('domcontentloaded');

  // Click Add New
  await page.locator('a.page-title-action, a:has-text("Add New Album")').first().click();
  await page.waitForLoadState('domcontentloaded');
}

/**
 * Select album template
 */
export async function selectAlbumTemplate(page: Page, template: 'default' | 'stack'): Promise<void> {
  const select = page.locator(ALBUM_SELECTORS.admin.templateSelect);
  await select.waitFor({ state: 'visible', timeout: 10000 });

  if (template === 'stack') {
    await select.selectOption({ label: 'All-In-One Stack Album' });
  } else {
    await select.selectOption({ label: 'Responsive Album Layout' });
  }
  await page.waitForTimeout(500); // Wait for settings to update
}

/**
 * Select galleries for the album by clicking on them
 */
export async function selectGalleries(page: Page, count: number = 3): Promise<void> {
  const galleryItems = page.locator(ALBUM_SELECTORS.admin.galleryItem);
  await galleryItems.first().waitFor({ state: 'visible', timeout: 10000 });

  const availableCount = await galleryItems.count();
  const toSelect = Math.min(count, availableCount);

  for (let i = 0; i < toSelect; i++) {
    await galleryItems.nth(i).click();
    await page.waitForTimeout(200);
  }
}

/**
 * Configure default template settings
 */
export async function configureDefaultSettings(page: Page, settings: DefaultTemplateSettings): Promise<void> {
  if (settings.thumbWidth) {
    const widthInput = page.locator(ALBUM_SELECTORS.admin.defaultThumbWidth);
    await widthInput.fill(String(settings.thumbWidth));
  }

  if (settings.thumbHeight) {
    const heightInput = page.locator(ALBUM_SELECTORS.admin.defaultThumbHeight);
    await heightInput.fill(String(settings.thumbHeight));
  }

  if (settings.alignment) {
    const alignSelect = page.locator(ALBUM_SELECTORS.admin.defaultAlignment);
    // Capitalize first letter for the dropdown option
    const capitalizedAlignment = settings.alignment.charAt(0).toUpperCase() + settings.alignment.slice(1);
    await alignSelect.selectOption(capitalizedAlignment);
  }

  if (settings.galleryLink) {
    if (settings.galleryLink === 'pretty') {
      await page.locator(ALBUM_SELECTORS.admin.defaultGalleryLinkPretty).click();
    } else {
      await page.locator(ALBUM_SELECTORS.admin.defaultGalleryLinkQuery).click();
    }
  }

  if (settings.linkFormat) {
    if (settings.linkFormat === 'pretty') {
      await page.locator(ALBUM_SELECTORS.admin.defaultLinkFormatPretty).click();
    } else {
      await page.locator(ALBUM_SELECTORS.admin.defaultLinkFormatQuery).click();
    }
  }

  if (settings.albumHash !== undefined) {
    if (settings.albumHash) {
      await page.locator(ALBUM_SELECTORS.admin.defaultAlbumHashYes).click();
    } else {
      await page.locator(ALBUM_SELECTORS.admin.defaultAlbumHashNo).click();
    }
  }

  if (settings.titleSize) {
    const titleSelect = page.locator(ALBUM_SELECTORS.admin.defaultTitleSize);
    await titleSelect.selectOption(settings.titleSize);
  }

  await page.waitForTimeout(300);
}

/**
 * Configure stack template settings
 */
export async function configureStackSettings(page: Page, settings: StackTemplateSettings): Promise<void> {
  if (settings.randomAngle !== undefined) {
    if (settings.randomAngle) {
      await page.locator(ALBUM_SELECTORS.admin.stackRandomAngleYes).click();
    } else {
      await page.locator(ALBUM_SELECTORS.admin.stackRandomAngleNo).click();
    }
  }

  if (settings.thumbWidth) {
    const widthInput = page.locator(ALBUM_SELECTORS.admin.stackThumbWidth);
    await widthInput.fill(String(settings.thumbWidth));
  }

  if (settings.thumbHeight) {
    const heightInput = page.locator(ALBUM_SELECTORS.admin.stackThumbHeight);
    await heightInput.fill(String(settings.thumbHeight));
  }

  if (settings.gutter !== undefined) {
    const gutterInput = page.locator(ALBUM_SELECTORS.admin.stackGutter);
    await gutterInput.fill(String(settings.gutter));
  }

  if (settings.pileAngle) {
    // Use label clicks for pile angle radio buttons
    const pileAnglesRow = page.locator(ALBUM_SELECTORS.admin.stackPileAnglesRow);
    const labels = pileAnglesRow.locator('label');
    await labels.nth(settings.pileAngle - 1).click();
  }

  await page.waitForTimeout(300);
}

/**
 * Publish album and extract ID
 */
export async function publishAlbum(page: Page): Promise<string> {
  await page.locator(ALBUM_SELECTORS.admin.publishButton).click();
  await page.waitForLoadState('networkidle');

  // Wait for publish to complete
  await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

  const url = page.url();
  const match = url.match(/post=(\d+)/);
  return match ? match[1] : '';
}

/**
 * Update an existing album
 */
export async function updateAlbum(page: Page): Promise<void> {
  await page.locator(ALBUM_SELECTORS.admin.updateButton).click();
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(500);
}

/**
 * Get album shortcode from admin
 */
export async function getAlbumShortcode(page: Page): Promise<string> {
  const shortcodeInput = page.locator(ALBUM_SELECTORS.admin.shortcodeInput);
  await shortcodeInput.waitFor({ state: 'visible', timeout: 5000 });
  return await shortcodeInput.inputValue();
}

/**
 * Create a page with the album shortcode and navigate to it
 * Note: Albums don't have a "Create Album Page" button like galleries,
 * so we need to create the page manually using classic editor for simplicity
 */
export async function createPageWithAlbum(page: Page): Promise<string> {
  // Get the shortcode from the album page
  const shortcode = await getAlbumShortcode(page);

  // Get album title for page name
  const albumTitle = await page.locator(ALBUM_SELECTORS.admin.titleInput).inputValue();
  const pageTitle = `${albumTitle} Page`;
  const slug = albumTitle.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '') + '-page';

  // Navigate to create new page using classic editor (add classic-editor parameter)
  await page.goto('/wp-admin/post-new.php?post_type=page&classic-editor');
  await page.waitForLoadState('domcontentloaded');

  // Wait for page to be ready
  await page.waitForTimeout(500);

  // Check which editor we're in and handle accordingly
  const classicTitle = page.locator('#title');
  const blockTitle = page.locator('[aria-label="Add title"], .editor-post-title__input');

  if (await classicTitle.isVisible()) {
    // Classic editor
    await classicTitle.fill(pageTitle);

    // Add shortcode to content
    const contentArea = page.locator('#content');
    if (await contentArea.isVisible()) {
      await contentArea.fill(shortcode);
    }

    // Publish
    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');

  } else if (await blockTitle.first().isVisible()) {
    // Block editor - use keyboard to type shortcode
    await blockTitle.first().fill(pageTitle);
    await page.waitForTimeout(300);

    // Click in content area and type shortcode
    await page.keyboard.press('Tab');
    await page.keyboard.type(shortcode);
    await page.waitForTimeout(300);

    // Click Publish button (first click to open panel)
    const publishButton = page.locator('button.editor-post-publish-panel__toggle, button:has-text("Publish"):visible').first();
    if (await publishButton.isVisible()) {
      await publishButton.click();
      await page.waitForTimeout(500);
    }

    // Confirm publish (second click if panel opened)
    const confirmButton = page.locator('button.editor-post-publish-button:visible');
    if (await confirmButton.count() > 0) {
      await confirmButton.first().click();
      await page.waitForTimeout(2000);
    }
  }

  // Construct the URL and navigate
  const viewUrl = `/${slug}/`;
  await page.goto(viewUrl);
  await page.waitForLoadState('networkidle');

  return viewUrl;
}

/**
 * Full album creation flow
 */
export async function createAlbumWithGalleries(page: Page, options: AlbumTestOptions): Promise<{ albumId: string; pageUrl: string }> {
  const { albumName, template = 'default', galleryCount = 3, screenshotPrefix } = options;

  await page.setViewportSize({ width: 1932, height: 1271 });

  // Ensure galleries exist
  await ensureGalleriesExist(page, galleryCount);

  // Navigate to Add New Album
  await navigateToAddNewAlbum(page);

  // Enter title
  await page.locator(ALBUM_SELECTORS.admin.titleInput).fill(albumName);

  // Select template
  await selectAlbumTemplate(page, template);

  // Select galleries
  await selectGalleries(page, galleryCount);

  if (screenshotPrefix) {
    await page.screenshot({ path: `test-results/${screenshotPrefix}-configured.png` });
  }

  // Publish
  const albumId = await publishAlbum(page);

  if (screenshotPrefix) {
    await page.screenshot({ path: `test-results/${screenshotPrefix}-published.png` });
  }

  // Create page and navigate
  const pageUrl = await createPageWithAlbum(page);

  if (screenshotPrefix) {
    await page.screenshot({ path: `test-results/${screenshotPrefix}-frontend.png` });
  }

  return { albumId, pageUrl };
}

/**
 * Click a gallery pile on the album frontend (default template)
 */
export async function clickGalleryPile(page: Page, index: number = 0): Promise<void> {
  const piles = page.locator(ALBUM_SELECTORS.frontend.pile);
  await piles.nth(index).click();
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(500);
}

/**
 * Click a gallery pile on the stack album frontend
 */
export async function clickStackPile(page: Page, index: number = 0): Promise<void> {
  const piles = page.locator(ALBUM_SELECTORS.frontend.stackPile);
  await piles.nth(index).click();
  await page.waitForTimeout(500);
}

/**
 * Navigate back to album from gallery view (default template)
 */
export async function navigateBackToAlbum(page: Page): Promise<void> {
  const backLink = page.locator(ALBUM_SELECTORS.frontend.backLink);
  await backLink.first().click();
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(500);
}

/**
 * Navigate back to album from stack view
 */
export async function navigateBackFromStack(page: Page): Promise<void> {
  const backButton = page.locator(ALBUM_SELECTORS.frontend.stackBackButton);
  await backButton.click();
  await page.waitForTimeout(500);
}

/**
 * Open lightbox by clicking on an image in the gallery
 */
export async function openLightbox(page: Page, imageIndex: number = 0): Promise<void> {
  const images = page.locator(ALBUM_SELECTORS.frontend.galleryImage);
  await images.nth(imageIndex).click({ force: true });
  await page.waitForSelector(ALBUM_SELECTORS.frontend.lightboxContent, { state: 'visible', timeout: 10000 });
}

/**
 * Close the lightbox
 */
export async function closeLightbox(page: Page): Promise<void> {
  const closeButton = page.locator(ALBUM_SELECTORS.frontend.lightboxClose);
  await closeButton.click();
  await page.waitForSelector(ALBUM_SELECTORS.frontend.lightboxContent, { state: 'hidden', timeout: 10000 });
}

/**
 * Navigate to next image in lightbox
 */
export async function lightboxNext(page: Page): Promise<void> {
  await page.locator(ALBUM_SELECTORS.frontend.lightboxNext).click();
  await page.waitForTimeout(300);
}

/**
 * Navigate to previous image in lightbox
 */
export async function lightboxPrev(page: Page): Promise<void> {
  await page.locator(ALBUM_SELECTORS.frontend.lightboxPrev).click();
  await page.waitForTimeout(300);
}

/**
 * Wait for album to be ready on frontend
 */
export async function waitForAlbumReady(page: Page, template: 'default' | 'stack' = 'default'): Promise<void> {
  if (template === 'stack') {
    await page.waitForSelector(ALBUM_SELECTORS.frontend.stackContainer, { state: 'visible', timeout: 15000 });
    await page.waitForSelector(ALBUM_SELECTORS.frontend.stackPile, { state: 'visible', timeout: 15000 });
  } else {
    await page.waitForSelector(ALBUM_SELECTORS.frontend.albumContainer, { state: 'visible', timeout: 15000 });
    await page.waitForSelector(ALBUM_SELECTORS.frontend.pile, { state: 'visible', timeout: 15000 });
  }
  await page.waitForTimeout(500);
}

/**
 * Wait for gallery inside album to be ready
 */
export async function waitForGalleryInAlbum(page: Page): Promise<void> {
  await page.waitForSelector(ALBUM_SELECTORS.frontend.galleryContainer, { state: 'visible', timeout: 15000 });
  await page.waitForSelector(ALBUM_SELECTORS.frontend.galleryItem, { state: 'visible', timeout: 15000 });
  await page.waitForTimeout(500);
}

/**
 * Get count of gallery piles in album
 */
export async function getPileCount(page: Page, template: 'default' | 'stack' = 'default'): Promise<number> {
  if (template === 'stack') {
    return await page.locator(ALBUM_SELECTORS.frontend.stackPile).count();
  }
  return await page.locator(ALBUM_SELECTORS.frontend.pile).count();
}

/**
 * Get gallery titles from album piles
 */
export async function getPileTitles(page: Page): Promise<string[]> {
  const titles = page.locator(ALBUM_SELECTORS.frontend.pileTitle);
  return await titles.allTextContents();
}

/**
 * Navigate to edit an existing album by ID
 */
export async function navigateToEditAlbum(page: Page, albumId: string): Promise<void> {
  await page.goto(`/wp-admin/post.php?post=${albumId}&action=edit`);
  await page.waitForLoadState('domcontentloaded');
}

/**
 * Navigate to albums admin list
 */
export async function navigateToAlbumsList(page: Page): Promise<void> {
  await page.goto('/wp-admin/edit.php?post_type=foogallery-album');
  await page.waitForLoadState('domcontentloaded');
}

/**
 * Deselect a gallery by index (toggle off)
 */
export async function deselectGallery(page: Page, index: number): Promise<void> {
  // Selected galleries have the 'selected' class
  const selectedGalleries = page.locator(`${ALBUM_SELECTORS.admin.galleryItem}.selected`);
  const item = selectedGalleries.nth(index);
  if (await item.count() > 0) {
    await item.click();
    await page.waitForTimeout(200);
  }
}

/**
 * Check if album exists in admin list
 */
export async function albumExistsInList(page: Page, albumName: string): Promise<boolean> {
  await navigateToAlbumsList(page);
  const rows = page.locator('table.wp-list-table tbody tr a.row-title');
  const titles = await rows.allTextContents();
  return titles.some(t => t.includes(albumName));
}
