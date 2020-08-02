<?php
/*
 * FooGallery Public class
 */

if ( ! class_exists( 'FooGallery_Public' ) ) {

	class FooGallery_Public {

		function __construct() {
			//new FooGallery_Shortcodes();
            new FooGallery_CSS_Load_Optimizer();
			new FooGallery_AdminBar();
			new FooGallery_Yoast_Seo_Sitemap_Support();
			new FooGallery_RankMath_Seo_Sitemap_Support();
			new FooGallery_All_In_One_Seo_Sitemap_Support();
		}

	}

}
