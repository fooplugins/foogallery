// File: tests/helpers/appearance-test-helper.ts
// Helper for Appearance (Instagram Filters) and Hover Effects pro feature tests

import { Page, expect } from '@playwright/test';

// ============================================================================
// CONSTANTS - Instagram Filters
// ============================================================================

export const INSTAGRAM_FILTERS = {
  none: { index: 0, class: '' },
  '1977': { index: 1, class: 'fg-filter-1977' },
  amaro: { index: 2, class: 'fg-filter-amaro' },
  brannan: { index: 3, class: 'fg-filter-brannan' },
  clarendon: { index: 4, class: 'fg-filter-clarendon' },
  earlybird: { index: 5, class: 'fg-filter-earlybird' },
  lofi: { index: 6, class: 'fg-filter-lofi' },
  poprocket: { index: 7, class: 'fg-filter-poprocket' },
  reyes: { index: 8, class: 'fg-filter-reyes' },
  toaster: { index: 9, class: 'fg-filter-toaster' },
  walden: { index: 10, class: 'fg-filter-walden' },
  xpro2: { index: 11, class: 'fg-filter-xpro2' },
  xtreme: { index: 12, class: 'fg-filter-xtreme' },
} as const;

export type InstagramFilterName = keyof typeof INSTAGRAM_FILTERS;

// ============================================================================
// CONSTANTS - Loaded Effects
// ============================================================================

export const LOADED_EFFECTS = {
  none: { index: 0, class: '' },
  fadeIn: { index: 1, class: 'fg-loaded-fade-in' },
  slideUp: { index: 2, class: 'fg-loaded-slide-up' },
  slideDown: { index: 3, class: 'fg-loaded-slide-down' },
  slideLeft: { index: 4, class: 'fg-loaded-slide-left' },
  slideRight: { index: 5, class: 'fg-loaded-slide-right' },
  scaleUp: { index: 6, class: 'fg-loaded-scale-up' },
  swingDown: { index: 7, class: 'fg-loaded-swing-down' },
  drop: { index: 8, class: 'fg-loaded-drop' },
  fly: { index: 9, class: 'fg-loaded-fly' },
  flip: { index: 10, class: 'fg-loaded-flip' },
} as const;

export type LoadedEffectName = keyof typeof LOADED_EFFECTS;

// ============================================================================
// CONSTANTS - Border Size
// ============================================================================

export const BORDER_SIZES = {
  none: { index: 0, class: '' },
  thin: { index: 1, class: 'fg-border-thin' },
  medium: { index: 2, class: 'fg-border-medium' },
  thick: { index: 3, class: 'fg-border-thick' },
} as const;

export type BorderSizeName = keyof typeof BORDER_SIZES;

// ============================================================================
// CONSTANTS - Rounded Corners
// ============================================================================

export const ROUNDED_CORNERS = {
  none: { index: 0, class: '' },
  small: { index: 1, class: 'fg-round-small' },
  medium: { index: 2, class: 'fg-round-medium' },
  large: { index: 3, class: 'fg-round-large' },
  full: { index: 4, class: 'fg-round-full' },
} as const;

export type RoundedCornersName = keyof typeof ROUNDED_CORNERS;

// ============================================================================
// CONSTANTS - Drop Shadow
// ============================================================================

export const DROP_SHADOWS = {
  none: { index: 0, class: '' },
  outline: { index: 1, class: 'fg-shadow-outline' },
  small: { index: 2, class: 'fg-shadow-small' },
  medium: { index: 3, class: 'fg-shadow-medium' },
  large: { index: 4, class: 'fg-shadow-large' },
} as const;

export type DropShadowName = keyof typeof DROP_SHADOWS;

// ============================================================================
// CONSTANTS - Inner Shadow
// ============================================================================

export const INNER_SHADOWS = {
  none: { index: 0, class: '' },
  outline: { index: 1, class: 'fg-shadow-inset-outline' },
  small: { index: 2, class: 'fg-shadow-inset-small' },
  medium: { index: 3, class: 'fg-shadow-inset-medium' },
  large: { index: 4, class: 'fg-shadow-inset-large' },
} as const;

export type InnerShadowName = keyof typeof INNER_SHADOWS;

// ============================================================================
// CONSTANTS - Theme
// ============================================================================

