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

// Check which plugins are installed/active
if ( ! function_exists( 'is_plugin_active' ) ) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$plugins_status = array(
    'wpai'         => is_plugin_active( 'wp-all-import/wp-all-import.php' ) || is_plugin_active( 'wp-all-import-pro/wp-all-import-pro.php' ),
    'wpematico'    => is_plugin_active( 'wpematico/wpematico.php' ),
    'feedwordpress'=> is_plugin_active( 'feedwordpress/feedwordpress.php' ),
    'wpautomatic'  => is_plugin_active( 'developer-developer/developer-developer.php' ) || class_exists( 'wp_automatic' ),
    'fifu'         => is_plugin_active( 'featured-image-from-url/featured-image-from-url.php' ) || is_plugin_active( 'fifu-premium/fifu-premium.php' ),
    'cmb2'         => is_plugin_active( 'cmb2/init.php' ) || class_exists( 'CMB2' ),
    'acf'          => is_plugin_active( 'advanced-custom-fields/acf.php' ) || is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) || class_exists( 'ACF' ),
    'metaboxio'    => is_plugin_active( 'meta-box/meta-box.php' ) || class_exists( 'RWMB_Loader' ),
);

/**
 * Helper function to display plugin status badge
 */
function asi_plugin_status_badge( $is_active ) {
    if ( $is_active ) {
        return '<span class="asi-badge asi-badge-success" title="' . esc_attr__( 'Plugin detected and active', 'all-sources-images' ) . '"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__( 'Active', 'all-sources-images' ) . '</span>';
    } else {
        return '<span class="asi-badge asi-badge-warning" title="' . esc_attr__( 'Plugin not detected', 'all-sources-images' ) . '"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Not detected', 'all-sources-images' ) . '</span>';
    }
}
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
                        <?php echo asi_plugin_status_badge( $plugins_status['wpai'] ); ?>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_wpai]" id="enable_wpai" value="true" <?php checked( ! empty( $options['enable_wpai'] ) && $options['enable_wpai'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Automatically generate images when posts are imported via WP All Import.', 'all-sources-images' ); ?></p>
                        <?php if ( ! empty( $options['enable_wpai'] ) && $options['enable_wpai'] == 'true' && $plugins_status['wpai'] ) : ?>
                            <p class="description" style="color: #46b450; margin-top: 5px;">
                                <span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e( 'Integration active! Images will be generated automatically after import.', 'all-sources-images' ); ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- WPeMatico -->
                <tr>
                    <th scope="row">
                        <label for="enable_wpematico"><?php esc_html_e( 'WPeMatico', 'all-sources-images' ); ?></label>
                        <?php echo asi_plugin_status_badge( $plugins_status['wpematico'] ); ?>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_wpematico]" id="enable_wpematico" value="true" <?php checked( ! empty( $options['enable_wpematico'] ) && $options['enable_wpematico'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Automatically generate images when posts are fetched via WPeMatico RSS aggregator.', 'all-sources-images' ); ?></p>
                        <?php if ( ! empty( $options['enable_wpematico'] ) && $options['enable_wpematico'] == 'true' && $plugins_status['wpematico'] ) : ?>
                            <p class="description" style="color: #46b450; margin-top: 5px;">
                                <span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e( 'Integration active! Images will be generated automatically after fetch.', 'all-sources-images' ); ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- FeedWordPress -->
                <tr>
                    <th scope="row">
                        <label for="enable_feedwordpress"><?php esc_html_e( 'FeedWordPress', 'all-sources-images' ); ?></label>
                        <?php echo asi_plugin_status_badge( $plugins_status['feedwordpress'] ); ?>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_feedwordpress]" id="enable_feedwordpress" value="true" <?php checked( ! empty( $options['enable_feedwordpress'] ) && $options['enable_feedwordpress'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Automatically generate images when posts are syndicated via FeedWordPress.', 'all-sources-images' ); ?></p>
                        <?php if ( ! empty( $options['enable_feedwordpress'] ) && $options['enable_feedwordpress'] == 'true' && $plugins_status['feedwordpress'] ) : ?>
                            <p class="description" style="color: #46b450; margin-top: 5px;">
                                <span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e( 'Integration active! Images will be generated automatically after syndication.', 'all-sources-images' ); ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- WP Automatic -->
                <tr>
                    <th scope="row">
                        <label for="enable_wpautomatic"><?php esc_html_e( 'WP Automatic Plugin', 'all-sources-images' ); ?></label>
                        <?php echo asi_plugin_status_badge( $plugins_status['wpautomatic'] ); ?>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_wpautomatic]" id="enable_wpautomatic" value="true" <?php checked( ! empty( $options['enable_wpautomatic'] ) && $options['enable_wpautomatic'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Automatically generate images when posts are created via WP Automatic.', 'all-sources-images' ); ?></p>
                        <?php if ( ! empty( $options['enable_wpautomatic'] ) && $options['enable_wpautomatic'] == 'true' && $plugins_status['wpautomatic'] ) : ?>
                            <p class="description" style="color: #46b450; margin-top: 5px;">
                                <span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e( 'Integration active! Images will be generated automatically after post creation.', 'all-sources-images' ); ?>
                            </p>
                        <?php endif; ?>
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
                        <?php echo asi_plugin_status_badge( $plugins_status['fifu'] ); ?>
                        <span class="asi-badge asi-badge-success"><?php esc_html_e( 'Implemented', 'all-sources-images' ); ?></span>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_FIFU]" id="enable_FIFU" value="true" <?php checked( ! empty( $options['enable_FIFU'] ) && $options['enable_FIFU'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Use external URLs instead of uploading to media library.', 'all-sources-images' ); ?></p>
                        <p class="description asi-warning"><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Warning: Does not work with DALL-E & Stable Diffusion.', 'all-sources-images' ); ?></p>
                        <?php if ( ! empty( $options['enable_FIFU'] ) && $options['enable_FIFU'] == 'true' && $plugins_status['fifu'] ) : ?>
                            <p class="description" style="color: #46b450; margin-top: 5px;">
                                <span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e( 'Integration active! Images will be stored as external URLs.', 'all-sources-images' ); ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- CMB2 -->
                <tr>
                    <th scope="row">
                        <label for="enable_cmb2"><?php esc_html_e( 'CMB2', 'all-sources-images' ); ?></label>
                        <?php echo asi_plugin_status_badge( $plugins_status['cmb2'] ); ?>
                        <span class="asi-badge asi-badge-warning"><?php esc_html_e( 'Coming Soon', 'all-sources-images' ); ?></span>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_cmb2]" id="enable_cmb2" value="true" <?php checked( ! empty( $options['enable_cmb2'] ) && $options['enable_cmb2'] == 'true' ); ?> disabled />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Set image to CMB2 custom field.', 'all-sources-images' ); ?></p>
                        <p class="description" style="color: #666; font-style: italic;">
                            <span class="dashicons dashicons-info"></span>
                            <?php esc_html_e( 'This integration is not yet implemented. Coming in a future update.', 'all-sources-images' ); ?>
                        </p>
                    </td>
                </tr>

                <!-- ACF -->
                <tr>
                    <th scope="row">
                        <label for="enable_acf"><?php esc_html_e( 'Advanced Custom Fields (ACF)', 'all-sources-images' ); ?></label>
                        <?php echo asi_plugin_status_badge( $plugins_status['acf'] ); ?>
                        <span class="asi-badge asi-badge-warning"><?php esc_html_e( 'Coming Soon', 'all-sources-images' ); ?></span>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_acf]" id="enable_acf" value="true" <?php checked( ! empty( $options['enable_acf'] ) && $options['enable_acf'] == 'true' ); ?> disabled />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Set image to ACF custom field.', 'all-sources-images' ); ?></p>
                        <p class="description" style="color: #666; font-style: italic;">
                            <span class="dashicons dashicons-info"></span>
                            <?php esc_html_e( 'This integration is not yet implemented. Coming in a future update.', 'all-sources-images' ); ?>
                        </p>
                    </td>
                </tr>

                <!-- Meta Box -->
                <tr>
                    <th scope="row">
                        <label for="enable_metaboxio"><?php esc_html_e( 'Meta Box (metabox.io)', 'all-sources-images' ); ?></label>
                        <?php echo asi_plugin_status_badge( $plugins_status['metabox'] ); ?>
                        <span class="asi-badge asi-badge-warning"><?php esc_html_e( 'Coming Soon', 'all-sources-images' ); ?></span>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_compatibility_settings[enable_metaboxio]" id="enable_metaboxio" value="true" <?php checked( ! empty( $options['enable_metaboxio'] ) && $options['enable_metaboxio'] == 'true' ); ?> disabled />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Set image to Meta Box custom field.', 'all-sources-images' ); ?></p>
                        <p class="description" style="color: #666; font-style: italic;">
                            <span class="dashicons dashicons-info"></span>
                            <?php esc_html_e( 'This integration is not yet implemented. Coming in a future update.', 'all-sources-images' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php submit_button(); ?>
</form>
