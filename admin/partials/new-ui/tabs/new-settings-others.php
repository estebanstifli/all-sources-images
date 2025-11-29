<?php
/**
 * New Settings - Others Tab
 * Block settings + Logs enable/disable
 *
 * @since 6.2.0
 */

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

settings_errors();

$options_block = wp_parse_args( get_option( 'ASI_plugin_block_settings' ), $this->ASI_default_options_block_settings( TRUE ) );
$options_logs = wp_parse_args( get_option( 'ASI_plugin_logs_settings' ), $this->ASI_default_options_logs_settings( TRUE ) );

// Alt tag language
if ( ! isset( $options_block['translate_alt_lang'] ) ) {
    $wp_lang  = get_bloginfo( 'language' );
    $alt_lang = substr( $wp_lang, 0, 2 );
} else {
    $alt_lang = $options_block['translate_alt_lang'];
}

// Language options for translation
$country_choose = array(
    __( 'Afrikaans', 'all-sources-images' )             => 'af',
    __( 'Albanian', 'all-sources-images' )              => 'sq',
    __( 'Arabic', 'all-sources-images' )                => 'ar',
    __( 'Bulgarian', 'all-sources-images' )             => 'bg',
    __( 'Catalan', 'all-sources-images' )               => 'ca',
    __( 'Chinese (Simplified)', 'all-sources-images' )  => 'zh-CN',
    __( 'Chinese (Traditional)', 'all-sources-images' ) => 'zh-TW',
    __( 'Croatian', 'all-sources-images' )              => 'hr',
    __( 'Czech', 'all-sources-images' )                 => 'cs',
    __( 'Danish', 'all-sources-images' )                => 'da',
    __( 'Dutch', 'all-sources-images' )                 => 'nl',
    __( 'Estonian', 'all-sources-images' )              => 'et',
    __( 'Finnish', 'all-sources-images' )               => 'fi',
    __( 'French', 'all-sources-images' )                => 'fr',
    __( 'German', 'all-sources-images' )                => 'de',
    __( 'Greek', 'all-sources-images' )                 => 'el',
    __( 'Hebrew', 'all-sources-images' )                => 'iw',
    __( 'Hindi', 'all-sources-images' )                 => 'hi',
    __( 'Hungarian', 'all-sources-images' )             => 'hu',
    __( 'Indonesian', 'all-sources-images' )            => 'id',
    __( 'Italian', 'all-sources-images' )               => 'it',
    __( 'Japanese', 'all-sources-images' )              => 'ja',
    __( 'Korean', 'all-sources-images' )                => 'ko',
    __( 'Latvian', 'all-sources-images' )               => 'lv',
    __( 'Lithuanian', 'all-sources-images' )            => 'lt',
    __( 'Norwegian', 'all-sources-images' )             => 'no',
    __( 'Polish', 'all-sources-images' )                => 'pl',
    __( 'Portuguese (Brazil)', 'all-sources-images' )   => 'pt-BR',
    __( 'Portuguese (Portugal)', 'all-sources-images' ) => 'pt-PT',
    __( 'Romanian', 'all-sources-images' )              => 'ro',
    __( 'Russian', 'all-sources-images' )               => 'ru',
    __( 'Serbian', 'all-sources-images' )               => 'sr',
    __( 'Slovak', 'all-sources-images' )                => 'sk',
    __( 'Slovenian', 'all-sources-images' )             => 'sl',
    __( 'Spanish', 'all-sources-images' )               => 'es',
    __( 'Swedish', 'all-sources-images' )               => 'sv',
    __( 'Thai', 'all-sources-images' )                  => 'th',
    __( 'Turkish', 'all-sources-images' )               => 'tr',
    __( 'Ukrainian', 'all-sources-images' )             => 'uk',
    __( 'Vietnamese', 'all-sources-images' )            => 'vi',
);
?>

