<?php
/**
 * FooGallery default responsive album template
 */
global $current_foogallery_album;
global $current_foogallery_album_arguments;
$gallery = foogallery_album_get_current_gallery();
$no_images_text = foogallery_album_template_setting( 'no_images_text', false );
$single_image_text = foogallery_album_template_setting( 'single_image_text', false );
$images_text = foogallery_album_template_setting( 'images_text', false );
$alignment = foogallery_album_template_setting( 'alignment', 'alignment-left' );
$foogallery = false;

if ( !empty( $gallery ) ) {
	$foogallery = FooGallery::get_by_slug( $gallery );

	//check to see if the gallery belongs to the album
	if ( !$current_foogallery_album->includes_gallery( $foogallery->ID ) ) {
		$foogallery = false;
	}
}

if ( false !== $foogallery ) {
	$album_url = foogallery_album_remove_gallery_from_link();
	echo '<div id="' . $current_foogallery_album->slug . '" class="foogallery-album-header">';
	echo '<p><a href="' . esc_url( $album_url ) . '">' . foogallery_album_template_setting( 'back_to_album_text', '&laquo; back to album' ) . '</a></p>';
	echo '<h2>' . $foogallery->name . '</h2>';
	echo '</div>';
	echo do_shortcode('[foogallery id="' . $foogallery->ID . '"]');
} else {
	$title_bg = foogallery_album_template_setting( 'title_bg', '#ffffff' );
	$title_font_color = foogallery_album_template_setting( 'title_font_color', '#000000' );
	$args = foogallery_album_template_setting( 'thumbnail_dimensions', array() );
	if ( !empty( $title_bg ) || !empty( $title_font_color ) ) {
		echo '<style type="text/css">';
		if ( !empty( $title_bg ) ) {
			echo '.foogallery-album-gallery-list .foogallery-pile h3 { background: ' . $title_bg . ' !important; }';
		}
		if ( !empty( $title_font_color ) ) {
			echo '.foogallery-album-gallery-list .foogallery-pile h3 { color: ' . $title_font_color . ' !important; }';
		}
		echo '</style>';
	}
?>
<div id="foogallery-album-<?php echo $current_foogallery_album->ID; ?>">
	<ul class="foogallery-album-gallery-list <?php echo $alignment; ?>">
		<?php
		foreach ( $current_foogallery_album->galleries() as $gallery ) {
			$attachment = $gallery->featured_attachment();
			$img_html = $attachment->html_img( $args );
			$images = $gallery->image_count( $no_images_text, $single_image_text, $images_text );
			$gallery_link = foogallery_album_build_gallery_link( $current_foogallery_album, $gallery );
			?>
			<li>
				<div class="foogallery-pile">
					<div class="foogallery-pile-inner">
						<a href="<?php echo esc_url( $gallery_link ); ?>">
							<?php echo $img_html; ?>
							<?php

							$title = empty( $gallery->name ) ?
								sprintf( __( '%s #%s', 'foogallery' ), foogallery_plugin_name(), $gallery->ID ) :
								$gallery->name;

							?>
							<h3><?php echo $title; ?>
								<span><?php echo $images; ?></span>
							</h3>
						</a>
					</div>
				</div>
			</li>

		<?php } ?>
	</ul>
	<div style="clear: both;"></div>
</div>
<?php }
