<?php
/**
 * FooGallery All-In-One Stack Album template
 */
global $current_foogallery_album;
global $current_foogallery_album_arguments;
global $current_foogallery;


$args = foogallery_album_template_setting( 'thumbnail_dimensions', array() );
$args['crop'] = true;
$lightbox = foogallery_album_template_setting( 'lightbox', 'unknown' );
if ( $lightbox === 'foogallery' ) {
    $lightbox = 'unknown';
}
$random_angle = foogallery_album_template_setting( 'random_angle', 'false' );
$gutter = foogallery_album_template_setting( 'gutter', '40' );
$pile_angles = foogallery_album_template_setting( 'pile_angles', '2' );

$data_options = array(
	'itemWidth' => intval( $args['width'] ),
	'itemHeight' => intval( $args['height'] ),
    'gutter' => intval( $gutter ),
    'angleStep' => intval( $pile_angles ),
    'randomAngle' => 'true' === $random_angle
);

$data_options = foogallery_json_encode( $data_options );

if ( !function_exists( 'foogallery_album_all_in_one_stack_render_gallery_attachment2' ) ) {
	function foogallery_album_all_in_one_stack_render_gallery_attachment2( $gallery, $attachment, $args ) {
		echo '<li class="fg-pile-item">';
		$args['link_attributes']['class'] = apply_filters( 'foogallery_album_stack_link_class_name', 'fg-pile-item-thumb' );

		$anchor_html = foogallery_attachment_html_anchor_opening( $attachment, $args );
        $anchor_html = str_replace( 'fg-thumb', '', $anchor_html );
        echo $anchor_html;

		$image_html = foogallery_attachment_html_image( $attachment, $args );
		$image_html = str_replace( 'fg-image', 'fg-pile-item-image', $image_html );
		echo $image_html;

		$captions = foogallery_build_attachment_html_caption( $attachment, $args );
		if ( $captions !== false ) {
			echo '<span class="fg-pile-item-caption">';
			if ( array_key_exists( 'title', $captions ) ) {
				echo '<span class="fg-pile-item-title">' . $captions['title'] . '</span>';
			}
			if ( array_key_exists( 'desc', $captions ) ) {
				echo '<span class="fg-pile-item-desc">' . $captions['desc'] . '</span>';
			}
			echo '</span>';
		}

		echo '</a>';
		echo '</li>';
	}
}
?>
<div id="foogallery-album-<?php echo $current_foogallery_album->ID; ?>" class="foogallery-container foogallery-stack-album" data-foogallery="<?php echo esc_attr( $data_options ); ?>">
    <div class="fg-header">
        <h2 class="fg-header-title"><?php echo $current_foogallery_album->name; ?></h2>
        <span class="fg-header-back">&larr;</span>
        <h3 class="fg-header-active"></h3>
    </div>
    <div class="fg-piles">
	    <?php
	    foreach ( $current_foogallery_album->galleries() as $gallery ) {
		    ?><ul class="fg-pile <?php echo $lightbox; ?>" data-title="<?php echo esc_attr( $gallery->name ); ?>"><?php
		    $current_foogallery = $gallery;
		    $featured_attachment = $gallery->featured_attachment();
		    //render the featured attachment first
		    foogallery_album_all_in_one_stack_render_gallery_attachment2( $gallery, $featured_attachment, $args, $lightbox );

		    foreach ( $gallery->attachments() as $attachment ) {
			    if ( $featured_attachment->ID !== $attachment->ID ) {
				    //render all but the featured attachment
				    foogallery_album_all_in_one_stack_render_gallery_attachment2( $gallery, $attachment, $args, $lightbox );
			    }
		    }
            ?></ul><?php
	    }
	    ?>
    </div>
</div>

