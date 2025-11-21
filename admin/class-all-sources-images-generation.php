<?php

require_once plugin_dir_path( __FILE__ ) . 'sources/class-asi-image-source.php';
require_once plugin_dir_path( __FILE__ ) . 'sources/class-asi-source-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'sources/class-asi-source-gemini.php';
require_once plugin_dir_path( __FILE__ ) . 'sources/class-asi-source-workers-ai.php';

/**
 * The functionalities for ASI Images generation
 *
 * @package    All_Sources_Images
 * @subpackage All_Sources_Images/admin
 * @author     Magic Post Thumbnail <contact@magic-post-thumbnail.com>
 */
class All_Sources_Images_Generation extends All_Sources_Images_Admin {
    /**
     * The ID of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Lazy-loaded source manager used to resolve modular image providers.
     *
     * @var ASI_Source_Manager|null
     */
    private $source_manager = null;

    /**
     * Track if built-in sources have already been registered.
     *
     * @var bool
     */
    private $sources_registered = false;

    /**
     * Initialize the class and set its properties.
     *
     * @since    4.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        // Ajax calls
        // AJAX endpoints for image generation (with and without ASI prefix for compatibility)
        add_action( 'wp_ajax_nopriv_generate_image', array(&$this, 'ASI_ajax_call') );
        add_action( 'wp_ajax_generate_image', array(&$this, 'ASI_ajax_call') );
        add_action( 'wp_ajax_nopriv_asi_generate_image', array(&$this, 'ASI_ajax_call') );
        add_action( 'wp_ajax_asi_generate_image', array(&$this, 'ASI_ajax_call') );
        $main_settings = wp_parse_args( get_option( 'ASI_plugin_main_settings' ), $this->ASI_default_options_main_settings( FALSE ) );
        // Enable save_post hook
        if ( isset( $main_settings['enable_save_post_hook'] ) && 'enable' == $main_settings['enable_save_post_hook'] ) {
            add_action(
                'save_post',
                array(&$this, 'ASI_check_post_type'),
                10,
                3
            );
        }
    }

    /**
     * Retrieve the shared source manager and register built-in sources on demand.
     *
     * @return ASI_Source_Manager|null
     */
    private function ASI_get_source_manager_instance() {
        if ( ! class_exists( 'ASI_Source_Manager' ) ) {
            return null;
        }

        if ( null === $this->source_manager ) {
            $this->source_manager = ASI_Source_Manager::instance();
        }

        if ( false === $this->sources_registered ) {
            $this->ASI_register_builtin_sources();
            $this->sources_registered = true;
        }

        return $this->source_manager;
    }

    /**
     * Register core sources shipped with the plugin.
     */
    private function ASI_register_builtin_sources() {
        if ( ! $this->source_manager ) {
            return;
        }

        if ( class_exists( 'ASI_Source_Gemini' ) && ! $this->source_manager->has_source( 'gemini' ) ) {
            $this->source_manager->register_source( new ASI_Source_Gemini() );
        }

        if ( class_exists( 'ASI_Source_Workers_AI' ) && ! $this->source_manager->has_source( 'workers_ai' ) ) {
            $this->source_manager->register_source( new ASI_Source_Workers_AI() );
        }
    }

    /**
     * Ajax call for bulk generation
     *
     * @since    4.0.0
     */
    public function ASI_ajax_call() {
        // DEBUG: Log entry point
        error_log('[All Sources Images] [AJAX CALL] Started - POST data: ' . print_r($_POST, true));
        
        // Check if button "Generate Automatically" is clicked
        $button_autogenerate = ( isset( $_POST['buttonAutoGenerate'] ) ? boolval( $_POST['buttonAutoGenerate'] ) : false );
        
        // Convert the JSON-encoded post IDs into an array and sanitize them.
        $post_ids = array_map( 'absint', json_decode( $_POST['ids_mpt_generation'] ) );
        
        // DEBUG: Log security check values
        $can_manage = current_user_can( 'asi_manage' );  // Fixed: lowercase asi_manage
        $nonce_valid = wp_verify_nonce( $_POST['nonce'], 'ajax_nonce_All_Sources_Images' );
        error_log('[All Sources Images] [AJAX CALL] Security - can_manage: ' . ($can_manage ? 'YES' : 'NO') . ', nonce_valid: ' . ($nonce_valid ? 'YES' : 'NO'));
        
        // Security checks: Verify user capability and nonce for security.
        if ( !$can_manage || false === $nonce_valid ) {
            error_log('[All Sources Images] [AJAX CALL] FAILED - Security check failed');
            wp_send_json_error();
            // Send an error response if checks fail.
        }
        
        error_log('[All Sources Images] [AJAX CALL] Passed security checks');
        // Validate the presence of post IDs.
        if ( !isset( $_POST['ids_mpt_generation'] ) ) {
            return false;
        }
        // Exit if no post IDs are provided.
        // Count the number of posts for bulk processing.
        $count = count( $post_ids );
        // Re-index post IDs starting from 1 for consistency.
        foreach ( $post_ids as $key => $val ) {
            $post_ids_with_keys[$key + 1] = $val;
        }
        // Retrieve the current post index and ID from the AJAX request.
        $current_post_index = (int) $_POST['currentPostIndex'];
        $current_post_id = $post_ids_with_keys[$current_post_index];
        // Load plugin settings for image generation.
        $main_settings = get_option( 'ASI_plugin_main_settings' );
        // Check if the 'rewrite featured image' option is enabled.
        if ( isset( $main_settings['rewrite_featured'] ) && $main_settings['rewrite_featured'] == true ) {
            $rewrite_featured = true;
        } else {
            $rewrite_featured = false;
        }
        $int_blockIndex = 0;
        $counter_blocks = 1;
        $img_blocks = $main_settings['image_block'];
        $speed = '500';
        // Default speed for image generation.
        $current_image_index = $int_blockIndex;
        // Current image block index.
        $counter_img = $counter_blocks;
        // Total number of image blocks.
        $keys = array_keys( $img_blocks );
        $img_block = $img_blocks[$keys[$current_image_index]];
        // Current image block data.
        // display all infos under $img_block
        // Determine image location (featured or content).
        $image_location = ( !empty( $img_block['image_location'] ) ? $img_block['image_location'] : 'featured' );
        // Handle generation of the featured image.
        if ( has_post_thumbnail( $current_post_id ) && $rewrite_featured == false && $image_location == 'featured' ) {
            $generation_status = 'already-done';
            // Image already exists and rewriting is not needed.
        } elseif ( (!has_post_thumbnail( $current_post_id ) || $rewrite_featured == true || $image_location != 'featured') && $current_post_id != 0 ) {
            // Generate featured image if not present or if rewriting is enabled.
            $image_generation_result = $this->ASI_create_thumb(
                $current_post_id,
                '0',
                '0',
                '0',
                $rewrite_featured,
                false,
                null,
                null,
                $keys[$current_image_index],
                false,
                true,
                $button_autogenerate
            );
            // Check result and set generation status.
            $ASI_return = ( is_array( $image_generation_result ) ? $image_generation_result['id'] : $image_generation_result );
            if ( $ASI_return == null ) {
                $generation_status = 'failed';
            } else {
                $generation_status = 'successful';
                $speed = '500';
            }
        } else {
            $generation_status = 'error';
            // An error occurred during generation.
        }
        // Load compatibility settings for external plugins.
        $compatibility = wp_parse_args( get_option( 'ASI_plugin_compatibility_settings' ), $this->ASI_default_options_compatibility_settings( TRUE ) );
        $thumbnail_url = '';
        // Handle image preview when using the FIFU plugin.
        if ( true == $compatibility['enable_FIFU'] && 'FIFU' == $img_block['image_location'] && (is_plugin_active( 'featured-image-from-url/featured-image-from-url.php' ) || is_plugin_active( 'fifu-premium/fifu-premium.php' )) && $ASI_return != null ) {
        } elseif ( ($generation_status == 'already-done' || $generation_status == 'successful') && !empty( $ASI_return ) ) {
            // Display the newly generated image.
            $new_image = wp_get_attachment_image_src( $ASI_return, array(70, 70) );
            $thumbnail_preview_html = '<a class="generated-img" target="_blank" href="' . admin_url() . 'upload.php?item=' . $ASI_return . '"><img src="' . $new_image[0] . '" width="70" height="70" /></a>';
            $datas['thumbnail_id'] = $ASI_return;
            $datas['postimagediv'] = _wp_post_thumbnail_html( $ASI_return, $current_post_id );
        } elseif ( $generation_status == 'already-done' && "featured" === $image_location ) {
            // Display existing featured image.
            $thumbnail_preview_html = '<a class="generated-img" target="_blank" href="' . admin_url() . 'upload.php?item=' . get_post_thumbnail_id( $current_post_id ) . '">' . get_the_post_thumbnail( $current_post_id, array('70', '70') ) . '</a>';
        } else {
            // Display a placeholder image if generation fails.
            $thumbnail_preview_html = '<img src="' . plugins_url( 'img/no-image.jpg', __FILE__ ) . '" />';
        }
        // Prepare data for the next image block iteration.
        $current_image_index++;
        $datas['id'] = $current_post_id;
        $datas['status'] = $generation_status;
        $datas['img'] = $thumbnail_url;
        $datas['fimg'] = $thumbnail_preview_html;
        $datas['speed'] = $speed;
        $datas['blockIndex'] = $current_image_index;
        if ( is_array( $image_generation_result ) ) {
            $datas['keyword'] = ( isset( $image_generation_result['keyword'] ) ? $image_generation_result['keyword'] : '' );
            $datas['img_resolution'] = ( isset( $image_generation_result['img_resolution'] ) ? $image_generation_result['img_resolution'] : '' );
            $datas['img_size'] = ( isset( $image_generation_result['img_size'] ) ? $image_generation_result['img_size'] : '' );
            $datas['api_chosen'] = ( isset( $image_generation_result['api_chosen'] ) ? $image_generation_result['api_chosen'] : '' );
        }
        // If more image blocks are remaining, continue processing.
        if ( $current_image_index < $counter_img ) {
            $datas['nextPost'] = true;
            wp_send_json_success( $datas );
            // Send successful response for next iteration.
        } else {
            $datas['nextPost'] = false;
            // Send final response or error if data is incomplete.
            if ( !empty( $datas['id'] ) ) {
                wp_send_json_success( $datas );
            } else {
                wp_send_json_error( $datas );
            }
        }
    }

