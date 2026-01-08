<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$allsi_options = wp_parse_args(
    get_option( 'ALLSI_plugin_banks_settings' ),
    $this->ALLSI_default_options_banks_settings( true )
);
?>

<tr valign="top">
    <th scope="row">
        <label for="hseparator"><?php esc_html_e('Google Translate API Key (Optional)', 'all-sources-images'); ?></label>
    </th>
    <td>
        <input name="ALLSI_plugin_banks_settings[google_translate][apikey]" type="text" class="form-control form-control-lg" placeholder="<?php esc_html_e('Leave empty to use free translation', 'all-sources-images'); ?>" value="<?php echo ( ! empty( $allsi_options['google_translate']['apikey'] ) ? esc_attr( $allsi_options['google_translate']['apikey'] ) : '' ); ?>" />
        <p class="description">
            <?php esc_html_e('Optional: Provide your Google Cloud Translation API key for better quality and reliability. If left empty, the plugin will use the free Google Translate service.', 'all-sources-images'); ?>
            <br>
            <?php esc_html_e('Get your API key from:', 'all-sources-images'); ?> 
            <a href="https://console.cloud.google.com/apis/library/translate.googleapis.com" target="_blank">Google Cloud Console</a>
        </p>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="hseparator"><?php esc_html_e('Translation Features', 'all-sources-images'); ?></label>
    </th>
    <td>
        <div class="alert alert-info">
            <h4><?php esc_html_e('How Translation Works', 'all-sources-images'); ?></h4>
            <ul>
                <li><strong><?php esc_html_e('Translate to English:', 'all-sources-images'); ?></strong> <?php esc_html_e('Translates your search keywords to English before searching image banks. This helps get more results from international APIs.', 'all-sources-images'); ?></li>
                <li><strong><?php esc_html_e('Translate ALT text:', 'all-sources-images'); ?></strong> <?php esc_html_e('Translates the image ALT attribute from English to your selected language. Improves SEO for non-English sites.', 'all-sources-images'); ?></li>
            </ul>
            <p><?php esc_html_e('Enable these options in:', 'all-sources-images'); ?></p>
            <ul>
                <li><?php esc_html_e('Automatic → Places (Translate to English)', 'all-sources-images'); ?></li>
                <li><?php esc_html_e('Automatic → Alteration (Translate ALT text)', 'all-sources-images'); ?></li>
                <li><?php esc_html_e('Gutenberg Block Settings (Both options)', 'all-sources-images'); ?></li>
            </ul>
        </div>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="hseparator"><?php esc_html_e('Free vs API Translation', 'all-sources-images'); ?></label>
    </th>
    <td>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><?php esc_html_e('Feature', 'all-sources-images'); ?></th>
                    <th><?php esc_html_e('Free (No API Key)', 'all-sources-images'); ?></th>
                    <th><?php esc_html_e('API (With API Key)', 'all-sources-images'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php esc_html_e('Cost', 'all-sources-images'); ?></td>
                    <td><span class="badge badge-success"><?php esc_html_e('Free', 'all-sources-images'); ?></span></td>
                    <td><?php esc_html_e('$20 per million characters (500k free/month)', 'all-sources-images'); ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Reliability', 'all-sources-images'); ?></td>
                    <td><?php esc_html_e('May be blocked with heavy use', 'all-sources-images'); ?></td>
                    <td><span class="badge badge-success"><?php esc_html_e('High', 'all-sources-images'); ?></span></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Quality', 'all-sources-images'); ?></td>
                    <td><?php esc_html_e('Good', 'all-sources-images'); ?></td>
                    <td><span class="badge badge-success"><?php esc_html_e('Best', 'all-sources-images'); ?></span></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Setup Required', 'all-sources-images'); ?></td>
                    <td><span class="badge badge-success"><?php esc_html_e('None', 'all-sources-images'); ?></span></td>
                    <td><?php esc_html_e('API key configuration', 'all-sources-images'); ?></td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
