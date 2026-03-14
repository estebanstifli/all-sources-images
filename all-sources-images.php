<?php

/**
 *
 * @link              https://github.com/estebanstifli/all-sources-images
 * @since             1.0.0
 * @package           All_Sources_Images
 *
 * @wordpress-plugin
 * Plugin Name:       All Sources Images
 * Plugin URI:        https://github.com/estebanstifli/all-sources-images
 * Description:       Generate stunning images for posts via AI (DALL·E, Stable Diffusion, etc) or image banks (Pexels, Unsplash, etc)
 * Version:           1.0.7
 * Author:            estebandezafra
 * Author URI:        https://github.com/estebanstifli
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       all-sources-images
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Tested up to:      6.9
 * Requires PHP:      7.4
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
if ( ! defined( 'ALLSI_DEBUG' ) ) {
    define( 'ALLSI_DEBUG', false );
}

if ( ! defined( 'ALLSI_DIAGNOSTIC_TOKEN' ) ) {
    define( 'ALLSI_DIAGNOSTIC_TOKEN', '' ); // Provide your own token via wp-config.php when needed
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'ALL_SOURCES_IMAGES_VERSION', '1.0.7' );

/**
 * Load helper functions
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/allsi-helpers.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-all-sources-images-activator.php
 */
function ALLSI_activate_plugin() {
    ALLSI_log( 'Plugin activation started', 'ACTIVATION' );
    try {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-all-sources-images-activator.php';
        All_Sources_Images_Activator::activate();
        ALLSI_log( 'Plugin activation completed successfully', 'ACTIVATION' );
    } catch ( Exception $e ) {
        ALLSI_log_error( 'Plugin activation failed', $e );
        throw $e;
    }
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-all-sources-images-deactivator.php
 */
function ALLSI_deactivate_plugin() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-all-sources-images-deactivator.php';
    All_Sources_Images_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'ALLSI_activate_plugin' );
register_deactivation_hook( __FILE__, 'ALLSI_deactivate_plugin' );

/**
 * Log when plugin is successfully activated
 */
add_action( 'activated_plugin', function( $plugin ) {
    if ( $plugin === plugin_basename( __FILE__ ) ) {
        ALLSI_log( 'Plugin successfully activated by WordPress', 'ACTIVATION' );
        ALLSI_log( 'Current user ID: ' . get_current_user_id(), 'ACTIVATION' );
        ALLSI_log( 'User capabilities: ' . wp_json_encode( wp_get_current_user()->allcaps ), 'ACTIVATION' );
    }
} );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-all-sources-images.php';

/**
 * WordPress Abilities API integration (WP 6.9+)
 * Must be loaded early to catch the wp_abilities_api_init hook.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-allsi-abilities.php';

/**
 * Add capabilities
 */
function ALLSI_add_capability() {
    // Don't log on every request
    // ALLSI_log_entry( 'ALLSI_add_capability' );
    
    $options = get_option( 'ALLSI_plugin_rights_settings' );
    
    // Administrators always have the capability
    $admin_role = get_role( 'administrator' );
    if ( $admin_role ) {
        $admin_role->add_cap( 'ALLSI_manage', true );
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
                    $role->add_cap( 'ALLSI_manage', true );
                } else {
                    // Removes the capacity if the option is deactivated
                    $role->remove_cap( 'ALLSI_manage' );
                }
        }
    }
}

/**
 * Check hook wp_insert_post to fire functionalities
 *
 * @since    6.0.0
 */
function ALLSI_check_hook() {
}

/**
 * Check capabilities before launching the plugin main features
 *
 * @since    5.0.0
 */
function ALLSI_check_capability() {
    // Don't log on every request to avoid flooding the log
    // ALLSI_log_entry( 'ALLSI_check_capability' );
    
    $additional_check = false;
    // add capability for the cron
    if ( wp_doing_cron() || true == $additional_check ) {
        global $current_user;
        $current_user->add_cap( 'ALLSI_manage' );
        ALLSI_log( 'Added ALLSI_manage capability for cron', 'CAPABILITY' );
    }
    
    // CRITICAL: Always allow administrators, even if ALLSI_manage capability not assigned
    // This prevents blocking the entire WordPress admin
    if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'ALLSI_manage' ) ) {
        // User doesn't have permission - just don't load plugin features
        // DON'T block WordPress or show errors
        return;
    }
    
    // User has permission - initialize plugin
    try {
        $plugin = new All_Sources_Images();
        $plugin->run();
    } catch ( Exception $e ) {
        ALLSI_log_error( 'Failed to initialize plugin', $e );
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
function ALLSI_run_plugin() {
    // User role & capacity
    add_action( 'init', 'ALLSI_add_capability', 1 );
    add_action( 'init', 'ALLSI_check_capability', 2 );
    // Features with hooks
    add_action( 'init', 'ALLSI_check_hook', 5 );
}

ALLSI_run_plugin();

/**
 * Load New Admin UI
 * 
 * @since 6.2.0
 */
require_once plugin_dir_path( __FILE__ ) . 'admin/partials/new-ui/new-ui-loader.php';