export const THEMES = {
  light: { index: 0, class: 'fg-light' },
  dark: { index: 1, class: 'fg-dark' },
  custom: { index: 2, class: 'fg-custom' },
} as const;

export type ThemeName = keyof typeof THEMES;

// ============================================================================
// CONSTANTS - Hover Effect Types
// ============================================================================

export const HOVER_EFFECT_TYPES = {
  none: { index: 0, class: '' },
  normal: { index: 1, class: 'fg-hover-normal' },
  preset: { index: 2, class: 'fg-preset' },
} as const;

export type HoverEffectTypeName = keyof typeof HOVER_EFFECT_TYPES;

// ============================================================================
// CONSTANTS - Hover Effect Presets
// ============================================================================

export const HOVER_PRESETS = {
  brad: { index: 0, class: 'fg-brad' },
  sadie: { index: 1, class: 'fg-sadie' },
  layla: { index: 2, class: 'fg-layla' },
  oscar: { index: 3, class: 'fg-oscar' },
  sarah: { index: 4, class: 'fg-sarah' },
  goliath: { index: 5, class: 'fg-goliath' },
  jazz: { index: 6, class: 'fg-jazz' },
  lily: { index: 7, class: 'fg-lily' },
  ming: { index: 8, class: 'fg-ming' },
  selena: { index: 9, class: 'fg-selena' },
  steve: { index: 10, class: 'fg-steve' },
  zoe: { index: 11, class: 'fg-zoe' },
} as const;

export type HoverPresetName = keyof typeof HOVER_PRESETS;

// ============================================================================
// CONSTANTS - Hover Preset Sizes
// ============================================================================

export const HOVER_PRESET_SIZES = {
  small: { index: 0, class: 'fg-preset-small' },
  medium: { index: 1, class: 'fg-preset-medium' },
  large: { index: 2, class: 'fg-preset-large' },
} as const;

export type HoverPresetSizeName = keyof typeof HOVER_PRESET_SIZES;

// ============================================================================
// CONSTANTS - Hover Effect Color
// ============================================================================

export const HOVER_EFFECT_COLORS = {
  none: { index: 0, class: '' },
  colorize: { index: 1, class: 'fg-hover-colorize' },
  grayscale: { index: 2, class: 'fg-hover-grayscale' },
} as const;

export type HoverEffectColorName = keyof typeof HOVER_EFFECT_COLORS;

// ============================================================================
// CONSTANTS - Hover Effect Scale
// ============================================================================

export const HOVER_EFFECT_SCALES = {
  none: { index: 0, class: '' },
  scale: { index: 1, class: 'fg-hover-scale' },
  zoom: { index: 2, class: 'fg-hover-zoomed' },
  semiZoom: { index: 3, class: 'fg-hover-semi-zoomed' },
} as const;

export type HoverEffectScaleName = keyof typeof HOVER_EFFECT_SCALES;

// ============================================================================
// CONSTANTS - Hover Effect Transition
// ============================================================================

export const HOVER_EFFECT_TRANSITIONS = {
  instant: { index: 0, class: 'fg-hover-instant' },
  fade: { index: 1, class: 'fg-hover-fade' },
  slideUp: { index: 2, class: 'fg-hover-slide-up' },
  slideDown: { index: 3, class: 'fg-hover-slide-down' },
  slideLeft: { index: 4, class: 'fg-hover-slide-left' },
  slideRight: { index: 5, class: 'fg-hover-slide-right' },
  push: { index: 6, class: 'fg-hover-push' },
} as const;

export type HoverEffectTransitionName = keyof typeof HOVER_EFFECT_TRANSITIONS;

// ============================================================================
// CONSTANTS - Hover Effect Icons
// ============================================================================

export const HOVER_EFFECT_ICONS = {
  none: { index: 0, class: '' },
  zoom: { index: 1, class: 'fg-hover-zoom' },
  zoomPlus: { index: 2, class: 'fg-hover-zoom2' },
  zoomCircle: { index: 3, class: 'fg-hover-zoom3' },
  plus: { index: 4, class: 'fg-hover-plus' },
  circleUp: { index: 5, class: 'fg-hover-circle-plus' },
  eye: { index: 6, class: 'fg-hover-eye' },
  external: { index: 7, class: 'fg-hover-external' },
  search: { index: 8, class: 'fg-hover-search' },
  info: { index: 9, class: 'fg-hover-info' },
  cart: { index: 10, class: 'fg-hover-cart' },
  photo: { index: 11, class: 'fg-hover-photo' },
  camera: { index: 12, class: 'fg-hover-camera' },
  arrowDown: { index: 13, class: 'fg-hover-arrow-down' },
} as const;

