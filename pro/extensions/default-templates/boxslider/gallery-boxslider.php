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
$show_controls = foogallery_gallery_template_setting('show_controls', 'true') === 'true';

// Get language settings
$prev_text = esc_html(foogallery_get_setting('language_boxslider_prev_text', __('Prev', 'foogallery')));
$next_text = esc_html(foogallery_get_setting('language_boxslider_next_text', __('Next', 'foogallery')));
$play_text = esc_html(foogallery_get_setting('language_boxslider_play_text', __('Play', 'foogallery')));
$pause_text = esc_html(foogallery_get_setting('language_boxslider_pause_text', __('Pause', 'foogallery')));
?>

<div <?php echo $foogallery_default_attributes; ?> style="width: 100%; max-width: 800px; height: 400px; overflow: hidden; margin: 0 auto;">
    <div id="boxslider-<?php echo $current_foogallery->ID; ?>" class="boxslider">
        <?php foreach (foogallery_current_gallery_attachments_for_rendering() as $attachment) : ?>
            <div class="slide">
                <?php echo foogallery_attachment_html($attachment); ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ($show_controls) : ?>
    <div class="boxslider-controls">
        <button class="boxslider-prev"><?php echo esc_html($prev_text); ?></button>
        <button class="boxslider-next"><?php echo esc_html($next_text); ?></button>
        <button class="boxslider-play"><?php echo esc_html($play_text); ?></button>
        <button class="boxslider-pause"><?php echo esc_html($pause_text); ?></button>
    </div>
    <?php endif; ?>
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

        <?php if ($show_controls) : ?>
        // Add event listeners for control buttons
        document.querySelector('.boxslider-prev').addEventListener('click', () => slider.prev());
        document.querySelector('.boxslider-next').addEventListener('click', () => slider.next());
        document.querySelector('.boxslider-play').addEventListener('click', () => slider.play());
        document.querySelector('.boxslider-pause').addEventListener('click', () => slider.pause());

        // Update play/pause button text based on slider state
        slider.addEventListener('play', () => {
            document.querySelector('.boxslider-play').style.display = 'none';
            document.querySelector('.boxslider-pause').style.display = 'inline-block';
        });
        slider.addEventListener('pause', () => {
            document.querySelector('.boxslider-play').style.display = 'inline-block';
            document.querySelector('.boxslider-pause').style.display = 'none';
        });

        // Initialize play/pause button state
        if (<?php echo $autoScroll ? 'true' : 'false'; ?>) {
            document.querySelector('.boxslider-play').style.display = 'none';
        } else {
            document.querySelector('.boxslider-pause').style.display = 'none';
        }
        <?php endif; ?>

        // Add event listeners for before and after transitions
        slider.addEventListener('before', (data) => {
            console.log('Transition starting', data);
        });
        slider.addEventListener('after', (data) => {
            console.log('Transition complete', data);
        });

        // Destroy slider when the container is removed from the DOM
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' && !document.body.contains(slider.container)) {
                    slider.destroy();
                    observer.disconnect();
                }
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
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
    .boxslider-controls {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
    }
    .boxslider-controls button {
        margin: 0 5px;
        padding: 5px 10px;
        background-color: rgba(0,0,0,0.5);
        color: white;
        border: none;
        cursor: pointer;
    }
</style>