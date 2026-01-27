<?php
/**
 * All Sources Images - WordPress Abilities API Integration
 *
 * Exposes plugin functionality as WordPress Abilities for AI agents
 * to interact with via MCP (Model Context Protocol).
 *
 * @package All_Sources_Images
 * @since   1.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ALLSI_Abilities
 *
 * Registers WordPress Abilities for the All Sources Images plugin.
 * These abilities can be discovered and executed by AI agents via MCP Adapter.
 *
 * @since 1.0.6
 */
class ALLSI_Abilities {

    /**
     * Singleton instance.
     *
     * @var ALLSI_Abilities
     */
    private static $instance = null;

    /**
     * Reference to the generation class.
     *
     * @var All_Sources_Images_Generation
     */
    private $generation = null;

    /**
     * Get singleton instance.
     *
     * @return ALLSI_Abilities
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        // WordPress 6.9+ requires categories to be registered first
        add_action( 'wp_abilities_api_categories_init', array( $this, 'register_categories' ) );
        // Then abilities on wp_abilities_api_init hook
        add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
        
        // Debug logging
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            ALLSI_log( 'ALLSI_Abilities: Constructor called, hooks added' );
        }
    }
    
    /**
     * Register ability categories.
     *
     * @return void
     */
    public function register_categories() {
        wp_register_ability_category(
            'media',
            array(
                'label'       => __( 'Media', 'all-sources-images' ),
                'description' => __( 'Abilities for managing media and images.', 'all-sources-images' ),
            )
        );
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            ALLSI_log( 'ALLSI_Abilities: Registered media category' );
        }
    }

    /**
     * Get the generation class instance.
     *
     * @return All_Sources_Images_Generation|null
     */
    private function get_generation_instance() {
        if ( null === $this->generation ) {
            // Ensure the admin class is loaded first (Generation extends Admin)
            if ( ! class_exists( 'All_Sources_Images_Admin' ) ) {
                $admin_file = plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-all-sources-images-admin.php';
                if ( file_exists( $admin_file ) ) {
                    require_once $admin_file;
                }
            }
            
            // Then load the generation class
            if ( ! class_exists( 'All_Sources_Images_Generation' ) ) {
                $gen_file = plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-all-sources-images-generation.php';
                if ( file_exists( $gen_file ) ) {
                    require_once $gen_file;
                }
            }
            
            if ( class_exists( 'All_Sources_Images_Generation' ) ) {
                $this->generation = new All_Sources_Images_Generation( 'all-sources-images', ALL_SOURCES_IMAGES_VERSION );
            }
        }
        return $this->generation;
    }

    /**
     * Register all plugin abilities.
     *
     * @return void
     */
    public function register_abilities() {
        // Debug logging
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            ALLSI_log( 'ALLSI_Abilities: register_abilities() called' );
        }
        
        $this->register_search_image_ability();
        $this->register_set_featured_image_ability();
        $this->register_auto_generate_for_post_ability();
        $this->register_insert_image_in_content_ability();
        $this->register_generate_ai_image_ability();
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            ALLSI_log( 'ALLSI_Abilities: All 5 abilities registered successfully' );
        }
    }

    /**
     * Register the search-image ability.
     *
     * Allows AI agents to search for images across configured sources.
     *
     * @return void
     */
    private function register_search_image_ability() {
        $result = wp_register_ability(
            'allsi/search-image',
            array(
                'label'       => __( 'Search Images', 'all-sources-images' ),
                'description' => __( 'Search for images using a text query. Supports stock photo banks (Pexels, Pixabay, Unsplash, Flickr, Openverse, Giphy) and AI image generators (DALL-E, Stable Diffusion, Gemini, Replicate). Use this when you need to find images for blog posts, articles, or any content. Returns multiple image options with URLs, thumbnails, alt text, and attribution.', 'all-sources-images' ),
                'category'    => 'media',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'search_term' => array(
                            'type'        => 'string',
                            'description' => __( 'The search query to find images. For stock photos, use descriptive keywords like "sunset beach" or "business meeting". For AI generators, use detailed prompts like "a photorealistic image of a golden retriever playing in autumn leaves".', 'all-sources-images' ),
                        ),
                        'source' => array(
                            'type'        => 'string',
                            'enum'        => array( 'pixabay', 'pexels', 'unsplash', 'flickr', 'openverse', 'giphy', 'dallev1', 'stability', 'gemini', 'replicate', 'workers_ai' ),
                            'default'     => 'pixabay',
                            'description' => __( 'The image source to search. Stock photos: pixabay (free, large library), pexels (high quality, free), unsplash (artistic photos), flickr (diverse content), openverse (creative commons), giphy (animated GIFs). AI generators: dallev1 (OpenAI DALL-E), stability (Stable Diffusion), gemini (Google Gemini), replicate (various AI models), workers_ai (Cloudflare AI).', 'all-sources-images' ),
                        ),
                        'count' => array(
                            'type'        => 'integer',
                            'minimum'     => 1,
                            'maximum'     => 20,
                            'default'     => 5,
                            'description' => __( 'Number of images to return. Default is 5. Maximum is 20. Use fewer images for faster responses or when you only need one option.', 'all-sources-images' ),
                        ),
                        'selection' => array(
                            'type'        => 'string',
                            'enum'        => array( 'first_result', 'random_result' ),
                            'default'     => 'random_result',
                            'description' => __( 'How to select images from results. "first_result" returns the most relevant matches. "random_result" adds variety by randomizing the order. Use "first_result" when you want the best match, "random_result" when you want diversity.', 'all-sources-images' ),
                        ),
                    ),
                    'required' => array( 'search_term' ),
                ),
                'output_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'success' => array(
                            'type'        => 'boolean',
                            'description' => __( 'True if images were found, false if search failed or no results.', 'all-sources-images' ),
                        ),
                        'search_term' => array(
                            'type'        => 'string',
                            'description' => __( 'The search term that was used.', 'all-sources-images' ),
                        ),
                        'source' => array(
                            'type'        => 'string',
                            'description' => __( 'The image source that was searched.', 'all-sources-images' ),
                        ),
                        'count' => array(
                            'type'        => 'integer',
                            'description' => __( 'Number of images returned.', 'all-sources-images' ),
                        ),
                        'images' => array(
                            'type'        => 'array',
                            'description' => __( 'Array of image objects. Each image has: url (full size image URL to download), thumbnail (smaller preview URL), alt (descriptive alt text), caption (attribution text like "Photo by X on Pexels"), width (image width in pixels), height (image height in pixels).', 'all-sources-images' ),
                            'items'       => array(
                                'type'       => 'object',
                                'properties' => array(
                                    'url'       => array( 'type' => 'string', 'description' => 'Full-size image URL for downloading' ),
                                    'thumbnail' => array( 'type' => 'string', 'description' => 'Smaller preview image URL' ),
                                    'alt'       => array( 'type' => 'string', 'description' => 'Descriptive alt text for accessibility' ),
                                    'caption'   => array( 'type' => 'string', 'description' => 'Attribution text (e.g., "Photo by John on Pexels")' ),
                                    'width'     => array( 'type' => 'integer', 'description' => 'Image width in pixels' ),
                                    'height'    => array( 'type' => 'integer', 'description' => 'Image height in pixels' ),
                                ),
                            ),
                        ),
                        'error' => array(
                            'type'        => 'string',
                            'description' => __( 'Error message explaining why the search failed. Only present when success is false.', 'all-sources-images' ),
                        ),
                    ),
                ),
                'execute_callback'    => array( $this, 'execute_search_image' ),
                'permission_callback' => array( $this, 'can_edit_posts' ),
                'meta'                => array(
                    'show_in_rest' => true,
                    'mcp'          => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                ),
            )
        );
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            if ( is_wp_error( $result ) ) {
                ALLSI_log( 'ALLSI_Abilities: Failed to register allsi/search-image: ' . $result->get_error_message() );
            } else {
                ALLSI_log( 'ALLSI_Abilities: Registered allsi/search-image successfully' );
            }
        }
    }

    /**
     * Register the set-featured-image ability.
     *
     * Allows AI agents to set a featured image on a post.
     *
     * @return void
     */
    private function register_set_featured_image_ability() {
        $result = wp_register_ability(
            'allsi/set-featured-image',
            array(
                'label'       => __( 'Set Featured Image', 'all-sources-images' ),
                'description' => __( 'Downloads an image from any URL and sets it as the featured image (thumbnail) of a WordPress post. The image is saved to the WordPress media library and properly attached to the post. Use this after searching for images with allsi/search-image, or with any direct image URL.', 'all-sources-images' ),
                'category'    => 'media',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => __( 'The WordPress post ID to set the featured image for. You can get this from the post URL or by listing posts.', 'all-sources-images' ),
                        ),
                        'image_url' => array(
                            'type'        => 'string',
                            'description' => __( 'The full URL of the image to download. Use the "url" field from allsi/search-image results, or any direct link to a JPG, PNG, GIF, or WebP image.', 'all-sources-images' ),
                        ),
                        'alt_text' => array(
                            'type'        => 'string',
                            'description' => __( 'Alternative text describing the image for accessibility and SEO. If not provided, uses the post title. Use the "alt" field from search results when available.', 'all-sources-images' ),
                        ),
                        'caption' => array(
                            'type'        => 'string',
                            'description' => __( 'Image caption for attribution. Use the "caption" field from search results (e.g., "Photo by John on Pexels") to properly credit the source.', 'all-sources-images' ),
                        ),
                    ),
                    'required' => array( 'post_id', 'image_url' ),
                ),
                'output_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'success' => array(
                            'type'        => 'boolean',
                            'description' => __( 'True if the image was downloaded and set as featured image successfully.', 'all-sources-images' ),
                        ),
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => __( 'The post ID that received the featured image.', 'all-sources-images' ),
                        ),
                        'attachment_id' => array(
                            'type'        => 'integer',
                            'description' => __( 'The WordPress media library attachment ID of the uploaded image. Can be used to reference the image later.', 'all-sources-images' ),
                        ),
                        'thumbnail_url' => array(
                            'type'        => 'string',
                            'description' => __( 'The local WordPress URL where the image is now hosted.', 'all-sources-images' ),
                        ),
                        'error' => array(
                            'type'        => 'string',
                            'description' => __( 'Error message explaining what went wrong. Only present when success is false.', 'all-sources-images' ),
                        ),
                    ),
                ),
                'execute_callback'    => array( $this, 'execute_set_featured_image' ),
                'permission_callback' => array( $this, 'can_edit_posts' ),
                'meta'                => array(
                    'show_in_rest' => true,
                    'mcp'          => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                ),
            )
        );
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            if ( is_wp_error( $result ) ) {
                ALLSI_log( 'ALLSI_Abilities: Failed to register allsi/set-featured-image: ' . $result->get_error_message() );
            } else {
                ALLSI_log( 'ALLSI_Abilities: Registered allsi/set-featured-image successfully' );
            }
        }
    }

    /**
     * Register the auto-generate-for-post ability.
     *
     * Allows AI agents to auto-generate an image for a post based on its content.
     *
     * @return void
     */
    private function register_auto_generate_for_post_ability() {
        $result = wp_register_ability(
            'allsi/auto-generate-for-post',
            array(
                'label'       => __( 'Auto Generate Image for Post', 'all-sources-images' ),
                'description' => __( 'Automatically finds and sets a featured image for a WordPress post. Analyzes the post title to extract relevant keywords, searches for matching images, and sets the best result as the featured image. This is a one-step solution that combines search and set operations. Ideal for bulk operations or when you want hands-off image assignment.', 'all-sources-images' ),
                'category'    => 'media',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => __( 'The WordPress post ID to generate an image for. The post title will be analyzed to extract search keywords.', 'all-sources-images' ),
                        ),
                        'source' => array(
                            'type'        => 'string',
                            'enum'        => array( 'pixabay', 'pexels', 'unsplash', 'flickr', 'openverse', 'giphy', 'dallev1', 'stability', 'gemini', 'replicate', 'workers_ai' ),
                            'default'     => 'pixabay',
                            'description' => __( 'The image source to use. Defaults to pixabay (free, large library). Use pexels for high quality, unsplash for artistic photos. For AI-generated images use dallev1, stability, or gemini.', 'all-sources-images' ),
                        ),
                        'overwrite' => array(
                            'type'        => 'boolean',
                            'default'     => false,
                            'description' => __( 'Whether to replace an existing featured image. If false (default), posts that already have a featured image will be skipped. Set to true to force replacement.', 'all-sources-images' ),
                        ),
                    ),
                    'required' => array( 'post_id' ),
                ),
                'output_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'success' => array(
                            'type'        => 'boolean',
                            'description' => __( 'True if an image was found and set as featured image successfully.', 'all-sources-images' ),
                        ),
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => __( 'The post ID that was processed.', 'all-sources-images' ),
                        ),
                        'post_title' => array(
                            'type'        => 'string',
                            'description' => __( 'The title of the post that was processed.', 'all-sources-images' ),
                        ),
                        'search_term' => array(
                            'type'        => 'string',
                            'description' => __( 'The keywords extracted from the post title and used for image search.', 'all-sources-images' ),
                        ),
                        'source_used' => array(
                            'type'        => 'string',
                            'description' => __( 'The image source that provided the image.', 'all-sources-images' ),
                        ),
                        'attachment_id' => array(
                            'type'        => 'integer',
                            'description' => __( 'The WordPress media library attachment ID of the new featured image.', 'all-sources-images' ),
                        ),
                        'image_url' => array(
                            'type'        => 'string',
                            'description' => __( 'The local WordPress URL where the image is now hosted.', 'all-sources-images' ),
                        ),
                        'error' => array(
                            'type'        => 'string',
                            'description' => __( 'Error message if the operation failed. Common reasons: post not found, post already has image (set overwrite=true), no images found for search term.', 'all-sources-images' ),
                        ),
                    ),
                ),
                'execute_callback'    => array( $this, 'execute_auto_generate_for_post' ),
                'permission_callback' => array( $this, 'can_edit_posts' ),
                'meta'                => array(
                    'show_in_rest' => true,
                    'mcp'          => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                ),
            )
        );
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            if ( is_wp_error( $result ) ) {
                ALLSI_log( 'ALLSI_Abilities: Failed to register allsi/auto-generate-for-post: ' . $result->get_error_message() );
            } else {
                ALLSI_log( 'ALLSI_Abilities: Registered allsi/auto-generate-for-post successfully' );
            }
        }
    }

    /**
     * Register the insert-image-in-content ability.
     *
     * Allows AI agents to insert images within post content at specific positions.
     *
     * @return void
     */
    private function register_insert_image_in_content_ability() {
        $result = wp_register_ability(
            'allsi/insert-image-in-content',
            array(
                'label'       => __( 'Insert Image in Post Content', 'all-sources-images' ),
                'description' => __( 'Inserts an image within the content of a WordPress post at a specified position. The image can be placed before or after a specific paragraph, heading, or other HTML element. Supports both Classic Editor and Gutenberg block formats. Use this to add inline images that break up long text or illustrate specific sections.', 'all-sources-images' ),
                'category'    => 'media',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => __( 'The WordPress post ID where the image will be inserted.', 'all-sources-images' ),
                        ),
                        'image_url' => array(
                            'type'        => 'string',
                            'description' => __( 'The URL of the image to insert. Can be an external URL (will be downloaded to media library) or a URL from allsi/search-image results.', 'all-sources-images' ),
                        ),
                        'attachment_id' => array(
                            'type'        => 'integer',
                            'description' => __( 'Alternative to image_url: Use an existing WordPress media library attachment ID. If both are provided, attachment_id takes priority.', 'all-sources-images' ),
                        ),
                        'position' => array(
                            'type'        => 'integer',
                            'minimum'     => 1,
                            'maximum'     => 20,
                            'default'     => 1,
                            'description' => __( 'Which element number to insert the image near. 1 = first paragraph/heading, 2 = second, etc. Use higher numbers for longer posts.', 'all-sources-images' ),
                        ),
                        'placement' => array(
                            'type'        => 'string',
                            'enum'        => array( 'after', 'before' ),
                            'default'     => 'after',
                            'description' => __( 'Whether to place the image "before" or "after" the target element. Default is "after" (image appears below the paragraph).', 'all-sources-images' ),
                        ),
                        'element' => array(
                            'type'        => 'string',
                            'enum'        => array( 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ),
                            'default'     => 'p',
                            'description' => __( 'The HTML element type to target. "p" for paragraphs (most common), "h2" for headings, etc. Default is "p" (paragraph).', 'all-sources-images' ),
                        ),
                        'image_size' => array(
                            'type'        => 'string',
                            'enum'        => array( 'thumbnail', 'medium', 'large', 'full' ),
                            'default'     => 'large',
                            'description' => __( 'WordPress image size to use. "large" is recommended for inline content. "full" for maximum quality, "medium" for smaller inline images.', 'all-sources-images' ),
                        ),
                        'alt_text' => array(
                            'type'        => 'string',
                            'description' => __( 'Alt text for the image. Used for accessibility and SEO. If not provided, uses the image filename or search term.', 'all-sources-images' ),
                        ),
                    ),
                    'required' => array( 'post_id' ),
                ),
                'output_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'success' => array(
                            'type'        => 'boolean',
                            'description' => __( 'True if the image was successfully inserted into the post content.', 'all-sources-images' ),
                        ),
                        'post_id' => array(
                            'type'        => 'integer',
                            'description' => __( 'The post ID that was modified.', 'all-sources-images' ),
                        ),
                        'attachment_id' => array(
                            'type'        => 'integer',
                            'description' => __( 'The attachment ID of the inserted image.', 'all-sources-images' ),
                        ),
                        'position_description' => array(
                            'type'        => 'string',
                            'description' => __( 'Human-readable description of where the image was inserted (e.g., "after paragraph 2").', 'all-sources-images' ),
                        ),
                        'error' => array(
                            'type'        => 'string',
                            'description' => __( 'Error message if the operation failed.', 'all-sources-images' ),
                        ),
                    ),
                ),
                'execute_callback'    => array( $this, 'execute_insert_image_in_content' ),
                'permission_callback' => array( $this, 'can_edit_posts' ),
                'meta'                => array(
                    'show_in_rest' => true,
                    'mcp'          => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                ),
            )
        );
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            if ( is_wp_error( $result ) ) {
                ALLSI_log( 'ALLSI_Abilities: Failed to register allsi/insert-image-in-content: ' . $result->get_error_message() );
            } else {
                ALLSI_log( 'ALLSI_Abilities: Registered allsi/insert-image-in-content successfully' );
            }
        }
    }

    /**
     * Register the generate-ai-image ability.
     *
     * Allows AI agents to generate images using AI models (DALL-E, Stability, Gemini, etc.).
     *
     * @return void
     */
    private function register_generate_ai_image_ability() {
        $result = wp_register_ability(
            'allsi/generate-ai-image',
            array(
                'label'       => __( 'Generate AI Image', 'all-sources-images' ),
                'description' => __( 'Generate an image using artificial intelligence. Supports multiple AI providers: OpenAI DALL-E 3 (high quality, photorealistic), Stability AI / Stable Diffusion (artistic, customizable styles), Google Gemini (multimodal AI), Replicate (various AI models), and Cloudflare Workers AI (fast, edge-based). Provide a detailed text prompt describing the image you want. The AI will create a unique image based on your description. Returns the generated image URL ready for use.', 'all-sources-images' ),
                'category'    => 'media',
                'input_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'prompt' => array(
                            'type'        => 'string',
                            'description' => __( 'The text description of the image to generate. Be specific and detailed for best results. Example: "A majestic golden dragon flying over snow-capped mountains at sunset, photorealistic, highly detailed, cinematic lighting". For DALL-E, the prompt will be prefixed with "Photorealistic image of".', 'all-sources-images' ),
                        ),
                        'source' => array(
                            'type'        => 'string',
                            'enum'        => array( 'dallev1', 'stability', 'gemini', 'replicate', 'workers_ai' ),
                            'default'     => 'dallev1',
                            'description' => __( 'The AI image generator to use. dallev1 (OpenAI DALL-E 3): High quality, photorealistic, best for general use. stability (Stable Diffusion): Artistic styles, good for creative images. gemini (Google Gemini): Multimodal AI, good for complex prompts. replicate (Various models): Multiple AI models available. workers_ai (Cloudflare): Fast edge-based generation.', 'all-sources-images' ),
                        ),
                        'size' => array(
                            'type'        => 'string',
                            'enum'        => array( '1024x1024', '1024x1792', '1792x1024' ),
                            'default'     => '1024x1024',
                            'description' => __( 'Image dimensions. "1024x1024" for square images (default, good for most uses). "1024x1792" for portrait/vertical images. "1792x1024" for landscape/horizontal images. Note: Some providers may have different size options.', 'all-sources-images' ),
                        ),
                        'style' => array(
                            'type'        => 'string',
                            'enum'        => array( 'vivid', 'natural' ),
                            'default'     => 'vivid',
                            'description' => __( 'Image style for DALL-E. "vivid" produces hyper-real, dramatic images (default). "natural" produces more realistic, less exaggerated images. Only applies to DALL-E; other providers use their default styles.', 'all-sources-images' ),
                        ),
                        'quality' => array(
                            'type'        => 'string',
                            'enum'        => array( 'standard', 'hd' ),
                            'default'     => 'hd',
                            'description' => __( 'Image quality for DALL-E. "hd" produces higher detail images with finer textures (default, recommended). "standard" is faster but lower quality. Only applies to DALL-E.', 'all-sources-images' ),
                        ),
                    ),
                    'required' => array( 'prompt' ),
                ),
                'output_schema' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'success' => array(
                            'type'        => 'boolean',
                            'description' => __( 'True if the image was generated successfully.', 'all-sources-images' ),
                        ),
                        'url' => array(
                            'type'        => 'string',
                            'description' => __( 'The URL of the generated image. This is a temporary URL from the AI provider. Use allsi/set-featured-image or allsi/insert-image-in-content to save it to WordPress.', 'all-sources-images' ),
                        ),
                        'prompt_used' => array(
                            'type'        => 'string',
                            'description' => __( 'The actual prompt sent to the AI (may be modified from input).', 'all-sources-images' ),
                        ),
                        'revised_prompt' => array(
                            'type'        => 'string',
                            'description' => __( 'For DALL-E: The revised prompt that was actually used (DALL-E may modify prompts for safety/quality).', 'all-sources-images' ),
                        ),
                        'source' => array(
                            'type'        => 'string',
                            'description' => __( 'The AI provider that generated the image.', 'all-sources-images' ),
                        ),
                        'size' => array(
                            'type'        => 'string',
                            'description' => __( 'The size of the generated image.', 'all-sources-images' ),
                        ),
                        'error' => array(
                            'type'        => 'string',
                            'description' => __( 'Error message if generation failed. Common reasons: missing API key, invalid prompt, API rate limits.', 'all-sources-images' ),
                        ),
                    ),
                ),
                'execute_callback'    => array( $this, 'execute_generate_ai_image' ),
                'permission_callback' => array( $this, 'can_edit_posts' ),
                'meta'                => array(
                    'show_in_rest' => true,
                    'mcp'          => array(
                        'public' => true,
                        'type'   => 'tool',
                    ),
                ),
            )
        );
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            if ( is_wp_error( $result ) ) {
                ALLSI_log( 'ALLSI_Abilities: Failed to register allsi/generate-ai-image: ' . $result->get_error_message() );
            } else {
                ALLSI_log( 'ALLSI_Abilities: Registered allsi/generate-ai-image successfully' );
            }
        }
    }

    // =========================================================================
    // Permission Callbacks
    // =========================================================================

    /**
     * Check if current user can edit posts.
     *
     * Simple permission check - user must be able to edit posts.
     * This is the default permission for all image-related abilities.
     *
     * @return bool
     */
    public function can_edit_posts() {
        return current_user_can( 'edit_posts' );
    }

    // =========================================================================
    // Execute Callbacks
    // =========================================================================

    /**
     * Execute the search-image ability.
     *
     * Uses ALLSI_create_thumb with get_only_thumb=true to leverage existing search logic.
     *
     * @param array $input Input parameters from the ability call.
     * @return array Result with images or error.
     */
    public function execute_search_image( $input ) {
        $search_term    = isset( $input['search_term'] ) ? sanitize_text_field( $input['search_term'] ) : '';
        $source         = isset( $input['source'] ) ? sanitize_key( $input['source'] ) : 'pixabay';
        $count          = isset( $input['count'] ) ? min( 20, max( 1, absint( $input['count'] ) ) ) : 5;
        $selected_image = isset( $input['selection'] ) ? sanitize_key( $input['selection'] ) : 'random_result'; // 'first_result' or 'random_result'

        if ( empty( $search_term ) ) {
            return array(
                'success' => false,
                'error'   => __( 'Search term is required.', 'all-sources-images' ),
            );
        }

        // Get generation instance
        $generation = $this->get_generation_instance();
        if ( ! $generation ) {
            return array(
                'success' => false,
                'error'   => __( 'Generation class not available.', 'all-sources-images' ),
            );
        }

        // Add capability for this operation
        add_filter( 'user_has_cap', function( $allcaps ) {
            $allcaps['ALLSI_manage'] = true;
            return $allcaps;
        } );

        try {
            // Use ALLSI_create_thumb with get_only_thumb=true (same as manual search)
            // This reuses all existing logic: proxy, API keys, selected_image mode, etc.
            $result = $generation->ALLSI_create_thumb(
                0,                    // $id - no post
                0,                    // $check_value_enable
                0,                    // $check_post_type
                0,                    // $check_category
                false,                // $rewrite_featured
                true,                 // $get_only_thumb - SEARCH MODE
                $search_term,         // $extracted_search_term
                $source,              // $api_chosen
                null,                 // $key_img_block
                true,                 // $avoid_revision
                true,                 // $include_datas
                false,                // $button_autogenerate
                array(                // $additional_context
                    'selected_image' => $selected_image,
                    'count'          => $count,
                )
            );

            // ALLSI_create_thumb returns the raw API response when get_only_thumb=true
            if ( is_wp_error( $result ) ) {
                return array(
                    'success'     => false,
                    'search_term' => $search_term,
                    'source'      => $source,
                    'images'      => array(),
                    'count'       => 0,
                    'error'       => $result->get_error_message(),
                );
            }

            // Parse the response based on source format
            $images = $this->parse_search_results( $result, $source, $search_term, $count );

            if ( empty( $images ) ) {
                return array(
                    'success'     => false,
                    'search_term' => $search_term,
                    'source'      => $source,
                    'images'      => array(),
                    'count'       => 0,
                    'error'       => __( 'No images found for this search term.', 'all-sources-images' ),
                );
            }

            return array(
                'success'     => true,
                'search_term' => $search_term,
                'source'      => $source,
                'images'      => $images,
                'count'       => count( $images ),
            );

        } catch ( Exception $e ) {
            return array(
                'success' => false,
                'error'   => $e->getMessage(),
            );
        }
    }

    /**
     * Parse search results from different API formats.
     *
     * @param mixed  $result      Raw API response.
     * @param string $source      Source name.
     * @param string $search_term Original search term.
     * @param int    $count       Max images to return.
     * @return array Normalized array of images.
     */
    private function parse_search_results( $result, $source, $search_term, $count ) {
        $images = array();

        if ( ! is_array( $result ) ) {
            return $images;
        }

        // Pixabay format
        if ( isset( $result['hits'] ) && is_array( $result['hits'] ) ) {
            foreach ( array_slice( $result['hits'], 0, $count ) as $hit ) {
                // translators: %s is the photographer's name.
                $caption = sprintf( __( 'Photo by %s on Pixabay', 'all-sources-images' ), isset( $hit['user'] ) ? $hit['user'] : 'Unknown' );
                $images[] = array(
                    'url'       => isset( $hit['largeImageURL'] ) ? $hit['largeImageURL'] : ( isset( $hit['webformatURL'] ) ? $hit['webformatURL'] : '' ),
                    'thumbnail' => isset( $hit['previewURL'] ) ? $hit['previewURL'] : '',
                    'alt'       => isset( $hit['tags'] ) ? $hit['tags'] : $search_term,
                    'caption'   => $caption,
                    'width'     => isset( $hit['imageWidth'] ) ? (int) $hit['imageWidth'] : 0,
                    'height'    => isset( $hit['imageHeight'] ) ? (int) $hit['imageHeight'] : 0,
                );
            }
        }
        // Pexels format
        elseif ( isset( $result['photos'] ) && is_array( $result['photos'] ) ) {
            foreach ( array_slice( $result['photos'], 0, $count ) as $photo ) {
                // translators: %s is the photographer's name.
                $caption = sprintf( __( 'Photo by %s on Pexels', 'all-sources-images' ), isset( $photo['photographer'] ) ? $photo['photographer'] : 'Unknown' );
                $images[] = array(
                    'url'       => isset( $photo['src']['original'] ) ? $photo['src']['original'] : ( isset( $photo['src']['large'] ) ? $photo['src']['large'] : '' ),
                    'thumbnail' => isset( $photo['src']['medium'] ) ? $photo['src']['medium'] : '',
                    'alt'       => isset( $photo['alt'] ) ? $photo['alt'] : $search_term,
                    'caption'   => $caption,
                    'width'     => isset( $photo['width'] ) ? (int) $photo['width'] : 0,
                    'height'    => isset( $photo['height'] ) ? (int) $photo['height'] : 0,
                );
            }
        }
        // Unsplash format
        elseif ( isset( $result['results'] ) && is_array( $result['results'] ) ) {
            foreach ( array_slice( $result['results'], 0, $count ) as $item ) {
                // translators: %s is the photographer's name.
                $caption = sprintf( __( 'Photo by %s on Unsplash', 'all-sources-images' ), isset( $item['user']['name'] ) ? $item['user']['name'] : 'Unknown' );
                $images[] = array(
                    'url'       => isset( $item['urls']['regular'] ) ? $item['urls']['regular'] : '',
                    'thumbnail' => isset( $item['urls']['thumb'] ) ? $item['urls']['thumb'] : '',
                    'alt'       => isset( $item['alt_description'] ) ? $item['alt_description'] : $search_term,
                    'caption'   => $caption,
                    'width'     => isset( $item['width'] ) ? (int) $item['width'] : 0,
                    'height'    => isset( $item['height'] ) ? (int) $item['height'] : 0,
                );
            }
        }
        // Standard plugin format with url_results
        elseif ( isset( $result['url_results'] ) ) {
            $urls = is_array( $result['url_results'] ) ? $result['url_results'] : array( $result['url_results'] );
            $alts = isset( $result['alt_img'] ) ? ( is_array( $result['alt_img'] ) ? $result['alt_img'] : array( $result['alt_img'] ) ) : array();
            $captions = isset( $result['caption_img'] ) ? ( is_array( $result['caption_img'] ) ? $result['caption_img'] : array( $result['caption_img'] ) ) : array();

            foreach ( array_slice( $urls, 0, $count ) as $index => $url ) {
                $images[] = array(
                    'url'       => $url,
                    'thumbnail' => $url,
                    'alt'       => isset( $alts[ $index ] ) ? $alts[ $index ] : $search_term,
                    'caption'   => isset( $captions[ $index ] ) ? $captions[ $index ] : '',
                    'width'     => 0,
                    'height'    => 0,
                );
            }
        }
        // Replicate format: {"output": ["url1", "url2", ...]} or {"output": "url"}
        elseif ( isset( $result['output'] ) ) {
            $outputs = is_array( $result['output'] ) ? $result['output'] : array( $result['output'] );
            $model_name = isset( $result['model'] ) ? $result['model'] : 'Replicate';
            
            foreach ( array_slice( $outputs, 0, $count ) as $url ) {
                if ( ! is_string( $url ) || empty( $url ) ) {
                    continue;
                }
                // translators: %s is the AI model name used on Replicate.
                $caption = sprintf( __( 'Generated with %s on Replicate', 'all-sources-images' ), $model_name );
                $images[] = array(
                    'url'       => $url,
                    'thumbnail' => $url,
                    'alt'       => $search_term,
                    'caption'   => $caption,
                    'width'     => 0,
                    'height'    => 0,
                );
            }
        }
        // DALL-E format: {"data": [{"url": "...", "revised_prompt": "..."}]}
        elseif ( isset( $result['data'] ) && is_array( $result['data'] ) ) {
            foreach ( array_slice( $result['data'], 0, $count ) as $item ) {
                $url = isset( $item['url'] ) ? $item['url'] : '';
                if ( empty( $url ) ) {
                    continue;
                }
                $images[] = array(
                    'url'       => $url,
                    'thumbnail' => $url,
                    'alt'       => isset( $item['revised_prompt'] ) ? $item['revised_prompt'] : $search_term,
                    'caption'   => __( 'Generated with DALL-E', 'all-sources-images' ),
                    'width'     => 0,
                    'height'    => 0,
                );
            }
        }
        // Stability AI format: binary image response is handled differently, but if JSON
        elseif ( isset( $result['artifacts'] ) && is_array( $result['artifacts'] ) ) {
            foreach ( array_slice( $result['artifacts'], 0, $count ) as $artifact ) {
                // Stability can return base64 or URL
                $url = '';
                if ( isset( $artifact['url'] ) ) {
                    $url = $artifact['url'];
                } elseif ( isset( $artifact['base64'] ) ) {
                    // Base64 images need special handling - skip for now
                    continue;
                }
                if ( empty( $url ) ) {
                    continue;
                }
                $images[] = array(
                    'url'       => $url,
                    'thumbnail' => $url,
                    'alt'       => $search_term,
                    'caption'   => __( 'Generated with Stability AI', 'all-sources-images' ),
                    'width'     => 0,
                    'height'    => 0,
                );
            }
        }
        // Gemini format: {"candidates": [{"content": {"parts": [{"inlineData": {"data": "base64..."}}]}}]}
        // Note: Gemini returns base64, which requires special handling in the source class
        elseif ( isset( $result['candidates'] ) && is_array( $result['candidates'] ) ) {
            // Gemini images are base64 encoded - the source class handles conversion
            // If we got here with raw response, we can't easily provide a URL
            // This format is handled by the Gemini source class which saves to temp file
        }
        // Workers AI / Cloudflare format: can vary, often returns binary or {"result": {"image": "base64"}}
        elseif ( isset( $result['result'] ) && is_array( $result['result'] ) ) {
            if ( isset( $result['result']['image'] ) ) {
                // Base64 image - skip for abilities (needs conversion)
            }
        }

        return $images;
    }

    /**
     * Execute the set-featured-image ability.
     *
     * @param array $input Input parameters from the ability call.
     * @return array Result with attachment info or error.
     */
    public function execute_set_featured_image( $input ) {
        $post_id   = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
        $image_url = isset( $input['image_url'] ) ? esc_url_raw( $input['image_url'] ) : '';
        $alt_text  = isset( $input['alt_text'] ) ? sanitize_text_field( $input['alt_text'] ) : '';
        $caption   = isset( $input['caption'] ) ? sanitize_text_field( $input['caption'] ) : '';

        // Validate post
        if ( ! $post_id ) {
            return array(
                'success' => false,
                'error'   => __( 'Post ID is required.', 'all-sources-images' ),
            );
        }

        $post = get_post( $post_id );
        if ( ! $post ) {
            return array(
                'success' => false,
                // translators: %d is the WordPress post ID.
                'error'   => sprintf( __( 'Post with ID %d not found.', 'all-sources-images' ), $post_id ),
            );
        }

        // Check permission for this specific post
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return array(
                'success' => false,
                'error'   => __( 'You do not have permission to edit this post.', 'all-sources-images' ),
            );
        }

        // Validate image URL
        if ( empty( $image_url ) ) {
            return array(
                'success' => false,
                'error'   => __( 'Image URL is required.', 'all-sources-images' ),
            );
        }

        // Get generation instance
        $generation = $this->get_generation_instance();
        if ( ! $generation ) {
            return array(
                'success' => false,
                'error'   => __( 'Generation class not available.', 'all-sources-images' ),
            );
        }

        // Use default alt text from post title if not provided
        if ( empty( $alt_text ) ) {
            $alt_text = $post->post_title;
        }

        // Download and attach the image
        try {
            // Use WordPress's media_sideload_image or custom download function
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            // Download file to temp location
            $tmp = download_url( $image_url, 30 );

            if ( is_wp_error( $tmp ) ) {
                return array(
                    'success' => false,
                    'error'   => sprintf( __( 'Failed to download image: %s', 'all-sources-images' ), $tmp->get_error_message() ),
                );
            }

            // Get filename from URL
            $url_path = wp_parse_url( $image_url, PHP_URL_PATH );
            $filename = basename( $url_path );

            // If no extension, try to detect it
            if ( ! preg_match( '/\.(jpe?g|png|gif|webp)$/i', $filename ) ) {
                $filename = sanitize_title( $post->post_title ) . '.jpg';
            }

            $file_array = array(
                'name'     => $filename,
                'tmp_name' => $tmp,
            );

            // Sideload the file
            $attachment_id = media_handle_sideload( $file_array, $post_id, $alt_text );

            if ( is_wp_error( $attachment_id ) ) {
                wp_delete_file( $tmp );
                return array(
                    'success' => false,
                    // translators: %s is the error message from media_handle_sideload.
                    'error'   => sprintf( __( 'Failed to sideload image: %s', 'all-sources-images' ), $attachment_id->get_error_message() ),
                );
            }

            // Set alt text and caption
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );

            if ( ! empty( $caption ) ) {
                wp_update_post( array(
                    'ID'           => $attachment_id,
                    'post_excerpt' => $caption,
                ) );
            }

            // Set as featured image
            $result = set_post_thumbnail( $post_id, $attachment_id );

            if ( ! $result ) {
                return array(
                    'success' => false,
                    'error'   => __( 'Failed to set featured image.', 'all-sources-images' ),
                );
            }

            return array(
                'success'       => true,
                'post_id'       => $post_id,
                'attachment_id' => $attachment_id,
                'thumbnail_url' => wp_get_attachment_url( $attachment_id ),
            );

        } catch ( Exception $e ) {
            return array(
                'success' => false,
                'error'   => $e->getMessage(),
            );
        }
    }

    /**
     * Execute the auto-generate-for-post ability.
     *
     * Uses a two-step approach: first searches for an image using the post title,
     * then sets it as the featured image using the same logic as set-featured-image.
     *
     * @param array $input Input parameters from the ability call.
     * @return array Result with generation info or error.
     */
    public function execute_auto_generate_for_post( $input ) {
        $post_id   = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
        $source    = isset( $input['source'] ) ? sanitize_key( $input['source'] ) : 'pixabay';
        $overwrite = isset( $input['overwrite'] ) ? (bool) $input['overwrite'] : false;

        // Validate post
        if ( ! $post_id ) {
            return array(
                'success' => false,
                'error'   => __( 'Post ID is required.', 'all-sources-images' ),
            );
        }

        $post = get_post( $post_id );
        if ( ! $post ) {
            return array(
                'success' => false,
                'error'   => sprintf( __( 'Post with ID %d not found.', 'all-sources-images' ), $post_id ),
            );
        }

        // Check permission for this specific post
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return array(
                'success' => false,
                'error'   => __( 'You do not have permission to edit this post.', 'all-sources-images' ),
            );
        }

        // Check if already has featured image
        if ( has_post_thumbnail( $post_id ) && ! $overwrite ) {
            return array(
                'success'       => false,
                'post_id'       => $post_id,
                'post_title'    => $post->post_title,
                'error'         => __( 'Post already has a featured image. Set overwrite=true to replace it.', 'all-sources-images' ),
            );
        }

        try {
            // Step 1: Extract search term from post title (simplify long titles)
            $search_term = $this->extract_search_term( $post->post_title );

            // Step 2: Search for an image
            $search_result = $this->execute_search_image( array(
                'search_term' => $search_term,
                'source'      => $source,
                'count'       => 5,
                'selection'   => 'random_result',
            ) );

            if ( ! $search_result['success'] || empty( $search_result['images'] ) ) {
                return array(
                    'success'     => false,
                    'post_id'     => $post_id,
                    'post_title'  => $post->post_title,
                    'search_term' => $search_term,
                    // translators: %s is the search term used to find images.
                    'error'       => sprintf(
                        __( 'No images found for search term: %s', 'all-sources-images' ),
                        $search_term
                    ),
                );
            }

            // Step 3: Pick a random image from results
            $selected_image = $search_result['images'][ array_rand( $search_result['images'] ) ];
            $image_url = $selected_image['url'];
            $alt_text = ! empty( $selected_image['alt'] ) ? $selected_image['alt'] : $search_term;
            $caption = ! empty( $selected_image['caption'] ) ? $selected_image['caption'] : '';

            // Step 4: Set the featured image
            $set_result = $this->execute_set_featured_image( array(
                'post_id'   => $post_id,
                'image_url' => $image_url,
                'alt_text'  => $alt_text,
                'caption'   => $caption,
            ) );

            if ( ! $set_result['success'] ) {
                return array(
                    'success'     => false,
                    'post_id'     => $post_id,
                    'post_title'  => $post->post_title,
                    'search_term' => $search_term,
                    'error'       => $set_result['error'],
                );
            }

            return array(
                'success'       => true,
                'post_id'       => $post_id,
                'post_title'    => $post->post_title,
                'search_term'   => $search_term,
                'source_used'   => $source,
                'attachment_id' => $set_result['attachment_id'],
                'image_url'     => $set_result['thumbnail_url'],
            );

        } catch ( Exception $e ) {
            return array(
                'success' => false,
                'error'   => $e->getMessage(),
            );
        }
    }

    /**
     * Execute the insert-image-in-content ability.
     *
     * Inserts an image within post content at a specified position.
     *
     * @param array $input Input parameters from the ability call.
     * @return array Result with insertion info or error.
     */
    public function execute_insert_image_in_content( $input ) {
        $post_id       = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
        $image_url     = isset( $input['image_url'] ) ? esc_url_raw( $input['image_url'] ) : '';
        $attachment_id = isset( $input['attachment_id'] ) ? absint( $input['attachment_id'] ) : 0;
        $position      = isset( $input['position'] ) ? absint( $input['position'] ) : 1;
        $placement     = isset( $input['placement'] ) ? sanitize_key( $input['placement'] ) : 'after';
        $element       = isset( $input['element'] ) ? sanitize_key( $input['element'] ) : 'p';
        $image_size    = isset( $input['image_size'] ) ? sanitize_key( $input['image_size'] ) : 'large';
        $alt_text      = isset( $input['alt_text'] ) ? sanitize_text_field( $input['alt_text'] ) : '';

        // Validate post
        if ( ! $post_id ) {
            return array(
                'success' => false,
                'error'   => __( 'Post ID is required.', 'all-sources-images' ),
            );
        }

        $post = get_post( $post_id );
        if ( ! $post ) {
            return array(
                'success' => false,
                // translators: %d is the WordPress post ID.
                'error'   => sprintf( __( 'Post with ID %d not found.', 'all-sources-images' ), $post_id ),
            );
        }

        // Check permission for this specific post
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return array(
                'success' => false,
                'error'   => __( 'You do not have permission to edit this post.', 'all-sources-images' ),
            );
        }

        // Validate placement
        if ( ! in_array( $placement, array( 'before', 'after' ), true ) ) {
            $placement = 'after';
        }

        // Validate element
        $valid_elements = array( 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
        if ( ! in_array( $element, $valid_elements, true ) ) {
            $element = 'p';
        }

        // Validate image size
        $valid_sizes = array( 'thumbnail', 'medium', 'large', 'full' );
        if ( ! in_array( $image_size, $valid_sizes, true ) ) {
            $image_size = 'large';
        }

        // Need either image_url or attachment_id
        if ( empty( $image_url ) && ! $attachment_id ) {
            return array(
                'success' => false,
                'error'   => __( 'Either image_url or attachment_id is required.', 'all-sources-images' ),
            );
        }

        try {
            // If we have image_url but no attachment_id, download and create attachment
            if ( ! $attachment_id && ! empty( $image_url ) ) {
                // Require media functions
                if ( ! function_exists( 'media_handle_sideload' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    require_once ABSPATH . 'wp-admin/includes/media.php';
                }

                // Download to temp file
                $tmp = download_url( $image_url, 30 );
                if ( is_wp_error( $tmp ) ) {
                    return array(
                        'success' => false,
                        'error'   => sprintf( __( 'Failed to download image: %s', 'all-sources-images' ), $tmp->get_error_message() ),
                    );
                }

                // Get filename from URL
                $url_path = wp_parse_url( $image_url, PHP_URL_PATH );
                $filename = basename( $url_path );

                // If no extension, add .jpg
                if ( ! preg_match( '/\.(jpe?g|png|gif|webp)$/i', $filename ) ) {
                    $filename = sanitize_title( $post->post_title ) . '-inline.jpg';
                }

                $file_array = array(
                    'name'     => $filename,
                    'tmp_name' => $tmp,
                );

                // Sideload the file
                $attachment_id = media_handle_sideload( $file_array, $post_id, $alt_text );

                if ( is_wp_error( $attachment_id ) ) {
                    wp_delete_file( $tmp );
                    return array(
                        'success' => false,
                        // translators: %s is the error message from media_handle_sideload.
                        'error'   => sprintf( __( 'Failed to sideload image: %s', 'all-sources-images' ), $attachment_id->get_error_message() ),
                    );
                }

                // Set alt text
                if ( ! empty( $alt_text ) ) {
                    update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
                }
            }

            // Verify attachment exists
            if ( ! wp_attachment_is_image( $attachment_id ) ) {
                return array(
                    'success' => false,
                    // translators: %d is the WordPress attachment ID.
                    'error'   => sprintf( __( 'Attachment ID %d is not a valid image.', 'all-sources-images' ), $attachment_id ),
                );
            }

            // Get the generation instance to use ALLSI_insert_content_image
            $generation = $this->get_generation_instance();
            if ( ! $generation ) {
                return array(
                    'success' => false,
                    'error'   => __( 'Generation class not available.', 'all-sources-images' ),
                );
            }

            // Get current post content
            $content = $post->post_content;

            if ( empty( trim( $content ) ) ) {
                return array(
                    'success' => false,
                    'error'   => __( 'Post has no content to insert image into.', 'all-sources-images' ),
                );
            }

            // Use reflection to call private method ALLSI_insert_content_image
            $reflection = new ReflectionMethod( $generation, 'ALLSI_insert_content_image' );
            $reflection->setAccessible( true );

            $new_content = $reflection->invoke(
                $generation,
                $content,
                $attachment_id,
                $element,
                $placement,
                $position,
                $image_size
            );

            // Update the post content
            $update_result = wp_update_post( array(
                'ID'           => $post_id,
                'post_content' => $new_content,
            ), true );

            if ( is_wp_error( $update_result ) ) {
                return array(
                    'success' => false,
                    // translators: %s is the error message from wp_update_post.
                    'error'   => sprintf( __( 'Failed to update post: %s', 'all-sources-images' ), $update_result->get_error_message() ),
                );
            }

            // Create position description
            $element_names = array(
                'p'  => 'paragraph',
                'h1' => 'heading 1',
                'h2' => 'heading 2',
                'h3' => 'heading 3',
                'h4' => 'heading 4',
                'h5' => 'heading 5',
                'h6' => 'heading 6',
            );
            $element_name = isset( $element_names[ $element ] ) ? $element_names[ $element ] : $element;
            $position_description = sprintf( '%s %s %d', $placement, $element_name, $position );

            return array(
                'success'              => true,
                'post_id'              => $post_id,
                'attachment_id'        => $attachment_id,
                'position_description' => $position_description,
                'image_url'            => wp_get_attachment_url( $attachment_id ),
            );

        } catch ( Exception $e ) {
            return array(
                'success' => false,
                'error'   => $e->getMessage(),
            );
        }
    }

    /**
     * Execute the generate-ai-image ability.
     *
     * Generates an image using AI (DALL-E, Stability, Gemini, etc.).
     *
     * @param array $input Input parameters from the ability call.
     * @return array Result with generated image info or error.
     */
    public function execute_generate_ai_image( $input ) {
        $prompt  = isset( $input['prompt'] ) ? sanitize_text_field( $input['prompt'] ) : '';
        $source  = isset( $input['source'] ) ? sanitize_key( $input['source'] ) : 'dallev1';
        $size    = isset( $input['size'] ) ? sanitize_text_field( $input['size'] ) : '1024x1024';
        $style   = isset( $input['style'] ) ? sanitize_key( $input['style'] ) : 'vivid';
        $quality = isset( $input['quality'] ) ? sanitize_key( $input['quality'] ) : 'hd';

        // Validate prompt
        if ( empty( $prompt ) ) {
            return array(
                'success' => false,
                'error'   => __( 'Prompt is required to generate an AI image.', 'all-sources-images' ),
            );
        }

        // Validate source is AI-capable
        $ai_sources = array( 'dallev1', 'stability', 'gemini', 'replicate', 'workers_ai' );
        if ( ! in_array( $source, $ai_sources, true ) ) {
            return array(
                'success' => false,
                // translators: %1$s is the invalid source name, %2$s is the list of valid AI sources.
                'error'   => sprintf(
                    __( 'Invalid AI source: %1$s. Valid options: %2$s', 'all-sources-images' ),
                    $source,
                    implode( ', ', $ai_sources )
                ),
            );
        }

        // Validate size
        $valid_sizes = array( '1024x1024', '1024x1792', '1792x1024' );
        if ( ! in_array( $size, $valid_sizes, true ) ) {
            $size = '1024x1024';
        }

        // Validate style (DALL-E specific)
        $valid_styles = array( 'vivid', 'natural' );
        if ( ! in_array( $style, $valid_styles, true ) ) {
            $style = 'vivid';
        }

        // Validate quality (DALL-E specific)
        $valid_qualities = array( 'standard', 'hd' );
        if ( ! in_array( $quality, $valid_qualities, true ) ) {
            $quality = 'hd';
        }

        // Get generation instance
        $generation = $this->get_generation_instance();
        if ( ! $generation ) {
            return array(
                'success' => false,
                'error'   => __( 'Generation class not available.', 'all-sources-images' ),
            );
        }

        // Add capability for this operation
        add_filter( 'user_has_cap', function( $allcaps ) {
            $allcaps['ALLSI_manage'] = true;
            return $allcaps;
        } );

        try {
            // Get the source manager instance from the generation class
            $source_manager = $generation->ALLSI_get_source_manager_instance();
            if ( ! $source_manager ) {
                return array(
                    'success' => false,
                    'error'   => __( 'Source manager not available.', 'all-sources-images' ),
                );
            }

            // Get the specific AI source
            $ai_source = $source_manager->get_source( $source );
            if ( ! $ai_source ) {
                return array(
                    'success' => false,
                    // translators: %s is the name of the AI source (e.g., dallev1, stability).
                    'error'   => sprintf( __( 'AI source "%s" not available.', 'all-sources-images' ), $source ),
                );
            }

            // Get plugin options (API keys, etc.)
            $banks_options = get_option( 'ALLSI_plugin_banks_settings', array() );
            $main_options  = get_option( 'ALLSI_plugin_main_settings', array() );

            // Build context for the AI source
            // For base64 sources (Workers AI, Gemini), we need full processing to get data URI
            $needs_full_processing = in_array( $source, array( 'workers_ai', 'gemini' ), true );
            $context = array(
                'search'         => $prompt,
                'options'        => $banks_options,
                'main_options'   => $main_options,
                'get_only_thumb' => ! $needs_full_processing, // Full processing for base64 sources
                'post_id'        => 0,
                'generation'     => $generation,
                'proxy_args'     => array(),
            );

            // Add source-specific options
            if ( 'dallev1' === $source ) {
                // Override DALL-E settings with ability parameters
                if ( ! isset( $context['options']['dallev1'] ) ) {
                    $context['options']['dallev1'] = array();
                }
                $context['options']['dallev1']['imgsize'] = $size;
                // Note: style and quality are handled by DALL-E source internally
            }

            // Generate the image
            $result = $ai_source->generate( $context );

            if ( is_wp_error( $result ) ) {
                return array(
                    'success'     => false,
                    'prompt_used' => $prompt,
                    'source'      => $source,
                    'error'       => $result->get_error_message(),
                );
            }

            // Parse the result based on source format
            $image_url       = '';
            $revised_prompt  = '';
            $model_used      = '';

            // DALL-E format: {"data": [{"url": "...", "revised_prompt": "..."}]}
            if ( isset( $result['data'] ) && is_array( $result['data'] ) && ! empty( $result['data'][0] ) ) {
                $image_url = isset( $result['data'][0]['url'] ) ? $result['data'][0]['url'] : '';
                $revised_prompt = isset( $result['data'][0]['revised_prompt'] ) ? $result['data'][0]['revised_prompt'] : '';
            }
            // Replicate format: {"output": ["url1", "url2", ...]} or {"output": "url"}
            elseif ( isset( $result['output'] ) ) {
                $outputs = is_array( $result['output'] ) ? $result['output'] : array( $result['output'] );
                foreach ( $outputs as $output ) {
                    if ( is_string( $output ) && ! empty( $output ) ) {
                        $image_url = $output;
                        break;
                    }
                }
                $model_used = isset( $result['model'] ) ? $result['model'] : '';
            }
            // Standard plugin format
            elseif ( isset( $result['url_results'] ) ) {
                $image_url = is_array( $result['url_results'] ) ? $result['url_results'][0] : $result['url_results'];
            }
            // Stability AI format: {"artifacts": [{"url": "..."}]} or binary response handled by source
            elseif ( isset( $result['artifacts'] ) && is_array( $result['artifacts'] ) && ! empty( $result['artifacts'][0] ) ) {
                $image_url = isset( $result['artifacts'][0]['url'] ) ? $result['artifacts'][0]['url'] : '';
            }
            // Gemini format (base64 image)
            elseif ( isset( $result['candidates'] ) && is_array( $result['candidates'] ) ) {
                // Gemini returns base64 encoded images, need special handling
                if ( isset( $result['candidates'][0]['content']['parts'][0]['inlineData']['data'] ) ) {
                    $base64_data = $result['candidates'][0]['content']['parts'][0]['inlineData']['data'];
                    $mime_type = isset( $result['candidates'][0]['content']['parts'][0]['inlineData']['mimeType'] ) 
                        ? $result['candidates'][0]['content']['parts'][0]['inlineData']['mimeType'] 
                        : 'image/png';
                    $image_url = 'data:' . $mime_type . ';base64,' . $base64_data;
                }
            }
            // Workers AI format: {"result": {"image": "base64..."}} - raw response with get_only_thumb
            elseif ( isset( $result['result'] ) && is_array( $result['result'] ) ) {
                $base64_image = '';
                if ( isset( $result['result']['image'] ) && is_string( $result['result']['image'] ) ) {
                    $base64_image = $result['result']['image'];
                } elseif ( isset( $result['result']['images'][0] ) && is_string( $result['result']['images'][0] ) ) {
                    $base64_image = $result['result']['images'][0];
                }
                if ( ! empty( $base64_image ) ) {
                    // Check if already a data URI
                    if ( strpos( $base64_image, 'data:' ) === 0 ) {
                        $image_url = $base64_image;
                    } else {
                        $image_url = 'data:image/png;base64,' . $base64_image;
                    }
                }
            }

            if ( empty( $image_url ) ) {
                return array(
                    'success'     => false,
                    'prompt_used' => $prompt,
                    'source'      => $source,
                    'error'       => __( 'AI generated no image URL. The API may have returned an unexpected format.', 'all-sources-images' ),
                );
            }

            $response = array(
                'success'     => true,
                'url'         => $image_url,
                'prompt_used' => $prompt,
                'source'      => $source,
                'size'        => $size,
            );

            if ( ! empty( $revised_prompt ) ) {
                $response['revised_prompt'] = $revised_prompt;
            }

            if ( ! empty( $model_used ) ) {
                $response['model'] = $model_used;
            }

            return $response;

        } catch ( Exception $e ) {
            return array(
                'success' => false,
                'error'   => $e->getMessage(),
            );
        }
    }

    /**
     * Extract a simplified search term from a post title.
     *
     * Removes common words and limits length for better search results.
     *
     * @param string $title The post title.
     * @return string Simplified search term.
     */
    private function extract_search_term( $title ) {
        // Remove HTML entities
        $term = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
        
        // Remove special characters except spaces and basic punctuation
        $term = preg_replace( '/[^\p{L}\p{N}\s\-]/u', '', $term );
        
        // Convert to lowercase
        $term = strtolower( $term );
        
        // Common stop words to remove
        $stop_words = array(
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been',
            'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
            'could', 'should', 'may', 'might', 'must', 'shall', 'can', 'need',
            'that', 'which', 'who', 'whom', 'this', 'these', 'those', 'it', 'its',
            'how', 'what', 'when', 'where', 'why', 'your', 'you', 'we', 'they',
            'simple', 'quick', 'fast', 'easy', 'guide', 'tutorial', 'modern',
            'complete', 'ultimate', 'best', 'every', 'all', 'any', 'make', 'way',
        );
        
        $words = explode( ' ', $term );
        $filtered = array_filter( $words, function( $word ) use ( $stop_words ) {
            return strlen( $word ) > 2 && ! in_array( $word, $stop_words );
        } );
        
        // Take first 3-5 meaningful words
        $filtered = array_slice( array_values( $filtered ), 0, 4 );
        
        // Join back
        $result = implode( ' ', $filtered );
        
        // Fallback: if too short, use first 3 words of original
        if ( strlen( $result ) < 5 ) {
            $words = explode( ' ', $term );
            $result = implode( ' ', array_slice( $words, 0, 3 ) );
        }
        
        return trim( $result );
    }
}

/**
 * Initialize the Abilities integration.
 *
 * @return ALLSI_Abilities
 */
function ALLSI_abilities_init() {
    return ALLSI_Abilities::instance();
}

// Auto-initialize when this file is loaded to ensure we catch wp_abilities_api_init hook
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    ALLSI_log( 'ALLSI_Abilities: File loaded. did_action(wp_abilities_api_init)=' . did_action( 'wp_abilities_api_init' ) );
}
ALLSI_abilities_init();