    public function ASI_check_post_type( $ID, $post, $update ) {
        // Checks whether the capacity has already been checked for this session
        if ( get_option( 'ASI_hook_checked' ) ) {
            // Deletes the option immediately after execution
            delete_option( 'ASI_hook_checked' );
            return;
            // Exits the function if it has already been executed
        }
        // Set capacity as verified to avoid additional calls
        update_option( 'ASI_hook_checked', true );
        $transient_key = 'ASI_wp_insert_processing_post_' . $ID;
        if ( wp_is_post_revision( $ID ) || wp_is_post_autosave( $ID ) ) {
            return;
        }
        if ( get_transient( $transient_key ) ) {
            return;
            // This post is already being processed
        } else {
            // Defines the transient to indicate that the post is being processed
            set_transient( $transient_key, true, 120 );
            // Expires after 2 minutes
        }
        global $pagenow;
        // Avoid not selected post types
        $main_settings = wp_parse_args( get_option( 'ASI_plugin_main_settings' ), $this->ASI_default_options_main_settings( FALSE ) );
        if ( empty( $main_settings['choosed_save_post_post_type'] ) ) {
            $choosed_post_type = $this->ASI_default_posts_types();
            $main_settings['choosed_save_post_post_type'] = $choosed_post_type["choosed_post_type"];
        }
        if ( !in_array( get_post_type( $ID ), $main_settings['choosed_save_post_post_type'] ) ) {
            return false;
        }
        //$active_posts_types	= wp_parse_args( get_option( 'ASI_plugin_posts_settings' ), $this->ASI_default_options_posts_settings( FALSE ) );
        $active_posts_types = $this->ASI_default_posts_types();
        // Avoid generation when click "Add New"
        if ( ($pagenow == 'index.php' || $pagenow == 'post.php') && in_array( get_post_type( $ID ), $active_posts_types['choosed_post_type'] ) ) {
            $main_settings = get_option( 'ASI_plugin_main_settings' );
            if ( isset( $main_settings['rewrite_featured'] ) && $main_settings['rewrite_featured'] == true ) {
                $rewrite_featured = true;
            } else {
                $rewrite_featured = false;
            }
            $img_blocks = $main_settings['image_block'];
            foreach ( $img_blocks as $key_img_block => $img_block ) {
                $this->ASI_create_thumb(
                    $ID,
                    '0',
                    '1',
                    '0',
                    $rewrite_featured,
                    false,
                    null,
                    null,
                    $key_img_block,
                    false,
                    false,
                    false  // button_autogenerate is false for save_post hook
                );
            }
        }
        // Delete the transient when processing is complete
        delete_transient( $transient_key );
    }

