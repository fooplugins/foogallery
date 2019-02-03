<?php
$instance = FooGallery_Plugin::get_instance();
$info = $instance->get_plugin_info();
$title = apply_filters( 'foogallery_admin_help_title', sprintf( __( 'Welcome to %s %s', 'foogallery' ), foogallery_plugin_name(), $info['version'] ) );
$tagline = apply_filters( 'foogallery_admin_help_tagline', sprintf( __( 'Thank you for choosing %s, the most intuitive and extensible gallery creation and management tool ever created for WordPress!', 'foogallery' ), foogallery_plugin_name() ) );
$link = apply_filters( 'foogallery_admin_help_tagline_link', ' - <a href="https://foo.gallery?utm_source=foogallery_plugin_help" target="_blank">' . __( 'Visit our homepage', 'foogallery' ) . '</a>' );
$show_logo = apply_filters( 'foogallery_admin_help_show_logo', true );
$show_tabs = apply_filters( 'foogallery_admin_help_show_tabs', true );
$demo_link = '<a href="https://foo.gallery/demos?utm_source=foogallery_plugin_help" target="_blank">' . __( 'gallery demos', 'foogallery' ) . '</a>';
$support_url = 'https://fooplugins.link/support?utm_source=foogallery_plugin_help_support';

$fs_instance = freemius( FOOGALLERY_SLUG );
$show_upgrade = $fs_instance->is_free_plan();
$show_trial_message = !$fs_instance->is_trial_utilized();
$upgrade_tab_text = __( 'Upgrade to PRO', 'foogallery' );
$upgrade_button_text = __( 'Upgrade to PRO!', 'foogallery' );
if ( $show_trial_message ) {
	$upgrade_tab_text = __( 'Free Trial', 'foogallery' );
	$upgrade_button_text = __( 'Already convinced? Upgrade to PRO!', 'foogallery' );
}

$show_demos = apply_filters( 'foogallery_admin_help_show_demos', true );
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$.admin_tabs = {

			init : function() {
				$("a.nav-tab").click( function(e) {
					e.preventDefault();

					$this = $(this);

					$this.parents(".nav-tab-wrapper:first").find(".nav-tab-active").removeClass("nav-tab-active");
					$this.addClass("nav-tab-active");

					$(".nav-container:visible").hide();

					var hash = $this.attr("href");

					$(hash+'_section').show();

					window.location.hash = hash;
				});

				if (window.location.hash) {
					$('a.nav-tab[href="' + window.location.hash + '"]').click();
				}

				return false;
			}

		}; //End of admin_tabs

		$.admin_tabs.init();
	});
</script>
<style>
	.about-wrap img.foogallery-help-screenshot {
		float:right;
		margin-left: 20px;
		width: inherit;
	}

	.foogallery-badge-logo {
		position: absolute;
		top: 15px;
		right: 0;
		background:url(<?php echo FOOGALLERY_URL; ?>assets/logo.png) no-repeat;
		width:200px;
		height:200px;
	}

	.about-wrap h2.nav-tab-wrapper {
		margin-bottom: 20px;
	}

	.foogallery-tip {
		position: relative;
		display: block;
		line-height: 19px;
		padding: 15px 10px 15px 50px;
		font-size: 14px;
		text-align: left;
		margin: 5px 0 0 2px;
		background-color: #1e8cbe;
		border-radius: 3px;
		-webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
		box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
		color: #fff;
	}

	.foogallery-tip a {
		color: #a4f2ff;
		font-weight: bold;
	}

	.foogallery-tip:before {
		content: "\f348";
		font: 400 30px/1 dashicons !important;
		speak: none;
		color: #fff;
		display: inline-block;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
		vertical-align: bottom;
		position: absolute;
		left: 10px;
		margin-top: -15px;
		top: 50%;
		height: 1em;
	}

	.feature-cta {
		text-align: center;
		display: block;
		padding: 20px;
	}

	.feature-cta a {
		background: #0085ba;
		border-color: #0073aa #006799 #006799;
		-webkit-box-shadow: 0 1px 0 #006799;
		box-shadow: 0 1px 0 #006799;
		color: #fff;
		text-decoration: none;
		text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799;
		padding: 5px 20px;
		border-radius: 3px;
	}

	.feature-section .dashicons {
		font-size: 1.8em;
		padding-right: 10px;
	}

	#freetrial_section .dashicons {
		color: green;
	}

	#demos_section .dashicons {
		color: #006799;
	}

	#support_section .dashicons {
		color: #006799;
	}

	#demos_section {
		text-align: center;
	}