export type HoverEffectIconName = keyof typeof HOVER_EFFECT_ICONS;

// ============================================================================
// CONSTANTS - Hover Effect Icon Sizes
// ============================================================================

export const HOVER_EFFECT_ICON_SIZES = {
  default: { index: 0, class: '' },
  '1.5x': { index: 1, class: 'fg-hover-icon-1-5' },
  '2x': { index: 2, class: 'fg-hover-icon-2' },
  '2.5x': { index: 3, class: 'fg-hover-icon-2-5' },
  '3x': { index: 4, class: 'fg-hover-icon-3' },
} as const;

export type HoverEffectIconSizeName = keyof typeof HOVER_EFFECT_ICON_SIZES;

// ============================================================================
// CONSTANTS - Caption Visibility
// ============================================================================

export const CAPTION_VISIBILITIES = {
  none: { index: 0, class: '' },
  hover: { index: 1, class: 'fg-caption-hover' },
  always: { index: 2, class: 'fg-caption-always' },
} as const;

export type CaptionVisibilityName = keyof typeof CAPTION_VISIBILITIES;

// ============================================================================
// CONSTANTS - Caption Invert Color (Overlay Theme)
// ============================================================================

export const CAPTION_INVERT_COLORS = {
  dark: { index: 0, class: '' },
  light: { index: 1, class: 'fg-light-overlays' },
  transparent: { index: 2, class: 'fg-transparent-overlays' },
} as const;

export type CaptionInvertColorName = keyof typeof CAPTION_INVERT_COLORS;

// ============================================================================
// SELECTORS
// ============================================================================

export const APPEARANCE_SELECTORS = {
  // Gallery container
  galleryContainer: '.foogallery',

  // Admin - Tab Navigation (for default template)
  appearanceTab: (template: string) =>
    `div.foogallery-settings-container-${template} div.foogallery-vertical-tabs > div:nth-of-type(3)`,
  hoverEffectsTab: (template: string) =>
    `div.foogallery-settings-container-${template} div.foogallery-vertical-tabs > div:nth-of-type(4)`,

  // Admin - Instagram Filter
  instagramFilter: (template: string, index: number) =>
    `#FooGallerySettings_${template}_instagram${index}`,

  // Admin - Loaded Effect
  loadedEffect: (template: string, index: number) =>
    `#FooGallerySettings_${template}_loaded_effect${index}`,

  // Admin - Border Size
  borderSize: (template: string, index: number) =>
    `#FooGallerySettings_${template}_border_size${index}`,

  // Admin - Rounded Corners
  roundedCorners: (template: string, index: number) =>
    `#FooGallerySettings_${template}_rounded_corners${index}`,

  // Admin - Drop Shadow
  dropShadow: (template: string, index: number) =>
    `#FooGallerySettings_${template}_drop_shadow${index}`,

  // Admin - Inner Shadow
  innerShadow: (template: string, index: number) =>
    `#FooGallerySettings_${template}_inner_shadow${index}`,

  // Admin - Theme
  theme: (template: string, index: number) =>
    `#FooGallerySettings_${template}_theme${index}`,

  // Admin - Hover Effect Type
  hoverEffectType: (template: string, index: number) =>
    `#FooGallerySettings_${template}_hover_effect_type${index}`,

  // Admin - Hover Effect Preset
  hoverEffectPreset: (template: string, index: number) =>
    `#FooGallerySettings_${template}_hover_effect_preset${index}`,

  // Admin - Hover Preset Size
  hoverPresetSize: (template: string, index: number) =>
    `#FooGallerySettings_${template}_hover_effect_preset_size${index}`,

  // Admin - Hover Effect Color
  hoverEffectColor: (template: string, index: number) =>
    `#FooGallerySettings_${template}_hover_effect_color${index}`,

  // Admin - Hover Effect Scale
  hoverEffectScale: (template: string, index: number) =>
    `#FooGallerySettings_${template}_hover_effect_scale${index}`,

  // Admin - Hover Effect Transition
  hoverEffectTransition: (template: string, index: number) =>
    `#FooGallerySettings_${template}_hover_effect_transition${index}`,

  // Admin - Hover Effect Icon
  hoverEffectIcon: (template: string, index: number) =>
    `#FooGallerySettings_${template}_hover_effect_icon${index}`,

  // Admin - Hover Effect Icon Size
  hoverEffectIconSize: (template: string, index: number) =>
    `#FooGallerySettings_${template}_hover_effect_icon_size${index}`,

  // Admin - Caption Visibility
  captionVisibility: (template: string, index: number) =>
    `#FooGallerySettings_${template}_hover_effect_caption_visibility${index}`,

  // Admin - Caption Invert Color
  captionInvertColor: (template: string, index: number) =>
    `#FooGallerySettings_${template}_caption_invert_color${index}`,

  // Frontend
  galleryItem: '.fg-item',
  itemAnchor: '.fg-item a.fg-thumb',
} as const;

