<?php
$nextgen = new FooGallery_NextGen_Helper();

if ( isset($_POST['foogallery_nextgen_import']) && isset($_POST['nextgen-id']) ) {

	if ( check_admin_referer( 'foogallery_nextgen_import', 'foogallery_nextgen_import' ) ) {
		$nextgen_gallery_ids = $_POST['nextgen-id'];

		$default_timeout = ini_get( 'max_execution_time' );
		set_time_limit( 0 );

		foreach ( $nextgen_gallery_ids as $gid ) {
			$foogallery_title = stripslashes( $_POST['foogallery-name-' . $gid] );
			$nextgen->import_gallery( $gid, $foogallery_title );
		}

		set_time_limit( $default_timeout );
	}
}
?>
<style>
	.foogallery-badge-foobot {
		position: absolute;
		top: 5px;
		right: 0;
		background: url(<?php echo FOOGALLERY_URL; ?>assets/foobot_small.png) no-repeat;
		width: 82px;
		height: 150px;
		z-index: 100;
	}

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
</style>
<script>
	jQuery(function ($) {
		$('#do_import').click(function () {
			$(this).hide();
			$('#import_spinner .spinner').show();
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
		<form method="POST">
			<table class="wp-list-table widefat" cellspacing="0">
				<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column">
						<label class="screen-reader-text"
							   for="cb-select-all-1"><?php _e( 'Select All', 'foogallery' ); ?></label>
						<input id="cb-select-all-1" type="checkbox" checked="checked">
					</th>
					<th scope="col" class="manage-column">
						<span><?php _e( 'NextGen Gallery', 'foogallery' ); ?></span>
					</th>
					<th scope="col" id="title" class="manage-column">
						<span><?php _e( 'FooGallery Name', 'foogallery' ); ?></span>
					</th>
					<th scope="col" id="title" class="manage-column">
						<span><?php _e( 'Import Progress', 'foogallery' ); ?></span>
					</th>
				</tr>
				</thead>
				<tbody id="the-list">
				<?php
				foreach ( $galleries as $gallery ) {
					$progress = $nextgen->get_import_progress( $gallery->gid );
					$done     = isset($progress['foogallery']);
					if ( $done ) {
						$foogallery = FooGallery::get_by_id( $progress['foogallery'] );
						$edit_link  = '<a href="' . admin_url( 'post.php?post=' . $progress['foogallery'] . '&action=edit' ) . '">' . $foogallery->name . '</a>';
					}
					?>
					<tr>
						<th scope="row" class="column-cb check-column">
							<?php if ( !$done ) { ?>
								<input name="nextgen-id[]" type="checkbox" checked="checked"
									   value="<?php echo $gallery->gid; ?>">
							<?php } ?>
						</th>
						<td>
							<?php echo $gallery->title . sprintf( __( ' (%s images)', 'foogallery' ), $gallery->image_count ); ?>
						</td>
						<td>
							<?php if ( $done ) {
								echo $edit_link;
							} else {
								?>
								<input name="foogallery-name-<?php echo $gallery->gid; ?>"
									   value="<?php echo $gallery->title; ?>">
							<?php } ?>
						</td>
						<td class="<?php echo $progress['status']; ?>">
							<?php echo $progress['message']; ?>
						</td>
					</tr>
				<?php
				}
				?>
				</tbody>
			</table>
			<br/>
			<?php wp_nonce_field( 'foogallery_nextgen_import', 'foogallery_nextgen_import' ); ?>
			<input type="submit" class="button-primary" id="do_import"
				   value="<?php _e( 'Start Import', 'foogallery' ); ?>">

			<div id="import_spinner" style="width:20px">
				<span class="spinner"></span>
			</div>
		</form>
	<?php } ?>
</div>
