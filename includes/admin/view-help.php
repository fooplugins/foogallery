<?php
$instance = FooGallery_Plugin::get_instance();
$info = $instance->get_plugin_info();

$logo = FOOGALLERY_URL . 'assets/logo.png';
$loader = FOOGALLERY_URL . 'assets/loader.gif';
$plugin_name = foogallery_plugin_name();
$fooplugins_url = foogallery_admin_url( 'https://fooplugins.com/', 'help' );
$plugin_url = foogallery_admin_url( 'https://fooplugins.com/foogallery/', 'help' );
$support_url = foogallery_admin_url( 'https://fooplugins.link/support/', 'help' );

$fooplugins_link = sprintf( '<a href="%s" target="_blank">%s</a>', $fooplugins_url, __( 'FooPlugins', 'foogallery' ) );

$link = sprintf('<a href="%s" target="_blank">%s</a>', $plugin_url, sprintf( __( 'Visit the %s Homepage', 'foogallery' ), $plugin_name ) );
$tagline = sprintf( __( 'Thank you for choosing %s!<br />Easily create better galleries for WordPress, which are faster, more flexible and beautiful!', 'foogallery' ), $plugin_name );

$made_by = __( 'Made with ❤️ by %s', 'foogallery' );
$footer_text = sprintf( $made_by, $fooplugins_link );

//allow the variables to be overwritten by other things!
$logo = apply_filters( 'foogallery_admin_help_logo_url', $logo );

$demos_created = foogallery_get_setting( 'demo_content' ) === 'on';

$fs_instance = foogallery_fs();
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

					if ( confirm('Are you sure you want to create demo galleries? Images will be imported into your media library') ) {
						var $this = $(this),
							data = {
								'action': 'foogallery_admin_import_demos',
								'_wpnonce': $this.data( 'nonce' ),
								'_wp_http_referer': encodeURIComponent( $( 'input[name="_wp_http_referer"]' ).val() )
							};

						$this.addClass("foogallery-admin-help-loading").removeAttr('href');

						$.ajax({
							type: 'POST',
							url: ajaxurl,
							data: data,
							cache: false,
							success: function( html ) {
								alert( html );
								$('.foogallery-admin-help-create-demos').hide();
								$('.foogallery-admin-help-created-demos').show();
							}
						}).always(function(){
							$this.removeClass("foogallery-admin-help-loading").attr('href', '#demo_content');
						});
					}
				} );
			}
		};

		$.foogallery_import_data.init();

		$.foogallery_demos = {
			init : function() {
				$(".foogallery-admin-help-demo").click( function(e) {
					e.preventDefault();

					var $this = $(this),
						demo_id = $this.data('foogallery-admin-help-demo'),
						data = {
							'action': 'foogallery_admin_help_demo',
							'demo': demo_id,
							'_wpnonce': $( '#foogallery_help_demo_nonce' ).val(),
							'_wp_http_referer': encodeURIComponent( $( 'input[name="_wp_http_referer"]' ).val() )
						};

					$this.addClass("foogallery-admin-help-loading").removeAttr('href');

					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: data,
						cache: false,
						success: function( html ) {
							FooBar.dismissAll(true);

							//remove all foobars from the page
							$('.foobar').remove();

							var $html = $(html);

							//append the bar content to end of body
							$( 'body' ).append( $html );

							//init the bar
							const bar = FooBar.create( $html.attr('id') );
							if ( bar instanceof FooBar.Bar ) {
								bar.init();
							}
						}
					}).always(function(){
						$this.removeClass("foogallery-admin-help-loading").attr('href', '#demo');
					});
				} );
			}
		};

		$.foogallery_demos.init();
	});
