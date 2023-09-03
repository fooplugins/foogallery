<?php 
$instance = FooGallery_Plugin::get_instance();
$api = new FooGallery_Extensions_API();

// Filter extensions based on user selection (active, inactive, lightbox, premium).
$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';
$extensions = $api->get_all_for_view();

$has_errors = false;

$show_message = safe_get_from_request('show_message');

if ('yes' === $show_message) {
    $result = get_transient(FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY);
    if ($result === false) {
        $result = null;
    }
}



// Define the counts for different statuses
$total_count = count($extensions);
$active_count = count(array_filter($extensions, function ($extension) {
    return isset($extension['is_active']) && $extension['is_active'];
}));
$inactive_count = count(array_filter($extensions, function ($extension) {
    return isset($extension['is_active']) && !$extension['is_active'];
}));
$lightbox_count = count(array_filter($extensions, function ($extension) {
    return in_array('lightbox', $extension['tags']);
}));
$premium_count = count(array_filter($extensions, function ($extension) {
    return in_array('Premium', $extension['categories']);
}));
?>

<style>
    .foogallery-text {
        font-size: 18px;
        margin: 10px 0;
    }
	/* Define the column widths */
    .column-icon {
        width: 5%;
    }

    .column-name {
        width: 30%;
    }

    .column-description {
        width: 60%;
    }
</style>
<div class="wrap foogallery-extensions">
    <h2>
        <?php printf(__('%s Features', 'foogallery'), foogallery_plugin_name()); ?>
        <span class="spinner"></span>
    </h2>

    <?php
    if (isset($result)) { ?>
        <div class="foogallery-message-<?php echo $result['type']; ?>">
            <p><?php echo $result['message']; ?></p>
        </div>
    <?php } ?>
    <hr />
</div>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <div class="foogallery-status-tabs">
        <a href="<?php echo admin_url('edit.php?post_type=foogallery&page=foogallery-extensions&status=all'); ?>"
            class="foogallery-status-tab <?php echo $status_filter === 'all' ? 'current' : ''; ?>"
            style=" text-decoration: none;">
            <?php printf(__('All (%d)', 'foogallery'), $total_count); ?></a> |
        <a href="<?php echo admin_url('edit.php?post_type=foogallery&page=foogallery-extensions&status=active'); ?>"
            class="foogallery-status-tab <?php echo $status_filter === 'active' ? 'current' : ''; ?>"
            style=" text-decoration: none;">
            <?php printf(__('Active (%d)', 'foogallery'), $active_count); ?></a> |
        <a href="<?php echo admin_url('edit.php?post_type=foogallery&page=foogallery-extensions&status=inactive'); ?>"
            class="foogallery-status-tab <?php echo $status_filter === 'inactive' ? 'current' : ''; ?>"
            style=" text-decoration: none;">
            <?php printf(__('Inactive (%d)', 'foogallery'), $inactive_count); ?></a> |
        <a href="<?php echo admin_url('edit.php?post_type=foogallery&page=foogallery-extensions&status=lightbox'); ?>"
            class="foogallery-status-tab <?php echo $status_filter === 'lightbox' ? 'current' : ''; ?>"
            style=" text-decoration: none;">
            <?php printf(__('Lightbox (%d)', 'foogallery'), $lightbox_count); ?></a> |
        <a href="<?php echo admin_url('edit.php?post_type=foogallery&page=foogallery-extensions&status=premium'); ?>"
            class="foogallery-status-tab <?php echo $status_filter === 'premium' ? 'current' : ''; ?>"
            style=" text-decoration: none;">
            <?php printf(__('Premium (%d)', 'foogallery'), $premium_count); ?></a>
    </div>

    <form method="get">
        <input type="hidden" name="post_type" value="foogallery" />
        <input type="hidden" name="page" value="foogallery-extensions" />
        <p class="search-box">
            <label class="screen-reader-text" for="extension-search-input">
                <?php _e('Search Extensions', 'foogallery'); ?>:</label>
            <input type="search" id="extension-search-input" placeholder="search features..."
                name="s" value="<?php echo esc_attr(isset($_REQUEST['s']) ? $_REQUEST['s'] : ''); ?>" />
        </p>
    </form>
</div>

