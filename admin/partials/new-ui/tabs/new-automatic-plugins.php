<?php
/**
 * New Automatic - Plugins Tab
 * Compatibility with other plugins
 *
 * @since 6.2.0
 */

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

settings_errors();

$options = wp_parse_args( get_option( 'ASI_plugin_compatibility_settings' ), $this->ASI_default_options_compatibility_settings( TRUE ) );
?>

<form method="post" action="options.php">
    <?php settings_fields( 'ASI-plugin-compatibility-settings' ); ?>

    <!-- Info Alert -->
    <div class="asi-alert asi-alert-info">
        <div class="asi-alert-icon">
            <span class="dashicons dashicons-admin-plugins"></span>
        </div>
        <div class="asi-alert-content">
            <strong><?php esc_html_e( 'Plugin Integrations', 'all-sources-images' ); ?></strong>
            <p><?php esc_html_e( 'Enable compatibility with other plugins to automatically generate images when posts are created through them.', 'all-sources-images' ); ?></p>
        </div>
    </div>

    <!-- Content Importers -->
    <div class="asi-card">
        <h3 class="asi-card-title">
            <span class="dashicons dashicons-download"></span>
            <?php esc_html_e( 'Content Importers', 'all-sources-images' ); ?>
        </h3>
        <p class="description"><?php esc_html_e( 'Enable to generate images when posts are created via import plugins or REST API.', 'all-sources-images' ); ?></p>

        <table class="form-table">
            <tbody>
                <!-- REST API -->
                <tr>
                    <th scope="row">
                        <label for="enable_REST"><?php esc_html_e( 'REST API Requests', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_REST]" id="enable_REST" value="true" <?php checked( ! empty( $options['enable_REST'] ) && $options['enable_REST'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Enable for posts created via REST API (useful for AI content services).', 'all-sources-images' ); ?></p>
                    </td>
                </tr>

                <!-- WP All Import -->
                <tr>
                    <th scope="row">
                        <label for="enable_wpai"><?php esc_html_e( 'WP All Import', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_wpai]" id="enable_wpai" value="true" <?php checked( ! empty( $options['enable_wpai'] ) && $options['enable_wpai'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Enable compatibility with WP All Import plugin.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>

                <!-- WPeMatico -->
                <tr>
                    <th scope="row">
                        <label for="enable_wpematico"><?php esc_html_e( 'WPeMatico', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_wpematico]" id="enable_wpematico" value="true" <?php checked( ! empty( $options['enable_wpematico'] ) && $options['enable_wpematico'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Enable compatibility with WPeMatico RSS aggregator plugin.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>

                <!-- FeedWordPress -->
                <tr>
                    <th scope="row">
                        <label for="enable_feedwordpress"><?php esc_html_e( 'FeedWordPress', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_feedwordpress]" id="enable_feedwordpress" value="true" <?php checked( ! empty( $options['enable_feedwordpress'] ) && $options['enable_feedwordpress'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Enable compatibility with FeedWordPress plugin.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>

                <!-- WP Automatic -->
                <tr>
                    <th scope="row">
                        <label for="enable_wpautomatic"><?php esc_html_e( 'WP Automatic Plugin', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_wpautomatic]" id="enable_wpautomatic" value="true" <?php checked( ! empty( $options['enable_wpautomatic'] ) && $options['enable_wpautomatic'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Enable compatibility with WP Automatic Plugin.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <hr class="asi-separator" />

    <!-- Image Storage Plugins -->
    <div class="asi-card">
        <h3 class="asi-card-title">
            <span class="dashicons dashicons-format-image"></span>
            <?php esc_html_e( 'Image Storage & Custom Fields', 'all-sources-images' ); ?>
        </h3>
        <p class="description"><?php esc_html_e( 'Configure how images are stored or linked to custom fields.', 'all-sources-images' ); ?></p>

        <table class="form-table">
            <tbody>
                <!-- FIFU -->
                <tr>
                    <th scope="row">
                        <label for="enable_FIFU"><?php esc_html_e( 'Featured Image from URL (FIFU)', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_FIFU]" id="enable_FIFU" value="true" <?php checked( ! empty( $options['enable_FIFU'] ) && $options['enable_FIFU'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Use external URLs instead of uploading to media library.', 'all-sources-images' ); ?></p>
                        <p class="description asi-warning"><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Warning: Does not work with DALL-E & Stable Diffusion.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>

                <!-- CMB2 -->
                <tr>
                    <th scope="row">
                        <label for="enable_cmb2"><?php esc_html_e( 'CMB2', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_cmb2]" id="enable_cmb2" value="true" <?php checked( ! empty( $options['enable_cmb2'] ) && $options['enable_cmb2'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Set image to CMB2 custom field.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>

                <!-- ACF -->
                <tr>
                    <th scope="row">
                        <label for="enable_acf"><?php esc_html_e( 'Advanced Custom Fields (ACF)', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_acf]" id="enable_acf" value="true" <?php checked( ! empty( $options['enable_acf'] ) && $options['enable_acf'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Set image to ACF custom field.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>

                <!-- Meta Box -->
                <tr>
                    <th scope="row">
                        <label for="enable_metaboxio"><?php esc_html_e( 'Meta Box (metabox.io)', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_metaboxio]" id="enable_metaboxio" value="true" <?php checked( ! empty( $options['enable_metaboxio'] ) && $options['enable_metaboxio'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Set image to Meta Box custom field.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php submit_button(); ?>
</form>
