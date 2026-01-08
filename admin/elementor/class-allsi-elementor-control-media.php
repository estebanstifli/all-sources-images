<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Control_Media;
use Elementor\Controls_Manager;

if ( ! class_exists( 'ALLSI_Elementor_Control_Media' ) ) {
    /**
     * Custom media control that prefers the All Sources Images modal.
     */
    class ALLSI_Elementor_Control_Media extends Control_Media {

        /**
         * Control type slug.
         */
        public function get_type() {
            return 'ALLSI_media';
        }

        /**
         * Additional scripts for this control.
         */
        public function get_script_depends() {
            return array_merge( parent::get_script_depends(), array( 'allsi-elementor-media-control' ) );
        }

        /**
         * Labels for this control.
         */
        protected function get_default_settings() {
            $defaults = parent::get_default_settings();
            $defaults['ALLSI_button_text'] = __( 'Choose image', 'all-sources-images' );
            return $defaults;
        }

        /**
         * Custom template copied from core media control with minor tweaks.
         */
        public function content_template() {
            ?>
            <div class="elementor-control-field allsi-media-control">
                <input type="hidden" data-setting="{{ data.name }}" />
                <label class="elementor-control-title">{{{ data.label }}}</label>
                <div class="allsi-media-preview" style="display:none;"></div>
                <div class="allsi-media-placeholder"><?php esc_html_e( 'No image selected', 'all-sources-images' ); ?></div>
                <div class="allsi-media-actions">
                    <button type="button" class="elementor-button elementor-button-default allsi-media-select">{{{ data.button_text || data.ALLSI_button_text }}}</button>
                    <button type="button" class="elementor-button elementor-button-default allsi-media-remove" style="display:none;"><?php esc_html_e( 'Remove', 'all-sources-images' ); ?></button>
                </div>
            </div>
            <?php
        }
    }
}
