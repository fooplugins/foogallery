<?php

/**
 * Class to handle preview scenarios, where rest/ajax calls are made from a gallery that is in preview mode.
 */
if (! class_exists('FooGallery_Previews')) {

	class FooGallery_Previews
	{

		/**
		 * FooGallery_Previews constructor.
		 */
		function __construct()
		{
			//override gallery settings specifically for previews.

			if ($this->should_init()) {
				$this->safe_init();
			}

			add_action('rest_api_init', function () {
				if ($this->should_init()) {
					$this->safe_init();
				}
			});
		}

		/**
		 * Check if the preview functionality should be initialized.
		 */
		private function should_init()
		{
			//first, check if we are in the admin
			if (is_admin()) {
				return true;
			}

			//then, check if we are a rest request in admin
			if (foogallery_is_rest_request_from_admin()) {
				return true;
			}

			return false;
		}

		/**
		 * Safely init the preview functionality.
		 */
		private function safe_init()
		{
			if (has_filter('foogallery_instance_get_setting')) {
				return;
			}

			add_filter('foogallery_instance_get_setting', array($this, 'override_instance_get_setting_for_previews'), 10, 4);
			add_action('foogallery_preview_before_render', array($this, 'store_preview_data'), 10, 2);
		}

		/**
		 * Temporarily store the preview data, so it can be picked up by AJAX / Rest requests.
		 */
		public function store_preview_data($foogallery_id, $args)
		{
			//store transient for 5 minutes
			set_transient('foogallery_preview_data_' . $foogallery_id, $args, 60 * 5);
		}

		/**
		 * Override the gallery settings for previews.
		 */
		public function override_instance_get_setting_for_previews($value, $key, $default, $gallery)
		{
			if (!isset($gallery) || !is_a($gallery, 'FooGallery')) {
				return $value;
			}

			$gallery_id = intval($gallery->ID);
			if ($gallery_id === 0) {
				return $value;
			}

			$temp_preview_data = get_transient('foogallery_preview_data_' . $gallery_id);

			if (!is_array($temp_preview_data) || empty($temp_preview_data)) {
				return $value;
			}

			if (isset($temp_preview_data[$key])) {
				return $temp_preview_data[$key];
			}

			return $value;
		}
	}
}
