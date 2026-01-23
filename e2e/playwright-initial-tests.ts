test.describe("Load More Basic Flow", () => {
  test("tests Load More Basic Flow", async ({ page }) => {
    await page.setViewportSize({
          width: 1760,
          height: 1246
        })
    await page.goto("http://localhost:10009/wp-admin/index.php");
    await page.locator("#menu-posts-foogallery li:nth-of-type(3) > a").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post-new.php?post_type=foogallery');
    await page.locator("#title").type("Load More Basic Flow");
    await page.locator("div.foogallery-items-add button.button-primary").click()
    await page.locator("li:nth-of-type(1) > div > div").click()
    await page.locator("li:nth-of-type(2) > div > div").click()
    await page.locator("li:nth-of-type(3) > div > div").click()
    await page.locator("li:nth-of-type(4) > div > div").click()
    await page.locator("li:nth-of-type(5) > div > div").click()
    await page.locator("li:nth-of-type(6) > div > div").click()
    await page.locator("li:nth-of-type(7) > div > div").click()
    await page.locator("li:nth-of-type(8) > div > div").click()
    await page.locator("li:nth-of-type(9) > div > div").click()
    await page.locator("li:nth-of-type(10) > div > div").click()
    await page.locator("li:nth-of-type(11) > div > div").click()
    await page.locator("li:nth-of-type(12) > div > div").click()
    await page.locator("li:nth-of-type(13) > div > div").click()
    await page.locator("li:nth-of-type(14) > div > div").click()
    await page.locator("li:nth-of-type(15) > div > div").click()
    await page.locator("li:nth-of-type(16) > div > div").click()
    await page.locator("li:nth-of-type(17) > div > div").click()
    await page.locator("li:nth-of-type(18) > div > div").click()
    await page.locator("li:nth-of-type(19) > div > div").click()
    await page.locator("li:nth-of-type(20) > div > div").click()
    await page.locator("button.media-button-select").click()
    await page.locator("div.foogallery-settings-container-default div:nth-of-type(6) > span.foogallery-tab-text").click()
    await page.locator("#FooGallerySettings_default_paging_type4").click()
    await page.locator("div.foogallery-settings-container-default div.foogallery-tab-contents").click()
    await page.locator("#FooGallerySettings_default_paging_size").type("19");
    await page.locator("#FooGallerySettings_default_paging_size").click()
    await page.locator("#FooGallerySettings_default_paging_size").click()
    await page.locator("#FooGallerySettings_default_paging_size").dblclick();
    await page.locator("#FooGallerySettings_default_paging_size").click()
    await page.locator("#FooGallerySettings_default_paging_size").type("5");
    await page.locator("div.foogallery-settings-container-default tr.foogallery_template_field_id-paging_size > th").click()
    await page.locator("#FooGallerySettings_default_paging_size").type("6");
    await page.locator("#FooGallerySettings_default_paging_size").type("7");
    await page.locator("#FooGallerySettings_default_paging_size").dblclick();
    await page.locator("#FooGallerySettings_default_paging_size").type("6");
    await page.locator("#FooGallerySettings_default_paging_size").type("5");
    await page.locator("#FooGallerySettings_default_paging_size").dblclick();
    await page.locator("div.foogallery-settings-container-default tr.foogallery_template_field_id-paging_size > td").click()
    await page.locator("#publish").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post.php?post=1721&action=edit&message=6');
    await page.locator("#foogallery_create_page").click()
    expect(page.url()).toBe('undefined');
    await page.locator("span.view > a").click()
    await page.locator("#wp--skip-link--target button").click()
    await page.locator("#wp--skip-link--target button").click()
    await page.locator("#wp--skip-link--target button").click()
  });
});


