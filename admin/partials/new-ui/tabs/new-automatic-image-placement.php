<?php
/**
 * New UI - Image Placement Tab
 * This file handles the Image Placement configuration in the new admin UI
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current settings
$options_main = get_option( 'ASI_plugin_main_settings' );
$options_main = wp_parse_args( $options_main, $this->ASI_default_options_main_settings() );
$image_blocks = isset( $options_main['image_block'] ) ? $options_main['image_block'] : array();

// Get available image sizes
$image_sizes = get_intermediate_image_sizes();

// Get available image sources
$list_api_auto = $this->ASI_banks_name_auto();

// Pro feature check
$disabled = '';
$class_disabled = '';
$checkbox_disabled = '';
if ( function_exists( 'asi_freemius' ) && !asi_freemius()->is_premium() ) {
    $disabled = 'disabled';
    $class_disabled = 'disabled-option';
    $checkbox_disabled = 'checkbox-disabled';
}

// Calculate next block index
$blockIndex = count( $image_blocks ) + 1;
?>

<style>
/* Image Placement specific styles */
.image-placement-block {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}
.image-placement-block.template-block {
    display: none;
}
.block-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}
.block-header h4 {
    margin: 0;
    color: #333;
}
.form-row {
    margin-bottom: 15px;
}
.form-row label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}
.form-row .description {
    color: #666;
    font-size: 12px;
    margin-top: 5px;
}
.inline-content-fields {
    display: none;
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-top: 10px;
}
.inline-content-fields.visible {
    display: block;
}
.radio-group label {
    display: inline-flex;
    align-items: center;
    margin-right: 20px;
    cursor: pointer;
}
.radio-group label input[type="radio"] {
    margin-right: 5px;
}
.select-row {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}
.select-row select {
    min-width: 120px;
}
.btn-add-block {
    background: #28a745;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
}
.btn-add-block:hover {
    background: #218838;
}
.btn-delete-block {
    background: #dc3545;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 13px;
}
.btn-delete-block:hover {
    background: #c82333;
}
#image-blocks-container {
    margin-bottom: 20px;
}
</style>

