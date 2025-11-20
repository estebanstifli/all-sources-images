<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Stable Diffusion Logo" src="<?php echo plugin_dir_url( __FILE__ ); ?>/img/stability.png"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php _e('<b>It\'s required</b> to provide your own <b>API key</b>. You can register at Stability AI platform <a target="_blank" href="https://platform.stability.ai/account/keys">here</a> and get your API key. New accounts receive 25 free credits.', 'all-sources-images' ); ?>
		</div>
		<div class="update-nag">
			<?php _e('Stable Diffusion API uses credits: $1 USD = 100 credits. Image generation costs vary by model and resolution. Check <a target="_blank" href="https://platform.stability.ai/pricing">pricing details</a>.', 'all-sources-images' ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'API Key', 'all-sources-images' ); ?></label>
	</th>
	<td id="password-stability" class="password">
		<input type="password" name="ASI_plugin_banks_settings[stability][apikey]" class="form-control" value="<?php echo( isset( $options['stability']['apikey'] ) && !empty( $options['stability']['apikey']) )? esc_attr( $options['stability']['apikey'] ) : ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>

<tr valign="top">
	<td colspan="2">
		<button class="btn btn-primary" id="btnStability" onclick="return false;">
			<?php esc_html_e( 'API testing', 'all-sources-images' ); ?>
		</button>
		<span id="resultStability"><img src="<?php echo plugin_dir_url( __FILE__ ); ?>../../../img/loader-mpt.gif" width="32" class="hidden"/></span>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Model', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[stability][model]" class="form-control form-control-lg" >
			<?php
			$selected = isset($options['stability']['model']) ? $options['stability']['model'] : 'sd3-large';

			$models = array(
				esc_html__( 'Stable Diffusion 3 Large', 'all-sources-images' )       => 'sd3-large',
				esc_html__( 'Stable Diffusion 3 Large Turbo', 'all-sources-images' ) => 'sd3-large-turbo',
				esc_html__( 'Stable Diffusion 3 Medium', 'all-sources-images' )      => 'sd3-medium',
				esc_html__( 'Stable Image Core', 'all-sources-images' )              => 'core',
				esc_html__( 'Stable Image Ultra', 'all-sources-images' )             => 'ultra',
			);

			foreach( $models as $name_model => $code_model ) {
				$choose = ($selected == $code_model) ? 'selected="selected"' : '';
				echo '<option '. $choose .' value="'. $code_model .'">'. $name_model .'</option>';
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Choose the Stable Diffusion model for image generation. Ultra provides highest quality, Turbo is faster but lower quality.', 'all-sources-images' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Aspect Ratio', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[stability][aspect_ratio]" class="form-control form-control-lg" >
			<?php
			$selected = isset($options['stability']['aspect_ratio']) ? $options['stability']['aspect_ratio'] : '1:1';

			$ratios = array(
				esc_html__( '1:1 (Square)', 'all-sources-images' )      => '1:1',
				esc_html__( '16:9 (Landscape)', 'all-sources-images' )  => '16:9',
				esc_html__( '21:9 (Ultrawide)', 'all-sources-images' )  => '21:9',
				esc_html__( '2:3 (Portrait)', 'all-sources-images' )    => '2:3',
				esc_html__( '3:2 (Photo)', 'all-sources-images' )       => '3:2',
				esc_html__( '4:5 (Portrait)', 'all-sources-images' )    => '4:5',
				esc_html__( '5:4 (Landscape)', 'all-sources-images' )   => '5:4',
				esc_html__( '9:16 (Mobile)', 'all-sources-images' )     => '9:16',
				esc_html__( '9:21 (Mobile)', 'all-sources-images' )     => '9:21',
			);

			foreach( $ratios as $name_ratio => $code_ratio ) {
				$choose = ($selected == $code_ratio) ? 'selected="selected"' : '';
				echo '<option '. $choose .' value="'. $code_ratio .'">'. $name_ratio .'</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Output Format', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[stability][output_format]" class="form-control form-control-lg" >
			<?php
			$selected = isset($options['stability']['output_format']) ? $options['stability']['output_format'] : 'jpeg';

			$formats = array(
				esc_html__( 'JPEG', 'all-sources-images' ) => 'jpeg',
				esc_html__( 'PNG', 'all-sources-images' )  => 'png',
				esc_html__( 'WebP', 'all-sources-images' ) => 'webp',
			);

			foreach( $formats as $name_format => $code_format ) {
				$choose = ($selected == $code_format) ? 'selected="selected"' : '';
				echo '<option '. $choose .' value="'. $code_format .'">'. $name_format .'</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Negative Prompt', 'all-sources-images' ); ?></label>
	</th>
	<td class="checkbox-list">
		<label class="checkbox ">
			<input name="ASI_plugin_banks_settings[stability][use_negative_prompt]" type="checkbox" value="true" <?php echo( !empty( $options['stability']['use_negative_prompt'] ) && $options['stability']['use_negative_prompt'] == 'true' )? 'checked': ''; ?>><span></span>
			<?php esc_html_e( 'Use default negative prompt', 'all-sources-images' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'Adds common exclusions: "blurry, low quality, distorted, disfigured, ugly, low resolution"', 'all-sources-images' ); ?></p>
	</td>
</tr>
