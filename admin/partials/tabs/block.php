<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
?>
<div class="wrap">

	<?php settings_errors(); ?>

    <form method="post" action="options.php" id="tabs">

	      <?php
	        settings_fields( 'ASI-plugin-block-settings' );
	        $options = wp_parse_args( get_option( 'ASI_plugin_block_settings' ), $this->ASI_default_options_block_settings( TRUE ) );
			//$options = get_option( 'ASI_plugin_block_settings' );
	      ?>
	          <table id="general-options" class="form-table tabs-content">
	              <tbody>
	                  <tr>
	                    <td>
                          <?php esc_html_e( 'Display manual search for featured image ', 'all-sources-images' ); ?>
	                    </td>
	                    <td>
	                      <label class="checkbox">
	                          <input data-switch="true" type="checkbox" name="ASI_plugin_block_settings[enable_manual_search]" id="enable_manual_search" value="true" <?php echo( !empty( $options['enable_manual_search']) && $options['enable_manual_search'] == 'true' )? 'checked': ''; ?> />
	                      </label>
	                    </td>
	                  </tr>

					  <?php 
							// Alt Tag

							
						?>

							<tr valign="top" class="based_on_bottom">
								<th scope="row">
									<label for="hseparator"><?php esc_html_e( 'Add alt tag on image', 'all-sources-images' ); ?></label>
								</th>
								<td>
									<label class="checkbox">
										<input data-switch="true" type="checkbox" name="ASI_plugin_block_settings[enable_alt]" id="enable_alt" value="enable" <?php echo( !empty( $options['enable_alt']) && $options['enable_alt'] == 'enable' )? 'checked': ''; ?> />
									</label>
								</td>
							</tr>

							<?php 
									if( !isset( $options['translate_alt_lang'] ) ) {
										$wp_lang    = get_bloginfo('language');
										$alt_lang   = substr( $wp_lang, 0, 2 );
									} else {
										$alt_lang   = $options['translate_alt_lang'];
									}
							?>

							<tr valign="top" class="show_alt" <?php echo (isset($options['enable_alt']) && $options['enable_alt'] != 'enable') ? 'style="display:none;"' : ''; ?>>
								<th scope="row">
										<?php esc_html_e( 'Translation', 'all-sources-images' ); ?>
								</th>
								<td class="checkbox-list">
										<label class="checkbox">
											<input name="ASI_plugin_block_settings[translate_alt]" type="checkbox" value="true" <?php echo( !empty( $options['translate_alt']) && $options['translate_alt'] == 'true' )? 'checked': ''; ?>><span></span> 
											<?php esc_html_e( 'Translate alt text from english to', 'all-sources-images' ); ?>:
										</label>

										<select name="ASI_plugin_block_settings[translate_alt_lang]" class="form-control form-control-lg" >
											<?php
												
												$country_choose = array(
													__( 'Afrikaans', 'all-sources-images' )             => 'af',
													__( 'Afrikaans', 'all-sources-images' )             => 'af',
													__( 'Albanian', 'all-sources-images' )              => 'sq',
													__( 'Amharic', 'all-sources-images' )               => 'sm',
													__( 'Arabic', 'all-sources-images' )                => 'ar',
													__( 'Azerbaijani', 'all-sources-images' )           => 'az',
													__( 'Basque', 'all-sources-images' )                => 'eu',
													__( 'Belarusian', 'all-sources-images' )            => 'be',
													__( 'Bengali', 'all-sources-images' )               => 'bn',
													__( 'Bihari', 'all-sources-images' )                => 'bh',
													__( 'Bosnian', 'all-sources-images' )               => 'bs',
													__( 'Bulgarian', 'all-sources-images' )             => 'bg',
													__( 'Catalan', 'all-sources-images' )               => 'ca',
													__( 'Chinese (Simplified)', 'all-sources-images' )  => 'zh-CN',
													__( 'Chinese (Traditional)', 'all-sources-images' ) => 'zh-TW',
													__( 'Croatian', 'all-sources-images' )              => 'hr',
													__( 'Czech', 'all-sources-images' )                 => 'cs',
													__( 'Danish', 'all-sources-images' )                => 'da',
													__( 'Dutch', 'all-sources-images' )                 => 'nl',
													__( 'Esperanto', 'all-sources-images' )             => 'eo',
													__( 'Estonian', 'all-sources-images' )              => 'et',
													__( 'Faroese', 'all-sources-images' )               => 'fo',
													__( 'Finnish', 'all-sources-images' )               => 'fi',
													__( 'French', 'all-sources-images' )                => 'fr',
													__( 'Frisian', 'all-sources-images' )               => 'fy',
													__( 'Galician', 'all-sources-images' )              => 'gl',
													__( 'Georgian', 'all-sources-images' )              => 'ka',
													__( 'German', 'all-sources-images' )                => 'de',
													__( 'Greek', 'all-sources-images' )                 => 'el',
													__( 'Gujarati', 'all-sources-images' )              => 'gu',
													__( 'Hebrew', 'all-sources-images' )                => 'iw',
													__( 'Hindi', 'all-sources-images' )                 => 'hi',
													__( 'Hungarian', 'all-sources-images' )             => 'hu',
													__( 'Icelandic', 'all-sources-images' )             => 'is',
													__( 'Indonesian', 'all-sources-images' )            => 'id',
													__( 'Interlingua', 'all-sources-images' )           => 'ia',
													__( 'Irish', 'all-sources-images' )                 => 'ga',
													__( 'Italian', 'all-sources-images' )               => 'it',
													__( 'Japanese', 'all-sources-images' )              => 'ja',
													__( 'Javanese', 'all-sources-images' )              => 'jw',
													__( 'Kannada', 'all-sources-images' )               => 'kn',
													__( 'Korean', 'all-sources-images' )                => 'ko',
													__( 'Latin', 'all-sources-images' )                 => 'la',
													__( 'Latvian', 'all-sources-images' )               => 'lv',
													__( 'Lithuanian', 'all-sources-images' )            => 'lt',
													__( 'Macedonian', 'all-sources-images' )            => 'mk',
													__( 'Malay', 'all-sources-images' )                 => 'ms',
													__( 'Malayam', 'all-sources-images' )               => 'ml',
													__( 'Maltese', 'all-sources-images' )               => 'mt',
													__( 'Marathi', 'all-sources-images' )               => 'mr',
													__( 'Nepali', 'all-sources-images' )                => 'ne',
													__( 'Norwegian', 'all-sources-images' )             => 'no',
													__( 'Norwegian (Nynorsk)', 'all-sources-images' )   => 'nn',
													__( 'Occitan', 'all-sources-images' )               => 'oc',
													__( 'Persian', 'all-sources-images' )               => 'fa',
													__( 'Polish', 'all-sources-images' )                => 'pl',
													__( 'Portuguese (Brazil)', 'all-sources-images' )   => 'pt-BR',
													__( 'Portuguese (Portugal)', 'all-sources-images' ) => 'pt-PT',
													__( 'Punjabi', 'all-sources-images' )               => 'pa',
													__( 'Romanian', 'all-sources-images' )              => 'ro',
													__( 'Russian', 'all-sources-images' )               => 'ru',
													__( 'Scots Gaelic', 'all-sources-images' )          => 'gd',
													__( 'Serbian', 'all-sources-images' )               => 'sr',
													__( 'Sinhalese', 'all-sources-images' )             => 'si',
													__( 'Slovak', 'all-sources-images' )                => 'sk',
													__( 'Slovenian', 'all-sources-images' )             => 'sl',
													__( 'Spanish', 'all-sources-images' )               => 'es',
													__( 'Sudanese', 'all-sources-images' )              => 'su',
													__( 'Swahili', 'all-sources-images' )               => 'sw',
													__( 'Swedish', 'all-sources-images' )               => 'sv',
													__( 'Tagalog', 'all-sources-images' )               => 'tl',
													__( 'Tamil', 'all-sources-images' )                 => 'ta',
													__( 'Telugu', 'all-sources-images' )                => 'te',
													__( 'Thai', 'all-sources-images' )                  => 'th',
													__( 'Tigrinya', 'all-sources-images' )              => 'ti',
													__( 'Turkish', 'all-sources-images' )               => 'tr',
													__( 'Ukrainian', 'all-sources-images' )             => 'uk',
													__( 'Urdu', 'all-sources-images' )                  => 'ur',
													__( 'Uzbek', 'all-sources-images' )                 => 'uz',
													__( 'Vietnamese', 'all-sources-images' )            => 'vi',
													__( 'Welsh', 'all-sources-images' )                 => 'cy',
													__( 'Xhosa', 'all-sources-images' )                 => 'xh',
													__( 'Zulu', 'all-sources-images' )                  => 'zu',
												);
												ksort( $country_choose );

												foreach( $country_choose as $name_country => $code_country ) {
													$choose = ( $alt_lang == $code_country) ? 'selected="selected"': '';
													echo '<option '. $choose .' value="'. $code_country .'">'. $name_country .'</option>';
												}
												?>
										</select>

								</td>
							</tr><?php 
							// Caption Tag

							
						?>

							<tr valign="top" class="based_on_bottom">
								<th scope="row">
									<label for="hseparator"><?php esc_html_e( 'Add caption tag on image', 'all-sources-images' ); ?></label>
								</th>
								<td>
									<label class="checkbox">
										<input data-switch="true" type="checkbox" name="ASI_plugin_block_settings[enable_caption]" id="enable_caption" value="enable" <?php echo( !empty( $options['enable_caption']) && $options['enable_caption'] == 'enable' )? 'checked': ''; ?> />
									</label>
							</td>
						</tr>

	              </tbody>
	          </table>
	      <?php submit_button(); ?>
						
    </form>
</div>
