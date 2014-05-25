<?php
/**
 * FooGallery masonry gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;
?>
<div class="foogallery-container foogallery-default">
	<?php foreach ( $current_foogallery->attachments() as $attachment_id ) {
		echo wp_get_attachment_link( $attachment_id, 'thumbnail', false );
	} ?>
</div>
