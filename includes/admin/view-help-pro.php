<?php

$features = array(
	array(
		'title' => __( 'More Gallery Templates','foogallery' ),
		'desc' => __( 'Get more gallery templates, including Slider PRO, Grid PRO and Polaroid.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery/beautiful-gallery-templates/',
		'utm_content' => 'gallery_templates',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-templates.png'
	),
	array(
		'title' => __( 'Videos','foogallery' ),
		'desc' => __( 'Create amazing video galleries by simply importing videos from YouTube, Vimeo and other sources. Also create galleries from self-hosted videos that you have uploaded to your media library. You can also create mixed galleries with both videos and images.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery/wordpress-video-gallery/',
		'utm_content' => 'videos',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-videos.png'
	),
	array(
		'title' => __( 'Tag Filtering','foogallery' ),
		'desc' => __( 'Add tags or categories to your images or videos, and then allow your visitors to filter your gallery. Distinguish between your tags by showing count, or changing the size or opacity. You can also setup multi-level filtering!', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery/wordpress-filtered-gallery/',
		'utm_content' => 'filtering',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-filtering.png'
	),
	array(
		'title' => __( 'Dynamic Galleries','foogallery' ),
		'desc' => __( 'Create dynamic galleries from other sources, including Tags, Categories, Server Folders, Adobe Lightroom or a Post Query.', 'foogallery' ),
		'link' => 'https://fooplugins.com/load-galleries-from-other-sources/',
		'utm_content' => 'datasources',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-datasources.png'
	),
	array(
		'title' => __( 'Custom Captions','foogallery' ),
		'desc' => __( 'Customize your gallery captions, by building dynamic captions using HTML and pre-defined placeholders. Integrates with popular solutions like ACF and Pods for unlimited possibilities.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery/custom-captions-wordpress-gallery/',
		'utm_content' => 'captions',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-captions.png'
	),
	array(
		'title' => __( 'PRO Lightbox','foogallery' ),
		'desc' => __( 'Enable a full-featured, customizable lightbox. You can change the color scheme, show a thumbnail strip, enable a slideshow, customize the captions and so much more!', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery/foogallery-pro-lightbox/',
		'utm_content' => 'lightbox',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-lightbox.png'
	),
	array(
		'title' => __( 'Advanced Pagination','foogallery' ),
		'desc' => __( 'Add various types of paging to your galleries, including the popular Infinite Scroll and Load More variations. Paging is a very useful for larger galleries, as it means your visitors do not need to load the entire gallery all at once.', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery/gallery-pagination/',
		'utm_content' => 'pagination',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-pagination.png'
	),
	array(
		'title' => __( 'Image Filter Effects','foogallery' ),
		'desc' => __( 'Add 12 image filter effects to your thumbnails, just like you can do with Instagram. Make your galleries stand out from the competition!', 'foogallery' ),
		'link' => 'https://fooplugins.com/foogallery/thumbnail-filters/',
		'utm_content' => 'filter-effects',
		'link_text' => __( 'Learn More','foogallery' ),
		'image' => 'https://assets.fooplugins.com/foogallery/plugin/foogallery-admin-help-pro-effects.png'
	),
);

?>

<div id="pro_section" class="foogallery-admin-help-section" style="display: none">
	<?php if ( $show_trial_message ) { ?>
		<div class="foogallery-admin-help-section-feature foogallery-admin-help-centered">
			<h2><?php _e( 'FooBar PRO Free Trial 🤩', 'foogallery' );?></h2>
			<p><?php _e( 'Want to test out all the PRO features below? No problem! You can start a 7-day free trial immediately!', 'foogallery' );?></p>
			<a class="foogallery-admin-help-button-cta" href="<?php echo esc_url ( foogallery_admin_freetrial_url() ); ?>"><?php _e( 'Start Your 7-day Free Trial', 'foogallery' ); ?></a>
		</div>
	<?php } else if ( $show_thanks_for_pro ) { ?>
		<div class="foogallery-admin-help-section-feature foogallery-admin-help-centered">
			<h2><?php _e( 'Thanks for your support by purchasing a PRO license 😍', 'foogallery' );?></h2>
			<p><?php _e( 'Check out the PRO features you can start using immediately...', 'foogallery' );?></p>
		</div>
	<?php } else if ( $is_trial ) { ?>
		<div class="foogallery-admin-help-section-feature foogallery-admin-help-centered">
			<h2><?php _e( 'Thanks for trying out PRO 😍', 'foogallery' );?></h2>
			<p><?php _e( 'Check out the PRO features you can try out immediately...', 'foogallery' );?></p>
		</div>
	<?php } ?>

	<?php foreach ( $features as $feature ) { ?>
	<div class="foogallery-admin-help-section-feature">

		<div class="foogallery-admin-help-2-columns">
			<div class="foogallery-admin-help-column">
				<h2>
					<img src="<?php echo esc_url( 'https://assets.fooplugins.com/foogallery/plugin/green-tick.png' ); ?>" />
					<span style="vertical-align: top"><?php echo esc_html( $feature['title']); ?></span>
				</h2>
				<p>
					<?php echo esc_html( $feature['desc']); ?>
					<a href="<?php echo esc_url( foogallery_admin_url( $feature['link'], 'help', $feature['utm_content'] ) ); ?>" target="_blank"><?php echo esc_html( $feature['link_text']); ?></a>
				</p>
			</div>
			<div class="foogallery-admin-help-column">
				<a href="<?php echo esc_url( foogallery_admin_url( $feature['link'], 'help', $feature['utm_content'] ) ); ?>" target="_blank">
					<img src="<?php echo esc_url( $feature['image'] ); ?>" />
				</a>
			</div>
		</div>
	</div>
	<?php } ?>
</div>