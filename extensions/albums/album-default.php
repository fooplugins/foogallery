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
    $album_url = trailingslashit( foogallery_album_remove_gallery_from_link() );
    $gallery_title_size = esc_attr( foogallery_album_template_setting( 'gallery_title_size', 'h2' ) );
    echo '<div id="' . esc_attr( $current_foogallery_album->slug ) . '" class="foogallery-album-header">';
    echo '<p><a href="' . esc_url( $album_url ) . '">' . esc_html( foogallery_get_setting( 'language_back_to_album_text', __( '&laquo; back to album', 'foogallery' ) ) ) . '</a></p>';
    echo '<' . esc_attr( $gallery_title_size ) . '>' . esc_html( $foogallery->name ) . '</' . esc_attr( $gallery_title_size ) . '>';
    echo wp_kses_post( apply_filters( 'foogallery_album_default_gallery_content', '', $foogallery ) );
    echo '</div>';
    echo do_shortcode( foogallery_build_gallery_shortcode( $foogallery->ID ) );
} 
else {
    $title_bg = esc_attr( foogallery_album_template_setting( 'title_bg', '#ffffff' ) );
    $title_font_color = esc_attr( foogallery_album_template_setting( 'title_font_color', '#000000' ) );
    $args = foogallery_album_template_setting( 'thumbnail_dimensions', array() );
    if ( !empty( $title_bg ) || !empty( $title_font_color ) ) {
        echo '<style type="text/css">';
        if ( !empty( $title_bg ) ) {
            echo '.foogallery-album-gallery-list .foogallery-pile h3 { background: ' . esc_attr( $title_bg ) . ' !important; }';
        }
        if ( !empty( $title_font_color ) ) {
            echo '.foogallery-album-gallery-list .foogallery-pile h3 { color: ' . esc_attr( $title_font_color ) . ' !important; }';
        }
        echo '</style>';
    }
?>
<div id="foogallery-album-<?php echo esc_attr( $current_foogallery_album->ID ); ?>">
    <ul class="foogallery-album-gallery-list <?php echo esc_attr( $alignment ); ?>">
        <?php
        foreach ( $current_foogallery_album->galleries() as $gallery ) {
            $current_foogallery = $gallery;
            if ( !empty( $gallery->has_items() ) ) {
                $attachment = $gallery->featured_attachment();

                if ( false === $attachment ) continue;

                $img_html = $attachment->html_img( $args );
                $images = $gallery->image_count();
                $gallery_link = foogallery_album_build_gallery_link( $current_foogallery_album, $gallery );
                $gallery_link_target = esc_attr( foogallery_album_build_gallery_link_target( $current_foogallery_album, $gallery ) );
                ?>
                <li>
                    <div class="foogallery-pile">
                        <div class="foogallery-pile-inner">
                            <a href="<?php echo esc_url( $gallery_link ); ?>" target="<?php echo esc_attr( $gallery_link_target ); ?>">
                                <?php echo wp_kses_post( $img_html );?>
                                <?php

                                $title = empty( $gallery->name ) ?
                                    sprintf( esc_html__( '%s #%s', 'foogallery' ), esc_html( foogallery_plugin_name() ), esc_html( $gallery->ID ) ) :
                                    esc_html( $gallery->name );

                                ?>
                               <h3><?php echo esc_html( $title ); ?>
									<span><?php echo esc_html( $images ); ?></span>
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
<?php } ?>
