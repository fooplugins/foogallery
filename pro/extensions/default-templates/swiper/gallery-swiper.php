<?php
/**
 * FooGallery Swiper PRO gallery template
 */
global $current_foogallery;
global $current_foogallery_arguments;

$columns = foogallery_gallery_template_setting( 'columns', 'swiper-cols-4' );

$foogallery_default_classes = foogallery_build_class_attribute_safe( $current_foogallery, 'swiper', $columns );
$foogallery_default_attributes = foogallery_build_container_attributes_safe( $current_foogallery, array( 'class' => $foogallery_default_classes ) );

?>
<div <?php echo $foogallery_default_attributes; ?>>
    <div class="swiper-container">
        <div class="swiper-wrapper">
            <?php foreach(foogallery_current_gallery_attachments_for_rendering() as $attachment) { ?>
                <div class="swiper-slide" role="group" style="width: <?= $current_foogallery->settings['swiper_width_image_container'] ?>px !important; height: <?= $current_foogallery->settings['swiper_height_image_container'] ?>px !important;">
                    <div class="card-image">
                        <img src="<?= $attachment->url ?>" alt="<?= $attachment->title ?>" style="max-width: 100%;object-fit: <?= $current_foogallery->settings['swiper_lightbox_thumbs'] ?>; height: <?= $current_foogallery->settings['swiper_height_image_container'] ?>px;">
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php if($current_foogallery->settings['swiper_lightbox_show_nav_buttons'] == "yes") { ?>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
        <?php } ?>
        <div class="swiper-pagination"></div>
    </div>
</div>

<script>
    var swiper_slides_per_view = "<?= $current_foogallery->settings['swiper_slides_per_view'] ?>";
    var swiper_space_between = "<?= $current_foogallery->settings['swiper_space_between'] ?>";
    var swiper_direction = "<?= $current_foogallery->settings['swiper_lightbox_transition'] ?>";
    var swiper_effect = "<?= $current_foogallery->settings['swiper_effect'] ?>";
    var swiper_autoHeight = "<?php if($current_foogallery->settings['swiper_lightbox_fit_media'] == 'yes') {echo 'true';} else {echo 'false';} ?>";
    var swiper_height = "<?= $current_foogallery->settings['swiper_height_container'] ?>";
    <?php if($current_foogallery->settings['swiper_lightbox_auto_progress'] == "yes") { ?>
        var swiper_autoplay = {
            delay: <?= intval($current_foogallery->settings['swiper_lightbox_auto_progress_seconds']) * 1000 ?>,
            disableOnInteraction: false,
            pauseOnMouseEnter: false,
            stopOnLastSlide: false,
            waitForTransition: false
        }
    <?php } else { ?>
        var swiper_autoplay = false;
    <?php } ?>
    <?php if($current_foogallery->settings['swiper_lightbox_show_thumbstrip_button'] == "yes") { ?>
        var swiper_pagination = {
            el: '.swiper-pagination',
            type: 'bullets',
            clickable: true
        }
    <?php } else { ?>
        var swiper_pagination = false;
    <?php } ?>
    <?php if($current_foogallery->settings['swiper_lightbox_show_nav_buttons'] == "yes") { ?>
    var swiper_navigation = {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    }
    <?php } else { ?>
    var swiper_navigation = false;
    <?php } ?>
    <?php if($current_foogallery->settings['swiper_lightbox_show_caption_button'] == "yes") { ?>
    var swiper_grabcursor = true;
    <?php } else { ?>
    var swiper_grabcursor = false;
    <?php } ?>
    <?php if($current_foogallery->settings['swiper_lightbox_show_maximize_button'] == "yes") { ?>
    var swiper_loop = true;
    <?php } else { ?>
    var swiper_loop = false;
    <?php } ?>
</script>
<?php
// For Preview in Edit Gallery
if($_POST['action'] == 'foogallery_preview') { ?>
    <style>
        .postbox-container .foogallery_preview_container {
            height: 100% !important;
            transform: unset !important;
        }
        .postbox-container .swiper-wrapper .swiper-slide {
            width: <?= $current_foogallery->settings['swiper_width_image_container'] ?>px !important;
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
                height: swiper_height,
                slidesPerView: swiper_slides_per_view,
            });
        }
    </script>
<?php } ?>