<?php
$nextgen = new FooGallery_NextGen_Helper();
if ( isset( $_POST['foogallery_nextgen_reset'] ) ) {

	if ( check_admin_referer( 'foogallery_nextgen_import_reset', 'foogallery_nextgen_import_reset' ) ) {
		$nextgen->reset_import();
	}
}
?>
<style>
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
					alert('<?php _e('Something went wrong with the import and the page will now reload. Once it has reloaded, click "Resume Import" to continue with the import.', 'foogallery'); ?>');
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
			if (!confirm('<?php echo __('Are you sure you want to cancel?', 'foogallery'); ?>')) {
				e.preventDefault();
				return false;
			}
		});

		$('#nextgen_import_form').on('click', '.reset_import', function (e) {
			if (!confirm('<?php echo __('Are you sure you want to reset all NextGen import data? This may result in duplicate galleries and media attachments!', 'foogallery'); ?>')) {
				e.preventDefault();
				return false;
			}
		});
	});
</script>
<div class="wrap about-wrap">
	<h2><?php _e( 'NextGen Gallery Importer', 'foogallery' ); ?></h2>

	<div class="foogallery-help">
		<?php printf( __( 'Choose the NextGen galleries you want to import into %s. Please note that importing galleries with lots of images can take a while.', 'foogallery' ), foogallery_plugin_name() ); ?>
	</div>
	<?php
	$galleries = $nextgen->get_galleries();
	if ( !$galleries ) {
		_e( 'There are no NextGen galleries to import!', 'foogallery' );
	} else {
		?>
		<form id="nextgen_import_form" method="POST">
			<?php $nextgen->render_import_form( $galleries ); ?>
		</form>
	<?php } ?>
</div>
