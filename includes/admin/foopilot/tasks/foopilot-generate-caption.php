<?php
/**
 * Class FooGallery_Admin_Foopilot_Generate_Caption
 *
 * This class generates HTML for the "Generate Caption" task in the FooPilot admin section.
 */
class FooGallery_Admin_Foopilot_Generate_Caption {
	/**
	 * Get HTML for generating captions.
	 *
	 * @return string HTML markup for generating captions.
	 */
	public static function get_foopilot_generate_caption_html() {
		ob_start();
		?>
		<span class="setting has-description" data-setting="foopilot-image-caption" style="margin-bottom: 8px;">
			<button class="foogallery-foopilot button button-primary button-large" style="width: 150px" data-task="caption"><?php esc_html_e( 'Generate', 'fogallery' ); ?></button>
		</span>
		<?php
		return ob_get_clean();
	}
}
