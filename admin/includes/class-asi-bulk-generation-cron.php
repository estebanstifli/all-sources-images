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
     * Batch size per cron execution
     */
    const BATCH_SIZE = 5;

    /**
     * Constructor - register cron hooks
     */
    public function __construct() {
        add_action( 'asi_bulk_process_job', array( $this, 'process_job' ) );
        add_action( 'asi_bulk_process_batch', array( $this, 'process_batch' ) );
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

            // Mark as processing
            ASI_Bulk_Generation_DB::update_job_post( $job_post->id, array( 'status' => 'processing' ) );

            // Process this post
            $result = $this->process_single_post( $job, $job_post );

            $processed_count++;

            if ( $result['success'] ) {
                $successful_count++;
                ASI_Bulk_Generation_DB::mark_post_processed( $job_post->id, 'completed', array(
                    'featured_image_id'     => $result['featured_image_id'] ?? null,
                    'featured_image_status' => 'completed',
                    'search_keyword'        => $result['search_keyword'] ?? '',
                    'image_source'          => $result['image_source'] ?? '',
                ) );
            } else {
                $failed_count++;
                ASI_Bulk_Generation_DB::mark_post_processed( $job_post->id, 'failed', array(
                    'featured_image_status' => 'failed',
                    'error_message'         => $result['error'] ?? __( 'Unknown error', 'magic-post-thumbnail' ),
                ) );
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
        } else {
            // Schedule next batch
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
                'error'   => __( 'Post not found', 'magic-post-thumbnail' ),
            );
        }

        // Skip if already has featured image and not set to overwrite
        $options = get_option( 'ASI_plugin_main_settings' );
        $options = wp_parse_args( $options, array( 'rewrite_featured' => 'false' ) );
        
        if ( has_post_thumbnail( $post_id ) && $options['rewrite_featured'] !== 'true' ) {
            return array(
                'success' => false,
                'error'   => __( 'Post already has featured image', 'magic-post-thumbnail' ),
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
                'error'   => __( 'Image generation class not available', 'magic-post-thumbnail' ),
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
        
        // Get the configured image bank
        $api_chosen = isset( $banks_settings['api_chosen_auto'] ) ? $banks_settings['api_chosen_auto'] : array( 'openverse' );

        // Process each image block up to the requested count
        for ( $block_index = 0; $block_index < min( $images_count, count( $image_blocks ) ); $block_index++ ) {
            try {
                // Add capability for cron execution
                add_filter( 'user_has_cap', function( $allcaps ) {
                    $allcaps['asi_manage'] = true;
                    return $allcaps;
                } );
                
                ASI_log( 'Calling ASI_create_thumb for post ' . $post_id . ' with api_chosen: ' . print_r( $api_chosen, true ), 'CRON_GENERATE' );
                
                // Call the existing generation method with correct parameters
                // Parameters: $id, $check_value_enable, $check_post_type, $check_category, 
                //             $rewrite_featured, $get_only_thumb, $extracted_search_term, $api_chosen, 
                //             $key_img_block, $avoid_revision, $include_datas, $button_autogenerate, $additional_context
                $result = $generation->ASI_create_thumb(
                    $post_id,           // $id
                    0,                  // $check_value_enable - don't check enable/disable
                    1,                  // $check_post_type - check post type
                    1,                  // $check_category - check category
                    0,                  // $rewrite_featured - don't overwrite
                    false,              // $get_only_thumb - full generation
                    null,               // $extracted_search_term - use default
                    $api_chosen,        // $api_chosen - pass the configured banks
                    $block_index,       // $key_img_block
                    true,               // $avoid_revision
                    null,               // $include_datas
                    'bulk',             // $button_autogenerate
                    array()             // $additional_context
                );

                // Check result
                if ( has_post_thumbnail( $post_id ) ) {
                    $featured_image_id = get_post_thumbnail_id( $post_id );
                    $image_source = is_array( $api_chosen ) ? implode( ', ', $api_chosen ) : $api_chosen;
                    
                    return array(
                        'success'           => true,
                        'featured_image_id' => $featured_image_id,
                        'search_keyword'    => $search_keyword,
                        'image_source'      => $image_source,
                    );
                }
            } catch ( Exception $e ) {
                ASI_log( 'Exception: ' . $e->getMessage(), 'CRON_ERROR' );
                return array(
                    'success' => false,
                    'error'   => $e->getMessage(),
                );
            }
        }

        // If we get here and no image was set
        if ( ! has_post_thumbnail( $post_id ) ) {
            return array(
                'success' => false,
                'error'   => __( 'Could not generate image', 'magic-post-thumbnail' ),
            );
        }

        return array(
            'success'           => true,
            'featured_image_id' => get_post_thumbnail_id( $post_id ),
            'search_keyword'    => $search_keyword,
            'image_source'      => $image_source,
        );
    }
}

// Initialize
new ASI_Bulk_Generation_Cron();
