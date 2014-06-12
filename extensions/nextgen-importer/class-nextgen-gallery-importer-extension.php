<?php
/**
 * @TODO
 */
if ( !class_exists( 'FooGallery_Nextgen_Gallery_Importer_Extension' ) ) {

	require_once 'class-nextgen-helper.php';

	class FooGallery_Nextgen_Gallery_Importer_Extension {

		private $nextgen;

		function __construct() {
			$nextgen = new FooGallery_NextGen_Helper();

			//only do anything if NextGen is installed
			if ( $nextgen->is_nextgen_installed() ) {
				//hook into the foogallery menu
				add_action( 'foogallery_admin_menu_after', array($this, 'add_menu') );
				add_action( 'foogallery_extension_activated-nextgen', array($this, 'add_menu') );
				add_action( 'foogallery_admin_help_after_section_one', array($this, 'show_nextgen_import_help') );
			}
		}

		function add_menu() {
			foogallery_add_submenu_page(
				__( 'NextGen Importer', 'foogallery' ),
				'manage_options',
				'foogallery-nextgen-importer',
				array($this, 'render_view')
			);
		}

		function render_view() {
			require_once 'view-importer.php';
		}

		function show_nextgen_import_help() {
?>
			<div class="changelog">

				<div class="feature-section">
					<img src="<?php echo FOOGALLERY_URL . 'assets/screenshots/admin-nextgen-import.jpg'; ?>" class="foogallery-help-screenshot"/>

					<h2><?php _e( 'Import Your NextGen Galleries', 'foogallery' );?></h2>

					<h4><?php _e( 'Import Galleries', 'foogallery' );?></h4>
					<p><?php _e( 'Import all your NextGen galleries in a single click, or choose the galleries you would like to migrate over to FooGallery.', 'foogallery'); ?></p>

					<h4><?php _e( 'Import Images', 'foogallery' );?></h4>
					<p><?php _e( 'NextGen gallery images are imported into your WordPress media library, where they should be!', 'foogallery' );?></p>

				</div>
			</div>
<?php	}
	}
}
