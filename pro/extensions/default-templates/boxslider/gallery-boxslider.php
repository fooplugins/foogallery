<?php
global $current_foogallery;
$lightbox = foogallery_gallery_template_setting_lightbox();
$link = foogallery_gallery_template_setting( 'thumbnail_link', 'image' );
$foogallery_boxslider_classes = foogallery_build_class_attribute_safe($current_foogallery, 'foogallery-link-' . $link, 'foogallery-lightbox-' . $lightbox, 'boxslider' );
$foogallery_boxslider_attributes = foogallery_build_container_attributes_safe($current_foogallery, array('class' => $foogallery_boxslider_classes));

// Get language settings
$prev_text = esc_html(foogallery_get_setting('language_boxslider_prev_text', __('Prev', 'foogallery')));
$next_text = esc_html(foogallery_get_setting('language_boxslider_next_text', __('Next', 'foogallery')));
$play_text = esc_html(foogallery_get_setting('language_boxslider_play_text', __('Play', 'foogallery')));
$pause_text = esc_html(foogallery_get_setting('language_boxslider_pause_text', __('Pause', 'foogallery')));
?>

<div <?php echo $foogallery_boxslider_attributes; ?> style="width: 100%; max-width: 800px; height: 400px; overflow: hidden; margin: 0 auto;" class="fg-boxslider">
    <div id="fg-bx-boxslider" class="boxslider">
        <?php foreach (foogallery_current_gallery_attachments_for_rendering() as $attachment) : ?>
            <div class="slide">
                <?php echo foogallery_attachment_html($attachment); ?>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- Always render the controls, and let JavaScript handle the visibility -->
    <div class="boxslider-controls">
        <button class="boxslider-prev"><?php echo esc_html($prev_text); ?></button>
        <button class="boxslider-next"><?php echo esc_html($next_text); ?></button>
        <button class="boxslider-play"><?php echo esc_html($play_text); ?></button>
        <button class="boxslider-pause"><?php echo esc_html($pause_text); ?></button>
    </div>
</div>
