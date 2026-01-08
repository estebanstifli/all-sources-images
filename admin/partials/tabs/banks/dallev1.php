<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$allsi_dalle_options = array();
if ( isset( $options['dallev1'] ) && is_array( $options['dallev1'] ) ) {
	$allsi_dalle_options = $options['dallev1'];
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Dall-e Logo" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/dalle.png' ); ?>"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php echo wp_kses_post( __( '<b>It\'s required</b> to provide your own <b>api key</b>. You can register in OpenAI API website <a target="_blank" href="https://openai.com/">here</a> and get api key.', 'all-sources-images' ) ); ?>
		</div>
		<div class="update-nag red-part">
			<?php echo wp_kses_post( __( '<b>Caution: DALL-E API can take a long time</b> to generate. About 20 seconds to get a 1024x1024 image.', 'all-sources-images' ) ); ?>
		</div>
		<div class="update-nag">
			<?php echo wp_kses_post( __( 'DALL-E API has some <b>restricted words</b> that will not generate any images. Please refer to the <a href="https://labs.openai.com/policies/content-policy">content policy</a> if you want more details.', 'all-sources-images' ) ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'API Key', 'all-sources-images' ); ?></label>
	</th>
	<td id="password-dalle" class="password">
		<input type="password" name="ALLSI_plugin_banks_settings[dallev1][apikey]" class="form-control" value="<?php echo ( isset( $allsi_dalle_options['apikey'] ) && ! empty( $allsi_dalle_options['apikey'] ) ) ? esc_attr( $allsi_dalle_options['apikey'] ) : ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>
<tr valign="top">
	<td colspan="2">
		<button type="button" class="btn btn-primary" id="btnDalle">
			<?php esc_html_e( 'API testing', 'all-sources-images' ); ?>
		</button>
		<span id="resultDalle"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../img/loader-mpt.gif' ); ?>" width="32" class="hidden"/></span>
	</td>
</tr>



<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image size', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[dallev1][imgsize]" class="form-control form-control-lg" >
			<?php
			$allsi_selected = isset( $allsi_dalle_options['imgsize'] ) ? $allsi_dalle_options['imgsize'] : '1024x1024';

			$allsi_sizes = array(
				esc_html__( '1024x1024', 'all-sources-images' )   => '1024x1024',
				esc_html__( '1792x1024', 'all-sources-images' )   => '1792x1024',
				esc_html__( '1024x1792', 'all-sources-images' )   => '1024x1792',
			);

			foreach( $allsi_sizes as $allsi_name_size => $allsi_code_size ) {
				echo '<option ' . selected( $allsi_selected, $allsi_code_size, false ) . ' value="' . esc_attr( $allsi_code_size ) . '">' . esc_html( $allsi_name_size ) . '</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'PNG to JPEG', 'all-sources-images' ); ?></label>
	</th>
	<td class="checkbox-list">
		<label class="checkbox ">
			<input name="ALLSI_plugin_banks_settings[dallev1][convert_jpg]" type="checkbox" value="true" <?php checked( ! empty( $allsi_dalle_options['convert_jpg'] ) && $allsi_dalle_options['convert_jpg'] == 'true' ); ?>><span></span>
			<?php esc_html_e( 'Convert to Jpeg image extension', 'all-sources-images' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'DALL-E will create a PNG file, click this option if you want to convert the file to a Jpeg image.', 'all-sources-images' ); ?></p>
	</td>
</tr>
