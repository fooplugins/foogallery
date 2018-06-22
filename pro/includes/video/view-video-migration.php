<?php
$helper = new FooGallery_Pro_Video_Migration_Helper();
?>
<style>
	div.about-wrap h2 {
		text-align: left;
	}

	.foogallery-help {
		margin-bottom: 10px;
	}

	.spinner.shown {
		display: inline !important;
		margin: 0;
	}



	.nextgen-import-progress-error {
		color: #f00 !important;
	}

	.nextgen-import-progress-not_started {
		color: #f60 !important;
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
<script type="text/javascript">
	jQuery(function ($) {
		$('.foogallery-video-migrate-container').on('click', '.foogallery-video-migrate', function (e) {
			e.preventDefault();

			//show the spinner
			$('.foogallery-video-migrate-spinner').addClass('is-active');

			var data = {
				action: $(this).data('action'),
				'_wpnonce' : $('#foogallery_video_migration').val()
			};

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				dataType: 'JSON',
				success: function(data) {
					$('.button-primary.foogallery-video-migrate').attr('value', data.button_text);
					$('.foogallery_video_migration_message').html(data.message);
				},
				complete: function() {
					$('.foogallery-video-migrate-spinner').removeClass('is-active');
				},
				error: function() {
					//something went wrong! Alert the user and reload the page
					alert('<?php _e( 'Something went wrong with migration, so the page will now reload.', 'foogallery' ); ?>');
					location.reload();
				}
			});
		});
	});
</script>
<div class="wrap about-wrap foogallery-video-migrate-container">
	<?php
	$state = $helper->get_migration_state();
	$button_text = $state['button_text'];
	?>

	<h2><?php _e( 'FooGallery Video Migration Tool', 'foogallery' ); ?></h2>

	<div class="foogallery-help">
		<?php _e('This migration tool will help you migrate all videos and galleries that were created with the legacy FooVideo extension to be compatible with the new video features within FooGallery PRO. Once the migration is complete, you will be able to deactivate and delete the FooVideo extension.', 'foogallery' ); ?>
	</div>

	<p class="foogallery_video_migration_message"><?php echo $state['message']; ?></p>

	<?php if ( $state['step'] > 0 ) { ?>
		<input type="submit" class="button button-secondary foogallery-video-migrate" data-action="foogallery_video_migration_reset" value="<?php _e('Restart Migration', 'foogallery'); ?>">
	<?php } ?>

	<input type="submit" class="button button-primary foogallery-video-migrate" data-action="foogallery_video_migration" value="<?php echo $button_text; ?>">

	<?php wp_nonce_field( 'foogallery_video_migration', 'foogallery_video_migration' ); ?>
	<div style="width:40px; display: inline-block;"><span class="foogallery-video-migrate-spinner spinner"></span></div>
</div>
