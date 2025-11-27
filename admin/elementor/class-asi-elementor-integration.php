<?php
/**
 * Elementor integration bootstrapper for All Sources Images.
 */
class ASI_Elementor_Integration {

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
        require_once $this->base_path . 'class-asi-elementor-image-widget.php';
        $widgets_manager->register( new ASI_Elementor_Image_Widget() );
    }

    /**
     * Register custom Elementor controls.
     */
    public function register_controls( $controls_manager ) {
        require_once $this->base_path . 'class-asi-elementor-control-media.php';
        $controls_manager->register( new ASI_Elementor_Control_Media() );
    }

    /**
     * Enqueue scripts/styles required for the custom control.
     */
    public function enqueue_editor_assets() {
        wp_enqueue_style(
            'asi-elementor-media-control',
            $this->base_url . 'assets/css/elementor-widget.css',
            array(),
            $this->version
        );

        // Ensure media modal scripts are loaded
        wp_enqueue_media();

        // Register and enqueue the main ASI block script (contains ASIImagesExplorerMount)
        $block_dir = plugin_dir_path( __FILE__ ) . '../blocks/asi-images/build/';
        $block_url = plugin_dir_url( __FILE__ ) . '../blocks/asi-images/build/';
        
        if ( ! wp_script_is( 'asi-images-script', 'registered' ) ) {
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
            if ( ! wp_script_is( 'asi-minimasonry', 'registered' ) ) {
                wp_register_script(
                    'asi-minimasonry',
                    plugin_dir_url( __FILE__ ) . '../blocks/asi-images/build/minimasonry.min.js',
                    array(),
                    '1.3.2',
                    true
                );
            }
            $dependencies[] = 'asi-minimasonry';
            
            wp_register_script(
                'asi-images-script',
                $block_url . 'index.js',
                $dependencies,
                $version,
                true
            );
            
            // Localize with required data
            $banks = wp_parse_args( get_option( 'ASI_plugin_banks_settings' ), array(
                'api_chosen_manual' => array( 'pixabay' ),
            ) );
            
            wp_localize_script( 'asi-images-script', 'asiAjax', array(
                'ajax_url'         => admin_url( 'admin-ajax.php' ),
                'admin_url'        => admin_url(),
                'nonce'            => wp_create_nonce( 'ASI_gutenberg_block' ),
                'choosed_banks'    => isset( $banks['api_chosen_manual'] ) ? $banks['api_chosen_manual'] : array( 'pixabay' ),
                'available_banks'  => array(
                    'pixabay'   => 'Pixabay',
                    'pexels'    => 'Pexels',
                    'unsplash'  => 'Unsplash',
                ),
                'licensing_data'   => '1',
                'path_default_img' => plugin_dir_url( __FILE__ ) . '../blocks/asi-images/img/',
                'ai_sources'       => array(),
                'default_post_id'  => 0,
            ) );
        }
        
        // Also register editor style if not registered
        if ( ! wp_style_is( 'asi-images-editor-style', 'registered' ) ) {
            $css_file = $block_dir . 'asi-images-editor.css';
            if ( file_exists( $css_file ) ) {
                wp_register_style(
                    'asi-images-editor-style',
                    $block_url . 'asi-images-editor.css',
                    array(),
                    filemtime( $css_file )
                );
            }
        }
        
        wp_enqueue_style( 'asi-images-editor-style' );
        wp_enqueue_script( 'asi-images-script' );

        // Ensure ASI media modal tab is registered
        if ( ! wp_script_is( 'asi-media-modal', 'registered' ) ) {
            wp_register_script(
                'asi-media-modal',
                plugins_url( '../js/asi-media-modal.js', __FILE__ ),
                array( 'jquery', 'media-views' ),
                $this->version,
                true
            );
        }
        wp_localize_script( 'asi-media-modal', 'asiMediaModal', array(
            'tabLabel'       => __( 'All Sources Images', 'all-sources-images' ),
            'fallbackPostId' => 0,
            'tabId'          => 'asi-media-tab',
        ) );
        wp_enqueue_script( 'asi-media-modal' );

        wp_enqueue_script(
            'asi-elementor-media-control',
            $this->base_url . 'assets/js/asi-elementor-media-control.js',
            array( 'jquery', 'media-views', 'elementor-editor', 'asi-media-modal' ),
            $this->version,
            true
        );

        wp_localize_script(
            'asi-elementor-media-control',
            'asiElementorMediaControl',
            array(
                'tabId'       => 'asi-media-tab',
                'chooseLabel' => __( 'Choose with All Sources Images', 'all-sources-images' ),
                'removeLabel' => __( 'Remove image', 'all-sources-images' ),
                'placeholder' => __( 'No image selected', 'all-sources-images' ),
            )
        );
    }
}
