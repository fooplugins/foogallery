<?php
$nextgen = new FooGallery_NextGen_Helper();
if ( isset( $_POST['foogallery_nextgen_reset'] ) ) {

	if ( check_admin_referer( 'foogallery_nextgen_import_reset', 'foogallery_nextgen_import_reset' ) ) {
		$nextgen->reset_import();
	}
}
?>
<style>
	.foogallery-help {
		display: block;
		line-height: 19px;
		padding: 11px 15px 11px 5px;
		font-size: 14px;
		text-align: left;
		margin: 5px 0 20px 2px;
		background-color: #fff;
		border-left: 4px solid #1e8cbe;
		-webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
		box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
	}

	.foogallery-help:before {
		content: "\f223";
		font: 400 20px/1 dashicons !important;
		speak: none;
		color: #1e8cbe;
		display: inline-block;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
		padding-left: 5px;
		vertical-align: bottom;
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
</style>
<script>
	jQuery(function ($) {

		function nextgen_ajax(action, success_callback) {
			var data = jQuery("#nextgen_import_form").serialize();

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data + "&action=" + action,
				success: success_callback
			});
		}

		function nextgen_import_continue(check_progress) {
			nextgen_ajax('foogallery_nextgen_import_refresh', function (data) {
				$('#nextgen_import_form').html(data);

				check_progress = check_progress || true;

				if (check_progress) {
					//check if we need to carry on polling
					var percentage = $('#nextgen_import_progress').val();
					if (percentage < 100) {
						nextgen_import_continue();
					} else {
						nextgen_import_continue(false);
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
		<?php _e( 'Choose the NextGen galleries you want to import into FooGallery. Please note that importing galleries with lots of images can take a while.', 'foogallery' ); ?>
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