test.describe("Infinite Scroll Basic Flow", () => {
  test("tests Infinite Scroll Basic Flow", async ({ page }) => {
    await page.setViewportSize({
          width: 1760,
          height: 1246
        })
    await page.goto("http://localhost:10009/wp-admin/index.php");
    await page.locator("#menu-posts-foogallery li:nth-of-type(3) > a").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post-new.php?post_type=foogallery');
    await page.locator("#title").type("Infinite Scroll Basic Flow");
    await page.locator("#title").click()
    await page.locator("button.button-primary > span.foogallery-add-button-label").click()
    await page.locator("li:nth-of-type(1) > div > div").click()
    await page.locator("li:nth-of-type(2) > div > div").click()
    await page.locator("li:nth-of-type(3) > div > div").click()
    await page.locator("li:nth-of-type(4) > div > div").click()
    await page.locator("li:nth-of-type(5) > div > div").click()
    await page.locator("li:nth-of-type(6) > div > div").click()
    await page.locator("li:nth-of-type(7) > div > div").click()
    await page.locator("li:nth-of-type(8) > div > div").click()
    await page.locator("li:nth-of-type(9) > div > div").click()
    await page.locator("li:nth-of-type(10) > div > div").click()
    await page.locator("li:nth-of-type(11) > div > div").click()
    await page.locator("li:nth-of-type(12) > div > div").click()
    await page.locator("li:nth-of-type(13) > div > div").click()
    await page.locator("li:nth-of-type(14) > div > div").click()
    await page.locator("li:nth-of-type(15) > div > div").click()
    await page.locator("li:nth-of-type(16) > div > div").click()
    await page.locator("li:nth-of-type(17) > div > div").click()
    await page.locator("div.attachments-wrapper li:nth-of-type(18)").click()
    await page.locator("li:nth-of-type(19) > div > div").click()
    await page.locator("li:nth-of-type(20) > div > div").click()
    await page.locator("button.media-button-select").click()
    await page.locator("div.foogallery-settings-container-default div:nth-of-type(6) > span.foogallery-tab-text").click()
    await page.locator("#FooGallerySettings_default_paging_type3").click()
    await page.locator("#FooGallerySettings_default_paging_size").dblclick();
    await page.locator("#FooGallerySettings_default_paging_size").click()
    await page.locator("#FooGallerySettings_default_paging_size").type("5");
    await page.locator("div.foogallery-settings-container-default tr.foogallery_template_field_id-paging_size div").click()
    await page.locator("#publish").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post.php?post=1723&action=edit&message=6');
    await page.locator("#foogallery_create_page").click()
    expect(page.url()).toBe('undefined');
    await page.locator("span.view > a").click()
  });
});


