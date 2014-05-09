<?php
$instance = FooGallery_Plugin::get_instance();
$info = $instance->get_plugin_info();
?>
<style>
	.foogallery-badge-foobot {
		position: absolute;
		top: 15px;
		right: 0;
		background:url(<?php echo FOOGALLERY_URL; ?>assets/foobot.png) no-repeat;
		width:109px;
		height:200px;
	}

	.foogallery-extension-browser {
		margin-top: 20px;
	}

		.foogallery-extension-browser .extensions .extension {
			float: left;
			margin: 0 4% 4% 0;
			position: relative;
			width: 30.6%;
			border: 1px solid #DEDEDE;
			-webkit-box-shadow: 0 1px 1px -1px rgba(0, 0, 0, 0.1);
			box-shadow: 0 1px 1px -1px rgba(0, 0, 0, 0.1);
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
		}

			.foogallery-extension-browser .extensions .extension h3 {
				font-size: 15px;
				font-weight: 600;
				height: 18px;
				line-height: 18px;
				margin: 0;
				padding: 15px;
				-webkit-box-shadow: inset 0 1px 0 rgba(0, 0, 0, 0.1);
				box-shadow: inset 0 1px 0 rgba(0, 0, 0, 0.1);
				overflow: hidden;
				white-space: nowrap;
				text-overflow: ellipsis;
				background: #FFF;
				background: rgba(255, 255, 255, 0.65);
			}

				.foogallery-extension-browser .extensions .extension h3 .new {
					background: #D54E21;
					background: rgba(213, 78, 33, 0.95);
					-webkit-border-radius: 3px;
					border-radius: 3px;
					padding:2px 5px;
					color:#fff;
					font-size: 10px;
					margin-left: 5px;
					margin-bottom: 3px;
				}

			.foogallery-extension-browser .extensions .extension .screenshot {
				-webkit-transform: translateZ(0);
				-webkit-transition: opacity .2s ease-in-out;
				transition: opacity .2s ease-in-out;
			}

				.foogallery-extension-browser .extensions .extension:hover .screenshot {
					opacity: .4;
				}

			.foogallery-extension-browser .extensions .extension .extension-actions {
				-webkit-transition: opacity .1s ease-in-out;
				transition: opacity .1s ease-in-out;
				position: absolute;
				bottom: 0;
				right: 0;
				height: 38px;
				padding: 9px 10px 0;
			}

			.foogallery-extension-browser .extensions .extension .extension-details {
				-ms-filter: "alpha(Opacity=0)";
				opacity: 0;
				position: absolute;
				top: 25%;
				right: 20%;
				left: 20%;
				background: #222;
				background: rgba(0, 0, 0, 0.7);
				color: #FFF;
				font-size: 15px;
				text-shadow: 0 1px 0 rgba(0, 0, 0, 0.6);
				-webkit-font-smoothing: antialiased;
				font-weight: 600;
				padding: 15px 12px;
				text-align: center;
				-webkit-border-radius: 3px;
				border-radius: 3px;
				-webkit-transition: opacity .1s ease-in-out;
				transition: opacity .1s ease-in-out;
			}

				.foogallery-extension-browser .extensions .extension:hover .extension-details {
					-ms-filter: "alpha(Opacity=1)";
					opacity: 1;
				}

			.foogallery-extension-browser .extensions .extension.active .banner {
				background: #0074A2;
				color: #FFF;
				display: block;
				font-size: 13px;
				font-weight: 400;
				height: 48px;
				line-height: 48px;
				padding: 0 10px 0 40px;
				position: absolute;
				top: 0;
				right: 0;
				left: 0;
				border-bottom: 1px solid rgba(0, 0, 0, 0.25);
				overflow: hidden;
			}

				.foogallery-extension-browser .extensions .extension.active .banner:before {
					content: "\f147";
					display: inline-block;
					font: 400 40px/1 dashicons;
					top: 5px;
					left: 0;
					position: absolute;
					speak: none;
					-webkit-font-smoothing: antialiased;
				}

			.foogallery-extension-browser .extensions .extension.active .activate {
				display: none;
			}

			.foogallery-extension-browser .extensions .extension .deactivate {
				display: none;
			}

			.foogallery-extension-browser .extensions .extension.active .deactivate {
				display: block;
			}
</style>
<div class="wrap about-wrap">
	<h1><?php _e( 'FooGallery Extensions', 'foogallery' ); ?></h1>

	<div class="about-text"><?php _e( 'Extensions make FooGallery even more awesome, without bloating the core plugin.', 'foogallery' ); ?></div>
	<div class="foogallery-badge-foobot"></div>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab" href="#">
			<?php _e( "Getting Started", 'foogallery' ); ?>
		</a>
		<a class="nav-tab nav-tab-active" href="<?php echo foogallery_admin_extensions_url(); ?>">
			<?php _e( 'Extensions', 'foogallery' ); ?>
		</a>
		<a class="nav-tab" href="#other">
			<?php _e( 'Other Plugins', 'foogallery' ); ?>
		</a>
	</h2>

	<div class="foogallery-extension-browser">
		<div class="extensions">

			<div class="extension active">

				<div class="screenshot">
					<img src="http://wpfoo/wp-content/themes/stargazer/screenshot.png" alt="">
				</div>

				<div class="extension-details">
					<p>Some awesome description that means something</p>
					<a href="#">By FooPlugins</a>
				</div>

				<h3>Albums</h3>


				<div class="extension-actions">

					<a class="button button-primary activate" href="#">Activate</a>
					<a class="button button-secondary deactivate" href="#">Deactivate</a>

				</div>


				<div class="banner">Activated</div>

			</div>

			<div class="extension">

				<div class="screenshot">
					<img src="http://wpfoo/wp-content/themes/stargazer/screenshot.png" alt="">
				</div>

				<div class="extension-details">
					<p>Enable the FooBox lightbox</p>
					<a href="#">By FooPlugins</a>
				</div>

				<h3>FooBox<span class="new">New!</span></h3>



				<div class="extension-actions">

					<a class="button button-primary activate" href="#">Activate</a>
					<a class="button button-secondary deactivate" href="#">Deactivate</a>

				</div>

			</div>
		</div>

	</div>

</div>