<!-- Block Settings Form -->
<form method="post" action="options.php" class="asi-form-section">
    <?php settings_fields( 'ASI-plugin-block-settings' ); ?>

    <div class="asi-card">
        <h3 class="asi-card-title">
            <span class="dashicons dashicons-screenoptions"></span>
            <?php esc_html_e( 'Manual Search Settings', 'all-sources-images' ); ?>
        </h3>
        <p class="description"><?php esc_html_e( 'Configure options for manual image search in the editor.', 'all-sources-images' ); ?></p>

        <table class="form-table">
            <tbody>
                <!-- Enable Manual Search Button -->
                <tr>
                    <th scope="row">
                        <label for="enable_manual_search"><?php esc_html_e( 'Display Manual Search Button', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_block_settings[enable_manual_search]" id="enable_manual_search" value="true" <?php checked( ! empty( $options_block['enable_manual_search'] ) && $options_block['enable_manual_search'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Show the "Generate Manually" button in the featured image panel.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>

                <!-- Enable Alt Tag -->
                <tr>
                    <th scope="row">
                        <label for="enable_alt"><?php esc_html_e( 'Add Alt Tag on Images', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_block_settings[enable_alt]" id="enable_alt" value="enable" <?php checked( ! empty( $options_block['enable_alt'] ) && $options_block['enable_alt'] == 'enable' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'Automatically add alt text to downloaded images.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>

                <!-- Alt Translation -->
                <tr class="asi-alt-translation" <?php echo ( empty( $options_block['enable_alt'] ) || $options_block['enable_alt'] != 'enable' ) ? 'style="display:none;"' : ''; ?>>
                    <th scope="row">
                        <label for="translate_alt"><?php esc_html_e( 'Translate Alt Text', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="checkbox">
                            <input type="checkbox" name="ASI_plugin_block_settings[translate_alt]" id="translate_alt" value="true" <?php checked( ! empty( $options_block['translate_alt'] ) && $options_block['translate_alt'] == 'true' ); ?> />
                            <span></span>
                            <?php esc_html_e( 'Translate alt text from English to:', 'all-sources-images' ); ?>
                        </label>
                        <select name="ASI_plugin_block_settings[translate_alt_lang]" class="asi-select">
                            <?php foreach ( $country_choose as $country => $value ) : ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $alt_lang, $value ); ?>><?php echo esc_html( $country ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button( __( 'Save Block Settings', 'all-sources-images' ), 'primary' ); ?>
    </div>
</form>

<hr class="asi-separator" />

<!-- Logs Settings Form -->
<form method="post" action="options.php" class="asi-form-section">
    <?php settings_fields( 'ASI-plugin-logs-settings' ); ?>

    <div class="asi-card">
        <h3 class="asi-card-title">
            <span class="dashicons dashicons-text-page"></span>
            <?php esc_html_e( 'Logging', 'all-sources-images' ); ?>
        </h3>
        <p class="description"><?php esc_html_e( 'Enable logging to track image generation activity and debug issues.', 'all-sources-images' ); ?></p>

        <table class="form-table">
            <tbody>
                <!-- Enable Logs -->
                <tr>
                    <th scope="row">
                        <label for="enable_logs"><?php esc_html_e( 'Enable Logs', 'all-sources-images' ); ?></label>
                    </th>
                    <td>
                        <label class="asi-switch">
                            <input type="checkbox" name="ASI_plugin_logs_settings[logs]" id="enable_logs" value="true" <?php checked( ! empty( $options_logs['logs'] ) && $options_logs['logs'] == 'true' ); ?> />
                            <span class="asi-switch-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e( 'When enabled, the plugin will log image generation events for debugging.', 'all-sources-images' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button( __( 'Save Log Settings', 'all-sources-images' ), 'primary' ); ?>
    </div>
</form>

<script>
jQuery(document).ready(function($) {
    $('#enable_alt').on('change', function() {
        if ($(this).is(':checked')) {
            $('.asi-alt-translation').slideDown();
        } else {
            $('.asi-alt-translation').slideUp();
        }
    });
});
</script>
