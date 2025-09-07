<?php
/*
 * FooGallery Trial Mode class
 */

if ( ! class_exists( 'FooGallery_Trial_Mode' ) ) {
	class FooGallery_Trial_Mode {

		private $current_plan;
		private $is_free;
		private $is_trial;
		private $plan_starter;
		private $plan_expert;
		private $plan_commerce;

		function __construct() {
			add_action( 'admin_init', array( $this, 'init_trial_mode' ) );
		}

		/**
		 * Returns true if the trial mode is enabled
		 * @return bool
		 */
		private function is_trial_mode_enabled() {
			return foogallery_get_setting( 'enable_trial_mode' ) === 'on';
		}

        /**
         * Initialize the trial mode
         */
		function init_trial_mode() {
			if ( $this->is_trial_mode_enabled() ) {
				// Determine current plan, and show promotions based on the current plan.
				$fs_instance = foogallery_fs();
				$this->current_plan = $fs_instance->get_plan_name();
				$this->is_free = $fs_instance->is_free_plan();
				$this->is_trial = $fs_instance->is_trial();

				$this->plan_starter = false;
				$this->plan_expert = false;
				$this->plan_commerce = false;

				if ( !$this->is_free ) {
					if ( FOOGALLERY_PRO_PLAN_STARTER === $this->current_plan ) {
						$this->plan_starter = true;
					} else if ( FOOGALLERY_PRO_PLAN_EXPERT == $this->current_plan ) {
						$this->plan_expert = true;
					} else if ( FOOGALLERY_PRO_PLAN_COMMERCE == $this->current_plan ) {
						$this->plan_commerce = true;
					}
				}

				add_filter( 'foogallery_gallery_templates', array( $this, 'change_gallery_templates' ), 999 );
			}
		}

		function change_gallery_templates( $templates ) {

			// add a pulse to the card, so it stands out.
			if ( array_key_exists( 'polaroid_new', $templates ) ) {
				$templates['polaroid_new']['html'] = '<div data-balloon="' . __( 'This layout is part of PRO Starter plan', 'foogallery' ) . '" class="pulse pro-starter"></div>';
			}
			if ( array_key_exists( 'foogridpro', $templates ) ) {
				$templates['foogridpro']['html'] = '<div data-balloon="' . __( 'This layout is part of PRO Starter plan', 'foogallery' ) . '" class="pulse pro-starter"></div>';
			}
			if ( array_key_exists( 'slider', $templates ) ) {
				$templates['slider']['html'] = '<div data-balloon="' . __( 'This layout is part of PRO Starter plan', 'foogallery' ) . '" class="pulse pro-starter"></div>';
			}
			if ( array_key_exists( 'product', $templates ) ) {
				$templates['product']['html'] = '<div data-balloon="' . __( 'This layout is part of PRO Commerce plan', 'foogallery') . '" class="pulse pro-commerce"></div>';
			}

			return $templates;
		}
	}
}