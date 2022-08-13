<?php
global $wp_version;
/**
 * Get which version of GD is installed, if any.
 *
 * Returns the version (1 or 2) of the GD extension.
 */
function foogallery_gdversion() {
	if ( ! extension_loaded( 'gd' ) ) {
		return '0';
	}

	// Use the gd_info() function if possible.
	if ( function_exists( 'gd_info' ) ) {
		$ver_info = gd_info();
		preg_match( '/\d/', $ver_info['GD Version'], $match );

		return $match[0];
	}
	// If phpinfo() is disabled use a specified / fail-safe choice...
	if ( preg_match( '/phpinfo/', ini_get( 'disable_functions' ) ) ) {
		return '?';
	}
	// ...otherwise use phpinfo().
	ob_start();
	phpinfo( 8 );
	$info = ob_get_contents();
	ob_end_clean();
	$info = stristr( $info, 'gd version' );
	preg_match( '/\d/', $info, $match );

	return $match[0];
}

if ( current_user_can( 'activate_plugins' ) ) {
	$instance     = FooGallery_Plugin::get_instance();
	$info         = $instance->get_plugin_info();
	$title        = apply_filters( 'foogallery_admin_systeminfo_title', sprintf( __( '%s System Information', 'foogallery' ), foogallery_plugin_name() ) );
	$support_text = apply_filters( 'foogallery_admin_systeminfo_supporttext', sprintf( __( 'Below is some information about your server configuration. You can use this info to help debug issues you may have with %s.' ), foogallery_plugin_name() ) );
	$api          = new FooGallery_Extensions_API();

	//get all gallery templates
	$template_slugs = array();
	foreach ( foogallery_gallery_templates() as $template ) {
		$template_slugs[] = $template['slug'];
	}

	//get all activated plugins
	$plugins = array();
	foreach ( get_option('active_plugins') as $plugin_slug => $plugin ) {
		$plugins[] = $plugin;
	}

	$current_theme = wp_get_theme();

	$foogallery = FooGallery_Plugin::get_instance();
	$settings = $foogallery->options()->get_all();

	$stream_wrappers = stream_get_wrappers();

	$debug_info = array(
		__( 'FooGallery version', 'foogallery' )  			=> $info['version'],
		__( 'WordPress version', 'foogallery' )   			=> $wp_version,
		__( 'Activated Theme', 'foogallery' )     			=> $current_theme['Name'],
		__( 'WordPress URL', 'foogallery' )       			=> get_site_url(),
		__( 'PHP version', 'foogallery' )         			=> phpversion(),
		__( 'Thumb Engine', 'foogallery' )                  => foogallery_get_setting( 'thumb_engine' )
	);

	$extra_debug_info = array(
		__( 'PHP Open SSL', 'foogallery' )        			=> extension_loaded( 'openssl' ) ? __( 'Loaded', 'foogallery' ) : __( 'Not found!', 'foogallery' ),
		__( 'PHP HTTP Wrapper', 'foogallery' )    			=> in_array( 'http', $stream_wrappers ) ? __( 'Found', 'foogallery' ) : __( 'Not found!', 'foogallery' ),
		__( 'PHP HTTPS Wrapper', 'foogallery' )   			=> in_array( 'https', $stream_wrappers ) ? __( 'Found', 'foogallery' ) : __( 'Not found!', 'foogallery' ),
		__( 'PHP Config[allow_url_fopen]', 'foogallery' ) 	=> ini_get( 'allow_url_fopen' ),
		__( 'PHP Config[allow_url_include]', 'foogallery' ) => ini_get( 'allow_url_include' ),

		__( 'Features Active', 'foogallery' )   			=> array_keys( $api->get_active_extensions() ),
		__( 'Gallery Templates', 'foogallery' )   			=> $template_slugs,
		__( 'Lightboxes', 'foogallery' )          			=> apply_filters( 'foogallery_gallery_template_field_lightboxes', array() ),
		__( 'Settings', 'foogallery' )            			=> $settings,
		__( 'Active Plugins', 'foogallery' )      			=> $plugins
	);

	if ( foogallery_thumb_active_engine()->uses_image_editors() ) {
		$image_editor = _wp_image_editor_choose( array( 'methods' => array( 'get_image' ) ) );
		$test_image_url = foogallery_test_thumb_url();
		$test_image_url_scheme = parse_url( $test_image_url ,PHP_URL_SCHEME );
		$home_url_scheme = parse_url( home_url() ,PHP_URL_SCHEME );

		$debug_info[ __( 'PHP GD', 'foogallery' ) ] = extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ? __( 'Loaded', 'foogallery' ) . ' (V' . foogallery_gdversion() . ')' : __( 'Not found!', 'foogallery' );
		$debug_info[ __( 'PHP Imagick', 'foogallery' ) ] = extension_loaded( 'imagick' ) ? __( 'Loaded', 'foogallery' ) : __( 'Not found!', 'foogallery' );
		$debug_info[ __( 'WP Image Editor', 'foogallery' ) ] = $image_editor;
		$debug_info[ __( 'Thumbnail Generation Test', 'foogallery') ] = $test_image_url;
		$debug_info[ __( 'HTTPS Thumb Mismatch', 'foogallery' )] = $test_image_url_scheme === $home_url_scheme ? __( 'None', 'foogallery' ) : __( 'There is a protocol mismatch between your site URL and the thumbnail URL!', 'foogallery' );
		$debug_info[ __( 'Available Image Editors', 'foogallery' ) ] = apply_filters( 'wp_image_editors', array( 'WP_Image_Editor_Imagick', 'WP_Image_Editor_GD' ) );
	}

	$debug_info = array_merge( $debug_info, $extra_debug_info );

	$debug_info = apply_filters( 'foogallery_admin_debug_info', $debug_info );
	?>
	<style>
		.foogallery-debug {
			width: 100%;
			font-family: "courier new";
			height: 500px;
		}
	</style>
	<div class="wrap about-wrap">
		<h1><?php echo $title; ?></h1>
		<p><?php echo $support_text; ?></p>
    <textarea class="foogallery-debug">
<?php foreach ( $debug_info as $key => $value ) {
	echo $key . ' : ';
	print_r( $value );
	echo "\n";
} ?>
    </textarea>
	</div>
<?php }