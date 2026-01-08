<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$allsi_workers_options = isset( $options['workers_ai'] ) ? $options['workers_ai'] : array();
$allsi_account_id      = isset( $allsi_workers_options['account_id'] ) ? $allsi_workers_options['account_id'] : '';
$allsi_api_token       = isset( $allsi_workers_options['api_token'] ) ? $allsi_workers_options['api_token'] : '';
$allsi_selected_model  = isset( $allsi_workers_options['model'] ) ? $allsi_workers_options['model'] : '@cf/black-forest-labs/flux-1-schnell';
$allsi_selected_steps  = isset( $allsi_workers_options['steps'] ) ? (int) $allsi_workers_options['steps'] : 4;
$allsi_negative_prompt = isset( $allsi_workers_options['negative_prompt'] ) ? $allsi_workers_options['negative_prompt'] : '';
?>

<tr valign="top">
    <td colspan="2" class="source-logo source-logo--text">
        <strong><?php esc_html_e( 'Cloudflare Workers AI', 'all-sources-images' ); ?></strong>
    </td>
</tr>

<tr valign="top">
    <td colspan="2">
        <div class="update-nag">
            <?php
            /* translators: %s: Cloudflare dashboard URL. */
            printf( wp_kses_post( __( 'You need a Cloudflare account with Workers AI enabled. Create an API token with <strong>Workers AI:Read</strong> or higher and find your <strong>Account ID</strong> inside the <a href="%s" target="_blank" rel="noopener noreferrer">dashboard</a>.', 'all-sources-images' ) ), esc_url( 'https://dash.cloudflare.com/' ) );
            ?>
        </div>
        <div class="update-nag">
            <?php esc_html_e( 'Each request is billed by Cloudflare (roughly $0.00011 per step for FLUX). The plugin never proxies your credentials.', 'all-sources-images' ); ?>
        </div>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="workers-account-id"><?php esc_html_e( 'Account ID', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <input id="workers-account-id" type="text" name="ALLSI_plugin_banks_settings[workers_ai][account_id]" class="form-control" value="<?php echo esc_attr( $allsi_account_id ); ?>" />
        <p class="description"><?php esc_html_e( 'Found under the Workers AI overview in the Cloudflare dashboard.', 'all-sources-images' ); ?></p>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="workers-api-token"><?php esc_html_e( 'API token', 'all-sources-images' ); ?></label>
    </th>
    <td id="password-workers" class="password">
        <input id="workers-api-token" type="password" name="ALLSI_plugin_banks_settings[workers_ai][api_token]" class="form-control" value="<?php echo esc_attr( $allsi_api_token ); ?>" />
        <i id="togglePassword"></i>
        <p class="description"><?php esc_html_e( 'Use a token with at least Workers AI Read access. The token is stored encrypted in WordPress options.', 'all-sources-images' ); ?></p>
    </td>
</tr>

<tr valign="top">
    <td colspan="2">
        <button type="button" class="btn btn-primary" id="btnWorkersAI">
            <?php esc_html_e( 'API testing', 'all-sources-images' ); ?>
        </button>
        <span id="resultWorkersAI"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../img/loader-mpt.gif' ); ?>" width="32" class="hidden"/></span>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="workers-model"><?php esc_html_e( 'Model', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <select id="workers-model" name="ALLSI_plugin_banks_settings[workers_ai][model]" class="form-control form-control-lg">
            <?php
            $allsi_models = array(
                '@cf/black-forest-labs/flux-1-schnell'        => esc_html__( 'FLUX.1 [schnell] (fast)', 'all-sources-images' ),
                '@cf/black-forest-labs/flux-1-dev'            => esc_html__( 'FLUX.1 [dev] (quality)', 'all-sources-images' ),
                '@cf/stabilityai/stable-diffusion-xl-base-1.0' => esc_html__( 'Stable Diffusion XL Base 1.0', 'all-sources-images' ),
            );
            foreach ( $allsi_models as $allsi_value => $allsi_label ) {
                printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $allsi_value ), esc_html( $allsi_label ), selected( $allsi_selected_model, $allsi_value, false ) );
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e( 'You can paste any Workers AI model slug if you have access. The defaults focus on text-to-image endpoints.', 'all-sources-images' ); ?></p>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="workers-steps"><?php esc_html_e( 'Steps', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <input id="workers-steps" type="number" min="1" max="8" name="ALLSI_plugin_banks_settings[workers_ai][steps]" class="form-control" value="<?php echo esc_attr( $allsi_selected_steps ); ?>" />
        <p class="description"><?php esc_html_e( 'Workers AI caps most text-to-image models at 8 steps. Higher values mean more cost when supported.', 'all-sources-images' ); ?></p>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="workers-negative"><?php esc_html_e( 'Negative prompt (optional)', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <textarea id="workers-negative" name="ALLSI_plugin_banks_settings[workers_ai][negative_prompt]" class="form-control" rows="3"><?php echo esc_textarea( $allsi_negative_prompt ); ?></textarea>
        <p class="description"><?php esc_html_e( 'Helps the model avoid unwanted traits (for example: blurry, low quality, extra limbs).', 'all-sources-images' ); ?></p>
    </td>
</tr>
