<?php

if ( ! class_exists( 'FooGallery_Export_View_Helper' ) ) {

	/**
	 * Class FooGallery_Export_View_Helper
	 */
	class FooGallery_Export_View_Helper {
        /**
         * Renders the export form for a number of galleries.
         *
         * @param $galleries
         * @return void
         */
		public function render_export_form( $galleries ) {
			?>
			<style>
				#foogallery_gallery_export_output {
					width: 100%;
					height: 500px;
				}
			</style>
			<script>
				jQuery(function ($) {
					$('#foogallery_export_form').on('click', '.foogallery_gallery_export', function (e) {
						e.preventDefault();

						$('#foogallery_gallery_export_output_container').hide();
						$('.foogallery_export_spinner').addClass('is-active');

						var data = {
							action: 'foogallery_gallery_export',
							galleries: $('#foogallery_export_form .foogallery_id:checkbox:checked').map(function() {
								return this.value;
							}).get(),
							'_wpnonce' : $('#foogallery_gallery_export').val()
						};

						$.ajax({
							type: "POST",
							url: ajaxurl,
							data: data,
							success: function(data) {
								$('#foogallery_gallery_export_output').val(data);
								$('#foogallery_gallery_export_output_container').show();
							},
							complete: function() {
								$('.foogallery_export_spinner').removeClass('is-active');
							},
							error: function() {
								//something went wrong! Alert the user and reload the page
								alert('<?php _e( 'Something went wrong with the export!', 'foogallery' ); ?>');
							}
						});
					});
				});
			</script>
			<?php
			wp_nonce_field( 'foogallery_gallery_export', 'foogallery_gallery_export', false );
			?>
			<form id="foogallery_export_form" method="POST">
				<table class="wp-list-table widefat" cellspacing="0">
				<thead>
				<tr>
					<td scope="col" id="cb" class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All', 'foogallery' ); ?></label>
						<input id="cb-select-all-1" type="checkbox" />
					</td>
					<th scope="col" class="manage-column">
						<span><?php _e( 'Gallery Name', 'foogallery' ); ?></span>
					</th>
					<th scope="col" class="manage-column">
						<span><?php _e( 'Template', 'foogallery' ); ?></span>
					</th>
					<th scope="col" class="manage-column">
						<span><?php _e( 'Datasource', 'foogallery' ); ?></span>
					</th>
					<th scope="col" class="manage-column">
						<span><?php _e( 'Attachments', 'foogallery' ); ?></span>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$counter = 0;
				foreach ( $galleries as $gallery ) {
					$counter++; ?>
					<tr class="<?php echo ($counter % 2 === 0) ? 'alternate' : ''; ?>">
						<th scope="row" class="column-cb check-column">
							<input name="foogallery-id[]" class="foogallery_id" type="checkbox" value="<?php echo $gallery->ID; ?>">
						</th>
						<td>
							<?php echo $gallery->name; ?>
						</td>
						<td>
							<?php echo $gallery->gallery_template; ?>
						</td>
						<td>
							<?php echo $gallery->datasource_name; ?>
						</td>
						<td>
							<?php
							if ( 'media_library' === $gallery->datasource_name ) {
								echo $gallery->item_count();
							} else {
								echo '0';
							}
							?>
						</td>
					</tr>
				<?php }	?>
				</tbody>
			</table>
				<br />
				<input type="submit" name="foogallery_gallery_export" class="button button-primary foogallery_gallery_export" value="<?php _e( 'Export', 'foogallery' ); ?>">
				<span class="foogallery_export_spinner spinner" style="float: none"></span>
			</form>
			<br />
			<div id="foogallery_gallery_export_output_container" style="display:none">
			<p><?php echo esc_html( __( 'Copy the export data below and paste it into the import form on the destination site.', 'foogallery' ) ); ?></p>
			<textarea id="foogallery_gallery_export_output"></textarea>
			</div>
			<?php
		}
	}
}