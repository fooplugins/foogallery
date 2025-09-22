# AI Agent Instructions: Building WordPress.org Compliant Plugins

This document provides comprehensive instructions for AI agents to create WordPress plugins that meet all WordPress.org repository requirements and pass the plugin review process.

## Core Requirements

### 1. Plugin Header Structure

```php
<?php
/**
 * Plugin Name:       My Plugin Name
 * Plugin URI:        https://example.com/plugins/my-plugin/
 * Description:       Brief description of what the plugin does.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Author Name
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-plugin
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

**Critical Notes:**
- Plugin URI must be DIFFERENT from Author URI (WordPress.org requirement)
- License MUST be GPL v2 or later (or compatible)
- Text Domain must match the plugin slug
- Do NOT call `load_plugin_textdomain()` - WordPress.org handles this automatically

### 2. Security: Input Sanitization & Output Escaping

#### Input Sanitization
Always sanitize ALL input data:

```php
// Sanitize text input
$text = sanitize_text_field( $_POST['text_field'] );

// Sanitize email
$email = sanitize_email( $_POST['email'] );

// Sanitize textarea
$content = sanitize_textarea_field( $_POST['content'] );

// Sanitize URLs
$url = esc_url_raw( $_POST['url'] );

// Sanitize array of integers
$ids = array_map( 'absint', $_POST['ids'] );

// Sanitize HTML content (with allowed tags)
$html = wp_kses_post( $_POST['html_content'] );
```

#### Output Escaping
ALWAYS escape at the LAST possible moment before output:

```php
// Escape HTML
echo esc_html( $text );

// Escape attributes
echo '<input type="text" value="' . esc_attr( $value ) . '">';

// Escape URLs
echo '<a href="' . esc_url( $url ) . '">Link</a>';

// Escape JavaScript
echo '<script>var data = ' . wp_json_encode( $data ) . ';</script>';

// Escape textarea content
echo '<textarea>' . esc_textarea( $content ) . '</textarea>';

// For translatable strings with HTML
echo wp_kses_post( __( 'Text with <strong>HTML</strong>', 'text-domain' ) );
```

### 3. Nonces for Forms and AJAX

Always use nonces for form submissions and AJAX requests:

```php
// Creating a form with nonce
function render_form() {
    ?>
    <form method="post" action="">
        <?php wp_nonce_field( 'my_plugin_action', 'my_plugin_nonce' ); ?>
        <input type="text" name="user_input" />
        <input type="submit" value="Submit" />
    </form>
    <?php
}

// Verifying nonce on submission
function handle_form_submission() {
    // Check nonce
    if ( ! isset( $_POST['my_plugin_nonce'] ) || 
         ! wp_verify_nonce( $_POST['my_plugin_nonce'], 'my_plugin_action' ) ) {
        wp_die( __( 'Security check failed', 'text-domain' ) );
    }
    
    // Check capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Insufficient permissions', 'text-domain' ) );
    }
    
    // Process form...
    $input = sanitize_text_field( $_POST['user_input'] );
}

// AJAX with nonce
add_action( 'wp_ajax_my_plugin_action', 'handle_ajax' );
function handle_ajax() {
    check_ajax_referer( 'my_plugin_ajax_nonce', 'nonce' );
    
    // Process AJAX request...
    wp_send_json_success( $data );
}
```

### 4. Proper Class Structure with Namespaces

**IMPORTANT:** Namespaces must be unique and at least 4 characters long to avoid conflicts.

Use namespaces and PSR-4 autoloading:

```php
<?php
namespace MyAwesomePlugin\Admin;  // Good: Unique and > 4 chars
// namespace MyPlugin\Admin;       // Bad: Too generic, potential conflicts

/**
 * Admin functionality
 *
 * @package MyPlugin
 * @since   1.0.0
 */
class Settings {
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
    }
    
    /**
     * Add admin menu
     *
     * @since 1.0.0
     */
    public function add_menu() {
        add_options_page(
            __( 'My Plugin Settings', 'my-plugin' ),
            __( 'My Plugin', 'my-plugin' ),
            'manage_options',
            'my-plugin',
            array( $this, 'render_page' )
        );
    }
}
```

### 5. Composer Autoloading

Set up composer.json for classmap autoloading:

```json
{
    "name": "vendor/my-plugin",
    "description": "Plugin description",
    "type": "wordpress-plugin",
    "license": "GPL-2.0-or-later",
    "require": {
        "php": ">=7.4"
    },
    "autoload": {
        "classmap": [
            "includes/",
            "admin/",
            "public/"
        ],
        "files": [
            "includes/functions.php"
        ]
    }
}
```

### 6. Script and Style Enqueueing

NEVER directly include scripts. Always use wp_enqueue:

```php
/**
 * Enqueue admin scripts and styles
 */
