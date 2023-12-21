<?php

/**
 * Get the FooGallery plugin instance.
 *
 * @return FooGallery_Plugin The plugin instance.
 */
$instance = FooGallery_Plugin::get_instance();

/**
 * Create an instance of the FooGallery Extensions API.
 *
 * @var FooGallery_Extensions_API $api The Extensions API instance.
 */
$api = new FooGallery_Extensions_API();

/**
 * Filter extensions based on user selection (active, inactive, lightbox, premium).
 *
 * @var string $status_filter The selected status filter.
 */
$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';


/**
 * Get all extensions for view.
 *
 * @var array $extensions An array of extensions data.
 */
$extensions = $api->get_all_for_view();

/**
 * Flag indicating whether there are errors.
 *
 * @var bool $has_errors True if there are errors; otherwise, false.
 */
$has_errors = false;

/**
 * Show message flag.
 *
 * @var string $show_message Flag to indicate if a message should be shown.
 */
$show_message = safe_get_from_request( 'show_message' );

if ( 'yes' === $show_message ) {
	/**
	 * Result retrieved from a transient key.
	 *
	 * @var array|null $result The result retrieved from a transient key or null if not found.
	 */
	$result = get_transient( FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY );

	if ( $result === false ) {
		$result = null;
	}
}

/**
 * Total count of extensions.
 *
 * @var int $total_count The total count of extensions.
 */
$total_count = count( $extensions );

/**
 * Count of active extensions.
 *
 * @var int $active_count The count of active extensions.
 */
$active_count = count( array_filter( $extensions, function ( $extension ) {
	return isset( $extension['is_active'] ) && $extension['is_active'];
} ) );

/**
 * Count of inactive extensions.
 *
 * @var int $inactive_count The count of inactive extensions.
 */
$inactive_count = count( array_filter( $extensions, function ( $extension ) {
	return isset( $extension['is_active'] ) && !$extension['is_active'];
} ) );

/**
 * Count of extensions with 'Premium' category.
 *
 * @var int $premium_count The count of extensions with 'Premium' category.
 */
$premium_count = count( array_filter( $extensions, function ( $extension ) {
	return in_array( 'Premium', $extension['categories'] );
} ) );
?>

<style>
	.foogallery-text {
		font-size: 18px;
		margin: 10px 0;
	}
	/* Define the column widths */
	
	.column-name {
		width: 30%;
	}

	.column-description {
		width: 60%;
	}

	.dashicons {
    	font-size: 24px;
		vertical-align: middle;
	}
	.tablenav.top {
		display:none;
	}
</style>
<div class="wrap foogallery-features">
	<h2>
		<?php printf( __( '%s Features', 'foogallery' ), foogallery_plugin_name() ); ?>
		<span class="spinner"></span>
	</h2>

	<?php
	if ( isset( $result ) ) { ?>
		<div class="foogallery-message-<?php echo $result['type']; ?>">
			<p><?php echo $result['message']; ?></p>
		</div>
	<?php } ?>
	<hr />
</div>

