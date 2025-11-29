<?php
/**
 * New UI Menu Registration
 * 
 * This file registers the new admin menu pages for the redesigned UI.
 * To activate, add this line in class-all-sources-images-admin.php ASI_main_settings():
 *     include_once plugin_dir_path( __FILE__ ) . 'partials/new-ui/new-ui-menus.php';
 *
 * Or hook it via functions.php or a custom plugin:
 *     add_action( 'admin_menu', 'asi_register_new_ui_menus', 100 );
 *
 * @package All_Sources_Images
 * @since 6.2.0
 */

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

/**
 * Store the admin instance globally for use in render functions
 */
global $ASI_new_ui_admin;
$ASI_new_ui_admin = null;

/**
 * Set the admin instance for new UI pages
 * 
 * @param All_Sources_Images_Admin $instance
 */
function asi_set_new_ui_admin( $instance ) {
    global $ASI_new_ui_admin;
    $ASI_new_ui_admin = $instance;
}

/**
 * Get the admin instance for new UI pages
 * 
 * @return All_Sources_Images_Admin|null
 */
function asi_get_new_ui_admin() {
    global $ASI_new_ui_admin;
    return $ASI_new_ui_admin;
}

/**
 * Register the new UI admin menus
 * 
 * This should be called from the admin class or after admin_menu hook
 */
function asi_register_new_ui_menus() {
    // New Settings submenu
    add_submenu_page(
        'all-sources-images-admin-display',
        __( 'New Settings', 'all-sources-images' ),
        __( '→ New Settings', 'all-sources-images' ),
        'asi_manage',
        'asi-new-settings',
        'asi_render_new_settings_page'
    );
    
    // New Automatic submenu
    add_submenu_page(
        'all-sources-images-admin-display',
        __( 'New Automatic', 'all-sources-images' ),
        __( '→ New Automatic', 'all-sources-images' ),
        'asi_manage',
        'asi-new-automatic',
        'asi_render_new_automatic_page'
    );
    
    // New Bulk Generation submenu
    add_submenu_page(
        'all-sources-images-admin-display',
        __( 'New Bulk Generation', 'all-sources-images' ),
        __( '→ New Bulk Generation', 'all-sources-images' ),
        'asi_manage',
        'asi-new-bulk-generation',
        'asi_render_new_bulk_generation_page'
    );
}

/**
 * Render the New Settings page
 */
function asi_render_new_settings_page() {
    $admin = asi_get_new_ui_admin();
    
    if ( ! $admin ) {
        $admin = asi_try_get_admin_instance();
    }
    
    if ( $admin ) {
        // Make $this available in included files by binding to admin instance
        $render = function() {
            include_once plugin_dir_path( __FILE__ ) . 'new-settings.php';
        };
        $render = Closure::bind( $render, $admin, get_class( $admin ) );
        $render();
    } else {
        asi_render_error_page();
    }
}

/**
 * Render the New Automatic page
 */
function asi_render_new_automatic_page() {
    $admin = asi_get_new_ui_admin();
    
    if ( ! $admin ) {
        $admin = asi_try_get_admin_instance();
    }
    
    if ( $admin ) {
        // Make $this available in included files by binding to admin instance
        $render = function() {
            include_once plugin_dir_path( __FILE__ ) . 'new-automatic.php';
        };
        $render = Closure::bind( $render, $admin, get_class( $admin ) );
        $render();
    } else {
        asi_render_error_page();
    }
}

/**
 * Render the New Bulk Generation page
 */
function asi_render_new_bulk_generation_page() {
    $admin = asi_get_new_ui_admin();
    
    if ( ! $admin ) {
        $admin = asi_try_get_admin_instance();
    }
    
    if ( $admin ) {
        // Make $this available in included files by binding to admin instance
        $render = function() {
            include_once plugin_dir_path( __FILE__ ) . 'new-bulk-generation.php';
        };
        $render = Closure::bind( $render, $admin, get_class( $admin ) );
        $render();
    } else {
        asi_render_error_page();
    }
}

/**
 * Try to get admin instance from various sources
 * 
 * @return object|null
 */
function asi_try_get_admin_instance() {
    // Method 1: Check global
    global $ASI_admin_instance;
    if ( isset( $ASI_admin_instance ) && is_object( $ASI_admin_instance ) ) {
        return $ASI_admin_instance;
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
function asi_render_error_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Error', 'all-sources-images' ); ?></h1>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'Unable to load the admin interface. Please ensure the plugin is properly activated.', 'all-sources-images' ); ?></p>
        </div>
    </div>
    <?php
}

// Auto-register if this file is included
if ( did_action( 'admin_menu' ) ) {
    // If admin_menu already fired, register immediately
    asi_register_new_ui_menus();
} else {
    // Otherwise hook it
    add_action( 'admin_menu', 'asi_register_new_ui_menus', 100 );
}
