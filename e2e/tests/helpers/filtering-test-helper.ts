// File: tests/helpers/filtering-test-helper.ts
// Helper for filtering pro feature tests

import { Page, expect, Locator } from '@playwright/test';

// Filtering selectors - updated based on Chrome Recorder and actual FooGallery DOM structure
export const FILTERING_SELECTORS = {
  // The filtering container - look for nav with filter links or filtering div
  container: '.foogallery nav, nav:has(a[href^="#tag-"]), [id*="_filtering-top"], [id*="_filtering-bottom"]',
  // Tag items - links with tag hrefs
  tagItem: 'a[href^="#tag-"]',
  // Tag links have href starting with #tag-
  tagLink: 'a[href^="#tag-"]',
  // Selected tag has fg-selected class or data attribute
  tagSelected: '.fg-selected, [data-selected="true"]',
  // All filter has href="#tag-" (empty tag)
  tagAll: 'a[href="#tag-"]',
  // Search input - from Chrome Recorder: #foogallery-gallery-{id}_filtering-top input
  searchInput: '[id*="_filtering-"] input, .fg-f-search input, input[type="search"]',
  // Search clear button - from Chrome Recorder: button.fg-search-clear > svg
  searchClear: 'button.fg-search-clear, .fg-search-clear',
  // Dropdown - from Chrome Recorder: just use select in gallery context
  dropdown: '.foogallery select, [id*="_filtering-"] select, select',
  galleryItem: 'figure:has(img)',
  hiddenItem: 'figure.fg-hidden',
  // Style classes
  styleButton: '.fg-style-button',
  stylePill: '.fg-style-pill',
  styleDropdown: '.fg-style-dropdown',
} as const;

export interface FilteringTestOptions {
  galleryName: string;
  templateSelector: string;
  screenshotPrefix: string;
  imageCount?: number;
  filteringType?: 'simple' | 'multi';
  filteringStyle?: '' | 'button' | 'button-block' | 'pill' | 'pill-block' | 'dropdown' | 'dropdown-block';
  filteringPosition?: 'top' | 'bottom' | 'both';
  filteringMode?: 'single' | 'union' | 'intersect';
  hideAll?: boolean;
  includeSearch?: boolean;
  searchPosition?: string;
}

// Map style names to numeric values used in FooGallery admin
// Based on Chrome Recorder showing #FooGallerySettings_default_filtering_style5 for dropdown
const STYLE_VALUE_MAP: Record<string, string> = {
  '': '',           // default/link style
  'button': '1',
  'button-block': '2',
  'pill': '3',
  'pill-block': '4',
  'dropdown': '5',
  'dropdown-block': '6',
};

// Map position names to numeric values
const POSITION_VALUE_MAP: Record<string, string> = {
  'top': '0',
  'bottom': '1',
  'both': '2',
};

/**
 * Navigate to gallery edit page and configure filtering settings
 */