<div style="display: flex; justify-content: space-between; align-items: center;">
	<div class="foogallery-status-tabs">
		<?php
		$status_tabs = array(
			'all' => __( 'All', 'foogallery' ),
			'active' => __( 'Active', 'foogallery' ),
			'inactive' => __( 'Inactive', 'foogallery' ),
			'premium' => __( 'Premium', 'foogallery' ),
		);

		foreach ( $status_tabs as $status_key => $status_label ) {
			$is_current = $status_filter === $status_key ? 'current' : '';
			$text_color = $is_current ? 'color: black;' : 'color: blue;';
			$status_url = add_query_arg( array( 'status' => $status_key ), foogallery_admin_features_url() );

			echo "<a href='{$status_url}' class='foogallery-status-tab {$is_current}' style='text-decoration: none; {$text_color}'>{$status_label} (";
			if ( $status_key === 'all' ) {
				echo $total_count;
			} elseif ( $status_key === 'active' ) {
				echo $active_count;
			} elseif ( $status_key === 'inactive' ) {
				echo $inactive_count;
			} elseif ( $status_key === 'premium' ) {
				echo $premium_count;
			}
			echo ")</a>";
			if ( $status_key !== 'premium' ) {
				echo ' | ';
			}
		}
		?>
	</div>

	<form method="get">
		<input type="hidden" name="post_type" value="foogallery" />
		<input type="hidden" name="page" value="foogallery-features" />
		<div style="display:flex; justify-content:space-evenly; align-items:center;">

			<p>
				<label for="tag-filter"><?php _e( 'Filter by Tag:', 'foogallery' ); ?></label>
				<select id="tag-filter" name="tag">
					<option value="all"><?php _e( 'All Tags', 'foogallery' ); ?></option>
					<?php
					// Get all unique tags from extensions data.
					$all_tags = array();
					foreach ( $extensions as $extension ) {
						if ( isset( $extension['tags'] ) ) {
							foreach ( $extension['tags'] as $tag ) {
								if ( !in_array( $tag, $all_tags ) ) {
									$all_tags[] = $tag;
								}
							}
						}
					}

					// Output options for each tag.
					foreach ( $all_tags as $tag ) {
						$selected = isset( $_GET['tag'] ) && $_GET['tag'] === $tag ? 'selected' : '';
						echo '<option value="' . esc_attr( $tag ) . '" ' . $selected . '>' . esc_html( $tag ) . '</option>';
					}
					?>
				</select>
			</p>

			<p class="search-box">
				<label class="screen-reader-text" for="extension-search-input">
					<?php _e( 'Search Extensions', 'foogallery' ); ?>:</label>
				<input type="search" id="extension-search-input" placeholder="Search features..."
					name="s" value="<?php echo esc_attr( isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '' ); ?>" />
			</p>
		</div>

	</form>

</div>

<?php
if ( $status_filter !== 'all' ) {
	$extensions = array_filter( $extensions, function ( $extension ) use ( $status_filter ) {
		if ( $status_filter === 'premium' ) {
			return in_array( 'Premium', $extension['categories'] );
		} elseif ( $status_filter === 'active' ) {
			return isset( $extension['is_active'] ) && $extension['is_active'];
		} elseif ( $status_filter === 'inactive' ) {
			return isset( $extension['is_active'] ) && !$extension['is_active'];
		}
		return false;
	} );
}

if ( isset( $_GET['tag'] ) && $_GET['tag'] !== 'all' ) {
	$tag_to_filter = sanitize_text_field( $_GET['tag'] );
	$extensions = array_filter( $extensions, function ( $extension ) use ( $tag_to_filter ) {
		return in_array( $tag_to_filter, $extension['tags'] );
	} );
}


// Define sortable columns.
$sortable_columns = array( 
	'name' => 'Name',
);

/**
 * Class FooGallery_Features_List_Table
 *
 * Custom table class to display FooGallery features in the WordPress admin.
 */
class FooGallery_Features_List_Table extends WP_List_Table {

	/**
	 * @var array $extensions An array of extensions data.
	 */
	private $extensions;

	/**
	 * FooGallery_Features_List_Table constructor.
	 *
	 * @param array $extensions An array of extensions data.
	 */
	public function __construct( $extensions ) {
		parent::__construct( array(
			'singular' => 'extension',
			'plural'   => 'extensions',
			'ajax'     => false,
		) );

		$this->extensions = $extensions;
	}

	/**
	 * Prepare the items for the table to display.
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Apply search filter
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
		if ( ! empty( $search ) ) {
			$this->extensions = array_filter( $this->extensions, function ( $extension ) use ( $search ) {
				return stripos( $extension['title'], $search ) !== false || stripos( $extension['description'], $search ) !== false;
			} );
		}

		// Apply sorting
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'name';
		$order   = isset( $_GET['order'] ) && in_array( $_GET['order'], [ 'asc', 'desc' ] ) ? $_GET['order'] : 'asc';

		if ( $orderby === 'name' ) {
			usort( $this->extensions, function ( $a, $b ) use ( $order ) {
				$result = strcmp( $a['title'], $b['title'] );
				return $order === 'asc' ? $result : -$result;
			} );
		}

		$this->items = $this->extensions;
	}

	/**
	 * Define the columns for the table.
	 *
	 * @return array An array of column names and labels.
	 */
	public function get_columns() {
		return array(
			'name' => 'Name',
			'description' => 'Description',
		);
	}


