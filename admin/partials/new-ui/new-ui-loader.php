<?php
/**
 * New UI Loader
 * 
 * Main entry point for loading the new admin UI.
 * Include this file from the main plugin or functions.php to activate the new UI.
 * 
 * Usage:
 *   include_once WP_PLUGIN_DIR . '/all-sources-images/admin/partials/new-ui/new-ui-loader.php';
 *
 * @package All_Sources_Images
 * @since 6.2.0
 */

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

// Prevent double loading
if ( defined( 'ASI_NEW_UI_LOADED' ) ) {
    return;
}
define( 'ASI_NEW_UI_LOADED', true );

// Get the directory of this file
$asi_new_ui_dir = plugin_dir_path( __FILE__ );

// Load menu registration
require_once $asi_new_ui_dir . 'new-ui-menus.php';

// Load asset enqueuing
require_once $asi_new_ui_dir . 'new-ui-assets.php';

/**
 * Initialize new UI with admin instance
 * 
 * Call this function from your admin class constructor or init hook
 * to ensure the admin instance is available for the new UI pages.
 * 
 * @param All_Sources_Images_Admin $admin_instance
 */
function asi_init_new_ui( $admin_instance = null ) {
    if ( $admin_instance && function_exists( 'asi_set_new_ui_admin' ) ) {
        asi_set_new_ui_admin( $admin_instance );
    }
}
