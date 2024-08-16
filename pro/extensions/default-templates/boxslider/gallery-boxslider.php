<?php
global $current_foogallery;

$lightbox = foogallery_gallery_template_setting_lightbox();
$foogallery_default_classes = foogallery_build_class_attribute_safe($current_foogallery, 'boxslider', 'foogallery-lightbox-' . $lightbox);
$foogallery_default_attributes = foogallery_build_container_attributes_safe($current_foogallery, array('class' => $foogallery_default_classes));
?>
<div <?php echo $foogallery_default_attributes; ?>>
    <div id="boxslider-<?php echo $current_foogallery->ID; ?>" class="boxslider">
        <?php foreach (foogallery_current_gallery_attachments_for_rendering() as $attachment) : ?>
            <div class="slide">
                <?php echo foogallery_attachment_html($attachment); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script type="module">
    import { BoxSlider, FadeSlider, TileSlider, CubeSlider, CarouselSlider } from 'https://cdn.jsdelivr.net/npm/@boxslider/slider/+esm';

    document.addEventListener('DOMContentLoaded', function() {
        let sliderEffect;
        const selectedEffect = '<?php echo foogallery_gallery_template_setting('effect', 'fade'); ?>';

        switch (selectedEffect) {
            case 'tile':
                sliderEffect = new TileSlider();
                break;
            case 'cube':
                sliderEffect = new CubeSlider();
                break;
            case 'carousel':
                sliderEffect = new CarouselSlider();
                break;
            case 'fade':
            default:
                sliderEffect = new FadeSlider();
                break;
        }

        const slider = new BoxSlider(
            document.getElementById('boxslider-<?php echo $current_foogallery->ID; ?>'),
            sliderEffect
        );
    });
</script>
