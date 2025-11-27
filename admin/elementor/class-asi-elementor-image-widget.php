<?php
use Elementor\Controls_Manager;
use Elementor\Widget_Base;

if ( ! class_exists( 'ASI_Elementor_Image_Widget' ) ) {
    /**
     * Elementor widget that mirrors the default image widget while forcing All Sources Images modal.
     */
    class ASI_Elementor_Image_Widget extends Widget_Base {

        public function get_name() {
            return 'asi-image';
        }

        public function get_title() {
            return __( 'ASI Image', 'all-sources-images' );
        }

        public function get_icon() {
            return 'eicon-image-bold';
        }

        public function get_categories() {
            return array( 'general' );
        }

        protected function register_controls() {
            $this->start_controls_section(
                'section_image',
                array(
                    'label' => __( 'Image', 'all-sources-images' ),
                )
            );

            $this->add_control(
                'asi_image',
                array(
                    'label'   => __( 'Image', 'all-sources-images' ),
                    'type'    => 'asi_media',
                    'dynamic' => array( 'active' => true ),
                )
            );

            $this->add_control(
                'image_size',
                array(
                    'label'   => __( 'Image Size', 'all-sources-images' ),
                    'type'    => Controls_Manager::IMAGE_DIMENSIONS,
                    'default' => array(
                        'width'  => '',
                        'height' => '',
                    ),
                )
            );

            $this->add_control(
                'alignment',
                array(
                    'label'   => __( 'Alignment', 'all-sources-images' ),
                    'type'    => Controls_Manager::CHOOSE,
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
                )
            );

            $this->add_control(
                'caption',
                array(
                    'label' => __( 'Caption', 'all-sources-images' ),
                    'type'  => Controls_Manager::TEXT,
                )
            );

            $this->end_controls_section();
        }

        protected function render() {
            $settings = $this->get_settings_for_display();
            if ( empty( $settings['asi_image']['url'] ) ) {
                return;
            }

            $this->add_render_attribute( 'wrapper', 'class', 'asi-elementor-image' );
            $this->add_render_attribute( 'wrapper', 'style', sprintf( 'text-align:%s;', esc_attr( $settings['alignment'] ) ) );

            $this->add_render_attribute( 'img', 'src', esc_url( $settings['asi_image']['url'] ) );
            if ( ! empty( $settings['asi_image']['id'] ) ) {
                $alt = get_post_meta( (int) $settings['asi_image']['id'], '_wp_attachment_image_alt', true );
                if ( $alt ) {
                    $this->add_render_attribute( 'img', 'alt', esc_attr( $alt ) );
                }
            }

            if ( ! empty( $settings['image_size']['width'] ) ) {
                $this->add_render_attribute( 'img', 'width', (int) $settings['image_size']['width'] );
            }
            if ( ! empty( $settings['image_size']['height'] ) ) {
                $this->add_render_attribute( 'img', 'height', (int) $settings['image_size']['height'] );
            }

            echo '<figure ' . $this->get_render_attribute_string( 'wrapper' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '<img ' . $this->get_render_attribute_string( 'img' ) . ' />'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            if ( ! empty( $settings['caption'] ) ) {
                echo '<figcaption>' . esc_html( $settings['caption'] ) . '</figcaption>';
            }
            echo '</figure>';
        }

        protected function content_template() {
            ?>
            <# if ( settings.asi_image && settings.asi_image.url ) { #>
                <figure class="asi-elementor-image" style="text-align: {{ settings.alignment }};">
                    <img src="{{ settings.asi_image.url }}" alt="" />
                    <# if ( settings.caption ) { #>
                        <figcaption>{{{ settings.caption }}}</figcaption>
                    <# } #>
                </figure>
            <# } else { #>
                <div class="asi-elementor-image placeholder">{{{ '<?php echo esc_js( __( 'Select an image', 'all-sources-images' ) ); ?>' }}}</div>
            <# } #>
            <?php
        }
    }
}
