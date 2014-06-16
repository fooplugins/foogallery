<?php
/**
 * @TODO
 */
if ( ! class_exists( 'FooGallery_Nextgen_Gallery_Importer_Extension' ) ) {

	require_once 'class-nextgen-helper.php';
	require_once 'class-nextgen-import-progress.php';

	class FooGallery_Nextgen_Gallery_Importer_Extension {

		private $nextgen;

		function __construct() {
			$nextgen = new FooGallery_NextGen_Helper();

			//only do anything if NextGen is installed
			if ( $nextgen->is_nextgen_installed() ) {
				//hook into the foogallery menu
				add_action( 'foogallery_admin_menu_after', array( $this, 'add_menu' ) );
				add_action( 'foogallery_extension_activated-nextgen', array( $this, 'add_menu' ) );
				add_action( 'foogallery_admin_help_after_section_one', array( $this, 'show_nextgen_import_help' ) );

				// Ajax calls for importing
				add_action( 'wp_ajax_foogallery_nextgen_import', array( $this, 'ajax_nextgen_start_import' ) );
				add_action( 'wp_ajax_foogallery_nextgen_import_refresh', array(
						$this,
						'ajax_nextgen_continue_import'
					) );
				add_action( 'wp_ajax_foogallery_nextgen_import_cancel', array( $this, 'ajax_nextgen_cancel_import' ) );
			}
		}

		function add_menu() {
			foogallery_add_submenu_page( __( 'NextGen Importer', 'foogallery' ), 'manage_options', 'foogallery-nextgen-importer', array(
					$this,
					'render_view'
				) );
		}

		function render_view() {
			require_once 'view-importer.php';
		}

		function ajax_nextgen_start_import() {
			if ( check_admin_referer( 'foogallery_nextgen_import', 'foogallery_nextgen_import' ) ) {

				$nextgen = new FooGallery_NextGen_Helper();

				if ( array_key_exists( 'nextgen-id', $_POST ) ) {

					$nextgen_gallery_ids = $_POST['nextgen-id'];

					foreach ( $nextgen_gallery_ids as $gid ) {
						$foogallery_title = stripslashes( $_POST[ 'foogallery-name-' . $gid ] );

						//init the start progress of the import for the gallery
						$nextgen->init_import_progress( $gid, $foogallery_title );
					}

					$nextgen->start_import();

				} else {

				}
			}

			$nextgen->render_import_form();

			die();

		}

		function ajax_nextgen_continue_import() {
			if ( check_admin_referer( 'foogallery_nextgen_import_refresh', 'foogallery_nextgen_import_refresh' ) ) {

				$nextgen = new FooGallery_NextGen_Helper();

				$nextgen->continue_import();

				$nextgen->render_import_form();

			}

			die();

		}

		function ajax_nextgen_cancel_import() {
			if ( check_admin_referer( 'foogallery_nextgen_import_cancel', 'foogallery_nextgen_import_cancel' ) ) {

				$nextgen = new FooGallery_NextGen_Helper();

				$nextgen->cancel_import();

				$nextgen->render_import_form();

			}


			die();
		}

		function show_nextgen_import_help() {
			?>
			<div class="changelog">

				<div class="feature-section">
					<img src="<?php echo FOOGALLERY_URL . 'assets/screenshots/admin-nextgen-import.jpg'; ?>"
					     class="foogallery-help-screenshot"/>

					<h2><?php _e( 'Import Your NextGen Galleries', 'foogallery' ); ?></h2>

					<h4><?php _e( 'Import Galleries', 'foogallery' ); ?></h4>

					<p><?php _e( 'Import all your NextGen galleries in a single click, or choose the galleries you would like to migrate over to FooGallery.', 'foogallery' ); ?></p>

					<h4><?php _e( 'Import Images', 'foogallery' ); ?></h4>

					<p><?php _e( 'NextGen gallery images are imported into your WordPress media library, where they should be!', 'foogallery' ); ?></p>

				</div>
			</div>
		<?php
		}
	}
}
