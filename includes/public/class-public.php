<?php
/*
 * FooGallery Public class
 */

if ( ! class_exists( 'FooGallery_Public' ) ) {

	class FooGallery_Public {

		function __construct() {
			new FooGallery_Shortcodes();
            new FooGallery_CSS_Load_Optimizer();
			new FooGallery_AdminBar();
			new FooGallery_Yoast_Seo_Sitemap_Support();
		}

	}

}
