<?php

if ( !function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
?>
<div class="wrap">

        <?php 
settings_errors();
?>

        <form method="post" action="options.php" id="tabs">

        <?php 
settings_fields( 'ASI-plugin-proxy-settings' );
$options = wp_parse_args( get_option( 'ASI_plugin_proxy_settings' ), $this->ASI_default_options_proxy_settings( FALSE ) );
?>

		<!-- Info Alert -->
		<div class="alert alert-custom alert-light-info fade show mb-10" role="alert">
			<div class="alert-icon">
				<i class="flaticon-warning"></i>
			</div>
			<div class="alert-text">
				<strong><?php esc_html_e( 'About Proxy Configuration', 'all-sources-images' ); ?></strong>
				<p><?php esc_html_e( 'The Proxy feature was introduced with v3 of the plugin, before the Interval feature.', 'all-sources-images' ); ?></p>
				<p><?php esc_html_e( 'This option can be useful if, for example, there are a lot of requests to Google Images in a very short period of time.', 'all-sources-images' ); ?></p>
				<p><strong><?php esc_html_e( 'Recommendation:', 'all-sources-images' ); ?></strong> <?php esc_html_e( 'It is now recommended that you use the Interval feature (in Bulk Generation settings), which allows you to separate each generation by a few seconds, instead of using a proxy.', 'all-sources-images' ); ?></p>
				<p><?php esc_html_e( 'If you have a free or paid proxy, you can configure it in this section. You must use a proxy with the HTTPS protocol, otherwise, it will not work.', 'all-sources-images' ); ?></p>
				<p><strong><?php esc_html_e( 'Important:', 'all-sources-images' ); ?></strong> <?php esc_html_e( 'You should also check with your hosting provider. Some rules may block requests from these proxies.', 'all-sources-images' ); ?></p>
			</div>
		</div>

		<table id="general-options" class="form-table tabs-content">
			<tbody>
				<!-- Enable Proxy -->
				<tr>
					<td style="width: 20%;">
						<label for="enable_proxy">
							<?php esc_html_e( 'Enable Proxy', 'all-sources-images' ); ?>
						</label>
					</td>
					<td style="width: 15%;">
						<label class="checkbox">
							<input data-switch="true" type="checkbox" name="ASI_plugin_proxy_settings[enable_proxy]" id="enable_proxy" value="enable" <?php echo( !empty( $options['enable_proxy']) && $options['enable_proxy'] == 'enable' )? 'checked': ''; ?> />
							<span></span>
						</label>
					</td>
					<td style="width: 65%;">
						<p class="description">
							<?php esc_html_e( 'Enable proxy server for all API requests. Useful if image banks APIs are blocked in your region.', 'all-sources-images' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>

		<!-- Proxy Settings (shown when enabled) -->
		<div id="proxy-settings-container" style="display: <?php echo( !empty( $options['enable_proxy']) && $options['enable_proxy'] == 'enable' ) ? 'block' : 'none'; ?>;">
			<table class="form-table tabs-content">
				<tbody>
					<!-- Proxy Address -->
					<tr>
						<td style="width: 20%;">
							<label for="proxy_address">
								<?php esc_html_e( 'Address', 'all-sources-images' ); ?>
							</label>
						</td>
						<td style="width: 80%;" colspan="2">
							<input type="text" class="regular-text" name="ASI_plugin_proxy_settings[proxy_address]" id="proxy_address" value="<?php echo esc_attr( isset($options['proxy_address']) ? $options['proxy_address'] : '' ); ?>" placeholder="xxx.xxx.xxx.xx" />
							<p class="description">
								<?php esc_html_e( 'Proxy server IP address or hostname.', 'all-sources-images' ); ?>
							</p>
						</td>
					</tr>

					<!-- Proxy Port -->
					<tr>
						<td style="width: 20%;">
							<label for="proxy_port">
								<?php esc_html_e( 'Port', 'all-sources-images' ); ?>
							</label>
						</td>
						<td style="width: 80%;" colspan="2">
							<input type="number" class="regular-text" name="ASI_plugin_proxy_settings[proxy_port]" id="proxy_port" value="<?php echo esc_attr( isset($options['proxy_port']) ? $options['proxy_port'] : '80' ); ?>" placeholder="80" min="1" max="65535" />
							<p class="description">
								<?php esc_html_e( 'Proxy server port (default: 80 for HTTP, 443 for HTTPS).', 'all-sources-images' ); ?>
							</p>
						</td>
					</tr>

					<!-- Proxy Username -->
					<tr>
						<td style="width: 20%;">
							<label for="proxy_username">
								<?php esc_html_e( 'Username', 'all-sources-images' ); ?>
							</label>
						</td>
						<td style="width: 80%;" colspan="2">
							<input type="text" class="regular-text" name="ASI_plugin_proxy_settings[proxy_username]" id="proxy_username" value="<?php echo esc_attr( isset($options['proxy_username']) ? $options['proxy_username'] : '' ); ?>" />
							<p class="description">
								<?php esc_html_e( 'Username if it requires authentication.', 'all-sources-images' ); ?>
							</p>
						</td>
					</tr>

					<!-- Proxy Password -->
					<tr>
						<td style="width: 20%;">
							<label for="proxy_password">
								<?php esc_html_e( 'Password', 'all-sources-images' ); ?>
							</label>
						</td>
						<td style="width: 80%;" colspan="2">
							<input type="password" class="regular-text" name="ASI_plugin_proxy_settings[proxy_password]" id="proxy_password" value="<?php echo esc_attr( isset($options['proxy_password']) ? $options['proxy_password'] : '' ); ?>" />
							<p class="description">
								<?php esc_html_e( 'Password if it requires authentication.', 'all-sources-images' ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<?php submit_button(); ?>

        </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	// Show/hide proxy settings based on enable checkbox
	$('#enable_proxy').on('change', function() {
		if ($(this).is(':checked')) {
			$('#proxy-settings-container').slideDown();
		} else {
			$('#proxy-settings-container').slideUp();
		}
	});
});
</script>
