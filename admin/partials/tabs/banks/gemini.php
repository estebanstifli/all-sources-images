<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$allsi_gemini_options = isset( $options['gemini'] ) ? $options['gemini'] : array();
$allsi_selected_model = isset( $allsi_gemini_options['model'] ) ? $allsi_gemini_options['model'] : 'gemini-2.5-flash-image';
$allsi_selected_ratio = isset( $allsi_gemini_options['aspect_ratio'] ) ? $allsi_gemini_options['aspect_ratio'] : '1:1';
$allsi_selected_size  = isset( $allsi_gemini_options['image_size'] ) ? $allsi_gemini_options['image_size'] : '';
?>

<tr valign="top">
    <td colspan="2" class="source-logo source-logo--text">
        <strong><?php esc_html_e( 'Gemini (Google AI)', 'all-sources-images' ); ?></strong>
    </td>
</tr>

<tr valign="top">
    <td colspan="2">
        <div class="update-nag">
            <?php esc_html_e( 'Provide a Google AI Studio API key with access to the Gemini Image models. Requests are billed directly by Google.', 'all-sources-images' ); ?>
        </div>
        <div class="update-nag">
            <?php
            /* translators: %s: Google AI Studio API key page URL. */
            printf( wp_kses_post( __( 'Need a key? Create one in <a href="%s" target="_blank" rel="noopener noreferrer">Google AI Studio</a>.', 'all-sources-images' ) ), esc_url( 'https://aistudio.google.com/app/apikey' ) );
            ?>
        </div>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="gemini-apikey"><?php esc_html_e( 'API Key', 'all-sources-images' ); ?></label>
    </th>
    <td id="password-gemini" class="password">
        <input id="gemini-apikey" type="password" name="ALLSI_plugin_banks_settings[gemini][apikey]" class="form-control" value="<?php echo isset( $allsi_gemini_options['apikey'] ) ? esc_attr( $allsi_gemini_options['apikey'] ) : ''; ?>" />
        <i id="togglePassword"></i>
    </td>
</tr>

<tr valign="top">
    <td colspan="2">
        <button type="button" class="btn btn-primary" id="btnGemini">
            <?php esc_html_e( 'API testing', 'all-sources-images' ); ?>
        </button>
        <span id="resultGemini"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../img/loader-mpt.gif' ); ?>" width="32" class="hidden"/></span>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="gemini-model"><?php esc_html_e( 'Model', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <select name="ALLSI_plugin_banks_settings[gemini][model]" id="gemini-model" class="form-control form-control-lg">
            <?php
            $allsi_models = apply_filters(
                'ALLSI_gemini_supported_models',
                array(
                    'gemini-2.5-flash-image'         => esc_html__( 'Gemini 2.5 Flash Image (Nano Banana)', 'all-sources-images' ),
                    'gemini-2.5-flash-preview-image' => esc_html__( 'Gemini 2.5 Flash Image Preview', 'all-sources-images' ),
                    'gemini-3-pro-image-preview'     => esc_html__( 'Gemini 3 Pro Image Preview (4K capable)', 'all-sources-images' ),
                )
            );
            foreach ( $allsi_models as $allsi_value => $allsi_label ) {
                printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $allsi_value ), esc_html( $allsi_label ), selected( $allsi_selected_model, $allsi_value, false ) );
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e( 'Only the image-ready Nano Banana models are listed here. If Google adds new versions you can extend the list with the ALLSI_gemini_supported_models filter.', 'all-sources-images' ); ?></p>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="gemini-aspect"><?php esc_html_e( 'Aspect ratio', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <select name="ALLSI_plugin_banks_settings[gemini][aspect_ratio]" id="gemini-aspect" class="form-control form-control-lg">
            <?php
            $allsi_ratios = array(
                '1:1'  => esc_html__( 'Square (1:1)', 'all-sources-images' ),
                '16:9' => esc_html__( 'Landscape (16:9)', 'all-sources-images' ),
                '3:2'  => esc_html__( 'Photo (3:2)', 'all-sources-images' ),
                '4:5'  => esc_html__( 'Portrait (4:5)', 'all-sources-images' ),
                '9:16' => esc_html__( 'Vertical (9:16)', 'all-sources-images' ),
            );
            foreach ( $allsi_ratios as $allsi_value => $allsi_label ) {
                printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $allsi_value ), esc_html( $allsi_label ), selected( $allsi_selected_ratio, $allsi_value, false ) );
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e( 'Optional. Leave square to let Gemini decide the best framing.', 'all-sources-images' ); ?></p>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="gemini-size"><?php esc_html_e( 'Image size', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <select name="ALLSI_plugin_banks_settings[gemini][image_size]" id="gemini-size" class="form-control form-control-lg">
            <?php
            $allsi_sizes = array(
                ''         => esc_html__( 'Auto (model default)', 'all-sources-images' ),
                '1024x1024'=> '1024 × 1024',
                '1152x896' => '1152 × 896',
                '896x1152' => '896 × 1152',
                '1536x1536'=> '1536 × 1536',
            );
            foreach ( $allsi_sizes as $allsi_value => $allsi_label ) {
                printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $allsi_value ), esc_html( $allsi_label ), selected( $allsi_selected_size, $allsi_value, false ) );
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e( 'Optional. Some models only support up to 1 MP. Leave blank to stick with Google defaults.', 'all-sources-images' ); ?></p>
    </td>
</tr>
