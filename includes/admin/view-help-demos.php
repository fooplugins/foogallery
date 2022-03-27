<?php

function foogallery_admin_help_demo_item( $seed, $width, $height, $title, $desc, $href ) {
	$placeholder = foogallery_get_svg_placeholder_image( $width, $height );
?><div class="fg-item fg-type-image fg-idle">
	<figure class="fg-item-inner">
		<a class="fg-thumb"
		   href="<?php echo $href; ?>"
		   data-type="image"
		   data-title="<?php echo $title; ?>"
		   data-description="<?php echo $desc; ?>">
            <span class="fg-image-wrap">
                <img class="fg-image"
                     src="<?php echo $placeholder; ?>"
                     data-src-fg="https://picsum.photos/seed/<?php echo $seed; ?>/<?php echo $width; ?>/<?php echo $height; ?>"
                     data-srcset-fg="https://picsum.photos/seed/<?php echo $seed; ?>/<?php echo ($width * 2); ?>/<?php echo ($height * 2); ?> 2x"
                     width="<?php echo $width; ?>"
                     height="<?php echo $height; ?>"
                     title="<?php echo $title; ?>"
                     alt="<?php echo $desc; ?>">
            </span>
			<span class="fg-image-overlay"></span>
		</a>
		<figcaption class="fg-caption">
			<div class="fg-caption-inner">
				<div class="fg-caption-title"><?php echo $title; ?></div>
				<?php if ( !empty( $desc ) ) {?>
				<div class="fg-caption-desc"><?php echo $desc; ?></div>
				<?php } ?>
			</div>
		</figcaption>
	</figure>
	<div class="fg-loader"></div>
</div>
<?php
}

?>

