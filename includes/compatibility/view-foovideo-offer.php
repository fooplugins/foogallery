<?php
$show_upgrade = apply_filters('foogallery_foovideo_discount_offer_show_upgrade', true );
$message = apply_filters('foogallery_foovideo_discount_offer_message', '' );
?>
<style>
	div.about-wrap h2 {
		text-align: left;
	}

	.foogallery-help {
		margin-bottom: 10px;
	}
</style>
<script type="text/javascript">
	jQuery(function ($) {
		$('.foogallery-video-offer-container').on('click', '.foogallery-video-discount-offer', function (e) {
			e.preventDefault();

			//show the spinner
			$('.foogallery-video-discount-offer-spinner').addClass('is-active');

			var data = {
				action: 'foogallery_video_discount_offer',
				'_wpnonce' : $('#foogallery_video_discount_offer').val()
			};

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function(data) {
					$('.foogallery-video-offer-result').html(data);
				},
				complete: function() {
					$('.foogallery-video-discount-offer-spinner').removeClass('is-active');
				},
				error: function() {
					//something went wrong! Alert the user and reload the page
					alert('<?php esc_html_e( 'Something went wrong when retrieving the discount code.', 'foogallery' ); ?>');
				}
			});
		}).on('click', '.foogallery-video-discount-offer-support', function(e){
			e.preventDefault();

			$('.foogallery-video-discount-offer-support-container').slideDown();
		}).on('click', '.foogallery-video-discount-offer-support-submit', function(e){
			e.preventDefault();

			//show the spinner
			$('.foogallery-video-discount-offer-support-spinner').addClass('is-active');

			var data = {
				action: 'foogallery_video_discount_offer_support',
				'_wpnonce' : $('#foogallery_video_discount_offer_support').val(),
				'message' : $('#foogallery_video_discount_offer_support_message').val()
			};

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function(data) {
					alert(data);
				},
				complete: function() {
					$('.foogallery-video-discount-offer-support-spinner').removeClass('is-active');
				},
				error: function() {
					//something went wrong! Alert the user and reload the page
					alert('<?php esc_html_e( 'Something went wrong when sending the ticket.', 'foogallery' ); ?>');
				}
			});
		}).on('click', '.foogallery-video-discount-offer-not-interested', function(e){
			e.preventDefault();

			if ( confirm('Are you sure? You will not be able to redeem the offer again!') ) {
				var data = {
					action    : 'foogallery_video_discount_offer_hide',
					'_wpnonce': $('#foogallery_video_discount_offer_hide').val()
				};

				$.ajax({
					type    : "POST",
					url     : ajaxurl,
					data    : data,
					complete: function () {
						location.href = '<?php echo esc_url( foogallery_admin_settings_url() ); ?>';
					}
				});
			}
		});


	});
</script>
<div class="wrap about-wrap foogallery-video-offer-container">
	<h2><?php esc_html_e( 'FooGallery PRO Discount Offer', 'foogallery' ); ?></h2>

	<div class="foogallery-help">
		<?php esc_html_e('You are eligible for a FooGallery PRO discount, because you own a license for the FooVideo extension.', 'foogallery' ); ?>
	</div>
	<?php if ( $show_upgrade ) { ?>
	<h4><?php esc_html_e( 'Reasons to upgrade to FooGallery PRO:', 'foogallery' ); ?></h4>
	<ul class="ul-disc">
		<li><?php esc_html_e('FooGallery PRO now has all the features of FooVideo built-in.', 'foogallery'); ?></li>
		<li><?php esc_html_e('FooGallery PRO has support for more video providers.', 'foogallery'); ?></li>
		<li><?php esc_html_e('FooGallery PRO has better support for self-hosted videos.', 'foogallery'); ?></li>
		<li><?php esc_html_e('FooVideo will no longer be supported and updated!', 'foogallery'); ?></li>
	</ul>
	<?php } ?>
	<?php if ( !empty( $message ) ) { ?>
	<p><?php echo wp_kses_post( $message ); ?></p>
	<?php } ?>
	<p><?php esc_html_e('Click the "Redeem Now" button below to retrieve your discount code based on the FooVideo license you have entered for this site. If you have not entered your FooVideo license, please enter it on the FooGallery Settings page, under the Features tab, and then try again.','foogallery'); ?></p>

	<p>
		<?php esc_html_e('You can also log a support ticket through to our help desk if you are having any problems.', 'foogallery'); ?>
		<a class="foogallery-video-discount-offer-support" href="#logticket"><?php esc_html_e('Log a support ticket.', 'foogallery' ); ?></a>
	</p>

	<input type="submit" class="button button-primary foogallery-video-discount-offer" value="<?php esc_attr_e( 'Redeem Now!', 'foogallery'); ?>">

	<?php wp_nonce_field( 'foogallery_video_discount_offer', 'foogallery_video_discount_offer' ); ?>
	<div style="width:40px; display: inline-block;"><span class="foogallery-video-discount-offer-spinner spinner"></span></div>

	<input type="submit" class="button foogallery-video-discount-offer-not-interested" value="<?php esc_attr_e( 'I am no longer interested!', 'foogallery'); ?>">
	<?php wp_nonce_field( 'foogallery_video_discount_offer_hide', 'foogallery_video_discount_offer_hide' ); ?>

	<p class="foogallery-video-offer-result"></p>

	<div style="display: none" class="foogallery-video-discount-offer-support-container">
		<h4><?php esc_html_e('Log a Support Ticket', 'foogallery'); ?></h4>
		<p><?php esc_html_e('The following information will be included in your support ticket:', 'foogallery'); ?></p>
		<textarea id="foogallery_video_discount_offer_support_message" style="width: 600px; height: 100px">Site : <?php echo esc_url( home_url() ); ?>

Email : <?php
			$user = wp_get_current_user();
			echo esc_html( $user->get('user_email') );
		?>

FooVideo License Key : <?php echo esc_html( get_site_option( 'foo-video_licensekey' ) ); ?>

Message : I am an existing FooVideo customer - please contact me.</textarea>
		<br />
		<input type="submit" class="button foogallery-video-discount-offer-support-submit" value="<?php esc_attr_e( 'Log Support Ticket', 'foogallery'); ?>">
		<?php wp_nonce_field( 'foogallery_video_discount_offer_support', 'foogallery_video_discount_offer_support' ); ?>
		<div style="width:40px; display: inline-block;"><span class="foogallery-video-discount-offer-support-spinner spinner"></span></div>
	</div>
</div>
