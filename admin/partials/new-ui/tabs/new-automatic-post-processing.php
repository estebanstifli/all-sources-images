<?php
/**
 * New Automatic - Post-Processing Tab
 * 
 * This tab handles image modifications after download (flip, crop, naming, alt tags, etc.)
 * Part of the new admin UI structure
 *
 * @package All_Sources_Images
 */

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

// Get options
$options = get_option( 'ASI_plugin_main_settings' );
$options = wp_parse_args( $options, $this->ASI_default_options_main_settings() );

// Premium check
$checkbox_disabled = '';
$disabled = '';
if ( function_exists( 'asi_freemius' ) && !asi_freemius()->is_premium() ) {
    $checkbox_disabled = 'disabled-custom';
    $disabled = 'disabled';
}

// Alt language
if( !isset( $options['translate_alt_lang'] ) ) {
    $wp_lang    = get_bloginfo('language');
    $alt_lang   = substr( $wp_lang, 0, 2 );
} else {
    $alt_lang   = $options['translate_alt_lang'];
}
?>

<div class="new-automatic-post-processing-tab">
    <div class="alert alert-custom alert-light-primary fade show mb-5" role="alert">
        <div class="alert-icon"><i class="flaticon-info"></i></div>
        <div class="alert-text">
            <strong><?php esc_html_e( 'Post-Processing Options', 'all-sources-images' ); ?></strong><br>
            <?php esc_html_e( 'Configure how images are processed after downloading. This includes naming conventions, image modifications, and metadata settings.', 'all-sources-images' ); ?>
        </div>
    </div>

    <form method="post" action="options.php" id="post-processing-form">
        <?php settings_fields( 'ASI-plugin-main-settings' ); ?>
        <input type="hidden" name="ASI_plugin_main_settings[_saving_tab]" value="post_processing">
        
        <table class="form-table">
        <!-- Image Naming Convention -->
        <tr valign="top" class="selected_image">
            <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Image Naming Convention', 'all-sources-images' ); ?></label>
            </th>
            <td class="chosen_title radio-inline">
                <label class="radio radio-outline radio-outline-2x radio-primary">
                    <input value="title" name="ASI_plugin_main_settings[image_filename]" type="radio" <?php echo( !empty( $options['image_filename']) && $options['image_filename'] == 'title' )? 'checked': ''; ?>>
                    <span></span> <?php esc_html_e( 'Title', 'all-sources-images' ); ?>
                </label><br/>
                <label class="radio radio-outline radio-outline-2x radio-primary">
                    <input value="date" name="ASI_plugin_main_settings[image_filename]" type="radio" <?php echo( !empty( $options['image_filename']) && $options['image_filename'] == 'date' )? 'checked': ''; ?>>
                    <span></span> <?php esc_html_e( 'Date', 'all-sources-images' ); ?>
                </label><br/>
                <label class="radio radio-outline radio-outline-2x radio-primary">
                    <input value="random" name="ASI_plugin_main_settings[image_filename]" type="radio" <?php echo( !empty( $options['image_filename']) && $options['image_filename'] == 'random' )? 'checked': ''; ?>>
                    <span></span> <?php esc_html_e( 'Random number', 'all-sources-images' ); ?>
                </label>
            </td>
        </tr>

        <!-- Overwrite Featured Images -->
        <tr valign="top">
            <th scope="row">
                <?php esc_html_e( 'Overwrite featured images', 'all-sources-images' ); ?>
            </th>
            <td class="checkbox-list">
                <label class="checkbox">
                    <input <?php echo( !empty( $options['rewrite_featured']) && $options['rewrite_featured'] == 'true' )? 'checked': ''; ?> name="ASI_plugin_main_settings[rewrite_featured]" type="checkbox" value="true">
                    <span></span> <?php esc_html_e( 'Overwrite', 'all-sources-images' ); ?>
                </label>
                <p class="description">
                    <?php esc_html_e( 'Warning: This option will overwrite existing featured images', 'all-sources-images' ); ?>
                </p>
            </td>
        </tr>

        <!-- Image Reuse -->
        <tr valign="top">
            <th scope="row">
                <?php esc_html_e( 'Image reuse', 'all-sources-images' ); ?>
            </th>
            <td class="checkbox-list">
                <label class="checkbox">
                    <input <?php echo( !empty( $options['image_reuse']) && $options['image_reuse'] == 'true' )? 'checked': ''; ?> name="ASI_plugin_main_settings[image_reuse]" type="checkbox" value="true">
                    <span></span> <?php esc_html_e( 'Enable', 'all-sources-images' ); ?>
                </label>
                <p class="description">
                    <?php esc_html_e( 'Check for existing images media before downloading new ones to prevent duplicates and save storage space. Reuse is based on filename.', 'all-sources-images' ); ?>
                </p>
            </td>
        </tr>

        <!-- Image Modifications -->
        <tr valign="top" class="shuffle_image">
            <th scope="row">
                <?php esc_html_e( 'Image Modifications', 'all-sources-images' ); ?>
            </th>
            <td class="checkbox-list">
                <label class="checkbox <?php echo $checkbox_disabled; ?>">
                    <input <?php echo( !empty( $options['image_flip']) && $options['image_flip'] == 'true' )? 'checked': ''; ?> name="ASI_plugin_main_settings[image_flip]" type="checkbox" value="true" <?php echo $disabled; ?>>
                    <span></span> <?php esc_html_e( 'Flip horizontally', 'all-sources-images' ); ?>
                </label>
                <label class="checkbox <?php echo $checkbox_disabled; ?>">
                    <input <?php echo( !empty( $options['image_crop']) && $options['image_crop'] == 'true' )? 'checked': ''; ?> name="ASI_plugin_main_settings[image_crop]" type="checkbox" value="true" <?php echo $disabled; ?>>
                    <span></span> <?php esc_html_e( 'Crop Image by 10%', 'all-sources-images' ); ?>
                </label>
                <?php if ( $disabled ) : ?>
                    <p class="description text-warning"><?php esc_html_e( 'Premium feature - upgrade to unlock', 'all-sources-images' ); ?></p>
                <?php endif; ?>
            </td>
        </tr>

        <tr>
            <td colspan="2"><hr/></td>
        </tr>

        <!-- Add ALT Attribute to Image -->
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

        <tr valign="top" class="show_alt" <?php echo(isset($options['enable_alt']) && ($options['enable_alt'] != 'enable') ? 'style="display:none;"': ''); ?>>
            <th scope="row">
                <?php esc_html_e( 'ALT Text Translation', 'all-sources-images' ); ?>
            </th>
            <td class="checkbox-list">
                <label class="checkbox">
                    <input name="ASI_plugin_main_settings[translate_alt]" type="checkbox" value="true" <?php echo( !empty( $options['translate_alt']) && $options['translate_alt'] == 'true' )? 'checked': ''; ?>><span></span> 
                    <?php esc_html_e( 'Translate alt text from english to', 'all-sources-images' ); ?>:
                </label>

                <select name="ASI_plugin_main_settings[translate_alt_lang]" class="form-control form-control-lg">
                    <?php
                    $country_choose = array(
                        __( 'German', 'all-sources-images' )    => 'de',
                        __( 'Spanish', 'all-sources-images' )   => 'es',
                        __( 'French', 'all-sources-images' )    => 'fr',
                        __( 'Italian', 'all-sources-images' )   => 'it',
                        __( 'Portuguese', 'all-sources-images' ) => 'pt-PT',
                        __( 'Dutch', 'all-sources-images' )     => 'nl',
                        __( 'Polish', 'all-sources-images' )    => 'pl',
                        __( 'Russian', 'all-sources-images' )   => 'ru',
                        __( 'Japanese', 'all-sources-images' )  => 'ja',
                        __( 'Chinese (Simplified)', 'all-sources-images' ) => 'zh-CN',
                        __( 'Korean', 'all-sources-images' )    => 'ko',
                        __( 'Arabic', 'all-sources-images' )    => 'ar',
                    );
                    ksort( $country_choose );

                    foreach( $country_choose as $name_country => $code_country ) {
                        $choose = ( $alt_lang == $code_country) ? 'selected="selected"': '';
                        echo '<option ' . esc_attr( $choose ) . ' value="' . esc_attr( $code_country ) . '">' . esc_html( $name_country ) . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <td colspan="2"><hr/></td>
        </tr>

        <!-- Add Caption Tag -->
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
    </table>
    
    <div class="d-flex justify-content-end mt-6">
        <?php submit_button( __( 'Save Changes', 'all-sources-images' ), 'btn btn-primary', 'submit', false ); ?>
    </div>
    </form>
</div>