test.describe("Numbered Pagination - All Buttons", () => {
  test("tests Numbered Pagination - All Buttons", async ({ page }) => {
    await page.setViewportSize({
          width: 1760,
          height: 1246
        })
    await page.goto("http://localhost:10009/wp-admin/edit.php?post_type=foogallery");
    await page.locator("#menu-posts-foogallery li:nth-of-type(3) > a").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post-new.php?post_type=foogallery');
    await page.locator("#title").type("Numbered Pagination - All Buttons");
    await page.locator("#foogallery_template > div.inside > div > div").click()
    await page.locator("button.button-primary > span.dashicons").click()
    await page.locator("li:nth-of-type(1) > div > div").click()
    await page.locator("li:nth-of-type(2) > div > div").click()
    await page.locator("li:nth-of-type(3) > div > div").click()
    await page.locator("li:nth-of-type(4) > div > div").click()
    await page.locator("li:nth-of-type(5) > div > div").click()
    await page.locator("li:nth-of-type(6) > div > div").click()
    await page.locator("li:nth-of-type(7) > div > div").click()
    await page.locator("li:nth-of-type(8) > div > div").click()
    await page.locator("li:nth-of-type(9) > div > div").click()
    await page.locator("li:nth-of-type(10) > div > div").click()
    await page.locator("li:nth-of-type(11) > div > div").click()
    await page.locator("li:nth-of-type(12) > div > div").click()
    await page.locator("li:nth-of-type(13) > div > div").click()
    await page.locator("li:nth-of-type(14) > div > div").click()
    await page.locator("li:nth-of-type(15) > div > div").click()
    await page.locator("li:nth-of-type(16) > div > div").click()
    await page.locator("li.details > div > div").click()
    await page.locator("li:nth-of-type(18) > div > div").click()
    await page.locator("li:nth-of-type(19) > div > div").click()
    await page.locator("li:nth-of-type(20) > div > div").click()
    await page.locator("button.media-button-select").click()
    await page.locator("div.foogallery-settings-container-default div:nth-of-type(6) > span.foogallery-tab-text").click()
    await page.locator("#FooGallerySettings_default_paging_type2").click()
    await page.locator("div.foogallery-settings-container-default tr.foogallery_template_field_id-paging_limit > th").click()
    await page.locator("#FooGallerySettings_default_paging_showFirstLast0").click()
    await page.locator("#FooGallerySettings_default_paging_showPrevNext0").click()
    await page.locator("#FooGallerySettings_default_paging_showPrevNextMore0").click()
    await page.locator("div.foogallery-settings-container-default tr.foogallery_template_field_id-paging_limit > th").click()
    await page.locator("#FooGallerySettings_default_paging_showFirstLast0").click()
    await page.locator("#FooGallerySettings_default_paging_showPrevNext0").click()
    await page.locator("#publish").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post.php?post=1725&action=edit&message=6');
    await page.locator("#foogallery_create_page").click()
    expect(page.url()).toBe('');
    await page.locator("span.view > a").click()
    await page.locator("div.foogallery-settings-container-default div.foogallery-vertical-tabs > div:nth-of-type(6)").click()
    await page.locator("span.view > a").click()
    await page.locator("#FooGallerySettings_default_paging_size").dblclick();
    await page.locator("#FooGallerySettings_default_paging_size").click()
    await page.locator("#FooGallerySettings_default_paging_size").type("5");
    await page.locator("div.foogallery-settings-container-default tr.foogallery_template_field_id-paging_size p").click()
    await page.locator("#publish").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post.php?post=1725&action=edit&message=1');
    await page.locator("span.view > a").click()
    await page.locator("#wp--skip-link--target li:nth-of-type(4) > a").click()
    await page.locator("#wp--skip-link--target li:nth-of-type(5) > a").click()
    await page.locator("li.fg-page-next > a").click()
    await page.locator("li.fg-page-prev > a").click()
    await page.locator("li.fg-page-prev > a").click()
    await page.locator("li.fg-page-first > a").click()
    await page.locator("li.fg-page-last > a").click()
  });
});