export async function configureGalleryFiltering(page: Page, options: FilteringTestOptions): Promise<void> {
  const {
    templateSelector,
    filteringType = 'simple',
    filteringStyle = '',
    filteringPosition = 'top',
    filteringMode = 'single',
    hideAll = false,
    includeSearch = false,
    searchPosition = 'above-center'
  } = options;

  // Scroll down to Gallery Settings section
  const settingsSection = page.locator('#foogallery_settings');
  await settingsSection.scrollIntoViewIfNeeded();
  await page.waitForTimeout(500);

  // Click on Filtering section tab in Gallery Settings for the selected template
  // Based on Chrome Recorder: div.foogallery-show-child-menu > span.foogallery-tab-text
  const templateContainer = page.locator(`.foogallery-settings-container-${templateSelector}`);
  const filteringTab = templateContainer.locator('span.foogallery-tab-text').filter({ hasText: 'Filtering' });
  await filteringTab.click({ force: true });
  await page.waitForTimeout(500);

  // Enable filtering - select the type for the current template
  // Use template-specific selector to avoid strict mode violations
  const filteringTypeRadio = templateContainer.locator(`input[name*="${templateSelector}_filtering_type"][value="${filteringType}"]`);
  await filteringTypeRadio.click();
  await page.waitForTimeout(300);

  // Set position if not default (template-specific)
  // Position values: top=0, bottom=1, both=2
  // Use ID pattern from Chrome Recorder: #FooGallerySettings_{template}_filtering_position{value}
  if (filteringPosition !== 'top') {
    const positionValue = POSITION_VALUE_MAP[filteringPosition] || filteringPosition;
    const positionRadioId = `#FooGallerySettings_${templateSelector}_filtering_position${positionValue}`;
    await page.locator(positionRadioId).click();
    await page.waitForTimeout(300);
  }

  // Set style if specified (template-specific)
  // Style values: button=1, button-block=2, pill=3, pill-block=4, dropdown=5, dropdown-block=6
  if (filteringStyle) {
    const styleValue = STYLE_VALUE_MAP[filteringStyle] || filteringStyle;
    // Use the ID pattern from Chrome Recorder: #FooGallerySettings_{template}_filtering_style{value}
    const styleRadioId = `#FooGallerySettings_${templateSelector}_filtering_style${styleValue}`;
    await page.locator(styleRadioId).click();
    await page.waitForTimeout(300);
  }

  // Set mode if not single (template-specific)
  if (filteringMode !== 'single') {
    // Click Advanced subsection - it's a child menu under Filtering
    // Use the child navigation area which has class foogallery-tabs-child
    const childNav = templateContainer.locator('.foogallery-tabs-child, .foogallery-child-tabs');
    const advancedLink = childNav.getByText('Advanced', { exact: true });

    if (await advancedLink.count() > 0) {
      await advancedLink.click({ force: true });
    } else {
      // Fallback: click any visible "Advanced" text in the template container
      // that's not the main Advanced tab (which is at a different level)
      const allAdvanced = templateContainer.locator('text=Advanced');
      const count = await allAdvanced.count();
      if (count > 1) {
        // Click the first one which should be the submenu item
        await allAdvanced.first().click({ force: true });
      }
    }
    await page.waitForTimeout(300);

    const modeRadio = templateContainer.locator(`input[name*="${templateSelector}_filtering_mode"][value="${filteringMode}"]`);
    await modeRadio.click();
  }

  // Hide "All" option if specified (template-specific)
  if (hideAll) {
    const hideAllRadio = templateContainer.locator(`input[name*="${templateSelector}_filtering_hideall"][value="hide"]`);
    await hideAllRadio.click();
  }

  // Enable search if specified (template-specific)
  if (includeSearch) {
    // Click Search subsection - it's a child menu under Filtering
    const childNav = templateContainer.locator('.foogallery-tabs-child, .foogallery-child-tabs');
    const searchLink = childNav.getByText('Search', { exact: true });

    if (await searchLink.count() > 0) {
      await searchLink.click({ force: true });
    } else {
      // Fallback: click the Search text in the template container
      const allSearch = templateContainer.locator('text=Search');
      if (await allSearch.count() > 0) {
        await allSearch.first().click({ force: true });
      }
    }
    await page.waitForTimeout(300);

    // Enable search - value is "1" based on Chrome Recorder: #FooGallerySettings_simple_portfolio_filtering_search1
    const searchRadioId = `#FooGallerySettings_${templateSelector}_filtering_search1`;
    await page.locator(searchRadioId).click();
    await page.waitForTimeout(300);

    // Set search position if not default
    // Position values are numeric: 0=above-center, 1=above-right, 2=below-center, etc.
    if (searchPosition !== 'above-center') {
      const searchPositionMap: Record<string, string> = {
        'above-center': '0',
        'above-right': '1',
        'below-center': '2',
        'before-merged': '3',
        'after-merged': '4',
      };
      const searchPosValue = searchPositionMap[searchPosition] || searchPosition;
      const searchPosRadioId = `#FooGallerySettings_${templateSelector}_filtering_search_position${searchPosValue}`;
      await page.locator(searchPosRadioId).click();
      await page.waitForTimeout(300);
    }
  }
}

/**
 * Create a gallery with filtering enabled and navigate to view it
 */