<div id="demos_section" class="foogallery-admin-help-section fgah-demo" style="display: none">
	<header>
		<h3><?php _e( 'Gallery Demos 😎', 'foogallery' );?></h3>
		<p><?php _e( 'Select a demo below to see it in action!', 'foogallery' ); ?></p>
		<a href="#foogallery-admin-help-demo-1" class="foogallery-admin-help-demo foogallery-admin-help-button foogallery-admin-help-button-active"><?php _e( 'Default', 'foogallery' ); ?></a>
		<a href="#foogallery-admin-help-demo-2" class="foogallery-admin-help-demo foogallery-admin-help-button"><?php _e( 'Masonry', 'foogallery' ); ?></a>
		<a href="#foogallery-admin-help-demo-3" class="foogallery-admin-help-demo foogallery-admin-help-button"><?php _e( 'Image Viewer', 'foogallery' ); ?></a>
		<a href="#foogallery-admin-help-demo-4" class="foogallery-admin-help-demo foogallery-admin-help-button"><?php _e( 'Justified', 'foogallery' ); ?></a>
		<a href="#foogallery-admin-help-demo-5" class="foogallery-admin-help-demo foogallery-admin-help-button"><?php _e( 'Simple Portfolio', 'foogallery' ); ?></a>
		<a href="#foogallery-admin-help-demo-6" class="foogallery-admin-help-demo foogallery-admin-help-button"><?php _e( 'Carousel', 'foogallery' ); ?></a>
		<a href="<?php echo esc_url( foogallery_admin_url( 'https://fooplugins.com/foogallery-wordpress-gallery-plugin/demos/', 'help' ) ); ?>" target="_blank" class="foogallery-admin-help-button"><?php _e( 'More Demos', 'foogallery' ); ?></a>
	</header>

	<div id="foogallery-admin-help-demo-1" class="foogallery-admin-help-demo-content">
		<header class="foogallery-admin-help-header">
			<h3 id="default_demo"><?php _e( 'Default Responsive Gallery Demo', 'foogallery' );?></h3>
			<p><?php _e( 'Our default responsive gallery template. You have full control over the image border, captions and hover effects. This demo has a thin white border with a small gutter. An icon and the captions are shown on hover. There is also a dark tint and zoom hover effect.', 'foogallery' );?></p>
		</header>

		<div id="foogallery-gallery-0" class="foogallery fg-default fg-center fg-hover-zoomed fg-gutter-10 fg-m-col2 fg-loading-default fg-loaded-fade-in fg-light fg-border-thin fg-shadow-outline fg-shadow-inset-small fg-caption-hover fg-hover-fade fg-hover-zoom"
		     data-foogallery="{&quot;lazy&quot;:true}">

			<?php foogallery_admin_help_demo_item( '001', 150, 150, 'Lorem Ipsum'        , 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae', '#default_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '016', 150, 150, 'Dolor Sit Amet'     , 'Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula.', '#default_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '003', 150, 150, 'Nulla Quis Lorem'   , 'Quisque velit nisi, pretium ut lacinia in, elementum id enim. Quisque velit nisi.', '#default_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '009', 150, 150, 'Quisque ut Libero'  , 'Pellentesque in ipsum id orci porta dapibus. Curabitur arcu erat.', '#default_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '005', 150, 150, 'Velit Nisi'         , 'Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui.', '#default_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '006', 150, 150, 'Vivamus Magna'      , 'Praesent sapien massa, convallis a pellentesque nec, egestas non nisi.', '#default_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '014', 150, 150, 'Lacinia Eget'       , 'Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem.', '#default_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '012', 150, 150, 'Consectetur Sed'    , 'Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula.', '#default_demo' ); ?>

		</div>
	</div>

	<div id="foogallery-admin-help-demo-2" class="foogallery-admin-help-demo-content" style="display: none">
		<header class="foogallery-admin-help-header">
			<h3 id="masonry_demo"><?php _e( 'Masonry Demo', 'foogallery' );?></h3>
			<p><?php _e( 'A masonry-style gallery template, which keeps all images at a constant width while packing them in to best fill the space. This demo has a thin white border and captions are below the image. Images also have hover effects.', 'foogallery' );?></p>
		</header>
		<style>
	        #foogallery-gallery-1.fg-masonry .fg-item {
	            width: 200px;
	            margin-right: 10px;
	            margin-bottom: 10px;
	        }
		</style>
		<div id="foogallery-gallery-1" class="foogallery fg-center fg-masonry fg-light fg-border-thin fg-shadow-small fg-loading-default fg-loaded-fade-in fg-hover-eye fg-captions-bottom fg-hover-fade fg-hover-plus fg-ready fg-fixed"
		     data-foogallery="{&quot;item&quot;:{&quot;showCaptionTitle&quot;:true,&quot;showCaptionDescription&quot;:true},&quot;lazy&quot;:true,&quot;template&quot;:{&quot;columnWidth&quot;:200,&quot;gutter&quot;:10}}">
			<div class="fg-column-width"></div>
			<div class="fg-gutter-width"></div>

			<?php foogallery_admin_help_demo_item( '1',  200, 300, 'Lorem Ipsum'        , 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae', '#masonry_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '4',  200, 200, 'Dolor Sit Amet'     , 'Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Donec sollicitudin molestie malesuada.', '#masonry_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '6',  200, 250, 'Nulla Quis Lorem'   , 'Quisque velit nisi, pretium ut lacinia in, elementum id enim. Quisque velit nisi, pretium ut lacinia in, elementum id enim.', '#masonry_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '9',  200, 220, 'Quisque ut Libero'  , 'Pellentesque in ipsum id orci porta dapibus. Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem.', '#masonry_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '16', 200, 250, 'Velit Nisi'         , 'Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Vivamus magna justo, lacinia eget consectetur sed, convallis at tellus.', '#masonry_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '15', 200, 300, 'Vivamus Magna'      , 'Praesent sapien massa, convallis a pellentesque nec, egestas non nisi.', '#masonry_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '12', 200, 150, 'Lacinia Eget'       , 'Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem. Vestibulum ante ipsum primis in faucibus orci luctus.', '#masonry_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '13', 200, 200, 'Consectetur Sed'    , 'Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula.', '#masonry_demo' ); ?>

		</div>
	</div>

	<div id="foogallery-admin-help-demo-3" class="foogallery-admin-help-demo-content" style="display: none">
		<header class="foogallery-admin-help-header">
			<h3 id="iv_demo"><?php _e( 'Image Viewer Demo', 'foogallery' );?></h3>
			<p><?php _e( 'Our image viewer gallery template, which showcases a single image at a time. In this demo, captions are always shown, and a white hover effect is also enabled.', 'foogallery' );?></p>
		</header>
		<div id="foogallery-gallery-2" class="foogallery foogallery-link-image fg-center fg-image-viewer fg-light fg-border-thin fg-shadow-outline fg-loading-default fg-loaded-fade-in fg-caption-always fg-hover-fade fg-hover-zoom fg-ready fg-light-overlays fg-round-small"
		     data-foogallery="{&quot;item&quot;:{&quot;showCaptionTitle&quot;:true,&quot;showCaptionDescription&quot;:true},&quot;lazy&quot;:true,&quot;template&quot;:{&quot;loop&quot;:true}}">
			<div class="fiv-inner">
				<div class="fiv-inner-container">
					<?php foogallery_admin_help_demo_item( '21',  500, 400, 'Lorem Ipsum'        , 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae', '#iv_demo' ); ?>
					<?php foogallery_admin_help_demo_item( '24',  500, 400, 'Dolor Sit Amet'     , 'Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Donec sollicitudin molestie malesuada.', '#iv_demo' ); ?>
					<?php foogallery_admin_help_demo_item( '26',  500, 400, 'Nulla Quis Lorem'   , 'Quisque velit nisi, pretium ut lacinia in, elementum id enim. Quisque velit nisi, pretium ut lacinia in, elementum id enim.', '#iv_demo' ); ?>
					<?php foogallery_admin_help_demo_item( '29',  500, 400, 'Quisque ut Libero'  , 'Pellentesque in ipsum id orci porta dapibus. Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem.', '#iv_demo' ); ?>
					<?php foogallery_admin_help_demo_item( '216', 500, 400, 'Velit Nisi'         , 'Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Vivamus magna justo, lacinia eget consectetur sed, convallis at tellus.', '#iv_demo' ); ?>
				</div>
				<div class="fiv-ctrls">
					<div class="fiv-prev"><span>Prev</span></div>
					<label class="fiv-count"><span class="fiv-count-current">1</span>of<span class="fiv-count-total">5</span></label>
					<div class="fiv-next"><span>Next</span></div>
				</div>
			</div>
		</div>
	</div>

	<div id="foogallery-admin-help-demo-4" class="foogallery-admin-help-demo-content" style="display: none">
		<header class="foogallery-admin-help-header">
			<h3 id="justified_demo"><?php _e( 'Justified Demo', 'foogallery' );?></h3>
			<p><?php _e( 'A justified gallery template, where the images have a similar height. This demo has no image borders, the captions are always visible and overlaid on top of the images. There is also a simple hover effect.', 'foogallery' );?></p>
		</header>
		<style>
	        #foogallery-gallery-3.fg-justified .fg-item {
	            margin-right: 1px;
	            margin-bottom: 1px;
	        }
	        #foogallery-gallery-3.fg-justified .fg-image {
	            height: 200px;
	        }
		</style>
		<div id="foogallery-gallery-3" class="foogallery foogallery-container foogallery-justified foogallery-lightbox-foogallery fg-justified fg-dark fg-loading-default fg-loaded-fade-in fg-caption-always fg-hover-fade fg-hover-zoom2 fg-ready"
		     data-foogallery="{&quot;item&quot;:{&quot;showCaptionTitle&quot;:true,&quot;showCaptionDescription&quot;:false},&quot;lazy&quot;:true,&quot;template&quot;:{&quot;rowHeight&quot;:200,&quot;maxRowHeight&quot;:250,&quot;margins&quot;:1,&quot;align&quot;:&quot;center&quot;}}">
			<div class="fg-column-width"></div>
			<div class="fg-gutter-width"></div>

			<?php foogallery_admin_help_demo_item( '31', 300, 250, 'Lorem Ipsum'        , '', '#justified_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '32', 150, 250, 'Dolor Sit Amet'     , '', '#justified_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '34', 200, 250, 'Nulla Quis Lorem'   , '', '#justified_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '37', 220, 250, 'Quisque ut Libero'  , '', '#justified_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '38', 250, 250, 'Velit Nisi'         , '', '#justified_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '39', 180, 250, 'Vivamus Magna'      , '', '#justified_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '310', 350, 250, 'Lacinia Eget'      , '', '#justified_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '313', 250, 250, 'Consectetur Sed'   , '', '#justified_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '314', 320, 250, 'Nisi Magna'        , '', '#justified_demo' ); ?>
		</div>
	</div>

	<div id="foogallery-admin-help-demo-5" class="foogallery-admin-help-demo-content" style="display: none">
		<header class="foogallery-admin-help-header">
			<h3 id="portfolio_demo"><?php _e( 'Simple Portfolio Gallery Demo', 'foogallery' );?></h3>
			<p><?php _e( 'A portfolio gallery template that keeps all items in a row at the same height. Captions are visible below the images and centered.', 'foogallery' );?></p>
		</header>
		<style>
	        #foogallery-gallery-4.fg-simple_portfolio {
	            justify-content: center;
	        }
	        #foogallery-gallery-4.fg-simple_portfolio .fg-item {
	            flex-basis: 250px;
	            margin: 5px;
	        }
		</style>
		<div id="foogallery-gallery-4" class="foogallery foogallery-container foogallery-simple_portfolio foogallery-lightbox-foogallery fg-simple_portfolio fg-light fg-border-thin fg-shadow-outline fg-loading-default fg-loaded-fade-in fg-caption-always fg-hover-fade fg-hover-zoom fg-c-c fg-ready fg-caption-always"
		     data-foogallery="{&quot;item&quot;:{&quot;showCaptionTitle&quot;:true,&quot;showCaptionDescription&quot;:true},&quot;lazy&quot;:true}">
			<?php foogallery_admin_help_demo_item( '41',  250, 200, 'Lorem Ipsum'        , 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae', '#portfolio_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '42',  250, 200, 'Dolor Sit Amet'     , 'Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Donec sollicitudin molestie malesuada.', '#portfolio_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '44',  250, 200, 'Nulla Quis Lorem'   , 'Quisque velit nisi, pretium ut lacinia in, elementum id enim. Quisque velit nisi, pretium ut lacinia in, elementum id enim.', '#portfolio_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '45',  250, 200, 'Quisque ut Libero'  , 'Pellentesque in ipsum id orci porta dapibus. Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem.', '#portfolio_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '46',  250, 200, 'Velit Nisi'         , 'Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Vivamus magna justo, lacinia eget consectetur sed, convallis at tellus.', '#portfolio_demo' ); ?>
			<?php foogallery_admin_help_demo_item( '47',  250, 200, 'Quis Dolor'         , 'Curabitur arcu erat, accumsan id imperdiet et. Vestibulum ac diam sit amet.', '#portfolio_demo' ); ?>
		</div>
	</div>

	<div id="foogallery-admin-help-demo-6" class="foogallery-admin-help-demo-content" style="display: none">
		<header class="foogallery-admin-help-header">
			<h3 id="portfolio_demo"><?php _e( 'Carousel Gallery Demo', 'foogallery' );?></h3>
			<p><?php _e( 'A responsive carousel gallery allows you to showcase your images in a single row.', 'foogallery' );?></p>
		</header>
		<div id="foogallery-gallery-5" class="foogallery foogallery-container foogallery-carousel foogallery-lightbox-foogallery fg-carousel fg-light fg-border-thin fg-shadow-outline fg-loading-default fg-loaded-fade-in fg-hover-fade fg-hover-zoom fg-c-c fg-ready"
		     data-foogallery="{&quot;item&quot;:{&quot;showCaptionTitle&quot;:true,&quot;showCaptionDescription&quot;:false},&quot;lazy&quot;:true,&quot;template&quot;:{&quot;maxItems&quot;:5,&quot;scale&quot;:0.12,&quot;gutter&quot;:{&quot;min&quot;:-40,&quot;max&quot;:-20,&quot;unit&quot;:&quot;%&quot;},&quot;autoplay&quot;:{&quot;time&quot;:6,&quot;interaction&quot;:&quot;pause&quot;},&quot;centerOnClick&quot;:true}}">
			<button type="button" class="fg-carousel-prev"></button>
			<div class="fg-carousel-inner">
				<div class="fg-carousel-center"></div>
				<?php foogallery_admin_help_demo_item( '48',  250, 200, 'Lorem Ipsum'        , 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae', '#portfolio_demo' ); ?>
				<?php foogallery_admin_help_demo_item( '49',  250, 200, 'Dolor Sit Amet'     , 'Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Donec sollicitudin molestie malesuada.', '#carousel_demo' ); ?>
				<?php foogallery_admin_help_demo_item( '50',  250, 200, 'Nulla Quis Lorem'   , 'Quisque velit nisi, pretium ut lacinia in, elementum id enim. Quisque velit nisi, pretium ut lacinia in, elementum id enim.', '#carousel_demo' ); ?>
				<?php foogallery_admin_help_demo_item( '51',  250, 200, 'Quisque ut Libero'  , 'Pellentesque in ipsum id orci porta dapibus. Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem.', '#carousel_demo' ); ?>
				<?php foogallery_admin_help_demo_item( '52',  250, 200, 'Velit Nisi'         , 'Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Vivamus magna justo, lacinia eget consectetur sed, convallis at tellus.', '#carousel_demo' ); ?>
				<?php foogallery_admin_help_demo_item( '53',  250, 200, 'Quis Dolor'         , 'Curabitur arcu erat, accumsan id imperdiet et. Vestibulum ac diam sit amet.', '#carousel_demo' ); ?>
			</div>
			<div class="fg-carousel-bottom"></div>
			<div class="fg-carousel-progress"></div>
			<button type="button" class="fg-carousel-next"></button>
		</div>
	</div>
</div>