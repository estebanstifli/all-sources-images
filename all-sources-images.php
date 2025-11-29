<?php

/**
 *
 * @link              https://github.com/yourusername/all-sources-images
 * @since             1.0.0
 * @package           All_Sources_Images
 *
 * @wordpress-plugin
 * Plugin Name:       All Sources Images
 * Plugin URI:        https://github.com/yourusername/all-sources-images
 * Description:       Add stunning images to your posts effortlessly, as featured images or within content. All Sources Images retrieves them automatically from multiple image banks and AI services.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://yourwebsite.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       all-sources-images
 * Domain Path:       /languages
 *
 *
 *
 *
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

/**
 * Enable debug logging
 * Set to true to enable detailed logging to debug.log
 * 
 * @since 1.0.0
 */
if ( ! defined( 'ASI_DEBUG' ) ) {
    define( 'ASI_DEBUG', true );
}

if ( ! defined( 'ASI_DIAGNOSTIC_TOKEN' ) ) {
    define( 'ASI_DIAGNOSTIC_TOKEN', '' ); // Provide your own token via wp-config.php when needed
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'ALL_SOURCES_IMAGES_VERSION', '1.0.0' );

/**
 * Load helper functions
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/asi-helpers.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-all-sources-images-activator.php
 */
function asi_activate_plugin() {
    ASI_log( 'Plugin activation started', 'ACTIVATION' );
    try {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-all-sources-images-activator.php';
        All_Sources_Images_Activator::activate();
        ASI_log( 'Plugin activation completed successfully', 'ACTIVATION' );
    } catch ( Exception $e ) {
        ASI_log_error( 'Plugin activation failed', $e );
        throw $e;
    }
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-all-sources-images-deactivator.php
 */
function asi_deactivate_plugin() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-all-sources-images-deactivator.php';
    All_Sources_Images_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'asi_activate_plugin' );
register_deactivation_hook( __FILE__, 'asi_deactivate_plugin' );

/**
 * Log when plugin is successfully activated
 */
add_action( 'activated_plugin', function( $plugin ) {
    if ( $plugin === plugin_basename( __FILE__ ) ) {
        ASI_log( 'Plugin successfully activated by WordPress', 'ACTIVATION' );
        ASI_log( 'Current user ID: ' . get_current_user_id(), 'ACTIVATION' );
        ASI_log( 'User capabilities: ' . print_r( wp_get_current_user()->allcaps, true ), 'ACTIVATION' );
    }
} );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-all-sources-images.php';

/**
 * Add capabilities
 */
function ASI_add_capability() {
    // Don't log on every request
    // ASI_log_entry( 'ASI_add_capability' );
    
    $options = get_option( 'ASI_plugin_rights_settings' );
    
    // Administrators always have the capability
    $admin_role = get_role( 'administrator' );
    if ( $admin_role ) {
        $admin_role->add_cap( 'asi_manage', true );
    }
        // Manage other roles by adding or removing capabilities according to options
        $roles = array(
            'editor'      => 'rights_editor',
            'author'      => 'rights_author',
            'contributor' => 'rights_contributor',
            'subscriber'  => 'rights_subscriber',
        );
        foreach ( $roles as $role_name => $option_key ) {
            $role = get_role( $role_name );
            if ( $role ) {
                if ( isset( $options[$option_key] ) && $options[$option_key] === 'true' ) {
                    // Adds capacity if the option is enabled
                    $role->add_cap( 'asi_manage', true );
                } else {
                    // Removes the capacity if the option is deactivated
                    $role->remove_cap( 'asi_manage' );
                }
        }
    }
}

/**
 * Check hook wp_insert_post to fire functionalities
 *
 * @since    6.0.0
 */
function ASI_check_hook() {
}

/**
 * Check capabilities before launching the plugin main features
 *
 * @since    5.0.0
 */
function ASI_check_capability() {
    // Don't log on every request to avoid flooding the log
    // ASI_log_entry( 'ASI_check_capability' );
    
    $additional_check = false;
    // add capability for the cron
    if ( wp_doing_cron() || true == $additional_check ) {
        global $current_user;
        $current_user->add_cap( 'asi_manage' );
        ASI_log( 'Added asi_manage capability for cron', 'CAPABILITY' );
    }
    
    // CRITICAL: Always allow administrators, even if asi_manage capability not assigned
    // This prevents blocking the entire WordPress admin
    if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'asi_manage' ) ) {
        // User doesn't have permission - just don't load plugin features
        // DON'T block WordPress or show errors
        return;
    }
    
    // User has permission - initialize plugin
    try {
        $plugin = new All_Sources_Images();
        $plugin->run();
    } catch ( Exception $e ) {
        ASI_log_error( 'Failed to initialize plugin', $e );
    }
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    4.0.0
 */
function asi_run_plugin() {
    // User role & capacity
    add_action( 'init', 'ASI_add_capability', 1 );
    add_action( 'init', 'ASI_check_capability', 2 );
    // Features with hooks
    add_action( 'init', 'ASI_check_hook', 5 );
}

asi_run_plugin();

/**
 * Load New Admin UI
 * 
 * @since 6.2.0
 */
require_once plugin_dir_path( __FILE__ ) . 'admin/partials/new-ui/new-ui-loader.php';