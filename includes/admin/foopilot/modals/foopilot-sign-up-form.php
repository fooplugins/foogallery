<?php ?>
<div class="foogallery-foopilot-signup-form">
	<div class="foogallery-foopilot-signup-form-inner">
		<p><?php esc_html_e( 'Unlock the power of FooPilot! Sign up for free and get 20 credits to explore our service.', 'foogallery' ); ?></p>
		<form class="foogallery-foopilot-signup-form-inner-content">
			<div style="margin-bottom: 20px;">
				<input type="email" id="foopilot-email" name="email" placeholder="<?php echo esc_attr( __( 'Enter your email', 'foogallery' ) ); ?>" value="<?php echo esc_attr( foogallery_sanitize_javascript( wp_get_current_user()->user_email ) ); ?>" style="padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 250px;">
			</div>
			<button class="foogallery-foopilot-signup-form-inner-content-button button button-primary button-large" type="submit" style="padding: 10px 20px; background-color: #0073e6; color: #fff; border: none; border-radius: 5px; cursor: pointer;"><?php esc_html_e( 'Sign Up for free', 'foogallery' ); ?></button>
		</form>
	</div>
</div>