function enqueue_admin_assets( $hook ) {
    // Only load on our plugin pages
    if ( 'toplevel_page_my-plugin' !== $hook ) {
        return;
    }
    
    // Use WordPress bundled libraries - NEVER download your own
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    
    // Enqueue plugin scripts
    wp_enqueue_script(
        'my-plugin-admin',
        plugin_dir_url( __FILE__ ) . 'assets/js/admin.js',
        array( 'jquery', 'wp-i18n' ),
        '1.0.0',
        true
    );
    
    // Add inline script for dynamic data
    wp_add_inline_script(
        'my-plugin-admin',
        'const myPluginData = ' . wp_json_encode( array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'my_plugin_ajax_nonce' ),
            'strings' => array(
                'confirm' => __( 'Are you sure?', 'my-plugin' ),
            ),
        ) ),
        'before'
    );
    
    // Enqueue styles
    wp_enqueue_style(
        'my-plugin-admin',
        plugin_dir_url( __FILE__ ) . 'assets/css/admin.css',
        array(),
        '1.0.0'
    );
}
add_action( 'admin_enqueue_scripts', 'enqueue_admin_assets' );

/**
 * Enqueue frontend assets
 */
function enqueue_public_assets() {
    // Only enqueue where needed
    if ( ! is_singular( 'my_post_type' ) ) {
        return;
    }
    
    wp_enqueue_script(
        'my-plugin-public',
        plugin_dir_url( __FILE__ ) . 'assets/js/public.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'enqueue_public_assets' );
```

### 7. Translation Best Practices

Include translator comments for context:

```php
/* translators: %s: User display name */
$message = sprintf( __( 'Welcome back, %s!', 'my-plugin' ), $user_name );

/* translators: 1: Opening link tag, 2: Closing link tag */
$text = sprintf( 
    __( 'Please %1$sclick here%2$s to continue.', 'my-plugin' ),
    '<a href="' . esc_url( $url ) . '">',
    '</a>'
);

// Plural forms
$text = sprintf(
    /* translators: %s: Number of items */
    _n(
        '%s item found',
        '%s items found',
        $count,
        'my-plugin'
    ),
    number_format_i18n( $count )
);
```

### 8. Database Operations

Use WordPress database API with proper prefixes:

```php
global $wpdb;

// Use placeholders for security
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}my_plugin_table WHERE user_id = %d",
        $user_id
    )
);

// Insert data
$wpdb->insert(
    $wpdb->prefix . 'my_plugin_table',
    array(
        'user_id' => $user_id,
        'data'    => $data,
    ),
    array( '%d', '%s' )
);
```

### 9. Trademark Compliance

**NEVER use these in plugin names or slugs:**
- WordPress (use WP instead)
- WooCommerce
- Google, Facebook, Twitter, etc. (unless official)
- Any trademarked names

**Acceptable:**
- "WP Plugin Name" instead of "WordPress Plugin Name"
- "Integration for WooCommerce" instead of "WooCommerce Extension"

### 11. Activation/Deactivation Hooks

```php
// Activation
register_activation_hook( __FILE__, array( 'MyPlugin\\Activator', 'activate' ) );

// Deactivation  
register_deactivation_hook( __FILE__, array( 'MyPlugin\\Deactivator', 'deactivate' ) );

// Uninstall (in uninstall.php)
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Clean up database, options, etc.
delete_option( 'my_plugin_options' );
```

### 12. PHPCS Configuration

Create phpcs.xml.dist:

```xml
<?xml version="1.0"?>
<ruleset name="WordPress Plugin Coding Standards">
    <description>WordPress Plugin Coding Standards</description>
    
    <rule ref="WordPress-Core"/>
    <rule ref="WordPress-Docs"/>
    <rule ref="WordPress-Extra"/>
    <rule ref="WordPress.WP.I18n">
        <properties>
            <property name="text_domain" type="array">
                <element value="my-plugin"/>
            </property>
        </properties>
    </rule>
    
    <rule ref="WordPress.NamingConventions.PrefixAllGlobals">
        <properties>
            <property name="prefixes" type="array">
                <element value="my_plugin"/>
                <element value="MyPlugin"/>
            </property>
        </properties>
    </rule>
