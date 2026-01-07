<?php
/**
 * Elementor integration bootstrapper for All Sources Images.
 */
class ALLSI_Elementor_Integration {

    /**
     * Plugin version for asset cache busting.
     *
     * @var string
     */
    private $version;

    /**
     * Base URL for this integration folder.
     *
     * @var string
     */
    private $base_url;

    /**
     * Base path for requiring files.
     *
     * @var string
     */
    private $base_path;

    /**
     * Constructor.
     *
     * @param string $version Plugin version.
     */
    public function __construct( $version ) {
        $this->version   = $version;
        $this->base_path = plugin_dir_path( __FILE__ );
        $this->base_url  = plugin_dir_url( __FILE__ );

        add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
        add_action( 'elementor/controls/register', array( $this, 'register_controls' ) );
        add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_editor_assets' ) );
    }

    /**
     * Register custom Elementor widget.
     */
    public function register_widgets( $widgets_manager ) {
        require_once $this->base_path . 'class-allsi-elementor-image-widget.php';
        $widgets_manager->register( new ALLSI_Elementor_Image_Widget() );
    }

    /**
     * Register custom Elementor controls.
     */
    public function register_controls( $controls_manager ) {
        require_once $this->base_path . 'class-allsi-elementor-control-media.php';
        $controls_manager->register( new ALLSI_Elementor_Control_Media() );
    }

