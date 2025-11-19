<?php

if ( !function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
?>

<tr valign="top">
	<td colspan="2" class="source-logo"><img alt="Google Images Logo" src="<?php 
echo plugin_dir_url( __FILE__ );
?>/img/google_images.png"></td>
</tr>

<tr valign="top">
	
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Choose the language', 'all-sources-images' );
?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[google_scraping][search_country]" class="form-control form-control-lg" >
			<?php 
$selected = $options['google_scraping']['search_country'];
$country_choose = array(
    __( 'English (default)', 'all-sources-images' )     => 'en',
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
    __( 'English', 'all-sources-images' )               => 'en',
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
//ksort( $country_choose );
foreach ( $country_choose as $name_country => $code_country ) {
    $choose = ( $selected == $code_country ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_country . '">' . $name_country . '</option>';
}
?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Specified color predominantly', 'all-sources-images' );
?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[google_scraping][img_color]" class="form-control form-control-lg" >
			<?php 
$selected = $options['google_scraping']['img_color'];
$img_color = array(
    __( '-- Default --', 'all-sources-images' ) => '',
    __( 'Black', 'all-sources-images' )         => 'black',
    __( 'Blue', 'all-sources-images' )          => 'blue',
    __( 'Brown', 'all-sources-images' )         => 'brown',
    __( 'Gray', 'all-sources-images' )          => 'gray',
    __( 'Green', 'all-sources-images' )         => 'green',
    __( 'Pink', 'all-sources-images' )          => 'pink',
    __( 'Purple', 'all-sources-images' )        => 'purple',
    __( 'Teal', 'all-sources-images' )          => 'teal',
    __( 'White', 'all-sources-images' )         => 'white',
    __( 'Yellow', 'all-sources-images' )        => 'yellow',
);
ksort( $img_color );
foreach ( $img_color as $name_color => $code_color ) {
    $choose = ( $selected == $code_color ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_color . '">' . $name_color . '</option>';
}
?>
		</select>
		<br/>
		<p class="description">
			<i><?php 
esc_html_e( 'Experimental', 'all-sources-images' );
?></i> -
			<?php 
esc_html_e( 'Restricts results to images that contain a specified color predominantly', 'all-sources-images' );
?>
		</p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Rights', 'all-sources-images' );
?></label>
	</th>
	<td>
		<p class="description">
			<?php 
esc_html_e( 'Choose these options can reduce relevance of results, but permit to use free-to-use images.', 'all-sources-images' );
?>
		</p>
		<select name="ASI_plugin_banks_settings[google_scraping][rights]" class="form-control form-control-lg" >
			<?php 
$selected = $options['google_scraping']['rights'];
$rights = array(
    __( 'Not filtered by license (default)', 'all-sources-images' )                 => '',
    __( 'Labeled for reuse with modification', 'all-sources-images' )               => 'fmc',
    __( 'Labeled for reuse', 'all-sources-images' )                                 => 'fc',
    __( 'Labeled for noncommercial reuse with modification', 'all-sources-images' ) => 'fm',
    __( 'Labeled for noncommercial reuse', 'all-sources-images' )                   => 'f',
);
foreach ( $rights as $name_rights => $code_rights ) {
    $choose = ( $selected == $code_rights ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_rights . '">' . $name_rights . '</option>';
}
?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Image size', 'all-sources-images' );
?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[google_scraping][imgsz]" class="form-control form-control-lg" >
			<?php 
$selected = $options['google_scraping']['imgsz'];
$imgsz = array(
    __( '-- Default --', 'all-sources-images' )      => '',
    __( 'icon', 'all-sources-images' )               => 'i',
    __( 'medium', 'all-sources-images' )             => 'm',
    __( 'large', 'all-sources-images' )              => 'l',
    __( 'More than 400x300', 'all-sources-images' )  => 'lt,islt:qsvga',
    __( 'More than 640x480', 'all-sources-images' )  => 'lt,islt:vga',
    __( 'More than 800x600', 'all-sources-images' )  => 'lt,islt:svga',
    __( 'More than 1024x768', 'all-sources-images' ) => 'lt,islt:xga',
    __( 'More than 2Mpx', 'all-sources-images' )     => 'lt,islt:2mp',
    __( 'More than 4Mpx', 'all-sources-images' )     => 'lt,islt:4mp',
    __( 'More than 6Mpx', 'all-sources-images' )     => 'lt,islt:6mp',
    __( 'More than 8Mpx', 'all-sources-images' )     => 'lt,islt:8mp',
    __( 'More than 10Mpx', 'all-sources-images' )    => 'lt,islt:10mp',
);
foreach ( $imgsz as $name_imgsz => $code_imgsz ) {
    $choose = ( $selected == $code_imgsz ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_imgsz . '">' . $name_imgsz . '</option>';
}
?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Image format', 'all-sources-images' );
?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[google_scraping][format]" class="form-control form-control-lg" >
			<?php 
$selected = $options['google_scraping']['format'];
$formats = array(
    __( '-- Default --', 'all-sources-images' ) => '',
    __( 'Portrait', 'all-sources-images' )      => 't',
    __( 'Square', 'all-sources-images' )        => 's',
    __( 'Landscape', 'all-sources-images' )     => 'w',
    __( 'Panoramic', 'all-sources-images' )     => 'xw',
);
// format
foreach ( $formats as $name_format => $code_format ) {
    $choose = ( $selected == $code_format ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_format . '">' . $name_format . '</option>';
}
?>
		</select>
	</td>
</tr>


<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Image Type', 'all-sources-images' );
?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[google_scraping][imgtype]" class="form-control form-control-lg" >
			<?php 
$selected = $options['google_scraping']['imgtype'];
$imgtype = array(
    __( '-- Default --', 'all-sources-images' ) => '',
    __( 'Face', 'all-sources-images' )          => 'face',
    __( 'Photo', 'all-sources-images' )         => 'photo',
    __( 'Clipart', 'all-sources-images' )       => 'clipart',
    __( 'Lineart', 'all-sources-images' )       => 'lineart',
    __( 'Animated', 'all-sources-images' )      => 'animated',
);
foreach ( $imgtype as $name_imgtype => $code_imgtype ) {
    $choose = ( $selected == $code_imgtype ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_imgtype . '">' . $name_imgtype . '</option>';
}
?>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="hseparator"><?php 
esc_html_e( 'Safety level', 'all-sources-images' );
?></label>
	</th>
	<td>
		<select name="ASI_plugin_banks_settings[google_scraping][safe]" class="form-control form-control-lg" >
			<?php 
$selected = $options['google_scraping']['safe'];
$safe = array(
    __( 'Moderate (default)', 'all-sources-images' ) => 'moderate',
    __( 'Active', 'all-sources-images' )             => 'activate',
    __( 'Off', 'all-sources-images' )                => 'off',
);
foreach ( $safe as $name_safe => $code_safe ) {
    $choose = ( $selected == $code_safe ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_safe . '">' . $name_safe . '</option>';
}
?>
		</select>
	</td>
</tr>

<?php 
$executeElseBlock = true;
if ( $executeElseBlock ) {
    $restricted_domains = '';
    $readonly = 'readonly';
}
?>
	<tr valign="top">
		<th scope="row">
			<label for="hseparator"><?php 
esc_html_e( 'Restricted domains', 'all-sources-images' );
?></label>
			<p class="description">
				<?php 
esc_html_e( 'One domain per line', 'all-sources-images' );
?><br/>
				<?php 
esc_html_e( 'Leave empty to disable it', 'all-sources-images' );
?>
			</p>
		</th>
		<td>
      <p class="description">
              <?php 
_e( 'Add domains here if you want image <strong>only</strong> from these domains.', 'all-sources-images' );
?>
      </p>
			<textarea class="form-control form-control-solid" id="restricted_domains" name="ASI_plugin_banks_settings[google_scraping][restricted_domains]" <?php 
echo $readonly;
?> placeholder="domain-one.com&#13;&#10;domain-two.net&#13;&#10;domain-three.info" rows="8" cols="40"><?php 
echo esc_attr( $restricted_domains );
?></textarea>
		</td>
	</tr>

<?php 
if ( $executeElseBlock ) {
    $blacklisted_domains = '';
    $readonly = 'readonly';
}
?>
	<tr valign="top">
		<th scope="row">
			<label for="hseparator"><?php 
esc_html_e( 'Blacklisted domains', 'all-sources-images' );
?></label>
			<p class="description">
				<?php 
esc_html_e( 'One domain per line', 'all-sources-images' );
?><br/>
				<?php 
esc_html_e( 'Leave empty to disable it', 'all-sources-images' );
?>
			</p>
		</th>
		<td>
	    <p class="description">
	            <?php 
esc_html_e( 'Add domains here if you want to blacklist them from results.', 'all-sources-images' );
?>
	    </p>
			<textarea class="form-control form-control-solid" id="blacklisted_domains" name="ASI_plugin_banks_settings[google_scraping][blacklisted_domains]" <?php 
echo $readonly;
?> placeholder="domain-one.com&#13;&#10;domain-two.net&#13;&#10;domain-three.info" rows="8" cols="40"><?php 
echo esc_attr( $blacklisted_domains );
?></textarea>
		</td>
	</tr>
