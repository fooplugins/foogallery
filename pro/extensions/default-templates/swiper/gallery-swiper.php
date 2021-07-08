<?php
/**
 * FooGallery Swiper PRO gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;

$columns = foogallery_gallery_template_setting( 'columns', 'swiper-cols-4' );

$foogallery_default_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'swiper', $columns );
$foogallery_default_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_default_classes ) );

if($_POST['action'] == 'foogallery_preview') {
    $current_foogallery->settings = $_POST['_foogallery_settings'];
}
?><div <?php echo $foogallery_default_attributes; ?>>
    <div class="swiper-container">
        <div class="swiper-wrapper">
            <?php foreach(foogallery_current_gallery_attachments_for_rendering() as $attachment) { ?>
                <div class="swiper-slide" role="group" style="width: <?php echo (int)$current_foogallery->settings['swiper_width_image_container']; ?>px !important; height: <?php echo (int)$current_foogallery->settings['swiper_height_image_container']; ?>px !important;">
                    <div class="card-image">
                        <img src="<?php echo esc_url($attachment->url); ?>" alt="<?php echo esc_attr($attachment->title); ?>" style="max-width: 100%;object-fit: <?php echo esc_attr($current_foogallery->settings['swiper_lightbox_thumbs']); ?>;height: <?php echo (int)$current_foogallery->settings['swiper_height_image_container'] ?>px;">
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php if(isset($current_foogallery->settings['swiper_lightbox_show_nav_buttons']) && $current_foogallery->settings['swiper_lightbox_show_nav_buttons'] == "yes") { ?>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
        <?php } ?>
        <div class="swiper-pagination"></div>
    </div>
</div>

<script>
    var swiper_slides_per_view = <?php echo isset($current_foogallery->settings['swiper_slides_per_view']) ? (int)$current_foogallery->settings['swiper_slides_per_view'] : 1; ?>;
    var swiper_space_between = <?php echo isset($current_foogallery->settings['swiper_space_between']) ? (int)$current_foogallery->settings['swiper_space_between'] : 0; ?>;
    var swiper_direction = "<?php echo isset($current_foogallery->settings['swiper_lightbox_transition']) ? htmlspecialchars($current_foogallery->settings['swiper_lightbox_transition']) : ''; ?>";
    var swiper_effect = "<?php echo isset($current_foogallery->settings['swiper_effect']) ? htmlspecialchars($current_foogallery->settings['swiper_effect']) : 'default'; ?>";
    var swiper_autoHeight = "<?php if(isset($current_foogallery->settings['swiper_lightbox_fit_media']) && $current_foogallery->settings['swiper_lightbox_fit_media'] == 'yes') { echo 'true'; } else { echo 'false'; } ?>";
    var swiper_height = <?php echo isset($current_foogallery->settings['swiper_height_image_container']) ? (int)$current_foogallery->settings['swiper_height_image_container'] : 'false'; ?>;
    <?php if(isset($current_foogallery->settings['swiper_lightbox_auto_progress']) && $current_foogallery->settings['swiper_lightbox_auto_progress'] == "yes") { ?>
        var swiper_autoplay = {
            delay: <?php echo (int)$current_foogallery->settings['swiper_lightbox_auto_progress_seconds'] * 1000 ?>,
            disableOnInteraction: false,
            pauseOnMouseEnter: false,
            stopOnLastSlide: false,
            waitForTransition: false
        }
    <?php } else { ?>
        var swiper_autoplay = false;
    <?php } ?>
    <?php if(isset($current_foogallery->settings['swiper_lightbox_show_thumbstrip_button']) && $current_foogallery->settings['swiper_lightbox_show_thumbstrip_button'] == "yes") { ?>
        var swiper_pagination = {
            el: '.swiper-pagination',
            type: 'bullets',
            clickable: true
        }
    <?php } else { ?>
        var swiper_pagination = false;
    <?php } ?>
    <?php if(isset($current_foogallery->settings['swiper_lightbox_show_nav_buttons']) && $current_foogallery->settings['swiper_lightbox_show_nav_buttons'] == "yes") { ?>
    var swiper_navigation = {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    }
    <?php } else { ?>
    var swiper_navigation = false;
    <?php } ?>
    <?php if(isset($current_foogallery->settings['swiper_lightbox_show_caption_button']) && $current_foogallery->settings['swiper_lightbox_show_caption_button'] == "yes") { ?>
    var swiper_grabcursor = true;
    <?php } else { ?>
    var swiper_grabcursor = false;
    <?php } ?>
    <?php if(isset($current_foogallery->settings['swiper_lightbox_show_maximize_button']) && $current_foogallery->settings['swiper_lightbox_show_maximize_button'] == "yes") { ?>
    var swiper_loop = true;
    <?php } else { ?>
    var swiper_loop = false;
    <?php } ?>
</script>

<style>
    .swiper-container {
        width: <?php echo isset($current_foogallery->settings['swiper_width_container']) ? (int)$current_foogallery->settings['swiper_width_container'].'px' : '100%'; ?> !important;
    }
</style>

<?php
// For Preview in Edit Gallery
if($_POST && $_POST['action'] == 'foogallery_preview') { ?>
    <style>
        .postbox-container .foogallery_preview_container {
            height: 100% !important;
            transform: unset !important;
        }
        .postbox-container .swiper-container-autoheight, .swiper-container-autoheight .swiper-slide {
            height: 100% !important;
        }
        .swiper-slide-active {
            z-index: 99;
        }
    </style>
    <script>
        if (typeof swiper_grabcursor !== 'undefined') {
            // Swiper Configuration
            var swiper = new Swiper(".swiper-container", {
                autoHeight: swiper_autoHeight,
                slidesPerView: swiper_slides_per_view,
                spaceBetween: swiper_space_between,
                height: swiper_height,
                effect: swiper_effect,
                direction: swiper_direction,
                grabCursor: swiper_grabcursor,
                loop: swiper_loop,
                pagination: swiper_pagination,
                autoplay: swiper_autoplay,
                navigation: swiper_navigation,
            });
        }
    </script>
<?php } ?>