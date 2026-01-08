<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Pixabay Logo" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/pixabay.png' ); ?>"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php echo wp_kses_post( __( 'From now on, <b>it\'s required</b> to provide your own <b>username</b> and <b>api key</b>. You can register in Pixabay website <a target="_blank" href="https://pixabay.com/en/accounts/register/">here</a> and get api username/key <a target="_blank" href="https://pixabay.com/api/docs/">here</a>', 'all-sources-images' ) ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Pixabay username', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<input type="text" name="ALLSI_plugin_banks_settings[pixabay][username]" class="form-control" value="<?php echo( isset( $options['pixabay']['username'] ) && !empty( $options['pixabay']['username']) )? esc_attr( $options['pixabay']['username'] ): ''; ?>" >
	</td>
</tr>
<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Api key', 'all-sources-images' ); ?></label>
	</th>
	<td id="password-pixabay" class="password">
		<input type="password" name="ALLSI_plugin_banks_settings[pixabay][apikey]" class="form-control" value="<?php echo( isset( $options['pixabay']['apikey'] ) && !empty( $options['pixabay']['apikey']) )? esc_attr( $options['pixabay']['apikey'] ): ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>
<tr valign="top">
	<td colspan="2">
		<button type="button" class="btn btn-primary" id="btnPixabay">
			<?php esc_html_e( 'API testing', 'all-sources-images' ); ?>
		</button>
		<span id="resultPixabay"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../../img/loader-mpt.gif' ); ?>" width="32" class="hidden"/></span>
	</td>
</tr>


<tr valign="top">
	<td colspan="2">
		<hr/>
	</td>
</tr>


<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image Type', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[pixabay][imgtype]" class="form-control" >
			<?php
			$selected = $options['pixabay']['imgtype'];

			$imgtype = array(
				__( '-- All --', 'all-sources-images' )		=> 'all',
				__( 'Photo', 'all-sources-images' )				=> 'photo',
				__( 'Illustration', 'all-sources-images' )	=> 'illustration',
				__( 'Vector image', 'all-sources-images' )				=> 'vector',
			);

			foreach( $imgtype as $name_imgtype => $code_imgtype ) {
				$choose=($selected == $code_imgtype)?'selected="selected"': '';
				echo '<option ' . esc_attr( $choose ) . ' value="' . esc_attr( $code_imgtype ) . '">' . esc_html( $name_imgtype ) . '</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Choose the language', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[pixabay][search_country]" class="form-control" >
			<?php

				$selected = $options['pixabay']['search_country'];
				$country_choose = array(
					__( 'Czech', 'all-sources-images' )				=> 'cs',
					__( 'Danish', 'all-sources-images' )				=> 'da',
					__( 'German', 'all-sources-images' )				=> 'de',
					__( 'English', 'all-sources-images' )			=> 'en',
					__( 'Spanish', 'all-sources-images' )			=> 'es',
					__( 'French', 'all-sources-images' )				=> 'fr',
					__( 'Indonesian', 'all-sources-images' )		=> 'id',
					__( 'Italian', 'all-sources-images' )			=> 'it',
					__( 'Hungarian', 'all-sources-images' )		=> 'hu',
					__( 'Dutch', 'all-sources-images' )				=> 'nl',
					__( 'Norwegian', 'all-sources-images' )		=> 'no',
					__( 'Polish', 'all-sources-images' )				=> 'pl',
					__( 'Portuguese', 'all-sources-images' )		=> 'pt',
					__( 'Romanian', 'all-sources-images' )			=> 'ro',
					__( 'Slovak', 'all-sources-images' )				=> 'sk',
					__( 'Finnish', 'all-sources-images' )			=> 'fi',
					__( 'Swedish', 'all-sources-images' )			=> 'sv',
					__( 'Turkish', 'all-sources-images' )			=> 'tr',
					__( 'Vietnamese', 'all-sources-images' )		=> 'vi',
					__( 'Thai', 'all-sources-images' )					=> 'th',
					__( 'Bulgarian', 'all-sources-images' )		=> 'bg',
					__( 'Russian', 'all-sources-images' )			=> 'ru',
					__( 'Greek', 'all-sources-images' )				=> 'el',
					__( 'Japanese', 'all-sources-images' )			=> 'ja',
					__( 'Korean', 'all-sources-images' )				=> 'ko',
					__( 'Chinese', 'all-sources-images' )			=> 'zh',
				);
				ksort( $country_choose );

				foreach( $country_choose as $name_country => $code_country ) {
					$choose=($selected == $code_country)?'selected="selected"': '';
					echo '<option ' . esc_attr( $choose ) . ' value="' . esc_attr( $code_country ) . '">' . esc_html( $name_country ) . '</option>';
				}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Orientation', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[pixabay][orientation]" class="form-control" >
			<?php
			$selected = $options['pixabay']['orientation'];

			$orientation = array(
				__( '-- All --', 'all-sources-images' )	=> 'all',
				__( 'Horizontal', 'all-sources-images' )	=> 'horizontal',
				__( 'Vertical', 'all-sources-images' )		=> 'vertical',
			);

			foreach( $orientation as $name_orientation => $code_orientation ) {
				$choose=($selected == $code_orientation)?'selected="selected"': '';
				echo '<option ' . esc_attr( $choose ) . ' value="' . esc_attr( $code_orientation ) . '">' . esc_html( $name_orientation ) . '</option>';
			}
			?>
		</select>
	</td>
</tr>


<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Minimum width', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<input type="number" name="ALLSI_plugin_banks_settings[pixabay][min_width]" min="0" class="form-control" value="<?php echo esc_attr( isset( $options['pixabay']['min_width'] ) && ! empty( $options['pixabay']['min_width'] ) ? absint( $options['pixabay']['min_width'] ) : 0 ); ?>" >
		<i><?php esc_html_e( 'px minimum for width', 'all-sources-images' ); ?></i>
	</td>
</tr>



<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Minimum height', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<input type="number" name="ALLSI_plugin_banks_settings[pixabay][min_height]" min="0" class="form-control" value="<?php echo esc_attr( isset( $options['pixabay']['min_height'] ) && ! empty( $options['pixabay']['min_height'] ) ? absint( $options['pixabay']['min_height'] ) : 0 ); ?>" >
		<i><?php esc_html_e( 'px minimum for height', 'all-sources-images' ); ?></i>
	</td>
</tr>



<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Safesearch', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ALLSI_plugin_banks_settings[pixabay][safesearch]" class="form-control" >
			<?php
			$selected = $options['pixabay']['safesearch'];

			$safesearch = array(
				__( 'Off', 'all-sources-images' )		=> 'false',
				__( 'Active', 'all-sources-images' )	=> 'true',
			);

			foreach( $safesearch as $name_safesearch => $code_safesearch ) {
				$choose=($selected == $code_safesearch)?'selected="selected"': '';
				echo '<option ' . esc_attr( $choose ) . ' value="' . esc_attr( $code_safesearch ) . '">' . esc_html( $name_safesearch ) . '</option>';
			}
			?>
		</select>
	</td>
</tr>
