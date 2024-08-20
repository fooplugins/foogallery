<?php
/**
 * FooGallery BoxSlider Template
 *
 * @package foogallery
 */

global $current_foogallery;

$gallery_id = $current_foogallery->ID;
$lightbox   = foogallery_gallery_template_setting_lightbox();
$link       = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );

$classes = foogallery_build_class_attribute_safe(
	$current_foogallery,
	'foogallery-link-' . $link,
	'foogallery-lightbox-' . $lightbox,
	'boxslider'
);

$attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $classes ) );

$effect           = foogallery_gallery_template_setting( 'effect', 'fade' );
$speed            = intval( foogallery_gallery_template_setting( 'speed', 800 ) );
$swipe            = foogallery_gallery_template_setting( 'swipe', 'true' ) === 'true';
$auto_scroll      = foogallery_gallery_template_setting( 'autoScroll', 'true' ) === 'true';
$timeout          = intval( foogallery_gallery_template_setting( 'timeout', 5000 ) );
$pause_on_hover   = foogallery_gallery_template_setting( 'pauseOnHover', 'false' ) === 'true';
$show_controls    = foogallery_gallery_template_setting( 'show_controls', 'true' ) === 'true';
$timing_function  = foogallery_gallery_template_setting( 'timing-function', 'ease-in' );
$tile_effect      = foogallery_gallery_template_setting( 'tile-effect', 'flip' );
$rows             = intval( foogallery_gallery_template_setting( 'rows', 8 ) );
$row_offset       = intval( foogallery_gallery_template_setting( 'rowOffset', 50 ) );
$direction        = foogallery_gallery_template_setting( 'direction', 'horizontal' );
$cover            = foogallery_gallery_template_setting( 'cover', 'true' ) === 'true';

$prev_text  = esc_html( foogallery_get_setting( 'language_boxslider_prev_text', __( 'Prev', 'foogallery' ) ) );
$next_text  = esc_html( foogallery_get_setting( 'language_boxslider_next_text', __( 'Next', 'foogallery' ) ) );
$play_text  = esc_html( foogallery_get_setting( 'language_boxslider_play_text', __( 'Play', 'foogallery' ) ) );
$pause_text = esc_html( foogallery_get_setting( 'language_boxslider_pause_text', __( 'Pause', 'foogallery' ) ) );

?>

<div <?php echo $attributes; ?>>
	<bs-slider-controls>
		<bs-<?php echo esc_attr( $effect ); ?>
			id="boxslider-<?php echo esc_attr( $gallery_id ); ?>"
			speed="<?php echo esc_attr( $speed ); ?>"
			<?php echo $swipe ? 'swipe' : ''; ?>
			<?php echo $auto_scroll ? 'auto-scroll' : ''; ?>
			timeout="<?php echo esc_attr( $timeout ); ?>"
			<?php echo $pause_on_hover ? 'pause-on-hover' : ''; ?>
			timing-function="<?php echo esc_attr( $timing_function ); ?>"
			tile-effect="<?php echo esc_attr( $tile_effect ); ?>"
			rows="<?php echo esc_attr( $rows ); ?>"
			row-offset="<?php echo esc_attr( $row_offset ); ?>"
			direction="<?php echo esc_attr( $direction ); ?>"
			<?php echo $cover ? 'cover' : ''; ?>
		>
			<?php foreach ( foogallery_current_gallery_attachments_for_rendering() as $attachment ) : ?>
				<div class="slide">
					<?php echo foogallery_attachment_html( $attachment ); ?>
				</div>
			<?php endforeach; ?>
		</bs-<?php echo esc_attr( $effect ); ?>>
	</bs-slider-controls>
	<?php if ( $show_controls ) : ?>
		<div class="boxslider-controls">
			<button class="boxslider-prev"><?php echo esc_html( $prev_text ); ?></button>
			<button class="boxslider-next"><?php echo esc_html( $next_text ); ?></button>
			<button class="boxslider-play"><?php echo esc_html( $play_text ); ?></button>
			<button class="boxslider-pause"><?php echo esc_html( $pause_text ); ?></button>
		</div>
	<?php endif; ?>
</div>