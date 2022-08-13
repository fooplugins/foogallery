<?php

if ( ! class_exists( 'FooGallery_Import_View_Helper' ) ) {

	/**
	 * Class FooGallery_Import_View_Helper
	 */
	class FooGallery_Import_View_Helper {
		public function render_import_form() {
			?>
			<style>
				#foogallery_import_form textarea {
					width: 100%;
					height: 200px;
				}
				.foogallery_gallery_import_results {
					display: none;
				}
			</style>
			<script>
				jQuery(function ($) {
					$('#foogallery_import_form').on('click', '.foogallery_gallery_import', function (e) {
						e.preventDefault();

						//show the spinner
						$('.foogallery_import_spinner').addClass('is-active');

						var data = {
							action: 'foogallery_gallery_import',
							data: $('#foogallery_import_form textarea').val(),
							'_wpnonce' : $('#foogallery_gallery_import').val()
						};

						$.ajax({
							type: "POST",
							url: ajaxurl,
							data: data,
							success: function(data) {
								$('.foogallery_gallery_import_results').html(data).show();
							},
							complete: function() {
								$('.foogallery_import_spinner').removeClass('is-active');
							},
							error: function() {
								alert('<?php _e( 'Something went wrong with the import!', 'foogallery' ); ?>');
							}
						});
					});
				});
			</script>
			<?php
			wp_nonce_field( 'foogallery_gallery_import', 'foogallery_gallery_import', false );
			?>
			<form id="foogallery_import_form" method="POST">
				<textarea></textarea>
				<br />
				<br />
				<input type="submit" name="foogallery_gallery_import" class="button button-primary foogallery_gallery_import" value="<?php echo esc_attr( __( 'Import', 'foogallery' ) ); ?>">
				<span class="foogallery_import_spinner spinner" style="float: none"></span>
				<p class="foogallery_gallery_import_results"></p>
			</form>
			<?php
		}
	}
}