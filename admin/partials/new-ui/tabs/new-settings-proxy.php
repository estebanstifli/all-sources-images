<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * New Settings - Proxy Tab
 *
 * @since 6.2.0
 */

settings_errors();

$allsi_options = wp_parse_args( get_option( 'ALLSI_plugin_proxy_settings' ), $this->ALLSI_default_options_proxy_settings( FALSE ) );
$allsi_is_proxy_enabled = ( ! empty( $allsi_options['enable_proxy'] ) && $allsi_options['enable_proxy'] === 'enable' );
?>

<form method="post" action="options.php">
    <?php settings_fields( 'ASI-plugin-proxy-settings' ); ?>

    <!-- Info Alert -->
    <div class="allsi-alert allsi-alert-info">
        <div class="allsi-alert-icon">
            <span class="dashicons dashicons-info-outline"></span>
        </div>
        <div class="allsi-alert-content">
            <strong><?php esc_html_e( 'About Proxy Configuration', 'all-sources-images' ); ?></strong>
            <p><?php esc_html_e( 'This option allows you to use a custom proxy server for API requests. Useful if image source APIs are blocked in your region.', 'all-sources-images' ); ?></p>
            <p><strong><?php esc_html_e( 'Tip:', 'all-sources-images' ); ?></strong> <?php esc_html_e( 'Use the Interval feature in Bulk Generation settings to separate requests, instead of relying on a proxy.', 'all-sources-images' ); ?></p>
        </div>
    </div>

    <!-- Cloudflare Fallback Info -->
    <div class="allsi-alert allsi-alert-success">
        <div class="allsi-alert-icon">
            <span class="dashicons dashicons-cloud"></span>
        </div>
        <div class="allsi-alert-content">
            <strong><?php esc_html_e( 'Automatic Cloudflare Fallback', 'all-sources-images' ); ?></strong>
            <p><?php esc_html_e( 'For these sources, if you don\'t configure your own API key, the plugin automatically uses our Cloudflare proxy:', 'all-sources-images' ); ?></p>
            <ul class="allsi-inline-list">
                <li><span class="dashicons dashicons-yes"></span> Pixabay</li>
                <li><span class="dashicons dashicons-yes"></span> Pexels</li>
                <li><span class="dashicons dashicons-yes"></span> Unsplash</li>
                <li><span class="dashicons dashicons-yes"></span> Flickr</li>
                <li><span class="dashicons dashicons-yes"></span> GIPHY</li>
            </ul>
            <p class="description"><?php esc_html_e( 'For better performance, we recommend configuring your own API keys.', 'all-sources-images' ); ?></p>
        </div>
    </div>

    <div class="allsi-card">
        <h3 class="allsi-card-title">
            <span class="dashicons dashicons-shield"></span>
            <?php esc_html_e( 'Custom Proxy Settings', 'all-sources-images' ); ?>
        </h3>

        <table class="form-table">
            <tbody>
                <!-- Enable Proxy -->
                <tr>
                    <th scope="row">
                        <label for="enable_proxy"><?php esc_html_e( 'Enable Custom Proxy', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="allsi-switch">
                            <input type="checkbox" name="ALLSI_plugin_proxy_settings[enable_proxy]" id="enable_proxy" value="enable" <?php checked( $allsi_is_proxy_enabled ); ?> />
                            <span class="allsi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Enable a custom HTTPS proxy server for all API requests.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Proxy Settings (shown when enabled) -->
        <div id="allsi-proxy-settings" style="display: <?php echo esc_attr( $allsi_is_proxy_enabled ? 'block' : 'none' ); ?>;">
            <table class="form-table">
                <tbody>
                    <!-- Proxy Address -->
                    <tr>
                        <th scope="row">
                            <label for="proxy_address"><?php esc_html_e( 'Address', 'all-sources-images' ); ?></label>
                        </th>
                        <td>
                            <input type="text" class="regular-text" name="ALLSI_plugin_proxy_settings[proxy_address]" id="proxy_address" value="<?php echo esc_attr( isset( $allsi_options['proxy_address'] ) ? $allsi_options['proxy_address'] : '' ); ?>" placeholder="xxx.xxx.xxx.xx" />
                            <p class="description"><?php esc_html_e( 'Proxy server IP address or hostname.', 'all-sources-images' ); ?></p>
                        </td>
                    </tr>

                    <!-- Proxy Port -->
                    <tr>
                        <th scope="row">
                            <label for="proxy_port"><?php esc_html_e( 'Port', 'all-sources-images' ); ?></label>
                        </th>
                        <td>
                            <input type="number" class="small-text" name="ALLSI_plugin_proxy_settings[proxy_port]" id="proxy_port" value="<?php echo esc_attr( isset( $allsi_options['proxy_port'] ) ? $allsi_options['proxy_port'] : '80' ); ?>" placeholder="80" min="1" max="65535" />
                            <p class="description"><?php esc_html_e( 'Proxy server port (default: 80 for HTTP, 443 for HTTPS).', 'all-sources-images' ); ?></p>
                        </td>
                    </tr>

                    <!-- Proxy Username -->
                    <tr>
                        <th scope="row">
                            <label for="proxy_username"><?php esc_html_e( 'Username', 'all-sources-images' ); ?></label>
                        </th>
                        <td>
                            <input type="text" class="regular-text" name="ALLSI_plugin_proxy_settings[proxy_username]" id="proxy_username" value="<?php echo esc_attr( isset( $allsi_options['proxy_username'] ) ? $allsi_options['proxy_username'] : '' ); ?>" />
                            <p class="description"><?php esc_html_e( 'Username if authentication is required (optional).', 'all-sources-images' ); ?></p>
                        </td>
                    </tr>

                    <!-- Proxy Password -->
                    <tr>
                        <th scope="row">
                            <label for="proxy_password"><?php esc_html_e( 'Password', 'all-sources-images' ); ?></label>
                        </th>
                        <td>
                            <input type="password" class="regular-text" name="ALLSI_plugin_proxy_settings[proxy_password]" id="proxy_password" value="<?php echo esc_attr( isset( $allsi_options['proxy_password'] ) ? $allsi_options['proxy_password'] : '' ); ?>" />
                            <p class="description"><?php esc_html_e( 'Password if authentication is required (optional).', 'all-sources-images' ); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php submit_button(); ?>
</form>
