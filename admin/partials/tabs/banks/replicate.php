<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Replicate Logo" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/replicate.png' ); ?>"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php echo wp_kses_post( __( '<b>It\'s required</b> to provide your own <b>API token</b>. You can create an account and get your token at <a target="_blank" href="https://replicate.com/account/api-tokens">Replicate API Tokens</a>. New accounts receive free credit.', 'all-sources-images' ) ); ?>
		</div>
		<div class="update-nag">
			<?php echo wp_kses_post( __( 'Replicate offers access to thousands of AI models including FLUX, SDXL, and custom community models. Pricing varies by model and compute time. Check <a target="_blank" href="https://replicate.com/pricing">pricing details</a>.', 'all-sources-images' ) ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'API Token', 'all-sources-images' ); ?></label>
	</th>
	<td id="password-replicate" class="password">
		<input type="password" name="ALLSI_plugin_banks_settings[replicate][apitoken]" class="form-control" value="<?php echo( isset( $options['replicate']['apitoken'] ) && !empty( $options['replicate']['apitoken']) )? esc_attr( $options['replicate']['apitoken'] ) : ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>

<tr valign="top">
	<td colspan="2">
		<button type="button" class="btn btn-primary" id="btnReplicate">
			<?php esc_html_e( 'API testing', 'all-sources-images' ); ?>
		</button>
		<span id="resultReplicate"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../img/loader-mpt.gif' ); ?>" width="32" class="hidden"/></span>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Model', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[replicate][model]" class="form-control form-control-lg" >
			<?php
			$allsi_selected_model = isset( $options['replicate']['model'] ) ? $options['replicate']['model'] : 'black-forest-labs/flux-schnell';

			$allsi_models = array(
				// FLUX Models (Black Forest Labs)
				esc_html__( 'FLUX.1 [schnell] - Fast, high-quality', 'all-sources-images' )                => 'black-forest-labs/flux-schnell',
				esc_html__( 'FLUX.1 [dev] - Development version', 'all-sources-images' )                   => 'black-forest-labs/flux-dev',
				esc_html__( 'FLUX.1 [pro] - Professional quality', 'all-sources-images' )                  => 'black-forest-labs/flux-pro',
				esc_html__( 'FLUX.1.1 [pro] - Latest professional', 'all-sources-images' )                 => 'black-forest-labs/flux-1.1-pro',
				
				// Stable Diffusion Models (Stability AI)
				esc_html__( 'SDXL - Stable Diffusion XL', 'all-sources-images' )                           => 'stability-ai/sdxl',
				esc_html__( 'SD 3 - Stable Diffusion 3', 'all-sources-images' )                            => 'stability-ai/stable-diffusion-3',
				esc_html__( 'SD 3.5 Large - Latest SD model', 'all-sources-images' )                       => 'stability-ai/stable-diffusion-3-5-large',
				
				// Playground AI
				esc_html__( 'Playground v2.5 - Aesthetic images', 'all-sources-images' )                   => 'playgroundai/playground-v2.5-1024px-aesthetic',
				
				// Midjourney-style
				esc_html__( 'Dreamshaper XL - Artistic style', 'all-sources-images' )                      => 'lucataco/dreamshaper-xl-lightning',
				
				// Realistic
				esc_html__( 'RealVisXL v4.0 - Photorealistic', 'all-sources-images' )                     => 'lucataco/realvisxl-v4.0',
				
				// Anime/Illustration
				esc_html__( 'Animagine XL 3.1 - Anime style', 'all-sources-images' )                       => 'cjwbw/animagine-xl-3.1',
				
				// ControlNet
				esc_html__( 'SDXL ControlNet - Guided generation', 'all-sources-images' )                  => 'lucataco/sdxl-controlnet',
				
				// Upscaling
				esc_html__( 'Real-ESRGAN - Image upscaling', 'all-sources-images' )                        => 'nightmareai/real-esrgan',
				esc_html__( 'GFPGAN - Face restoration', 'all-sources-images' )                            => 'tencentarc/gfpgan',
				
				// Video to Image
				esc_html__( 'DynamiCrafter - Video interpolation', 'all-sources-images' )                  => 'cjwbw/dynamicrafter',
				
				// Custom (user can specify full model path)
				esc_html__( 'Custom Model (specify in version field)', 'all-sources-images' )              => 'custom',
			);

			foreach ( $allsi_models as $allsi_name_model => $allsi_code_model ) {
				echo '<option ' . selected( $allsi_selected_model, $allsi_code_model, false ) . ' value="' . esc_attr( $allsi_code_model ) . '">' . esc_html( $allsi_name_model ) . '</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Select from popular AI image models. FLUX models offer best quality, SDXL is versatile, specialized models for specific styles.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Model Version (Optional)', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<input type="text" name="ALLSI_plugin_banks_settings[replicate][version]" class="form-control" value="<?php echo( isset( $options['replicate']['version'] ) && !empty( $options['replicate']['version']) )? esc_attr( $options['replicate']['version'] ) : ''; ?>" placeholder="Leave empty for latest version">
		<p class="description"><?php esc_html_e( 'Specify a 64-character version ID for exact model version, or leave empty to use latest. Format: owner/model:version_id or just version_id', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Prediction Timeout', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[replicate][timeout]" class="form-control form-control-lg" >
			<?php
			$allsi_selected_timeout = isset( $options['replicate']['timeout'] ) ? $options['replicate']['timeout'] : '60';

			$allsi_timeouts = array(
				esc_html__( '30 seconds', 'all-sources-images' )  => '30',
				esc_html__( '60 seconds', 'all-sources-images' )  => '60',
				esc_html__( '90 seconds', 'all-sources-images' )  => '90',
				esc_html__( '120 seconds', 'all-sources-images' ) => '120',
				esc_html__( '180 seconds', 'all-sources-images' ) => '180',
				esc_html__( '300 seconds', 'all-sources-images' ) => '300',
			);

			foreach ( $allsi_timeouts as $allsi_name_timeout => $allsi_code_timeout ) {
				echo '<option ' . selected( $allsi_selected_timeout, $allsi_code_timeout, false ) . ' value="' . esc_attr( $allsi_code_timeout ) . '">' . esc_html( $allsi_name_timeout ) . '</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Maximum time to wait for image generation. Complex models may need longer timeouts. Default is 60 seconds.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Polling Interval', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[replicate][polling_interval]" class="form-control form-control-lg" >
			<?php
			$allsi_selected_interval = isset( $options['replicate']['polling_interval'] ) ? $options['replicate']['polling_interval'] : '2';

			$allsi_intervals = array(
				esc_html__( '1 second', 'all-sources-images' )  => '1',
				esc_html__( '2 seconds', 'all-sources-images' ) => '2',
				esc_html__( '3 seconds', 'all-sources-images' ) => '3',
				esc_html__( '5 seconds', 'all-sources-images' ) => '5',
				esc_html__( '10 seconds', 'all-sources-images' )=> '10',
			);

			foreach ( $allsi_intervals as $allsi_name_interval => $allsi_code_interval ) {
				echo '<option ' . selected( $allsi_selected_interval, $allsi_code_interval, false ) . ' value="' . esc_attr( $allsi_code_interval ) . '">' . esc_html( $allsi_name_interval ) . '</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'How often to check prediction status while waiting. Lower values get results faster but use more API calls.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Webhook URL (Optional)', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<input type="url" name="ALLSI_plugin_banks_settings[replicate][webhook_url]" class="form-control" value="<?php echo( isset( $options['replicate']['webhook_url'] ) && !empty( $options['replicate']['webhook_url']) )? esc_attr( $options['replicate']['webhook_url'] ) : ''; ?>" placeholder="https://yoursite.com/webhook">
		<p class="description"><?php esc_html_e( 'HTTPS URL to receive webhook notifications when prediction completes. Leave empty to use polling instead.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image Size', 'all-sources-images' ); ?></label>
	</th>
	<td class="checkbox-list">
		<select name="ALLSI_plugin_banks_settings[replicate][image_size]" class="form-control form-control-lg" >
			<?php
			$allsi_selected_image_size = isset( $options['replicate']['image_size'] ) ? $options['replicate']['image_size'] : '1024x1024';

			$allsi_sizes = array(
				esc_html__( '512x512', 'all-sources-images' )   => '512x512',
				esc_html__( '768x768', 'all-sources-images' )   => '768x768',
				esc_html__( '1024x1024', 'all-sources-images' ) => '1024x1024',
				esc_html__( '1280x720', 'all-sources-images' )  => '1280x720',
				esc_html__( '1920x1080', 'all-sources-images' ) => '1920x1080',
			);

			foreach ( $allsi_sizes as $allsi_name_size => $allsi_code_size ) {
				echo '<option ' . selected( $allsi_selected_image_size, $allsi_code_size, false ) . ' value="' . esc_attr( $allsi_code_size ) . '">' . esc_html( $allsi_name_size ) . '</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Default image dimensions. Note: Not all models support all sizes. Check model documentation.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Guidance Scale', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<input type="number" name="ALLSI_plugin_banks_settings[replicate][guidance_scale]" class="form-control" min="1" max="20" step="0.5" value="<?php echo( isset( $options['replicate']['guidance_scale'] ) && !empty( $options['replicate']['guidance_scale']) )? esc_attr( $options['replicate']['guidance_scale'] ) : '7.5'; ?>" >
		<p class="description"><?php esc_html_e( 'How closely to follow the prompt. Higher values (10-15) follow prompt strictly, lower values (5-7) allow more creativity. Default: 7.5', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Number of Outputs', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[replicate][num_outputs]" class="form-control form-control-lg" >
			<?php
			$allsi_selected_num_outputs = isset( $options['replicate']['num_outputs'] ) ? $options['replicate']['num_outputs'] : '1';

			$allsi_outputs = array(
				esc_html__( '1 image', 'all-sources-images' ) => '1',
				esc_html__( '2 images', 'all-sources-images' )=> '2',
				esc_html__( '4 images', 'all-sources-images' )=> '4',
			);

			foreach ( $allsi_outputs as $allsi_name_output => $allsi_code_output ) {
				echo '<option ' . selected( $allsi_selected_num_outputs, $allsi_code_output, false ) . ' value="' . esc_attr( $allsi_code_output ) . '">' . esc_html( $allsi_name_output ) . '</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Generate multiple variations. Plugin will use the first image. More outputs = higher cost.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Advanced Options', 'all-sources-images' ); ?></label>
	</th>
	<td class="checkbox-list">
		<label class="checkbox ">
			<input name="ALLSI_plugin_banks_settings[replicate][use_negative_prompt]" type="checkbox" value="true" <?php checked( ! empty( $options['replicate']['use_negative_prompt'] ) && $options['replicate']['use_negative_prompt'] == 'true' ); ?>><span></span>
			<?php esc_html_e( 'Use default negative prompt', 'all-sources-images' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'Adds common exclusions: "blurry, low quality, distorted, disfigured, ugly, low resolution, watermark"', 'all-sources-images' ); ?></p>
	</td>
</tr>
