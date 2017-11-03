<?php
$instance = FooGallery_Plugin::get_instance();
$info = $instance->get_plugin_info();
$title = apply_filters( 'foogallery_admin_help_title', sprintf( __( 'Welcome to %s %s', 'foogallery' ), foogallery_plugin_name(), $info['version'] ) );
$tagline = apply_filters( 'foogallery_admin_help_tagline', sprintf( __( 'Thank you for choosing %s, the most intuitive and extensible gallery creation and management tool ever created for WordPress!', 'foogallery' ), foogallery_plugin_name() ) );
$link = apply_filters( 'foogallery_admin_help_tagline_link', ' - <a href="http://foo.gallery" target="_blank">' . __( 'Visit our homepage', 'foogallery' ) . '</a>' );
$show_foobot = apply_filters( 'foogallery_admin_show_foobot', true );
$show_tabs = apply_filters( 'foogallery_admin_help_show_tabs', true );
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
		background:url(<?php echo FOOGALLERY_URL; ?>assets/logo.png) no-repeat;
		width:200px;
		height:200px;
	}
	.feature-section h2 {
		margin-top: 0;
	}

	.about-wrap h2.nav-tab-wrapper {
		margin-bottom: 20px;
	}

</style>
<div class="wrap about-wrap">
	<h1><?php echo $title; ?></h1>
	<div class="about-text">
		<?php echo $tagline. $link; ?>
	</div>
	<?php if ( $show_foobot ) { ?>
	<div class="foogallery-badge-foobot"></div>
	<?php } ?>
	<?php if ( $show_tabs ) { ?>
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" href="#">
			<?php _e( 'Getting Started', 'foogallery' ); ?>
		</a>
		<a class="nav-tab" href="<?php echo esc_url( foogallery_admin_extensions_url() ); ?>">
			<?php _e( 'Extensions', 'foogallery' ); ?>
		</a>
		<a class="nav-tab" href="http://fooplugins.com">
			<?php _e( 'Other Plugins', 'foogallery' ); ?>
		</a>
		<?php if ( current_user_can( 'activate_plugins' ) ) { ?>
		<a class="nav-tab" href="<?php echo esc_url( foogallery_admin_systeminfo_url() ); ?>">
			<?php _e( 'System Info', 'foogallery' ); ?>
		</a>
		<?php } ?>
	</h2>
	<?php } else { ?><hr /><?php } ?>
	<div class="changelog">

		<div class="feature-section">

			<img src="<?php echo FOOGALLERY_URL . 'assets/screenshots/admin-edit-gallery.jpg'; ?>" class="foogallery-help-screenshot"/>

			<h2><?php _e( 'Creating Your First Gallery', 'foogallery' );?></h2>

			<h4><?php printf( __( '1. <a href="%s">Galleries &rarr; Add New</a>', 'foogallery' ), esc_url ( admin_url( 'post-new.php?post_type=foogallery' ) ) ); ?></h4>
			<p><?php _e( 'To create your first gallery, simply click the Add New button or click the Add Gallery link in the menu.', 'foogallery' ); ?></p>

			<h4><?php _e( '2. Add Media', 'foogallery' );?></h4>
			<p><?php _e( 'Click the Add Media button and choose images from the media library to include in your gallery.', 'foogallery' );?></p>

			<h4><?php _e( '3. Choose a Template', 'foogallery' );?></h4>
			<p><?php _e( 'We have loads of awesome built-in gallery templates to choose from.', 'foogallery' );?></p>

			<h4><?php _e( '4. Adjust Your Settings', 'foogallery' );?></h4>
			<p><?php _e( 'There are tons of settings to help you customize the gallery to suit your needs.', 'foogallery' );?></p>
		</div>
	</div>

	<?php do_action( 'foogallery_admin_help_after_section_one' ); ?>

	<div class="changelog">

		<div class="feature-section">
			<img src="<?php echo FOOGALLERY_URL . 'assets/screenshots/admin-insert-shortcode.jpg'; ?>" class="foogallery-help-screenshot"/>

			<h2><?php _e( 'Show Off Your Gallery', 'foogallery' );?></h2>

			<h4><?php printf( __( 'The <em>[%s]</em> Short Code','foogallery' ), foogallery_gallery_shortcode_tag() );?></h4>
			<p><?php _e( 'Simply copy the shortcode code from the gallery listing page and paste it into your posts or pages.', 'foogallery' );?></p>

			<h4><?php _e( 'Visual Editor Button', 'foogallery' );?></h4>
			<p><?php printf( __( 'Or to make life even easier, you can insert a gallery using the Add %s button inside the WordPress visual editor.', 'foogallery' ), foogallery_plugin_name() );?></p>

			<h4><?php _e( 'Copy To Clipboard','foogallery' );?></h4>
			<p><?php _e( 'We make your life easy! Just click the shortcodes and they get copied to your clipboard automatically. ', 'foogallery' );?></p>

		</div>
	</div>

	<?php do_action( 'foogallery_admin_help_after_section_two' ); ?>

</div>
