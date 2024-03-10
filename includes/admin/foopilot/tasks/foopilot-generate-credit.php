<?php
/**
 * Class FooGallery_Admin_Foopilot_Generate_Credit
 *
 * This class generates HTML for the "Generate credit purchase page" task in the FooPilot admin section.
 */
class FooGallery_Admin_Foopilot_Generate_Credit {
	/**
	 * Get HTML for generating credit purchase page.
	 *
	 * @return string HTML markup for generating credit purchase page.
	 */
	public static function get_foopilot_generate_credit_html() {
		ob_start();
		?>
		<div class="foopilot-purchase-points">
			<h2><?php esc_html_e( 'Purchase Credits', 'fogallery' ); ?></h2>
			<p><?php esc_html_e( 'You can purchase credits to unlock foopilot features.', 'fogallery' ); ?></p>
			<form id="purchase-form">
				<label for="credit_amount"><?php esc_html_e( 'Select the number of credits to purchase:', 'fogallery' ); ?></label>
				<select name="credit_amount" id="credit_amount">
					<option value="10">10 <?php esc_html_e( 'credits - $0.99', 'fogallery' ); ?></option>
					<option value="20">20 <?php esc_html_e( 'credits - $1.49', 'fogallery' ); ?></option>
				</select>
				<button type="submit" class="button button-primary foopilot-purchase-points"><?php esc_html_e( 'Purchase Credits', 'fogallery' ); ?></button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
}