	/**
 * Render the default column output.
 *
 * @param array  $item         The item being displayed.
 * @param string $column_name  The name of the current column.
 *
 * @return mixed The rendered column content.
 */
public function column_default( $item, $column_name ) {
    switch ( $column_name ) {
        case 'name':
			$upgrade 	 = isset( $item['source'] ) && 'upgrade' === $item['source'];
            $downloaded  = isset( $item['downloaded'] ) && true === $item['downloaded'];
            $is_active   = isset( $item['is_active'] ) && true === $item['is_active'];
            $has_errors  = isset( $item['has_errors'] ) && true === $item['has_errors'];
            $actions     = '';
			if ( array_key_exists( 'actions_disabled', $item ) && $item['actions_disabled'] === true ) {
				//Do nothing - there should be no actions.
            } elseif ( $upgrade ) {
				$actions     .= '<a href="' . esc_url( foogallery_fs()->checkout_url( WP_FS__PERIOD_ANNUALLY, true ) ) . '">' . __( 'Start FREE Trial', 'foogallery' ) . '</a>';
			} elseif ( !$downloaded ) {
                $base_url     = add_query_arg( array( 'extension' => $item['slug'], '_wpnonce' => wp_create_nonce( 'foogallery_extension_action' ) ) );
                $download_url = add_query_arg( 'action', 'download', $base_url );
                $actions     .= '<a href="' . esc_url( $download_url ) . '">' . __( 'Download', 'foogallery' ) . '</a>';
            } elseif ( $is_active ) {
                $base_url         = add_query_arg( array( 'extension' => $item['slug'], '_wpnonce' => wp_create_nonce( 'foogallery_extension_action' ) ) );
                $deactivate_url   = add_query_arg( 'action', 'deactivate', $base_url );
                $actions         .= '<a href="' . esc_url( $deactivate_url ) . '">' . __( 'Deactivate', 'foogallery' ) . '</a>';
            } else {
                $base_url     = add_query_arg( array( 'extension' => $item['slug'], '_wpnonce' => wp_create_nonce( 'foogallery_extension_action' ) ) );
                $activate_url = add_query_arg( 'action', 'activate', $base_url );
                $actions     .= '<a href="' . esc_url( $activate_url ) . '">' . __( 'Activate', 'foogallery' ) . '</a>';
            }

            // Include the icon
            $icon = '<span class="dashicons ' . $item['dashicon'] . '" style="margin-right: 10px;"></span>';

            return $icon . ' <strong>' . $item['title'] . '</strong><br>' . ' <div style="margin-left: 35px;">' . $actions .'</div>';

        case 'description':
			$external_link = '';
			if ( array_key_exists( 'external_link_url', $item ) && array_key_exists( 'external_link_text', $item ) ) {
				$external_link = '<br><a href="' . esc_url( $item['external_link_url'] ) . '" target="_blank">' . esc_html( $item['external_link_text'] ) . '</a>';
			}

            return $item['description'] . $external_link;

        default:
            return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
    }
}


	/**
	 * Define sortable columns for the table.
	 *
	 * @return array An array of sortable column names.
	 */
	public function get_sortable_columns() {
		return array(
			'name' => array( 'name', true ),
		);
	}
}

$extensions_table = new FooGallery_Features_List_Table( $extensions );
$extensions_table->prepare_items();
$extensions_table->display();
?>

<script>
	const searchInput = document.getElementById( 'extension-search-input' );
	const extensionsTable = document.querySelector( '.wp-list-table' );
	const tagFilter = document.getElementById( 'tag-filter' );

	searchInput.addEventListener( 'input', function () {
		const searchTerm = searchInput.value.toLowerCase();
		Array.from( extensionsTable.querySelectorAll( 'tbody tr' ) ).forEach( function ( row ) {
			const titleCell = row.querySelector( 'td.column-name' );
			if ( titleCell ) {
				const titleText = titleCell.textContent.toLowerCase();
				if ( titleText.includes( searchTerm ) ) {
					row.style.display = '';
				} else {
					row.style.display = 'none';
				}
			}
		} );
	} );	

	function reloadPageWithFilter() {
		const selectedTag = tagFilter.value;
		const currentUrl = window.location.href;
		const url = new URL( currentUrl );
		url.searchParams.set( 'tag', selectedTag );
		window.location.href = url.toString();
	}

	tagFilter.addEventListener( 'change', reloadPageWithFilter );
</script>
