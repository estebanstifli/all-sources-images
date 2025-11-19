<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
?>
<tr valign="top" class="selected_image">
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Image Naming Convention', 'all-sources-images' ); ?></label>
        </th>
        <td class="chosen_title radio-inline">
                <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="title"  name="ASI_plugin_main_settings[image_filename] " type="radio" <?php echo( !empty( $options['image_filename']) && $options['image_filename'] == 'title'  )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Title', 'all-sources-images' ); ?></label><br/>
                <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="date"   name="ASI_plugin_main_settings[image_filename] " type="radio" <?php echo( !empty( $options['image_filename']) && $options['image_filename'] == 'date'   )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Date', 'all-sources-images' ); ?></label><br/>
                <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="random" name="ASI_plugin_main_settings[image_filename] " type="radio" <?php echo( !empty( $options['image_filename']) && $options['image_filename'] == 'random' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Random number', 'all-sources-images' ); ?></label>
        </td>
</tr>

<tr valign="top">
        <th scope="row">
                <?php esc_html_e( 'Overwrite featured images', 'all-sources-images' ); ?>
        </th>
        <td class="checkbox-list">
                <label class="checkbox"><input <?php echo( !empty( $options['rewrite_featured']) && $options['rewrite_featured'] == 'true' )? 'checked': ''; ?> name="ASI_plugin_main_settings[rewrite_featured]" type="checkbox" value="true"> <span></span> <?php esc_html_e( 'Overwrite', 'all-sources-images' ); ?></label>
                <p class="description">
                        <?php esc_html_e( 'Warning: This option will overwrite existing featured images', 'all-sources-images' ); ?>
                </p>
        </td>
</tr>

<tr valign="top">
        <th scope="row">
                <?php esc_html_e( 'Image reuse', 'all-sources-images' ); ?>
        </th>
        <td class="checkbox-list">
                <label class="checkbox"><input <?php echo( !empty( $options['image_reuse']) && $options['image_reuse'] == 'true' )? 'checked': ''; ?> name="ASI_plugin_main_settings[image_reuse]" type="checkbox" value="true"> <span></span> <?php esc_html_e( 'Enable', 'all-sources-images' ); ?></label>
                <p class="description">
                        <?php esc_html_e( 'Check for existing images media before downloading new ones to prevent duplicates and save storage space. Reuse is based on filename.', 'all-sources-images' ); ?>
                </p>
        </td>
</tr>

<tr valign="top" class="shuffle_image">
        <th scope="row">
                <?php esc_html_e( 'Image Modifications', 'all-sources-images' ); ?>
        </th>
        <td class="checkbox-list">
            <label class="checkbox <?php echo $checkbox_disabled; ?>"><input <?php echo( !empty( $options['image_flip']) && $options['image_flip'] == 'true' )? 'checked': ''; ?> name="ASI_plugin_main_settings[image_flip]" type="checkbox" value="true"> <span></span> <?php esc_html_e( 'Flip horizontally', 'all-sources-images' ); ?></label>
            <label class="checkbox <?php echo $checkbox_disabled; ?>"><input <?php echo( !empty( $options['image_crop']) && $options['image_crop'] == 'true' )? 'checked': ''; ?> name="ASI_plugin_main_settings[image_crop]" type="checkbox" value="true"> <span></span> <?php esc_html_e( 'Crop Image by 10%	', 'all-sources-images' ); ?></label>
        </td>
</tr>


<?php 
    // Alt Tag

    
?>

    <tr valign="top" class="based_on_bottom">
        <th scope="row">
            <label for="hseparator"><?php esc_html_e( 'Add ALT Attribute to Image', 'all-sources-images' ); ?></label>
        </th>
        <td>
            <label class="checkbox">
                <input data-switch="true" type="checkbox" name="ASI_plugin_main_settings[enable_alt]" id="enable_alt" value="enable" <?php echo( !empty( $options['enable_alt']) && $options['enable_alt'] == 'enable' )? 'checked': ''; ?> />
            </label>
        </td>
    </tr>

    <tr valign="top" class="show_alt" <?php echo( !isset( $options['enable_alt'] ) || ( $options['enable_alt'] != 'enable' ) ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Alt Text from', 'all-sources-images' ); ?></label>
        </th>
        <td class="tags radio-list">
            <label class="radio">
                <input value="source" name="ASI_plugin_main_settings[alt_from]" type="radio" <?php echo( !empty( $options['alt_from']) && $options['alt_from'] == 'source' )? 'checked': ''; ?>><span></span> 
                <?php esc_html_e( 'Source', 'all-sources-images' ); ?>
            </label>
            <label class="radio">
                <input value="based_on" name="ASI_plugin_main_settings[alt_from]" type="radio" <?php echo( !empty( $options['alt_from']) && $options['alt_from'] == 'based_on' )? 'checked': ''; ?>> <span></span>
                <?php esc_html_e( 'Text "based_on"', 'all-sources-images' ); ?>
            </label>
        </td>
    </tr>

    <?php 
            if( !isset( $options['translate_alt_lang'] ) ) {
                $wp_lang    = get_bloginfo('language');
                $alt_lang   = substr( $wp_lang, 0, 2 );
            } else {
                $alt_lang   = $options['translate_alt_lang'];
            ?>

    <tr valign="top" class="show_alt" <?php echo(isset($options['enable_alt']) && ($options['enable_alt'] != 'enable') ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <?php esc_html_e( 'ALT Text Translation', 'all-sources-images' ); ?>
        </th>
        <td class="checkbox-list">
                <label class="checkbox">
                    <input name="ASI_plugin_main_settings[translate_alt]" type="checkbox" value="true" <?php echo( !empty( $options['translate_alt']) && $options['translate_alt'] == 'true' )? 'checked': ''; ?>><span></span> 
                    <?php esc_html_e( 'Translate alt text from english to', 'all-sources-images' ); ?>:
                </label>

                <select name="ASI_plugin_main_settings[translate_alt_lang]" class="form-control form-control-lg" >
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
                        ?>
                </select>

        </td>
    </tr>


<?php } } else { ?>
    <tr valign="top" class="based_on_bottom">
        <th scope="row">
            <label for="hseparator">
                <?php esc_html_e( 'Add ALT Attribute to Image', 'all-sources-images' ); ?><br/>
                <small><?php esc_html_e( 'Only available with the pro version', 'all-sources-images' ); ?></small>
            </label>
        </th>
        <td>
            <label class="checkbox checkbox-disabled checkbox-admin">
                <input disabled="disabled" data-switch="true" type="checkbox" name="ASI_plugin_main_settings[enable_alt]" id="enable_alt" value="disable" />
            </label>
        </td>
    </tr>
<?php ?>


<tr valign="top" class="based_on_bottom">
    <th scope="row">
        <label for="hseparator"><?php esc_html_e( 'Add caption tag on image', 'all-sources-images' ); ?></label>
    </th>
    <td>
        <label class="checkbox">
            <input data-switch="true" type="checkbox" name="ASI_plugin_main_settings[enable_caption]" id="enable_caption" value="enable" <?php echo( !empty( $options['enable_caption']) && $options['enable_caption'] == 'enable' )? 'checked': ''; ?> />
        </label>
    </td>
</tr>

<tr valign="top" class="show_caption" <?php echo( !isset( $options['enable_caption'] ) || ( $options['enable_caption'] != 'enable' ) ? 'style="display:none;"': ''); ?>>
    <th scope="row">
            <label for="hseparator"><?php esc_html_e( 'Caption Format', 'all-sources-images' ); ?></label>
    </th>
    <td class="tags radio-list">
        <label class="radio">
            <input value="author" name="ASI_plugin_main_settings[caption_from]" type="radio" <?php echo( !empty( $options['caption_from']) && $options['caption_from'] == 'author' )? 'checked': ''; ?>><span></span> 
            <?php esc_html_e( 'Image Author', 'all-sources-images' ); ?>
        </label>
        <label class="radio">
            <input value="author_bank" name="ASI_plugin_main_settings[caption_from]" type="radio" <?php echo( !empty( $options['caption_from']) && $options['caption_from'] == 'author_bank' )? 'checked': ''; ?>> <span></span>
            <?php _e( 'Image Author + Name of Image Bank (Example: <em>Author Name from Pixabay</em>)', 'all-sources-images' ); ?>
        </label>
    </td>
</tr>