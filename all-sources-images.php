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
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'ALL_SOURCES_IMAGES_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-all-sources-images-activator.php
 */
function activate_all_sources_images() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-all-sources-images-activator.php';
    All_Sources_Images_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-all-sources-images-deactivator.php
 */
function deactivate_all_sources_images() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-all-sources-images-deactivator.php';
    All_Sources_Images_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_all_sources_images' );
register_deactivation_hook( __FILE__, 'deactivate_all_sources_images' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-all-sources-images.php';

/**
 * Add capabilities
 */
function ASI_add_capability() {
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
        $additional_check = false;
        // add capability for the cron
        if ( wp_doing_cron() || true == $additional_check ) {
            global $current_user;
            $current_user->add_cap( 'asi_manage' );
        }
        if ( current_user_can( 'asi_manage' ) ) {
            $plugin = new All_Sources_Images();
        $plugin->run();
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
function run_all_sources_images() {
    // User role & capacity
    add_action( 'init', 'ASI_add_capability', 1 );
    add_action( 'init', 'ASI_check_capability', 2 );
    // Features with hooks
    add_action( 'init', 'ASI_check_hook', 5 );
}

run_all_sources_images();