<?php
/**
 * New UI Asset Loader
 * 
 * Enqueues the SAME CSS and JS as the original admin pages
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
 * Enqueue admin styles - same as original pages
 */
function asi_enqueue_new_ui_assets( $hook ) {
    // Only load on our new pages
    $new_ui_pages = array(
        'toplevel_page_asi-new-settings',
        'all-sources-images_page_asi-new-settings',
        'all-sources-images_page_asi-new-automatic',
        'all-sources-images_page_asi-new-bulk-generation',
    );
    
    if ( ! in_array( $hook, $new_ui_pages, true ) ) {
        return;
    }
    
    $plugin_url = plugin_dir_url( __FILE__ ) . '../../';
    $version = defined( 'ALL_SOURCES_IMAGES_VERSION' ) ? ALL_SOURCES_IMAGES_VERSION : '1.0.0';
    
    // === CSS - Same as original admin pages ===
    wp_enqueue_style(
        'all-sources-images',
        $plugin_url . 'css/all-sources-images-admin.css',
        array(),
        $version,
        'all'
    );
    wp_enqueue_style(
        'plugins-bundle',
        $plugin_url . 'css/plugins.bundle.css',
        array(),
        $version,
        'all'
    );
    wp_enqueue_style(
        'style.bundle',
        $plugin_url . 'css/style.bundle.css',
        array(),
        $version,
        'all'
    );
    wp_enqueue_style(
        'theme-base-light',
        $plugin_url . 'css/themes/layout/header/base/light.css',
        array(),
        $version,
        'all'
    );
    wp_enqueue_style(
        'theme-menu-light',
        $plugin_url . 'css/themes/layout/header/menu/light.css',
        array(),
        $version,
        'all'
    );
    wp_enqueue_style(
        'theme-brand-dark',
        $plugin_url . 'css/themes/layout/brand/dark.css',
        array(),
        $version,
        'all'
    );
    wp_enqueue_style(
        'theme-aside-dark',
        $plugin_url . 'css/themes/layout/aside/dark.css',
        array(),
        $version,
        'all'
    );
    
    // jQuery UI CSS
    wp_enqueue_style( 'style-jquery-ui', $plugin_url . 'js/jquery-ui/jquery-ui.css', array(), $version );
    
    // === JavaScript - Same as original admin pages ===
    wp_enqueue_script(
        'prismjs-bundle',
        $plugin_url . 'js/prismjs.bundle.js',
        array('jquery'),
        $version,
        true
    );
    wp_enqueue_script(
        'scripts-bundle',
        $plugin_url . 'js/scripts.bundle.js',
        array('jquery'),
        $version,
        true
    );
    wp_enqueue_script(
        'common-mpt',
        $plugin_url . 'js/common.js',
        array('jquery'),
        $version,
        true
    );
    
    // jQuery UI
    wp_enqueue_script( 'jquery-ui', $plugin_url . 'js/jquery-ui/jquery-ui.js', array('jquery'), $version, true );
    
    // Source page JS (for API testing)
    wp_enqueue_script( 'source', $plugin_url . 'js/source.js', array('jquery', 'jquery-ui'), $version, true );
    wp_localize_script( 'source', 'apisTestingAjax', array(
        'ajaxurl'            => admin_url( 'admin-ajax.php' ),
        'nonce'              => wp_create_nonce( 'api_testing_nonce' ),
        'successful_testing' => esc_html__( 'Your API key works!', 'all-sources-images' ),
        'error_key'          => esc_html__( "Your API key doesn't work.", 'all-sources-images' ),
        'error_testing'      => esc_html__( 'An error has occurred with the remote API.', 'all-sources-images' ),
    ) );
    
    // Main admin JS (contains password toggle)
    wp_enqueue_script(
        'mpt-admin',
        $plugin_url . 'js/magic-post-thumbnail-admin.js',
        array('jquery', 'jquery-ui'),
        $version,
        true
    );
    
    // Bootstrap Icons CSS
    wp_enqueue_style(
        'bootstrap-icons',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
        array(),
        '1.11.1'
    );
    
    // Bulk Generation JS (only on bulk generation page)
    if ( $hook === 'all-sources-images_page_asi-new-bulk-generation' ) {
        // Bootstrap 5 JS for tabs/accordions
        wp_enqueue_script(
            'bootstrap5',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
            array('jquery'),
            '5.3.2',
            true
        );
        
        wp_enqueue_script(
            'asi-bulk-generation',
            $plugin_url . 'js/bulk-generation.js',
            array('jquery', 'bootstrap5'),
            $version,
            true
        );
        wp_localize_script( 'asi-bulk-generation', 'asiBulkAjax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'asi_bulk_nonce' ),
            'edit_url' => admin_url( 'post.php' ),
            'i18n'     => array(
                'no_selection'   => esc_html__( 'Please select content to generate images for.', 'magic-post-thumbnail' ),
                'confirm_delete' => esc_html__( 'Are you sure you want to delete this job?', 'magic-post-thumbnail' ),
                'loading'        => esc_html__( 'Loading...', 'magic-post-thumbnail' ),
            ),
        ) );
    }
    
    // Custom styles for new UI pages (loaded last to override)
    wp_enqueue_style(
        'asi-new-ui-styles',
        plugin_dir_url( __FILE__ ) . 'new-ui-styles.css',
        array( 'all-sources-images', 'style.bundle' ),
        $version,
        'all'
    );
}
add_action( 'admin_enqueue_scripts', 'asi_enqueue_new_ui_assets' );