<div class="card mb-5 mb-xl-10">
    <div class="card-header border-0">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0"><?php esc_html_e( 'Image Placement', 'all-sources-images' ); ?></h3>
        </div>
    </div>
    <div class="card-body border-top p-9">
        <p class="text-muted mb-6"><?php esc_html_e( 'Configure where and how images are placed in your posts. You can add multiple image locations.', 'all-sources-images' ); ?></p>
        
        <form method="post" action="options.php" id="image-placement-form">
            <?php settings_fields( 'ASI_plugin_main_settings_group' ); ?>
            
            <!-- Hidden Template Block (index 0) for cloning -->
            <div class="image-placement-block template-block image-block-0" data-block-index="0" style="display: none;">
                <div class="block-header">
                    <h4><?php esc_html_e( 'Image Location', 'all-sources-images' ); ?> #<span class="block-number">0</span></h4>
                    <button type="button" class="btn-delete-block"><?php esc_html_e( '[-] Delete', 'all-sources-images' ); ?></button>
                </div>
                
                <!-- Featured Image / Inline Content -->
                <div class="form-row image_location">
                    <label><?php esc_html_e( 'Image Location Type', 'all-sources-images' ); ?></label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="ASI_plugin_main_settings[image_block][0][image_location]" value="featured" checked>
                            <?php esc_html_e( 'Featured Image', 'all-sources-images' ); ?>
                        </label>
                        <label>
                            <input type="radio" name="ASI_plugin_main_settings[image_block][0][image_location]" value="custom">
                            <?php esc_html_e( 'Inline Content', 'all-sources-images' ); ?>
                        </label>
                    </div>
                    <p class="description"><?php esc_html_e( '"Inline content" allows you to generate the image anywhere in the content', 'all-sources-images' ); ?></p>
                </div>
                
                <!-- Inline Content Fields (hidden by default) -->
                <div class="inline-content-fields section_custom_image_position">
                    <div class="form-row">
                        <label><?php esc_html_e( 'Image Position', 'all-sources-images' ); ?></label>
                        <div class="select-row">
                            <span><?php esc_html_e( 'Insert', 'all-sources-images' ); ?></span>
                            <select name="ASI_plugin_main_settings[image_block][0][image_custom_location_placement]" class="form-control">
                                <option value="before"><?php esc_html_e( 'Before', 'all-sources-images' ); ?></option>
                                <option value="after"><?php esc_html_e( 'After', 'all-sources-images' ); ?></option>
                            </select>
                            <span><?php esc_html_e( 'the', 'all-sources-images' ); ?></span>
                            <select name="ASI_plugin_main_settings[image_block][0][image_custom_location_position]" class="form-control">
                                <option value="1"><?php esc_html_e( 'First', 'all-sources-images' ); ?></option>
                                <option value="2"><?php esc_html_e( 'Second', 'all-sources-images' ); ?></option>
                                <option value="3"><?php esc_html_e( 'Third', 'all-sources-images' ); ?></option>
                                <option value="4"><?php esc_html_e( 'Fourth', 'all-sources-images' ); ?></option>
                                <option value="5"><?php esc_html_e( 'Fifth', 'all-sources-images' ); ?></option>
                                <option value="6"><?php esc_html_e( 'Sixth', 'all-sources-images' ); ?></option>
                                <option value="7"><?php esc_html_e( 'Seventh', 'all-sources-images' ); ?></option>
                                <option value="8"><?php esc_html_e( 'Eighth', 'all-sources-images' ); ?></option>
                                <option value="9"><?php esc_html_e( 'Ninth', 'all-sources-images' ); ?></option>
                                <option value="10"><?php esc_html_e( 'Tenth', 'all-sources-images' ); ?></option>
                                <option value="last"><?php esc_html_e( 'Last', 'all-sources-images' ); ?></option>
                            </select>
                            <select name="ASI_plugin_main_settings[image_block][0][image_custom_location_tag]" class="form-control">
                                <option value="p"><?php esc_html_e( 'paragraph (p)', 'all-sources-images' ); ?></option>
                                <option value="h2">h2</option>
                                <option value="h3">h3</option>
                                <option value="h4">h4</option>
                                <option value="h5">h5</option>
                                <option value="h6">h6</option>
                                <option value="div">div</option>
                                <option value="a"><?php esc_html_e( 'link (a)', 'all-sources-images' ); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <label><?php esc_html_e( 'Image Size', 'all-sources-images' ); ?></label>
                        <select name="ASI_plugin_main_settings[image_block][0][image_custom_image_size]" class="form-control" style="max-width: 200px;">
                            <?php foreach ( $image_sizes as $image_size ) : ?>
                                <option value="<?php echo esc_attr( $image_size ); ?>"><?php echo esc_html( $image_size ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Image Source -->
                <div class="form-row">
                    <label><?php esc_html_e( 'Image Source', 'all-sources-images' ); ?></label>
                    <select name="ASI_plugin_main_settings[image_block][0][api_chosen]" class="form-control" style="max-width: 300px;">
                        <?php foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) : ?>
                            <?php if ( true === $api_auto_id[1] ) : ?>
                                <option value="<?php echo esc_attr( $api_auto_id[0] ); ?>"><?php echo esc_html( $api_auto_name ); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Second Image Source -->
                <div class="form-row">
                    <label><?php esc_html_e( 'Second Image Source (Fallback)', 'all-sources-images' ); ?></label>
                    <select name="ASI_plugin_main_settings[image_block][0][api_chosen_2]" class="form-control" style="max-width: 300px;">
                        <option value="none"><?php esc_html_e( 'None', 'all-sources-images' ); ?></option>
                        <?php foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) : ?>
                            <?php if ( true === $api_auto_id[1] ) : ?>
                                <option value="<?php echo esc_attr( $api_auto_id[0] ); ?>"><?php echo esc_html( $api_auto_name ); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Search Based On -->
                <div class="form-row">
                    <label><?php esc_html_e( 'Search Based On', 'all-sources-images' ); ?></label>
                    <select name="ASI_plugin_main_settings[image_block][0][based_on]" class="form-control based-on-select" style="max-width: 300px;">
                        <option value="title"><?php esc_html_e( 'Title', 'all-sources-images' ); ?></option>
                        <option value="text_analyser"><?php esc_html_e( 'Text Analyzer: Full text', 'all-sources-images' ); ?></option>
                        <option value="tags" <?php echo $disabled; ?>><?php esc_html_e( 'Tags', 'all-sources-images' ); ?></option>
                        <option value="categories" <?php echo $disabled; ?>><?php esc_html_e( 'Categories', 'all-sources-images' ); ?></option>
                        <option value="custom_field" <?php echo $disabled; ?>><?php esc_html_e( 'Custom Field', 'all-sources-images' ); ?></option>
                        <option value="openai_extractor" <?php echo $disabled; ?>><?php esc_html_e( 'OpenAI Keyword Extractor', 'all-sources-images' ); ?></option>
                    </select>
                </div>
                
                <!-- Title Options (shown when based_on = title) -->
                <div class="form-row section_title">
                    <label><?php esc_html_e( 'Title Selection', 'all-sources-images' ); ?></label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="ASI_plugin_main_settings[image_block][0][title_selection]" value="full_title" checked>
                            <?php esc_html_e( 'Full title', 'all-sources-images' ); ?>
                        </label>
                        <label>
                            <input type="radio" name="ASI_plugin_main_settings[image_block][0][title_selection]" value="cut_title">
                            <?php esc_html_e( 'Specific Part', 'all-sources-images' ); ?>:
                            <input type="number" name="ASI_plugin_main_settings[image_block][0][title_length]" min="1" value="3" class="form-control" style="width: 60px; margin-left: 5px;">
                            <span style="margin-left: 5px;"><?php esc_html_e( 'first words', 'all-sources-images' ); ?></span>
                        </label>
                    </div>
                </div>
                
                <!-- Image Selection -->
                <div class="form-row">
                    <label><?php esc_html_e( 'Image Selection', 'all-sources-images' ); ?></label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="ASI_plugin_main_settings[image_block][0][selected_image]" value="first_result" checked>
                            <?php esc_html_e( 'First result', 'all-sources-images' ); ?>
                        </label>
                        <label class="<?php echo $class_disabled; ?>">
                            <input type="radio" name="ASI_plugin_main_settings[image_block][0][selected_image]" value="random_result" <?php echo $disabled; ?>>
                            <?php esc_html_e( 'Random result', 'all-sources-images' ); ?>
                        </label>
                    </div>
                </div>
            </div>
            <!-- End Hidden Template Block -->
            
            <!-- Container for saved and new blocks -->
            <div id="image-blocks-container">
                <?php if ( !empty( $image_blocks ) ) : ?>
                    <?php $displayIndex = 1; ?>
                    <?php foreach ( $image_blocks as $index => $block ) : ?>
                        <?php 
                        $is_inline = ( isset( $block['image_location'] ) && $block['image_location'] === 'custom' );
                        ?>
                        <div class="image-placement-block image-block-<?php echo $displayIndex; ?>" data-block-index="<?php echo $displayIndex; ?>">
                            <div class="block-header">
                                <h4><?php esc_html_e( 'Image Location', 'all-sources-images' ); ?> #<span class="block-number"><?php echo $displayIndex; ?></span></h4>
                                <button type="button" class="btn-delete-block"><?php esc_html_e( '[-] Delete', 'all-sources-images' ); ?></button>
                            </div>
                            
                            <!-- Featured Image / Inline Content -->
                            <div class="form-row image_location">
                                <label><?php esc_html_e( 'Image Location Type', 'all-sources-images' ); ?></label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][image_location]" value="featured" <?php checked( isset( $block['image_location'] ) ? $block['image_location'] : 'featured', 'featured' ); ?>>
                                        <?php esc_html_e( 'Featured Image', 'all-sources-images' ); ?>
                                    </label>
                                    <label>
                                        <input type="radio" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][image_location]" value="custom" <?php checked( isset( $block['image_location'] ) ? $block['image_location'] : '', 'custom' ); ?>>
                                        <?php esc_html_e( 'Inline Content', 'all-sources-images' ); ?>
                                    </label>
                                </div>
                                <p class="description"><?php esc_html_e( '"Inline content" allows you to generate the image anywhere in the content', 'all-sources-images' ); ?></p>
                            </div>
                            
                            <!-- Inline Content Fields -->
                            <div class="inline-content-fields section_custom_image_position <?php echo $is_inline ? 'visible' : ''; ?>">
                                <div class="form-row">
                                    <label><?php esc_html_e( 'Image Position', 'all-sources-images' ); ?></label>
                                    <div class="select-row">
                                        <span><?php esc_html_e( 'Insert', 'all-sources-images' ); ?></span>
                                        <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][image_custom_location_placement]" class="form-control">
                                            <option value="before" <?php selected( isset( $block['image_custom_location_placement'] ) ? $block['image_custom_location_placement'] : '', 'before' ); ?>><?php esc_html_e( 'Before', 'all-sources-images' ); ?></option>
                                            <option value="after" <?php selected( isset( $block['image_custom_location_placement'] ) ? $block['image_custom_location_placement'] : '', 'after' ); ?>><?php esc_html_e( 'After', 'all-sources-images' ); ?></option>
                                        </select>
                                        <span><?php esc_html_e( 'the', 'all-sources-images' ); ?></span>
                                        <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][image_custom_location_position]" class="form-control">
                                            <?php 
                                            $positions = array(
                                                '1' => __( 'First', 'all-sources-images' ),
                                                '2' => __( 'Second', 'all-sources-images' ),
                                                '3' => __( 'Third', 'all-sources-images' ),
                                                '4' => __( 'Fourth', 'all-sources-images' ),
                                                '5' => __( 'Fifth', 'all-sources-images' ),
                                                '6' => __( 'Sixth', 'all-sources-images' ),
                                                '7' => __( 'Seventh', 'all-sources-images' ),
                                                '8' => __( 'Eighth', 'all-sources-images' ),
                                                '9' => __( 'Ninth', 'all-sources-images' ),
                                                '10' => __( 'Tenth', 'all-sources-images' ),
                                                'last' => __( 'Last', 'all-sources-images' ),
                                            );
                                            foreach ( $positions as $val => $label ) : ?>
                                                <option value="<?php echo esc_attr( $val ); ?>" <?php selected( isset( $block['image_custom_location_position'] ) ? $block['image_custom_location_position'] : '', $val ); ?>><?php echo esc_html( $label ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][image_custom_location_tag]" class="form-control">
                                            <?php 
                                            $tags = array( 'p' => __( 'paragraph (p)', 'all-sources-images' ), 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4', 'h5' => 'h5', 'h6' => 'h6', 'div' => 'div', 'a' => __( 'link (a)', 'all-sources-images' ) );
                                            foreach ( $tags as $val => $label ) : ?>
                                                <option value="<?php echo esc_attr( $val ); ?>" <?php selected( isset( $block['image_custom_location_tag'] ) ? $block['image_custom_location_tag'] : '', $val ); ?>><?php echo esc_html( $label ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <label><?php esc_html_e( 'Image Size', 'all-sources-images' ); ?></label>
                                    <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][image_custom_image_size]" class="form-control" style="max-width: 200px;">
                                        <?php foreach ( $image_sizes as $image_size ) : ?>
                                            <option value="<?php echo esc_attr( $image_size ); ?>" <?php selected( isset( $block['image_custom_image_size'] ) ? $block['image_custom_image_size'] : '', $image_size ); ?>><?php echo esc_html( $image_size ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Image Source -->
                            <div class="form-row">
                                <label><?php esc_html_e( 'Image Source', 'all-sources-images' ); ?></label>
                                <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][api_chosen]" class="form-control" style="max-width: 300px;">
                                    <?php foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) : ?>
                                        <?php if ( true === $api_auto_id[1] ) : ?>
                                            <option value="<?php echo esc_attr( $api_auto_id[0] ); ?>" <?php selected( isset( $block['api_chosen'] ) ? $block['api_chosen'] : '', $api_auto_id[0] ); ?>><?php echo esc_html( $api_auto_name ); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Second Image Source -->
                            <div class="form-row">
                                <label><?php esc_html_e( 'Second Image Source (Fallback)', 'all-sources-images' ); ?></label>
                                <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][api_chosen_2]" class="form-control" style="max-width: 300px;">
                                    <option value="none"><?php esc_html_e( 'None', 'all-sources-images' ); ?></option>
                                    <?php foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) : ?>
                                        <?php if ( true === $api_auto_id[1] ) : ?>
                                            <option value="<?php echo esc_attr( $api_auto_id[0] ); ?>" <?php selected( isset( $block['api_chosen_2'] ) ? $block['api_chosen_2'] : '', $api_auto_id[0] ); ?>><?php echo esc_html( $api_auto_name ); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Search Based On -->
                            <div class="form-row">
                                <label><?php esc_html_e( 'Search Based On', 'all-sources-images' ); ?></label>
                                <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][based_on]" class="form-control based-on-select" style="max-width: 300px;">
                                    <option value="title" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'title' ); ?>><?php esc_html_e( 'Title', 'all-sources-images' ); ?></option>
                                    <option value="text_analyser" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'text_analyser' ); ?>><?php esc_html_e( 'Text Analyzer: Full text', 'all-sources-images' ); ?></option>
                                    <option value="tags" <?php echo $disabled; ?> <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'tags' ); ?>><?php esc_html_e( 'Tags', 'all-sources-images' ); ?></option>
                                    <option value="categories" <?php echo $disabled; ?> <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'categories' ); ?>><?php esc_html_e( 'Categories', 'all-sources-images' ); ?></option>
                                    <option value="custom_field" <?php echo $disabled; ?> <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'custom_field' ); ?>><?php esc_html_e( 'Custom Field', 'all-sources-images' ); ?></option>
                                    <option value="openai_extractor" <?php echo $disabled; ?> <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'openai_extractor' ); ?>><?php esc_html_e( 'OpenAI Keyword Extractor', 'all-sources-images' ); ?></option>
                                </select>
                            </div>
                            
                            <!-- Title Options -->
                            <div class="form-row section_title" style="<?php echo ( isset( $block['based_on'] ) && $block['based_on'] !== 'title' ) ? 'display:none;' : ''; ?>">
                                <label><?php esc_html_e( 'Title Selection', 'all-sources-images' ); ?></label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][title_selection]" value="full_title" <?php checked( isset( $block['title_selection'] ) ? $block['title_selection'] : 'full_title', 'full_title' ); ?>>
                                        <?php esc_html_e( 'Full title', 'all-sources-images' ); ?>
                                    </label>
                                    <label>
                                        <input type="radio" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][title_selection]" value="cut_title" <?php checked( isset( $block['title_selection'] ) ? $block['title_selection'] : '', 'cut_title' ); ?>>
                                        <?php esc_html_e( 'Specific Part', 'all-sources-images' ); ?>:
                                        <input type="number" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][title_length]" min="1" value="<?php echo isset( $block['title_length'] ) ? esc_attr( $block['title_length'] ) : '3'; ?>" class="form-control" style="width: 60px; margin-left: 5px;">
                                        <span style="margin-left: 5px;"><?php esc_html_e( 'first words', 'all-sources-images' ); ?></span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Image Selection -->
                            <div class="form-row">
                                <label><?php esc_html_e( 'Image Selection', 'all-sources-images' ); ?></label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][selected_image]" value="first_result" <?php checked( isset( $block['selected_image'] ) ? $block['selected_image'] : 'first_result', 'first_result' ); ?>>
                                        <?php esc_html_e( 'First result', 'all-sources-images' ); ?>
                                    </label>
                                    <label class="<?php echo $class_disabled; ?>">
                                        <input type="radio" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][selected_image]" value="random_result" <?php echo $disabled; ?> <?php checked( isset( $block['selected_image'] ) ? $block['selected_image'] : '', 'random_result' ); ?>>
                                        <?php esc_html_e( 'Random result', 'all-sources-images' ); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php $displayIndex++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Add Block Button -->
            <div class="mb-4">
                <button type="button" id="add-image-block" class="btn-add-block"><?php esc_html_e( '[+] Add an Image Location', 'all-sources-images' ); ?></button>
            </div>
            
            <!-- Submit Button -->
            <div class="d-flex justify-content-end">
                <?php submit_button( __( 'Save Changes', 'all-sources-images' ), 'btn btn-primary', 'submit', false ); ?>
            </div>
        </form>
    </div>