test.describe("Numbered Pagination - Hide First/Last", () => {
  test("tests Numbered Pagination - Hide First/Last", async ({ page }) => {
    await page.setViewportSize({
          width: 1760,
          height: 1246
        })
    await page.goto("http://localhost:10009/wp-admin/edit.php?post_type=foogallery");
    await page.locator("div.wrap > a").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post-new.php?post_type=foogallery');
    await page.locator("#title").type("Numbered Pagination - Hide First/Last");
    await page.locator("div.foogallery-items-add button.button-primary").click()
    await page.locator("li:nth-of-type(1) > div > div").click()
    await page.locator("li:nth-of-type(2) > div > div").click()
    await page.locator("li:nth-of-type(3) > div > div").click()
    await page.locator("li:nth-of-type(4) > div > div").click()
    await page.locator("li:nth-of-type(5) > div > div").click()
    await page.locator("li:nth-of-type(6) > div > div").click()
    await page.locator("li:nth-of-type(7) > div > div").click()
    await page.locator("li:nth-of-type(8) > div > div").click()
    await page.locator("li:nth-of-type(9) > div > div").click()
    await page.locator("li:nth-of-type(10) > div > div").click()
    await page.locator("li:nth-of-type(11) > div > div").click()
    await page.locator("li:nth-of-type(12) > div > div").click()
    await page.locator("li:nth-of-type(13) > div > div").click()
    await page.locator("li:nth-of-type(14) > div > div").click()
    await page.locator("li:nth-of-type(15) > div > div").click()
    await page.locator("li:nth-of-type(16) > div > div").click()
    await page.locator("li:nth-of-type(17) > div > div").click()
    await page.locator("li:nth-of-type(18) > div > div").click()
    await page.locator("li:nth-of-type(19) > div > div").click()
    await page.locator("li:nth-of-type(20) > div > div").click()
    await page.locator("button.media-button-select").click()
    await page.locator("div.foogallery-settings-container-default div.foogallery-vertical-tabs > div:nth-of-type(7)").click()
    await page.locator("div.foogallery-settings-container-default div:nth-of-type(6) > span.foogallery-tab-text").click()
    await page.locator("#FooGallerySettings_default_paging_type2").click()
    await page.locator("#FooGallerySettings_default_paging_showFirstLast1").click()
    await page.locator("#FooGallerySettings_default_paging_showPrevNext1").click()
    await page.locator("#publish").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post.php?post=1727&action=edit&message=6');
    await page.locator("#foogallery_create_page").click()
    expect(page.url()).toBe('undefined');
    await page.locator("span.view > a").click()
    await page.locator("div.foogallery-settings-container-default div:nth-of-type(6) > span.foogallery-tab-text").click()
    await page.locator("#FooGallerySettings_default_paging_size").dblclick();
    await page.locator("#FooGallerySettings_default_paging_size").click()
    await page.locator("#FooGallerySettings_default_paging_size").type("5");
    await page.locator("#poststuff").click()
    await page.locator("#publish").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post.php?post=1727&action=edit&message=1');
    await page.locator("#wp--skip-link--target li:nth-of-type(2) > a").click()
    await page.locator("#wp--skip-link--target li:nth-of-type(3) > a").click()
    await page.locator("#wp--skip-link--target li:nth-of-type(4) > a").click()
  });
});


