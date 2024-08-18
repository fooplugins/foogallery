<?php
global $current_foogallery;
$lightbox = foogallery_gallery_template_setting_lightbox();
$foogallery_default_classes = foogallery_build_class_attribute_safe($current_foogallery, 'boxslider', 'foogallery-lightbox-' . $lightbox );
$foogallery_default_attributes = foogallery_build_container_attributes_safe($current_foogallery, array('class' => $foogallery_default_classes)); 

// Fetch all the settings
$effect = foogallery_gallery_template_setting('effect', 'fade');
$timing_function = foogallery_gallery_template_setting('timing-function', 'ease-in');
$tile_effect = foogallery_gallery_template_setting('tile-effect', 'flip');
$rows = intval(foogallery_gallery_template_setting('rows', 8));
$row_offset = intval(foogallery_gallery_template_setting('rowOffset', 50));
$speed = intval(foogallery_gallery_template_setting('speed', 800));
$timeout = intval(foogallery_gallery_template_setting('timeout', 5000));
$direction = foogallery_gallery_template_setting('direction', 'horizontal');
$cover = foogallery_gallery_template_setting('cover', 'true') === 'true';
$swipe = foogallery_gallery_template_setting('swipe', 'true') === 'true';
$autoScroll = foogallery_gallery_template_setting('autoScroll', 'true') === 'true';
$pause_on_hover = foogallery_gallery_template_setting('pauseOnHover', 'false') === 'true';
?>

<div <?php echo $foogallery_default_attributes; ?> style="width: 100%; max-width: 800px; height: 400px; overflow: hidden; margin: 0 auto;">
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
        const selectedEffect = '<?php echo $effect; ?>';
       
        const commonOptions = {
            speed: <?php echo $speed; ?>,
            swipe: <?php echo $swipe ? 'true' : 'false'; ?>,
            autoScroll: <?php echo $autoScroll ? 'true' : 'false'; ?>,
            timeout: <?php echo $timeout; ?>,
            pauseOnHover: <?php echo $pause_on_hover ? 'true' : 'false'; ?>
        };
       
        switch (selectedEffect) {
            case 'tile':
                sliderEffect = new TileSlider({
                    ...commonOptions,
                    effect: '<?php echo $tile_effect; ?>',
                    rows: <?php echo $rows; ?>,
                    rowOffset: <?php echo $row_offset; ?>
                });
                break;
            case 'cube':
                sliderEffect = new CubeSlider({
                    ...commonOptions,
                    direction: '<?php echo $direction; ?>'
                });
                break;
            case 'carousel':
                sliderEffect = new CarouselSlider({
                    ...commonOptions,
                    cover: <?php echo $cover ? 'true' : 'false'; ?>
                });
                break;
            case 'fade':
            default:
                sliderEffect = new FadeSlider({
                    ...commonOptions,
                    timingFunction: '<?php echo $timing_function; ?>'
                });
                break;
        }
       
        const slider = new BoxSlider(
            document.getElementById('boxslider-<?php echo $current_foogallery->ID; ?>'),
            sliderEffect
        );
    });
</script>

<style>
    #boxslider-<?php echo $current_foogallery->ID; ?> {
        width: 100%;
        height: 100%;
        position: relative;
        margin: 0 auto;
    }
    #boxslider-<?php echo $current_foogallery->ID; ?> .slide {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    #boxslider-<?php echo $current_foogallery->ID; ?> .slide img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
</style>