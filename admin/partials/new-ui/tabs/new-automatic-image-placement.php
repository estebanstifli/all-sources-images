<?php
/**
 * New Automatic - Image Placement Tab
 * 
 * This tab handles image location configuration (Featured vs Inline)
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

$image_blocks = isset( $options['image_block'] ) ? $options['image_block'] : array();

// Banks for Automatic Bulk
$list_api_auto = $this->ASI_banks_name_auto();

// Get image sizes
$image_sizes = get_intermediate_image_sizes();

// Premium check
$checkbox_disabled = '';
$disabled = '';
if ( function_exists( 'asi_freemius' ) && !asi_freemius()->is_premium() ) {
    $checkbox_disabled = 'disabled-custom';
    $disabled = 'disabled';
}
?>

<div class="new-automatic-image-placement-tab">
    <div class="alert alert-custom alert-light-primary fade show mb-5" role="alert">
        <div class="alert-icon"><i class="flaticon-info"></i></div>
        <div class="alert-text">
            <strong><?php esc_html_e( 'Image Placement', 'all-sources-images' ); ?></strong><br>
            <?php esc_html_e( 'Configure where images should be placed in your posts. You can set featured images or insert images at specific positions within the content.', 'all-sources-images' ); ?>
        </div>
    </div>

    <table class="form-table">
        <?php
        // Display saved blocks
        if ( !empty( $image_blocks ) ) {
            $blockIndex = 1;
            foreach ( $image_blocks as $index => $block ) {
                $class_inside_image = '';
                $class_field_image = '';
                $class_analyzer = '';
                if ( 'custom' != $block['image_location'] ) {
                    $class_inside_image = 'hidden';
                    $class_analyzer = 'hidden';
                }
                if ( !in_array( $block['image_location'], ['cmb2', 'acf', 'metaboxio'] ) ) {
                    $class_field_image = 'hidden';
                }
                ?>
                <!-- Template for displaying saved blocks -->
                <tr valign="top" class="image-location-template image-block-<?php echo $blockIndex; ?> top-add-block-img">
                    <th scope="row">
                        <?php esc_html_e( 'Featured Image / Inline Content', 'all-sources-images' ); ?>
                    </th>
                    <td class="image_location radio-list">
                        <label class="radio radio-outline radio-outline-2x radio-primary">
                            <input value="featured" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][image_location]" type="radio" <?php checked( $block['image_location'], 'featured' ); ?>>
                            <span></span> <?php esc_html_e( 'Featured Image', 'all-sources-images' ); ?>
                        </label>
                        <label class="radio radio-outline radio-outline-2x radio-primary">
                            <input value="custom" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][image_location]" type="radio" <?php checked( $block['image_location'], 'custom' ); ?>>
                            <span></span> <?php esc_html_e( 'Inline content', 'all-sources-images' ); ?>
                        </label>
                        <p class="description"><i><?php esc_html_e( '"Inline content" allows you to generate the image anywhere in the content', 'all-sources-images' ); ?></i></p>
                    </td>
                </tr>

                <tr valign="top" class="section_custom_image_position image-location-template <?php echo $class_inside_image; ?> image-inside-content image-block-<?php echo $blockIndex; ?> mid-add-block-img">
                    <th scope="row">
                        <label for="hseparator"><?php esc_html_e( 'Image position', 'all-sources-images' ); ?></label>
                    </th>
                    <td class="custom_image_location" valign="top">
                        <label><?php esc_html_e( 'Insert', 'all-sources-images' ); ?>
                            <select name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][image_custom_location_placement]" class="select-custom-location form-control">
                                <option value="before" <?php selected( ( isset( $block['image_custom_location_placement'] ) ? $block['image_custom_location_placement'] : '' ), 'before' ); ?>><?php esc_html_e( 'Before', 'all-sources-images' ); ?></option>
                                <option value="after" <?php selected( ( isset( $block['image_custom_location_placement'] ) ? $block['image_custom_location_placement'] : '' ), 'after' ); ?>><?php esc_html_e( 'After', 'all-sources-images' ); ?></option>
                            </select> 
                            <?php esc_html_e( 'the', 'all-sources-images' ); ?>
                            <select name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][image_custom_location_position]" class="select-custom-location form-control">
                                <option value="1" <?php selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '1' ); ?>><?php esc_html_e( 'First', 'all-sources-images' ); ?></option>
                                <option value="2" <?php selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '2' ); ?>><?php esc_html_e( 'Second', 'all-sources-images' ); ?></option>
                                <option value="3" <?php selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '3' ); ?>><?php esc_html_e( 'Third', 'all-sources-images' ); ?></option>
                                <option value="4" <?php selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '4' ); ?>><?php esc_html_e( 'Fourth', 'all-sources-images' ); ?></option>
                                <option value="5" <?php selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), '5' ); ?>><?php esc_html_e( 'Fifth', 'all-sources-images' ); ?></option>
                                <option value="last" <?php selected( ( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '' ), 'last' ); ?>><?php esc_html_e( 'Last', 'all-sources-images' ); ?></option>
                            </select>
                            <select name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][image_custom_location_tag]" class="select-custom-location form-control">
                                <option value="p" <?php selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'p' ); ?>><?php esc_html_e( 'paragraph (p)', 'all-sources-images' ); ?></option>
                                <option value="h2" <?php selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'h2' ); ?>>h2</option>
                                <option value="h3" <?php selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'h3' ); ?>>h3</option>
                                <option value="h4" <?php selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'h4' ); ?>>h4</option>
                                <option value="div" <?php selected( ( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '' ), 'div' ); ?>>div</option>
                            </select>
                        </label>
                    </td>
                </tr>

                <tr valign="top" class="section_custom_image_size image-location-template image-inside-content <?php echo $class_inside_image; ?> image-block-<?php echo $blockIndex; ?> mid-add-block-img">
                    <th scope="row">
                        <label for="hseparator"><?php esc_html_e( 'Image size', 'all-sources-images' ); ?></label>
                    </th>
                    <td class="custom_image_size" valign="top">
                        <label>
                            <select name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][image_custom_image_size]" class="select-custom-location form-control">
                                <?php foreach ( $image_sizes as $image_size ) { ?>
                                    <option value="<?php echo $image_size; ?>" <?php selected( ( isset( $block['image_custom_image_size'] ) ? $block['image_custom_image_size'] : '' ), $image_size ); ?>>
                                        <?php echo $image_size; ?>
                                    </option>
                                <?php } ?>
                            </select> 
                        </label>
                    </td>
                </tr>

                <tr valign="top" class="section_custom_image_bank image-block-<?php echo $blockIndex; ?> mid-add-block-img">
                    <th scope="row">
                        <label for="hseparator"><?php esc_html_e( 'Image Source', 'all-sources-images' ); ?></label>
                    </th>
                    <td class="custom_image_size" valign="top">
                        <label>
                            <select name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][api_chosen]" class="select-custom-location form-control">
                                <?php foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) {
                                    if ( true === $api_auto_id[1] ) {
                                        $api_disabled = '';
                                    } else {
                                        $api_disabled = 'disabled';
                                    }
                                    ?>
                                    <option <?php echo $api_disabled; ?> value="<?php echo $api_auto_id[0]; ?>" <?php echo ( isset( $block['api_chosen'] ) ? selected( $block['api_chosen'], $api_auto_id[0], false ) : '' ); ?>><?php echo $api_auto_name; ?></option>
                                <?php } ?>
                            </select> 
                        </label>
                    </td>
                </tr>

                <tr valign="top" class="section_custom_image_bank image-block-<?php echo $blockIndex; ?> mid-add-block-img">
                    <th scope="row">
                        <label for="hseparator"><?php esc_html_e( 'Second Image Source', 'all-sources-images' ); ?></label>
                    </th>
                    <td class="custom_image_size" valign="top">
                        <label>
                            <select name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][api_chosen_2]" class="select-custom-location form-control">
                                <option value="none"><?php esc_html_e( 'None', 'all-sources-images' ); ?></option>
                                <?php foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) {
                                    if ( true === $api_auto_id[1] ) {
                                        $api_disabled = '';
                                    } else {
                                        $api_disabled = 'disabled';
                                    }
                                    ?>
                                    <option <?php echo $api_disabled; ?> value="<?php echo $api_auto_id[0]; ?>" <?php echo ( isset( $block['api_chosen_2'] ) ? selected( $block['api_chosen_2'], $api_auto_id[0], false ) : '' ); ?>><?php echo $api_auto_name; ?></option>
                                <?php } ?>
                            </select> 
                            <i><?php esc_html_e( 'Second Image Source, just in case the first one doesn\'t work', 'all-sources-images' ); ?></i>
                        </label>
                    </td>
                </tr>

                <!-- Search Based On -->
                <tr valign="top" class="section_basedon image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img">
                    <th scope="row">
                        <label for="hseparator"><?php esc_html_e( 'Search Based on', 'all-sources-images' ); ?></label>
                    </th>
                    <td class="based_on radio-list">
                        <select name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][based_on]" class="select-custom-location form-control">
                            <option value="title" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'title'); ?>><?php esc_html_e( 'Title', 'all-sources-images' ); ?></option>
                            <option value="text_analyser" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'text_analyser'); ?>><?php esc_html_e( 'Text Analyzer: Full text', 'all-sources-images' ); ?></option>
                            <option value="tags" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'tags'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'Tags', 'all-sources-images' ); ?></option>
                            <option value="categories" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'categories'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'Categories', 'all-sources-images' ); ?></option>
                            <option value="custom_field" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'custom_field'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'Custom Field', 'all-sources-images' ); ?></option>
                        </select>
                    </td>
                </tr>

                <!-- Title Selection -->
                <tr valign="top" class="section_title image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo((isset($block['based_on']) && $block['based_on'] != 'title') ? 'style="display:none;"': ''); ?>>
                    <th scope="row">
                        <label for="hseparator"><?php esc_html_e( 'Title', 'all-sources-images' ); ?></label>
                    </th>
                    <td class="chosen_title radio-inline">
                        <label class="radio radio-outline radio-outline-2x radio-primary"><input value="full_title" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][title_selection]" type="radio" <?php echo( !empty( $block['title_selection']) && $block['title_selection'] == 'full_title' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Full title', 'all-sources-images' ); ?></label><br/>
                        <label class="radio radio-outline radio-outline-2x radio-primary"><input value="cut_title" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][title_selection]" type="radio" <?php echo( !empty( $block['title_selection']) && $block['title_selection'] == 'cut_title' )? 'checked': ''; ?>><span></span> <?php esc_html_e( 'Specific Part', 'all-sources-images' ); ?> : </label>
                        <input type="number" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][title_length]" min="1" class="col-lg-4 col-md-9 col-sm-12 form-control length_cut_title" value="<?php echo( isset( $block['title_length'] ) && !empty( $block['title_length']) )? (int)$block['title_length']: '3'; ?>"> <i><?php esc_html_e( 'first words of the title', 'all-sources-images' ); ?></i>
                    </td>
                </tr>

                <!-- Button to remove a block -->
                <tr valign="top" class="image-location-template image-block-<?php echo $blockIndex; ?> bottom-add-block-img">
                    <td colspan="2">
                        <button type="button" class="btn btn-sm font-weight-bolder btn-light-danger remove-block-btn" style="text-decoration: none;">[-] <?php esc_html_e( 'Delete', 'all-sources-images' ); ?></button>
                    </td>
                </tr>

                <tr class="image-location-template image-block-<?php echo $blockIndex; ?>">
                    <td colspan="2">
                        <hr/>
                    </td>
                </tr>
                <?php
                $blockIndex++;
            }
        } else {
            // Default empty block
            ?>
            <tr valign="top" class="image-location-template image-block-1 top-add-block-img">
                <th scope="row">
                    <?php esc_html_e( 'Featured Image / Inline Content', 'all-sources-images' ); ?>
                </th>
                <td class="image_location radio-list">
                    <label class="radio radio-outline radio-outline-2x radio-primary">
                        <input value="featured" name="ASI_plugin_main_settings[image_block][1][image_location]" type="radio" checked>
                        <span></span> <?php esc_html_e( 'Featured Image', 'all-sources-images' ); ?>
                    </label>
                    <label class="radio radio-outline radio-outline-2x radio-primary">
                        <input value="custom" name="ASI_plugin_main_settings[image_block][1][image_location]" type="radio">
                        <span></span> <?php esc_html_e( 'Inline content', 'all-sources-images' ); ?>
                    </label>
                    <p class="description"><i><?php esc_html_e( '"Inline content" allows you to generate the image anywhere in the content', 'all-sources-images' ); ?></i></p>
                </td>
            </tr>

            <tr valign="top" class="section_basedon image-location-template image-block-1 mid-add-block-img">
                <th scope="row">
                    <label for="hseparator"><?php esc_html_e( 'Search Based on', 'all-sources-images' ); ?></label>
                </th>
                <td class="based_on radio-list">
                    <select name="ASI_plugin_main_settings[image_block][1][based_on]" class="select-custom-location form-control">
                        <option value="title" selected><?php esc_html_e( 'Title', 'all-sources-images' ); ?></option>
                        <option value="text_analyser"><?php esc_html_e( 'Text Analyzer: Full text', 'all-sources-images' ); ?></option>
                    </select>
                </td>
            </tr>

            <tr class="image-location-template image-block-1">
                <td colspan="2">
                    <hr/>
                </td>
            </tr>
            <?php
        }
        ?>

        <!-- Button to add a new block dynamically -->
        <tr class="cloneBlock">
            <td colspan="2">
                <a href="#" class="btn font-weight-bolder btn-light-primary text-uppercase py-4 px-6" style="text-decoration: none;" id="add-image-btn">
                    [+] <?php esc_html_e( 'Add an image location', 'all-sources-images' ); ?>
                </a>
            </td>
        </tr>
    </table>
</div>
