<?php
/**
 * ASI Images Widget for Elementor
 *
 * @package    All_Sources_Images
 * @subpackage All_Sources_Images/admin/elementor
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ASI Images Elementor Widget
 */
class ASI_Elementor_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     *
     * @return string
     */
    public function get_name() {
        return 'asi-images';
    }

    /**
     * Get widget title
     *
     * @return string
     */
    public function get_title() {
        return __( 'ASI Images', 'all-sources-images' );
    }

    /**
     * Get widget icon
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-image-box';
    }

    /**
     * Get widget categories
     *
     * @return array
     */
    public function get_categories() {
        return array( 'general' );
    }

    /**
     * Get widget keywords
     *
     * @return array
     */
    public function get_keywords() {
        return array( 'image', 'gallery', 'media', 'asi', 'stock', 'photos' );
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __( 'Image Search', 'all-sources-images' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'search_term',
            array(
                'label'       => __( 'Search Term', 'all-sources-images' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => __( 'Enter search term...', 'all-sources-images' ),
                'description' => __( 'Leave empty to use post title or manual search', 'all-sources-images' ),
            )
        );

        // Get available image banks from plugin settings
        $bank_options = $this->get_available_banks();

        $this->add_control(
            'image_bank',
            array(
                'label'   => __( 'Image Bank', 'all-sources-images' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'pixabay',
                'options' => $bank_options,
            )
        );

        $this->add_control(
            'search_button',
            array(
                'label'       => __( 'Search Images', 'all-sources-images' ),
                'type'        => \Elementor\Controls_Manager::BUTTON,
                'text'        => __( 'Open Image Browser', 'all-sources-images' ),
                'button_type' => 'default',
                'event'       => 'asi:openBrowser',
            )
        );

        $this->add_control(
            'selected_image_url',
            array(
                'label'   => __( 'Selected Image URL', 'all-sources-images' ),
                'type'    => \Elementor\Controls_Manager::HIDDEN,
                'default' => '',
            )
        );

        $this->add_control(
            'selected_image_alt',
            array(
                'label'   => __( 'Image Alt Text', 'all-sources-images' ),
                'type'    => \Elementor\Controls_Manager::HIDDEN,
                'default' => '',
            )
        );

        $this->end_controls_section();

        // Display Settings Section
        $this->start_controls_section(
            'display_section',
            array(
                'label' => __( 'Display Settings', 'all-sources-images' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_responsive_control(
            'image_align',
            array(
                'label'   => __( 'Alignment', 'all-sources-images' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => array(
                    'left'   => array(
                        'title' => __( 'Left', 'all-sources-images' ),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center' => array(
                        'title' => __( 'Center', 'all-sources-images' ),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'right'  => array(
                        'title' => __( 'Right', 'all-sources-images' ),
                        'icon'  => 'eicon-text-align-right',
                    ),
                ),
                'default' => 'center',
                'selectors' => array(
                    '{{WRAPPER}} .asi-elementor-image-wrapper' => 'text-align: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'image_size',
            array(
                'label'   => __( 'Image Size', 'all-sources-images' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'full',
                'options' => array(
                    'thumbnail' => __( 'Thumbnail', 'all-sources-images' ),
                    'medium'    => __( 'Medium', 'all-sources-images' ),
                    'large'     => __( 'Large', 'all-sources-images' ),
                    'full'      => __( 'Full Size', 'all-sources-images' ),
                ),
            )
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __( 'Image Style', 'all-sources-images' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'image_width',
            array(
                'label'      => __( 'Width', 'all-sources-images' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( '%', 'px', 'vw' ),
                'range'      => array(
                    '%'  => array(
                        'min' => 0,
                        'max' => 100,
                    ),
                    'px' => array(
                        'min' => 0,
                        'max' => 2000,
                    ),
                ),
                'default'    => array(
                    'unit' => '%',
                    'size' => 100,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .asi-elementor-image img' => 'width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'image_border_radius',
            array(
                'label'      => __( 'Border Radius', 'all-sources-images' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .asi-elementor-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Get available image banks from plugin settings
     *
     * @return array
     */
    private function get_available_banks() {
        $banks = array(
            'pixabay'        => __( 'Pixabay', 'all-sources-images' ),
            'unsplash'       => __( 'Unsplash', 'all-sources-images' ),
            'pexels'         => __( 'Pexels', 'all-sources-images' ),
            'openverse'      => __( 'Openverse', 'all-sources-images' ),
            'flickr'         => __( 'Flickr', 'all-sources-images' ),
            'google_image'   => __( 'Google Images', 'all-sources-images' ),
            'giphy'          => __( 'Giphy', 'all-sources-images' ),
            'youtube'        => __( 'YouTube Thumbnails', 'all-sources-images' ),
            'dallev1'        => __( 'DALL·E', 'all-sources-images' ),
            'stability'      => __( 'Stable Diffusion', 'all-sources-images' ),
            'gemini'         => __( 'Gemini', 'all-sources-images' ),
            'replicate'      => __( 'Replicate', 'all-sources-images' ),
        );

        return $banks;
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $search_term = ! empty( $settings['search_term'] ) ? esc_attr( $settings['search_term'] ) : '';
        $image_bank = ! empty( $settings['image_bank'] ) ? esc_attr( $settings['image_bank'] ) : 'pixabay';
        $image_url = ! empty( $settings['selected_image_url'] ) ? esc_url( $settings['selected_image_url'] ) : '';
        $image_alt = ! empty( $settings['selected_image_alt'] ) ? esc_attr( $settings['selected_image_alt'] ) : '';
        
        ?>
        <div class="asi-elementor-image-wrapper" 
             data-widget-id="<?php echo esc_attr( $this->get_id() ); ?>"
             data-search-term="<?php echo esc_attr( $search_term ); ?>"
             data-image-bank="<?php echo esc_attr( $image_bank ); ?>">
            
            <?php if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) : ?>
                <!-- Editor Mode: Show search button -->
                <div class="asi-elementor-editor-controls">
                    <button type="button" class="asi-search-trigger elementor-button elementor-button-default">
                        <i class="eicon-search"></i>
                        <?php esc_html_e( 'Search Images', 'all-sources-images' ); ?>
                    </button>
                    <?php if ( ! empty( $image_url ) ) : ?>
                        <p class="asi-current-image-info">
                            <?php esc_html_e( 'Image selected', 'all-sources-images' ); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Actual Image Display -->
            <div class="asi-elementor-image">
                <?php if ( ! empty( $image_url ) ) : ?>
                    <img src="<?php echo esc_url( $image_url ); ?>" 
                         alt="<?php echo esc_attr( $image_alt ); ?>"
                         loading="lazy" />
                <?php else : ?>
                    <div class="asi-placeholder">
                        <i class="eicon-image-bold"></i>
                        <p><?php esc_html_e( 'No image selected. Click "Search Images" to browse.', 'all-sources-images' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modal container for image browser -->
            <div class="asi-modal-container"></div>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor (live preview)
     */
    protected function content_template() {
        ?>
        <#
        var searchTerm = settings.search_term || '';
        var imageBank = settings.image_bank || 'pixabay';
        var imageUrl = settings.selected_image_url || '';
        var imageAlt = settings.selected_image_alt || '';
        #>
        
        <div class="asi-elementor-image-wrapper" 
             data-widget-id="{{ view.getID() }}"
             data-search-term="{{ searchTerm }}"
             data-image-bank="{{ imageBank }}">
            
            <div class="asi-elementor-editor-controls">
                <button type="button" class="asi-search-trigger elementor-button elementor-button-default">
                    <i class="eicon-search"></i>
                    <?php esc_html_e( 'Search Images', 'all-sources-images' ); ?>
                </button>
                <# if ( imageUrl ) { #>
                    <p class="asi-current-image-info">
                        <?php esc_html_e( 'Image selected', 'all-sources-images' ); ?>
                    </p>
                <# } #>
            </div>

            <div class="asi-elementor-image">
                <# if ( imageUrl ) { #>
                    <img src="{{ imageUrl }}" alt="{{ imageAlt }}" loading="lazy" />
                <# } else { #>
                    <div class="asi-placeholder">
                        <i class="eicon-image-bold"></i>
                        <p><?php esc_html_e( 'No image selected. Click "Search Images" to browse.', 'all-sources-images' ); ?></p>
                    </div>
                <# } #>
            </div>
        </div>
        <?php
    }
}
