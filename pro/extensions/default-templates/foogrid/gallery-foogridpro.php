<?php
/**
 * FooGallery FooGrid PRO gallery template
 */
//the current FooGallery that is currently being rendered to the frontend
global $current_foogallery;
//the current shortcode args
global $current_foogallery_arguments;
//get our thumbnail sizing args
$args = foogallery_gallery_template_setting( 'thumbnail_size', 'thumbnail' );
//add the link setting to the args
$args['link'] = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
$theme = foogallery_gallery_template_setting( 'theme', '' );
$columns = foogallery_gallery_template_setting( 'columns', 'foogrid-cols-4' );
$captions = foogallery_gallery_template_setting( 'captions', 'foogrid-caption-below' );
$transition = foogallery_gallery_template_setting( 'transition', 'foogrid-transition-horizontal' );
// data attribute options
$loop = foogallery_gallery_template_setting( 'loop', 'yes' ) === 'yes';
$scroll = foogallery_gallery_template_setting( 'scroll', 'yes' ) === 'yes';
$scroll_smooth = foogallery_gallery_template_setting( 'scroll_smooth', 'yes' ) === 'yes';
$scroll_offset = foogallery_gallery_template_setting( 'scroll_offset', 0 );
?>
<ul id="foogallery-gallery-<?php echo $current_foogallery->ID; ?>" class="<?php echo foogallery_build_class_attribute( $current_foogallery, $theme, $columns, $captions, $transition ); ?>"
    data-loop="<?php echo $loop ?>" data-scroll="<?php echo $scroll ?>" data-scroll-smooth="<?php echo $scroll_smooth ?>" data-scroll-offset="<?php echo $scroll_offset ?>">
	<?php foreach ( $current_foogallery->attachments() as $attachment ) {
		echo '<li class="foogrid-item">' . $attachment->html( $args ) . '</li>';
	} ?>
</ul>