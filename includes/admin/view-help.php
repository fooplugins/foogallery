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
</style>
<div class="wrap about-wrap">
	<h1><?php printf( __( 'Welcome to FooGallery %s', 'foogallery' ), $info['version'] ); ?></h1>
	<div class="about-text"><?php _e( 'Thank you for choosing FooGallery, the most intuitive gallery creation and management tool for WordPress!', 'foogallery' ); ?></div>
	<div class="foogallery-badge-foobot"></div>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" href="#">
			<?php _e( "Getting Started", 'foogallery' ); ?>
		</a>
		<a class="nav-tab" href="<?php echo foogallery_admin_extensions_url(); ?>">
			<?php _e( 'Extensions', 'foogallery' ); ?>
		</a>
		<a class="nav-tab" href="#other">
			<?php _e( 'Other Plugins', 'foogallery' ); ?>
		</a>
	</h2>

	<div class="changelog">

		<div class="feature-section">

			<h3><?php _e( 'Creating Your First Gallery', 'foogallery' );?></h3>

			<img src="<?php echo FOOGALLERY_URL . 'assets/screenshots/admin-edit-gallery.jpg'; ?>" class="foogallery-help-screenshot"/>

			<h4><?php printf( __( '<a href="%s">Galleries &rarr; Add New</a>', 'foogallery' ), admin_url( 'post-new.php?post_type=foogallery' ) ); ?></h4>
			<p><?php _e( 'To create your first gallery, simply click the Add New button or click the Add Gallery menu link. Then choose images from the media library to include in your gallery.', 'foogallery'); ?></p>

			<h4><?php _e( 'Drag and Drop Reordering', 'foogallery' );?></h4>
			<p><?php _e( 'Sort the images in your gallery simply by dragging them around.', 'foogallery' );?></p>

			<h4><?php _e( 'Gallery Settings', 'foogallery' );?></h4>
			<p><?php _e( 'Then choose how you want your gallery to look and function on the front-end. It could not be any easier!', 'foogallery' );?></p>

		</div>
	</div>


	<div class="changelog">
		<h3><?php _e( 'Showing Off Your Gallery', 'foogallery' );?></h3>

		<div class="feature-section">

			<img src="<?php echo FOOGALLERY_URL . 'assets/screenshots/admin-insert-gallery.jpg'; ?>" class="foogallery-help-screenshot"/>

			<h4><?php _e( 'The <em>[foogallery]</em> Short Code','foogallery' );?></h4>
			<p><?php _e( 'Simply copy the shortcode code from the gallery listing page and paste it into your posts or pages. ', 'foogallery' );?></p>

			<h4><?php _e( 'Visual Editor Button', 'foogallery' );?></h4>
			<p><?php _e( 'Or to make life even easier, you can insert a gallery using the Insert Gallery button inside the WordPress visual editor.', 'foogallery' );?></p>

		</div>
	</div>

	<div class="changelog">
		<h3><?php _e( 'Need Help?', 'foogallery' );?></h3>

		<div class="feature-section">

			<h4><?php _e( 'Phenomenal Support','foogallery' );?></h4>
			<p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, post a question in the <a href="https://easydigitaldownloads.com/support">support forums</a>.', 'foogallery' );?></p>

			<h4><?php _e( 'Need Even Faster Support?', 'foogallery' );?></h4>
			<p><?php _e( 'Our <a href="https://easydigitaldownloads.com/support/pricing/">Priority Support forums</a> are there for customers that need faster and/or more in-depth assistance.', 'foogallery' );?></p>

		</div>
	</div>

	<div class="changelog">
		<h3><?php _e( 'Stay Up to Date', 'foogallery' );?></h3>

		<div class="feature-section">

			<h4><?php _e( 'Get Notified of Extension Releases','foogallery' );?></h4>
			<p><?php _e( 'New extensions that make Easy Digital Downloads even more powerful are released nearly every single week. Subscribe to the newsletter to stay up to date with our latest releases. <a href="http://eepurl.com/kaerz" target="_blank">Signup now</a> to ensure you do not miss a release!', 'foogallery' );?></p>

			<h4><?php _e( 'Get Alerted About New Tutorials', 'foogallery' );?></h4>
			<p><?php _e( '<a href="http://eepurl.com/kaerz" target="_blank">Signup now</a> to hear about the latest tutorial releases that explain how to take Easy Digital Downloads further.', 'foogallery' );?></p>

		</div>
	</div>

</div>