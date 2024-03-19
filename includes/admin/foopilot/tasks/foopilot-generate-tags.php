<?php
/**
 * Class FooGallery_Admin_Foopilot_Generate_Tags
 *
 * This class generates HTML for the "Generate Tags" task in the FooPilot admin section.
 */
class FooGallery_Admin_Foopilot_Generate_Tags {
	/**
	 * Get HTML for generating tags.
	 *
	 * @return string HTML markup for generating tags.
	 */
	public static function get_foopilot_generate_tags_html() {
		ob_start();
		?>
		<span class="setting has-description" data-setting="foopilot-image-tags" style="margin-bottom: 8px;">
			<button class="foogallery-foopilot button button-primary button-large" style="width: 150px" data-task="tags"><?php esc_html_e( 'Generate', 'fogallery' ); ?></button>
		</span>
		<?php
		return ob_get_clean();
	}
}
