<?php
if ( isset( $_POST['foogallery_demo_content_generate'] ) ) {
	if ( check_admin_referer( 'foogallery_demo_content_generate', 'foogallery_demo_content_generate' ) ) {
		if ( isset( $_POST['q'] ) ) {
			$query   = $_POST['q'];
			$message = FooGallery_Demo_Content_Generator::generate($query);
		} else {
			$message = __('Please provide a search term', 'foogallery');
		}
	}
} else {
	$query = 'color urban graffiti';
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

	<form id="nextgen_import_form" method="POST">
		<?php wp_nonce_field( 'foogallery_demo_content_generate', 'foogallery_demo_content_generate' ); ?>
		<input placeholder="<?php __('Search for?', 'foogallery'); ?>" type="text" name="q" value="<?php echo $query; ?>" />
		<input type="submit" class="button button-primary" value="<?php _e( 'Search + Generate!', 'foogallery' ); ?>">
	</form>
	<?php if ( isset( $message ) ) { ?>
	<p>
		<?php echo $message; ?>
	</p>
	<?php } ?>

	<p>

	</p>
</div>
