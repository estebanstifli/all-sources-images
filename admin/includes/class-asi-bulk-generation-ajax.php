<?php
/**
 * Bulk Generation AJAX Handlers
 * 
 * Handles all AJAX requests for bulk image generation
 *
 * @package All_Sources_Images
 * @since 6.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Bulk_Generation_Ajax {

    /**
     * Constructor - register AJAX handlers
     */
    public function __construct() {
        // Load items for selection
        add_action( 'wp_ajax_asi_bulk_load_items', array( $this, 'load_items' ) );
        
        // Job management
        add_action( 'wp_ajax_asi_bulk_create_job', array( $this, 'create_job' ) );
        add_action( 'wp_ajax_asi_bulk_create_job_from_ids', array( $this, 'create_job_from_ids' ) );
        add_action( 'wp_ajax_asi_bulk_get_jobs', array( $this, 'get_jobs' ) );
        add_action( 'wp_ajax_asi_bulk_get_job_details', array( $this, 'get_job_details' ) );
        add_action( 'wp_ajax_asi_bulk_start_job', array( $this, 'start_job' ) );
        add_action( 'wp_ajax_asi_bulk_pause_job', array( $this, 'pause_job' ) );
        add_action( 'wp_ajax_asi_bulk_delete_job', array( $this, 'delete_job' ) );
    }

    /**
     * Verify nonce and capability
     */
    private function verify_request() {
        if ( ! check_ajax_referer( 'asi_bulk_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'magic-post-thumbnail' ) ) );
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'magic-post-thumbnail' ) ) );
        }
    }

    /**
     * Load items for selection tabs
     */
    public function load_items() {
        $this->verify_request();

        $post_type = sanitize_text_field( $_POST['post_type'] ?? 'post' );
        $tab       = sanitize_text_field( $_POST['tab'] ?? 'recent' );
        $search    = sanitize_text_field( $_POST['search'] ?? '' );
        $paged     = absint( $_POST['paged'] ?? 1 );
        $category  = absint( $_POST['category'] ?? 0 );
        
        $per_page = 20;

        $args = array(
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        // Tab-specific modifications
        switch ( $tab ) {
            case 'recent':
                $args['posts_per_page'] = 10;
                break;

            case 'all':
                // Default args
                break;

            case 'search':
                if ( ! empty( $search ) ) {
                    $args['s'] = $search;
                }
                break;

            case 'category':
                if ( $category > 0 ) {
                    if ( $post_type === 'product' ) {
                        $args['tax_query'] = array(
                            array(
                                'taxonomy' => 'product_cat',
                                'field'    => 'term_id',
                                'terms'    => $category,
                            ),
                        );
                    } else {
                        $args['cat'] = $category;
                    }
                }
                break;
        }

        $query = new WP_Query( $args );
        $posts = $query->posts;

        // Build HTML
        $html = '';
        if ( empty( $posts ) ) {
            $html = '<p class="text-muted p-2">' . __( 'No items found.', 'magic-post-thumbnail' ) . '</p>';
        } else {
            foreach ( $posts as $post ) {
                $has_featured = has_post_thumbnail( $post->ID );
                $class = $has_featured ? 'has-featured' : 'no-featured';
                $badge_class = $has_featured ? 'has' : 'no';
                $badge_text = $has_featured ? __( 'Has image', 'magic-post-thumbnail' ) : __( 'No image', 'magic-post-thumbnail' );
                
                $html .= sprintf(
                    '<label class="%s"><input type="checkbox" value="%d"> %s <span class="featured-badge %s">%s</span></label>',
                    esc_attr( $class ),
                    esc_attr( $post->ID ),
                    esc_html( $post->post_title ),
                    esc_attr( $badge_class ),
                    esc_html( $badge_text )
                );
            }
        }

        wp_send_json_success( array(
            'html'         => $html,
            'total'        => $query->found_posts,
            'max_pages'    => $query->max_num_pages,
            'current_page' => $paged,
        ) );
    }

    /**
     * Create a new bulk generation job
     */
    public function create_job() {
        $this->verify_request();

        $job_name        = sanitize_text_field( $_POST['job_name'] ?? '' );
        $images_per_post = absint( $_POST['images_per_post'] ?? 1 );
        $selection       = json_decode( stripslashes( $_POST['selection'] ?? '{}' ), true );
        $start_immediately = absint( $_POST['start_immediately'] ?? 0 );

        if ( empty( $job_name ) ) {
            $job_name = sprintf( __( 'Bulk Job %s', 'magic-post-thumbnail' ), current_time( 'Y-m-d H:i' ) );
        }

        // Collect all post IDs based on selection
        $post_ids = array();
        $post_types_selected = array();

        foreach ( array( 'posts' => 'post', 'pages' => 'page', 'products' => 'product' ) as $key => $post_type ) {
            if ( empty( $selection[ $key ] ) ) {
                continue;
            }

            $mode = $selection[ $key ]['mode'] ?? '';
            $ids  = $selection[ $key ]['ids'] ?? array();

            if ( $mode === 'all' ) {
                $all_ids = get_posts( array(
                    'post_type'      => $post_type,
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                ) );
                $post_ids = array_merge( $post_ids, $all_ids );
                $post_types_selected[] = $post_type;
            } elseif ( $mode === 'no_featured' ) {
                $no_featured_ids = get_posts( array(
                    'post_type'      => $post_type,
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                    'meta_query'     => array(
                        array(
                            'key'     => '_thumbnail_id',
                            'compare' => 'NOT EXISTS',
                        ),
                    ),
                ) );
                $post_ids = array_merge( $post_ids, $no_featured_ids );
                $post_types_selected[] = $post_type;
            } elseif ( $mode === 'custom' && ! empty( $ids ) ) {
                $post_ids = array_merge( $post_ids, array_map( 'absint', $ids ) );
                $post_types_selected[] = $post_type;
            }
        }

        // Remove duplicates
        $post_ids = array_unique( $post_ids );

        if ( empty( $post_ids ) ) {
            wp_send_json_error( array( 'message' => __( 'No posts selected', 'magic-post-thumbnail' ) ) );
        }

        // Create job
        $job_id = ASI_Bulk_Generation_DB::create_job( array(
            'job_name'        => $job_name,
            'job_status'      => 'pending',
            'total_posts'     => count( $post_ids ),
            'images_per_post' => $images_per_post,
            'selection_mode'  => 'mixed',
            'post_types'      => $post_types_selected,
            'settings'        => array(
                'selection' => $selection,
            ),
        ) );

        if ( ! $job_id ) {
            wp_send_json_error( array( 'message' => __( 'Failed to create job', 'magic-post-thumbnail' ) ) );
        }

        // Add posts to job
        $added = ASI_Bulk_Generation_DB::add_posts_to_job( $job_id, $post_ids );

        // Update total with actual added count
        ASI_Bulk_Generation_DB::update_job( $job_id, array( 'total_posts' => $added ) );

        // Start immediately if requested
        if ( $start_immediately ) {
            ASI_Bulk_Generation_DB::update_job_status( $job_id, 'processing' );
            // Schedule cron event
            if ( ! wp_next_scheduled( 'asi_bulk_process_job', array( $job_id ) ) ) {
                wp_schedule_single_event( time(), 'asi_bulk_process_job', array( $job_id ) );
            }
        }

        wp_send_json_success( array(
            'message' => sprintf( __( 'Job created with %d posts', 'magic-post-thumbnail' ), $added ),
            'job_id'  => $job_id,
        ) );
    }

    /**
     * Create a job directly from post IDs (used by bulk action from posts list)
     */
    public function create_job_from_ids() {
        $this->verify_request();

        $post_ids_raw = sanitize_text_field( $_POST['post_ids'] ?? '' );
        
        if ( empty( $post_ids_raw ) ) {
            wp_send_json_error( array( 'message' => __( 'No posts provided', 'magic-post-thumbnail' ) ) );
        }

        // Parse IDs
        $post_ids = array_filter( array_map( 'absint', explode( ',', $post_ids_raw ) ) );
        
        if ( empty( $post_ids ) ) {
            wp_send_json_error( array( 'message' => __( 'No valid posts provided', 'magic-post-thumbnail' ) ) );
        }

        // Get images per post from settings
        $options = get_option( 'ASI_plugin_main_settings', array() );
        $image_blocks = isset( $options['image_block'] ) ? $options['image_block'] : array();
        $images_per_post = max( 1, count( $image_blocks ) );

        // Generate job name
        $job_name = sprintf( 
            __( 'Quick Job - %d posts (%s)', 'magic-post-thumbnail' ), 
            count( $post_ids ),
            current_time( 'Y-m-d H:i' ) 
        );

        // Determine post types from the IDs
        $post_types_selected = array();
        foreach ( $post_ids as $post_id ) {
            $post_type = get_post_type( $post_id );
            if ( $post_type && ! in_array( $post_type, $post_types_selected ) ) {
                $post_types_selected[] = $post_type;
            }
        }

        // Create job
        $job_id = ASI_Bulk_Generation_DB::create_job( array(
            'job_name'        => $job_name,
            'job_status'      => 'pending',
            'total_posts'     => count( $post_ids ),
            'images_per_post' => $images_per_post,
            'selection_mode'  => 'direct_ids',
            'post_types'      => $post_types_selected,
            'settings'        => array(
                'source' => 'bulk_action',
                'original_ids' => $post_ids,
            ),
        ) );

        if ( ! $job_id ) {
            wp_send_json_error( array( 'message' => __( 'Failed to create job', 'magic-post-thumbnail' ) ) );
        }

        // Add posts to job
        $added = ASI_Bulk_Generation_DB::add_posts_to_job( $job_id, $post_ids );

        // Update total with actual added count
        ASI_Bulk_Generation_DB::update_job( $job_id, array( 'total_posts' => $added ) );

        // Start job immediately
        ASI_Bulk_Generation_DB::update_job_status( $job_id, 'processing' );
        
        // Schedule cron event
        if ( ! wp_next_scheduled( 'asi_bulk_process_job', array( $job_id ) ) ) {
            wp_schedule_single_event( time(), 'asi_bulk_process_job', array( $job_id ) );
        }

        wp_send_json_success( array(
            'message' => sprintf( __( 'Job created and started with %d posts', 'magic-post-thumbnail' ), $added ),
            'job_id'  => $job_id,
            'total_posts' => $added,
        ) );
    }

    /**
     * Get jobs list
     */
    public function get_jobs() {
        $this->verify_request();

        $page = absint( $_POST['page'] ?? 1 );
        $status = sanitize_text_field( $_POST['status'] ?? '' );

        $result = ASI_Bulk_Generation_DB::get_jobs( array(
            'page'   => $page,
            'status' => $status,
        ) );

        wp_send_json_success( $result );
    }

    /**
     * Get job details with posts
     */
    public function get_job_details() {
        $this->verify_request();

        $job_id     = absint( $_POST['job_id'] ?? 0 );
        $posts_page = absint( $_POST['posts_page'] ?? 1 );

        if ( ! $job_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid job ID', 'magic-post-thumbnail' ) ) );
        }

        $job = ASI_Bulk_Generation_DB::get_job( $job_id );
        if ( ! $job ) {
            wp_send_json_error( array( 'message' => __( 'Job not found', 'magic-post-thumbnail' ) ) );
        }

        $stats = ASI_Bulk_Generation_DB::get_job_stats( $job_id );
        $posts = ASI_Bulk_Generation_DB::get_job_posts( $job_id, array( 'page' => $posts_page ) );
        
        // Add thumbnail URLs to posts (all images generated for this post)
        if ( ! empty( $posts['posts'] ) ) {
            foreach ( $posts['posts'] as &$post ) {
                $post->image_urls = array();
                $post->thumbnail_url = ''; // Keep for backward compatibility
                
                // Get featured image if exists
                if ( has_post_thumbnail( $post->post_id ) ) {
                    $thumb_id = get_post_thumbnail_id( $post->post_id );
                    $thumb = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
                    if ( $thumb ) {
                        $post->thumbnail_url = $thumb[0];
                        $post->image_urls[] = $thumb[0];
                        if ( empty( $post->featured_image_id ) ) {
                            $post->featured_image_id = $thumb_id;
                        }
                    }
                }
                
                // Get images attached to this post (uploaded during bulk generation)
                $attached_images = get_posts( array(
                    'post_type'      => 'attachment',
                    'post_mime_type' => 'image',
                    'post_parent'    => $post->post_id,
                    'posts_per_page' => 10,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'exclude'        => has_post_thumbnail( $post->post_id ) ? array( get_post_thumbnail_id( $post->post_id ) ) : array(),
                ) );
                
                foreach ( $attached_images as $image ) {
                    $thumb = wp_get_attachment_image_src( $image->ID, 'thumbnail' );
                    if ( $thumb && ! in_array( $thumb[0], $post->image_urls, true ) ) {
                        $post->image_urls[] = $thumb[0];
                    }
                }
            }
        }

        wp_send_json_success( array(
            'job'   => $job,
            'stats' => $stats,
            'posts' => $posts,
        ) );
    }

    /**
     * Start or resume a job
     */
    public function start_job() {
        $this->verify_request();

        $job_id = absint( $_POST['job_id'] ?? 0 );

        if ( ! $job_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid job ID', 'magic-post-thumbnail' ) ) );
        }

        $job = ASI_Bulk_Generation_DB::get_job( $job_id );
        if ( ! $job ) {
            wp_send_json_error( array( 'message' => __( 'Job not found', 'magic-post-thumbnail' ) ) );
        }

        if ( ! in_array( $job->job_status, array( 'pending', 'paused' ) ) ) {
            wp_send_json_error( array( 'message' => __( 'Job cannot be started', 'magic-post-thumbnail' ) ) );
        }

        ASI_Bulk_Generation_DB::update_job_status( $job_id, 'processing' );

        // Schedule cron event
        if ( ! wp_next_scheduled( 'asi_bulk_process_job', array( $job_id ) ) ) {
            wp_schedule_single_event( time(), 'asi_bulk_process_job', array( $job_id ) );
        }

        wp_send_json_success( array( 'message' => __( 'Job started', 'magic-post-thumbnail' ) ) );
    }

    /**
     * Pause a job
     */
    public function pause_job() {
        $this->verify_request();

        $job_id = absint( $_POST['job_id'] ?? 0 );

        if ( ! $job_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid job ID', 'magic-post-thumbnail' ) ) );
        }

        ASI_Bulk_Generation_DB::update_job_status( $job_id, 'paused' );

        // Clear scheduled cron
        wp_clear_scheduled_hook( 'asi_bulk_process_job', array( $job_id ) );

        wp_send_json_success( array( 'message' => __( 'Job paused', 'magic-post-thumbnail' ) ) );
    }

    /**
     * Delete a job
     */
    public function delete_job() {
        $this->verify_request();

        $job_id = absint( $_POST['job_id'] ?? 0 );

        if ( ! $job_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid job ID', 'magic-post-thumbnail' ) ) );
        }

        // Clear all scheduled cron events for this job
        wp_clear_scheduled_hook( 'asi_bulk_process_job', array( $job_id ) );
        wp_clear_scheduled_hook( 'asi_bulk_process_batch', array( $job_id ) );

        $deleted = ASI_Bulk_Generation_DB::delete_job( $job_id );

        if ( $deleted ) {
            wp_send_json_success( array( 'message' => __( 'Job deleted', 'magic-post-thumbnail' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to delete job', 'magic-post-thumbnail' ) ) );
        }
    }
}

// Initialize
new ASI_Bulk_Generation_Ajax();
