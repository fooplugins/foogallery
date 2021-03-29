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

			// Ajax calls for importing galleries
			add_action( 'wp_ajax_foogallery_nextgen_import', array( $this, 'ajax_nextgen_start_import' ) );
			add_action( 'wp_ajax_foogallery_nextgen_import_refresh', array(	$this, 'ajax_nextgen_continue_import' ) );
			add_action( 'wp_ajax_foogallery_nextgen_import_cancel', array( $this, 'ajax_nextgen_cancel_import' ) );
			add_action( 'wp_ajax_foogallery_nextgen_import_reset', array( $this, 'ajax_nextgen_reset_import' ) );

			// Ajax calls for importing albums
			add_action( 'wp_ajax_foogallery_nextgen_album_import_reset', array( $this, 'ajax_nextgen_reset_album_import' ) );
			add_action( 'wp_ajax_foogallery_nextgen_album_import', array( $this, 'ajax_nextgen_start_album_import' ) );

			// Ajax calls for converting shortcodes
			add_action( 'wp_ajax_foogallery_nextgen_find_shortcodes', array( $this, 'ajax_nextgen_find_shortcodes' ) );
			add_action( 'wp_ajax_foogallery_nextgen_replace_shortcodes', array( $this, 'ajax_nextgen_replace_shortcodes' ) );
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

		function ajax_nextgen_find_shortcodes() {
			if ( check_admin_referer( 'foogallery_nextgen_find_shortcodes' ) ) {
				$this->echo_findings_for_shortcode('nggallery', 'id');
				$this->echo_findings_for_shortcode('ngg_images', 'container_ids');
				$this->echo_findings_for_shortcode('imagebrowser', 'id');
				$this->echo_findings_for_shortcode('slideshow', 'id');

				?>
				<p>
				<input type="submit" class="button button-primary replace-shortcodes" value="<?php _e( 'Replace Shortcodes', 'foogallery' ); ?>">
				<?php wp_nonce_field( 'foogallery_nextgen_replace_shortcodes', 'foogallery_nextgen_replace_shortcodes' ); ?>
				<div style="width:40px; position: absolute;"><span class="spinner"></span></div>
				</p>
				<?php
			}
			die();
		}

		function ajax_nextgen_replace_shortcodes() {
			if ( check_admin_referer( 'foogallery_nextgen_replace_shortcodes' ) ) {
				$this->echo_replacements_for_shortcode('nggallery', 'id');
				$this->echo_replacements_for_shortcode('ngg_images', 'container_ids');
				$this->echo_replacements_for_shortcode('imagebrowser', 'id');
				$this->echo_replacements_for_shortcode('slideshow', 'id');
			}
			die();
		}

		function echo_findings_for_shortcode( $shortcode, $id_attrib ) {
			echo '<h3>[' . $shortcode . '] Shortcodes</h3>';

			$results = $this->find_posts_with_shortcode($shortcode, $id_attrib);

			$posts = array();
			$replacements = 0;
			$non_convertible = 0;
			foreach ($results as $result) {
				if ( !array_key_exists( $result->post_id, $posts ) ) {
					$posts[$result->post_id] = $result->post_id;
				}
				if ( isset( $result->foogallery_id ) ) {
					//a replacement is possible
					$replacements++;
				} else {
					$non_convertible++;
				}
			}
			if ( count($posts) > 0 ) {
				echo '<strong>' . count( $posts ) . '</strong> ' . __( 'posts found containing the shortcode', 'foogallery' ) . ' [' . $shortcode . ']<br>';
				if ( $replacements > 0 ) {
					echo '<strong>' . $replacements . '</strong> ' . __( 'shortcodes found that can be replaced!', 'foogallery' ) . '<br>';
				}
				if ( $non_convertible > 0 ) {
					echo '<strong>' . $non_convertible . '</strong> ' . __( 'shortcodes found that cannot be replaced, due to the gallery not being imported.', 'foogallery' ) . '<br>';
				}
			} else {
				echo __( 'NO posts were found containing the shortcode', 'foogallery' ) . ' [' . $shortcode . ']';
			}
		}

		function echo_replacements_for_shortcode( $shortcode, $id_attrib ) {
			echo '<h3>[' . $shortcode . '] Shortcodes</h3>';

			$results = $this->find_posts_with_shortcode($shortcode, $id_attrib);

			$replacements = 0;

			foreach ($results as $result) {
				if ( $result->foogallery_id > 0 ) {
					$content = str_replace( $result->original, $result->replacement, $result->post_content );

					$my_post = array(
						'ID'           => $result->post_id,
						'post_content' => $content,
					);

					//update the post in the database!
					wp_update_post( $my_post );
					$replacements++;
				}
			}
			if ( $replacements > 0 ) {
				echo '<strong>' . $replacements . '</strong> ' . __( 'shortcodes were replaced!', 'foogallery' ) . '<br>';
			} else {
				echo __( 'NO replacements were made for the shortcode', 'foogallery' ) . ' [' . $shortcode . ']';
			}
		}

		function find_posts_with_shortcode( $shortcode, $id_attrib ) {
			global $shortcode_tags;
			$temp_shortcode_tags = $shortcode_tags;
			$shortcode_tags      = array( $shortcode => '' );
			$regex               = '/' . get_shortcode_regex() . '/s';
			$shortcode_tags      = $temp_shortcode_tags;

			$posts = get_posts( array(
				'numberposts' => -1,
				'post_type' => 'any',
				's' => '[' . $shortcode,
			) );

			$results = array();

			if ( count( $posts ) > 0 ) {
				foreach ( $posts as $post ) {

					preg_match_all( $regex, $post->post_content, $matches );

					if ( isset( $matches[2] ) ) {
						foreach( $matches[2] as $key => $value ) {
							if ( $shortcode === $value ) {
								//we have found a shortcode match so store it
								$result = new stdClass();
								$result->post_id = $post->ID;
								$result->post_content = $post->post_content;
								$result->original = $matches[0][$key];
								$result->attributes = shortcode_parse_atts( $matches[3][$key] );
								if ( array_key_exists( $id_attrib, $result->attributes ) ) {
									$result->nextgen_id = intval( $result->attributes[$id_attrib] );

									$helper = new FooGallery_NextGen_Helper();
									$progress = $helper->get_import_progress( $result->nextgen_id );

									if ( $progress->foogallery_id > 0 ) {
										$result->foogallery_id = $progress->foogallery_id;
										$result->replacement = '[foogallery id="' . $result->foogallery_id . '"]';
									}
								}
								$results[] = $result;
							}
						}
					}
				}
			}

			return $results;
		}
	}
}