</ruleset>
```

### 13. Common Review Failures to Avoid

1. **Direct file access:** Always check `defined( 'ABSPATH' )`
2. **Calling file operations directly:** Use WordPress Filesystem API
3. **Using curl/file_get_contents:** Use `wp_remote_get()` / `wp_remote_post()`
4. **Creating custom tables unnecessarily:** Use post types and taxonomies when possible
5. **Not checking capabilities:** Always verify user permissions
6. **Including tracking/analytics without opt-in:** Must be opt-in with clear notice
7. **Obfuscated code:** All code must be readable
8. **External service dependencies:** Must work without external services (graceful degradation)
9. **Improper use of wp_die():** Provide proper error messages
10. **Not internationalizing strings:** All user-facing text must be translatable

### 14. Options API Best Practices

```php
// Use a single option for all plugin settings
$options = get_option( 'my_plugin_options', array() );

// Register settings properly
function register_settings() {
    register_setting(
        'my_plugin_settings_group',
        'my_plugin_options',
        array(
            'sanitize_callback' => array( $this, 'sanitize_options' ),
            'default' => array(),
        )
    );
}

// Sanitize options
function sanitize_options( $options ) {
    $sanitized = array();
    
    if ( isset( $options['text_field'] ) ) {
        $sanitized['text_field'] = sanitize_text_field( $options['text_field'] );
    }
    
    if ( isset( $options['number_field'] ) ) {
        $sanitized['number_field'] = absint( $options['number_field'] );
    }
    
    return $sanitized;
}
```

### 15. AJAX Best Practices

```php
// Localize script with AJAX data
wp_localize_script( 'my-plugin-script', 'myPluginAjax', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'my-plugin-ajax-nonce' ),
) );

// Handle AJAX for logged-in users
add_action( 'wp_ajax_my_plugin_action', 'handle_ajax_request' );

// Handle AJAX for non-logged-in users (if needed)
add_action( 'wp_ajax_nopriv_my_plugin_action', 'handle_ajax_request' );

function handle_ajax_request() {
    // Verify nonce
    if ( ! check_ajax_referer( 'my-plugin-ajax-nonce', 'nonce', false ) ) {
        wp_send_json_error( __( 'Invalid security token', 'my-plugin' ) );
    }
    
    // Check capabilities
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( __( 'Insufficient permissions', 'my-plugin' ) );
    }
    
    // Process request
    $data = sanitize_text_field( $_POST['data'] );
    
    // Return response
    wp_send_json_success( array(
        'message' => __( 'Success!', 'my-plugin' ),
        'data'    => $processed_data,
    ) );
}
```

## Final Checklist

Before submission, ensure:

- [ ] All inputs are sanitized
- [ ] All outputs are escaped
- [ ] All forms use nonces
- [ ] All AJAX requests verify nonces
- [ ] User capabilities are checked
- [ ] All strings are internationalized
- [ ] No trademark violations
- [ ] Scripts/styles are enqueued properly
- [ ] Using WordPress bundled libraries (jQuery, etc.)
- [ ] No direct file operations
- [ ] Using WordPress HTTP API for remote requests
- [ ] Proper error handling with user-friendly messages
- [ ] Code passes PHPCS with WordPress standards
- [ ] Plugin works without external services
- [ ] Uninstall cleanup is implemented
- [ ] No unnecessary database queries
- [ ] Options are properly sanitized and validated
- [ ] Plugin header is complete and accurate
- [ ] readme.txt follows WordPress.org format

## Example Plugin Structure

```php
<?php
/**
 * Plugin Name:       My Awesome Plugin
 * Plugin URI:        https://example.com/plugins/my-awesome-plugin/
 * Description:       Does awesome things the WordPress way.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            John Doe
 * Author URI:        https://johndoe.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-awesome-plugin
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants with unique prefix (minimum 4 characters)
// BAD: MY_ is too generic and only 2 chars
// define( 'MY_PLUGIN_VERSION', '1.0.0' );

// GOOD: Unique prefix with 4+ characters
define( 'MYAWESOMEPLUGIN_VERSION', '1.0.0' );
define( 'MYAWESOMEPLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MYAWESOMEPLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Require Composer autoloader if it exists
if ( file_exists( MYAWESOMEPLUGIN_PATH . 'vendor/autoload.php' ) ) {
    require_once MYAWESOMEPLUGIN_PATH . 'vendor/autoload.php';
}

// Initialize the plugin (function name must also be unique)
add_action( 'plugins_loaded', 'myawesomeplugin_init' );
function myawesomeplugin_init() {
    // Initialize plugin classes with properly namespaced class
    new \MyAwesomePlugin\Plugin();
}
```

Remember: The goal is to create secure, efficient, and user-friendly plugins that follow WordPress best practices and will be accepted into the WordPress.org repository on first submission.