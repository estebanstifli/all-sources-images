<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * New UI Menu Registration
 * 
 * This file registers the new admin menu pages for the redesigned UI.
 * To activate, add this line in class-all-sources-images-admin.php ALLSI_main_settings():
 *     include_once plugin_dir_path( __FILE__ ) . 'partials/new-ui/new-ui-menus.php';
 *
 * Or hook it via functions.php or a custom plugin:
 *     add_action( 'admin_menu', 'ALLSI_register_new_ui_menus', 100 );
 *
 * @package All_Sources_Images
 * @since 6.2.0
 */

/**
 * Store the admin instance globally for use in render functions
 */
global $ALLSI_new_ui_admin;
$ALLSI_new_ui_admin = null;

/**
 * Set the admin instance for new UI pages
 * 
 * @param All_Sources_Images_Admin $instance
 */
function ALLSI_set_new_ui_admin( $instance ) {
    global $ALLSI_new_ui_admin;
    $ALLSI_new_ui_admin = $instance;
}

/**
 * Get the admin instance for new UI pages
 * 
 * @return All_Sources_Images_Admin|null
 */
function ALLSI_get_new_ui_admin() {
    global $ALLSI_new_ui_admin;
    return $ALLSI_new_ui_admin;
}

/**
 * Register the new UI admin menus
 * 
 * This should be called from the admin class or after admin_menu hook
 */
function ALLSI_register_new_ui_menus() {
    // Settings submenu (first item, replaces Dashboard)
    add_submenu_page(
        'allsi-new-settings',
        __( 'Settings', 'all-sources-images' ),
        __( 'Settings', 'all-sources-images' ),
        'ALLSI_manage',
        'allsi-new-settings',
        'ALLSI_render_new_settings_page'
    );
    
    // Bulk Settings submenu (formerly Automatic)
    add_submenu_page(
        'allsi-new-settings',
        __( 'Bulk Settings', 'all-sources-images' ),
        __( 'Bulk Settings', 'all-sources-images' ),
        'ALLSI_manage',
        'allsi-new-automatic',
        'ALLSI_render_new_automatic_page'
    );
    
    // Bulk Generation submenu
    add_submenu_page(
        'allsi-new-settings',
        __( 'Bulk Generation', 'all-sources-images' ),
        __( 'Bulk Generation', 'all-sources-images' ),
        'ALLSI_manage',
        'allsi-new-bulk-generation',
        'ALLSI_render_new_bulk_generation_page'
    );
}

/**
 * Render the New Settings page
 */
function ALLSI_render_new_settings_page() {
    $admin = ALLSI_get_new_ui_admin();
    
    if ( ! $admin ) {
        $admin = ALLSI_try_get_admin_instance();
    }
    
    if ( $admin ) {
        // Make $this available in included files by binding to admin instance
        $render = function() {
            include_once plugin_dir_path( __FILE__ ) . 'new-settings.php';
        };
        $render = Closure::bind( $render, $admin, get_class( $admin ) );
        $render();
    } else {
        ALLSI_render_error_page();
    }
}

/**
 * Render the New Automatic page
 */
function ALLSI_render_new_automatic_page() {
    $admin = ALLSI_get_new_ui_admin();
    
    if ( ! $admin ) {
        $admin = ALLSI_try_get_admin_instance();
    }
    
    if ( $admin ) {
        // Make $this available in included files by binding to admin instance
        $render = function() {
            include_once plugin_dir_path( __FILE__ ) . 'new-automatic.php';
        };
        $render = Closure::bind( $render, $admin, get_class( $admin ) );
        $render();
    } else {
        ALLSI_render_error_page();
    }
}

/**
 * Render the New Bulk Generation page
 */
function ALLSI_render_new_bulk_generation_page() {
    $admin = ALLSI_get_new_ui_admin();
    
    if ( ! $admin ) {
        $admin = ALLSI_try_get_admin_instance();
    }
    
    if ( $admin ) {
        // Make $this available in included files by binding to admin instance
        $render = function() {
            include_once plugin_dir_path( __FILE__ ) . 'new-bulk-generation.php';
        };
        $render = Closure::bind( $render, $admin, get_class( $admin ) );
        $render();
    } else {
        ALLSI_render_error_page();
    }
}

/**
 * Try to get admin instance from various sources
 * 
 * @return object|null
 */
function ALLSI_try_get_admin_instance() {
    // Method 1: Check global
    global $ALLSI_admin_instance;
    if ( isset( $ALLSI_admin_instance ) && is_object( $ALLSI_admin_instance ) ) {
        return $ALLSI_admin_instance;
    }
    
    // Method 2: Try to instantiate the admin class directly
    if ( class_exists( 'All_Sources_Images_Admin' ) ) {
        // Check if there's a singleton or stored instance
        return new All_Sources_Images_Admin( 'all-sources-images', ALL_SOURCES_IMAGES_VERSION );
    }
    
    return null;
}

/**
 * Render error page when admin instance not available
 */
function ALLSI_render_error_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Error', 'all-sources-images' ); ?></h1>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'Unable to load the admin interface. Please ensure the plugin is properly activated.', 'all-sources-images' ); ?></p>
        </div>
    </div>
    <?php
}

// Note: Menus are registered via ALLSI_main_settings() which calls ALLSI_register_new_ui_menus()
// Do NOT call ALLSI_register_new_ui_menus() here as this file may be loaded before admin_menu hook
