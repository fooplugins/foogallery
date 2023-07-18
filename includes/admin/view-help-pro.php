<?php

$foogallery_plans = array(
	FOOGALLERY_PRO_PLAN_STARTER  => __( 'PRO Starter', 'foogallery' ),
	FOOGALLERY_PRO_PLAN_EXPERT   => __( 'PRO Expert', 'foogallery' ),
	FOOGALLERY_PRO_PLAN_COMMERCE => __( 'PRO Commerce', 'foogallery' ),
);

$foogallery_pro_features = array(
	array(
		'title' => __( 'PRO Gallery Templates','foogallery' ),
		'desc' => __( '3 more advanced gallery templates to help you showcase your photography, including Slider PRO, Grid PRO and Polaroid PRO.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/gallery-templates/',
		'utm_content' => 'gallery_templates',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-templates.png',
		'plan' => FOOGALLERY_PRO_PLAN_STARTER,
		'plans' => array( FOOGALLERY_PRO_PLAN_STARTER, FOOGALLERY_PRO_PLAN_EXPERT, FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Animated Hover Effect Presets','foogallery' ),
		'desc' => __( 'Choose from 11 animated hover effect presets, to add that professional and elegant look to your galleries.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/hover-presets/',
		'utm_content' => 'hover_presets',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-presets.png',
	    'plan' => FOOGALLERY_PRO_PLAN_STARTER,
		'plans' => array( FOOGALLERY_PRO_PLAN_STARTER, FOOGALLERY_PRO_PLAN_EXPERT, FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Image Filter Effects','foogallery' ),
		'desc' => __( 'Mage your galleries pop, by adding one of 12 image filter effects to your thumbnails, just like you can do with Instagram. Make your galleries stand out from the competition!', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/filter-effects/',
		'utm_content' => 'filter_effects',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-effects.png',
		'plan' => FOOGALLERY_PRO_PLAN_STARTER,
		'plans' => array( FOOGALLERY_PRO_PLAN_STARTER, FOOGALLERY_PRO_PLAN_EXPERT, FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Video Galleries','foogallery' ),
		'desc' => __( 'Showcase your videos! Create amazing video galleries by importing videos from YouTube, Vimeo and other sources. Also create galleries from self-hosted videos that you have uploaded to your media library. You can also create mixed galleries with both videos and images.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/video-gallery/',
		'utm_content' => 'videos',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-videos.png',
		'plan' => FOOGALLERY_PRO_PLAN_EXPERT,
		'plans' => array( FOOGALLERY_PRO_PLAN_EXPERT, FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Multi-Level Tag Filtering','foogallery' ),
		'desc' => __( 'Add tags or categories to your images or videos, and then allow your visitors to filter your gallery. Distinguish between your tags by showing count, or changing the size or opacity. You can also setup multi-level filtering!', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/filtering/',
		'utm_content' => 'filtering',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-filtering.png',
		'plan' => FOOGALLERY_PRO_PLAN_EXPERT,
		'plans' => array( FOOGALLERY_PRO_PLAN_EXPERT, FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Gallery Search','foogallery' ),
		'desc' => __( 'Add a search input to your gallery to allow your visitors to search the gallery by the caption or tags.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/filtering/#search',
		'utm_content' => 'search-filtering',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-search-filter.png',
		'plan' => FOOGALLERY_PRO_PLAN_EXPERT,
		'plans' => array( FOOGALLERY_PRO_PLAN_EXPERT, FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Advanced Pagination','foogallery' ),
		'desc' => __( 'Add more advanced types of pagination to your galleries, including page numbering and the popular "Infinite Scroll" and "Load More" variations. Paging is a very useful for larger galleries, as it means your visitors do not need to load the entire gallery all at once.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/pagination/',
		'utm_content' => 'pagination',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-pagination.png',
		'plan' => FOOGALLERY_PRO_PLAN_EXPERT,
		'plans' => array( FOOGALLERY_PRO_PLAN_EXPERT, FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Dynamic Galleries','foogallery' ),
		'desc' => __( 'Create dynamic galleries from other sources, including Tags, Categories, Server Folders, Adobe Lightroom or a Post Query.', 'foogallery' ),
		'link' => 'https://fooplugins.com/load-galleries-from-other-sources/',
		'utm_content' => 'datasources',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-datasources2.png',
		'plan' => FOOGALLERY_PRO_PLAN_EXPERT,
		'plans' => array( FOOGALLERY_PRO_PLAN_EXPERT, FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Custom Captions','foogallery' ),
		'desc' => __( 'Customize your gallery captions, by building dynamic captions using HTML and pre-defined placeholders. Integrates with popular solutions like ACF and Pods for unlimited possibilities.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/custom-captions/',
		'utm_content' => 'captions',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-captions.png',
		'plan' => FOOGALLERY_PRO_PLAN_EXPERT,
		'plans' => array( FOOGALLERY_PRO_PLAN_EXPERT, FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'EXIF Metadata','foogallery' ),
		'desc' => __( 'Show image metadata within your galleries. A must-have for professional photographers wanting to showcase specific metadata about each image.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/exif-data/',
		'utm_content' => 'exif',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-exif.png',
        'plan' => FOOGALLERY_PRO_PLAN_EXPERT,
		'plans' => array( FOOGALLERY_PRO_PLAN_EXPERT, FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Bulk Copy Gallery Settings','foogallery' ),
		'desc' => __( 'Copy settings from one gallery to other galleries in bulk.', 'foogallery' ),
		'link' => 'https://fooplugins.com/bulk-copy-foogallery-pro/',
		'utm_content' => 'bulk_copy_settings',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-bulk-copy-settings.png',
        'plan' => FOOGALLERY_PRO_PLAN_EXPERT,
		'plans' => array( FOOGALLERY_PRO_PLAN_EXPERT, FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'WooCommerce Product Datasource','foogallery' ),
		'desc' => __( 'Create a dynamic product gallery from your WooCommerce products. You can filter and limit the products shown.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/woocommerce-integration/',
		'utm_content' => 'cta_buttons',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-woocommerce-datasource.png',
		'plan' => FOOGALLERY_PRO_PLAN_COMMERCE,
		'plans' => array( FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Sell Images With A Master Product','foogallery' ),
		'desc' => __( 'Create a single "Master Product" with variations and link it to all the images in your gallery. This means you can start selling your image just a stock photo website!', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/woocommerce-integration/#master-product',
		'utm_content' => 'product_gallery',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-sell-images-master-product.png',
		'plan' => FOOGALLERY_PRO_PLAN_COMMERCE,
		'plans' => array( FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Product Gallery Template','foogallery' ),
		'desc' => __( 'We created a new gallery template specifically for showcasing products, with all the default settings you need to take the most advantage of the commerce features.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/woocommerce-integration/#product-gallery',
		'utm_content' => 'product_gallery',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-product-gallery.png',
		'plan' => FOOGALLERY_PRO_PLAN_COMMERCE,
		'plans' => array( FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Watermarking &amp; Protection','foogallery' ),
		'desc' => __( 'Protect your images by not allowing visitors to right click, and also by adding watermarks to images in your galleries. Beautiful looking repeating watermarks are built-in, or use your own custom image.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/photo-watermark/',
		'utm_content' => 'protection',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-watermarking.png',
		'plan' => FOOGALLERY_PRO_PLAN_COMMERCE,
		'plans' => array( FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Lightbox Product Info','foogallery' ),
		'desc' => __( 'Our lightbox integrates with WooCommerce, to show product information, including listing all variations and add to cart buttons.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/woocommerce-integration/#product-variations',
		'utm_content' => 'product_info_lightbox',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-product-info-lightbox.png',
		'plan' => FOOGALLERY_PRO_PLAN_COMMERCE,
		'plans' => array( FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'CTA Buttons','foogallery' ),
		'desc' => __( 'Add Call-to-Action buttons to your gallery images or videos. You can link to product pages or allow visitors to add to cart.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/woocommerce-integration/#cta-buttons',
		'utm_content' => 'cta_buttons',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-buttons.png',
		'plan' => FOOGALLERY_PRO_PLAN_COMMERCE,
		'plans' => array( FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Sales Ribbons','foogallery' ),
		'desc' => __( 'Add attention-grabbing ribbons to your images to highlight sales or special offers, to increase conversions.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/woocommerce-integration/#sales-ribbons',
		'utm_content' => 'cta_ribbons',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-ribbons.png',
		'plan' => FOOGALLERY_PRO_PLAN_COMMERCE,
		'plans' => array( FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
	array(
		'title' => __( 'Master Galleries','foogallery' ),
		'desc' => __( 'Tired of copying settings from one gallery to the next? You will love our master gallery feature! Setup a master gallery, that multiple other galleries can inherit those same settings and look and feel from.', 'foogallery' ),
		'link' => 'https://fooplugins.com/documentation/foogallery/pro-commerce/use-master-gallery/',
		'utm_content' => 'cta_master_galleries',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-master-galleries.png',
		'plan' => FOOGALLERY_PRO_PLAN_COMMERCE,
		'plans' => array( FOOGALLERY_PRO_PLAN_COMMERCE ),
	),
    array(
        'title' => __( 'White Labeling','foogallery' ),
        'desc' => __( 'Rebrand FooGallery to whatever you like for your clients. Move or hide menu items too. Ideal for freelancers and agencies.', 'foogallery' ),
        'link' => 'https://fooplugins.com/documentation/foogallery/pro-commerce/white-labeling/',
        'utm_content' => 'cta_white_labeling',
        'link_text' => __( 'Learn More','foogallery' ),
        'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-white-labeling.png',
        'plan' => FOOGALLERY_PRO_PLAN_COMMERCE,
        'plans' => array( FOOGALLERY_PRO_PLAN_COMMERCE ),
    ),
);

?>

<div id="pro_section" class="foogallery-admin-help-section" style="display: none">
	<section class="fgah-feature">
		<header>
			<h3><?php _e( 'FooGallery PRO Plans', 'foogallery' );?></h3>
			<p><?php _e( 'Choose from a PRO Plan that suits your requirements and budget : ', 'foogallery' );?>
				<span class="fgah-plan-prostarter"><?php _e( 'PRO Starter', 'foogallery' );?></span>,
				<span class="fgah-plan-pro"><?php _e( 'PRO Expert', 'foogallery' );?></span> <?php _e( 'or', 'foogallery' );?>
				<span class="fgah-plan-commerce"><?php _e( 'PRO Commerce', 'foogallery' );?></span>
			</p>
		</header>
		<footer>
			<a class="foogallery-admin-help-button-cta" href="<?php echo esc_url ( $plans_url ); ?>"><?php _e( 'Compare FooGallery PRO Plans', 'foogallery' ); ?></a>
		</footer>
	</section>
    <section class="fgah-feature">
        <?php if ( $show_trial_message ) { ?>
            <header>
                <h3><?php _e( 'FooGallery PRO Free Trial 🤩', 'foogallery' );?></h3>
                <p><?php _e( 'Want to test out all the PRO features below? No problem! You can start a 7-day free trial immediately!', 'foogallery' );?></p>
            </header>
            <footer>
                <a class="foogallery-admin-help-button-cta" href="<?php echo esc_url ( foogallery_admin_freetrial_url() ); ?>"><?php _e( 'Start Your 7-day Free Trial', 'foogallery' ); ?></a>
            </footer>
        <?php } else if ( $show_thanks_for_pro ) { ?>
            <header>
                <h3><?php echo sprintf( __( 'Thanks for your support by purchasing a %s license 😍', 'foogallery' ), '<span class="fgah-plan-' . $foogallery_current_plan . '">' . $foogallery_plans[ $foogallery_current_plan ] . '</span>' );?></h3>
                <p><?php _e( 'Check out the PRO features you can start using immediately...', 'foogallery' );?></p>
            </header>
        <?php } else if ( $is_trial ) { ?>
            <header>
                <h3><?php _e( 'Thanks for trying out PRO 😍', 'foogallery' );?></h3>
                <p><?php _e( 'Check out the PRO features you can try out immediately...', 'foogallery' );?></p>
            </header>
        <?php } ?>
    </section>

	<?php foreach ( $foogallery_pro_features as $i => $feature ) { ?>
    <section class="fgah-feature fgah-feature-pro<?php echo ( $i % 2 === 0 ) ? " fgah-feature-right" : ""; ?> fgah-feature-plan-<?php echo $feature['plan']; ?>">
        <div>
            <figure>
                <a href="<?php echo esc_url( foogallery_admin_url( $feature['link'], 'help', $feature['utm_content'] ) ); ?>" target="_blank">
                    <img src="<?php echo esc_url( $feature['image'] ); ?>" alt="<?php echo esc_html( $feature['title'] ); ?>" />
                </a>
            </figure>
            <dl>
                <dt><?php echo esc_html( $feature['title']); ?></dt>
                <dd>
	                <div class="fgah-feature-sub-title">
		                <h4><?php _e( 'Available in:', 'foogallery' ); ?></h4>
		                <?php
		                $available_plans = array();
		                foreach ( $feature['plans'] as $plan ) {
			                $available_plans[] = '<span class="fgah-plan-' . esc_attr( $plan ) . '">' . esc_html( $foogallery_plans[ $plan ] ) . '</span>';
		                }
		                echo implode( ' ', $available_plans );
	                    ?>
	                </div>
	                <p>
		                <?php echo esc_html( $feature['desc'] ); ?>
	                </p>
	                <p>
                        <a href="<?php echo esc_url( foogallery_admin_url( $feature['link'], 'help', $feature['utm_content'] ) ); ?>"
                           target="_blank"><?php echo esc_html( $feature['link_text']); ?></a>
	                </p>
                </dd>
            </dl>
        </div>
    </section>
    <?php } ?>
</div>