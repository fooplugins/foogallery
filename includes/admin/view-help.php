<?php
$instance = FooGallery_Plugin::get_instance();
$info = $instance->get_plugin_info();

$logo = FOOGALLERY_URL . 'assets/logo.png?v=2';

$plugin_name = foogallery_plugin_name();
$fooplugins_url = foogallery_admin_url( 'https://fooplugins.com/', 'help' );
$plugin_url = foogallery_admin_url( 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/', 'help' );
$support_url = foogallery_admin_url( 'https://fooplugins.link/support/', 'help' );
$plans_url = foogallery_admin_url( 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/pricing/#plans', 'help' );
$support_link = sprintf( '<a href="%s" target="_blank">%s</a>', $support_url, __( 'open a support ticket', 'foogallery' ) );
$support_text = sprintf( __('Still stuck? Please %s and we will help!', 'foogallery'), $support_link );

$fooplugins_link = sprintf( '<a href="%s" target="_blank">%s</a>', $fooplugins_url, __( 'FooPlugins', 'foogallery' ) );
$link = sprintf('<a href="%s" target="_blank">%s</a>', $plugin_url, sprintf( __( 'Visit the %s Homepage', 'foogallery' ), $plugin_name ) );
$tagline = sprintf( __( 'Thank you for choosing %s!<br />Better galleries for WordPress, that are faster, more flexible and beautiful!', 'foogallery' ), $plugin_name );

$made_by = __( 'Made with ❤️ by %s', 'foogallery' );
$footer_text = sprintf( $made_by, $fooplugins_link );

//allow the variables to be overwritten by other things!
$logo = apply_filters( 'foogallery_admin_help_logo_url', $logo );

$demos_created = foogallery_get_setting( 'demo_content' ) === 'on';

$fs_instance = foogallery_fs();
$foogallery_current_plan = $fs_instance->get_plan_name();
$is_free = $fs_instance->is_free_plan();
$is_trial = $fs_instance->is_trial();
$show_trial_message = !$is_trial && $is_free && !$fs_instance->is_trial_utilized();
$show_thanks_for_pro = foogallery_is_pro();

$upgrade_tab_text = __( 'Upgrade to PRO', 'foogallery' );
$upgrade_button_text = __( 'Upgrade to PRO!', 'foogallery' );

if ( $show_thanks_for_pro ) {
	$upgrade_tab_text = __( 'PRO Features', 'foogallery' );
} else if ( $show_trial_message ) {
	$upgrade_tab_text = __( 'Free Trial', 'foogallery' );
	$upgrade_button_text = __( 'Already convinced? Upgrade to PRO!', 'foogallery' );
}

$show_demos = apply_filters( 'foogallery_admin_help_show_demos', true );
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$.foogallery_help_tabs = {

			init : function() {
				$(".foogallery-admin-help nav a").click( function(e) {
					e.preventDefault();

					$this = $(this);

					$this.addClass("foogallery-admin-help-tab-active");

					$(".foogallery-admin-help-tab-active").not($this).removeClass("foogallery-admin-help-tab-active");

					$(".foogallery-admin-help-section:visible").hide();

					var hash = $this.attr("href");

					$(hash+'_section').show();

					window.location.hash = hash;
				} );

				if (window.location.hash) {
					$('.foogallery-admin-help nav a[href="' + window.location.hash + '"]').click();
				}

				return false;
			}

		}; //End of foogallery_help_tabs

		$.foogallery_help_tabs.init();

		$.foogallery_import_data = {
			init : function() {
				$(".foogallery-admin-help-import-demos").click( function(e) {
					e.preventDefault();

					var $this = $(this),
						data = {
							'action': 'foogallery_admin_import_demos',
							'_wpnonce': $this.data( 'nonce' ),
							'_wp_http_referer': encodeURIComponent( $( 'input[name="_wp_http_referer"]' ).val() )
						};

					$this.prop('disable', true).addClass("foogallery-admin-help-loading");
					$('.fgah-create-demos-text').html( $this.data('working') );

					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: data,
						cache: false,
						success: function( html ) {
							$('.fgah-demo-result').html( html );
							$('.fgah-create-demos').hide();
							$('.fgah-created-demos').show();
						}
					}).always(function(){
						$this.removeClass("foogallery-admin-help-loading").prop('disable', false);
					});
				} );
			}
		};

		$.foogallery_import_data.init();

		$.foogallery_demos = {
			init : function() {
				$(".foogallery-admin-help-demo").click( function(e) {
					e.preventDefault();
					var $this = $(this),
						$content = $( $this.attr('href') );

					$('.foogallery-admin-help-demo').removeClass( 'foogallery-admin-help-button-active' );
					$this.addClass( 'foogallery-admin-help-button-active' );

					$('.foogallery-admin-help-demo-content').hide();
					$content.show();
				} );
			}
		};

		$.foogallery_demos.init();
	});
