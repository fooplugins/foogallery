<?php
/**
 * FooGallery default responsive album template
 */
global $current_foogallery_album;
global $current_foogallery_album_arguments;
global $current_foogallery;
$gallery = foogallery_album_get_current_gallery();
$alignment = foogallery_album_template_setting( 'alignment', 'alignment-left' );
$foogallery = false;

if ( !empty( $gallery ) ) {
	$foogallery = FooGallery::get_by_slug( $gallery );

	//check to see if the gallery belongs to the album
	if ( $foogallery !== false && !$current_foogallery_album->includes_gallery( $foogallery->ID ) ) {
		$foogallery = false;
	}
}

if ( false !== $foogallery ) {
	$album_url = esc_url( trailingslashit( foogallery_album_remove_gallery_from_link() ) );

    // Allow only safe heading tags (h1-h6), default to h2 if invalid
    $allowed_headings = ['h2', 'h3', 'h4', 'h5', 'h6'];
    $gallery_title_size = foogallery_album_template_setting('gallery_title_size', 'h2');
    $gallery_title_size = in_array( $gallery_title_size, $allowed_headings ) ? $gallery_title_size : 'h2';

	echo '<div id="' . esc_attr( $current_foogallery_album->slug ) . '" class="foogallery-album-header">';
	echo '<p><a href="' . esc_url( $album_url ) . '">' . esc_html( foogallery_get_setting( 'language_back_to_album_text', __( '&laquo; back to album', 'foogallery' ) ) ) . '</a></p>';
	echo '<' . $gallery_title_size . '>' . esc_html( $foogallery->name ) . '</'. $gallery_title_size . '>';
	echo apply_filters('foogallery_album_default_gallery_content', '', $foogallery);
	echo '</div>';
	echo do_shortcode( foogallery_build_gallery_shortcode( $foogallery->ID ) );
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
			$current_foogallery = $gallery;
			if (!empty($gallery->has_items())) {
				$attachment = $gallery->featured_attachment();

				if ( false === $attachment ) continue;

				$img_html = $attachment->html_img( $args );
				$images = $gallery->image_count();
				$gallery_link = foogallery_album_build_gallery_link( $current_foogallery_album, $gallery );
				$gallery_link_target = foogallery_album_build_gallery_link_target( $current_foogallery_album, $gallery );
				?>
				<li>
					<div class="foogallery-pile">
						<div class="foogallery-pile-inner">
							<a href="<?php echo esc_url( $gallery_link ); ?>" target="<?php echo $gallery_link_target; ?>">
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
		<?php } ?>
	</ul>
	<div style="clear: both;"></div>
</div>
<?php }
