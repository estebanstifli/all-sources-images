<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Google Images Logo" src="<?php echo plugin_dir_url( __FILE__ ); ?>/img/google_images.png"></td>
</tr>

<tr valign="top">
	<td colspan="2">
		<div class="update-nag">
			<?php _e('From now on, <b>it\'s required</b> to provide your own Google <i>Search engine ID</i> and <b>API Key</b>. You must follow the process <a target="_blank" href="https://developers.google.com/custom-search/json-api/v1/overview#prerequisites">here</a>. You must get both valid <strong>Search engine ID</strong> and <strong>API Key</strong>', 'all-sources-images' ); ?>
		</div>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Custom Search Engine ID', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<input type="text" name="ASI_plugin_banks_settings[googleimage][cxid]" class="form-control" value="<?php echo( isset( $options['googleimage']['cxid'] ) && !empty( $options['googleimage']['cxid'] ) )? trim( $options['googleimage']['cxid'] ): ''; ?>" >
	</td>
</tr>
<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Google API Key', 'all-sources-images' ); ?></label>
	</th>
	<td id="password-googleAPI" class="password">
		<input type="password" name="ASI_plugin_banks_settings[googleimage][apikey]" class="form-control" value="<?php echo( isset( $options['googleimage']['apikey'] ) && !empty( $options['googleimage']['apikey']) )? trim( $options['googleimage']['apikey'] ): ''; ?>" >
		<i id="togglePassword"></i>
	</td>
</tr>