    /**
     * Checks if an image already exists in the WordPress media library
     * 
     * This function searches for an existing image in the media library 
     * based on the provided title and filename. It first cleans the filename
     * by removing numbers and extension, then performs a search in the 
     * WordPress database.
     *
     * @since    6.0.5
     * @param string $title The title of the image to search for
     * @param string $filename The filename of the image to search for
     * 
     * @return int|false Returns the attachment ID if found, false otherwise
     *
     * @access private
     */
    private function ASI_check_existing_image( $title, $filename ) {
        // Clean the base filename (remove extension and numbers)
        $base_filename = preg_replace( '/[0-9]+\\./', '.', $filename );
        $base_filename = pathinfo( $base_filename, PATHINFO_FILENAME );
        // Search in media library
        $args = array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'meta_query'     => array(array(
                'key'     => '_wp_attached_file',
                'value'   => $base_filename,
                'compare' => 'LIKE',
            )),
        );
        $query = new WP_Query($args);
        if ( $query->have_posts() ) {
            // If image already exists, return its ID
            return $query->posts[0]->ID;
        }
        return false;
    }

    /**
     * Retrieve Image from Database, save it into Media Library, and attach it to the post as featured image
     *
     * @since    4.0.0
     */
    public function ASI_create_thumb(
        $id,
        $check_value_enable = 0,
        $check_post_type = 1,
        $check_category = 1,
        $rewrite_featured = 0,
        $get_only_thumb = false,
        $extracted_search_term = null,
        $api_chosen = null,
        $key_img_block = null,
        $avoid_revision = null,
        $include_datas = null,
        $button_autogenerate = false
    ) {
        ASI_log( array(
            'id' => $id,
            'get_only_thumb' => $get_only_thumb,
            'api_chosen' => $api_chosen,
            'extracted_search_term' => $extracted_search_term
        ), 'CREATE_THUMB_START' );
        
        // Launch logs
        $log = $this->ASI_monolog_call();
        $log->info( 'New generation starting', array(
            'post' => $id,
        ) );
        //Avoid revision post type
        if ( 'revision' == get_post_type( $id ) && true !== $avoid_revision ) {
            ASI_log( 'Post is revision, avoiding', 'CREATE_THUMB_SKIP' );
            $log->error( 'This post is a revision. Avoided.', array(
                'post' => $id,
            ) );
            return false;
        }
        // Settings
        $main_settings = get_option( 'ASI_plugin_main_settings' );
        
        // For Gutenberg block (get_only_thumb), we don't need image_block config
        if ( TRUE == $get_only_thumb ) {
            // Create a minimal img_block array for Gutenberg
            $img_block = array(
                'api_chosen' => $api_chosen,
                'based_on' => 'title', // Not used in get_only_thumb mode
                'selected_image' => 'random_result',
            );
        } else {
            // one shot generation - use saved configuration
            if ( null == $key_img_block ) {
                if ( !isset($main_settings['image_block']) || empty($main_settings['image_block']) ) {
                    ASI_log( 'No image_block configuration found', 'CREATE_THUMB_ERROR' );
                    return false;
                }
                $key_img_block = array_key_first( $main_settings['image_block'] );
            }
            $img_block = $main_settings['image_block'][$key_img_block];
            if ( !isset( $img_block ) ) {
                return false;
            }
        }
        
        // Skip most validations for Gutenberg block mode (get_only_thumb)
        if ( TRUE !== $get_only_thumb ) {
            // Image location
            $image_location = ( !empty( $img_block['image_location'] ) ? $img_block['image_location'] : 'featured' );
            if ( "featured" !== $image_location ) {
                // Check if image generation into content is already done
                $content_post = get_post( $id );
                /*$image_generated = strpos( $content_post->post_content, 'mpt-img' ) ? true : false;
                		if( $image_generated ) {
                			return false;
                		}*/
            }
            // Check if thumbnail already exists
            if ( has_post_thumbnail( $id ) && $rewrite_featured == false && "featured" === $image_location ) {
                ASI_log( 'Featured image already exists', 'CREATE_THUMB_SKIP' );
                $log->error( 'Featured image already exists', array(
                    'post' => $id,
                ) );
                return false;
            }
            /* Action 'save_post' triggered when deleting posts. Check if post not trashed */
            if ( 'trash' == get_post_status( $id ) ) {
                ASI_log( 'Post is trashed', 'CREATE_THUMB_SKIP' );
                $log->error( 'Post is in the trash', array(
                    'post' => $id,
                ) );
                return false;
            }
            if ( !current_user_can( 'asi_manage' ) && !class_exists( 'Main_WPeMatico' ) && !class_exists( 'FeedWordPress' ) && !class_exists( 'rssPostImporter' ) && !class_exists( 'CyberSyn_Syndicator' ) && !class_exists( 'wp_automatic' ) ) {
                ASI_log( 'User lacks permissions', 'CREATE_THUMB_SKIP' );
                $log->error( 'The user does not have sufficient rights', array(
                    'post' => $id,
                ) );
                return false;
            }
            // Choosed Post types
            $post_types_default = $this->ASI_default_posts_types();
            $post_type_availables = $post_types_default['choosed_post_type'];
            $categories_availables = $post_types_default['choosed_categories'];
            /*
            		$post_type_availables  = ( ! empty( $posts_settings['choosed_post_type'] ) )  ? $posts_settings['choosed_post_type']  : '';
            		// Choosed Categories
            		$categories_availables = ( ! empty( $posts_settings['choosed_categories'] ) ) ? $posts_settings['choosed_categories'] : '';*/
            if ( !empty( $post_type_availables ) && $check_post_type ) {
                if ( !in_array( get_post_type( $id ), $post_type_availables ) ) {
                    ASI_log( 'Post type not in allowed list', 'CREATE_THUMB_SKIP' );
                    $log->error( 'The post is not in selected post types', array(
                        'post' => $id,
                    ) );
                    return false;
                }
            }
            // Check if category match and is a post
            if ( !empty( $categories_availables ) && $check_category && 'post' == get_post_type( $id ) ) {
                if ( !in_category( $categories_availables, $id ) ) {
                    ASI_log( 'Post category not in allowed list', 'CREATE_THUMB_SKIP' );
                    $log->error( 'The post is not in selected categories', array(
                        'post' => $id,
                    ) );
                    return false;
                }
            }
        }
        $options = wp_parse_args( get_option( 'ASI_plugin_main_settings' ), $this->ASI_default_options_main_settings( TRUE ) );
        $options_banks = wp_parse_args( get_option( 'ASI_plugin_banks_settings' ), $this->ASI_default_options_banks_settings( TRUE ) );
        $options_cron = wp_parse_args( get_option( 'ASI_plugin_cron_settings' ), $this->ASI_default_options_cron_settings( TRUE ) );
        $options = array_merge( $options, $options_banks, $options_cron );
        if ( isset( $api_chosen ) ) {
            $options['api_chosen'] = $api_chosen;
            $img_block['api_chosen'] = $api_chosen;
        } else {
            $options['api_chosen'] = $img_block['api_chosen'];
        }
        $executeElseBlock = true;
        $postcategories = get_the_category( $id );
        if ( $executeElseBlock ) {
            if ( TRUE == $get_only_thumb ) {
                $search = $extracted_search_term;
            } elseif ( $img_block['based_on'] == 'text_analyser' ) {
                $content = get_the_content( '', false, $id );
                $content = str_replace( "&nbsp;", ' ', $content );
                // Get lang option
                $selected_lang = $img_block['text_analyser_lang'];
                $search = $this->get_extracted_term( $content, $selected_lang );
                $log->info( 'Extracted search term', array(
                    'post'        => $id,
                    'search term' => $search,
                ) );
            } else {
                $search = get_post_field( 'post_title', $id, 'raw' );
            }
        }
        if ( isset( $img_block['title_selection'] ) && $img_block['title_selection'] == 'cut_title' && isset( $img_block['title_length'] ) ) {
            $length_title = (int) $img_block['title_length'] - 1;
            $search = preg_replace( '/((\\w+\\W*){' . $length_title . '}(\\w+))(.*)/', '${1}', $search );
        }
        
        // Translate search keywords to English if enabled
        if ( isset( $img_block['translation_EN'] ) && $img_block['translation_EN'] == 'true' ) {
            $wp_lang = get_bloginfo('language');
            $source_lang = substr( $wp_lang, 0, 2 );
            if ( $source_lang !== 'en' ) {
                $translated_search = $this->ASI_translate_text( $search, $source_lang, 'en' );
                if ( $translated_search !== false ) {
                    $search = $translated_search;
                    $log->info( 'Search term translated to English', array(
                        'post'   => $id,
                        'search' => $search,
                    ) );
                }
            }
        }
        if ( TRUE == $get_only_thumb ) {
            $source_manager = $this->ASI_get_source_manager_instance();
            if ( $source_manager && $source_manager->has_source( $img_block['api_chosen'] ) ) {
                $source = $source_manager->get_source( $img_block['api_chosen'] );
                $result = $source->generate( array(
                    'img_block'      => $img_block,
                    'options'        => $options,
                    'search'         => $search,
                    'log'            => $log,
                    'post_id'        => $id,
                    'get_only_thumb' => true,
                    'selected_image' => $img_block['selected_image'],
                    'proxy_args'     => $this->ASI_get_proxy_args(),
                ) );
                if ( is_wp_error( $result ) ) {
                    $log->error( 'Source generation failed', array(
                        'post'  => $id,
                        'error' => $result->get_error_message(),
                    ) );
                    ASI_log( array(
                        'post'    => $id,
                        'bank'    => $img_block['api_chosen'],
                        'message' => $result->get_error_message(),
                        'data'    => $result->get_error_data(),
                    ), 'GUTENBERG_BLOCK_SOURCE_ERROR' );
                    return false;
                }
                return $result;
            }

            /* SET ALL PARAMETERS */
            $array_parameters = $this->ASI_Get_Parameters( $img_block, $options, $search );
            
            ASI_log( array(
                'array_parameters' => $array_parameters,
                'has_url' => isset($array_parameters['url'])
            ), 'CREATE_THUMB_PARAMS' );
            
            $api_url = $array_parameters['url'];
            unset($array_parameters['url']);
            if ( !isset( $api_url ) ) {
                ASI_log( 'API URL not provided for bank: ' . $img_block['api_chosen'], 'CREATE_THUMB_ERROR' );
                $log->error( 'API URL not provided', array(
                    'post' => $id,
                ) );
                return false;
            }
            $result_body = $this->ASI_Generate(
                $img_block['api_chosen'],
                $api_url,
                $array_parameters,
                $img_block['selected_image'],
                $get_only_thumb,
                $search
            );
            return $result_body;
        }
        // Check if button "Generate Automatically" is clicked
        if ( true == $button_autogenerate && is_array( $options_banks['api_chosen_auto'] ) ) {
            $log->info( 'Button "Generate Automatically" clicked', array(
                'post' => $id,
            ) );
            $last_bank_element = end( $options_banks['api_chosen_auto'] );
            $proxy_args = $this->ASI_get_proxy_args();
            foreach ( $options_banks['api_chosen_auto'] as $bank ) {
                // Reset options according new image bank
                $options['api_chosen'] = $bank;

                $source_manager = $this->ASI_get_source_manager_instance();
                if ( $source_manager && $source_manager->has_source( $bank ) ) {
                    $context_block = $img_block;
                    $context_block['api_chosen'] = $bank;
                    $source = $source_manager->get_source( $bank );
                    $result = $source->generate( array(
                        'img_block'      => $context_block,
                        'options'        => $options,
                        'search'         => $search,
                        'log'            => $log,
                        'post_id'        => $id,
                        'get_only_thumb' => false,
                        'selected_image' => $context_block['selected_image'],
                        'proxy_args'     => $proxy_args,
                    ) );

                    if ( is_wp_error( $result ) ) {
                        $log->info( 'No results with ' . $bank, array(
                            'post'  => $id,
                            'error' => $result->get_error_message(),
                        ) );
                        if ( $bank === $last_bank_element ) {
                            $log->info( 'No image found with all banks selected', array(
                                'post' => $id,
                            ) );
                            return false;
                        }
                        continue;
                    }

                    $url_results = $result['url_results'];
                    $file_media = $result['file_media'];
                    $alt_img = isset( $result['alt_img'] ) ? $result['alt_img'] : '';
                    $caption_img = isset( $result['caption_img'] ) ? $result['caption_img'] : '';
                    break;
                }

                $array_parameters = $this->ASI_Get_Parameters( $options, $options, $search );
                $api_url = $array_parameters['url'];
                unset($array_parameters['url']);
                if ( !isset( $api_url ) ) {
                    $log->error( 'API URL not provided', array(
                        'post' => $id,
                    ) );
                    continue;
                }
                /* GET THE IMAGE URL */
                list( $url_results, $file_media, $alt_img ) = $this->ASI_Generate(
                    $bank,
                    $api_url,
                    $array_parameters,
                    $img_block['selected_image'],
                    false,
                    $search
                );
                if ( !isset( $url_results ) || !isset( $file_media ) ) {
                    $log->info( 'No results with ' . $bank, array(
                        'post' => $id,
                    ) );
                    // Return false with no results with each image bank
                    if ( $bank === $last_bank_element ) {
                        $log->info( 'No image found with all banks selected', array(
                            'post' => $id,
                        ) );
                        return false;
                    }
                } else {
                    // OK
                    break;
                }
            }
        } else {
            // Process the main image block
            $result = $this->ASI_Process_Image_Block(
                $img_block,
                $options,
                $search,
                $log,
                $id
            );
            // If the first API call fails, try the second image bank
            if ( $result === false && isset( $img_block['api_chosen_2'] ) && $img_block['api_chosen_2'] !== 'none' ) {
                $log->info( 'Second image bank used', array(
                    'post' => $id,
                ) );
                $img_block['api_chosen'] = $img_block['api_chosen_2'];
                $result = $this->ASI_Process_Image_Block(
                    $img_block,
                    $options,
                    $search,
                    $log,
                    $id
                );
            }
            // If both API calls fail, return false
            if ( $result === false ) {
                return false;
            }
            // Extract results and continue processing
            extract( $result );
        }
        $compatibility = wp_parse_args( get_option( 'ASI_plugin_compatibility_settings' ), $this->ASI_default_options_compatibility_settings( TRUE ) );
        $path_parts = pathinfo( $url_results );
        $filename = $path_parts['basename'];
        $wp_upload_dir = wp_upload_dir();
        /* Get the good file extension */
        $filetype = array(
            'image/png',
            'image/jpeg',
            'image/gif',
            'image/bmp',
            'image/vnd.microsoft.icon',
            'image/tiff',
            'image/svg+xml',
            'image/svg+xml',
            'image/webp'
        );
        $extensions = array(
            'png',
            'jpg',
            'gif',
            'bmp',
            'ico',
            'tif',
            'svg',
            'svgz',
            'webp'
        );
        if ( isset( $file_media['headers']['content-type'] ) ) {
            $imgextension = str_replace(
                $filetype,
                $extensions,
                $file_media['headers']['content-type'],
                $count_extension
            );
            /* Default type if not found : jpg */
            if ( (int) $count_extension == 0 ) {
                $imgextension = 'jpg';
            }
        } else {
            $imgextension = $path_parts['extension'];
        }
        $keywords_search = $search;
        $log->info( 'Search term', array(
            'post'        => $id,
            'Search term' => $keywords_search,
        ) );
        /* Image filename : title extension */
        $search = str_replace( '%', '', sanitize_title( $search ) );
        // Remove % for non-latin characters
        // Check if the 'resuse image' option is enabled.
        if ( isset( $main_settings['image_reuse'] ) && $main_settings['image_reuse'] == true ) {
            // Check if image file already exist if option is set
            $proposed_filename = sanitize_title( $search );
            $existing_image_id = $this->ASI_check_existing_image( get_the_title( $id ), $proposed_filename );
            if ( $existing_image_id ) {
                // Use the existing image instead of downloading a new one
                $log->info( 'Featured image with existing image (option set)', array(
                    'post'  => $id,
                    'image' => $existing_image_id,
                ) );
                set_post_thumbnail( $id, $existing_image_id );
                do_action( 'ASI_after_create_thumb', $id, $existing_image_id );
                return $existing_image_id;
            }
        }
        if ( $options['image_filename'] == 'date' ) {
            $current_time = current_time( 'Y-m-d' );
            $filename = wp_unique_filename( $wp_upload_dir['path'], $current_time . '.' . $imgextension );
        } elseif ( $options['image_filename'] == 'random' ) {
            $filename_rand = wp_rand( 1, 999999 );
            $filename = wp_unique_filename( $wp_upload_dir['path'], $filename_rand . '.' . $imgextension );
        } else {
            $filename = wp_unique_filename( $wp_upload_dir['path'], $search . '.' . $imgextension );
        }
        $folder = $wp_upload_dir['path'] . '/' . $filename;
        if ( $file_media['response']['code'] != '200' || empty( $file_media['body'] ) ) {
            $log->error( 'Problem with scrapping', array(
                'post' => $id,
            ) );
            return false;
        }
        if ( $file_media['body'] ) {
            /* Upload the file to wordpress directory */
            $file_upload = file_put_contents( $folder, $file_media['body'] );
            /* Convert png to jpeg for dalle */
            $png_jpg = ( !empty( $options['dallev1']['convert_jpg'] ) ? $options['dallev1']['convert_jpg'] : '' );
            //if( ( true == $png_jpg ) && ( 'dallev1' == $options['api_chosen'] ) ) {
            if ( true == $png_jpg && 'dallev1' == $img_block['api_chosen'] ) {
                $image = imagecreatefrompng( $folder );
                // Remove old png file
                unlink( $folder );
                $folder = str_replace( ".png", ".jpg", $folder );
                $filename = str_replace( ".png", ".jpg", $filename );
                imagejpeg( $image, $folder, 90 );
                imagedestroy( $image );
            }
            if ( $file_upload ) {
                $wp_filetype = wp_check_filetype( basename( $filename ), null );
                $wp_upload_dir = wp_upload_dir();
                $attachment = array(
                    'guid'           => $wp_upload_dir['url'] . '/' . urlencode( $filename ),
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title'     => $search,
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                );
                $attach_id = wp_insert_attachment( $attachment, $wp_upload_dir['path'] . '/' . urlencode( $filename ) );
                // Add alt text for image
                update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt_img );
                // Add caption text for image
                if ( !empty( $caption_img ) ) {
                    wp_update_post( array(
                        'ID'           => $attach_id,
                        'post_excerpt' => $caption_img,
                    ) );
                }
                /* Fire filter "wp_handle_upload" for plugins like optimizers etc. */
                $img_values = array(
                    'file' => $wp_upload_dir['path'] . '/' . urlencode( $filename ),
                    'url'  => $wp_upload_dir['url'] . '/' . urlencode( $filename ),
                    'type' => $wp_filetype['type'],
                );
                apply_filters( 'wp_handle_upload', $img_values );
                require_once ABSPATH . 'wp-admin/includes/image.php';
                $attach_data = wp_generate_attachment_metadata( $attach_id, $wp_upload_dir['path'] . '/' . urlencode( $filename ) );
                $update_attach_data = wp_update_attachment_metadata( $attach_id, $attach_data );
                $executeElseBlock = true;
                $plugin_desactivated = false;
                $missing_field = false;
                // If a field plugin is disabled and should be used
                if ( true === $plugin_desactivated || true === $missing_field ) {
                    return false;
                }
                if ( $executeElseBlock ) {
                    if ( "custom" === $image_location ) {
                        $tag = $img_block['image_custom_location_tag'];
                        $placement = $img_block['image_custom_location_placement'];
                        $position = $img_block['image_custom_location_position'];
                        $image_size = $img_block['image_custom_image_size'];
                        $content = $this->ASI_insert_content_image(
                            $content_post->post_content,
                            $attach_id,
                            $tag,
                            $placement,
                            $position,
                            $image_size
                        );
                    } else {
                        $log->info( 'Featured image added', array(
                            'post'  => $id,
                            'image' => $attach_id,
                        ) );
                        set_post_thumbnail( $id, $attach_id );
                    }
                }
                // Link the media to the post
                $media_link_uploaded_to = wp_update_post( array(
                    'ID'          => $attach_id,
                    'post_parent' => $id,
                ), true );
                if ( "custom" === $image_location ) {
                    // Array of post category ids
                    $arr_post_category = array();
                    foreach ( $postcategories as $postcategory ) {
                        $arr_post_category[] = (int) $postcategory->cat_ID;
                    }
                    /*
                     * Remove save_post Action to avoid infinite loop.
                     * cf https://developer.wordpress.org/reference/hooks/save_post/#avoiding-infinite-loops
                     * Use "remove_all_actions" instead of "remove_action". Otherwise the log file bug
                     * remove_action( 'save_post', array( &$this, 'ASI_create_thumb' ) );
                     */
                    remove_all_actions( 'save_post' );
                    wp_insert_post( array(
                        'ID'                    => $id,
                        'post_content'          => $content,
                        'post_category'         => $arr_post_category,
                        'post_title'            => $content_post->post_title,
                        'post_status'           => $content_post->post_status,
                        'post_author'           => $content_post->post_author,
                        'post_date'             => $content_post->post_date,
                        'post_date_gmt'         => $content_post->post_date_gmt,
                        'post_excerpt'          => $content_post->post_excerpt,
                        'comment_status'        => $content_post->comment_status,
                        'ping_status'           => $content_post->ping_status,
                        'post_password'         => $content_post->post_password,
                        'post_name'             => $content_post->post_name,
                        'to_ping'               => $content_post->to_ping,
                        'pinged'                => $content_post->pinged,
                        'post_modified'         => $content_post->post_modified,
                        'post_modified_gmt'     => $content_post->post_modified_gmt,
                        'post_content_filtered' => $content_post->post_content_filtered,
                        'post_parent'           => $content_post->post_parent,
                        'menu_order'            => $content_post->menu_order,
                        'post_type'             => $content_post->post_type,
                        'post_mime_type'        => $content_post->post_mime_type,
                    ) );
                    add_action( 'save_post', array(&$this, 'ASI_create_thumb') );
                    $log->info( 'Image added into the post', array(
                        'post'  => $id,
                        'image' => $attach_id,
                    ) );
                }
                do_action( 'ASI_after_create_thumb', $id, $attach_id );
                if ( true === $include_datas ) {
                    return array(
                        'id'             => $attach_id,
                        'keyword'        => $keywords_search,
                        'img_resolution' => $attach_data['width'] . 'x' . $attach_data['height'] . 'px',
                        'img_size'       => $this->ASI_get_image_size_in_bytes( $attach_id ),
                        'api_chosen'     => $img_block['api_chosen'],
                    );
                } else {
                    return $attach_id;
                }
            }
        }
    }

    /**
     * Generate an image and handle related errors
     * 
     * @param array $img_block Image block settings
     * @param array $options Plugin options
     * @param string $search Search term
     * @param object $log Logger instance
     * @param int $id Post ID
     * @return array|false Returns generated image data or false on failure
     */
    private function ASI_Process_Image_Block(
        $img_block,
        $options,
        $search,
        $log,
        $id
    ) {
        $source_manager = $this->ASI_get_source_manager_instance();
        if ( $source_manager && $source_manager->has_source( $img_block['api_chosen'] ) ) {
            $source = $source_manager->get_source( $img_block['api_chosen'] );
            $result = $source->generate( array(
                'img_block'      => $img_block,
                'options'        => $options,
                'search'         => $search,
                'log'            => $log,
                'post_id'        => $id,
                'get_only_thumb' => false,
                'selected_image' => $img_block['selected_image'],
                'proxy_args'     => $this->ASI_get_proxy_args(),
            ) );
            if ( is_wp_error( $result ) ) {
                $log->error( 'Source generation failed', array(
                    'post'  => $id,
                    'error' => $result->get_error_message(),
                ) );
                return false;
            }
            return $result;
        }

        // Set all parameters
        $array_parameters = $this->ASI_Get_Parameters( $img_block, $options, $search );
        $api_url = ( isset( $array_parameters['url'] ) ? $array_parameters['url'] : null );
        unset($array_parameters['url']);
        // Check if API URL is provided
        if ( !$api_url ) {
            $log->error( 'API URL not provided', array(
                'post' => $id,
            ) );
            return false;
        }
        // Get the image URL
        list( $url_results, $file_media, $alt_img, $caption_img ) = $this->ASI_Generate(
            $img_block['api_chosen'],
            $api_url,
            $array_parameters,
            $img_block['selected_image'],
            false,
            $search
        );
        // Check if results are valid
        if ( !isset( $url_results ) || !isset( $file_media ) ) {
            $log->error( 'No results', array(
                'post' => $id,
            ) );
            return false;
        }
        // Return the generated image data
        return compact(
            'url_results',
            'file_media',
            'alt_img',
            'caption_img'
        );
    }

    /**
     * Get Image size in ko
     *
     * @since 6.0.0
     */
    private function ASI_get_image_size_in_bytes( $attachment_id ) {
        // Full path of the image
        $image_path = get_attached_file( $attachment_id );
        if ( $image_path && file_exists( $image_path ) ) {
            // File size in bytes
            $image_size = filesize( $image_path );
        }
        // Convert to kilobytes
        $image_size_kb = round( $image_size / 1024, 2 );
        return $image_size_kb . 'ko';
    }

    /**
     * Insert an image before or after a specific Gutenberg block
     *
     * @since 5.2.0
     */
    private function ASI_insert_content_image(
        $content,
        $attach_id,
        $tag = 'p',
        $placement = 'after',
        $position = '1',
        $image_size = 'large'
    ) {
        // Check if the Classic Editor is being used
        $classic_editor = $this->ASI_is_using_classic_editor();
        $match = false;
        // Variable to track if the image was inserted successfully
        // Loop until the image insertion is successful
        while ( !$match ) {
            // Prepare the HTML block for the image depending on the editor type
            if ( $classic_editor ) {
                $match = true;
                // Set match to true since we'll insert the image for Classic Editor
                $image = wp_get_attachment_image(
                    $attach_id,
                    $image_size,
                    false,
                    array(
                        'class' => 'wp-image-' . $attach_id,
                    )
                );
                $image_block = '<p>' . $image . '</p>';
            } else {
                // Get image details for Gutenberg
                $image_src = wp_get_attachment_image_src( $attach_id, $image_size, false );
                $alt_text = get_post_meta( $attach_id, '_wp_attachment_image_alt', true );
                $image = '<img src="' . esc_url( $image_src[0] ) . '" alt="' . esc_attr( $alt_text ) . '" class="wp-image-' . esc_attr( $attach_id ) . '"/>';
                // Include caption if enabled in settings
                $options = wp_parse_args( get_option( 'ASI_plugin_main_settings' ), $this->ASI_default_options_main_settings( TRUE ) );
                if ( isset( $options['enable_caption'] ) && 'enable' == $options['enable_caption'] ) {
                    $caption_text = wp_get_attachment_caption( $attach_id );
                    $image .= '<figcaption class="wp-element-caption">' . esc_html( $caption_text ) . '</figcaption>';
                }
                // Wrap the image in a Gutenberg image block format
                $image_block = '<!-- wp:image {"id":' . $attach_id . ',"linkDestination":"none"} -->';
                $image_block .= '<figure class="wp-block-image">' . $image . '</figure>';
                $image_block .= '<!-- /wp:image -->';
            }
            // Define the tag-specific search pattern
            if ( $classic_editor ) {
                // Patterns for Classic Editor content
                $start_pattern = '<' . $tag;
                $end_pattern = '</' . $tag . '>';
                $pattern = '/(' . preg_quote( $start_pattern, '/' ) . '.*?' . preg_quote( $end_pattern, '/' ) . ')/s';
                $parts = preg_split(
                    $pattern,
                    $content,
                    -1,
                    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
                );
                // Remove empty content
                $filtered_array = array_filter( $parts, function ( $value ) {
                    return trim( $value ) !== '';
                } );
                // Re-indexing of table keys if necessary
                $parts = array_values( $filtered_array );
                // Restart the loop if can not find paragraphs
                if ( 'p' == $tag && count( $parts ) < 2 ) {
                    //Apply wpautop() for next loop
                    $content = wpautop( $content );
                    $match = false;
                    continue;
                }
            } else {
                // Patterns for Gutenberg Editor, ensuring separate blocks
                if ( $tag == 'p' ) {
                    $start_pattern = '<!-- wp:paragraph';
                    $end_pattern = '<\\/p>\\s*<!-- \\/wp:paragraph -->';
                } elseif ( $tag == 'a' ) {
                    $start_pattern = '<!-- wp:html -->';
                    $end_pattern = '<!-- /wp:html -->';
                } else {
                    $start_pattern = '<!-- wp:heading';
                    $end_pattern = '<\\/h[1-6]>\\s*<!-- \\/wp:heading -->';
                }
                $pattern = '/(' . preg_quote( $start_pattern, '/' ) . '.*?' . $end_pattern . ')/s';
                $parts = preg_split(
                    $pattern,
                    $content,
                    -1,
                    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
                );
            }
            // Initialize variables for constructing the new content
            $new_content = '';
            $counter = 0;
            $total_tags = substr_count( $content, $start_pattern );
            // Loop through each part to build the new content with the image block inserted
            foreach ( $parts as $part ) {
                // Check if part contains the target tag
                $contains_tag = preg_match( $pattern, $part );
                // Insert image before tag if 'before' placement is specified
                if ( $placement == 'before' && $contains_tag ) {
                    $counter++;
                    if ( $position == 'last' && $counter == $total_tags || $counter == $position ) {
                        if ( $classic_editor ) {
                            $new_content .= '</p>' . $image_block . '<p>';
                        } else {
                            $new_content .= $image_block;
                        }
                        $match = true;
                    }
                }
                // Add the current part to new content
                $new_content .= $part;
                // Insert image after tag if 'after' placement is specified
                if ( $placement == 'after' && $contains_tag ) {
                    $counter++;
                    if ( $position == 'last' && $counter == $total_tags || $counter == $position ) {
                        if ( $classic_editor ) {
                            $new_content .= '</p>' . $image_block . '<p>';
                        } else {
                            $new_content .= $image_block;
                        }
                        $match = true;
                    }
                }
            }
            // If no match found, switch to Classic Editor as a fallback
            if ( !$match ) {
                $classic_editor = true;
            }
        }
        return $new_content;
    }

    /**
     * Extract a specific paragraph from the post content.
     *
     * @since    6.0.0
     */
    private function ASI_extract_adjacent_element(
        $content,
        $element = 'p',
        $element_number = 1,
        $direction = 'text_analyser_previous_paragraph'
    ) {
        // Define the pattern to match the specified element
        $pattern = '/<(' . preg_quote( $element, '/' ) . ')(.*?)>(.*?)<\\/\\1>/s';
        // Find all matches of the specified element
        preg_match_all( $pattern, $content, $matches );
        // Check if the element_number is "last"
        if ( $element_number === 'last' ) {
            // Ensure the index is valid and points to the last element
            $index = count( $matches[0] ) - 1;
        } else {
            // Convert to zero-based index
            $index = $element_number - 1;
        }
        // Check if we have a valid index
        if ( isset( $matches[0][$index] ) ) {
            if ( $direction === 'text_analyser_previous_paragraph' || $element_number === 'last' ) {
                // Get the position of the target element in the content
                if ( 'p' == $element ) {
                    $target_position = strpos( $content, $matches[0][$index + 1] );
                } else {
                    $target_position = strpos( $content, $matches[0][$index] );
                }
                // Get the content before the target element
                $content_before = substr( $content, 0, $target_position );
                // Match all paragraphs before the target element
                $paragraph_pattern = '/<p\\b[^>]*>(.*?)<\\/p>/s';
                preg_match_all( $paragraph_pattern, $content_before, $paragraph_matches );
                // Remove too shorts paragraphs. For exemple "<p>&nbsp;</p>"
                if ( strlen( $paragraph_matches[0][0] ) < 15 ) {
                    unset($paragraph_matches[0][0]);
                    $paragraph_matches[0] = array_values( $paragraph_matches[0] );
                }
                // Return the last paragraph found before the target element
                if ( !empty( $paragraph_matches[0] ) ) {
                    // Return only the last paragraph, cleaned of HTML tags
                    return ( is_string( $paragraph_matches[0][count( $paragraph_matches[0] ) - 1] ) ? trim( strip_tags( $paragraph_matches[0][count( $paragraph_matches[0] ) - 1] ) ) : '' );
                }
            } elseif ( $direction === 'text_analyser_next_paragraph' ) {
                // Get the position of the target element in the content
                $target_position = strpos( $content, $matches[0][$index] );
                // Get the content before the target element
                $content_before = substr( $content, 0, $target_position );
                // Get the content after the target element
                $content_after = substr( $content, $target_position + strlen( $matches[0][$index] ) );
                // Match the first paragraph after the target element
                $paragraph_pattern = '/<p\\b[^>]*>(.*?)<\\/p>/s';
                preg_match_all( $paragraph_pattern, $content_after, $paragraph_matches );
                // Remove too shorts paragraphs. For exemple "<p>&nbsp;</p>"
                if ( strlen( $paragraph_matches[0][0] ) < 15 ) {
                    unset($paragraph_matches[0][0]);
                    $paragraph_matches[0] = array_values( $paragraph_matches[0] );
                }
                // Return the first paragraph found after the target element
                if ( !empty( $paragraph_matches[0] ) ) {
                    return ( is_string( $paragraph_matches[0][0] ) ? trim( strip_tags( $paragraph_matches[0][0] ) ) : '' );
                }
            }
        }
        return '';
        // Return empty if not found
    }

    /**
     * Check Classic Editor plugin
     *
     * @since    5.2.0
     */
    private function ASI_is_using_classic_editor() {
        // Include the is_plugin_active function if it's not already defined
        if ( !function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        // Check if the Classic Editor plugin is active
        return is_plugin_active( 'classic-editor/classic-editor.php' );
    }

    /**
     * Get all settings from user
     *
     * @since    4.0.0
     */
    private function ASI_Get_Parameters( $img_block, $options, $search ) {
        ASI_log( array(
            'bank' => $img_block['api_chosen'],
            'search' => $search
        ), 'GET_PARAMETERS' );
        
        /* GOOGLE IMAGE SCRAPING PARAMETERS */
        if ( $img_block['api_chosen'] == 'google_scraping' ) {
            $country = ( !empty( $options['google_scraping']['search_country'] ) ? $options['google_scraping']['search_country'] : 'en' );
            $img_color = ( !empty( $options['google_scraping']['img_color'] ) ? $options['google_scraping']['img_color'] : '' );
            $imgsz = ( !empty( $options['google_scraping']['imgsz'] ) ? $options['google_scraping']['imgsz'] : '' );
            $format = ( !empty( $options['google_scraping']['format'] ) ? $options['google_scraping']['format'] : '' );
            $imgtype = ( !empty( $options['google_scraping']['imgtype'] ) ? $options['google_scraping']['imgtype'] : '' );
            $rights = ( !empty( $options['google_scraping']['rights'] ) ? $options['google_scraping']['rights'] : '' );
            $safe = ( !empty( $options['google_scraping']['safe'] ) ? $options['google_scraping']['safe'] : 'medium' );
            // Old API option. Replace value for safe
            if ( $safe == 'moderate' ) {
                $safe = 'medium';
            }
            // Remove very special characters
            $search = str_replace( '…', '', $search );
            $array_parameters = array(
                'url'  => 'http://www.google.com/search',
                'tbm'  => 'isch',
                'q'    => urlencode( $search ),
                'hl'   => $country,
                'safe' => $safe,
                'rsz'  => '3',
                'tbs'  => '',
            );
            if ( !empty( $rights ) ) {
                $array_parameters['tbs'] .= 'sur:' . $rights . ',';
            }
            if ( !empty( $imgtype ) ) {
                $array_parameters['tbs'] .= 'itp:' . $imgtype . ',';
            }
            if ( !empty( $imgsz ) ) {
                $array_parameters['tbs'] .= 'isz:' . $imgsz . ',';
            }
            if ( !empty( $format ) ) {
                $array_parameters['tbs'] .= 'iar:' . $format . ',';
            }
            if ( !empty( $img_color ) ) {
                $array_parameters['tbs'] .= 'ic:specific,isc:' . $img_color;
            }
        } elseif ( $img_block['api_chosen'] == 'google_image' ) {
            if ( empty( $options['googleimage']['cxid'] ) || empty( $options['googleimage']['apikey'] ) ) {
                return false;
            }
            $country = ( !empty( $options['googleimage']['search_country'] ) ? $options['googleimage']['search_country'] : 'en' );
            $img_color = ( !empty( $options['googleimage']['img_color'] ) ? $options['googleimage']['img_color'] : '' );
            $filetype = ( !empty( $options['googleimage']['filetype'] ) ? $options['googleimage']['filetype'] : '' );
            $imgsz = ( !empty( $options['googleimage']['imgsz'] ) ? $options['googleimage']['imgsz'] : 'large' );
            $imgtype = ( !empty( $options['googleimage']['imgtype'] ) ? $options['googleimage']['imgtype'] : '' );
            $safe = ( !empty( $options['googleimage']['safe'] ) ? $options['googleimage']['safe'] : 'medium' );
            // Old API option. Replace value for safe
            if ( $safe == 'moderate' ) {
                $safe = 'medium';
            }
            if ( isset( $options['googleimage']['rights'] ) && !empty( $options['googleimage']['rights'] ) ) {
                $rights = '(';
                $last_right = array_keys( $options['googleimage']['rights'] );
                $last_right = end( $last_right );
                foreach ( $options['googleimage']['rights'] as $rights_into_searching ) {
                    $rights .= $rights_into_searching;
                    if ( $rights_into_searching != $last_right ) {
                        $rights .= '|';
                    }
                }
                $rights .= ')';
            } else {
                $rights = '';
            }
            $array_parameters = array(
                'url'      => 'https://www.googleapis.com/customsearch/v1',
                'imgSize'  => $imgsz,
                'rights'   => $rights,
                'imgtype'  => $imgtype,
                'hl'       => $country,
                'filetype' => $filetype,
                'safe'     => $safe,
                'rsz'      => '3',
                'q'        => urlencode( $search ),
                'userip'   => $_SERVER['SERVER_ADDR'],
                'cx'       => trim( $options['googleimage']['cxid'] ),
                'key'      => trim( $options['googleimage']['apikey'] ),
            );
            if ( !empty( $img_color ) ) {
                $array_parameters['imgDominantColor'] = $img_color;
            }
        } elseif ( $img_block['api_chosen'] == 'dallev1' ) {
            $api_key = ( !empty( $options['dallev1']['apikey'] ) ? $options['dallev1']['apikey'] : '' );
            $img_size = ( !empty( $options['dallev1']['imgsize'] ) ? $options['dallev1']['imgsize'] : '1024x1024' );
            $array_parameters = array(
                'url'         => 'https://api.openai.com/v1/images/generations',
                'redirection' => 2,
                'method'      => 'POST',
                'timeout'     => 30,
                'headers'     => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'body'        => json_encode( array(
                    "model"   => "dall-e-3",
                    "style"   => "vivid",
                    "quality" => 'hd',
                    "prompt"  => 'Photorealistic image of ' . $search,
                    "n"       => 1,
                    "size"    => $img_size,
                ) ),
            );
        } elseif ( isset( $img_block['api_chosen'] ) && $img_block['api_chosen'] === 'replicate' ) {
            $api_key = ( !empty( $options['replicate']['apikey'] ) ? $options['replicate']['apikey'] : '' );
            $model = ( !empty( $options['replicate']['model'] ) ? $options['replicate']['model'] : 'black-forest-labs/flux-schnell' );
            $output_format = ( !empty( $options['replicate']['output_format'] ) ? $options['replicate']['output_format'] : 'webp' );
            $aspect_ratio = ( !empty( $options['replicate']['aspect_ratio'] ) ? $options['replicate']['aspect_ratio'] : '16:9' );
            
            // Get version ID from model name (simplified - use latest version)
            $version_map = array(
                'black-forest-labs/flux-schnell' => 'latest',
                'stability-ai/sdxl' => 'latest',
            );
            $version = isset( $version_map[$model] ) ? $version_map[$model] : 'latest';
            
            $array_parameters = array(
                'url'     => 'https://api.replicate.com/v1/predictions',
                'method'  => 'POST',
                'timeout' => 30,
                'headers' => array(
                    'Authorization' => 'Token ' . $api_key,
                    'Content-Type'  => 'application/json',
                ),
                'body'    => json_encode( array(
                    'version' => $version,
                    'input'   => array(
                        'prompt'        => $search,
                        'output_format' => $output_format,
                        'aspect_ratio'  => $aspect_ratio,
                    ),
                ) ),
            );
        } elseif ( $img_block['api_chosen'] == 'stability' ) {
            $api_key = ( !empty( $options['stability']['apikey'] ) ? $options['stability']['apikey'] : '' );
            $model = ( !empty( $options['stability']['model'] ) ? $options['stability']['model'] : 'sd3-large' );
            $aspect_ratio = ( !empty( $options['stability']['aspect_ratio'] ) ? $options['stability']['aspect_ratio'] : '16:9' );
            $output_format = ( !empty( $options['stability']['output_format'] ) ? $options['stability']['output_format'] : 'jpeg' );
            $use_negative_prompt = ( !empty( $options['stability']['use_negative_prompt'] ) && 'true' === $options['stability']['use_negative_prompt'] );

            $endpoint_map = array(
                'sd3-large'         => 'sd3',
                'sd3-large-turbo'   => 'sd3-turbo',
                'sd3-medium'        => 'sd3',
                'sd3.5-large-turbo' => 'sd3',
                'sd3.5-large'       => 'sd3',
                'core'              => 'core',
                'ultra'             => 'ultra',
            );
            $endpoint = isset( $endpoint_map[$model] ) ? $endpoint_map[$model] : 'sd3';

            $payload = array(
                'prompt'        => $search,
                'model'         => $model,
                'aspect_ratio'  => $aspect_ratio,
                'output_format' => $output_format,
            );

            if ( in_array( $endpoint, array('core', 'ultra'), true ) ) {
                $aspect_map = array(
                    '21:9' => array('width' => 2560, 'height' => 1097),
                    '16:9' => array('width' => 2560, 'height' => 1440),
                    '3:2'  => array('width' => 2304, 'height' => 1536),
                    '5:4'  => array('width' => 2048, 'height' => 1638),
                    '1:1'  => array('width' => 2048, 'height' => 2048),
                    '4:5'  => array('width' => 1638, 'height' => 2048),
                    '2:3'  => array('width' => 1536, 'height' => 2304),
                    '9:16' => array('width' => 1440, 'height' => 2560),
                    '9:21' => array('width' => 1097, 'height' => 2560),
                );
                $dimensions = isset( $aspect_map[$aspect_ratio] ) ? $aspect_map[$aspect_ratio] : $aspect_map['16:9'];
                $payload['width']  = $dimensions['width'];
                $payload['height'] = $dimensions['height'];
            }

            if ( $use_negative_prompt ) {
                $payload['negative_prompt'] = 'blurry, low quality, distorted, disfigured, ugly, low resolution';
            }

            $boundary = 'ASI-' . wp_generate_password( 24, false );
            $body = '';
            foreach ( $payload as $field => $value ) {
                if ( $value === '' ) {
                    continue;
                }
                $body .= '--' . $boundary . "\r\n";
                $body .= 'Content-Disposition: form-data; name="' . $field . '"' . "\r\n\r\n";
                $body .= $value . "\r\n";
            }
            $body .= '--' . $boundary . "--\r\n";

            $array_parameters = array(
                'url'     => 'https://api.stability.ai/v2beta/stable-image/generate/' . $endpoint,
                'method'  => 'POST',
                'timeout' => 60,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Accept'        => 'image/*',
                    'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
                ),
                'body'    => $body,
            );
        } elseif ( $img_block['api_chosen'] == 'flickr' ) {
            $api_key = '63d9c292b9e2dfacd3a73908779d6d6f';
            $imgtype = ( !empty( $options['flickr']['imgtype'] ) ? $options['flickr']['imgtype'] : '7' );
            if ( isset( $options['flickr']['rights'] ) && !empty( $options['flickr']['rights'] ) ) {
                $rights = '';
                $last_right = array_keys( $options['flickr']['rights'] );
                $last_right = end( $last_right );
                foreach ( $options['flickr']['rights'] as $rights_into_searching ) {
                    $rights .= $rights_into_searching;
                    if ( $rights_into_searching != $last_right ) {
                        $rights .= ',';
                    }
                }
            } else {
                $rights = '0,1,2,3,4,5,6,7,8';
            }
            $array_parameters = array(
                'url'            => 'https://api.flickr.com/services/rest/',
                'method'         => 'flickr.photos.search',
                'api_key'        => $api_key,
                'text'           => urlencode( $search ),
                'per_page'       => '16',
                'format'         => 'json',
                'nojsoncallback' => '1',
                'privacy_filter' => '1',
                'license'        => $rights,
                'sort'           => 'relevance',
                'content_type'   => $imgtype,
            );
        } elseif ( $img_block['api_chosen'] == 'pixabay' ) {
            $pixabay_username = ( !empty( $options['pixabay']['username'] ) ? $options['pixabay']['username'] : '' );
            $api_key = ( !empty( $options['pixabay']['apikey'] ) ? $options['pixabay']['apikey'] : '' );
            $imgtype = ( !empty( $options['pixabay']['imgtype'] ) ? $options['pixabay']['imgtype'] : 'all' );
            $country = ( !empty( $options['pixabay']['search_country'] ) ? $options['pixabay']['search_country'] : 'en' );
            $orientation = ( !empty( $options['pixabay']['orientation'] ) ? $options['pixabay']['orientation'] : 'all' );
            $safe = ( !empty( $options['pixabay']['safesearch'] ) ? $options['pixabay']['safesearch'] : 'false' );
            $min_width = ( !empty( $options['pixabay']['min_width'] ) ? (int) $options['pixabay']['min_width'] : '0' );
            $min_height = ( !empty( $options['pixabay']['min_height'] ) ? (int) $options['pixabay']['min_height'] : '0' );
            $array_parameters = array(
                'url'         => 'https://pixabay.com/api/',
                'username'    => $pixabay_username,
                'key'         => $api_key,
                'lang'        => $country,
                'q'           => urlencode( $search ),
                'image_type'  => $imgtype,
                'per_page'    => '200',
                'orientation' => $orientation,
                'safesearch'  => $safe,
                'min_width'   => $min_width,
                'min_height'  => $min_height,
            );
        } elseif ( $img_block['api_chosen'] == 'youtube' ) {
            $api_key = ( !empty( $options['youtube']['apikey'] ) ? $options['youtube']['apikey'] : '' );
            $search_order = ( !empty( $options['youtube']['search_order'] ) ? $options['youtube']['search_order'] : 'relevance' );
            $array_parameters = array(
                'url'          => 'https://www.googleapis.com/youtube/v3/search',
                'key'          => $api_key,
                'q'            => urlencode( $search ),
                'part'         => 'snippet',
                'type'         => 'video',
                'maxResults'   => '10',
                'order'        => $search_order,
            );
        } elseif ( $img_block['api_chosen'] == 'pexels' ) {
            $api_key = ( !empty( $options['pexels']['apikey'] ) ? $options['pexels']['apikey'] : '' );
            $orientation = ( !empty( $options['pexels']['orientation'] ) && 'all' !== $options['pexels']['orientation'] ) ? $options['pexels']['orientation'] : '';
            $size = ( !empty( $options['pexels']['size'] ) && 'all' !== $options['pexels']['size'] ) ? $options['pexels']['size'] : '';
            $color = ( !empty( $options['pexels']['color'] ) && 'all' !== $options['pexels']['color'] ) ? $options['pexels']['color'] : '';
            $locale = ( !empty( $options['pexels']['locale'] ) ? $options['pexels']['locale'] : 'en-US' );
            $fallback_search = !empty( $search ) ? $search : ( isset( $img_block['based_on']['title'] ) ? $img_block['based_on']['title'] : '' );
            $array_parameters = array(
                'url'         => 'https://api.pexels.com/v1/search',
                'method'      => 'GET',
                'headers'     => array(
                    'Authorization' => $api_key,
                ),
                'query'       => $fallback_search,
                'per_page'    => '15',
                'locale'      => $locale,
            );

            if ( !empty( $orientation ) ) {
                $array_parameters['orientation'] = $orientation;
            }

            if ( !empty( $size ) ) {
                $array_parameters['size'] = $size;
            }

            if ( !empty( $color ) ) {
                $array_parameters['color'] = $color;
            }
        } elseif ( $img_block['api_chosen'] == 'unsplash' ) {
            $api_key = ( !empty( $options['unsplash']['apikey'] ) ? $options['unsplash']['apikey'] : '' );
            $orientation = ( !empty( $options['unsplash']['orientation'] ) && 'all' !== $options['unsplash']['orientation'] ) ? $options['unsplash']['orientation'] : '';
            $content_filter = ( !empty( $options['unsplash']['content_filter'] ) ? $options['unsplash']['content_filter'] : 'low' );
            $color = ( !empty( $options['unsplash']['color'] ) && 'all' !== $options['unsplash']['color'] ) ? $options['unsplash']['color'] : '';
            $array_parameters = array(
                'url'            => 'https://api.unsplash.com/search/photos',
                'query'          => urlencode( $search ),
                'per_page'       => '15',
                'content_filter' => $content_filter,
                'client_id'      => $api_key,
            );

            if ( !empty( $orientation ) ) {
                $array_parameters['orientation'] = $orientation;
            }

            if ( !empty( $color ) ) {
                $array_parameters['color'] = $color;
            }
        } elseif ( $img_block['api_chosen'] == 'cc_search' || $img_block['api_chosen'] == 'openverse' ) {
            $imgtype = ( !empty( $options['cc_search']['imgtype'] ) ? $options['cc_search']['imgtype'] : '' );
            $aspect_ratio = ( !empty( $options['cc_search']['aspect_ratio'] ) ? $options['cc_search']['aspect_ratio'] : '' );
            $sources = array(
                'wordpress',
                'woc_tech',
                'wikimedia',
                'wellcome_collection',
                'thorvaldsensmuseum',
                'thingiverse',
                'svgsilh',
                'statensmuseum',
                'spacex',
                'smithsonian_zoo_and_conservation',
                'smithsonian_postal_museum',
                'smithsonian_portrait_gallery',
                'smithsonian_national_museum_of_natural_history',
                'smithsonian_libraries',
                'smithsonian_institution_archives',
                'smithsonian_hirshhorn_museum',
                'smithsonian_gardens',
                'smithsonian_freer_gallery_of_art',
                'smithsonian_cooper_hewitt_museum',
                'smithsonian_anacostia_museum',
                'smithsonian_american_indian_museum',
                'smithsonian_american_history_museum',
                'smithsonian_american_art_museum',
                'smithsonian_air_and_space_museum',
                'smithsonian_african_art_museum',
                'smithsonian_african_american_history_museum',
                'sketchfab',
                'sciencemuseum',
                'rijksmuseum',
                'rawpixel',
                'phylopic',
                'nypl',
                'nasa',
                'museumsvictoria',
                'met',
                'mccordmuseum',
                'iha',
                'geographorguk',
                'floraon',
                'flickr',
                'europeana',
                'eol',
                'digitaltmuseum',
                'deviantart',
                'clevelandmuseum',
                'brooklynmuseum',
                'bio_diversity',
                'behance',
                'animaldiversity',
                'WoRMS',
                'CAPL',
                '500px'
            );
            $sources_with_comma = implode( ",", $sources );
            $array_parameters = array(
                'url'          => 'https://api.openverse.engineering/v1/images/',
                'q'            => urlencode( $search ),
                'imgtype'      => urlencode( $imgtype ),
                'aspect_ratio' => urlencode( $aspect_ratio ),
                'source'       => $sources_with_comma,
            );
        } else {
            ASI_log( 'Unknown bank: ' . $img_block['api_chosen'], 'GET_PARAMETERS_ERROR' );
            return false;
        }
        
        ASI_log( array(
            'url' => isset($array_parameters['url']) ? $array_parameters['url'] : 'NO URL',
            'params_count' => count($array_parameters)
        ), 'GET_PARAMETERS_SUCCESS' );
        
        return $array_parameters;
    }

    /**
     * Generate image process
     *
     * @since    4.0.0
     */
    public function ASI_Generate(
        $service,
        $url,
        $url_parameters,
        $selected_image,
        $get_only_thumb = false,
        $search = ''
    ) {
        ASI_log( array(
            'service' => $service,
            'url' => $url,
            'search' => $search,
            'get_only_thumb' => $get_only_thumb
        ), 'ASI_GENERATE_START' );
        
        $log = $this->ASI_monolog_call();
        list( $result_body, $result ) = $this->ASI_get_results( $service, $url, $url_parameters );
        // In case of API problem
        if ( empty( $result_body ) ) {
            ASI_log( 'Empty result_body from API: ' . $service, 'ASI_GENERATE_ERROR' );
            return false;
        }
        
        ASI_log( 'API response received for: ' . $service, 'ASI_GENERATE_RESPONSE' );

        if ( 'pexels' === $service ) {
            $raw_snippet = is_string( $result ) ? mb_substr( $result, 0, 4000 ) : print_r( $result, true );
            error_log( '[All Sources Images][Pexels RAW] ' . $raw_snippet );
        }
        $log = $this->ASI_monolog_call();
        $log->info( 'Source used', array(
            'Service' => $service,
        ) );
        // ── INSERT REPLICATE POLLING HERE ────────────────────────────────────────
        if ( $service === 'replicate' ) {
            // extract prediction ID and status URL
            $predictionId = $result_body['id'];
            $getUrl = $result_body['urls']['get'];
            // retrieve API token from settings
            $options = wp_parse_args( get_option( 'ASI_plugin_banks_settings' ), $this->ASI_default_options_banks_settings( true ) );
            $apiToken = ( !empty( $options['replicate']['apikey'] ) ? $options['replicate']['apikey'] : '' );
            // poll until prediction is complete
            do {
                usleep( 500000 );
                // wait 0.5s
                $proxy_args = $this->ASI_get_proxy_args();
                $resp = wp_remote_get( $getUrl, array_merge( array(
                    'headers' => array(
                        'Authorization' => 'Token ' . $apiToken,
                        'Content-Type'  => 'application/json',
                    ),
                    'timeout' => 10,
                ), $proxy_args ) );
                // DEBUG
                /*
                			$log->info( 'Replicate polling status', [
                				'http_code' => wp_remote_retrieve_response_code( $resp ),
                				'body'      => substr( wp_remote_retrieve_body( $resp ), 0, 100 ) // Start of JSON
                			] );*/
                if ( is_wp_error( $resp ) ) {
                    return false;
                }
                $body = json_decode( wp_remote_retrieve_body( $resp ), true );
                $status = isset( $body['status'] ) ? $body['status'] : '';
            } while ( in_array( $status, array('starting', 'processing'), true ) );
            // on success, replace output so subsequent logic sees the final URL
            if ( $status === 'succeeded' && !empty( $body['output'][0] ) ) {
                $result_body['output'] = $body['output'];
            } else {
                return false;
            }
        }
        // ── END OF REPLICATE POLLING ────────────────────────────────────────────
        if ( $service == 'google_image' ) {
            $loop_results = $result_body['items'];
            // TODO : Check if urls are real images or just redirections
            $url_path = 'pagemap';
        } elseif ( $service == 'google_scraping' ) {
            $loop_results = $result_body['results'];
            $url_path = 'url';
        } elseif ( $service == 'dallev1' ) {
            $loop_results = $result_body['data'];
            $url_path = 'url';
        } elseif ( $service == 'stability' ) {
            $loop_results = $result_body;
            $url_path = 'image';
        } elseif ( $service == 'flickr' ) {
            $loop_results = $result_body['photos']['photo'];
            $url_path = 'id';
            $url_caption = 'owner';
        } elseif ( $service == 'pixabay' ) {
            $loop_results = $result_body['hits'];
            $url_path = 'largeImageURL';
        } elseif ( $service == 'youtube' ) {
            $loop_results = $result_body['items'];
            $url_path = 'id';
        } elseif ( $service == 'pexels' ) {
            $loop_results = $result_body['photos'];
            $url_path = 'src';
        } elseif ( $service == 'unsplash' ) {
            $loop_results = $result_body['results'];
            $url_path = 'urls';
        } elseif ( $service == 'cc_search' ) {
            $loop_results = $result_body['results'];
            $url_path = 'url';
        } elseif ( $service == 'replicate' ) {
            $loop_results = ( isset( $result_body['output'] ) ? (array) $result_body['output'] : array() );
            $url_path = null;
        } else {
            return false;
        }
        // Check if function is launch for Gutenberg block
        /* if( ( TRUE == $get_only_thumb ) && ( $service == 'envato' ) ) { // DISABLED - Envato Elements no longer working
        		return $result_body['results']['search_query_result']['search_payload'];
        	} else */
        if ( TRUE == $get_only_thumb ) {
            return $result_body;
        }
        /* Random Image */
        if ( $selected_image == 'random_result' && $service != 'dallev1' && $service != 'stability' ) {
            @shuffle( $loop_results );
        }
        // Testing images
        if ( $service == 'google_scraping' ) {
            foreach ( $loop_results as $loop_result_result => $loop_result ) {
                $remote_img = wp_remote_head( $loop_result['url'] );
                $remote_response = wp_remote_retrieve_response_code( $remote_img );
                $log = $this->ASI_monolog_call();
                $log->info( 'Remote image', array(
                    'remote_img' => $loop_result,
                ) );
                if ( 200 !== $remote_response ) {
                    // Remove the result, image not valid
                    unset($loop_results[$loop_result_result]);
                } else {
                    // Image ok. Avoid next results.
                    break;
                }
                if ( $loop_result['url'] ) {
                    $infos_img = @getimagesize( $loop_result['url'] );
                } else {
                    $infos_img = false;
                }
                if ( false === $infos_img ) {
                    // Remove the result, image not valid
                    unset($loop_results[$loop_result_result]);
                } else {
                    // Image ok. Avoid next results.
                    break;
                }
            }
        }
        if ( !empty( $loop_results ) ) {
            $loop_count = 0;
            $numUrl = count( $loop_results );
            foreach ( $loop_results as $fetch_result_key => $fetch_result ) {
                if ( 'image' != $fetch_result_key && $service == 'stability' ) {
                    continue;
                }
                if ( $service == 'replicate' ) {
                    $url_result = $fetch_result;
                } elseif ( $service !== 'stability' ) {
                    $url_result = $fetch_result[$url_path];
                }
                // Change default url image
                if ( $service == 'google_image' ) {
                    $url_result = $url_result['cse_image'][0]['src'];
                } elseif ( $service == 'unsplash' ) {
                    $url_result = $url_result['full'];
                } elseif ( $service == 'pexels' ) {
                    $url_result = $url_result['original'];
                } elseif ( $service == 'dallev1' ) {
                    $url_result = $fetch_result[$url_path];
                    // Show revised prompt (by openAI) in logs
                    $log = $this->ASI_monolog_call();
                    $log->info( 'DALL_E', array(
                        'revised_prompt' => $fetch_result['revised_prompt'],
                    ) );
                } elseif ( $service == 'stability' ) {
                    $url_result = $fetch_result;
                } elseif ( $service == 'replicate' ) {
                    // For replicate, $fetch_result is already the final URL
                    $url_result = $fetch_result;
                } else {
                    $url_result = $fetch_result[$url_path];
                }
                $options = wp_parse_args( get_option( 'ASI_plugin_main_settings' ), $this->ASI_default_options_main_settings( TRUE ) );
                
                // ALT text generation
                $alt = '';
                if ( isset( $options['enable_alt'] ) && 'enable' == $options['enable_alt'] ) {
                    // Get ALT text source
                    if ( isset( $options['alt_from'] ) && $options['alt_from'] == 'source' ) {
                        // Use image bank name as ALT
                        $alt = ucfirst( $service );
                    } elseif ( isset( $options['alt_from'] ) && $options['alt_from'] == 'based_on' ) {
                        // Use search term as ALT
                        $alt = $search;
                    }
                    
                    // Translate ALT text if enabled
                    if ( !empty( $alt ) && isset( $options['translate_alt'] ) && $options['translate_alt'] == 'true' ) {
                        $target_lang = ( !empty( $options['translate_alt_lang'] ) ? $options['translate_alt_lang'] : 'en' );
                        if ( $target_lang !== 'en' ) {
                            $translated_alt = $this->ASI_translate_text( $alt, 'en', $target_lang );
                            if ( $translated_alt !== false ) {
                                $alt = $translated_alt;
                            }
                        }
                    }
                }
                
                // Caption texts
                if ( isset( $options['enable_caption'] ) && 'enable' == $options['enable_caption'] ) {
                    if ( $service == 'pixabay' ) {
                        $caption = $fetch_result['user'];
                    } elseif ( $service == 'unsplash' ) {
                        $caption = $fetch_result['user']['name'];
                    } elseif ( $service == 'pexels' ) {
                        $caption = $fetch_result['photographer'];
                    } elseif ( $service == 'cc_search' ) {
                        $caption = $fetch_result['creator'];
                    } elseif ( $service == 'google_scraping' ) {
                        $caption = $fetch_result['caption'];
                    } elseif ( $service == 'flickr' ) {
                        // FLICKR : Additional remote request to get image url
                        $url_result_owner = $fetch_result[$url_caption];
                        $api_key = '63d9c292b9e2dfacd3a73908779d6d6f';
                        $url = 'https://api.flickr.com/services/rest/?method=flickr.people.getInfo&api_key=' . $api_key . '&user_id=' . $url_result_owner . '&format=json&nojsoncallback=1';
                        $proxy_args = $this->ASI_get_proxy_args();
                        $result_img_flickr = wp_remote_request( $url, $proxy_args );
                        $result_img_body_flickr = json_decode( $result_img_flickr['body'], true );
                        if ( !empty( $result_img_body_flickr['person']['realname']['_content'] ) ) {
                            $caption = ucwords( $result_img_body_flickr['person']['realname']['_content'] );
                        } else {
                            $caption = ucwords( $result_img_body_flickr['person']['username']['_content'] );
                        }
                    } else {
                        $caption = '';
                    }
                    if ( 'author_bank' == $options['caption_from'] && $service != 'google_scraping' ) {
                        $caption .= esc_html__( ' from ', 'all-sources-images' ) . ucfirst( $service );
                    }
                } else {
                    $caption = '';
                }
                // FLICKR : Additional remote request to get image url
                if ( $service == 'flickr' ) {
                    $api_key = '63d9c292b9e2dfacd3a73908779d6d6f';
                    $url = 'https://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=' . $api_key . '&photo_id=' . $url_result . '&format=json&nojsoncallback=1';
                    $proxy_args = $this->ASI_get_proxy_args();
                    $result_img_flickr = wp_remote_request( $url, $proxy_args );
                    $result_img_body_flickr = json_decode( $result_img_flickr['body'], true );
                    $result = end( $result_img_body_flickr['sizes']['size'] );
                    $url_result = $result['source'];
                    //$url_result_sizes       = $result_img_body_flickr['sizes'];
                }
                // ENVATO : Additional remote request to get image url - DISABLED (no longer working)
                /*
                if( $service == 'envato' ) {
                
                		$url 				= 'https://api.extensions.envato.com/extensions/item/' . $url_result . '/download';
                		$project_ags 		= array( 'project_name' => get_bloginfo('name') );
                		$result_img_envato 	= wp_remote_post(
                			add_query_arg($project_ags, $url),
                			array(
                				'headers' => array(
                					"Extensions-Extension-Id" 	=> md5( get_site_url() ),
                					"Extensions-Token" 			=> $url_parameters['envato_token'],
                					"Content-Type"				=> "application/json"
                				),
                			)
                		);
                		$result 			= json_decode( $result_img_envato['body'] );
                		$url_result			 = $result->download_urls->max2000;
                
                }
                */
                // YOUTUBE : Additional remote request to get thumbnail
                if ( $service == 'youtube' ) {
                    $api_key = $url_parameters['key'];
                    $url = 'https://www.googleapis.com/youtube/v3/videos?key=' . $api_key . '&part=snippet&id=' . $fetch_result['id']['videoId'];
                    $proxy_args = $this->ASI_get_proxy_args();
                    $result_img_yt = wp_remote_request( $url, $proxy_args );
                    $result_img_body_yt = json_decode( $result_img_yt['body'], true );
                    $hdimg = end( $result_img_body_yt['items'][0]['snippet']['thumbnails'] );
                    $url_result = $hdimg['url'];
                }
                if ( empty( $url_result ) ) {
                    continue;
                }
                // Avoid unknown image type
                if ( $service != 'dallev1' && $service != 'stability' && $service != 'unsplash' && $service != 'pexels' ) {
                    $url_result = $url_result;
                    $wp_filetype = wp_check_filetype( $url_result );
                    if ( false == $wp_filetype['type'] ) {
                        continue;
                    }
                }
                if ( TRUE == $get_only_thumb ) {
                    $url_result_ar['photos'][]['url'] = $url_result;
                } else {
                    if ( 'stability' == $service ) {
                        if ( base64_decode( $url_result, true ) !== false ) {
                            // Decoding the image in Base64
                            $image_data = base64_decode( $url_result );
                            $options_banks = wp_parse_args( get_option( 'ASI_plugin_banks_settings' ), $this->ASI_default_options_banks_settings( TRUE ) );
                            // Specify a file path for the image in the download directory
                            $upload_dir = wp_upload_dir();
                            $file_path = $upload_dir['path'] . '/temp_mpt_stability_' . uniqid() . '.' . $options_banks['stability']['output_format'];
                            // Save the decoded image in the file
                            file_put_contents( $file_path, $image_data );
                            if ( file_exists( $file_path ) ) {
                                $file_content = file_get_contents( $file_path );
                            }
                            $file_media['response']['code'] = '200';
                            $file_media['body'] = $file_content;
                            $file_media['headers']['content-type'] = 'image/' . $options_banks['stability']['output_format'];
                            // Remove temporary file
                            unlink( $file_path );
                        }
                    } else {
                        $proxy_args = $this->ASI_get_proxy_args();
                        $file_media = @wp_remote_request( $url_result, $proxy_args );
                        if ( isset( $file_media->errors ) || $file_media['response']['code'] != 200 || strpos( $file_media['headers']['content-type'], 'text/html' ) !== false ) {
                            if ( ++$loop_count === $numUrl ) {
                                return false;
                            } else {
                                continue;
                            }
                        } else {
                            break;
                        }
                    }
                }
            }
            if ( TRUE == $get_only_thumb ) {
                return $url_result_ar;
            }
        } else {
            return false;
        }
        return array(
            $url_result,
            $file_media,
            $alt,
            $caption
        );
    }

    /**
     * Image results to get thumbnails
     *
     * @since    4.0.0
     */
    private function ASI_get_results( $service, $url, $url_parameters ) {
        if ( $service == 'dallev1' || $service == 'stability' || $service == 'pexels' || $service == 'replicate' ) {
            $defaults = $url_parameters;
        } else {
            /* Retrieve 3 images as result */
            $url = add_query_arg( $url_parameters, $url );
            // Simulate Default Browser
            $defaults = array(
                'redirection'        => 9,
                'user-agent'         => 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.96',
                'reject_unsafe_urls' => false,
                'sslverify'          => false,
            );
        }
        // Proxy settings
        $proxy_args = $this->ASI_get_proxy_args();
        $defaults = array_merge( $defaults, $proxy_args );
        
        ASI_log( array(
            'service' => $service,
            'url' => $url,
            'has_proxy' => !empty($proxy_args)
        ), 'HTTP_REQUEST' );
        
        $result = wp_remote_request( $url, $defaults );
        
        ASI_log( array(
            'response_code' => is_wp_error($result) ? 'ERROR' : wp_remote_retrieve_response_code($result),
            'is_error' => is_wp_error($result),
            'error_message' => is_wp_error($result) ? $result->get_error_message() : ''
        ), 'HTTP_RESPONSE' );
        // If error happen
        if ( !empty( $result->errors['http_request_failed'] ) ) {
            return false;
        }
        // Google Scraping : Different method
        if ( $service == 'google_scraping' ) {
            // Get all alts from Google
            preg_match_all( '/data-pt="([^"]*)"/', $result['body'], $output_img_alts );
            // Get all captions from Google
            preg_match_all( '/data-st="([^"]*)"/', $result['body'], $output_img_captions );
            // Get all images from Google
            //preg_match_all( '/,\["http[^"]((?!gstatic).)*",\d+?,\d+?\]/', $result['body'], $output_img_urls );
            preg_match_all( '/data-ou="(http[^"]*)"/', $result['body'], $output_img_urls );
            $result_body['results'] = array_map(
                array(&$this, 'ASI_order_array_urls'),
                $output_img_urls[1],
                $output_img_alts[1],
                $output_img_captions[1]
            );
        } elseif ( $service == 'stability' ) {
            $code = intval( $result['response']['code'] );
            if ( 200 !== $code ) {
                return false;
            }
            $result_body = array(
                'image' => base64_encode( $result['body'] ),
            );
        } else {
            $result_body = json_decode( $result['body'], true );
            $log = $this->ASI_monolog_call();
            // Dall-e: Catch the error
            if ( $service == 'dallev1' && isset( $result_body['error']['message'] ) ) {
                //error_log( print_r( $result, true ) );
                $log->info( 'Problem with Dalle', array(
                    'Error message' => $result_body['error']['message'],
                ) );
            }
            // accept 200 for most services, 201 for replicate
            $code = intval( $result['response']['code'] );
            if ( $service === 'replicate' && $code !== 201 || $service !== 'replicate' && $code !== 200 ) {
                return false;
            }
        }
        return array($result_body, $result);
    }

    /**
     * Remove gstatic images
     *
     * @since    4.0.0
     */
    public function ASI_order_array_urls( $str, $str_alt, $str_caption ) {
        // Get only the url and exclude Google image url (domain gstatic)
        $pattern = '/,\\["(http[^"]((?!gstatic).)*)",\\d+?,\\d+?\\]/';
        $replacement = '$1';
        // Check if $str is not null before using preg_replace
        $real_url = ( $str !== null ? preg_replace( $pattern, $replacement, $str ) : '' );
        return array(
            'url'     => $real_url,
            'alt'     => $str_alt,
            'caption' => $str_caption,
        );
    }

    /**
     * Function to get the depth of a category
     *
     * @since    5.2.11
     */
    private function get_category_depth( $cat_id, $current_depth = 0 ) {
        $category = get_category( $cat_id );
        if ( $category->parent == 0 ) {
            return $current_depth;
        } else {
            return $this->get_category_depth( $category->parent, $current_depth + 1 );
        }
    }

    /**
     * Function to get the desired category based on the user's choice
     *
     * @since    5.2.11
     */
    private function get_desired_category( $post_id, $category_choice ) {
        // Retrieve the categories of the post
        $postcategories = get_the_category( $post_id );
        $categories_with_depth = array();
        // Check if the post has any categories
        if ( empty( $postcategories ) ) {
            // Handle the case where no categories are associated
            return null;
        }
        // Get the depth of each category associated with the post
        foreach ( $postcategories as $category ) {
            $depth = $this->get_category_depth( $category->term_id );
            // Store the category with its depth as the key
            $categories_with_depth[$depth] = $category;
        }
        // Find the category with the greatest depth (most specific)
        $max_depth = max( array_keys( $categories_with_depth ) );
        $child_category = $categories_with_depth[$max_depth];
        // Determine the category to use based on the user's choice
        $desired_category = $child_category;
        if ( $category_choice && $child_category ) {
            if ( $category_choice == 'second_level' ) {
                if ( $child_category->parent ) {
                    $desired_category = get_category( $child_category->parent );
                }
            } elseif ( $category_choice == 'third_level' ) {
                if ( $child_category->parent ) {
                    $parent_category = get_category( $child_category->parent );
                    if ( $parent_category->parent ) {
                        $desired_category = get_category( $parent_category->parent );
                    } else {
                        $desired_category = $parent_category;
                    }
                }
            }
        }
        return $desired_category;
    }

    /**
     * Function to get the desired term based on the user's choice and a custom taxonomy
     *
     * @since    6.0.0
     */
    private function get_desired_taxonomy( $post_id, $taxonomy, $term_choice ) {
        // Retrieve the terms of the post in the specified taxonomy
        $post_terms = get_the_terms( $post_id, $taxonomy );
        $terms_with_depth = array();
        // Check if the post has any terms in the taxonomy
        if ( empty( $post_terms ) || is_wp_error( $post_terms ) ) {
            // Handle the case where no terms are associated or there's an error
            return null;
        }
        // Get the depth of each term associated with the post
        foreach ( $post_terms as $term ) {
            $depth = $this->get_term_depth( $term->term_id, $taxonomy );
            // Store the term with its depth as the key
            $terms_with_depth[$depth] = $term;
        }
        // Find the term with the greatest depth (most specific)
        $max_depth = max( array_keys( $terms_with_depth ) );
        $child_term = $terms_with_depth[$max_depth];
        // Determine the term to use based on the user's choice
        $desired_term = $child_term;
        if ( $term_choice && $child_term ) {
            if ( $term_choice == 'second_level' ) {
                if ( $child_term->parent ) {
                    $desired_term = get_term( $child_term->parent, $taxonomy );
                }
            } elseif ( $term_choice == 'third_level' ) {
                if ( $child_term->parent ) {
                    $parent_term = get_term( $child_term->parent, $taxonomy );
                    if ( $parent_term->parent ) {
                        $desired_term = get_term( $parent_term->parent, $taxonomy );
                    } else {
                        $desired_term = $parent_term;
                    }
                }
            }
        }
        return $desired_term;
    }

    /**
     * Helper function to get the depth of a term in a custom taxonomy.
     *
     * @since    6.0.0
     */
    private function get_term_depth( $term_id, $taxonomy ) {
        $depth = 0;
        $term = get_term( $term_id, $taxonomy );
        while ( $term && $term->parent ) {
            $term = get_term( $term->parent, $taxonomy );
            $depth++;
        }
        return $depth;
    }

    /**
     * Helper function to get the depth of a term in a custom taxonomy.
     *
     * @since    6.0.0
     */
    private function get_extracted_term( $text, $selected_lang = 'en' ) {
        require_once dirname( __FILE__ ) . '/../includes/php-ml/index.php';
        $extractor = new KeywordExtractor($selected_lang);
        $keywords = $extractor->extractKeywords( $text );
        return $keywords[0];
    }

    /**
     * Calculate dimensions from aspect ratio for models that use width/height
     *
     * @since    6.0.0
     * @param string $aspect_ratio The aspect ratio (e.g., '16:9', '4:3')
     * @param int $max_resolution Maximum resolution for the model
     * @return array Array with 'width' and 'height' keys
     */
    private function calculate_dimensions_from_aspect_ratio( $aspect_ratio, $max_resolution = 2048 ) {
        // Seedream-4 specific dimensions as per official documentation
        $seedream_dimensions = array(
            '1:1'  => array(
                'width'  => 2048,
                'height' => 2048,
            ),
            '4:3'  => array(
                'width'  => 2304,
                'height' => 1728,
            ),
            '16:9' => array(
                'width'  => 2560,
                'height' => 1440,
            ),
            '3:2'  => array(
                'width'  => 2304,
                'height' => 1536,
            ),
            '9:16' => array(
                'width'  => 1440,
                'height' => 2560,
            ),
        );
        // Check if we have specific dimensions for this aspect ratio
        if ( isset( $seedream_dimensions[$aspect_ratio] ) ) {
            return $seedream_dimensions[$aspect_ratio];
        }
        // Fallback to calculated dimensions for other ratios
        $ratio_parts = explode( ':', $aspect_ratio );
        if ( count( $ratio_parts ) !== 2 ) {
            // Default to 16:9 if invalid format
            $ratio_parts = array(16, 9);
        }
        $ratio_width = (int) $ratio_parts[0];
        $ratio_height = (int) $ratio_parts[1];
        // Calculate base dimensions
        $base_width = $max_resolution;
        $base_height = round( $max_resolution * $ratio_height / $ratio_width );
        // Ensure we don't exceed max resolution
        if ( $base_height > $max_resolution ) {
            $base_height = $max_resolution;
            $base_width = round( $max_resolution * $ratio_width / $ratio_height );
        }
        // Round to even numbers for better compatibility
        $width = $base_width - $base_width % 2;
        $height = $base_height - $base_height % 2;
        return array(
            'width'  => $width,
            'height' => $height,
        );
    }

    /**
     * Translate text using Google Translate
     *
     * @since    6.2.0
     * @param    string    $text          Text to translate
     * @param    string    $source_lang   Source language code (default: auto)
     * @param    string    $target_lang   Target language code (default: en)
     * @return   string|false             Translated text or false on error
     */
    private function ASI_translate_text( $text, $source_lang = 'auto', $target_lang = 'en' ) {
        if ( empty( $text ) || $source_lang === $target_lang ) {
            return $text;
        }
        
        $log = $this->ASI_monolog_call();
        $options_banks = wp_parse_args( get_option( 'ASI_plugin_banks_settings' ), $this->ASI_default_options_banks_settings( TRUE ) );
        $api_key = ( !empty( $options_banks['google_translate']['apikey'] ) ? $options_banks['google_translate']['apikey'] : '' );
        
        // Use official Google Translate API if API key is provided
        if ( !empty( $api_key ) ) {
            $url = 'https://translation.googleapis.com/language/translate/v2';
            $params = array(
                'key'    => $api_key,
                'q'      => $text,
                'source' => $source_lang,
                'target' => $target_lang,
                'format' => 'text',
            );
            
            $proxy_args = $this->ASI_get_proxy_args();
            $response = wp_remote_post( $url, array_merge( array(
                'body'    => $params,
                'timeout' => 10,
            ), $proxy_args ) );
            
            if ( is_wp_error( $response ) ) {
                $log->error( 'Google Translate API error', array(
                    'error' => $response->get_error_message(),
                ) );
                return false;
            }
            
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( isset( $body['data']['translations'][0]['translatedText'] ) ) {
                $translated = $body['data']['translations'][0]['translatedText'];
                $log->info( 'Text translated (API)', array(
                    'from' => $text,
                    'to'   => $translated,
                ) );
                return $translated;
            }
        }
        
        // Fallback to free Google Translate scraping method
        $url = 'https://translate.googleapis.com/translate_a/single';
        $params = array(
            'client' => 'gtx',
            'sl'     => $source_lang,
            'tl'     => $target_lang,
            'dt'     => 't',
            'q'      => $text,
        );
        
        $url = add_query_arg( $params, $url );
        $proxy_args = $this->ASI_get_proxy_args();
        $response = wp_remote_get( $url, array_merge( array(
            'timeout' => 10,
        ), $proxy_args ) );
        
        if ( is_wp_error( $response ) ) {
            $log->error( 'Google Translate scraping error', array(
                'error' => $response->get_error_message(),
            ) );
            return false;
        }
        
        $body = wp_remote_retrieve_body( $response );
        $result = json_decode( $body, true );
        
        if ( isset( $result[0][0][0] ) ) {
            $translated = $result[0][0][0];
            $log->info( 'Text translated (free)', array(
                'from' => $text,
                'to'   => $translated,
            ) );
            return $translated;
        }
        
        return false;
    }

}