</script>
<style>
    <?php if ( $demos_created ) { ?>
    .fgah-create-demos {
	    display: none;
    }
	<?php } else { ?>
    .fgah-created-demos {
        display: none;
    }
	<?php } ?>
</style>
<div class="foogallery-admin-help">
	<div class="foogallery-admin-help-header">
		<div class="foogallery-admin-help-ribbon"><span><?php echo FOOGALLERY_VERSION; ?></span></div>
		<img src="<?php echo $logo; ?>" width="200">
	</div>
	<nav>
		<a class="foogallery-admin-help-tab-active" href="#help">
			<?php _e( 'Welcome', 'foogallery' ); ?>
		</a>
		<a href="#pro">
			<?php _e( $upgrade_tab_text, 'foogallery' ); ?>
		</a>
		<a href="#demos">
			<?php _e( 'Demo', 'foogallery' ); ?>
		</a>
		<a href="#support">
			<?php _e( 'Support', 'foogallery' ); ?>
		</a>
	</nav>
	<div class="foogallery-admin-help-content">

		<?php include FOOGALLERY_PATH . 'includes/admin/view-help-getting-started.php'; ?>

		<?php include FOOGALLERY_PATH . 'includes/admin/view-help-pro.php'; ?>

		<?php include FOOGALLERY_PATH . 'includes/admin/view-help-demos.php'; ?>

		<div id="support_section" class="foogallery-admin-help-section" style="display: none">
            <section class="fgah-feature">
                <header>
                    <h3><?php _e( '🚑 Need help? We\'re here for you...' , 'foogallery' );?></h3>
                </header>
                <ul class="fgah-help-list">
                    <li>
                        <a href="<?php echo esc_url( foogallery_admin_url( 'https://fooplugins.com/documentation/foogallery/', 'help') ); ?>" target="_blank"><?php _e('FooGallery Documentation','foogallery'); ?></a>
                        - <?php _e('Our documentation covers everything you need to know, from install instructions and account management, to troubleshooting common issues and extending the functionality.', 'foogallery'); ?>
                    </li>
	                <?php if ( $is_free ) { ?>
                    <li>
                        <a href="https://wordpress.org/support/plugin/foogallery/" target="_blank"><?php _e('FooGallery WordPress.org Support','foogallery'); ?></a>
                        - <?php _e('We actively monitor and answer all questions posted on WordPress.org for FooGallery.', 'foogallery'); ?>
                    </li>
	                <?php } else { ?>
		                <li>
			                <a href="<?php echo esc_url( $support_url ); ?>" target="_blank"><?php _e('Premium Support','foogallery'); ?></a>
			                - <?php _e('Open a support ticket and our dedicated support team will assist. This is the fasted way to get help!', 'foogallery'); ?>
		                </li>
	                <?php } ?>
                </ul>
            </section>
		</div>
	</div>
	<div class="foogallery-admin-help-footer">
		<?php echo $footer_text; ?>
	</div>
</div>