export async function createGalleryWithFiltering(page: Page, options: FilteringTestOptions): Promise<string> {
  const { galleryName, templateSelector, screenshotPrefix, imageCount = 5 } = options;

  await page.setViewportSize({ width: 1932, height: 1271 });

  // Navigate to Add New Gallery
  await page.goto('/wp-admin/post-new.php?post_type=foogallery');
  await page.waitForLoadState('domcontentloaded');

  // Enter gallery title
  await page.locator('#title').fill(galleryName);

  // Select template
  const templateCard = page.locator(`[data-template="${templateSelector}"]`);
  await templateCard.waitFor({ state: 'visible', timeout: 10000 });
  await templateCard.click();
  await expect(templateCard).toHaveClass(/selected/);

  // Add images from media library
  await page.locator('text=Add From Media Library').click();
  await page.waitForLoadState('networkidle');

  const modal = page.locator('.media-modal:visible');
  await modal.waitFor({ state: 'visible', timeout: 10000 });

  const mediaLibraryTab = modal.locator('.media-menu-item').filter({ hasText: 'Media Library' });
  await mediaLibraryTab.click();

  const attachments = modal.locator('.attachment');
  await attachments.first().waitFor({ state: 'visible', timeout: 10000 });

  // Get actual count of available images
  const availableCount = await attachments.count();
  const imagesToSelect = Math.min(imageCount, availableCount);

  for (let i = 0; i < imagesToSelect; i++) {
    await attachments.nth(i).click();
  }

  const addButton = modal.locator('button.media-button-select, button:has-text("Add to Gallery")').first();
  await addButton.click();
  await page.waitForLoadState('networkidle');

  // Note: Media tags are pre-assigned during Docker setup via WP-CLI
  // See docker/scripts/setup-wordpress.sh for tag assignment

  // Configure filtering
  await configureGalleryFiltering(page, options);

  // Screenshot after configuration
  await page.screenshot({ path: `test-results/${screenshotPrefix}-configured.png` });

  // Publish gallery
  await page.locator('#publish').click();
  await page.waitForLoadState('networkidle');
  await expect(page).toHaveURL(/post\.php\?post=\d+&action=edit/);

  // Extract gallery ID
  const url = page.url();
  const postIdMatch = url.match(/post=(\d+)/);
  const galleryId = postIdMatch ? postIdMatch[1] : '';

  // Create gallery page and navigate to it
  await page.locator('#foogallery_create_page').click();
  await page.waitForLoadState('networkidle');

  const viewLink = page.getByRole('link', { name: 'View', exact: true }).first();
  await viewLink.waitFor({ state: 'visible', timeout: 30000 });
  const viewUrl = await viewLink.getAttribute('href');

  if (viewUrl) {
    await page.goto(viewUrl);
    await page.waitForLoadState('networkidle');
  }

  await page.screenshot({ path: `test-results/${screenshotPrefix}-frontend.png` });

  return galleryId;
}

/**
 * Get visible gallery items count
 */
export async function getVisibleItemCount(page: Page): Promise<number> {
  const items = page.locator(`${FILTERING_SELECTORS.galleryItem}:not(.fg-hidden)`);
  return await items.count();
}

/**
 * Get hidden gallery items count
 */
export async function getHiddenItemCount(page: Page): Promise<number> {
  const items = page.locator(FILTERING_SELECTORS.hiddenItem);
  return await items.count();
}

/**
 * Click a filter tag by its text
 */
export async function clickFilterTag(page: Page, tagText: string): Promise<void> {
  const tag = page.locator(FILTERING_SELECTORS.tagItem).filter({ hasText: tagText });
  await tag.click();
  await page.waitForTimeout(500); // Wait for filter animation
}

/**
 * Click the "All" filter tag
 */
export async function clickAllFilter(page: Page): Promise<void> {
  const allTag = page.locator(FILTERING_SELECTORS.tagAll);
  await allTag.click();
  await page.waitForTimeout(500);
}

/**
 * Check if a filter tag is selected
 */
