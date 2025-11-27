<?php
use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;

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
            // =====================
            // CONTENT TAB
            // =====================
            $this->start_controls_section(
                'section_image',
                array(
                    'label' => __( 'Image', 'all-sources-images' ),
                )
            );

            $this->add_control(
                'asi_image',
                array(
                    'label'   => __( 'Choose Image', 'all-sources-images' ),
                    'type'    => 'asi_media',
                    'dynamic' => array( 'active' => true ),
                )
            );

            // Use Group_Control_Image_Size like the native Image widget
            $this->add_group_control(
                Group_Control_Image_Size::get_type(),
                array(
                    'name'      => 'asi_image', // Usage: `{name}_size` and `{name}_custom_dimension`
                    'default'   => 'large',
                    'condition' => array(
                        'asi_image[url]!' => '',
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
                    'default'   => 'center',
                    'selectors' => array(
                        '{{WRAPPER}} .asi-elementor-image' => 'text-align: {{VALUE}};',
                    ),
                )
            );

            $this->add_control(
                'caption',
                array(
                    'label' => __( 'Caption', 'all-sources-images' ),
                    'type'  => Controls_Manager::TEXT,
                    'dynamic' => array( 'active' => true ),
                    'condition' => array(
                        'asi_image[url]!' => '',
                    ),
                )
            );

            $this->add_control(
                'link_to',
                array(
                    'label'   => __( 'Link', 'all-sources-images' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'none',
                    'options' => array(
                        'none'   => __( 'None', 'all-sources-images' ),
                        'file'   => __( 'Media File', 'all-sources-images' ),
                        'custom' => __( 'Custom URL', 'all-sources-images' ),
                    ),
                    'condition' => array(
                        'asi_image[url]!' => '',
                    ),
                )
            );

            $this->add_control(
                'link',
                array(
                    'label'       => __( 'Link', 'all-sources-images' ),
                    'type'        => Controls_Manager::URL,
                    'dynamic'     => array( 'active' => true ),
                    'condition'   => array(
                        'asi_image[url]!' => '',
                        'link_to'         => 'custom',
                    ),
                    'show_label'  => false,
                )
            );

            $this->end_controls_section();

            // =====================
            // STYLE TAB - Image
            // =====================
            $this->start_controls_section(
                'section_style_image',
                array(
                    'label' => __( 'Image', 'all-sources-images' ),
                    'tab'   => Controls_Manager::TAB_STYLE,
                )
            );

            $this->add_responsive_control(
                'width',
                array(
                    'label'      => __( 'Width', 'all-sources-images' ),
                    'type'       => Controls_Manager::SLIDER,
                    'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
                    'range'      => array(
                        'px' => array(
                            'min' => 1,
                            'max' => 1000,
                        ),
                        '%'  => array(
                            'min' => 1,
                            'max' => 100,
                        ),
                        'vw' => array(
                            'min' => 1,
                            'max' => 100,
                        ),
                    ),
                    'selectors'  => array(
                        '{{WRAPPER}} img' => 'width: {{SIZE}}{{UNIT}};',
                    ),
                )
            );

            $this->add_responsive_control(
                'space',
                array(
                    'label'          => __( 'Max Width', 'all-sources-images' ),
                    'type'           => Controls_Manager::SLIDER,
                    'size_units'     => array( 'px', '%', 'em', 'rem', 'vw', 'custom' ),
                    'range'          => array(
                        'px' => array(
                            'min' => 1,
                            'max' => 1000,
                        ),
                        '%'  => array(
                            'min' => 1,
                            'max' => 100,
                        ),
                    ),
                    'selectors'      => array(
                        '{{WRAPPER}} img' => 'max-width: {{SIZE}}{{UNIT}};',
                    ),
                )
            );

            $this->add_responsive_control(
                'height',
                array(
                    'label'      => __( 'Height', 'all-sources-images' ),
                    'type'       => Controls_Manager::SLIDER,
                    'size_units' => array( 'px', '%', 'em', 'rem', 'vh', 'custom' ),
                    'range'      => array(
                        'px' => array(
                            'min' => 1,
                            'max' => 500,
                        ),
                        'vh' => array(
                            'min' => 1,
                            'max' => 100,
                        ),
                    ),
                    'selectors'  => array(
                        '{{WRAPPER}} img' => 'height: {{SIZE}}{{UNIT}};',
                    ),
                )
            );

            $this->add_responsive_control(
                'object-fit',
                array(
                    'label'     => __( 'Object Fit', 'all-sources-images' ),
                    'type'      => Controls_Manager::SELECT,
                    'condition' => array(
                        'height[size]!' => '',
                    ),
                    'options'   => array(
                        ''           => __( 'Default', 'all-sources-images' ),
                        'fill'       => __( 'Fill', 'all-sources-images' ),
                        'cover'      => __( 'Cover', 'all-sources-images' ),
                        'contain'    => __( 'Contain', 'all-sources-images' ),
                        'scale-down' => __( 'Scale Down', 'all-sources-images' ),
                    ),
                    'default'   => '',
                    'selectors' => array(
                        '{{WRAPPER}} img' => 'object-fit: {{VALUE}};',
                    ),
                )
            );

            $this->add_control(
                'separator_panel_style',
                array(
                    'type'  => Controls_Manager::DIVIDER,
                    'style' => 'thick',
                )
            );

            $this->start_controls_tabs( 'image_effects' );

            $this->start_controls_tab(
                'normal',
                array(
                    'label' => __( 'Normal', 'all-sources-images' ),
                )
            );

            $this->add_control(
                'opacity',
                array(
                    'label'     => __( 'Opacity', 'all-sources-images' ),
                    'type'      => Controls_Manager::SLIDER,
                    'range'     => array(
                        'px' => array(
                            'max'  => 1,
                            'min'  => 0.10,
                            'step' => 0.01,
                        ),
                    ),
                    'selectors' => array(
                        '{{WRAPPER}} img' => 'opacity: {{SIZE}};',
                    ),
                )
            );

            $this->add_group_control(
                Group_Control_Css_Filter::get_type(),
                array(
                    'name'     => 'css_filters',
                    'selector' => '{{WRAPPER}} img',
                )
            );

            $this->end_controls_tab();

            $this->start_controls_tab(
                'hover',
                array(
                    'label' => __( 'Hover', 'all-sources-images' ),
                )
            );

            $this->add_control(
                'opacity_hover',
                array(
                    'label'     => __( 'Opacity', 'all-sources-images' ),
                    'type'      => Controls_Manager::SLIDER,
                    'range'     => array(
                        'px' => array(
                            'max'  => 1,
                            'min'  => 0.10,
                            'step' => 0.01,
                        ),
                    ),
                    'selectors' => array(
                        '{{WRAPPER}}:hover img' => 'opacity: {{SIZE}};',
                    ),
                )
            );

            $this->add_group_control(
                Group_Control_Css_Filter::get_type(),
                array(
                    'name'     => 'css_filters_hover',
                    'selector' => '{{WRAPPER}}:hover img',
                )
            );

            $this->add_control(
                'background_hover_transition',
                array(
                    'label'     => __( 'Transition Duration', 'all-sources-images' ),
                    'type'      => Controls_Manager::SLIDER,
                    'range'     => array(
                        'px' => array(
                            'max'  => 3,
                            'step' => 0.1,
                        ),
                    ),
                    'selectors' => array(
                        '{{WRAPPER}} img' => 'transition-duration: {{SIZE}}s;',
                    ),
                )
            );

            $this->add_control(
                'hover_animation',
                array(
                    'label' => __( 'Hover Animation', 'all-sources-images' ),
                    'type'  => Controls_Manager::HOVER_ANIMATION,
                )
            );

            $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_group_control(
                Group_Control_Border::get_type(),
                array(
                    'name'      => 'image_border',
                    'selector'  => '{{WRAPPER}} img',
                    'separator' => 'before',
                )
            );

            $this->add_responsive_control(
                'image_border_radius',
                array(
                    'label'      => __( 'Border Radius', 'all-sources-images' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
                    'selectors'  => array(
                        '{{WRAPPER}} img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ),
                )
            );

            $this->add_group_control(
                Group_Control_Box_Shadow::get_type(),
                array(
                    'name'     => 'image_box_shadow',
                    'selector' => '{{WRAPPER}} img',
                )
            );

            $this->end_controls_section();

            // =====================
            // STYLE TAB - Caption
            // =====================
            $this->start_controls_section(
                'section_style_caption',
                array(
                    'label'     => __( 'Caption', 'all-sources-images' ),
                    'tab'       => Controls_Manager::TAB_STYLE,
                    'condition' => array(
                        'caption!' => '',
                    ),
                )
            );

            $this->add_control(
                'caption_align',
                array(
                    'label'     => __( 'Alignment', 'all-sources-images' ),
                    'type'      => Controls_Manager::CHOOSE,
                    'options'   => array(
                        'left'    => array(
                            'title' => __( 'Left', 'all-sources-images' ),
                            'icon'  => 'eicon-text-align-left',
                        ),
                        'center'  => array(
                            'title' => __( 'Center', 'all-sources-images' ),
                            'icon'  => 'eicon-text-align-center',
                        ),
                        'right'   => array(
                            'title' => __( 'Right', 'all-sources-images' ),
                            'icon'  => 'eicon-text-align-right',
                        ),
                        'justify' => array(
                            'title' => __( 'Justified', 'all-sources-images' ),
                            'icon'  => 'eicon-text-align-justify',
                        ),
                    ),
                    'default'   => '',
                    'selectors' => array(
                        '{{WRAPPER}} figcaption' => 'text-align: {{VALUE}};',
                    ),
                )
            );

            $this->add_control(
                'text_color',
                array(
                    'label'     => __( 'Text Color', 'all-sources-images' ),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => array(
                        '{{WRAPPER}} figcaption' => 'color: {{VALUE}};',
                    ),
                )
            );

            $this->add_control(
                'caption_background_color',
                array(
                    'label'     => __( 'Background Color', 'all-sources-images' ),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => array(
                        '{{WRAPPER}} figcaption' => 'background-color: {{VALUE}};',
                    ),
                )
            );

            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                array(
                    'name'     => 'caption_typography',
                    'selector' => '{{WRAPPER}} figcaption',
                )
            );

            $this->add_responsive_control(
                'caption_space',
                array(
                    'label'     => __( 'Spacing', 'all-sources-images' ),
                    'type'      => Controls_Manager::SLIDER,
                    'range'     => array(
                        'px' => array(
                            'min' => 0,
                            'max' => 100,
                        ),
                    ),
                    'selectors' => array(
                        '{{WRAPPER}} figcaption' => 'margin-top: {{SIZE}}{{UNIT}};',
                    ),
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

            // Get the image URL based on selected size using Group_Control_Image_Size
            $image_url = $settings['asi_image']['url'];
            $attachment_id = ! empty( $settings['asi_image']['id'] ) ? (int) $settings['asi_image']['id'] : 0;
            
            if ( $attachment_id ) {
                // Get image size from settings (created by Group_Control_Image_Size)
                $size = ! empty( $settings['asi_image_size'] ) ? $settings['asi_image_size'] : 'large';
                
                if ( 'custom' === $size && ! empty( $settings['asi_image_custom_dimension'] ) ) {
                    // Custom size with specific dimensions
                    $custom = $settings['asi_image_custom_dimension'];
                    $width = ! empty( $custom['width'] ) ? (int) $custom['width'] : 0;
                    $height = ! empty( $custom['height'] ) ? (int) $custom['height'] : 0;
                    
                    if ( $width || $height ) {
                        $image_src = wp_get_attachment_image_src( $attachment_id, array( $width, $height ) );
                        if ( $image_src ) {
                            $image_url = $image_src[0];
                        }
                    }
                } else {
                    // Standard WordPress size (thumbnail, medium, large, full, etc.)
                    $image_src = wp_get_attachment_image_src( $attachment_id, $size );
                    if ( $image_src ) {
                        $image_url = $image_src[0];
                    }
                }
            }

            $this->add_render_attribute( 'img', 'src', esc_url( $image_url ) );
            
            if ( $attachment_id ) {
                $alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
                if ( $alt ) {
                    $this->add_render_attribute( 'img', 'alt', esc_attr( $alt ) );
                }
                
                // Add size class like native image widget
                $size = ! empty( $settings['asi_image_size'] ) ? $settings['asi_image_size'] : 'large';
                $this->add_render_attribute( 'img', 'class', 'attachment-' . $size . ' size-' . $size );
            }

            // Add hover animation class
            if ( ! empty( $settings['hover_animation'] ) ) {
                $this->add_render_attribute( 'img', 'class', 'elementor-animation-' . $settings['hover_animation'] );
            }

            // Handle link
            $link = '';
            if ( 'custom' === $settings['link_to'] && ! empty( $settings['link']['url'] ) ) {
                $link = $settings['link']['url'];
                $this->add_render_attribute( 'link', 'href', $link );
                if ( ! empty( $settings['link']['is_external'] ) ) {
                    $this->add_render_attribute( 'link', 'target', '_blank' );
                }
                if ( ! empty( $settings['link']['nofollow'] ) ) {
                    $this->add_render_attribute( 'link', 'rel', 'nofollow' );
                }
            } elseif ( 'file' === $settings['link_to'] ) {
                $link = $settings['asi_image']['url'];
                $this->add_render_attribute( 'link', 'href', $link );
            }

            echo '<figure ' . $this->get_render_attribute_string( 'wrapper' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            
            if ( $link ) {
                echo '<a ' . $this->get_render_attribute_string( 'link' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            
            echo '<img ' . $this->get_render_attribute_string( 'img' ) . ' />'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            
            if ( $link ) {
                echo '</a>';
            }
            
            if ( ! empty( $settings['caption'] ) ) {
                echo '<figcaption>' . esc_html( $settings['caption'] ) . '</figcaption>';
            }
            echo '</figure>';
        }

        protected function content_template() {
            ?>
            <# if ( settings.asi_image && settings.asi_image.url ) { #>
                <figure class="asi-elementor-image">
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