</div>

<script>
(function($) {
    'use strict';
    
    // Initialize block index
    var blockIndex = <?php echo $blockIndex; ?>;
    
    $(document).ready(function() {
        
        // Toggle inline content fields when radio changes
        $(document).on('change', '.image_location input[type="radio"]', function() {
            var $block = $(this).closest('.image-placement-block');
            var $inlineFields = $block.find('.inline-content-fields');
            
            if ($(this).val() === 'custom') {
                $inlineFields.addClass('visible');
            } else {
                $inlineFields.removeClass('visible');
            }
        });
        
        // Add new block
        $('#add-image-block').on('click', function() {
            var $template = $('.image-placement-block.template-block').first();
            var $newBlock = $template.clone();
            
            // Update block index
            $newBlock.removeClass('template-block image-block-0');
            $newBlock.addClass('image-block-' + blockIndex);
            $newBlock.attr('data-block-index', blockIndex);
            $newBlock.find('.block-number').text(blockIndex);
            
            // Update all input names
            $newBlock.find('[name*="[image_block][0]"]').each(function() {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace('[image_block][0]', '[image_block][' + blockIndex + ']'));
            });
            
            // Show the block
            $newBlock.css('display', 'block');
            
            // Append to container
            $('#image-blocks-container').append($newBlock);
            
            // Increment block index for next addition
            blockIndex++;
            
            // Renumber all visible blocks
            renumberBlocks();
        });
        
        // Delete block
        $(document).on('click', '.btn-delete-block', function() {
            var $block = $(this).closest('.image-placement-block');
            
            // Don't delete the template
            if ($block.hasClass('template-block')) {
                return;
            }
            
            $block.remove();
            renumberBlocks();
        });
        
        // Show/hide title options based on "based_on" selection
        $(document).on('change', '.based-on-select', function() {
            var $block = $(this).closest('.image-placement-block');
            var $titleSection = $block.find('.section_title');
            
            if ($(this).val() === 'title') {
                $titleSection.show();
            } else {
                $titleSection.hide();
            }
        });
        
        // Renumber blocks after deletion
        function renumberBlocks() {
            var displayNum = 1;
            $('#image-blocks-container .image-placement-block:not(.template-block)').each(function() {
                $(this).find('.block-number').text(displayNum);
                displayNum++;
            });
        }
        
    });
    
})(jQuery);
</script>
