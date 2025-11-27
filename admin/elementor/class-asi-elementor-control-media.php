<?php
use Elementor\Control_Media;
use Elementor\Controls_Manager;

if ( ! class_exists( 'ASI_Elementor_Control_Media' ) ) {
    /**
     * Custom media control that prefers the All Sources Images modal.
     */
    class ASI_Elementor_Control_Media extends Control_Media {

        /**
         * Control type slug.
         */
        public function get_type() {
            return 'asi_media';
        }

        /**
         * Additional scripts for this control.
         */
        public function get_script_depends() {
            return array_merge( parent::get_script_depends(), array( 'asi-elementor-media-control' ) );
        }

        /**
         * Labels for this control.
         */
        protected function get_default_settings() {
            $defaults = parent::get_default_settings();
            $defaults['asi_button_text'] = __( 'Choose image', 'all-sources-images' );
            return $defaults;
        }

        /**
         * Custom template copied from core media control with minor tweaks.
         */
        public function content_template() {
            ?>
            <div class="elementor-control-field asi-media-control">
                <input type="hidden" data-setting="{{ data.name }}" />
                <label class="elementor-control-title">{{{ data.label }}}</label>
                <div class="asi-media-preview" style="display:none;"></div>
                <div class="asi-media-placeholder"><?php esc_html_e( 'No image selected', 'all-sources-images' ); ?></div>
                <div class="asi-media-actions">
                    <button type="button" class="elementor-button elementor-button-default asi-media-select">{{{ data.button_text || data.asi_button_text }}}</button>
                    <button type="button" class="elementor-button elementor-button-default asi-media-remove" style="display:none;"><?php esc_html_e( 'Remove', 'all-sources-images' ); ?></button>
                </div>
            </div>
            <?php
        }
    }
}
