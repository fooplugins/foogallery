<?php

$instance = FooGallery_Plugin::get_instance();
$info = $instance->get_plugin_info();
?>
<style>
	.about-wrap img.foogallery-help-screenshot {
		float:right;
		margin-left: 20px;
	}

	.foogallery-badge-foobot {
		position: absolute;
		top: 15px;
		right: 0;
		background:url(<?php echo FOOGALLERY_URL; ?>assets/foobot.png) no-repeat;
		width:109px;
		height:200px;
	}
	.feature-section h2 {
		margin-top: 0;
	}

</style>
<div class="wrap about-wrap">
	<h1><?php printf( __( 'Welcome to FooGallery %s', 'foogallery' ), $info['version'] ); ?></h1>
	<div class="about-text">
		<?php _e( 'Thank you for choosing FooGallery, the most intuitive and extensible gallery creation and management tool ever created for WordPress!', 'foogallery' ); ?>
		-
		<a href="http://foo.gallery" target="_blank"><?php _e('Visit our homepage', 'foogallery'); ?>.</a>
	</div>
	<div class="foogallery-badge-foobot"></div>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" href="#">
			<?php _e( "Getting Started", 'foogallery' ); ?>
		</a>
		<a class="nav-tab" href="<?php echo foogallery_admin_extensions_url(); ?>">
			<?php _e( 'Extensions', 'foogallery' ); ?>
		</a>
		<a class="nav-tab" href="http://fooplugins.com">
			<?php _e( 'Other Plugins', 'foogallery' ); ?>
		</a>
	</h2>

	<div class="changelog">

		<div class="feature-section">

			<img src="<?php echo FOOGALLERY_URL . 'assets/screenshots/admin-edit-gallery.jpg'; ?>" class="foogallery-help-screenshot"/>

			<h2><?php _e( 'Creating Your First Gallery', 'foogallery' );?></h2>

			<h4><?php printf( __( '<a href="%s">Galleries &rarr; Add New</a>', 'foogallery' ), admin_url( 'post-new.php?post_type=foogallery' ) ); ?></h4>
			<p><?php _e( 'To create your first gallery, simply click the Add New button or click the Add Gallery menu link. Then choose images from the media library to include in your gallery.', 'foogallery'); ?></p>

			<h4><?php _e( 'Drag and Drop Reordering', 'foogallery' );?></h4>
			<p><?php _e( 'Sort the images in your gallery simply by dragging them around.', 'foogallery' );?></p>

			<h4><?php _e( 'Gallery Templates', 'foogallery' );?></h4>
			<p><?php _e( 'Choose one of our built-in gallery templates or download one via our extension library.', 'foogallery' );?></p>

			<h4><?php _e( 'Lightbox Support', 'foogallery' );?></h4>
			<p><?php _e( 'Our default gallery template supports FooBox : our popular responsive image lightbox.', 'foogallery' );?></p>


		</div>
	</div>

	<?php do_action('foogallery_admin_help_after_section_one'); ?>

	<div class="changelog">

		<div class="feature-section">
			<img src="<?php echo FOOGALLERY_URL . 'assets/screenshots/admin-insert-shortcode.jpg'; ?>" class="foogallery-help-screenshot"/>

			<h2><?php _e( 'Show Off Your Gallery', 'foogallery' );?></h2>

			<h4><?php _e( 'The <em>[foogallery]</em> Short Code','foogallery' );?></h4>
			<p><?php _e( 'Simply copy the shortcode code from the gallery listing page and paste it into your posts or pages.', 'foogallery' );?></p>

			<h4><?php _e( 'Visual Editor Button', 'foogallery' );?></h4>
			<p><?php _e( 'Or to make life even easier, you can insert a gallery using the Add FooGallery button inside the WordPress visual editor.', 'foogallery' );?></p>

			<h4><?php _e( 'Copy To Clipboard','foogallery' );?></h4>
			<p><?php _e( 'We make your life easy! Just click the shortcodes and they get copied to your clipboard automatically. ', 'foogallery' );?></p>

		</div>
	</div>

	<?php do_action('foogallery_admin_help_after_section_two'); ?>

	<div class="changelog">

		<div class="feature-section">

			<img src="<?php echo FOOGALLERY_URL . 'assets/screenshots/admin-extensions.jpg'; ?>" class="foogallery-help-screenshot"/>

			<h2><?php _e( 'Create Your Own Extensions', 'foogallery' );?></h2>

			<h4><?php _e( 'Easy To Code','foogallery' );?></h4>
			<p><?php _e( 'We have done all the hard work to make your life easier. Creating an extension for FooGallery can be done in a couple lines of code.', 'foogallery' );?></p>

			<h4><?php _e( 'Actions and Filters', 'foogallery' );?></h4>
			<p><?php _e( 'We coded FooGallery with extensibility in mind. There are hundreds of actions and filters and helper functions to change every aspect of the plugin.', 'foogallery' );?></p>

			<h4><?php _e( 'Host Anywhere', 'foogallery' );?></h4>
			<p><?php _e( 'Host your extensions on the WordPress.org plugin repo, or GitHub, or even in your own Amazon S3 bucket. You have the power and choice!', 'foogallery' );?></p>

		</div>
	</div>

	<?php do_action('foogallery_admin_help_after_section_three'); ?>

</div>