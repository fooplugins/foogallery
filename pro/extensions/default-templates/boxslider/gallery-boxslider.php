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

<script type="module">
    import { BoxSlider, FadeSlider, TileSlider, CubeSlider, CarouselSlider } from 'https://cdn.jsdelivr.net/npm/@boxslider/slider/+esm';

    class SliderManager {
        constructor(containerId, options) {
            this.containerId = containerId;
            this.options = options;
            this.slider = null;
            this.observer = null;
        }

        init() {
            this.createSlider();
            this.toggleControlVisibility();  // Toggle visibility based on showControls option
            if (this.options.showControls) {
                this.setupControlListeners();
            }
            this.setupTransitionListeners();
            this.observeContainerRemoval();
        }

        createSlider() {
            const sliderEffect = this.createSliderEffect();
            this.slider = new BoxSlider(
                document.getElementById(this.containerId),
                sliderEffect
            );
        }

        createSliderEffect() {
            const commonOptions = {
                speed: this.options.speed,
                swipe: this.options.swipe,
                autoScroll: this.options.autoScroll,
                timeout: this.options.timeout,
                pauseOnHover: this.options.pauseOnHover
            };

            switch (this.options.effect) {
                case 'tile':
                    return new TileSlider({
                        ...commonOptions,
                        effect: this.options.tileEffect,
                        rows: this.options.rows,
                        rowOffset: this.options.rowOffset
                    });
                case 'cube':
                    return new CubeSlider({
                        ...commonOptions,
                        direction: this.options.direction
                    });
                case 'carousel':
                    return new CarouselSlider({
                        ...commonOptions,
                        cover: this.options.cover
                    });
                case 'fade':
                default:
                    return new FadeSlider({
                        ...commonOptions,
                        timingFunction: this.options.timingFunction
                    });
            }
        }

        toggleControlVisibility() {
            const controls = document.querySelector('.boxslider-controls');
            if (controls) {
                controls.style.display = this.options.showControls ? 'block' : 'none';
            }
        }

        setupControlListeners() {
            this.addControlListener('.boxslider-prev', () => this.slider.prev());
            this.addControlListener('.boxslider-next', () => this.slider.next());
            this.addControlListener('.boxslider-play', () => this.slider.play());
            this.addControlListener('.boxslider-pause', () => this.slider.pause());
            this.updatePlayPauseButtonState();
        }

        addControlListener(selector, callback) {
            const element = document.querySelector(selector);
            if (element) {
                element.addEventListener('click', callback);
            }
        }

        updatePlayPauseButtonState() {
            const playButton = document.querySelector('.boxslider-play');
            const pauseButton = document.querySelector('.boxslider-pause');

            this.slider.addEventListener('play', () => this.togglePlayPauseButtons(playButton, pauseButton, true));
            this.slider.addEventListener('pause', () => this.togglePlayPauseButtons(playButton, pauseButton, false));

            this.togglePlayPauseButtons(playButton, pauseButton, this.options.autoScroll);
        }

        togglePlayPauseButtons(playButton, pauseButton, isPlaying) {
            if (playButton) playButton.style.display = isPlaying ? 'none' : 'inline-block';
            if (pauseButton) pauseButton.style.display = isPlaying ? 'inline-block' : 'none';
        }

        setupTransitionListeners() {
            this.slider.addEventListener('before', (data) => console.log('Transition starting', data));
            this.slider.addEventListener('after', (data) => console.log('Transition complete', data));
        }

        observeContainerRemoval() {
            this.observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList' && !document.body.contains(this.slider.container)) {
                        this.destroy();
                    }
                });
            });
            this.observer.observe(document.body, { childList: true, subtree: true });
        }

        destroy() {
            if (this.slider) {
                this.slider.destroy();
            }
            if (this.observer) {
                this.observer.disconnect();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
    const sliderElement = document.querySelector('.fg-boxslider');
    const galleryData = JSON.parse(sliderElement.getAttribute('data-foogallery'));

    const options = {
        containerId: sliderElement.querySelector('.boxslider').id,
        effect: galleryData.template.effect,
        speed: parseInt(galleryData.template.speed, 10),
        swipe: galleryData.template.swipe === "true",
        autoScroll: galleryData.template.autoScroll === "true",
        timeout: parseInt(galleryData.template.timeout, 10),
        pauseOnHover: galleryData.template.pauseOnHover === "true",
        showControls: galleryData.template.show_controls === "true",
        timingFunction: galleryData.template['timing-function'],
        tileEffect: galleryData.template['tile-effect'],
        rows: parseInt(galleryData.template.rows, 10),
        rowOffset: parseInt(galleryData.template.rowOffset, 10),
        direction: galleryData.template.direction,
        cover: galleryData.template.cover === "true"
    };

    const sliderManager = new SliderManager(options.containerId, options);
    sliderManager.init();
});
</script>