// ============================================================================
// INTERFACES
// ============================================================================

export interface AppearanceTestOptions {
  galleryName: string;
  templateSelector: string;
  screenshotPrefix: string;
  imageCount?: number;
}

export interface AppearanceSettingsOptions {
  instagramFilter?: InstagramFilterName;
  loadedEffect?: LoadedEffectName;
  borderSize?: BorderSizeName;
  roundedCorners?: RoundedCornersName;
  dropShadow?: DropShadowName;
  innerShadow?: InnerShadowName;
  theme?: ThemeName;
}

export interface HoverEffectSettingsOptions {
  type?: HoverEffectTypeName;
  preset?: HoverPresetName;
  presetSize?: HoverPresetSizeName;
  color?: HoverEffectColorName;
  scale?: HoverEffectScaleName;
  transition?: HoverEffectTransitionName;
  icon?: HoverEffectIconName;
  iconSize?: HoverEffectIconSizeName;
  captionVisibility?: CaptionVisibilityName;
  captionInvertColor?: CaptionInvertColorName;
}

// ============================================================================
// TAB NAVIGATION FUNCTIONS
// ============================================================================

/**
 * Navigate to the Appearance tab in gallery settings (Tab 3)
 */
export async function navigateToAppearanceTab(
  page: Page,
  templateSelector: string = 'default'
): Promise<void> {
  const settingsSection = page.locator('#foogallery_settings');
  await settingsSection.scrollIntoViewIfNeeded();
  await page.waitForTimeout(500);

  const appearanceTab = page.locator(APPEARANCE_SELECTORS.appearanceTab(templateSelector));
  await appearanceTab.scrollIntoViewIfNeeded();
  await appearanceTab.click();
  await page.waitForTimeout(300);
}

/**
 * Navigate to the Hover Effects tab in gallery settings (Tab 4)
 */
export async function navigateToHoverEffectsTab(
  page: Page,
  templateSelector: string = 'default'
): Promise<void> {
  const settingsSection = page.locator('#foogallery_settings');
  await settingsSection.scrollIntoViewIfNeeded();
  await page.waitForTimeout(500);

  const hoverTab = page.locator(APPEARANCE_SELECTORS.hoverEffectsTab(templateSelector));
  await hoverTab.scrollIntoViewIfNeeded();
  await hoverTab.click();
  await page.waitForTimeout(300);
}

// ============================================================================
// APPEARANCE SETTING FUNCTIONS
// ============================================================================

/**
 * Set Instagram filter
 */