<tr valign="top">
	<td colspan="2">
		<hr/>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Choose the language', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[googleimage][search_country]" class="form-control form-control-lg" >
			<?php
				$selected = $options['googleimage']['search_country'];
				$country_choose = array(
					esc_html__( 'English (default)', 'all-sources-images' )     => 'en',
					esc_html__( 'Afrikaans', 'all-sources-images' )             => 'af',
					esc_html__( 'Afrikaans', 'all-sources-images' )             => 'af',
					esc_html__( 'Albanian', 'all-sources-images' )              => 'sq',
					esc_html__( 'Amharic', 'all-sources-images' )               => 'sm',
					esc_html__( 'Arabic', 'all-sources-images' )                => 'ar',
					esc_html__( 'Azerbaijani', 'all-sources-images' )           => 'az',
					esc_html__( 'Basque', 'all-sources-images' )                => 'eu',
					esc_html__( 'Belarusian', 'all-sources-images' )            => 'be',
					esc_html__( 'Bengali', 'all-sources-images' )               => 'bn',
					esc_html__( 'Bihari', 'all-sources-images' )                => 'bh',
					esc_html__( 'Bosnian', 'all-sources-images' )               => 'bs',
					esc_html__( 'Bulgarian', 'all-sources-images' )             => 'bg',
					esc_html__( 'Catalan', 'all-sources-images' )               => 'ca',
					esc_html__( 'Chinese (Simplified)', 'all-sources-images' )  => 'zh-CN',
					esc_html__( 'Chinese (Traditional)', 'all-sources-images' ) => 'zh-TW',
					esc_html__( 'Croatian', 'all-sources-images' )              => 'hr',
					esc_html__( 'Czech', 'all-sources-images' )                 => 'cs',
					esc_html__( 'Danish', 'all-sources-images' )                => 'da',
					esc_html__( 'Dutch', 'all-sources-images' )                 => 'nl',
					esc_html__( 'English', 'all-sources-images' )               => 'en',
					esc_html__( 'Esperanto', 'all-sources-images' )             => 'eo',
					esc_html__( 'Estonian', 'all-sources-images' )              => 'et',
					esc_html__( 'Faroese', 'all-sources-images' )               => 'fo',
					esc_html__( 'Finnish', 'all-sources-images' )               => 'fi',
					esc_html__( 'French', 'all-sources-images' )                => 'fr',
					esc_html__( 'Frisian', 'all-sources-images' )               => 'fy',
					esc_html__( 'Galician', 'all-sources-images' )              => 'gl',
					esc_html__( 'Georgian', 'all-sources-images' )              => 'ka',
					esc_html__( 'German', 'all-sources-images' )                => 'de',
					esc_html__( 'Greek', 'all-sources-images' )                 => 'el',
					esc_html__( 'Gujarati', 'all-sources-images' )              => 'gu',
					esc_html__( 'Hebrew', 'all-sources-images' )                => 'iw',
					esc_html__( 'Hindi', 'all-sources-images' )                 => 'hi',
					esc_html__( 'Hungarian', 'all-sources-images' )             => 'hu',
					esc_html__( 'Icelandic', 'all-sources-images' )             => 'is',
					esc_html__( 'Indonesian', 'all-sources-images' )            => 'id',
					esc_html__( 'Interlingua', 'all-sources-images' )           => 'ia',
					esc_html__( 'Irish', 'all-sources-images' )                 => 'ga',
					esc_html__( 'Italian', 'all-sources-images' )               => 'it',
					esc_html__( 'Japanese', 'all-sources-images' )              => 'ja',
					esc_html__( 'Javanese', 'all-sources-images' )              => 'jw',
					esc_html__( 'Kannada', 'all-sources-images' )               => 'kn',
					esc_html__( 'Korean', 'all-sources-images' )                => 'ko',
					esc_html__( 'Latin', 'all-sources-images' )                 => 'la',
					esc_html__( 'Latvian', 'all-sources-images' )               => 'lv',
					esc_html__( 'Lithuanian', 'all-sources-images' )            => 'lt',
					esc_html__( 'Macedonian', 'all-sources-images' )            => 'mk',
					esc_html__( 'Malay', 'all-sources-images' )                 => 'ms',
					esc_html__( 'Malayam', 'all-sources-images' )               => 'ml',
					esc_html__( 'Maltese', 'all-sources-images' )               => 'mt',
					esc_html__( 'Marathi', 'all-sources-images' )               => 'mr',
					esc_html__( 'Nepali', 'all-sources-images' )                => 'ne',
					esc_html__( 'Norwegian', 'all-sources-images' )             => 'no',
					esc_html__( 'Norwegian (Nynorsk)', 'all-sources-images' )   => 'nn',
					esc_html__( 'Occitan', 'all-sources-images' )               => 'oc',
					esc_html__( 'Persian', 'all-sources-images' )               => 'fa',
					esc_html__( 'Polish', 'all-sources-images' )                => 'pl',
					esc_html__( 'Portuguese (Brazil)', 'all-sources-images' )   => 'pt-BR',
					esc_html__( 'Portuguese (Portugal)', 'all-sources-images' ) => 'pt-PT',
					esc_html__( 'Punjabi', 'all-sources-images' )               => 'pa',
					esc_html__( 'Romanian', 'all-sources-images' )              => 'ro',
					esc_html__( 'Russian', 'all-sources-images' )               => 'ru',
					esc_html__( 'Scots Gaelic', 'all-sources-images' )          => 'gd',
					esc_html__( 'Serbian', 'all-sources-images' )               => 'sr',
					esc_html__( 'Sinhalese', 'all-sources-images' )             => 'si',
					esc_html__( 'Slovak', 'all-sources-images' )                => 'sk',
					esc_html__( 'Slovenian', 'all-sources-images' )             => 'sl',
					esc_html__( 'Spanish', 'all-sources-images' )               => 'es',
					esc_html__( 'Sudanese', 'all-sources-images' )              => 'su',
					esc_html__( 'Swahili', 'all-sources-images' )               => 'sw',
					esc_html__( 'Swedish', 'all-sources-images' )               => 'sv',
					esc_html__( 'Tagalog', 'all-sources-images' )               => 'tl',
					esc_html__( 'Tamil', 'all-sources-images' )                 => 'ta',
					esc_html__( 'Telugu', 'all-sources-images' )                => 'te',
					esc_html__( 'Thai', 'all-sources-images' )                  => 'th',
					esc_html__( 'Tigrinya', 'all-sources-images' )              => 'ti',
					esc_html__( 'Turkish', 'all-sources-images' )               => 'tr',
					esc_html__( 'Ukrainian', 'all-sources-images' )             => 'uk',
					esc_html__( 'Urdu', 'all-sources-images' )                  => 'ur',
					esc_html__( 'Uzbek', 'all-sources-images' )                 => 'uz',
					esc_html__( 'Vietnamese', 'all-sources-images' )            => 'vi',
					esc_html__( 'Welsh', 'all-sources-images' )                 => 'cy',
					esc_html__( 'Xhosa', 'all-sources-images' )                 => 'xh',
					esc_html__( 'Zulu', 'all-sources-images' )                  => 'zu',
				);
				//ksort( $country_choose );

				foreach( $country_choose as $name_country => $code_country ) {
					$choose = ( $selected == $code_country) ? 'selected="selected"': '';
					echo '<option '. $choose .' value="'. $code_country .'">'. $name_country .'</option>';
				}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Specified color predominantly', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[googleimage][img_color]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['img_color'];

			$img_color = array(
				esc_html__( '-- Default --', 'all-sources-images' ) => '',
				esc_html__( 'Black', 'all-sources-images' )         => 'black',
				esc_html__( 'Blue', 'all-sources-images' )          => 'blue',
				esc_html__( 'Brown', 'all-sources-images' )         => 'brown',
				esc_html__( 'Gray', 'all-sources-images' )          => 'gray',
				esc_html__( 'Green', 'all-sources-images' )         => 'green',
				esc_html__( 'Pink', 'all-sources-images' )          => 'pink',
				esc_html__( 'Purple', 'all-sources-images' )        => 'purple',
				esc_html__( 'Teal', 'all-sources-images' )          => 'teal',
				esc_html__( 'White', 'all-sources-images' )         => 'white',
				esc_html__( 'Yellow', 'all-sources-images' )        => 'yellow',
			);
			ksort( $img_color );

			foreach( $img_color as $name_color => $code_color ) {
				$choose=($selected == $code_color)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_color .'">'. $name_color .'</option>';
			}
			?>
		</select>
		<br/>
		<p class="description">
			<i><?php esc_html_e( 'Experimental', 'all-sources-images' ); ?></i> -
			<?php esc_html_e( 'Restricts results to images that contain a specified color predominantly', 'all-sources-images' ); ?>
		</p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Filetype', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[googleimage][filetype]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['filetype'];

			$filetype = array(
				esc_html__( '-- Default --', 'all-sources-images' ) => '',
				esc_html__( 'jpg', 'all-sources-images' )           => 'jpg',
				esc_html__( 'png', 'all-sources-images' )           => 'png',
				esc_html__( 'gif', 'all-sources-images' )           => 'gif',
				esc_html__( 'bmp', 'all-sources-images' )           => 'bmp',
				esc_html__( 'webp', 'all-sources-images' )          => 'webp'
			);
			ksort( $filetype );

			foreach( $filetype as $name_filetype => $code_filetype ) {
				$choose=($selected == $code_filetype)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_filetype .'">'. $name_filetype .'</option>';
			}
			?>
		</select>
		<br/>
		<p class="description">
			<?php esc_html_e( 'Restricts image search to one of the following specific file types', 'all-sources-images' ); ?>
		</p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Rights', 'all-sources-images' ); ?></label>
	</th>
	<td class="checkbox-list">
		<p class="description">
			<?php esc_html_e( 'Choose these options can reduce relevance of results, but permit to use free-to-use images.', 'all-sources-images' ); ?>
		</p>
		<?php
			$rights_array = array(
				__( 'Publicdomain - <i>restricts search results to images with the publicdomain label.</i>', 'all-sources-images' ) 		=> 'cc_publicdomain',
				__( 'Attribute - <i>restricts search results to images with the attribute label.</i>', 'all-sources-images' )          => 'cc_attribute',
				__( 'Sharealike - <i>restricts search results to images with the sharealike label.</i>', 'all-sources-images' )        => 'cc_sharealike',
				__( 'Noncommercial - <i>restricts search results to images with the noncomercial label.</i>', 'all-sources-images' )   => 'cc_noncommercial',
				__( 'Nonderived - <i>restricts search results to images with the nonderived label.</i>', 'all-sources-images' )     		=> 'cc_nonderived',
			);


			foreach ( $rights_array  as $right => $right_code ) {
				$checked= ( isset( $options['googleimage']['rights'] ) && !empty( $options['googleimage']['rights'] ) && in_array( $right_code, $options['googleimage']['rights'] ) )? 'checked="checked""' : '';
				echo '<label class="checkbox">
					<input '. $checked .' name="ASI_plugin_banks_settings[googleimage][rights]['. $right_code .']" type="checkbox" value="'. $right_code .'"> <span></span> '. $right .'
				</label>';
			}
		?>

	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image size', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[googleimage][imgsz]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['imgsz'];

			$imgsz = array(
				esc_html__( '-- Default --', 'all-sources-images' ) => '',
				esc_html__( 'icon', 'all-sources-images' )          => 'icon',
				esc_html__( 'small', 'all-sources-images' )         => 'small',
				esc_html__( 'medium', 'all-sources-images' )        => 'medium',
				esc_html__( 'large', 'all-sources-images' )         => 'large',
				esc_html__( 'xlarge', 'all-sources-images' )        => 'xlarge',
				esc_html__( 'xxlarge', 'all-sources-images' )       => 'xxlarge',
				esc_html__( 'huge', 'all-sources-images' )          => 'huge',
			);

			foreach( $imgsz as $name_imgsz => $code_imgsz ) {
				$choose=($selected == $code_imgsz)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_imgsz .'">'. $name_imgsz .'</option>';
			}
			?>
		</select>
	</td>