    /**
     * Enqueue scripts/styles required for the custom control.
     */
    public function enqueue_editor_assets() {
        wp_enqueue_style(
            'allsi-elementor-media-control',
            $this->base_url . 'assets/css/elementor-widget.css',
            array(),
            $this->version
        );

        // Ensure media modal scripts are loaded
        wp_enqueue_media();

        // Register and enqueue the main ASI block script (contains allsiImagesExplorerMount)
        $block_dir = plugin_dir_path( __FILE__ ) . '../blocks/allsi-images/build/';
        $block_url = plugin_dir_url( __FILE__ ) . '../blocks/allsi-images/build/';
        
        if ( ! wp_script_is( 'allsi-images-script', 'registered' ) ) {
            // Check if asset file exists
            $asset_file = $block_dir . 'index.asset.php';
            if ( file_exists( $asset_file ) ) {
                $asset = include $asset_file;
                $dependencies = isset( $asset['dependencies'] ) ? $asset['dependencies'] : array( 'wp-element', 'wp-components', 'wp-i18n' );
                $version = isset( $asset['version'] ) ? $asset['version'] : $this->version;
            } else {
                $dependencies = array( 'wp-element', 'wp-components', 'wp-i18n', 'jquery' );
                $version = $this->version;
            }
            
            // Register minimasonry if needed
            if ( ! wp_script_is( 'allsi-minimasonry', 'registered' ) ) {
                wp_register_script(
                    'allsi-minimasonry',
                    plugin_dir_url( __FILE__ ) . '../blocks/allsi-images/build/minimasonry.min.js',
                    array(),
                    '1.3.2',
                    true
                );
            }
            $dependencies[] = 'allsi-minimasonry';
            
            wp_register_script(
                'allsi-images-script',
                $block_url . 'index.js',
                $dependencies,
                $version,
                true
            );
            
            // Localize with required data
            // Default manual banks: stock image sources selected by default
            // Order: Pixabay, Flickr, Openverse (cc_search), Unsplash, GIPHY, Pexels
            $default_manual_banks = array(
                'pixabay',
                'flickr',
                'cc_search',
                'unsplash',
                'giphy',
                'pexels',
            );
            $banks = wp_parse_args( get_option( 'ALLSI_plugin_banks_settings' ), array(
                'api_chosen_manual' => $default_manual_banks,
            ) );
            
            // Build available banks list (same as admin class)
            $manual_bank_labels = array(
                'google_image'    => __( 'Google Image (API)', 'all-sources-images' ),
                'dallev1'         => __( 'DALL·E (v3)', 'all-sources-images' ),
                'cc_search'       => __( 'Openverse', 'all-sources-images' ),
                'flickr'          => __( 'Flickr', 'all-sources-images' ),
                'pixabay'         => __( 'Pixabay', 'all-sources-images' ),
                'giphy'           => __( 'GIPHY', 'all-sources-images' ),
                'youtube'         => __( 'Youtube', 'all-sources-images' ),
                'unsplash'        => __( 'Unsplash', 'all-sources-images' ),
                'pexels'          => __( 'Pexels', 'all-sources-images' ),
                'stability'       => __( 'Stable Diffusion', 'all-sources-images' ),
                'replicate'       => __( 'Replicate', 'all-sources-images' ),
                'gemini'          => __( 'Gemini (Google AI)', 'all-sources-images' ),
                'workers_ai'      => __( 'Cloudflare Workers AI', 'all-sources-images' ),
            );
            
            // AI sources list
            $ai_sources = array( 'dallev1', 'stability', 'replicate', 'gemini', 'workers_ai' );
            
            // Check if translation to English is enabled in block settings
            $admin_class = new All_Sources_Images_Admin( 'all-sources-images', ALL_SOURCES_IMAGES_VERSION );
            $block_settings = wp_parse_args( get_option( 'ALLSI_plugin_block_settings' ), $admin_class->ALLSI_default_options_block_settings( TRUE ) );
            $translation_en_active = ( ! empty( $block_settings['translation_EN'] ) && $block_settings['translation_EN'] == 'true' );
            $translate_alt_active = ( ! empty( $block_settings['translate_alt'] ) && $block_settings['translate_alt'] == 'true' );
            $translate_alt_lang = ( ! empty( $block_settings['translate_alt_lang'] ) ? $block_settings['translate_alt_lang'] : '' );
            
            wp_localize_script( 'allsi-images-script', 'allsiAjax', array(
                'ajax_url'           => admin_url( 'admin-ajax.php' ),
                'admin_url'          => admin_url(),
                'nonce'              => wp_create_nonce( 'ALLSI_gutenberg_block' ),
                'choosed_banks'      => isset( $banks['api_chosen_manual'] ) ? $banks['api_chosen_manual'] : $default_manual_banks,
                'available_banks'    => $manual_bank_labels,
                'licensing_data'     => '1',
                'path_default_img'   => plugin_dir_url( __FILE__ ) . '../blocks/allsi-images/img/',
                'ai_sources'         => $ai_sources,
                'default_post_id'    => 0,
                'translation_en'     => $translation_en_active,
                'translate_alt'      => $translate_alt_active,
                'translate_alt_lang' => $translate_alt_lang,
            ) );
        }
        
        // Also register editor style if not registered
        if ( ! wp_style_is( 'allsi-images-editor-style', 'registered' ) ) {
            $css_file = $block_dir . 'allsi-images-editor.css';
            if ( file_exists( $css_file ) ) {
                wp_register_style(
                    'allsi-images-editor-style',
                    $block_url . 'allsi-images-editor.css',
                    array(),
                    filemtime( $css_file )
                );
            }
        }
        
        wp_enqueue_style( 'allsi-images-editor-style' );
        wp_enqueue_script( 'allsi-images-script' );

        // Ensure ASI media modal tab is registered
        if ( ! wp_script_is( 'allsi-media-modal', 'registered' ) ) {
            wp_register_script(
                'allsi-media-modal',
                plugins_url( '../js/allsi-media-modal.js', __FILE__ ),
                array( 'jquery', 'media-views' ),
                $this->version,
                true
            );
        }
        wp_localize_script( 'allsi-media-modal', 'allsiMediaModal', array(
            'tabLabel'       => __( 'All Sources Images', 'all-sources-images' ),
            'fallbackPostId' => 0,
            'tabId'          => 'allsi-media-tab',
        ) );
        wp_enqueue_script( 'allsi-media-modal' );

        wp_enqueue_script(
            'allsi-elementor-media-control',
            $this->base_url . 'assets/js/allsi-elementor-media-control.js',
            array( 'jquery', 'media-views', 'elementor-editor', 'allsi-media-modal' ),
            $this->version,
            true
        );

        wp_localize_script(
            'allsi-elementor-media-control',
            'allsiElementorMediaControl',
            array(
                'tabId'       => 'allsi-media-tab',
                'chooseLabel' => __( 'Choose with All Sources Images', 'all-sources-images' ),
                'removeLabel' => __( 'Remove image', 'all-sources-images' ),
                'placeholder' => __( 'No image selected', 'all-sources-images' ),
            )
        );
    }
}
