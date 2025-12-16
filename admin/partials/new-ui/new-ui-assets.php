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
    $plugin_root_url = plugin_dir_url( __FILE__ ) . '../../../';
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
        array('jquery', 'jquery-ui-tabs'),
        $version,
        true
    );
    
    // jQuery UI from WordPress Core
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-tabs' );
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'jquery-ui-draggable' );
    wp_enqueue_script( 'jquery-ui-droppable' );
    
    // Source page JS (for API testing)
    wp_enqueue_script( 'source', $plugin_url . 'js/source.js', array('jquery', 'jquery-ui-core'), $version, true );
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
        $plugin_url . 'js/all-sources-images-admin.js',
        array('jquery'),
        $version,
        true
    );
    
    // New UI Scripts (handles all inline script functionality)
    wp_enqueue_script(
        'asi-new-ui',
        $plugin_url . 'js/asi-new-ui.js',
        array('jquery'),
        $version,
        true
    );
    
    // Localize script data for Image Placement page
    if ( $hook === 'all-sources-images_page_asi-new-automatic' ) {
        // Get current block index from settings
        $options = get_option( 'ASI_plugin_main_settings', array() );
        $image_blocks = isset( $options['image_block'] ) ? $options['image_block'] : array();
        $block_index = empty( $image_blocks ) ? 1 : max( array_keys( $image_blocks ) ) + 1;
        
        wp_localize_script( 'asi-new-ui', 'asiNewUI', array(
            'imagePlacement' => array(
                'blockIndex' => $block_index,
                'helpTexts'  => array(
                    'title'                           => esc_html__( 'Uses the post title as the search term. This is the simplest and most common option.', 'all-sources-images' ),
                    'text_analyser'                   => esc_html__( 'Analyzes the post content to extract the most relevant keywords using ML algorithms.', 'all-sources-images' ),
                    'text_analyser_previous_paragraph'=> esc_html__( 'Analyzes only the paragraph BEFORE the image position for keyword extraction.', 'all-sources-images' ),
                    'text_analyser_next_paragraph'    => esc_html__( 'Analyzes only the paragraph AFTER the image position for keyword extraction.', 'all-sources-images' ),
                    'tags'                            => esc_html__( 'Uses post tags as search terms. Choose first, last, or random tag.', 'all-sources-images' ),
                    'categories'                      => esc_html__( 'Uses post categories as search terms with hierarchy level selection.', 'all-sources-images' ),
                    'custom_field'                    => esc_html__( 'Uses a custom field (post meta) value as the search term.', 'all-sources-images' ),
                    'custom_request'                  => esc_html__( 'Build your own search using placeholders: %%Title%%, %%Category%%, %%Tag%%, %%Taxonomy%%.', 'all-sources-images' ),
                    'openai_extractor'                => esc_html__( 'Uses OpenAI GPT to extract relevant keywords from your post title.', 'all-sources-images' ),
                    'ai_image_prompt'                 => esc_html__( 'Uses OpenAI to generate optimized prompts for AI image generation (DALL-E, Stable Diffusion, etc.).', 'all-sources-images' ),
                ),
            ),
        ) );
    }
    
    // Bootstrap Icons CSS (local)
    wp_enqueue_style(
        'bootstrap-icons',
        $plugin_root_url . 'vendor/bootstrap-icons/font/bootstrap-icons.css',
        array(),
        '1.11.3'
    );
    
    // Bulk Generation JS (only on bulk generation page)
    if ( $hook === 'all-sources-images_page_asi-new-bulk-generation' ) {
        // Bootstrap 5 JS for tabs/accordions (local)
        wp_enqueue_script(
            'bootstrap5',
            $plugin_root_url . 'vendor/bootstrap/js/bootstrap.bundle.min.js',
            array('jquery'),
            '5.3.3',
            true
        );
        
        // Bootstrap 5 CSS (local)
        wp_enqueue_style(
            'bootstrap5-css',
            $plugin_root_url . 'vendor/bootstrap/css/bootstrap.min.css',
            array(),
            '5.3.3'
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
                'no_selection'   => esc_html__( 'Please select content to generate images for.', 'all-sources-images' ),
                'confirm_delete' => esc_html__( 'Are you sure you want to delete this job?', 'all-sources-images' ),
                'loading'        => esc_html__( 'Loading...', 'all-sources-images' ),
                'creating_job'   => esc_html__( 'Creating job...', 'all-sources-images' ),
                'please_wait'    => esc_html__( 'Please wait while we set up your image generation job.', 'all-sources-images' ),
                'error'          => esc_html__( 'Error', 'all-sources-images' ),
                'network_error'  => esc_html__( 'Network error', 'all-sources-images' ),
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
    
    // Image Placement styles (only on automatic page)
    if ( $hook === 'all-sources-images_page_asi-new-automatic' ) {
        wp_enqueue_style(
            'asi-image-placement',
            $plugin_url . 'css/asi-image-placement.css',
            array( 'asi-new-ui-styles' ),
            $version,
            'all'
        );
    }
    
    // Bulk Generation styles (only on bulk generation page)
    if ( $hook === 'all-sources-images_page_asi-new-bulk-generation' ) {
        wp_enqueue_style(
            'asi-bulk-generation',
            $plugin_url . 'css/asi-bulk-generation.css',
            array( 'asi-new-ui-styles' ),
            $version,
            'all'
        );
    }
}
add_action( 'admin_enqueue_scripts', 'asi_enqueue_new_ui_assets' );
