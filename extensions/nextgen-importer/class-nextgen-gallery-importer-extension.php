<?php
/**
 * @TODO
 */
if ( ! class_exists( 'FooGallery_Nextgen_Gallery_Importer_Extension' ) ) {

	require_once 'class-nextgen-helper.php';
	require_once 'class-nextgen-import-progress.php';
	require_once 'class-nextgen-import-progress-album.php';

	class FooGallery_Nextgen_Gallery_Importer_Extension {

		/**
		 * @var FooGallery_NextGen_Helper
		 */
		private $nextgen;

		function __construct() {
			$this->nextgen = new FooGallery_NextGen_Helper();

			//always show the menu
			add_action( 'foogallery_admin_menu_after', array( $this, 'add_menu' ) );
			add_action( 'foogallery_extension_activated-nextgen', array( $this, 'add_menu' ) );

			//only do anything if NextGen is installed
			if ( $this->nextgen->is_nextgen_installed() ) {
				//hook into the foogallery menu
				add_action( 'foogallery_admin_help_after_section_one', array( $this, 'show_nextgen_import_help' ) );

				// Ajax calls for importing galleries
				add_action( 'wp_ajax_foogallery_nextgen_import', array( $this, 'ajax_nextgen_start_import' ) );
				add_action( 'wp_ajax_foogallery_nextgen_import_refresh', array(	$this, 'ajax_nextgen_continue_import' ) );
				add_action( 'wp_ajax_foogallery_nextgen_import_cancel', array( $this, 'ajax_nextgen_cancel_import' ) );
				add_action( 'wp_ajax_foogallery_nextgen_import_reset', array( $this, 'ajax_nextgen_reset_import' ) );

				// Ajax calls for importing albums
				add_action( 'wp_ajax_foogallery_nextgen_album_import_reset', array( $this, 'ajax_nextgen_reset_album_import' ) );
				add_action( 'wp_ajax_foogallery_nextgen_album_import', array( $this, 'ajax_nextgen_start_album_import' ) );
			}
		}

		function add_menu() {
			foogallery_add_submenu_page( __( 'NextGen Importer', 'foogallery' ), 'manage_options', 'foogallery-nextgen-importer', array(
					$this,
					'render_view',
				) );
		}

		function render_view() {
			require_once 'view-importer.php';
		}

		function ajax_nextgen_start_import() {
			if ( check_admin_referer( 'foogallery_nextgen_import', 'foogallery_nextgen_import' ) ) {

				$this->nextgen->ignore_previously_imported_galleries();

				if ( array_key_exists( 'nextgen-id', $_POST ) ) {

					$nextgen_gallery_ids = $_POST['nextgen-id'];

					foreach ( $nextgen_gallery_ids as $gid ) {
						$foogallery_title = stripslashes( $_POST[ 'foogallery-name-' . $gid ] );

						//init the start progress of the import for the gallery
						$this->nextgen->init_import_progress( $gid, $foogallery_title );
					}

					$this->nextgen->start_import();

				} else {

				}
			}

			$this->nextgen->render_import_form();

			die();

		}

		function ajax_nextgen_continue_import() {
			if ( check_admin_referer( 'foogallery_nextgen_import_refresh', 'foogallery_nextgen_import_refresh' ) ) {

				$this->nextgen->continue_import();

				$this->nextgen->render_import_form();

			}

			die();

		}

		function ajax_nextgen_cancel_import() {
			if ( check_admin_referer( 'foogallery_nextgen_import_cancel', 'foogallery_nextgen_import_cancel' ) ) {

				$this->nextgen->cancel_import();

				$this->nextgen->render_import_form();

			}
			die();
		}

		function ajax_nextgen_reset_import() {
			if ( check_admin_referer( 'foogallery_nextgen_reset', 'foogallery_nextgen_reset' ) ) {

				$this->nextgen->reset_import();

				$this->nextgen->render_import_form();

			}
			die();
		}

		function ajax_nextgen_start_album_import() {
			if ( check_admin_referer( 'foogallery_nextgen_album_import', 'foogallery_nextgen_album_import' ) ) {

				if ( array_key_exists( 'nextgen_album_id', $_POST ) ) {

					$nextgen_album_id = $_POST['nextgen_album_id'];
					$foogallery_album_title = stripslashes( $_POST[ 'foogallery_album_name' ] );

					//import the album
					$this->nextgen->import_album( $nextgen_album_id, $foogallery_album_title );

				} else {

				}
			}

			$this->nextgen->render_album_import_form();

			die();
		}

		function ajax_nextgen_reset_album_import() {
			if ( check_admin_referer( 'foogallery_nextgen_album_reset', 'foogallery_nextgen_album_reset' ) ) {

				//$this->nextgen->reset_import();

				$this->nextgen->render_album_import_form();

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



					<p><?php printf( __( 'Import all your NextGen galleries in a single click, or choose the galleries you would like to migrate over to %s.', 'foogallery' ), foogallery_plugin_name() ); ?></p>

					<h4><?php _e( 'Import Images', 'foogallery' ); ?></h4>

					<p><?php _e( 'NextGen gallery images are imported into your WordPress media library, where they should be!', 'foogallery' ); ?></p>

				</div>
			</div>
		<?php
		}
	}
}
