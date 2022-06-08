<?php
/**
 * Import / Export view
 *
 * @package foogallery
 */

$galleries     = foogallery_get_all_galleries();
$export_helper = new FooGallery_Export_View_Helper();
$import_helper = new FooGallery_Import_View_Helper();
?>
<style>
	.foo-nav-tabs a:focus {
		-webkit-box-shadow: none;
		box-shadow: none;
	}

	.foo-nav-container {
		margin-top: 10px;
	}

	.foogallery-help {
		margin-bottom: 10px;
	}
</style>
<script>
	jQuery(function ($) {
		$('.foo-nav-tabs').on('click', 'a', function (e) {
			$('.foo-nav-container').hide();
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

<h2><?php _e( 'FooGallery Import / Export', 'foogallery' ); ?></h2>

<h2 class="foo-nav-tabs nav-tab-wrapper">
	<a href="#import" data-tab="foogallery_import_galleries" class="nav-tab nav-tab-active"><?php _e('Import', 'foogallery'); ?></a>
	<a href="#export" data-tab="foogallery_export_galleries" class="nav-tab"><?php _e('Export', 'foogallery'); ?></a>


</h2>

<div class="foo-nav-container" id="foogallery_import_galleries">
	<div class="foogallery-help">
		<?php echo esc_html( __( 'Paste the output from a previous export into the textarea below and click Import.', 'foogallery' ) ); ?>
		<br />
		<?php echo esc_html( __( 'Attachments will be imported into the media library, but only if the exported images are publicly available. Galleries with large amounts of images will take a long time to import.', 'foogallery' ) ); ?>
	</div>
	<?php $import_helper->render_import_form(); ?>
</div>
<div class="foo-nav-container" id="foogallery_export_galleries" style="display: none">
<?php
if ( ! $galleries ) {
	_e( 'There are no galleries to export!', 'foogallery' );
} else { ?>
	<div class="foogallery-help">
		<?php echo esc_html( __( 'Choose the galleries you want to export and click Export. You can then copy the output and use that to import on another WordPress install.', 'foogallery' ) ); ?>
		<br />
		<?php echo esc_html( __( 'If your galleries are loaded from another source, images will not be exported.', 'foogallery' ) ); ?>
		<br />
		<?php echo esc_html( __( 'Only images that are publicly accessible will be able to be imported.', 'foogallery' ) ); ?>
	</div>

	<?php $export_helper->render_export_form( $galleries ); ?>
<?php } ?>
</div>
