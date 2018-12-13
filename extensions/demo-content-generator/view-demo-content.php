<?php
wp_enqueue_script( 'masonry' );
foogallery_enqueue_core_gallery_template_style();
foogallery_enqueue_core_gallery_template_script();

if ( isset( $_POST['foogallery_demo_content_generate'] ) ) {
	if ( check_admin_referer( 'foogallery_demo_content_generate', 'foogallery_demo_content_generate' ) ) {
		$query   = $_POST['q'];
		$count   = intval( $_POST['c'] );
		$action  = isset( $_POST['btn_search'] ) ? 'search' : 'import';

		if ( 'search' === $action ) {
			if ( ! empty( $query ) && $count > 0 ) {
				$results = FooGallery_Demo_Content_Generator::search( $query, $count );
			} else {
				$message = __( 'Please provide keywords and how many images you want to search for!', 'foogallery' );
			}
		} else {
			$gallery_id = FooGallery_Demo_Content_Generator::generate( $query, $count );
			$gallery_link = sprintf( '<a href="%s" target="_blank">%s</a>', get_edit_post_link( $gallery_id ), __( 'View the gallery', 'foogallery' ) );
			$message = sprintf( __( 'The images have been imported into your media library and a gallery has been generated. %s', 'foogallery' ), $gallery_link );
		}
	}
} else {
	//initial page load
	$query = '';
	$count = 20;
}
?>
<style>
	.spinner.shown {
		display: inline !important;
		margin: 0;
	}
	.foogallery-help {
		margin-bottom: 10px;
	}
</style>
<div class="wrap about-wrap">
	<?php
	$gallery_count = count( foogallery_gallery_templates() );
	?>

	<h2><?php _e( 'FooGallery Demo Content Generator', 'foogallery' ); ?></h2>

	<div class="foogallery-help">
		<?php _e( 'Search for images and generate galleries below. Use multiple keywords to ensure you find enough images.', 'foogallery' ); ?>
		<?php printf( __( 'Images are provided by %s', 'foogallery' ), '<a href="https://pixabay.com/" target="_blank">Pixabay</a>.' ); ?>
	</div>

	<a target="_blank" href="https://pixabay.com/"><img src="https://pixabay.com/static/img/public/leaderboard_a.png" alt="Pixabay"></a>

	<br /><br />

	<form id="demo_content_form" method="POST">
		<?php wp_nonce_field( 'foogallery_demo_content_generate', 'foogallery_demo_content_generate' ); ?>
		<?php _e( 'Keywords', 'foogallery' ); ?> <input placeholder="<?php __('Search for?', 'foogallery'); ?>" type="text" name="q" value="<?php echo $query; ?>" />
		<?php _e( 'Images', 'foogallery' ); ?> <input type="number" name="c" style="width: 3em" value="<?php echo $count; ?>" />
		<input type="submit" class="button button-primary" name="btn_search" value="<?php _e( 'Search', 'foogallery' ); ?>">
		<?php if ( isset( $results ) ) {
			$items = array(
				'items' => $results,
				'template' => array ('layout' => 'fixed', 'gutter' => 5 )
			);
			?>
			<p>Found <?php echo count($results); ?> images. Demo gallery:</p>
			<div id="fg-demo" class="foogallery fg-masonry fg-center fg-gutter fg-loading-default fg-loaded-fade-in fg-caption-hover fg-hover-fade"
				 data-foogallery="<?php echo esc_attr( json_encode( $items ) ); ?>">
			</div>
			<script>
				jQuery(function() {
					jQuery('.foogallery').foogallery();
				});
			</script>
			<input type="submit" class="button button-primary" name="btn_import" value="<?php _e( 'Import Images &amp; Generate Gallery', 'foogallery' ); ?>">
		<?php } ?>
	</form>
	<?php if ( isset( $message ) ) { ?>
	<p>
		<?php echo $message; ?>
	</p>
	<?php } ?>
</div>
