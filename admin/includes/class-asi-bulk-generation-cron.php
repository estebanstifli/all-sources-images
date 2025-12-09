<?php
/**
 * Bulk Generation Cron Processor
 * 
 * Handles background processing of bulk generation jobs
 *
 * @package All_Sources_Images
 * @since 6.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Bulk_Generation_Cron {

    /**
     * Batch size per cron execution (1 post at a time for reliability)
     */
    const BATCH_SIZE = 1;

    /**
     * Maximum retry attempts for failed posts
     */
    const MAX_RETRIES = 3;

    /**
     * Timeout threshold in seconds (for detecting stuck posts)
     */
    const PROCESSING_TIMEOUT = 120;

    /**
     * Currently processing job post ID (for shutdown handler)
     *
     * @var int|null
     */
    private static $current_job_post_id = null;

    /**
     * Currently processing job ID (for shutdown handler)
     *
     * @var int|null
     */
    private static $current_job_id = null;

    /**
     * Constructor - register cron hooks
     */
    public function __construct() {
        add_action( 'asi_bulk_process_job', array( $this, 'process_job' ) );
        add_action( 'asi_bulk_process_batch', array( $this, 'process_batch' ) );
        
        // Register shutdown handler to catch fatal errors
        register_shutdown_function( array( __CLASS__, 'handle_shutdown' ) );
    }

    /**
     * Shutdown handler to catch fatal errors during processing
     */
    public static function handle_shutdown() {
        $error = error_get_last();
        
        if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ) ) ) {
            // Fatal error occurred
            if ( self::$current_job_post_id && self::$current_job_id ) {
                ASI_log( 'Fatal error caught during processing: ' . $error['message'], 'CRON_FATAL' );
                
                // Get current retry count
                global $wpdb;
                ASI_Bulk_Generation_DB::init();
                $job_post = $wpdb->get_row( $wpdb->prepare(
                    "SELECT * FROM " . ASI_Bulk_Generation_DB::$table_posts . " WHERE id = %d",
                    self::$current_job_post_id
                ) );
                
                if ( $job_post ) {
                    $retry_count = isset( $job_post->retry_count ) ? (int) $job_post->retry_count : 0;
                    $retry_count++;
                    
                    if ( $retry_count >= self::MAX_RETRIES ) {
                        // Max retries reached, mark as failed
                        ASI_Bulk_Generation_DB::update_job_post( self::$current_job_post_id, array(
                            'status'        => 'failed',
                            'retry_count'   => $retry_count,
                            'error_message' => sprintf( 
                                __( 'Fatal error after %d attempts: %s', 'all-sources-images' ),
                                $retry_count,
                                $error['message']
                            ),
                            'processed_at'  => current_time( 'mysql' ),
                        ) );
                        ASI_Bulk_Generation_DB::increment_job_counter( self::$current_job_id, 'processed_posts', 1 );
                        ASI_Bulk_Generation_DB::increment_job_counter( self::$current_job_id, 'failed_posts', 1 );
                        ASI_log( 'Post ' . $job_post->post_id . ' failed after ' . $retry_count . ' attempts', 'CRON_FATAL' );
                    } else {
                        // Reset to pending for retry
                        ASI_Bulk_Generation_DB::update_job_post( self::$current_job_post_id, array(
                            'status'        => 'pending',
                            'retry_count'   => $retry_count,
                            'error_message' => sprintf(
                                __( 'Retry %d/%d after error: %s', 'all-sources-images' ),
                                $retry_count,
                                self::MAX_RETRIES,
                                $error['message']
                            ),
                        ) );
                        ASI_log( 'Post ' . $job_post->post_id . ' will retry (' . $retry_count . '/' . self::MAX_RETRIES . ')', 'CRON_RETRY' );
                    }
                }
                
                // Schedule next batch to continue processing
                if ( ! wp_next_scheduled( 'asi_bulk_process_batch', array( self::$current_job_id ) ) ) {
                    wp_schedule_single_event( time() + 5, 'asi_bulk_process_batch', array( self::$current_job_id ) );
                }
            }
        }
    }

    /**
     * Reset stuck posts that have been processing for too long
     *
     * @param int $job_id Job ID
     */
    private function reset_stuck_posts( $job_id ) {
        global $wpdb;
        
        ASI_Bulk_Generation_DB::init();
        
        $timeout_time = date( 'Y-m-d H:i:s', time() - self::PROCESSING_TIMEOUT );
        
        // Find posts stuck in 'processing' status
        $stuck_posts = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM " . ASI_Bulk_Generation_DB::$table_posts . " 
             WHERE job_id = %d 
             AND status = 'processing'",
            $job_id
        ) );
        
        foreach ( $stuck_posts as $post ) {
            $retry_count = isset( $post->retry_count ) ? (int) $post->retry_count : 0;
            $retry_count++;
            
            if ( $retry_count >= self::MAX_RETRIES ) {
                // Max retries, mark as failed
                ASI_Bulk_Generation_DB::update_job_post( $post->id, array(
                    'status'        => 'failed',
                    'retry_count'   => $retry_count,
                    'error_message' => __( 'Timed out after maximum retries', 'all-sources-images' ),
                    'processed_at'  => current_time( 'mysql' ),
                ) );
                ASI_Bulk_Generation_DB::increment_job_counter( $job_id, 'processed_posts', 1 );
                ASI_Bulk_Generation_DB::increment_job_counter( $job_id, 'failed_posts', 1 );
                ASI_log( 'Post ' . $post->post_id . ' timed out after ' . $retry_count . ' attempts', 'CRON_TIMEOUT' );
            } else {
                // Reset to pending for retry
                ASI_Bulk_Generation_DB::update_job_post( $post->id, array(
                    'status'      => 'pending',
                    'retry_count' => $retry_count,
                    'error_message' => sprintf(
                        __( 'Retry %d/%d after timeout', 'all-sources-images' ),
                        $retry_count,
                        self::MAX_RETRIES
                    ),
                ) );
                ASI_log( 'Post ' . $post->post_id . ' reset for retry after timeout (' . $retry_count . '/' . self::MAX_RETRIES . ')', 'CRON_TIMEOUT' );
            }
        }
    }

    /**
     * Process a job (entry point for cron)
     *
     * @param int $job_id Job ID
     */
    public function process_job( $job_id ) {
        $job = ASI_Bulk_Generation_DB::get_job( $job_id );

        if ( ! $job ) {
            return;
        }

        // Check if job should be processed
        if ( $job->job_status !== 'processing' ) {
            return;
        }

        // Process a batch
        $this->process_batch( $job_id );
    }

    /**
     * Process a batch of posts for a job
     *
     * @param int $job_id Job ID
     */
    public function process_batch( $job_id ) {
        $job = ASI_Bulk_Generation_DB::get_job( $job_id );

        if ( ! $job || $job->job_status !== 'processing' ) {
            return;
        }

        // First, reset any stuck posts that have been processing too long
        $this->reset_stuck_posts( $job_id );

        // Get next pending posts
        $processed_count = 0;
        $successful_count = 0;
        $failed_count = 0;

        for ( $i = 0; $i < self::BATCH_SIZE; $i++ ) {
            $job_post = ASI_Bulk_Generation_DB::get_next_pending_post( $job_id );

            if ( ! $job_post ) {
                // No more pending posts
                break;
            }

            // Set static vars for shutdown handler
            self::$current_job_id = $job_id;
            self::$current_job_post_id = $job_post->id;

            // Get current retry count
            $retry_count = isset( $job_post->retry_count ) ? (int) $job_post->retry_count : 0;

            // Mark as processing
            ASI_Bulk_Generation_DB::update_job_post( $job_post->id, array( 'status' => 'processing' ) );

            ASI_log( 'Processing post ' . $job_post->post_id . ' (attempt ' . ( $retry_count + 1 ) . '/' . self::MAX_RETRIES . ')', 'CRON_BATCH' );

            // Process this post
            $result = $this->process_single_post( $job, $job_post );

            // Clear static vars after successful processing
            self::$current_job_id = null;
            self::$current_job_post_id = null;

            $processed_count++;

            if ( $result['success'] ) {
                $successful_count++;
                ASI_Bulk_Generation_DB::mark_post_processed( $job_post->id, 'completed', array(
                    'featured_image_id'     => $result['featured_image_id'] ?? null,
                    'featured_image_status' => 'completed',
                    'search_keyword'        => $result['search_keyword'] ?? '',
                    'image_source'          => $result['image_source'] ?? '',
                    'retry_count'           => $retry_count,
                ) );
                ASI_log( 'Post ' . $job_post->post_id . ' completed successfully', 'CRON_BATCH' );
            } else {
                // Check if we should retry
                $retry_count++;
                
                if ( $retry_count >= self::MAX_RETRIES ) {
                    // Max retries reached, mark as failed
                    $failed_count++;
                    ASI_Bulk_Generation_DB::mark_post_processed( $job_post->id, 'failed', array(
                        'featured_image_status' => 'failed',
                        'error_message'         => sprintf(
                            __( 'Failed after %d attempts: %s', 'all-sources-images' ),
                            $retry_count,
                            $result['error'] ?? __( 'Unknown error', 'all-sources-images' )
                        ),
                        'retry_count'           => $retry_count,
                    ) );
                    ASI_log( 'Post ' . $job_post->post_id . ' failed after ' . $retry_count . ' attempts: ' . ( $result['error'] ?? 'Unknown error' ), 'CRON_BATCH' );
                } else {
                    // Reset to pending for retry (don't count as processed yet)
                    $processed_count--;
                    ASI_Bulk_Generation_DB::update_job_post( $job_post->id, array(
                        'status'        => 'pending',
                        'retry_count'   => $retry_count,
                        'error_message' => sprintf(
                            __( 'Retry %d/%d: %s', 'all-sources-images' ),
                            $retry_count,
                            self::MAX_RETRIES,
                            $result['error'] ?? __( 'Unknown error', 'all-sources-images' )
                        ),
                    ) );
                    ASI_log( 'Post ' . $job_post->post_id . ' queued for retry (' . $retry_count . '/' . self::MAX_RETRIES . '): ' . ( $result['error'] ?? 'Unknown error' ), 'CRON_RETRY' );
                }
            }
        }

        // Update job counters
        if ( $processed_count > 0 ) {
            ASI_Bulk_Generation_DB::increment_job_counter( $job_id, 'processed_posts', $processed_count );
        }
        if ( $successful_count > 0 ) {
            ASI_Bulk_Generation_DB::increment_job_counter( $job_id, 'successful_posts', $successful_count );
        }
        if ( $failed_count > 0 ) {
            ASI_Bulk_Generation_DB::increment_job_counter( $job_id, 'failed_posts', $failed_count );
        }

        // Check if job is complete
        $stats = ASI_Bulk_Generation_DB::get_job_stats( $job_id );

        if ( $stats['pending'] === 0 && $stats['processing'] === 0 ) {
            // Job complete
            ASI_Bulk_Generation_DB::update_job_status( $job_id, 'completed' );
            ASI_log( 'Job ' . $job_id . ' completed. Success: ' . $stats['completed'] . ', Failed: ' . $stats['failed'], 'CRON_JOB' );
        } else {
            // Schedule next batch with a small delay
            if ( ! wp_next_scheduled( 'asi_bulk_process_batch', array( $job_id ) ) ) {
                wp_schedule_single_event( time() + 2, 'asi_bulk_process_batch', array( $job_id ) );
            }
        }
    }

    /**
     * Process a single post (generate image)
     *
     * @param object $job      Job object
     * @param object $job_post Job post object
     * @return array Result with success status and details
     */
    private function process_single_post( $job, $job_post ) {
        $post_id = $job_post->post_id;
        $post = get_post( $post_id );

        if ( ! $post ) {
            return array(
                'success' => false,
                'error'   => __( 'Post not found', 'all-sources-images' ),
            );
        }

        // Skip if already has featured image and not set to overwrite
        $options = get_option( 'ASI_plugin_main_settings' );
        $options = wp_parse_args( $options, array( 'rewrite_featured' => 'false' ) );
        
        if ( has_post_thumbnail( $post_id ) && $options['rewrite_featured'] !== 'true' ) {
            return array(
                'success' => false,
                'error'   => __( 'Post already has featured image', 'all-sources-images' ),
            );
        }

        // Get the admin class instance
        global $all_sources_images_admin;

        if ( ! isset( $all_sources_images_admin ) || ! method_exists( $all_sources_images_admin, 'ASI_create_thumb' ) ) {
            // Try to get instance from global or create new one
            if ( class_exists( 'All_Sources_Images_Generation' ) ) {
                $generation = new All_Sources_Images_Generation( 'all-sources-images', ALL_SOURCES_IMAGES_VERSION );
                
                // Generate image using the existing method
                $result = $this->generate_image_for_post( $generation, $post_id, $job->images_per_post );
                
                return $result;
            }
            
            return array(
                'success' => false,
                'error'   => __( 'Image generation class not available', 'all-sources-images' ),
            );
        }

        // Use existing generation method
        $result = $this->generate_image_for_post( $all_sources_images_admin, $post_id, $job->images_per_post );

        return $result;
    }

    /**
     * Generate image for a post using the existing generation method
     *
     * @param object $generation Generation class instance
     * @param int    $post_id    Post ID
     * @param int    $images_count Number of images to generate
     * @return array Result
     */
    private function generate_image_for_post( $generation, $post_id, $images_count = 1 ) {
        $options = get_option( 'ASI_plugin_main_settings' );
        $options = wp_parse_args( $options, $generation->ASI_default_options_main_settings() );
        
        $banks_settings = get_option( 'ASI_plugin_banks_settings' );
        $banks_settings = wp_parse_args( $banks_settings, $generation->ASI_default_options_banks_settings() );

        $image_blocks = isset( $options['image_block'] ) ? $options['image_block'] : array( array() );
        $search_keyword = '';
        $image_source = '';
        $featured_image_id = null;
        $blocks_processed = 0;
        $blocks_success = 0;

        // Get rewrite_featured setting (used for all blocks)
        $rewrite_featured = ( isset( $options['rewrite_featured'] ) && $options['rewrite_featured'] === 'true' ) ? 1 : 0;

        // Add capability for cron execution (do this once before the loop)
        add_filter( 'user_has_cap', function( $allcaps ) {
            $allcaps['asi_manage'] = true;
            return $allcaps;
        } );

        // Process each image block up to the requested count
        $block_keys = array_keys( $image_blocks );
        $total_blocks = min( $images_count, count( $image_blocks ) );
        
        ASI_log( 'Starting image generation for post ' . $post_id . ' - Total blocks to process: ' . $total_blocks, 'CRON_GENERATE' );

        for ( $block_index = 0; $block_index < $total_blocks; $block_index++ ) {
            try {
                $blocks_processed++;
                
                // Get the API source from the image_block configuration
                // Each image_block can have its own api_chosen setting
                $current_block_key = isset( $block_keys[ $block_index ] ) ? $block_keys[ $block_index ] : $block_index;
                $current_block = isset( $image_blocks[ $current_block_key ] ) ? $image_blocks[ $current_block_key ] : array();
                
                // Get api_chosen from the image_block, fallback to banks_settings if not set
                $api_chosen = null; // Let ASI_create_thumb use the image_block's own api_chosen
                if ( isset( $current_block['api_chosen'] ) && ! empty( $current_block['api_chosen'] ) ) {
                    $api_chosen = $current_block['api_chosen'];
                }
                
                // Get image location for this block
                $image_location = isset( $current_block['image_location'] ) ? $current_block['image_location'] : 'featured';
                
                ASI_log( 'Processing block ' . ( $block_index + 1 ) . '/' . $total_blocks . ' for post ' . $post_id . ' - block_key: ' . $current_block_key . ', api_chosen: ' . print_r( $api_chosen, true ) . ', location: ' . $image_location, 'CRON_GENERATE' );
                
                // Call the existing generation method with correct parameters
                // Parameters: $id, $check_value_enable, $check_post_type, $check_category, 
                //             $rewrite_featured, $get_only_thumb, $extracted_search_term, $api_chosen, 
                //             $key_img_block, $avoid_revision, $include_datas, $button_autogenerate, $additional_context
                $result = $generation->ASI_create_thumb(
                    $post_id,           // $id
                    0,                  // $check_value_enable - don't check enable/disable
                    1,                  // $check_post_type - check post type
                    1,                  // $check_category - check category
                    $rewrite_featured,  // $rewrite_featured - from config
                    false,              // $get_only_thumb - full generation
                    null,               // $extracted_search_term - use default
                    $api_chosen,        // $api_chosen - from image_block config or null to use block's own
                    $current_block_key, // $key_img_block - use the actual block key
                    true,               // $avoid_revision
                    null,               // $include_datas
                    'bulk',             // $button_autogenerate
                    array()             // $additional_context
                );

                // Track success - for featured image blocks check has_post_thumbnail,
                // for content blocks, consider it successful if no exception was thrown
                if ( $image_location === 'featured' || $image_location === 'both' ) {
                    if ( has_post_thumbnail( $post_id ) ) {
                        $blocks_success++;
                        $featured_image_id = get_post_thumbnail_id( $post_id );
                        
                        // Get image source from the block config
                        if ( is_array( $api_chosen ) ) {
                            $image_source = implode( ', ', $api_chosen );
                        } elseif ( ! empty( $api_chosen ) ) {
                            $image_source = $api_chosen;
                        } else {
                            $image_source = isset( $current_block['api_chosen'] ) ? 
                                ( is_array( $current_block['api_chosen'] ) ? implode( ', ', $current_block['api_chosen'] ) : $current_block['api_chosen'] ) 
                                : 'unknown';
                        }
                        ASI_log( 'Block ' . ( $block_index + 1 ) . ' SUCCESS - Featured image set for post ' . $post_id, 'CRON_GENERATE' );
                    }
                } else {
                    // For content insertion blocks (custom), we assume success if no exception
                    $blocks_success++;
                    ASI_log( 'Block ' . ( $block_index + 1 ) . ' processed - Content image for post ' . $post_id . ' (location: ' . $image_location . ')', 'CRON_GENERATE' );
                }

            } catch ( Exception $e ) {
                ASI_log( 'Block ' . ( $block_index + 1 ) . ' FAILED for post ' . $post_id . ': ' . $e->getMessage(), 'CRON_ERROR' );
                // Continue to next block instead of returning
                continue;
            }
        }

        ASI_log( 'Finished processing post ' . $post_id . ' - Blocks processed: ' . $blocks_processed . ', Blocks successful: ' . $blocks_success, 'CRON_GENERATE' );

        // Return result based on overall success
        if ( $blocks_success > 0 ) {
            return array(
                'success'           => true,
                'featured_image_id' => $featured_image_id,
                'search_keyword'    => $search_keyword,
                'image_source'      => $image_source,
                'blocks_processed'  => $blocks_processed,
                'blocks_success'    => $blocks_success,
            );
        }

        return array(
            'success' => false,
            'error'   => __( 'Could not generate any images', 'all-sources-images' ),
        );
    }
}

// Initialize
new ASI_Bulk_Generation_Cron();

// Initialize
new ASI_Bulk_Generation_Cron();