</style>
<div class="wrap about-wrap">
	<h1><?php echo $title; ?></h1>
	<div class="about-text">
		<?php echo $tagline. $link; ?>
	</div>
	<?php if ( $show_logo ) { ?>
	<div class="foogallery-badge-logo"></div>
	<?php } ?>
	<?php if ( $show_tabs ) { ?>
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" href="#help">
			<?php _e( 'Getting Started', 'foogallery' ); ?>
		</a>
		<?php if ( $show_upgrade ) { ?>
		<a class="nav-tab" href="#freetrial">
			<?php _e( $upgrade_tab_text, 'foogallery' ); ?>
		</a>
		<?php } ?>
		<?php if ( $show_demos ) { ?>
			<a class="nav-tab" href="#demos">
				<?php _e( 'Demos', 'foogallery' ); ?>
			</a>
		<?php } ?>
		<a class="nav-tab" href="#support">
			<?php _e( 'Support', 'foogallery' ); ?>
		</a>
	</h2>
	<?php } else { ?><hr /><?php } ?>
	<div id="help_section" class="feature-section nav-container">
		<div class="changelog section-getting-started">
			<div class="feature-section">
				<h2><?php _e( 'Creating Your First Gallery', 'foogallery' );?></h2>

				<img src="https://s3.amazonaws.com/foocdn/foogallery/admin-edit-gallery.jpg" class="foogallery-help-screenshot"/>

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

		<div class="changelog section-getting-started">
			<div class="foogallery-tip">
				<?php printf( __( 'Not sure which gallery template to use? Check out all our different %s.', 'foogallery' ), $demo_link ); ?>
			</div>
		</div>

		<?php do_action( 'foogallery_admin_help_after_section_one' ); ?>

		<div class="changelog section-getting-started">
			<div class="feature-section">
				<h2><?php _e( 'Show Off Your Gallery', 'foogallery' );?></h2>

				<img src="https://s3.amazonaws.com/foocdn/foogallery/admin-insert-shortcode.jpg" class="foogallery-help-screenshot"/>

				<h4><?php printf( __( 'Gutenberg Editor','foogallery' ), foogallery_gallery_shortcode_tag() );?></h4>
				<p><?php _e( 'Use the new block directly in the new visual editor that comes standard in WordPress 5.', 'foogallery' );?></p>

				<h4><?php printf( __( 'The <em>[%s]</em> Short Code','foogallery' ), foogallery_gallery_shortcode_tag() );?></h4>
				<p><?php _e( 'Simply copy the shortcode code from the gallery listing page and paste it into your posts or pages.', 'foogallery' );?></p>

				<h4><?php _e( 'Copy To Clipboard','foogallery' );?></h4>
				<p><?php _e( 'We make your life easy! Just click the shortcodes and they get copied to your clipboard automatically. ', 'foogallery' );?></p>

			</div>
		</div>

		<?php do_action( 'foogallery_admin_help_after_section_two' ); ?>
	</div>
	<div id="freetrial_section" class="feature-section nav-container" style="display: none">
		<?php if ( $show_trial_message ) { ?>
			<div>
				<h2><?php _e( 'FooGallery PRO Free Trial', 'foogallery' );?></h2>
				<p><?php _e( 'Want to test out all the PRO features? No problem! You can start a 7-day free trial immediately. No credit card is required!', 'foogallery' );?></p>
				<div class="feature-cta"><?php printf( '<a href="%s">%s</a>', esc_url ( foogallery_admin_freetrial_url() ), __( 'Start Your 7-day Free Trial', 'foogallery' ) ); ?></div>
			</div>
		<?php } ?>
		<h2><?php _e( 'FooGallery PRO Features', 'foogallery' );?></h2>
		<p><?php _e( 'Click on a link below to open a demo of the PRO feature in a new tab.', 'foogallery' );?></p>
		<?php foreach ( foogallery_marketing_pro_features() as $feature ) {
			?><p><span class="dashicons dashicons-yes"></span><strong><a href="<?php echo esc_url($feature['demo'] . '?utm_source=foogallery_plugin_help_features' ); ?>" target="_blank" title="<?php __('Open PRO demo in new tab','foogallery'); ?>"><?php echo $feature['feature']; ?></a></strong> - <?php echo $feature['desc']; ?></p><?php
		}?>
		<div class="feature-cta"><?php printf( '<a href="%s">%s</a>', esc_url ( foogallery_admin_pricing_url() ), $upgrade_button_text ); ?></div>
	</div>
	<div id="demos_section" class="feature-section nav-container" style="display: none">
		<h2><?php _e( 'FooGallery Demos', 'foogallery' );?></h2>
		<?php
		$demo_section = '';
		foreach ( foogallery_marketing_demos() as $demo ) {
			if ( $demo_section !== $demo['section'] ) {
				$demo_section = $demo['section'];
				echo '<h3>' . $demo_section . '</h3>';
			}
			?><p><span class="dashicons dashicons-format-image"></span><a href="<?php echo esc_url($demo['href'] . '?utm_source=foogallery_plugin_help_demos' ); ?>" target="_blank" title="<?php __('Open demo in new tab','foogallery'); ?>"><?php echo $demo['demo']; ?></a></p><?php
		}?>
	</div>
	<div id="support_section" class="feature-section nav-container" style="display: none">
		<h2><?php _e( 'Need help? We\'re here for you...' , 'foogallery' );?></h2>

		<p><span class="dashicons dashicons-editor-help"></span><a href="https://docs.fooplugins.com/" target="_blank"><?php _e('FooPlugins Knowledgebase','foogallery'); ?></a> - <?php _e('A collection of common scenarios and questions. The knowledgebase articles will help you troubleshoot issues that have previously been solved.', 'foogallery'); ?></p>

		<p><span class="dashicons dashicons-editor-help"></span><a href="https://wordpress.org/support/plugin/foogallery/" target="_blank"><?php _e('FooGallery WordPress.org Support','foogallery'); ?></a> - <?php _e('We actively monitor and answer all questions posted on WordPress.org for FooGallery.', 'foogallery'); ?></p>

		<div class="feature-cta">
			<p><?php _e('Still stuck? Please open a support ticket and we will help:', 'foogallery'); ?></p>
			<a target="_blank" href="<?php echo esc_url ( $support_url ); ?>"><?php _e('Open a support ticket', 'fooplugins' ); ?></a>
		</div>
	</div>
</div>