test.describe("Pagination Position", () => {
  test("tests Pagination Position", async ({ page }) => {
    await page.setViewportSize({
          width: 1760,
          height: 1246
        })
    await page.goto("http://localhost:10009/wp-admin/index.php");
    await page.locator("#menu-posts-foogallery li:nth-of-type(3) > a").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post-new.php?post_type=foogallery');
    await page.locator("#title").type("Pagination Position - Top");
    await page.locator("div.foogallery-items-add button.button-primary").click()
    await page.locator("li:nth-of-type(1) > div > div").click()
    await page.locator("li:nth-of-type(2) > div > div").click()
    await page.locator("li:nth-of-type(3) > div > div").click()
    await page.locator("li:nth-of-type(4) > div > div").click()
    await page.locator("li:nth-of-type(5) > div > div").click()
    await page.locator("li:nth-of-type(6) > div > div").click()
    await page.locator("li:nth-of-type(7) > div > div").click()
    await page.locator("li:nth-of-type(8) > div > div").click()
    await page.locator("li:nth-of-type(9) > div > div").click()
    await page.locator("li:nth-of-type(10) > div > div").click()
    await page.locator("li:nth-of-type(11) > div > div").click()
    await page.locator("li:nth-of-type(12) > div > div").click()
    await page.locator("li:nth-of-type(13) > div > div").click()
    await page.locator("li:nth-of-type(14) > div > div").click()
    await page.locator("li:nth-of-type(15) > div > div").click()
    await page.locator("li:nth-of-type(16) > div > div").click()
    await page.locator("li:nth-of-type(17) > div > div").click()
    await page.locator("li:nth-of-type(18) > div > div").click()
    await page.locator("li:nth-of-type(19) > div > div").click()
    await page.locator("li:nth-of-type(20) > div > div").click()
    await page.locator("button.media-button-select").click()
    await page.locator("div.foogallery-settings-container-default div:nth-of-type(6) > span.foogallery-tab-text").click()
    await page.locator("#FooGallerySettings_default_paging_type2").click()
    await page.locator("#FooGallerySettings_default_paging_size").dblclick();
    await page.locator("#FooGallerySettings_default_paging_size").click()
    await page.locator("#FooGallerySettings_default_paging_size").click()
    await page.locator("#FooGallerySettings_default_paging_size").type("5");
    await page.locator("div.foogallery-settings-container-default tr.foogallery_template_field_id-paging_position p").click()
    await page.locator("#FooGallerySettings_default_paging_position1").click()
    await page.locator("#publish").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post.php?post=1729&action=edit&message=6');
    await page.locator("#foogallery_create_page").click()
    expect(page.url()).toBe('undefined');
    await page.locator("span.view > a").click()
    await page.locator("#wp--skip-link--target li:nth-of-type(4) > a").click()
    await page.locator("#wp--skip-link--target li:nth-of-type(5) > a").click()
    await page.locator("#wp--skip-link--target li:nth-of-type(6) > a").click()
    await page.locator("li.fg-page-prev > a").click()
    await page.locator("li.fg-page-prev > a").click()
    await page.locator("li.fg-page-next > a").click()
    await page.locator("li.fg-page-next > a").click()
    await page.locator("li.fg-page-first > a").click()
    await page.locator("li.fg-page-last > a").click()
    await page.locator("#menu-posts-foogallery li:nth-of-type(3) > a").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post-new.php?post_type=foogallery');
    await page.locator("#title").type("Pagination Position - Both");
    await page.locator("div.foogallery-items-add button.button-primary").click()
    await page.locator("li:nth-of-type(1) > div > div").click()
    await page.locator("li:nth-of-type(2) > div > div").click()
    await page.locator("li:nth-of-type(3) > div > div").click()
    await page.locator("li:nth-of-type(4) > div > div").click()
    await page.locator("li:nth-of-type(5) > div > div").click()
    await page.locator("li:nth-of-type(6) > div > div").click()
    await page.locator("li:nth-of-type(7) > div > div").click()
    await page.locator("li:nth-of-type(8) > div > div").click()
    await page.locator("li:nth-of-type(9) > div > div").click()
    await page.locator("li:nth-of-type(10) > div > div").click()
    await page.locator("li:nth-of-type(11) > div > div").click()
    await page.locator("li:nth-of-type(12) > div > div").click()
    await page.locator("li:nth-of-type(13) > div > div").click()
    await page.locator("li:nth-of-type(14) > div > div").click()
    await page.locator("li:nth-of-type(15) > div > div").click()
    await page.locator("li:nth-of-type(16) > div > div").click()
    await page.locator("li:nth-of-type(17) > div > div").click()
    await page.locator("li:nth-of-type(18) > div > div").click()
    await page.locator("li:nth-of-type(19) > div > div").click()
    await page.locator("li:nth-of-type(20) > div > div").click()
    await page.locator("button.media-button-select").click()
    await page.locator("div.foogallery-settings-container-default div:nth-of-type(6) > span.foogallery-tab-text").click()
    await page.locator("#FooGallerySettings_default_paging_type2").click()
    await page.locator("#FooGallerySettings_default_paging_size").click()
    await page.locator("#FooGallerySettings_default_paging_size").type("");
    page.keyboard.down("{Backspace}");
    await page.locator("#FooGallerySettings_default_paging_size").type("5");
    await page.locator("div.foogallery-settings-container-default tr.foogallery_template_field_id-paging_position > td").click()
    await page.locator("#FooGallerySettings_default_paging_position3").click()
    await page.locator("#publish").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post.php?post=1731&action=edit&message=6');
    await page.locator("#foogallery_create_page").click()
    expect(page.url()).toBe('undefined');
    await page.locator("span.view > a").click()
    await page.locator("#foogallery-gallery-1731_paging-bottom li:nth-of-type(4) > a").click()
    await page.locator("#foogallery-gallery-1731_paging-top li:nth-of-type(5) > a").click()
    await page.locator("#foogallery-gallery-1731_paging-bottom li.fg-page-next > a").click()
    await page.locator("#foogallery-gallery-1731_paging-top li.fg-page-prev > a").click()
    await page.locator("#foogallery-gallery-1731_paging-top li.fg-page-last > a").click()
    await page.locator("#foogallery-gallery-1731_paging-bottom li.fg-page-first > a").click()
    await page.locator("#foogallery-gallery-1731_paging-bottom li.fg-page-next > a").click()
    await page.locator("#foogallery-gallery-1731_paging-top li.fg-page-prev > a").click()
    await page.locator("#foogallery-gallery-1731_paging-top li.fg-page-next > a").click()
    await page.locator("#foogallery-gallery-1731_paging-bottom li.fg-page-prev > a").click()
  });
});