</tr>


<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Image Type', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[googleimage][imgtype]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['imgtype'];

			$imgtype = array(
				esc_html__( '-- Default --', 'all-sources-images' ) => '',
				esc_html__( 'Face', 'all-sources-images' )          => 'face',
				esc_html__( 'Photo', 'all-sources-images' )         => 'photo',
				esc_html__( 'Clipart', 'all-sources-images' )       => 'clipart',
				esc_html__( 'Lineart', 'all-sources-images' )       => 'lineart',
			);

			foreach( $imgtype as $name_imgtype => $code_imgtype ) {
				$choose=($selected == $code_imgtype)?'selected="selected"': '';
				echo '<option '. $choose .' value="'. $code_imgtype .'">'. $name_imgtype .'</option>';
			}
			?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php esc_html_e( 'Safety level', 'all-sources-images' ); ?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[googleimage][safe]" class="form-control form-control-lg" >
			<?php
			$selected = $options['googleimage']['safe'];

			$safe = array(
				esc_html__( 'Moderate (default)', 'all-sources-images' ) => 'moderate',
				esc_html__( 'Active', 'all-sources-images' )             => 'activate',
				esc_html__( 'Off', 'all-sources-images' )                => 'off',
			);

			foreach( $safe as $name_safe => $code_safe ) {
				$choose = ( $selected == $code_safe ) ? 'selected="selected"' : '';
				echo '<option '. $choose .' value="'. $code_safe .'">'. $name_safe .'</option>';
			}
			?>
		</select>
	</td>
</tr>
