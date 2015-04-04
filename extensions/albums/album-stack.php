<?php
/**
 * FooGallery All-In-One Stack Album template
 */
global $current_foogallery_album;
global $current_foogallery_album_arguments;
$args = foogallery_album_template_setting( 'thumbnail_dimensions', array() );
$lightbox = foogallery_album_template_setting( 'lightbox', 'unknown' );
$random_angle = foogallery_album_template_setting( 'random_angle', 'false' );
$gutter = foogallery_album_template_setting( 'gutter', '40' );
$delay = foogallery_album_template_setting( 'delay', '0' );
$pile_angles = foogallery_album_template_setting( 'pile_angles', '2' );
?>
<div id="foogallery-album-<?php echo $current_foogallery_album->ID; ?>" class="foogallery-container foogallery-stack-album">
	<div class="topbar">
		<span id="foogallery-stack-album-back-<?php echo $current_foogallery_album->ID; ?>" class="back">&larr;</span>
		<h2><?php echo $current_foogallery_album->name; ?></h2><h3 id="foogallery-stack-album-gallery-<?php echo $current_foogallery_album->ID; ?>"></h3>
	</div>
	<ul id="foogallery-stack-album-<?php echo $current_foogallery_album->ID; ?>" class="tp-grid">
		<?php
		foreach ( $current_foogallery_album->galleries() as $gallery ) {
			foreach ( $gallery->attachments() as $attachment ) {
				echo '<li data-pile="'. esc_attr($gallery->name) . '">';
				$args['link_attributes']['rel'] = 'gallery[' . $gallery->ID . ']';
				$args['link_attributes']['class'] = apply_filters( 'foogallery_album_stack_link_class_name', $lightbox );
				echo $attachment->html( $args, false, false );
				if ( $attachment->caption ) {
					echo '<span class="tp-info"><span>' . wp_filter_nohtml_kses( $attachment->caption ) . '</span></span>';
				}
				echo $attachment->html_img( $args );
				echo '</a>';
				echo '</li>';
			}
		}
		?>
	</ul>
</div>
<script type="text/javascript">
	jQuery(function($) {

		var $grid = $( '#foogallery-stack-album-<?php echo $current_foogallery_album->ID; ?>' ),
			$name = $( '#foogallery-stack-album-gallery-<?php echo $current_foogallery_album->ID; ?>' ),
			$close = $( '#foogallery-stack-album-back-<?php echo $current_foogallery_album->ID; ?>' ),
			$loader = $( '<div class="loader"><i></i><i></i><i></i><i></i><i></i><i></i></div>' ).insertBefore( $grid ),
			stapel = $grid.stapel( {
				delay : <?php echo $delay; ?>,
				randomAngle : <?php echo $random_angle; ?>,
				gutter : <?php echo $gutter; ?>,
				pileAngles : <?php echo $pile_angles; ?>,
				onLoad : function() {
					$loader.remove();
				},
				onBeforeOpen : function( pileName ) {
					$name.html( pileName );
				},
				onAfterOpen : function( pileName ) {
					$close.show();
				}
			} );

		$close.on( 'click', function() {
			$close.hide();
			$name.empty();
			stapel.closePile();
		} );
	} );
</script>
