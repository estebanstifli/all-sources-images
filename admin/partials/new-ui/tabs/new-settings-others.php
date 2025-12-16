<?php
/**
 * New Settings - Others Tab
 * Translation settings + Logs enable/disable
 *
 * @since 6.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

settings_errors();

$options_block = wp_parse_args( get_option( 'ASI_plugin_block_settings' ), $this->ASI_default_options_block_settings( TRUE ) );
$options_logs = wp_parse_args( get_option( 'ASI_plugin_logs_settings' ), $this->ASI_default_options_logs_settings( TRUE ) );
$options_banks = wp_parse_args( get_option( 'ASI_plugin_banks_settings' ), $this->ASI_default_options_banks_settings( TRUE ) );

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
    __( 'English', 'all-sources-images' )               => 'en',
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

// Get WordPress language and find matching code in country_choose
$wp_lang = get_bloginfo( 'language' );
$wp_lang_code = substr( $wp_lang, 0, 2 );

// Find the best matching language code from the list
$wp_lang_matched = $wp_lang_code; // Default to 2-letter code
foreach ( $country_choose as $name => $code ) {
    // First try exact match with full locale (e.g., pt-BR)
    if ( str_replace( '-', '-', $wp_lang ) === $code ) {
        $wp_lang_matched = $code;
        break;
    }
    // Then try 2-letter code match
    if ( substr( $code, 0, 2 ) === $wp_lang_code && strlen( $code ) === 2 ) {
        $wp_lang_matched = $code;
    }
}

// Alt tag language - use saved value or default to WordPress language
if ( ! empty( $options_block['translate_alt_lang'] ) ) {
    $alt_lang = $options_block['translate_alt_lang'];
} else {
    $alt_lang = $wp_lang_matched;
}
?>

<!-- Translation Settings Form -->
<form method="post" action="options.php" class="asi-form-section">
    <?php settings_fields( 'ASI-plugin-block-settings' ); ?>

    <h3><?php esc_html_e( 'Translation', 'all-sources-images' ); ?></h3>
    <p class="description"><?php esc_html_e( 'Configure automatic translation for search keywords and image metadata.', 'all-sources-images' ); ?></p>

    <div class="alert alert-custom alert-default" role="alert">
        <div class="alert-icon"><span class="svg-icon svg-icon-primary svg-icon-xl"><svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <rect x="0" y="0" width="24" height="24"></rect>
                <path d="M12.2674799,18.2323597 L12.0084872,5.45852451 C12.0004303,5.06114792 12.1504154,4.6768183 12.4255037,4.38993949 L15.0030167,1.70195304 L17.5910752,4.40093695 C17.8599071,4.6812911 18.0095067,5.05499603 18.0## files changedo14,5.44170626 L17.9## files changed19512,18.2062508 C17.9## files changed35,19.0329966 17.2## files changed24,19.6507571 16.3## files changed819,19.5## files changedo32 L13.## files changed9,19.2069 C12.## files changed34,19.## files changed9998 12.2## files changed9,18.9## files changed997 12.## files changed75,18.## files changed324 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(14.## files changed09, 10.## files changed78) rotate(-135.000000) translate(-14.## files changed09, -10.## files changed78)"></path>
                <path d="M12,2 C6.47715,2 2,6.47715 2,12 C2,17.5228 6.47715,22 12,22 C17.5228,22 22,17.5228 22,12 C22,6.47715 17.5228,2 12,2 Z M12,4 C16.4183,4 20,7.58172 20,12 C20,16.4183 16.4183,20 12,20 C7.58172,20 4,16.4183 4,12 C4,7.58172 7.58172,4 12,4 Z" fill="#000000" fill-rule="nonzero"></path>
            </g>
        </svg></span>
        </div>
        <div class="alert-text">
            <strong><?php esc_html_e( 'How Translation Works', 'all-sources-images' ); ?></strong>
            <ul style="margin: 10px 0 0 20px; list-style: disc;">
                <li><strong><?php esc_html_e( 'Translate to English:', 'all-sources-images' ); ?></strong> <?php esc_html_e( 'Translates your search keywords to English before searching image banks. This helps get more results from international APIs.', 'all-sources-images' ); ?></li>
                <li><strong><?php esc_html_e( 'Translate ALT text:', 'all-sources-images' ); ?></strong> <?php esc_html_e( 'Translates the image ALT attribute from English to your selected language. Improves SEO for non-English sites.', 'all-sources-images' ); ?></li>
            </ul>
        </div>
    </div>

    <table class="form-table">
        <tbody>
            <!-- Translate to English -->
            <tr>
                <th scope="row">
                    <label for="translation_EN"><?php esc_html_e( 'Translate Keywords to English', 'all-sources-images' ); ?></label>
                </th>
                <td>
                    <label class="checkbox">
                        <input type="checkbox" name="ASI_plugin_block_settings[translation_EN]" id="translation_EN" value="true" <?php checked( ! empty( $options_block['translation_EN'] ) && $options_block['translation_EN'] == 'true' ); ?> />
                        <span></span>
                        <?php esc_html_e( 'Translate search keywords to English', 'all-sources-images' ); ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'Translates your search keywords to English before searching. Helps get more results from international APIs.', 'all-sources-images' ); ?></p>
                </td>
            </tr>

            <!-- Source Language for Translation -->
            <?php
            // Determine current source language setting
            $current_source_lang = ! empty( $options_block['source_lang'] ) ? $options_block['source_lang'] : '';
            
            // Find the display name for WordPress language
            $wp_lang_name = '';
            foreach ( $country_choose as $name => $code ) {
                if ( $code === $wp_lang_matched ) {
                    $wp_lang_name = $name;
                    break;
                }
            }
            if ( empty( $wp_lang_name ) ) {
                $wp_lang_name = $wp_lang;
            }
            ?>
            <tr class="asi-source-lang-row" <?php echo ( empty( $options_block['translation_EN'] ) || $options_block['translation_EN'] != 'true' ) ? 'style="display:none;"' : ''; ?>>
                <th scope="row">
                    <label for="source_lang"><?php esc_html_e( 'Source Language', 'all-sources-images' ); ?></label>
                </th>
                <td>
                    <select name="ASI_plugin_block_settings[source_lang]" id="source_lang" class="form-control" style="width: auto;">
                        <option value="" <?php selected( $current_source_lang, '' ); ?>>
                            <?php 
                            /* translators: %s: WordPress language name */
                            printf( esc_html__( 'Auto (WordPress: %s)', 'all-sources-images' ), esc_html( $wp_lang_name ) ); 
                            ?>
                        </option>
                        <?php foreach ( $country_choose as $country => $value ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_source_lang, $value ); ?>><?php echo esc_html( $country ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e( 'Select the language of your content. This is the language that will be translated to English for searching. By default, it uses your WordPress site language.', 'all-sources-images' ); ?></p>
                </td>
            </tr>

            <!-- Translate Alt Text -->
            <tr>
                <th scope="row">
                    <label for="translate_alt"><?php esc_html_e( 'Translate Alt Text', 'all-sources-images' ); ?></label>
                </th>
                <td>
                    <label class="checkbox">
                        <input type="checkbox" name="ASI_plugin_block_settings[translate_alt]" id="translate_alt" value="true" <?php checked( ! empty( $options_block['translate_alt'] ) && $options_block['translate_alt'] == 'true' ); ?> />
                        <span></span>
                        <?php esc_html_e( 'Translate alt text from English to:', 'all-sources-images' ); ?>
                    </label>
                    <select name="ASI_plugin_block_settings[translate_alt_lang]" class="form-control" style="width: auto; display: inline-block; margin-left: 10px;">
                        <?php foreach ( $country_choose as $country => $value ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $alt_lang, $value ); ?>><?php echo esc_html( $country ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e( 'Translates the image ALT attribute from English to your selected language. Improves SEO for non-English sites.', 'all-sources-images' ); ?></p>
                </td>
            </tr>

            <!-- Google Translate API Key (shown when either translation option is enabled) -->
            <tr class="asi-google-api-row" <?php echo ( empty( $options_block['translation_EN'] ) || $options_block['translation_EN'] != 'true' ) && ( empty( $options_block['translate_alt'] ) || $options_block['translate_alt'] != 'true' ) ? 'style="display:none;"' : ''; ?>>
                <th scope="row">
                    <label for="google_translate_apikey"><?php esc_html_e( 'Google Translate API Key', 'all-sources-images' ); ?></label>
                    <span class="description" style="font-weight: normal; font-size: 11px; display: block;"><?php esc_html_e( '(Optional)', 'all-sources-images' ); ?></span>
                </th>
                <td id="password-google-translate" class="password">
                    <input type="password" name="ASI_plugin_block_settings[google_translate_apikey]" id="google_translate_apikey" class="form-control" placeholder="<?php esc_attr_e( 'Leave empty to use free translation', 'all-sources-images' ); ?>" value="<?php echo ! empty( $options_block['google_translate_apikey'] ) ? esc_attr( $options_block['google_translate_apikey'] ) : ''; ?>" />
                    <i id="togglePassword"></i>
                    <p class="description" style="clear: both; padding-top: 10px;">
                        <?php esc_html_e( 'Optional: Provide your Google Cloud Translation API key for better quality and reliability. If left empty, the plugin will use the free Google Translate service.', 'all-sources-images' ); ?>
                        <br>
                        <?php esc_html_e( 'Get your API key from:', 'all-sources-images' ); ?> 
                        <a href="https://console.cloud.google.com/apis/library/translate.googleapis.com" target="_blank">Google Cloud Console</a>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>

    <?php submit_button( __( 'Save Translation Settings', 'all-sources-images' ), 'primary' ); ?>
</form>

<hr/>

<!-- Logs Settings Form -->
<form method="post" action="options.php" class="asi-form-section">
    <?php settings_fields( 'ASI-plugin-logs-settings' ); ?>

    <h3><?php esc_html_e( 'Logging', 'all-sources-images' ); ?></h3>
    <p class="description"><?php esc_html_e( 'Enable logging to track image generation activity and debug issues.', 'all-sources-images' ); ?></p>

    <table class="form-table">
        <tbody>
            <!-- Enable Logs -->
            <tr>
                <th scope="row">
                    <label for="enable_logs"><?php esc_html_e( 'Enable Logs', 'all-sources-images' ); ?></label>
                </th>
                <td>
                    <label class="checkbox">
                        <input type="checkbox" name="ASI_plugin_logs_settings[logs]" id="enable_logs" value="true" <?php checked( ! empty( $options_logs['logs'] ) && $options_logs['logs'] == 'true' ); ?> />
                        <span></span>
                        <?php esc_html_e( 'Enable logging', 'all-sources-images' ); ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'When enabled, the plugin will log image generation events for debugging.', 'all-sources-images' ); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <?php submit_button( __( 'Save Log Settings', 'all-sources-images' ), 'primary' ); ?>
</form>
