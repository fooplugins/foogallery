<?php
/**
 * FooGallery default responsive album template
 */
global $current_foogallery_album;
global $current_foogallery_album_arguments;
$gallery = safe_get_from_request( 'gallery' );
if ( !empty( $gallery ) ) {
	$album_url = remove_query_arg('gallery');
	echo '<p><a href="' . $album_url . '">' . $current_foogallery_album->get_meta( 'back_to_album_text', '&laquo; back to album' ) . '</a></p>';
	$foogallery = FooGallery::get_by_slug( $gallery );

	echo '<h2>' . $foogallery->name . '</h2>';
	echo do_shortcode('[foogallery id="' . $foogallery->ID . '"]');
} else {
	$title_bg = $current_foogallery_album->get_meta( 'title_bg', '' );
	$title_font_color = $current_foogallery_album->get_meta( 'title_font_color', '' );
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
	<ul class="foogallery-album-gallery-list">
		<?php
		foreach ( $current_foogallery_album->galleries() as $gallery ) {
			$img_src  = $gallery->featured_image_src( array( 150, 150 ) );
			$images   = $gallery->image_count();
			$gallery_link = add_query_arg('gallery', $gallery->slug );
			?>
			<li>
				<div class="foogallery-pile">
					<div class="foogallery-pile-inner">
						<a href="<?php echo $gallery_link; ?>">
							<img src="<?php echo $img_src; ?>"/>
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