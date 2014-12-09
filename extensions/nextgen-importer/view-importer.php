<?php
$nextgen = new FooGallery_NextGen_Helper();
if ( isset( $_POST['foogallery_nextgen_reset'] ) ) {

	if ( check_admin_referer( 'foogallery_nextgen_import_reset', 'foogallery_nextgen_import_reset' ) ) {
		$nextgen->reset_import();
	}
}
?>
<style>
	.foo-nav-tabs a:focus {
		-webkit-box-shadow: none;
		box-shadow: none;
	}

	.spinner.shown {
		display: inline !important;
		margin: 0;
	}

	.nextgen-import-progress-not_started {
		color: #f00 !important;
	}

	.nextgen-import-progress-started {
		color: #f80 !important;
	}

	.nextgen-import-progress-completed {
		color: #080 !important;
	}

	.nextgen-import-progressbar {
		margin-top: 10px;
		display: inline-block;
		width: 500px;
		height: 10px;
		background: #ddd;
		position: relative;
	}

	.nextgen-import-progressbar span {
		position: absolute;
		height: 100%;
		left: 0;
		background: #888;
	}

	#nextgen_import_form .dashicons-arrow-right {
		font-size: 2em;
		margin-top: -0.2em;
	}

	.nextgen_import_container {
		margin-top: 10px;
	}
</style>
<script>
	jQuery(function ($) {

		function nextgen_ajax(action, success_callback) {
			var data = jQuery("#nextgen_import_form").serialize();

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data + "&action=" + action,
				success: success_callback,
				error: function() {
					//something went wrong! Alert the user and reload the page
					alert('<?php _e( 'Something went wrong with the import and the page will now reload. Once it has reloaded, click "Resume Import" to continue with the import.', 'foogallery' ); ?>');
					location.reload();
				}
			});
		}

		function nextgen_import_continue(dont_check_progress) {
			nextgen_ajax('foogallery_nextgen_import_refresh', function (data) {
				$('#nextgen_import_form').html(data);

				if (dont_check_progress != true) {
					//check if we need to carry on polling
					var percentage = parseInt($('#nextgen_import_progress').val());
					if (percentage < 100) {
						nextgen_import_continue();
					} else {
						nextgen_import_continue(true);
					}
				}
			});
		}

		$('#nextgen_import_form').on('click', '.start_import', function (e) {
			e.preventDefault();

			//show the spinner
			$('#nextgen_import_form .button').hide();
			$('#import_spinner .spinner').show();

			nextgen_ajax('foogallery_nextgen_import', function (data) {
				$('#nextgen_import_form').html(data);
				nextgen_import_continue();
			});
		});

		$('#nextgen_import_form').on('click', '.continue_import', function (e) {
			e.preventDefault();
			nextgen_import_continue();
		});

		$('#nextgen_import_form').on('click', '.cancel_import', function (e) {
			if (!confirm('<?php _e( 'Are you sure you want to cancel?', 'foogallery' ); ?>')) {
				e.preventDefault();
				return false;
			}
		});

		$('#nextgen_import_form').on('click', '.reset_import', function (e) {
			if (!confirm('<?php _e( 'Are you sure you want to reset all NextGen import data? This may result in duplicate galleries and media attachments!', 'foogallery' ); ?>')) {
				e.preventDefault();
				return false;
			}
		});

		$('.foo-nav-tabs').on('click', 'a', function (e) {
			$('.nextgen_import_container').hide();
			var tab = $(this).data('tab');
			$('#' + tab).show();
			$('.nav-tab').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
		});
	});
</script>
<div class="wrap about-wrap">
	<h2><?php _e( 'NextGen Gallery And Album Importer', 'foogallery' ); ?></h2>

	<div class="foogallery-help">
		<?php printf( __( 'Choose the NextGen galleries and albums you want to import into %s.', 'foogallery' ), foogallery_plugin_name() ); ?><br />
		<?php _e('Please note: importing large galleries with lots of images can take a while!', 'foogallery' ); ?>
	</div>

	<h2 class="foo-nav-tabs nav-tab-wrapper">
		<a href="#galleries" data-tab="nextgen_import_galleries" class="nav-tab nav-tab-active"><?php _e('Galleries', 'foogallery'); ?></a>
		<a href="#albums" data-tab="nextgen_import_albums" class="nav-tab"><?php _e('Albums', 'foogallery'); ?></a>
	</h2>

	<div class="nextgen_import_container" id="nextgen_import_galleries">
	<?php
	$galleries = $nextgen->get_galleries();
	if ( ! $galleries ) {
		_e( 'There are no NextGen galleries to import!', 'foogallery' );
	} else { ?>
		<form id="nextgen_import_form" method="POST">
			<?php $nextgen->render_import_form( $galleries ); ?>
		</form>
	<?php } ?>
	</div>
	<div class="nextgen_import_container" id="nextgen_import_albums" style="display: none">
	<?php
	$albums = $nextgen->get_albums();
	if ( ! $albums ) {
		_e( 'There are no NextGen albums to import!', 'foogallery' );
	} else { ?>
		<form id="nextgen_import_album_form" method="POST">
			<?php $nextgen->render_album_import_form( $albums ); ?>
		</form>
	<?php } ?>
	</div>
</div>
