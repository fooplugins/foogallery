<?php
$instance = FooGallery_Plugin::get_instance();
$api      = new FooGallery_Extensions_API();

$extensions = $api->get_all_for_view();
$has_errors = $api->has_extension_loading_errors();
$categories = $api->get_all_categories();

$show_message = safe_get_from_request( 'show_message' );

if ( 'yes' === $show_message ) {
	$result = get_transient( FOOGALLERY_EXTENSIONS_MESSAGE_TRANSIENT_KEY );
}

$tagline = apply_filters( 'foogallery_admin_extensions_tagline', sprintf( __( 'Extensions make %s even more awesome, without bloating the core plugin.', 'foogallery' ), foogallery_plugin_name() ) );
$show_foobot = apply_filters( 'foogallery_admin_show_foobot', true );
?>
<style>
	.foogallery-badge-foobot {
		position: absolute;
		top: 5px;
		right: 0;
		background: url(<?php echo FOOGALLERY_URL; ?>assets/foobot_small.png) no-repeat;
		width: 82px;
		height: 150px;
		z-index: 100;
	}
	.foogallery-text {
		font-size: 18px;
		margin: 10px 0;
	}
</style>
<div class="wrap about-wrap extensions-wrap">
<?php
if ( isset( $result ) ) { ?>
	<div class="foogallery-message-<?php echo $result['type']; ?>">
		<p><?php echo $result['message']; ?></p>
	</div>
<?php }
if ( $has_errors ) { ?>
	<div class="foogallery-message-error">
		<p><?php _e( 'There was a problem loading all the public extensions! Only the default bundled extensions will be shown.', 'foogallery' ); ?></p>
	</div>
<?php } ?>
	<h1><?php printf( __( '%s Extensions', 'foogallery' ), foogallery_plugin_name() ); ?><span class="spinner"></span></h1>

	<div class="foogallery-text"><?php echo $tagline; ?></div>
	<?php if ( $show_foobot ) { ?><div class="foogallery-badge-foobot"></div><?php } ?>

	<h2 class="foo-nav-tabs nav-tab-wrapper">
	<?php
	foreach ( $categories as $category_slug => $category ) {
		echo "<a href=\"#{$category_slug}\" class=\"nav-tab nav-tab-{$category_slug}\">{$category['name']}</a>";
	} ?>
		<div class="extension-search-box">
			<label class="screen-reader-text" for="plugin-search-input">Search Extensions:</label>
			<input placeholder="<?php echo __( 'Search extensions...', 'foogallery' ); ?>" type="search" id="extensions-search-input">
		</div>
		<div class="extension-reload">
			<a class="ext_action button" href="<?php echo esc_url( add_query_arg( 'action', 'reload' ) ); ?>"><span class="dashicons dashicons-update"></span> <?php _e( 'Reload', 'foogallery' ); ?></a>
		</div>
	</h2>
</div>

<div class="foogallery-extension-browser">
	<div class="extensions">
		<?php foreach ( $extensions as $extension ) {
			$slug = $extension['slug'];
			$classes = array('extension', 'all', 'extension-' . $slug);

			$downloaded = $api->is_downloaded( $extension );
			$is_active = $api->is_active( $slug );
			$has_errors = $api->has_errors( $slug );

			$banner_text = '';

			if ( $downloaded ) {
				$classes[] = 'downloaded';
			} else {
				$classes[] = 'download';
			}

			if ( $downloaded && $is_active ) {
				$classes[] = 'activated';
				$banner_text = __( 'Activated', 'foogallery' );
			}

			if ( $has_errors ) {
				$classes[] = 'has_error';
				$banner_text = $api->get_error_message( $slug );
			}

			$tag_html = '';
			if ( isset( $extension['tags'] ) ) {
				foreach ( $extension['tags'] as $tag ) {
					$classes[] = $tag;
					$tag_html .= '<span class="tag ' . $tag . '">'. $tag . '</span>';
				}
			}

			foreach ( $extension['categories'] as $category ) {
				$classes[] = foo_convert_to_key( $category );
			}

			if ( isset( $extension['css_class'] ) ) {
				$classes[] = $extension['css_class'];
			}

			$thumbnail = $extension['thumbnail'];
			if ( foo_starts_with( $thumbnail, '/') ) {
				$thumbnail = rtrim( FOOGALLERY_URL, '/' ) . $thumbnail;
			}
			$base_url = add_query_arg( 'extension', $slug );
			$download_url = add_query_arg( 'action', 'download', $base_url );
			$activate_url = add_query_arg( 'action', 'activate', $base_url );
			$deactivate_url = add_query_arg( 'action', 'deactivate', $base_url );

			$download_button_html = '';

			//check if we want to override the download button
			if ( isset( $extension['download_button'] ) ) {
				$download_button = $extension['download_button'];
				$download_button_href = esc_url( isset( $download_button['href'] ) ? $download_button['href'] : $download_url );
				$download_button_target = isset( $download_button['target'] ) ? ' target="' . $download_button['target'] . '" ' : '';
				$download_button_text = isset( $download_button['text'] ) ? __( $download_button['text'], 'foogallery' ) : '';
				$download_button_banner_text = ' data-banner-text="' . (isset( $download_button['banner-text'] ) ? $download_button['banner-text'] : __( 'downloading...', 'foogallery')) . '"';
				$download_button_confirm = isset( $download_button['confirm'] ) ? ' data-confirm="' .$download_button['confirm'] . '" ' : '';
				$download_button_html = "<a class=\"ext_action button button-primary download\" {$download_button_banner_text} {$download_button_target} href=\"{$download_button_href}\" >{$download_button_text}</a>";
			}
			?>
		<div class="<?php echo implode(' ', $classes); ?>">

			<div class="screenshot" style="background: url(<?php echo $thumbnail; ?>) no-repeat"></div>

			<div class="extension-details">
				<p class="search-me"><?php echo $extension['description']; ?></p>
				<a target="_blank" href="<?php echo esc_url( $extension['author_url'] ); ?>">By <?php echo $extension['author']; ?></a>
			</div>

			<h3 class="search-me"><?php echo $extension['title'] . $tag_html; ?></h3>

			<div class="extension-actions">
				<?php if ( ! empty( $download_button_html ) ) {
					echo $download_button_html;
				} else { ?>
				<a class="ext_action button button-primary download" data-banner-text="<?php _e( 'downloading...', 'foogallery'); ?>" data-confirm="<?php _e( 'Are you sure you want to download this extension?', 'foogallery' ); ?>" href="<?php echo esc_url( $download_url ); ?>"><?php _e( 'Download', 'foogallery' ); ?></a>
				<?php } ?>
				<a class="ext_action button button-primary activate" data-banner-text="<?php _e( 'activating...', 'foogallery'); ?>" href="<?php echo esc_url( $activate_url ); ?>"><?php _e( 'Activate', 'foogallery' ); ?></a>
				<a class="ext_action button button-secondary deactivate" data-banner-text="<?php _e( 'deactivating...', 'foogallery'); ?>" href="<?php echo esc_url( $deactivate_url ); ?>"><?php _e( 'Deactivate', 'foogallery' ); ?></a>
			</div>
			<div class="banner"><?php echo $banner_text; ?></div>
		</div>
		<?php } ?>
	</div>
	<?php
	$hide_build_your_own_tab = foogallery_get_setting( 'whitelabel_extensions_hide_build_your_own' );
	if ( 'on' != $hide_build_your_own_tab ) { ?>
	<div class="extension-page extension-page-build_your_own">
		<?php include 'view-extensions-build-your-own.php'; ?>
	</div>
	<?php } ?>
</div>
