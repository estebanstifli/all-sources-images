<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$workers_options = isset( $options['workers_ai'] ) ? $options['workers_ai'] : array();
$account_id      = isset( $workers_options['account_id'] ) ? $workers_options['account_id'] : '';
$api_token       = isset( $workers_options['api_token'] ) ? $workers_options['api_token'] : '';
$selected_model  = isset( $workers_options['model'] ) ? $workers_options['model'] : '@cf/black-forest-labs/flux-1-schnell';
$selected_steps  = isset( $workers_options['steps'] ) ? (int) $workers_options['steps'] : 4;
$negative_prompt = isset( $workers_options['negative_prompt'] ) ? $workers_options['negative_prompt'] : '';
?>

<tr valign="top">
    <td colspan="2" class="source-logo source-logo--text">
        <strong><?php esc_html_e( 'Cloudflare Workers AI', 'all-sources-images' ); ?></strong>
    </td>
</tr>

<tr valign="top">
    <td colspan="2">
        <div class="update-nag">
            <?php printf( wp_kses_post( __( 'You need a Cloudflare account with Workers AI enabled. Create an API token with <strong>Workers AI:Read</strong> or higher and find your <strong>Account ID</strong> inside the <a href="%s" target="_blank" rel="noopener noreferrer">dashboard</a>.', 'all-sources-images' ) ), esc_url( 'https://dash.cloudflare.com/' ) ); ?>
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
        <input id="workers-account-id" type="text" name="ALLSI_plugin_banks_settings[workers_ai][account_id]" class="form-control" value="<?php echo esc_attr( $account_id ); ?>" />
        <p class="description"><?php esc_html_e( 'Found under the Workers AI overview in the Cloudflare dashboard.', 'all-sources-images' ); ?></p>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="workers-api-token"><?php esc_html_e( 'API token', 'all-sources-images' ); ?></label>
    </th>
    <td id="password-workers" class="password">
        <input id="workers-api-token" type="password" name="ALLSI_plugin_banks_settings[workers_ai][api_token]" class="form-control" value="<?php echo esc_attr( $api_token ); ?>" />
        <i id="togglePassword"></i>
        <p class="description"><?php esc_html_e( 'Use a token with at least Workers AI Read access. The token is stored encrypted in WordPress options.', 'all-sources-images' ); ?></p>
    </td>
</tr>

<tr valign="top">
    <td colspan="2">
        <button class="btn btn-primary" id="btnWorkersAI" onclick="return false;">
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
            $models = array(
                '@cf/black-forest-labs/flux-1-schnell'        => esc_html__( 'FLUX.1 [schnell] (fast)', 'all-sources-images' ),
                '@cf/black-forest-labs/flux-1-dev'            => esc_html__( 'FLUX.1 [dev] (quality)', 'all-sources-images' ),
                '@cf/stabilityai/stable-diffusion-xl-base-1.0' => esc_html__( 'Stable Diffusion XL Base 1.0', 'all-sources-images' ),
            );
            foreach ( $models as $value => $label ) {
                printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $value ), esc_html( $label ), selected( $selected_model, $value, false ) );
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
        <input id="workers-steps" type="number" min="1" max="8" name="ALLSI_plugin_banks_settings[workers_ai][steps]" class="form-control" value="<?php echo esc_attr( $selected_steps ); ?>" />
        <p class="description"><?php esc_html_e( 'Workers AI caps most text-to-image models at 8 steps. Higher values mean more cost when supported.', 'all-sources-images' ); ?></p>
    </td>
</tr>

<tr valign="top">
    <th scope="row">
        <label for="workers-negative"><?php esc_html_e( 'Negative prompt (optional)', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <textarea id="workers-negative" name="ALLSI_plugin_banks_settings[workers_ai][negative_prompt]" class="form-control" rows="3"><?php echo esc_textarea( $negative_prompt ); ?></textarea>
        <p class="description"><?php esc_html_e( 'Helps the model avoid unwanted traits (for example: blurry, low quality, extra limbs).', 'all-sources-images' ); ?></p>
    </td>
</tr>
