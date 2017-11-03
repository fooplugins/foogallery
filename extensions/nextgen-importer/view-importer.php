<?php
$nextgen = new FooGallery_NextGen_Helper();
if ( isset( $_POST['foogallery_nextgen_reset'] ) ) {

	if ( check_admin_referer( 'foogallery_nextgen_import_reset', 'foogallery_nextgen_import_reset' ) ) {
		$nextgen->reset_import();
	}
} else if ( isset( $_POST['foogallery_nextgen_reset_album'] ) ) {

	if ( check_admin_referer( 'foogallery_nextgen_album_reset', 'foogallery_nextgen_album_reset' ) ) {
		$nextgen->reset_album_import();
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

	.tablenav .tablenav-pages a,
	.tablenav .tablenav-pages span {
		margin: 0 3px;
		padding: 5px;
	}

	.tablenav-pages span {
		display: inline-block;
		min-width: 17px;
		border: 1px solid #d2d2d2;
		background: #e4e4e4;
		font-size: 16px;
		line-height: 1;
		font-weight: normal;
		text-align: center;
	}

	.tablenav-pages span.selected-page {
		border-color: #5b9dd9;
		color: #fff;
		background: #00a0d2;
		-webkit-box-shadow: none;
		box-shadow: none;
		outline: none;
	}

	.tablenav-pages span.disabled {
		color: #888;
	}

	.foogallery-help {
		margin-bottom: 10px;
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
			$('#import_spinner .spinner').addClass('is-active');

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
			if (!confirm('<?php _e( 'Are you sure you want to reset all NextGen gallery import data? This may result in duplicate galleries and media attachments!', 'foogallery' ); ?>')) {
				e.preventDefault();
				return false;
			}
		});

		$('#nextgen_import_album_form').on('click', '.reset_album_import', function (e) {
			if (!confirm('<?php _e( 'Are you sure you want to reset all NextGen album import data? This may result in duplicate albums if you decide to import again!', 'foogallery' ); ?>')) {
				e.preventDefault();
				return false;
			}
		});

		$('#nextgen_import_album_form').on('click', '.start_album_import', function (e) {
			e.preventDefault();

			//show the spinner
			$(this).hide();
			var $tr = $(this).parents('tr:first');
			$tr.find('.spinner:first').addClass('is-active');

			var data = {
				action: 'foogallery_nextgen_album_import',
				foogallery_nextgen_album_import: $('#foogallery_nextgen_album_import').val(),
				nextgen_album_id: $tr.find('.foogallery-album-id').val(),
				foogallery_album_name: $tr.find('.foogallery-album-name').val()
			};

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function(data) {
					$('#nextgen_import_album_form').html(data);
				},
				error: function() {
					//something went wrong! Alert the user and reload the page
					alert('<?php _e( 'Something went wrong with the import and the page will now reload.', 'foogallery' ); ?>');
					location.reload();
				}
			});
		});

		$('.foo-nav-tabs').on('click', 'a', function (e) {
			$('.nextgen_import_container').hide();
			var tab = $(this).data('tab');
			$('#' + tab).show();
			$('.nav-tab').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
		});

		if (window.location.hash) {
			$('.foo-nav-tabs a[href="' + window.location.hash + '"]').click();
		}
	});
</script>
<div class="wrap about-wrap">
	<?php
	$galleries = $nextgen->get_galleries();
	$albums = $nextgen->get_albums();
	$gallery_count = '';
	if ( count( $galleries ) > 0 ) {
		$gallery_count = ' (' . count( $galleries ) . ')';
	}
	$album_count = '';
	if ( count( $albums ) > 0 ) {
		$album_count = ' (' . count( $albums ) . ')';
	}
	?>

	<h2><?php _e( 'NextGen Gallery And Album Importer', 'foogallery' ); ?></h2>

	<h2 class="foo-nav-tabs nav-tab-wrapper">
		<a href="#galleries" data-tab="nextgen_import_galleries" class="nav-tab nav-tab-active"><?php _e('Galleries', 'foogallery'); ?><?php echo $gallery_count; ?></a>
		<a href="#albums" data-tab="nextgen_import_albums" class="nav-tab"><?php _e('Albums', 'foogallery'); ?><?php echo $album_count; ?></a>
	</h2>

	<div class="nextgen_import_container" id="nextgen_import_galleries">
	<?php
	if ( ! $galleries ) {
		_e( 'There are no NextGen galleries to import!', 'foogallery' );
	} else { ?>
		<div class="foogallery-help">
			<?php _e( 'Importing galleries is really simple:', 'foogallery' ); ?>
			<ol>
				<li><?php printf( __( 'Choose the NextGen galleries you want to import into %s by checking their checkboxes.', 'foogallery' ), foogallery_plugin_name() ); ?></li>
				<li><?php _e( 'Click the Start Import button to start the import process.', 'foogallery' ); ?></li>
				<li><?php printf( __( 'Once a gallery is imported, you can click on the link under the %s Name column to edit the gallery.', 'foogallery' ), foogallery_plugin_name() ); ?></li>
			</ol>
			<?php _e('Please note: importing large galleries with lots of images can take a while!', 'foogallery' ); ?>
		</div>

		<form id="nextgen_import_form" method="POST">
			<?php $nextgen->render_import_form( $galleries ); ?>
		</form>
	<?php } ?>
	</div>
	<div class="nextgen_import_container" id="nextgen_import_albums" style="display: none">
	<?php
	if ( ! $albums ) {
		_e( 'There are no NextGen albums to import!', 'foogallery' );
	} else { ?>
		<div class="foogallery-help">
			<?php _e( 'Importing albums is also really simple:', 'foogallery' ); ?>
			<ol>
				<li><?php _e( __( 'For all the albums you wish to import, make sure all the galleries have been imported FIRST. If not, then go back to the Galleries tab.', 'foogallery' )); ?></li>
				<li><?php _e( 'Click the Import Album button for each album to import the album and link all the galleries. If you do not see the button, then that means you first need to import the galleries.', 'foogallery' ); ?></li>
				<li><?php _e( 'Once an album is imported, you can click on the link under the Album Name column to edit the album.', 'foogallery'); ?></li>
			</ol>
		</div>

		<form id="nextgen_import_album_form" method="POST">
			<?php $nextgen->render_album_import_form( $albums ); ?>
		</form>
	<?php } ?>
	</div>

</div>