test.describe("Load More with Theme", () => {
  test("tests Load More with Theme", async ({ page }) => {
    await page.setViewportSize({
          width: 1760,
          height: 1246
        })
    await page.goto("http://localhost:10009/wp-admin/edit.php?post_type=foogallery");
    await page.locator("#menu-posts-foogallery li:nth-of-type(3) > a").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post-new.php?post_type=foogallery');
    await page.locator("#title").click()
    await page.locator("#title").type("Load More with Theme");
    await page.locator("div.foogallery-items-add button.button-primary").click()
    await page.locator("li:nth-of-type(1) > div > div").click()
    await page.locator("li:nth-of-type(2) > div > div").click()
    await page.locator("li:nth-of-type(3) > div > div").click()
    await page.locator("li:nth-of-type(4) > div > div").click()
    await page.locator("li:nth-of-type(5) > div > div").click()
    await page.locator("li:nth-of-type(6) > div > div").click()
    await page.locator("li:nth-of-type(7) > div > div").click()
    await page.locator("li:nth-of-type(8) > div > div").click()
    await page.locator("li:nth-of-type(9) > div > div").click()
    await page.locator("li:nth-of-type(18) > div > div").click()
    await page.locator("li:nth-of-type(17) > div > div").click()
    await page.locator("li:nth-of-type(16) > div > div").click()
    await page.locator("li:nth-of-type(15) > div > div").click()
    await page.locator("li:nth-of-type(14) > div > div").click()
    await page.locator("li:nth-of-type(13) > div > div").click()
    await page.locator("li:nth-of-type(12) > div > div").click()
    await page.locator("li:nth-of-type(11) > div > div").click()
    await page.locator("li:nth-of-type(10) > div > div").click()
    await page.locator("li:nth-of-type(19) > div > div").click()
    await page.locator("li:nth-of-type(20) > div > div").click()
    await page.locator("button.media-button-select").click()
    await page.locator("div.foogallery-settings-container-default div.foogallery-vertical-tabs > div:nth-of-type(6)").click()
    await page.locator("#FooGallerySettings_default_paging_type4").click()
    await page.locator("#FooGallerySettings_default_paging_size").click()
    await page.locator("#FooGallerySettings_default_paging_size").type("");
    page.keyboard.down("{Backspace}");
    await page.locator("#FooGallerySettings_default_paging_size").type("5");
    await page.locator("div.foogallery-settings-container-default div.foogallery-tab-contents").click()
    await page.locator("div.foogallery-settings-container-default div.foogallery-tab-contents").click()
    await page.locator("#FooGallerySettings_default_paging_theme2").click()
    await page.locator("#publish").click()
    expect(page.url()).toBe('http://localhost:10009/wp-admin/post.php?post=1733&action=edit&message=6');
    await page.locator("#foogallery_create_page").click()
    expect(page.url()).toBe('undefined');
    await page.locator("span.view > a").click()
    await page.locator("#wp--skip-link--target button").click()
    await page.locator("#wp--skip-link--target button").click()
    await page.locator("#wp--skip-link--target button").click()
  });
});