export async function setInstagramFilter(
  page: Page,
  filterName: InstagramFilterName,
  templateSelector: string = 'default'
): Promise<void> {
  const filter = INSTAGRAM_FILTERS[filterName];
  const selector = APPEARANCE_SELECTORS.instagramFilter(templateSelector, filter.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set loaded effect
 */
export async function setLoadedEffect(
  page: Page,
  effectName: LoadedEffectName,
  templateSelector: string = 'default'
): Promise<void> {
  const effect = LOADED_EFFECTS[effectName];
  const selector = APPEARANCE_SELECTORS.loadedEffect(templateSelector, effect.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set border size
 */
export async function setBorderSize(
  page: Page,
  sizeName: BorderSizeName,
  templateSelector: string = 'default'
): Promise<void> {
  const size = BORDER_SIZES[sizeName];
  const selector = APPEARANCE_SELECTORS.borderSize(templateSelector, size.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set rounded corners
 */
export async function setRoundedCorners(
  page: Page,
  cornersName: RoundedCornersName,
  templateSelector: string = 'default'
): Promise<void> {
  const corners = ROUNDED_CORNERS[cornersName];
  const selector = APPEARANCE_SELECTORS.roundedCorners(templateSelector, corners.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set drop shadow
 */
export async function setDropShadow(
  page: Page,
  shadowName: DropShadowName,
  templateSelector: string = 'default'
): Promise<void> {
  const shadow = DROP_SHADOWS[shadowName];
  const selector = APPEARANCE_SELECTORS.dropShadow(templateSelector, shadow.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set inner shadow
 */
export async function setInnerShadow(
  page: Page,
  shadowName: InnerShadowName,
  templateSelector: string = 'default'
): Promise<void> {
  const shadow = INNER_SHADOWS[shadowName];
  const selector = APPEARANCE_SELECTORS.innerShadow(templateSelector, shadow.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set theme
 */
export async function setTheme(
  page: Page,
  themeName: ThemeName,
  templateSelector: string = 'default'
): Promise<void> {
  const theme = THEMES[themeName];
  const selector = APPEARANCE_SELECTORS.theme(templateSelector, theme.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

// ============================================================================
// HOVER EFFECT SETTING FUNCTIONS
// ============================================================================

/**
 * Set hover effect type
 */
export async function setHoverEffectType(
  page: Page,
  typeName: HoverEffectTypeName,
  templateSelector: string = 'default'
): Promise<void> {
  const type = HOVER_EFFECT_TYPES[typeName];
  const selector = APPEARANCE_SELECTORS.hoverEffectType(templateSelector, type.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(300);
}

/**
 * Set hover effect preset
 */
export async function setHoverEffectPreset(
  page: Page,
  presetName: HoverPresetName,
  templateSelector: string = 'default'
): Promise<void> {
  const preset = HOVER_PRESETS[presetName];
  const selector = APPEARANCE_SELECTORS.hoverEffectPreset(templateSelector, preset.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set hover preset size
 */
export async function setHoverPresetSize(
  page: Page,
  sizeName: HoverPresetSizeName,
  templateSelector: string = 'default'
): Promise<void> {
  const size = HOVER_PRESET_SIZES[sizeName];
  const selector = APPEARANCE_SELECTORS.hoverPresetSize(templateSelector, size.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set hover effect color
 */
export async function setHoverEffectColor(
  page: Page,
  colorName: HoverEffectColorName,
  templateSelector: string = 'default'
): Promise<void> {
  const color = HOVER_EFFECT_COLORS[colorName];
  const selector = APPEARANCE_SELECTORS.hoverEffectColor(templateSelector, color.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set hover effect scale
 */
export async function setHoverEffectScale(
  page: Page,
  scaleName: HoverEffectScaleName,
  templateSelector: string = 'default'
): Promise<void> {
  const scale = HOVER_EFFECT_SCALES[scaleName];
  const selector = APPEARANCE_SELECTORS.hoverEffectScale(templateSelector, scale.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set hover effect transition
 */
export async function setHoverEffectTransition(
  page: Page,
  transitionName: HoverEffectTransitionName,
  templateSelector: string = 'default'
): Promise<void> {
  const transition = HOVER_EFFECT_TRANSITIONS[transitionName];
  const selector = APPEARANCE_SELECTORS.hoverEffectTransition(templateSelector, transition.index);
  await page.click(selector, { force: true });
  await page.waitForTimeout(200);
}

/**
 * Set hover effect icon
 */
export async function setHoverEffectIcon(
  page: Page,
  iconName: HoverEffectIconName,
  templateSelector: string = 'default'
): Promise<void> {
  const icon = HOVER_EFFECT_ICONS[iconName];
  const selector = APPEARANCE_SELECTORS.hoverEffectIcon(templateSelector, icon.index);
  const element = page.locator(selector);
  // Use JavaScript click as these elements may be visually hidden radio buttons
  await element.evaluate((el: HTMLElement) => el.click());
  await page.waitForTimeout(200);
}

/**
 * Set hover effect icon size
 */
export async function setHoverEffectIconSize(
  page: Page,
  sizeName: HoverEffectIconSizeName,
  templateSelector: string = 'default'
): Promise<void> {
  const size = HOVER_EFFECT_ICON_SIZES[sizeName];
  const selector = APPEARANCE_SELECTORS.hoverEffectIconSize(templateSelector, size.index);
  const element = page.locator(selector);
  // Use JavaScript click as these elements may be visually hidden radio buttons
  await element.evaluate((el: HTMLElement) => el.click());
  await page.waitForTimeout(200);
}

/**
 * Set caption visibility
 */
export async function setCaptionVisibility(
  page: Page,
  visibilityName: CaptionVisibilityName,
  templateSelector: string = 'default'
): Promise<void> {
  const visibility = CAPTION_VISIBILITIES[visibilityName];
  const selector = APPEARANCE_SELECTORS.captionVisibility(templateSelector, visibility.index);
  const element = page.locator(selector);
  await element.scrollIntoViewIfNeeded();
  await page.waitForTimeout(300);
  await element.click({ force: true });
  await page.waitForTimeout(200);
}

/**
 * Set caption invert color (overlay theme)
 */
export async function setCaptionInvertColor(
  page: Page,
  colorName: CaptionInvertColorName,
  templateSelector: string = 'default'
): Promise<void> {
  const color = CAPTION_INVERT_COLORS[colorName];
  const selector = APPEARANCE_SELECTORS.captionInvertColor(templateSelector, color.index);
  const element = page.locator(selector);
  await element.scrollIntoViewIfNeeded();
  await page.waitForTimeout(300);
  await element.click({ force: true });
  await page.waitForTimeout(200);
}

// ============================================================================
// CONFIGURATION FUNCTIONS
// ============================================================================

/**
 * Configure all appearance settings
 */
export async function configureAppearanceSettings(
  page: Page,
  templateSelector: string,
  options: AppearanceSettingsOptions
): Promise<void> {
  await navigateToAppearanceTab(page, templateSelector);

  if (options.instagramFilter !== undefined) {
    await setInstagramFilter(page, options.instagramFilter, templateSelector);
  }

  if (options.loadedEffect !== undefined) {
    await setLoadedEffect(page, options.loadedEffect, templateSelector);
  }

  if (options.borderSize !== undefined) {
    await setBorderSize(page, options.borderSize, templateSelector);
  }

  if (options.roundedCorners !== undefined) {
    await setRoundedCorners(page, options.roundedCorners, templateSelector);
  }

  if (options.dropShadow !== undefined) {
    await setDropShadow(page, options.dropShadow, templateSelector);
  }

  if (options.innerShadow !== undefined) {
    await setInnerShadow(page, options.innerShadow, templateSelector);
  }

  if (options.theme !== undefined) {
    await setTheme(page, options.theme, templateSelector);
  }
}

/**
 * Configure all hover effect settings
 */
export async function configureHoverEffectSettings(
  page: Page,
  templateSelector: string,
  options: HoverEffectSettingsOptions
): Promise<void> {
  await navigateToHoverEffectsTab(page, templateSelector);

  if (options.type !== undefined) {
    await setHoverEffectType(page, options.type, templateSelector);
  }

  // Preset mode settings
  if (options.preset !== undefined) {
    await setHoverEffectPreset(page, options.preset, templateSelector);
  }

  if (options.presetSize !== undefined) {
    await setHoverPresetSize(page, options.presetSize, templateSelector);
  }

  // Normal mode settings
  if (options.color !== undefined) {
    await setHoverEffectColor(page, options.color, templateSelector);
  }

  if (options.scale !== undefined) {
    await setHoverEffectScale(page, options.scale, templateSelector);
  }

  if (options.transition !== undefined) {
    await setHoverEffectTransition(page, options.transition, templateSelector);
  }

  if (options.icon !== undefined) {
    await setHoverEffectIcon(page, options.icon, templateSelector);
  }

  if (options.iconSize !== undefined) {
    await setHoverEffectIconSize(page, options.iconSize, templateSelector);
  }

  if (options.captionVisibility !== undefined) {
    await setCaptionVisibility(page, options.captionVisibility, templateSelector);
  }

  if (options.captionInvertColor !== undefined) {
    await setCaptionInvertColor(page, options.captionInvertColor, templateSelector);
  }
}

// ============================================================================
// FRONTEND VERIFICATION FUNCTIONS
// ============================================================================

/**
 * Get all classes from the gallery container
 */
export async function getGalleryClasses(page: Page): Promise<string[]> {
  const gallery = page.locator(APPEARANCE_SELECTORS.galleryContainer).first();
  await gallery.waitFor({ state: 'visible', timeout: 15000 });
  const classAttr = await gallery.getAttribute('class');
  return classAttr ? classAttr.split(' ').filter(c => c.trim() !== '') : [];
}

/**
 * Verify gallery has a specific class
 */
export async function verifyGalleryHasClass(page: Page, className: string): Promise<void> {
  const gallery = page.locator(APPEARANCE_SELECTORS.galleryContainer).first();
  await gallery.waitFor({ state: 'visible', timeout: 15000 });
  await expect(gallery).toHaveClass(new RegExp(className));
}

/**
 * Verify gallery does NOT have a specific class
 */
export async function verifyGalleryDoesNotHaveClass(page: Page, className: string): Promise<void> {
  const gallery = page.locator(APPEARANCE_SELECTORS.galleryContainer).first();
  await gallery.waitFor({ state: 'visible', timeout: 15000 });
  await expect(gallery).not.toHaveClass(new RegExp(className));
}

/**
 * Verify multiple expected classes on the gallery
 */
export async function verifyGalleryHasClasses(page: Page, expectedClasses: string[]): Promise<void> {
  for (const className of expectedClasses) {
    if (className) {
      await verifyGalleryHasClass(page, className);
    }
  }
}

/**
 * Verify Instagram filter on frontend
 */
export async function verifyInstagramFilter(
  page: Page,
  filterName: InstagramFilterName
): Promise<void> {
  const filter = INSTAGRAM_FILTERS[filterName];
  if (filter.class) {
    await verifyGalleryHasClass(page, filter.class);
  }
}

/**
 * Verify loaded effect on frontend
 */
export async function verifyLoadedEffect(
  page: Page,
  effectName: LoadedEffectName
): Promise<void> {
  const effect = LOADED_EFFECTS[effectName];
  if (effect.class) {
    await verifyGalleryHasClass(page, effect.class);
  }
}

/**
 * Verify hover preset on frontend
 */
export async function verifyHoverPreset(
  page: Page,
  presetName: HoverPresetName
): Promise<void> {
  const preset = HOVER_PRESETS[presetName];
  await verifyGalleryHasClass(page, 'fg-preset');
  await verifyGalleryHasClass(page, preset.class);
}

// ============================================================================
// COMPLETE WORKFLOW FUNCTIONS
// ============================================================================

/**
 * Create a gallery with appearance settings and navigate to frontend
 */
export async function createGalleryWithAppearanceSettings(
  page: Page,
  options: AppearanceTestOptions,
  appearanceSettings?: AppearanceSettingsOptions,
  hoverSettings?: HoverEffectSettingsOptions
): Promise<string> {
  const { galleryName, templateSelector, screenshotPrefix, imageCount = 5 } = options;

  // Set viewport size
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

  // Configure appearance settings if provided
  if (appearanceSettings) {
    await configureAppearanceSettings(page, templateSelector, appearanceSettings);
  }

  // Configure hover effect settings if provided
  if (hoverSettings) {
    await configureHoverEffectSettings(page, templateSelector, hoverSettings);
  }

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

  // Wait for gallery to be visible
  await page.waitForSelector(APPEARANCE_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });

  // Screenshot frontend
  await page.screenshot({ path: `test-results/${screenshotPrefix}-frontend.png` });

  return galleryId;
}

/**
 * Wait for gallery to be ready on frontend
 */
export async function waitForGallery(page: Page): Promise<void> {
  await page.waitForSelector(APPEARANCE_SELECTORS.galleryContainer, { state: 'visible', timeout: 15000 });
  // Wait for images to load
  await page.waitForTimeout(1000);
}