export async function isFilterSelected(page: Page, tagText: string): Promise<boolean> {
  const tag = page.locator(FILTERING_SELECTORS.tagItem).filter({ hasText: tagText });
  // Check various possible indicators of selected state
  return await tag.evaluate(el => {
    // Check element itself and parent li/wrapper for selected classes
    const hasSelectedClass = el.classList.contains('fg-selected') ||
                             el.classList.contains('fg-active') ||
                             el.classList.contains('active') ||
                             el.classList.contains('selected');
    const parentHasSelected = el.parentElement?.classList.contains('fg-selected') ||
                              el.parentElement?.classList.contains('fg-active') ||
                              el.parentElement?.classList.contains('active');
    // Also check aria-selected attribute
    const ariaSelected = el.getAttribute('aria-selected') === 'true' ||
                         el.getAttribute('aria-current') === 'true';
    return hasSelectedClass || parentHasSelected || ariaSelected;
  });
}

/**
 * Get all filter tag names
 */
export async function getFilterTags(page: Page): Promise<string[]> {
  // Get all filter tag links directly
  const tags = page.locator(FILTERING_SELECTORS.tagLink);
  return await tags.allTextContents();
}

/**
 * Select a filter from dropdown (for dropdown style)
 * Based on Chrome Recorder: await page.locator("select").type("tag 1");
 */
export async function selectDropdownFilter(page: Page, value: string): Promise<void> {
  const dropdown = page.locator(FILTERING_SELECTORS.dropdown).first();
  await dropdown.click();
  await page.waitForTimeout(200);

  // Try selectOption first, then fallback to type
  try {
    await dropdown.selectOption({ label: value });
  } catch {
    // Fallback: use type like in Chrome Recorder
    await dropdown.type(value);
  }
  await page.waitForTimeout(500);
}

/**
 * Enter search text
 * Based on Chrome Recorder: #foogallery-gallery-{id}_filtering-top input
 */
export async function enterSearchText(page: Page, text: string): Promise<void> {
  const searchInput = page.locator(FILTERING_SELECTORS.searchInput).first();
  await searchInput.click();
  await searchInput.fill(text);
  // Press Enter to trigger search (from Chrome Recorder: page.keyboard.down("{Enter}"))
  await page.keyboard.press('Enter');
  await page.waitForTimeout(500); // Wait for search to apply
}

/**
 * Clear search input
 * Based on Chrome Recorder: button.fg-search-clear > svg
 */
export async function clearSearch(page: Page): Promise<void> {
  // Try the clear button first (from Chrome Recorder: button.fg-search-clear > svg)
  const clearButton = page.locator('button.fg-search-clear, button.fg-search-clear > svg');
  if (await clearButton.first().isVisible()) {
    await clearButton.first().click();
  } else {
    // Fallback: clear the input directly
    const searchInput = page.locator(FILTERING_SELECTORS.searchInput).first();
    await searchInput.clear();
  }
  await page.waitForTimeout(500);
}

/**
 * Verify filtering container exists at position
 * Based on Chrome Recorder: #foogallery-gallery-{id}_filtering-top and #foogallery-gallery-{id}_filtering-bottom
 */
export async function verifyFilteringPosition(page: Page, position: 'top' | 'bottom' | 'both'): Promise<void> {
  if (position === 'top' || position === 'both') {
    const topContainer = page.locator('[id*="_filtering-top"]');
    await expect(topContainer).toBeVisible();
  }

  if (position === 'bottom' || position === 'both') {
    const bottomContainer = page.locator('[id*="_filtering-bottom"]');
    await expect(bottomContainer).toBeVisible();
  }
}

/**
 * Wait for filtering to be ready (filters rendered)
 * Handles both tag-based filtering and dropdown-style filtering
 */
export async function waitForFiltering(page: Page): Promise<void> {
  // Wait for the gallery to load
  await page.waitForSelector('.foogallery', { state: 'visible', timeout: 15000 });

  // Try to find either tag links or dropdown (for dropdown style)
  try {
    // First try tag items (for button/pill styles)
    await page.waitForSelector(FILTERING_SELECTORS.tagItem, { state: 'visible', timeout: 5000 });
  } catch {
    // Fallback: check for dropdown (for dropdown style)
    try {
      await page.waitForSelector(FILTERING_SELECTORS.dropdown, { state: 'visible', timeout: 5000 });
    } catch {
      // Last resort: just wait for filtering container
      await page.waitForSelector('[id*="_filtering-"]', { state: 'visible', timeout: 5000 });
    }
  }

  // Small delay for animations
  await page.waitForTimeout(500);
}