<?php
if ($status_filter !== 'all') {
    $extensions = array_filter($extensions, function ($extension) use ($status_filter) {
        if ($status_filter === 'premium') {
            return in_array('Premium', $extension['categories']);
        } elseif ($status_filter === 'lightbox') {
            return in_array('lightbox', $extension['tags']);
        } elseif ($status_filter === 'active') {
            return isset($extension['is_active']) && $extension['is_active'];
        } elseif ($status_filter === 'inactive') {
            return isset($extension['is_active']) && !$extension['is_active'];
        }
        return false;
    });
}

// Define sortable columns
$sortable_columns = array(
    'name' => 'Name',
);

class Extensions_List_Table extends WP_List_Table {

    private $extensions;

    public function __construct($extensions) {
        parent::__construct(array(
            'singular' => 'extension',
            'plural'   => 'extensions',
            'ajax'     => false,
        ));

        $this->extensions = $extensions;
    }

    public function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        // Apply search filter
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        if (!empty($search)) {
            $this->extensions = array_filter($this->extensions, function ($extension) use ($search) {
                return stripos($extension['title'], $search) !== false || stripos($extension['description'], $search) !== false;
            });
        }

		// Apply sorting
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'name';
        $order = isset($_GET['order']) && in_array($_GET['order'], ['asc', 'desc']) ? $_GET['order'] : 'asc';

        if ($orderby === 'name') {
            usort($this->extensions, function ($a, $b) use ($order) {
                $result = strcmp($a['title'], $b['title']);
                return $order === 'asc' ? $result : -$result;
            });
        }

        $this->items = $this->extensions;
    }

    public function get_columns() {
        return array(
            'icon'          => 'Icon',
            'name'          => 'Name',
            'description'   => 'Description',
        );
    }

    public function column_default($item, $column_name) {
		switch ($column_name) {
			case 'icon':
				return '<span class="dashicons ' .  $item['dashicon'] . '"></span>';
	
			case 'name':
				$downloaded = isset( $item['downloaded'] ) && true === $item['downloaded'];
				$is_active = isset( $item['is_active'] ) && true === $item['is_active'];
				$has_errors = isset( $item['has_errors'] ) && true === $item['has_errors'];
	
				$actions = '';
				if ( ! $downloaded ) {
					$base_url = add_query_arg( array( 'extension' => $item['slug'], '_wpnonce' => wp_create_nonce( 'foogallery_extension_action') ) );
					$download_url = add_query_arg( 'action', 'download', $base_url );
					$actions .= '<a href="' . esc_url( $download_url ) . '">Download</a>';
				} elseif ( $is_active ) {
					$base_url = add_query_arg( array( 'extension' => $item['slug'], '_wpnonce' => wp_create_nonce( 'foogallery_extension_action') ) );
					$deactivate_url = add_query_arg( 'action', 'deactivate', $base_url );
					$actions .= '<a href="' . esc_url( $deactivate_url ) . '">Deactivate</a>';
				} else {
					$base_url = add_query_arg( array( 'extension' => $item['slug'], '_wpnonce' => wp_create_nonce( 'foogallery_extension_action') ) );
					$activate_url = add_query_arg( 'action', 'activate', $base_url );
					$actions .= '<a href="' . esc_url( $activate_url ) . '">Activate</a>';
				}
	
				return '<strong>' . $item['title'] . '</strong><br>' . $actions;
	
			case 'description':
				$external_link_url   = $item['external_link_url'];
				$external_link_text  = $item['external_link_text'];
	
				return $item['description'] . '<br><a href="' . esc_url($external_link_url) . '" target="_blank">' . esc_html($external_link_text) . '</a>';
	
			default:
				return isset($item[$column_name]) ? $item[$column_name] : '';
		}
	}
	

    public function get_sortable_columns() {
        return array(
            'name' => array('name', true),
        );
    }
}

$extensions_table = new Extensions_List_Table($extensions);
$extensions_table->prepare_items();
$extensions_table->display();
?>

<script>
    const searchInput = document.getElementById('extension-search-input');
    const extensionsTable = document.querySelector('.wp-list-table');

    searchInput.addEventListener('input', function () {
        const searchTerm = searchInput.value.toLowerCase();
        Array.from(extensionsTable.querySelectorAll('tbody tr')).forEach(function (row) {
            const titleCell = row.querySelector('td.column-name');
            if (titleCell) {
                const titleText = titleCell.textContent.toLowerCase();
                if (titleText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
</script>




