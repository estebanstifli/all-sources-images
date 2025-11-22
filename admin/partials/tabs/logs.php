<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

?>
<div class="wrap">

		<?php settings_errors(); ?>

    <form method="post" action="options.php" id="tabs">

        <?php
            settings_fields( 'ASI-plugin-logs-settings' );
            $options = wp_parse_args( get_option( 'ASI_plugin_logs_settings' ), $this->ASI_default_options_logs_settings( TRUE ) );

            // Get the log file
            $current_file = $this->ASI_log_file( true );

            $logs_dir = ASI_ensure_logs_dir();
            if( false !== $current_file && false !== $logs_dir ) {
                $log_file     = $logs_dir . $current_file;
                $file_content = file_exists( $log_file ) ? file_get_contents( $log_file ) : esc_html__( 'No log yet', 'all-sources-images' );
            } else {
                $file_content = esc_html__( 'No log yet', 'all-sources-images' );
            }

        ?>

        <table id="general-options" class="form-table logs">
                <tbody>
                    <tr valign="top">
                            <th scope="row">
                                    <?php esc_html_e( 'Enable Logs', 'all-sources-images' ); ?>
                            </th>
                            <td>
                                    <input data-switch="true" type="checkbox" name="ASI_plugin_logs_settings[logs]" id="enable_logs" value="true" <?php echo( !empty( $options['logs']) && $options['logs'] == 'true' )? 'checked': ''; ?> />
                            </td>
                    </tr>

                    <tr valign="top" class="show_logs" <?php echo(($options['logs'] != 'true') ? 'style="display:none;"': ''); ?>>
                        <th scope="row">
                                <?php esc_html_e( 'Logs history', 'all-sources-images' ); ?>
                        </th>

                        <td>
                            <pre id="logs-block" class="text-white bg-dark"><?php echo $file_content; ?></pre>
                            <?php
                                if( false !== $current_file && false !== $logs_dir ) {
                                    $download_file_URL = esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'downloadlog' ), admin_url( 'admin.php?page=all-sources-images-admin-display&module=logs' ) ), 'download_log' ) );
                                    $delete_file       = esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'deletelog' ), admin_url( 'admin.php?page=all-sources-images-admin-display&module=logs' ) ), 'delete_log' ) );
                            ?>

                            <a href="<?php echo $download_file_URL; ?>" class="button button-primary"><?php esc_html_e( 'Download Log', 'all-sources-images' ); ?></a>

                            <a href="<?php echo $delete_file; ?>" class="delete-logs"><?php esc_html_e( 'Delete all logs', 'all-sources-images' ); ?></a>

                        </td>

                    </tr>

                    <tr valign="top">
                            <th scope="row">
                                <a href="#" class="show-settings"><?php esc_html_e( 'Show settings config', 'all-sources-images' ); ?></a>
                                <a href="#" class="hide-settings"><?php esc_html_e( 'Hide settings config', 'all-sources-images' ); ?></a><br/>
                                <a href="#" class="copy-settings"><?php esc_html_e( 'Copy settings config', 'all-sources-images' ); ?></a><br/>
                                <span class="copied"><?php esc_html_e( 'Copied !', 'all-sources-images' ); ?></span>
                            </th>
                            <td>

                            <?php } ?>

                        <div>

                            <?php
                                $main_settings 		    = array( get_option( 'ASI_plugin_main_settings' ) );
                                $banks_settings 		= array( get_option( 'ASI_plugin_banks_settings' ) );
                                $compatibility_settings	= array( get_option( 'ASI_plugin_compatibility_settings' ) );
                                $cron_settings			= array( get_option( 'ASI_plugin_cron_settings' ) );
                                $rights_settings		= array( get_option( 'ASI_plugin_rights_settings' ) );
                                $proxy_settings			= array( get_option( 'ASI_plugin_proxy_settings' ) );

                                $settings = json_encode(
                                    array_merge(
                                        $main_settings,
                                        $banks_settings,
                                        $compatibility_settings,
                                        $cron_settings,
                                        $rights_settings,
                                        $proxy_settings
                                    ) );
                            ?>
                            <pre id="logs-block" class="settings-logs"><?php echo esc_html($settings); ?></pre>
                        </div>
                            </td>
                    </tr>

                </tbody>
        </table>

        <?php submit_button(); ?>

    </form>

</div>
