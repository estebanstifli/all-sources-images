<?php
/**
 * Plugin Integrations for All Sources Images
 * 
 * Handles compatibility with third-party plugins:
 * - WP All Import
 * - WPeMatico
 * - FeedWordPress
 * - WP Automatic
 * - REST API
 *
 * @package    All_Sources_Images
 * @subpackage All_Sources_Images/admin
 * @since      6.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Plugin_Integrations {

    /**
     * The ID of this plugin.
     *
     * @var string
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @var string
     */
    private $version;

    /**
     * Compatibility settings from database.
     *
     * @var array
     */
    private $compatibility_settings;

    /**
     * Singleton instance.
     *
     * @var ASI_Plugin_Integrations|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @param string $plugin_name The plugin name.
     * @param string $version     The plugin version.
     * @return ASI_Plugin_Integrations
     */
    public static function get_instance( $plugin_name = '', $version = '' ) {
        if ( null === self::$instance ) {
            self::$instance = new self( $plugin_name, $version );
        }
        return self::$instance;
    }

    /**
     * Initialize the class.
     *
     * @param string $plugin_name The plugin name.
     * @param string $version     The plugin version.
     */
    private function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->load_compatibility_settings();
        $this->init_hooks();
    }

    /**
     * Load compatibility settings from database.
     */
    private function load_compatibility_settings() {
        $defaults = array(
            'enable_REST'         => false,
            'enable_wpai'         => false,
            'enable_wpematico'    => false,
            'enable_feedwordpress'=> false,
            'enable_wpautomatic'  => false,
            'enable_FIFU'         => false,
            'enable_cmb2'         => false,
            'enable_acf'          => false,
            'enable_metaboxio'    => false,
        );
        $this->compatibility_settings = wp_parse_args( 
            get_option( 'ASI_plugin_compatibility_settings', array() ), 
            $defaults 
        );
    }

    /**
     * Check if a specific integration is enabled.
     *
     * @param string $integration Integration key (e.g., 'enable_wpai').
     * @return bool
     */
    public function is_enabled( $integration ) {
        return ! empty( $this->compatibility_settings[ $integration ] ) 
            && $this->compatibility_settings[ $integration ] === 'true';
    }

    /**
     * Initialize all plugin hooks based on settings.
     */
    private function init_hooks() {
        // Log which integrations are enabled for debugging
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is true.
            error_log( '[ASI Plugin Integration] Settings loaded: ' . wp_json_encode( $this->compatibility_settings ) );
        }
        
        // WP All Import
        if ( $this->is_enabled( 'enable_wpai' ) ) {
            $this->init_wp_all_import();
        }

        // WPeMatico - also check if WPeMatico is active
        if ( $this->is_enabled( 'enable_wpematico' ) ) {
            $this->init_wpematico();
        }

        // FeedWordPress
        if ( $this->is_enabled( 'enable_feedwordpress' ) ) {
            $this->init_feedwordpress();
        }

        // WP Automatic
        if ( $this->is_enabled( 'enable_wpautomatic' ) ) {
            $this->init_wp_automatic();
        }

        // REST API
        if ( $this->is_enabled( 'enable_REST' ) ) {
            $this->init_rest_api();
        }
    }

    /**
     * =========================================================================
     * WP ALL IMPORT INTEGRATION
     * =========================================================================
     */

    /**
     * Initialize WP All Import hooks.
     */
    private function init_wp_all_import() {
        // Set a global flag when WP All Import starts importing
        // This runs BEFORE save_post, so we can detect it
        add_action( 'pmxi_before_xml_import', array( $this, 'wpai_set_importing_flag' ), 1 );
        add_action( 'pmxi_after_xml_import', array( $this, 'wpai_clear_importing_flag' ), 999 );
        
        // Hook that fires after WP All Import saves a post
        add_action( 'pmxi_saved_post', array( $this, 'wpai_after_post_import' ), 10, 3 );
        
        $this->log( 'WP All Import integration initialized' );
    }

    /**
     * Set flag indicating WP All Import is currently importing.
     */
    public function wpai_set_importing_flag() {
        if ( ! defined( 'ASI_WPAI_IMPORTING' ) ) {
            define( 'ASI_WPAI_IMPORTING', true );
        }
        $this->log( 'WP All Import: Import started - flag set' );
    }

    /**
     * Clear the importing flag after import completes.
     * 
     * @param int $import_id The import ID.
     */
    public function wpai_clear_importing_flag( $import_id = 0 ) {
        $this->log( 'WP All Import: Import complete', array( 'import_id' => $import_id ) );
    }

    /**
     * Handle image generation after WP All Import saves a post.
     *
     * @param int   $post_id     The post ID.
     * @param array $xml_node    The XML data for this record.
     * @param bool  $is_update   Whether this is an update or new post.
     */
    public function wpai_after_post_import( $post_id, $xml_node = array(), $is_update = false ) {
        $this->log( 'WP All Import: Post imported', array(
            'post_id'   => $post_id,
            'is_update' => $is_update,
            'has_thumbnail' => has_post_thumbnail( $post_id ),
        ) );

        // Check if we should skip based on featured image and rewrite setting
        // Note: pmxi_saved_post fires AFTER WP All Import has set featured image (if any)
        if ( $this->should_skip_generation( $post_id ) ) {
            $this->log( 'WP All Import: Skipping - post already has featured image and rewrite is disabled', array( 
                'post_id' => $post_id,
            ) );
            return;
        }

        // Clear any existing block status flags for fresh generation
        $this->clear_block_status_meta( $post_id );

        // Schedule image generation to avoid conflicts during import
        $this->schedule_image_generation( $post_id, 'wpai' );
    }

    /**
     * =========================================================================
     * WPEMATICO INTEGRATION
     * =========================================================================
     */

    /**
     * Initialize WPeMatico hooks.
     */
    private function init_wpematico() {
        // Use wpematico_pre_insert_post filter to detect WPeMatico is running
        // This fires right before wp_insert_post with the post args
        add_filter( 'wpematico_pre_insert_post', array( $this, 'wpematico_set_importing_flag' ), 1, 2 );
        
        // The main hook that fires AFTER WPeMatico finishes processing post & images
        // This is the correct hook from WPeMatico's campaign_fetch.php line 821
        // Note: This fires BEFORE WPeMatico sets featured image (lines 824-900+)
        // Parameters: $post_id, $campaign, $item
        add_action( 'wpematico_inserted_post', array( $this, 'wpematico_after_insert' ), 999, 3 );
        
        $this->log( 'WPeMatico integration initialized' );
    }

    /**
     * Set flag indicating WPeMatico is currently importing.
     * This is a filter so we must return the args unchanged.
     * 
     * @param array $args The post args.
     * @param array $campaign The campaign data.
     * @return array
     */
    public function wpematico_set_importing_flag( $args, $campaign = array() ) {
        if ( ! defined( 'ASI_WPEMATICO_IMPORTING' ) ) {
            define( 'ASI_WPEMATICO_IMPORTING', true );
        }
        $this->log( 'WPeMatico: Pre-insert filter - flag set' );
        return $args;
    }

    /**
     * Handle image generation after WPeMatico creates a post.
     *
     * @param int    $post_id   The post ID.
     * @param array  $campaign  The WPeMatico campaign data array.
     * @param object $item      The feed item object (SimplePie item).
     */
    public function wpematico_after_insert( $post_id, $campaign = null, $item = null ) {
        // Validate post_id
        if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
            $this->log( 'WPeMatico: Invalid post ID', array( 'post_id' => $post_id ) );
            return;
        }
        
        // Get campaign info for logging
        $campaign_id = is_array( $campaign ) && isset( $campaign['ID'] ) ? $campaign['ID'] : 'unknown';
        $campaign_title = is_array( $campaign ) && isset( $campaign['campaign_title'] ) ? $campaign['campaign_title'] : 'unknown';
        
        $this->log( 'WPeMatico: Post created', array( 
            'post_id' => $post_id,
            'campaign_id' => $campaign_id,
            'campaign_title' => $campaign_title,
            'has_thumbnail' => has_post_thumbnail( $post_id ),
        ) );

        // Check if we should skip based on featured image and rewrite setting
        if ( $this->should_skip_generation( $post_id ) ) {
            $this->log( 'WPeMatico: Skipping - post already has featured image and rewrite is disabled', array( 
                'post_id' => $post_id,
                'campaign_id' => $campaign_id,
            ) );
            return;
        }

        // Clear block status and schedule generation
        $this->clear_block_status_meta( $post_id );
        $this->schedule_image_generation( $post_id, 'wpematico' );
        
        $this->log( 'WPeMatico: Scheduled image generation', array( 
            'post_id' => $post_id,
            'campaign_id' => $campaign_id,
        ) );
    }

    /**
     * =========================================================================
     * FEEDWORDPRESS INTEGRATION
     * =========================================================================
     */

    /**
     * Initialize FeedWordPress hooks.
     */
    private function init_feedwordpress() {
        // Hook that fires after FeedWordPress syndicates a post
        add_action( 'post_syndicated_item', array( $this, 'feedwordpress_after_syndicate' ), 10, 2 );
        
        // Alternative hook
        add_action( 'feedwordpress_update_complete', array( $this, 'feedwordpress_update_complete' ), 10, 1 );
        
        $this->log( 'FeedWordPress integration initialized' );
    }

    /**
     * Handle image generation after FeedWordPress syndicates a post.
     *
     * @param int   $post_id The post ID.
     * @param array $post    The post data array.
     */
    public function feedwordpress_after_syndicate( $post_id, $post = array() ) {
        $this->log( 'FeedWordPress: Post syndicated', array( 'post_id' => $post_id ) );

        // Check if we should skip based on featured image and rewrite setting
        if ( $this->should_skip_generation( $post_id ) ) {
            $this->log( 'FeedWordPress: Skipping - post already has featured image and rewrite is disabled', array( 'post_id' => $post_id ) );
            return;
        }

        // Clear block status and schedule generation
        $this->clear_block_status_meta( $post_id );
        $this->schedule_image_generation( $post_id, 'feedwordpress' );
    }

    /**
     * Handle FeedWordPress update complete.
     *
     * @param object $feedwordpress The FeedWordPress object.
     */
    public function feedwordpress_update_complete( $feedwordpress ) {
        $this->log( 'FeedWordPress: Update complete' );
    }

    /**
     * =========================================================================
     * WP AUTOMATIC INTEGRATION
     * =========================================================================
     */

    /**
     * Initialize WP Automatic hooks.
     */
    private function init_wp_automatic() {
        // Hook that fires after WP Automatic creates a post
        add_action( 'wp_automatic_after_post_publish', array( $this, 'wpautomatic_after_publish' ), 10, 1 );
        
        // Alternative hook - some versions use this
        add_filter( 'wp_automatic_post_data', array( $this, 'wpautomatic_post_data' ), 10, 2 );
        
        $this->log( 'WP Automatic integration initialized' );
    }

    /**
     * Handle image generation after WP Automatic publishes a post.
     *
     * @param array $post_data The post data array with post_id.
     */
    public function wpautomatic_after_publish( $post_data ) {
        $post_id = isset( $post_data['post_id'] ) ? $post_data['post_id'] : 0;
        
        if ( ! $post_id ) {
            $this->log( 'WP Automatic: No post ID provided' );
            return;
        }

        $this->log( 'WP Automatic: Post published', array( 'post_id' => $post_id ) );

        // Check if we should skip based on featured image and rewrite setting
        if ( $this->should_skip_generation( $post_id ) ) {
            $this->log( 'WP Automatic: Skipping - post already has featured image and rewrite is disabled', array( 'post_id' => $post_id ) );
            return;
        }

        // Clear block status and schedule generation
        $this->clear_block_status_meta( $post_id );
        $this->schedule_image_generation( $post_id, 'wpautomatic' );
    }

    /**
     * Filter WP Automatic post data (alternative hook).
     *
     * @param array  $post_data The post data.
     * @param string $source    The source type.
     * @return array
     */
    public function wpautomatic_post_data( $post_data, $source = '' ) {
        // This is a filter, we just mark that we should generate image after insert
        add_action( 'save_post', array( $this, 'wpautomatic_save_post_once' ), 999, 1 );
        return $post_data;
    }

    /**
     * One-time save_post hook for WP Automatic.
     *
     * @param int $post_id The post ID.
     */
    public function wpautomatic_save_post_once( $post_id ) {
        // Remove hook immediately to prevent multiple calls
        remove_action( 'save_post', array( $this, 'wpautomatic_save_post_once' ), 999 );
        
        // Check if we should skip based on featured image and rewrite setting
        if ( ! $this->should_skip_generation( $post_id ) ) {
            $this->clear_block_status_meta( $post_id );
            $this->schedule_image_generation( $post_id, 'wpautomatic' );
        }
    }

    /**
     * =========================================================================
     * REST API INTEGRATION
     * =========================================================================
     */

    /**
     * Initialize REST API hooks.
     */
    private function init_rest_api() {
        // Hook for all post types via REST API
        add_action( 'rest_after_insert_post', array( $this, 'rest_after_insert' ), 10, 3 );
        add_action( 'rest_after_insert_page', array( $this, 'rest_after_insert' ), 10, 3 );
        
        // Dynamic hook for custom post types
        add_action( 'init', array( $this, 'register_rest_hooks_for_cpts' ), 99 );
        
        $this->log( 'REST API integration initialized' );
    }

    /**
     * Register REST hooks for custom post types.
     */
    public function register_rest_hooks_for_cpts() {
        $post_types = get_post_types( array( 'show_in_rest' => true ), 'names' );
        
        foreach ( $post_types as $post_type ) {
            if ( ! in_array( $post_type, array( 'post', 'page', 'attachment' ), true ) ) {
                add_action( "rest_after_insert_{$post_type}", array( $this, 'rest_after_insert' ), 10, 3 );
            }
        }
    }

    /**
     * Handle image generation after REST API creates/updates a post.
     *
     * @param WP_Post         $post     The post object.
     * @param WP_REST_Request $request  The request object.
     * @param bool            $creating Whether this is a new post.
     */
    public function rest_after_insert( $post, $request, $creating ) {
        // Only process new posts, not updates (unless rewrite is enabled)
        $main_settings = get_option( 'ASI_plugin_main_settings', array() );
        $rewrite_featured = isset( $main_settings['rewrite_featured'] ) && $main_settings['rewrite_featured'] === 'true';
        
        if ( ! $creating && ! $rewrite_featured ) {
            return;
        }

        $this->log( 'REST API: Post created', array( 
            'post_id'   => $post->ID, 
            'post_type' => $post->post_type 
        ) );

        // Check if we should skip based on featured image and rewrite setting
        if ( $this->should_skip_generation( $post->ID ) ) {
            $this->log( 'REST API: Skipping - post already has featured image and rewrite is disabled', array( 'post_id' => $post->ID ) );
            return;
        }

        // Clear block status and schedule generation
        $this->clear_block_status_meta( $post->ID );
        $this->schedule_image_generation( $post->ID, 'rest_api' );
    }

    /**
     * =========================================================================
     * SHARED UTILITIES
     * =========================================================================
     */

    /**
     * Schedule image generation for a post.
     * Uses wp_schedule_single_event to avoid conflicts during import processes.
     * Includes duplicate prevention using transients.
     *
     * @param int    $post_id The post ID.
     * @param string $source  The source that triggered the generation.
     */
    private function schedule_image_generation( $post_id, $source = 'unknown' ) {
        // Check if we already scheduled generation for this post (prevents duplicates)
        $transient_key = 'asi_scheduled_' . $post_id;
        if ( get_transient( $transient_key ) ) {
            $this->log( "Image generation already scheduled for post {$post_id}, skipping duplicate from {$source}" );
            return;
        }
        
        // Set transient for 120 seconds to prevent duplicate scheduling
        set_transient( $transient_key, true, 120 );
        
        $this->log( "Scheduling image generation for post {$post_id} from {$source}" );

        // Use different delays based on source
        // WPeMatico needs more time because wpematico_inserted_post fires BEFORE featured image is set
        $delay = ( $source === 'wpematico' ) ? 15 : 5;
        
        // Schedule with delay to ensure post is fully saved and import plugin has finished
        $scheduled = wp_schedule_single_event(
            time() + $delay,
            'ASI_generate_scheduled_image',
            array( $post_id )
        );

        if ( $scheduled ) {
            $this->log( "Image generation scheduled successfully for post {$post_id}" );
        } else {
            // Event might already be scheduled, try immediate generation
            $this->log( "Could not schedule event (may already exist), skipping for post {$post_id}" );
        }
    }

    /**
     * Generate image immediately (fallback if scheduling fails).
     *
     * @param int $post_id The post ID.
     */
    private function generate_image_now( $post_id ) {
        // Only generate if Generation class exists
        if ( ! class_exists( 'All_Sources_Images_Generation' ) ) {
            require_once plugin_dir_path( __FILE__ ) . 'class-all-sources-images-generation.php';
        }

        $generation = new All_Sources_Images_Generation( $this->plugin_name, $this->version );
        $main_settings = get_option( 'ASI_plugin_main_settings' );
        
        if ( empty( $main_settings['image_block'] ) ) {
            $this->log( 'No image blocks configured, skipping generation' );
            return;
        }

        $img_blocks = $main_settings['image_block'];

        foreach ( $img_blocks as $key_img_block => $img_block ) {
            $this->log( "Generating image for post {$post_id}, block {$key_img_block}" );
            
            $generation->ASI_create_thumb(
                $post_id,
                0,      // $total
                1,      // $counter
                1,      // $counter_img
                0,      // $speed
                false,  // $echo
                null,   // $img_custom
                null,   // $only_manual_bank
                $key_img_block,
                true    // $button_autogenerate
            );
        }
    }

    /**
     * Log a message using Monolog if available.
     *
     * @param string $message The message to log.
     * @param array  $context Additional context data.
     */
    private function log( $message, $context = array() ) {
        // Try to use ASI's Monolog logger
        if ( class_exists( 'All_Sources_Images_Admin' ) ) {
            $admin = new All_Sources_Images_Admin( $this->plugin_name, $this->version );
            if ( method_exists( $admin, 'ASI_monolog_call' ) ) {
                $log = $admin->ASI_monolog_call();
                if ( $log ) {
                    $log->info( '[Plugin Integration] ' . $message, $context );
                    return;
                }
            }
        }
        
        // Fallback to error_log in debug mode
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is true.
            error_log( '[ASI Plugin Integration] ' . $message . ' ' . wp_json_encode( $context ) );
        }
    }

    /**
     * Check if a specific plugin is active.
     *
     * @param string $plugin_file The plugin file path (e.g., 'wp-all-import/wp-all-import.php').
     * @return bool
     */
    public function is_plugin_active( $plugin_file ) {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active( $plugin_file );
    }

    /**
     * Get current compatibility settings.
     *
     * @return array
     */
    public function get_settings() {
        return $this->compatibility_settings;
    }

    /**
     * Reload settings from database (useful after settings update).
     */
    public function reload_settings() {
        $this->load_compatibility_settings();
    }

    /**
     * Clear ASI block status meta for a post before scheduling new generation.
     * This ensures fresh generation for all image blocks.
     *
     * @param int $post_id The post ID.
     */
    private function clear_block_status_meta( $post_id ) {
        global $wpdb;
        $like_pattern = $wpdb->esc_like( '_asi_block_' ) . '%';
        $deleted = $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s",
            $post_id,
            $like_pattern
        ) );
        $this->log( "Cleared block status meta for post {$post_id}", array( 'deleted_rows' => $deleted ) );
    }

    /**
     * Check if we should skip image generation for a post.
     * Returns true if post has featured image AND rewrite_featured is disabled.
     *
     * @param int $post_id The post ID.
     * @return bool True if we should skip generation.
     */
    private function should_skip_generation( $post_id ) {
        // If no featured image, don't skip
        if ( ! has_post_thumbnail( $post_id ) ) {
            return false;
        }
        
        // Has featured image - check rewrite setting
        $main_settings = get_option( 'ASI_plugin_main_settings', array() );
        $rewrite_featured = isset( $main_settings['rewrite_featured'] ) && $main_settings['rewrite_featured'] === 'true';
        
        // Skip if has featured image AND rewrite is disabled
        return ! $rewrite_featured;
    }
}