</script>
<style>
    body {
        background-color: #484c50;
    }
    #wpcontent {
        padding-right: 20px;
    }
    @media screen and (max-width: 782px){
        .auto-fold #wpcontent {
            padding-right: 10px;
        }
    }

    .foogallery-admin-help {
        max-width: 1000px;
        margin: 24px auto;
        clear: both;
        background-color: #23282d;
        border-radius: 20px;
        color: #ffffff;
    }

    .foogallery-admin-help h2,
    .foogallery-admin-help h3,
    .foogallery-admin-help h4 {
        color: inherit;
    }

    .foogallery-admin-help a {
        color: #35beff;
        text-decoration: none;
    }
    .foogallery-admin-help a:hover {
        color: #0097de;
    }
    .foogallery-admin-help a:focus {
        box-shadow: none;
    }

    .foogallery-admin-help-header {
        margin: 0;
        color: #FFFFFF;
        position: relative;
        text-align: center;
        padding: 20px;
    }
    .foogallery-admin-help-header > img {
        max-width: 100%;
        height: auto;
        margin: 3em 0;
        box-sizing: border-box;
    }

    .foogallery-admin-help-tagline {
        margin: 0;
        padding: 10px;
        font-size: 1.5em;
    }

    .foogallery-admin-help-ribbon {
        position: absolute;
        right: -5px;
        top: -5px;
        z-index: 1;
        overflow: hidden;
        width: 75px;
        height: 75px;
        text-align: right;
    }
    .foogallery-admin-help-ribbon span {
        font-size: 10px;
        font-weight: 600;
        color: #2b2400;
        text-transform: uppercase;
        text-align: center;
        line-height: 20px;
        transform: rotate(45deg);
        width: 100px;
        display: block;
        background: #d67935;
        box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 1);
        position: absolute;
        top: 19px; right: -21px;
    }
    .foogallery-admin-help-ribbon span::before {
        content: "";
        position: absolute;
        left: 0;
        top: 100%;
        z-index: -1;
        border-left: 3px solid #d67935;
        border-right: 3px solid transparent;
        border-bottom: 3px solid transparent;
        border-top: 3px solid #d67935;
    }
    .foogallery-admin-help-ribbon span::after {
        content: "";
        position: absolute;
        right: 0;
        top: 100%;
        z-index: -1;
        border-left: 3px solid transparent;
        border-right: 3px solid #d67935;
        border-bottom: 3px solid transparent;
        border-top: 3px solid #d67935;
    }

    .foogallery-admin-help nav {
        background: #32373c;
        clear: both;
        padding-top: 0;
        color: #0097de;
        display: flex;
    }

    .foogallery-admin-help nav a {
        margin-left: 0;
        padding: 24px 32px 18px 32px;
        font-size: 1.3em;
        line-height: 1;
        border-width: 0 0 6px;
        border-style: solid;
        border-color: transparent;
        background: transparent;
        color: inherit;
        text-decoration: none;
        font-weight: 600;
        box-shadow: none;
    }

    .foogallery-admin-help nav a:hover {
        background-color: #0073aa;
        color: #ffffff;
        border-width: 0;
    }

    .foogallery-admin-help nav a.foogallery-admin-help-tab-active {
        background-color: #0073aa;
        color: #ffffff;
        border-color: #ffffff;
    }

    .foogallery-admin-help-section {
    }

    .foogallery-admin-help-centered {
        text-align: center;
    }

    .foogallery-admin-help-section .foogallery-admin-help-section-feature {
        margin: 32px;
    }

    .foogallery-admin-help-section .foogallery-admin-help-section-feature h2 {
        text-align: center;
        font-size: 1.6em;
        margin: 0;
        padding: 20px 0;
        font-weight: 600;
    }

    .foogallery-admin-help-section .foogallery-admin-help-section-feature .foogallery-admin-help-2-columns {
        display: -ms-grid;
        display: grid;
        grid-template-columns: 1fr 2fr;
    }

    .foogallery-admin-help-section .foogallery-admin-help-section-feature .foogallery-admin-help-2-columns .foogallery-admin-help-column {
        padding: 20px;
    }

    .foogallery-admin-help-section .foogallery-admin-help-section-feature .foogallery-admin-help-2-columns .foogallery-admin-help-column h2 {
	    text-align: left;
    }

    .foogallery-admin-help-button-cta {
        background: #0073aa;
        color: #ffffff !important;
        padding: 12px 36px;
        font-size: 1.3em;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
        min-width: 250px;
    }
    .foogallery-admin-help-button-cta:hover {
        background: #016b99;
    }

    .foogallery-admin-help-button-cta.foogallery-admin-help-loading {
        position: relative;
        cursor: wait;
    }

    .foogallery-admin-help-button-cta.foogallery-admin-help-loading:before {
        content: '';
        background: url('<?php echo $loader ?>') no-repeat;
        background-size: 20px 20px;
        display: inline-block;
        opacity: 0.7;
        filter: alpha(opacity=70);
        width: 20px;
        height: 20px;
        border: none;
        position: absolute;
        top: 50%;
        right: 8px;
        transform: translateY(-50%);
    }

    .foogallery-admin-help-footer {
        margin: 0;
        color: #ffffff;
        text-align: center;
        padding: 20px;
        font-size: 1.3em;
    }

    .foogallery-admin-help-column .foogallery-admin-help-button-cta {
        min-width: auto;
        padding: 12px 24px;
    }

    <?php if ( $demos_created ) { ?>
    .foogallery-admin-help-create-demos {
	    display: none;
    }
	<?php } else { ?>
    .foogallery-admin-help-created-demos {
        display: none;
    }
	<?php } ?>

</style>
<div class="foogallery-admin-help">
	<div class="foogallery-admin-help-header">
		<div class="foogallery-admin-help-ribbon"><span><?php echo FOOGALLERY_VERSION; ?></span></div>
		<img src="<?php echo $logo; ?>" width="200" height="200">
		<p class="foogallery-admin-help-tagline"><?php echo $tagline; ?></p>
		<p class="foogallery-admin-help-tagline"><?php echo $link; ?></p>
	</div>
	<nav>
		<a class="foogallery-admin-help-tab-active" href="#help">
			<?php _e( 'Getting Started', 'foogallery' ); ?>
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
			<div class="foogallery-admin-help-section-feature">
				<h2><?php _e( '🚑 Need help? We\'re here for you...' , 'foogallery' );?></h2>

				<p>
					<span class="dashicons dashicons-editor-help"></span>
					<a href="<? echo esc_url( foogallery_admin_url( 'https://fooplugins.com/documentation/foogallery/', 'help') ); ?>" target="_blank"><?php _e('FooGallery Documentation','foogallery'); ?></a>
					- <?php _e('Our documentation covers everything you need to know, from install instructions and account management, to troubleshooting common issues and extending the functionality.', 'foogallery'); ?></p>

				<p><span class="dashicons dashicons-editor-help"></span><a href="https://wordpress.org/support/plugin/foogallery/" target="_blank"><?php _e('FooGallery WordPress.org Support','foogallery'); ?></a> - <?php _e('We actively monitor and answer all questions posted on WordPress.org for FooGallery.', 'foogallery'); ?></p>

				<div class="feature-cta">
					<p>
						<?php _e('Still stuck? Please open a support ticket and we will help:', 'foogallery'); ?>
						<a target="_blank" href="<?php echo esc_url ( $support_url ); ?>"><?php _e('Open a support ticket', 'fooplugins' ); ?></a>
					</p>
				</div>
			</div>
		</div>
	</div>
	<div class="foogallery-admin-help-footer">
		<?php echo $footer_text; ?>
	</div>
</div>