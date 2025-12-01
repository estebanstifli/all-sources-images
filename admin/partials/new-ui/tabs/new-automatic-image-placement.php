<?php
/**
 * New UI - Image Placement Tab
 * This file handles the Image Placement configuration in the new admin UI
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current settings - use raw option first to see what's actually stored
$raw_options = get_option( 'ASI_plugin_main_settings' );
$options_main = wp_parse_args( $raw_options, $this->ASI_default_options_main_settings() );

// Get image_blocks from the RAW options first (what's actually in DB)
// If empty or not set, fall back to defaults
if ( isset( $raw_options['image_block'] ) && is_array( $raw_options['image_block'] ) && !empty( $raw_options['image_block'] ) ) {
    $raw_image_blocks = $raw_options['image_block'];
} else {
    $raw_image_blocks = isset( $options_main['image_block'] ) ? $options_main['image_block'] : array();
}

// Re-index image_blocks to use consecutive indices starting from 1
// This fixes issues where blocks had non-consecutive indices (e.g., 2, 5, 7)
$image_blocks = array();
$new_index = 1;
foreach ( $raw_image_blocks as $block ) {
    if ( is_array( $block ) ) {
        $image_blocks[ $new_index ] = $block;
        $new_index++;
    }
}

// Debug: uncomment to see what's stored
// error_log( 'ASI Image Blocks Raw: ' . print_r( $raw_image_blocks, true ) );
// error_log( 'ASI Image Blocks Re-indexed: ' . print_r( $image_blocks, true ) );

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

// Calculate next block index - after the last re-indexed block
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
/* Search Based On conditional fields */
.based-on-fields {
    display: none;
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-top: 10px;
}
.based-on-fields.visible {
    display: block;
}
/* Help text styling */
.based-on-help-text {
    padding: 12px 15px;
    border-radius: 5px;
    margin-top: 10px;
    font-size: 13px;
    line-height: 1.5;
    border-left: 4px solid;
}
.based-on-help-text.help-title { background-color: #e8f5e9; border-left-color: #4caf50; }
.based-on-help-text.help-text_analyser { background-color: #e3f2fd; border-left-color: #2196f3; }
.based-on-help-text.help-text_analyser_previous_paragraph { background-color: #f3e5f5; border-left-color: #9c27b0; }
.based-on-help-text.help-text_analyser_next_paragraph { background-color: #fff3e0; border-left-color: #ff9800; }
.based-on-help-text.help-tags { background-color: #fce4ec; border-left-color: #e91e63; }
.based-on-help-text.help-categories { background-color: #fff8e1; border-left-color: #ffc107; }
.based-on-help-text.help-custom_field { background-color: #e0f2f1; border-left-color: #009688; }
.based-on-help-text.help-custom_request { background-color: #f1f8e9; border-left-color: #8bc34a; }
.based-on-help-text.help-openai_extractor { background-color: #ede7f6; border-left-color: #673ab7; }
.based-on-help-text.help-ai_image_prompt { background-color: #e1f5fe; border-left-color: #03a9f4; }
.based-on-help-text i { margin-right: 8px; }
</style>

<div class="card mb-5 mb-xl-10">
    <div class="card-header border-0">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0"><?php esc_html_e( 'Image Placement', 'all-sources-images' ); ?></h3>
        </div>
    </div>
    <div class="card-body border-top p-9">
        <p class="text-muted mb-6"><?php esc_html_e( 'Configure where and how images are placed in your posts. You can add multiple image locations.', 'all-sources-images' ); ?></p>
        
        <!-- Hidden Template Block - OUTSIDE the form to prevent submission -->
        <div id="template-container" style="display: none;">
            <div class="image-placement-block template-block" data-block-index="0">
                <div class="block-header">
                    <h4><?php esc_html_e( 'Image Location', 'all-sources-images' ); ?> #<span class="block-number">0</span></h4>
                    <button type="button" class="btn-delete-block"><?php esc_html_e( '[-] Delete', 'all-sources-images' ); ?></button>
                </div>
                
                <!-- Featured Image / Inline Content -->
                <div class="form-row image_location">
                    <label><?php esc_html_e( 'Image Location Type', 'all-sources-images' ); ?></label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" data-name-template="ASI_plugin_main_settings[image_block][__INDEX__][image_location]" value="featured" checked>
                            <?php esc_html_e( 'Featured Image', 'all-sources-images' ); ?>
                        </label>
                        <label>
                            <input type="radio" data-name-template="ASI_plugin_main_settings[image_block][__INDEX__][image_location]" value="custom">
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
                    <select name="ASI_plugin_main_settings[image_block][0][based_on]" class="form-control based-on-select" style="max-width: 300px;" data-block-index="0">
                        <option value="title"><?php esc_html_e( 'Title', 'all-sources-images' ); ?></option>
                        <option value="text_analyser"><?php esc_html_e( 'Text Analyzer: Full text', 'all-sources-images' ); ?></option>
                        <option value="text_analyser_previous_paragraph"><?php esc_html_e( 'Text Analyzer: Previous paragraph', 'all-sources-images' ); ?></option>
                        <option value="text_analyser_next_paragraph"><?php esc_html_e( 'Text Analyzer: Next paragraph', 'all-sources-images' ); ?></option>
                        <option value="tags"><?php esc_html_e( 'Tags', 'all-sources-images' ); ?></option>
                        <option value="categories"><?php esc_html_e( 'Categories', 'all-sources-images' ); ?></option>
                        <option value="custom_field"><?php esc_html_e( 'Custom Field', 'all-sources-images' ); ?></option>
                        <option value="custom_request"><?php esc_html_e( 'Custom Request (Placeholders)', 'all-sources-images' ); ?></option>
                        <option value="openai_extractor"><?php esc_html_e( 'OpenAI Keyword Extractor', 'all-sources-images' ); ?></option>
                        <option value="ai_image_prompt"><?php esc_html_e( 'AI Image Prompt (Enhanced)', 'all-sources-images' ); ?></option>
                    </select>
                    <!-- Help text container -->
                    <div class="based-on-help-text help-title" data-based-on="title">
                        <i class="dashicons dashicons-info-outline"></i>
                        <?php esc_html_e( 'Uses the post title as the search term. This is the simplest and most common option.', 'all-sources-images' ); ?>
                    </div>
                </div>
                
                <!-- Text Analyzer Language (for text_analyser options) -->
                <div class="form-row based-on-fields section_text_analyser">
                    <label><?php esc_html_e( 'Content Language', 'all-sources-images' ); ?></label>
                    <select name="ASI_plugin_main_settings[image_block][0][text_analyser_lang]" class="form-control" style="max-width: 200px;">
                        <option value="en"><?php esc_html_e( 'English', 'all-sources-images' ); ?></option>
                        <option value="es"><?php esc_html_e( 'Spanish', 'all-sources-images' ); ?></option>
                        <option value="fr"><?php esc_html_e( 'French', 'all-sources-images' ); ?></option>
                        <option value="de"><?php esc_html_e( 'German', 'all-sources-images' ); ?></option>
                        <option value="it"><?php esc_html_e( 'Italian', 'all-sources-images' ); ?></option>
                        <option value="pt"><?php esc_html_e( 'Portuguese', 'all-sources-images' ); ?></option>
                        <option value="nl"><?php esc_html_e( 'Dutch', 'all-sources-images' ); ?></option>
                        <option value="ru"><?php esc_html_e( 'Russian', 'all-sources-images' ); ?></option>
                        <option value="ja"><?php esc_html_e( 'Japanese', 'all-sources-images' ); ?></option>
                        <option value="zh"><?php esc_html_e( 'Chinese', 'all-sources-images' ); ?></option>
                        <option value="ar"><?php esc_html_e( 'Arabic', 'all-sources-images' ); ?></option>
                    </select>
                </div>
                
                <!-- Tags Options -->
                <div class="form-row based-on-fields section_tags">
                    <label><?php esc_html_e( 'Tag Selection', 'all-sources-images' ); ?></label>
                    <div class="radio-group">
                        <label><input type="radio" name="ASI_plugin_main_settings[image_block][0][tags]" value="first_tag" checked> <?php esc_html_e( 'First Tag', 'all-sources-images' ); ?></label>
                        <label><input type="radio" name="ASI_plugin_main_settings[image_block][0][tags]" value="last_tag"> <?php esc_html_e( 'Last Tag', 'all-sources-images' ); ?></label>
                        <label><input type="radio" name="ASI_plugin_main_settings[image_block][0][tags]" value="random_tag"> <?php esc_html_e( 'Random Tag', 'all-sources-images' ); ?></label>
                    </div>
                </div>
                
                <!-- Categories Options -->
                <div class="form-row based-on-fields section_categories">
                    <label><?php esc_html_e( 'Category Selection', 'all-sources-images' ); ?></label>
                    <div class="select-row" style="margin-bottom: 10px;">
                        <select name="ASI_plugin_main_settings[image_block][0][categories]" class="form-control" style="max-width: 200px;">
                            <option value="first_category"><?php esc_html_e( 'First Category', 'all-sources-images' ); ?></option>
                            <option value="last_category"><?php esc_html_e( 'Last Category', 'all-sources-images' ); ?></option>
                            <option value="random_category"><?php esc_html_e( 'Random Category', 'all-sources-images' ); ?></option>
                        </select>
                        <select name="ASI_plugin_main_settings[image_block][0][categories_level]" class="form-control" style="max-width: 200px;">
                            <option value="child"><?php esc_html_e( 'Child (Most Specific)', 'all-sources-images' ); ?></option>
                            <option value="parent"><?php esc_html_e( 'Parent', 'all-sources-images' ); ?></option>
                            <option value="grandparent"><?php esc_html_e( 'Grandparent (Top Level)', 'all-sources-images' ); ?></option>
                        </select>
                    </div>
                </div>
                
                <!-- Custom Field Options -->
                <div class="form-row based-on-fields section_custom_field">
                    <label><?php esc_html_e( 'Custom Field Name', 'all-sources-images' ); ?></label>
                    <input type="text" name="ASI_plugin_main_settings[image_block][0][custom_field]" class="form-control" style="max-width: 300px;" placeholder="my_custom_field">
                    <p class="description"><?php esc_html_e( 'Enter the meta key name of your custom field.', 'all-sources-images' ); ?></p>
                </div>
                
                <!-- Custom Request Options -->
                <div class="form-row based-on-fields section_custom_request">
                    <label><?php esc_html_e( 'Custom Search Template', 'all-sources-images' ); ?></label>
                    <input type="text" name="ASI_plugin_main_settings[image_block][0][custom_request]" class="form-control" style="max-width: 400px;" placeholder="%%Title%% %%Category%%" value="%%Title%% %%Category%%">
                    <p class="description">
                        <?php esc_html_e( 'Available placeholders:', 'all-sources-images' ); ?> 
                        <code>%%Title%%</code>, <code>%%Category%%</code>, <code>%%Tag%%</code>, <code>%%Taxonomy%%</code>
                    </p>
                </div>
                
                <!-- OpenAI Keyword Extractor Options -->
                <div class="form-row based-on-fields section_openai_extractor">
                    <label><?php esc_html_e( 'OpenAI API Key', 'all-sources-images' ); ?></label>
                    <input type="password" name="ASI_plugin_main_settings[image_block][0][openai_extractor_apikey]" class="form-control" style="max-width: 400px;" placeholder="sk-...">
                    <div style="margin-top: 10px;">
                        <label><?php esc_html_e( 'Number of Keywords', 'all-sources-images' ); ?></label>
                        <select name="ASI_plugin_main_settings[image_block][0][openai_number_of_keywords]" class="form-control" style="max-width: 100px;">
                            <option value="1-2">1-2</option>
                            <option value="2" selected>2</option>
                            <option value="3">3</option>
                        </select>
                    </div>
                </div>
                
                <!-- AI Image Prompt Options -->
                <div class="form-row based-on-fields section_ai_image_prompt">
                    <label><?php esc_html_e( 'OpenAI API Key', 'all-sources-images' ); ?></label>
                    <input type="password" name="ASI_plugin_main_settings[image_block][0][ai_prompt_apikey]" class="form-control" style="max-width: 400px;" placeholder="sk-...">
                    <div style="margin-top: 10px;">
                        <label><?php esc_html_e( 'Image Style', 'all-sources-images' ); ?></label>
                        <select name="ASI_plugin_main_settings[image_block][0][ai_prompt_style]" class="form-control" style="max-width: 200px;">
                            <option value="photorealistic"><?php esc_html_e( 'Photorealistic', 'all-sources-images' ); ?></option>
                            <option value="illustration"><?php esc_html_e( 'Illustration', 'all-sources-images' ); ?></option>
                            <option value="digital_art"><?php esc_html_e( 'Digital Art', 'all-sources-images' ); ?></option>
                            <option value="3d_render"><?php esc_html_e( '3D Render', 'all-sources-images' ); ?></option>
                            <option value="oil_painting"><?php esc_html_e( 'Oil Painting', 'all-sources-images' ); ?></option>
                            <option value="watercolor"><?php esc_html_e( 'Watercolor', 'all-sources-images' ); ?></option>
                            <option value="sketch"><?php esc_html_e( 'Sketch/Drawing', 'all-sources-images' ); ?></option>
                            <option value="minimalist"><?php esc_html_e( 'Minimalist', 'all-sources-images' ); ?></option>
                            <option value="cinematic"><?php esc_html_e( 'Cinematic', 'all-sources-images' ); ?></option>
                        </select>
                    </div>
                    <div style="margin-top: 10px;">
                        <label><?php esc_html_e( 'Custom Instructions (Optional)', 'all-sources-images' ); ?></label>
                        <input type="text" name="ASI_plugin_main_settings[image_block][0][ai_prompt_custom_instructions]" class="form-control" style="max-width: 400px;" placeholder="<?php esc_attr_e( 'e.g., vibrant colors, no text', 'all-sources-images' ); ?>">
                    </div>
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
                
                <!-- Translate to English -->
                <div class="form-row">
                    <label><?php esc_html_e( 'Translate to English', 'all-sources-images' ); ?></label>
                    <div class="field-content">
                        <label class="toggle-switch">
                            <input type="checkbox" name="ASI_plugin_main_settings[image_block][0][translation_EN]" value="true">
                            <span class="toggle-slider"></span>
                            <span class="toggle-label"><?php esc_html_e( 'Translate', 'all-sources-images' ); ?></span>
                        </label>
                        <p class="field-description" style="display: block; margin-top: 8px; clear: both;"><?php esc_html_e( 'The "based on" phrase/keywords will be translated into English. This helps to get better results with most image databases.', 'all-sources-images' ); ?></p>
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
        </div>
        <!-- End Template Container -->
        
        <form method="post" action="options.php" id="image-placement-form">
            <?php settings_fields( 'ASI-plugin-main-settings' ); ?>
            
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
                                <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][based_on]" class="form-control based-on-select" style="max-width: 300px;" data-block-index="<?php echo $displayIndex; ?>">
                                    <option value="title" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'title' ); ?>><?php esc_html_e( 'Title', 'all-sources-images' ); ?></option>
                                    <option value="text_analyser" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'text_analyser' ); ?>><?php esc_html_e( 'Text Analyzer: Full text', 'all-sources-images' ); ?></option>
                                    <option value="text_analyser_previous_paragraph" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'text_analyser_previous_paragraph' ); ?>><?php esc_html_e( 'Text Analyzer: Previous paragraph', 'all-sources-images' ); ?></option>
                                    <option value="text_analyser_next_paragraph" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'text_analyser_next_paragraph' ); ?>><?php esc_html_e( 'Text Analyzer: Next paragraph', 'all-sources-images' ); ?></option>
                                    <option value="tags" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'tags' ); ?>><?php esc_html_e( 'Tags', 'all-sources-images' ); ?></option>
                                    <option value="categories" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'categories' ); ?>><?php esc_html_e( 'Categories', 'all-sources-images' ); ?></option>
                                    <option value="custom_field" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'custom_field' ); ?>><?php esc_html_e( 'Custom Field', 'all-sources-images' ); ?></option>
                                    <option value="custom_request" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'custom_request' ); ?>><?php esc_html_e( 'Custom Request (Placeholders)', 'all-sources-images' ); ?></option>
                                    <option value="openai_extractor" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'openai_extractor' ); ?>><?php esc_html_e( 'OpenAI Keyword Extractor', 'all-sources-images' ); ?></option>
                                    <option value="ai_image_prompt" <?php selected( isset( $block['based_on'] ) ? $block['based_on'] : '', 'ai_image_prompt' ); ?>><?php esc_html_e( 'AI Image Prompt (Enhanced)', 'all-sources-images' ); ?></option>
                                </select>
                                <!-- Help text container -->
                                <?php 
                                $current_based_on = isset( $block['based_on'] ) ? $block['based_on'] : 'title';
                                $help_texts = array(
                                    'title' => __( 'Uses the post title as the search term. This is the simplest and most common option.', 'all-sources-images' ),
                                    'text_analyser' => __( 'Analyzes the post content to extract the most relevant keywords using ML algorithms.', 'all-sources-images' ),
                                    'text_analyser_previous_paragraph' => __( 'Analyzes only the paragraph BEFORE the image position for keyword extraction.', 'all-sources-images' ),
                                    'text_analyser_next_paragraph' => __( 'Analyzes only the paragraph AFTER the image position for keyword extraction.', 'all-sources-images' ),
                                    'tags' => __( 'Uses post tags as search terms. Choose first, last, or random tag.', 'all-sources-images' ),
                                    'categories' => __( 'Uses post categories as search terms with hierarchy level selection.', 'all-sources-images' ),
                                    'custom_field' => __( 'Uses a custom field (post meta) value as the search term.', 'all-sources-images' ),
                                    'custom_request' => __( 'Build your own search using placeholders: %%Title%%, %%Category%%, %%Tag%%, %%Taxonomy%%.', 'all-sources-images' ),
                                    'openai_extractor' => __( 'Uses OpenAI GPT to extract relevant keywords from your post title.', 'all-sources-images' ),
                                    'ai_image_prompt' => __( 'Uses OpenAI to generate optimized prompts for AI image generation (DALL-E, Stable Diffusion, etc.).', 'all-sources-images' ),
                                );
                                ?>
                                <div class="based-on-help-text help-<?php echo esc_attr( $current_based_on ); ?>">
                                    <i class="dashicons dashicons-info-outline"></i>
                                    <?php echo esc_html( $help_texts[ $current_based_on ] ); ?>
                                </div>
                            </div>
                            
                            <?php 
                            // Determine which sections should be visible
                            $show_text_analyser = in_array( $current_based_on, array( 'text_analyser', 'text_analyser_previous_paragraph', 'text_analyser_next_paragraph' ) );
                            $show_tags = ( $current_based_on === 'tags' );
                            $show_categories = ( $current_based_on === 'categories' );
                            $show_custom_field = ( $current_based_on === 'custom_field' );
                            $show_custom_request = ( $current_based_on === 'custom_request' );
                            $show_openai_extractor = ( $current_based_on === 'openai_extractor' );
                            $show_ai_image_prompt = ( $current_based_on === 'ai_image_prompt' );
                            ?>
                            
                            <!-- Text Analyzer Language -->
                            <div class="form-row based-on-fields section_text_analyser <?php echo $show_text_analyser ? 'visible' : ''; ?>">
                                <label><?php esc_html_e( 'Content Language', 'all-sources-images' ); ?></label>
                                <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][text_analyser_lang]" class="form-control" style="max-width: 200px;">
                                    <?php 
                                    $langs = array( 'en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'it' => 'Italian', 'pt' => 'Portuguese', 'nl' => 'Dutch', 'ru' => 'Russian', 'ja' => 'Japanese', 'zh' => 'Chinese', 'ar' => 'Arabic' );
                                    $current_lang = isset( $block['text_analyser_lang'] ) ? $block['text_analyser_lang'] : 'en';
                                    foreach ( $langs as $code => $name ) : ?>
                                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $current_lang, $code ); ?>><?php echo esc_html( $name ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Tags Options -->
                            <div class="form-row based-on-fields section_tags <?php echo $show_tags ? 'visible' : ''; ?>">
                                <label><?php esc_html_e( 'Tag Selection', 'all-sources-images' ); ?></label>
                                <div class="radio-group">
                                    <?php $current_tags = isset( $block['tags'] ) ? $block['tags'] : 'first_tag'; ?>
                                    <label><input type="radio" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][tags]" value="first_tag" <?php checked( $current_tags, 'first_tag' ); ?>> <?php esc_html_e( 'First Tag', 'all-sources-images' ); ?></label>
                                    <label><input type="radio" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][tags]" value="last_tag" <?php checked( $current_tags, 'last_tag' ); ?>> <?php esc_html_e( 'Last Tag', 'all-sources-images' ); ?></label>
                                    <label><input type="radio" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][tags]" value="random_tag" <?php checked( $current_tags, 'random_tag' ); ?>> <?php esc_html_e( 'Random Tag', 'all-sources-images' ); ?></label>
                                </div>
                            </div>
                            
                            <!-- Categories Options -->
                            <div class="form-row based-on-fields section_categories <?php echo $show_categories ? 'visible' : ''; ?>">
                                <label><?php esc_html_e( 'Category Selection', 'all-sources-images' ); ?></label>
                                <div class="select-row" style="margin-bottom: 10px;">
                                    <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][categories]" class="form-control" style="max-width: 200px;">
                                        <?php $current_cat = isset( $block['categories'] ) ? $block['categories'] : 'first_category'; ?>
                                        <option value="first_category" <?php selected( $current_cat, 'first_category' ); ?>><?php esc_html_e( 'First Category', 'all-sources-images' ); ?></option>
                                        <option value="last_category" <?php selected( $current_cat, 'last_category' ); ?>><?php esc_html_e( 'Last Category', 'all-sources-images' ); ?></option>
                                        <option value="random_category" <?php selected( $current_cat, 'random_category' ); ?>><?php esc_html_e( 'Random Category', 'all-sources-images' ); ?></option>
                                    </select>
                                    <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][categories_level]" class="form-control" style="max-width: 200px;">
                                        <?php $current_level = isset( $block['categories_level'] ) ? $block['categories_level'] : 'child'; ?>
                                        <option value="child" <?php selected( $current_level, 'child' ); ?>><?php esc_html_e( 'Child (Most Specific)', 'all-sources-images' ); ?></option>
                                        <option value="parent" <?php selected( $current_level, 'parent' ); ?>><?php esc_html_e( 'Parent', 'all-sources-images' ); ?></option>
                                        <option value="grandparent" <?php selected( $current_level, 'grandparent' ); ?>><?php esc_html_e( 'Grandparent (Top Level)', 'all-sources-images' ); ?></option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Custom Field Options -->
                            <div class="form-row based-on-fields section_custom_field <?php echo $show_custom_field ? 'visible' : ''; ?>">
                                <label><?php esc_html_e( 'Custom Field Name', 'all-sources-images' ); ?></label>
                                <input type="text" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][custom_field]" class="form-control" style="max-width: 300px;" placeholder="my_custom_field" value="<?php echo esc_attr( isset( $block['custom_field'] ) ? $block['custom_field'] : '' ); ?>">
                                <p class="description"><?php esc_html_e( 'Enter the meta key name of your custom field.', 'all-sources-images' ); ?></p>
                            </div>
                            
                            <!-- Custom Request Options -->
                            <div class="form-row based-on-fields section_custom_request <?php echo $show_custom_request ? 'visible' : ''; ?>">
                                <label><?php esc_html_e( 'Custom Search Template', 'all-sources-images' ); ?></label>
                                <input type="text" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][custom_request]" class="form-control" style="max-width: 400px;" placeholder="%%Title%% %%Category%%" value="<?php echo esc_attr( isset( $block['custom_request'] ) ? $block['custom_request'] : '%%Title%% %%Category%%' ); ?>">
                                <p class="description">
                                    <?php esc_html_e( 'Available placeholders:', 'all-sources-images' ); ?> 
                                    <code>%%Title%%</code>, <code>%%Category%%</code>, <code>%%Tag%%</code>, <code>%%Taxonomy%%</code>
                                </p>
                            </div>
                            
                            <!-- OpenAI Keyword Extractor Options -->
                            <div class="form-row based-on-fields section_openai_extractor <?php echo $show_openai_extractor ? 'visible' : ''; ?>">
                                <label><?php esc_html_e( 'OpenAI API Key', 'all-sources-images' ); ?></label>
                                <input type="password" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][openai_extractor_apikey]" class="form-control" style="max-width: 400px;" placeholder="sk-..." value="<?php echo esc_attr( isset( $block['openai_extractor_apikey'] ) ? $block['openai_extractor_apikey'] : '' ); ?>">
                                <div style="margin-top: 10px;">
                                    <label><?php esc_html_e( 'Number of Keywords', 'all-sources-images' ); ?></label>
                                    <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][openai_number_of_keywords]" class="form-control" style="max-width: 100px;">
                                        <?php $current_num = isset( $block['openai_number_of_keywords'] ) ? $block['openai_number_of_keywords'] : '2'; ?>
                                        <option value="1-2" <?php selected( $current_num, '1-2' ); ?>>1-2</option>
                                        <option value="2" <?php selected( $current_num, '2' ); ?>>2</option>
                                        <option value="3" <?php selected( $current_num, '3' ); ?>>3</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- AI Image Prompt Options -->
                            <div class="form-row based-on-fields section_ai_image_prompt <?php echo $show_ai_image_prompt ? 'visible' : ''; ?>">
                                <label><?php esc_html_e( 'OpenAI API Key', 'all-sources-images' ); ?></label>
                                <input type="password" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][ai_prompt_apikey]" class="form-control" style="max-width: 400px;" placeholder="sk-..." value="<?php echo esc_attr( isset( $block['ai_prompt_apikey'] ) ? $block['ai_prompt_apikey'] : '' ); ?>">
                                <div style="margin-top: 10px;">
                                    <label><?php esc_html_e( 'Image Style', 'all-sources-images' ); ?></label>
                                    <select name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][ai_prompt_style]" class="form-control" style="max-width: 200px;">
                                        <?php $current_style = isset( $block['ai_prompt_style'] ) ? $block['ai_prompt_style'] : 'photorealistic'; ?>
                                        <option value="photorealistic" <?php selected( $current_style, 'photorealistic' ); ?>><?php esc_html_e( 'Photorealistic', 'all-sources-images' ); ?></option>
                                        <option value="illustration" <?php selected( $current_style, 'illustration' ); ?>><?php esc_html_e( 'Illustration', 'all-sources-images' ); ?></option>
                                        <option value="digital_art" <?php selected( $current_style, 'digital_art' ); ?>><?php esc_html_e( 'Digital Art', 'all-sources-images' ); ?></option>
                                        <option value="3d_render" <?php selected( $current_style, '3d_render' ); ?>><?php esc_html_e( '3D Render', 'all-sources-images' ); ?></option>
                                        <option value="oil_painting" <?php selected( $current_style, 'oil_painting' ); ?>><?php esc_html_e( 'Oil Painting', 'all-sources-images' ); ?></option>
                                        <option value="watercolor" <?php selected( $current_style, 'watercolor' ); ?>><?php esc_html_e( 'Watercolor', 'all-sources-images' ); ?></option>
                                        <option value="sketch" <?php selected( $current_style, 'sketch' ); ?>><?php esc_html_e( 'Sketch/Drawing', 'all-sources-images' ); ?></option>
                                        <option value="minimalist" <?php selected( $current_style, 'minimalist' ); ?>><?php esc_html_e( 'Minimalist', 'all-sources-images' ); ?></option>
                                        <option value="cinematic" <?php selected( $current_style, 'cinematic' ); ?>><?php esc_html_e( 'Cinematic', 'all-sources-images' ); ?></option>
                                    </select>
                                </div>
                                <div style="margin-top: 10px;">
                                    <label><?php esc_html_e( 'Custom Instructions (Optional)', 'all-sources-images' ); ?></label>
                                    <input type="text" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][ai_prompt_custom_instructions]" class="form-control" style="max-width: 400px;" placeholder="<?php esc_attr_e( 'e.g., vibrant colors, no text', 'all-sources-images' ); ?>" value="<?php echo esc_attr( isset( $block['ai_prompt_custom_instructions'] ) ? $block['ai_prompt_custom_instructions'] : '' ); ?>">
                                </div>
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
                            
                            <!-- Translate to English -->
                            <div class="form-row">
                                <label><?php esc_html_e( 'Translate to English', 'all-sources-images' ); ?></label>
                                <div class="field-content">
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="ASI_plugin_main_settings[image_block][<?php echo $displayIndex; ?>][translation_EN]" value="true" <?php checked( isset( $block['translation_EN'] ) && $block['translation_EN'] == 'true' ); ?>>
                                        <span class="toggle-slider"></span>
                                        <span class="toggle-label"><?php esc_html_e( 'Translate', 'all-sources-images' ); ?></span>
                                    </label>
                                    <p class="field-description" style="display: block; margin-top: 8px; clear: both;"><?php esc_html_e( 'The "based on" phrase/keywords will be translated into English. This helps to get better results with most image databases.', 'all-sources-images' ); ?></p>
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
    
    // Help texts for each based_on option
    var helpTexts = {
        'title': '<?php echo esc_js( __( 'Uses the post title as the search term. This is the simplest and most common option.', 'all-sources-images' ) ); ?>',
        'text_analyser': '<?php echo esc_js( __( 'Analyzes the post content to extract the most relevant keywords using ML algorithms.', 'all-sources-images' ) ); ?>',
        'text_analyser_previous_paragraph': '<?php echo esc_js( __( 'Analyzes only the paragraph BEFORE the image position for keyword extraction.', 'all-sources-images' ) ); ?>',
        'text_analyser_next_paragraph': '<?php echo esc_js( __( 'Analyzes only the paragraph AFTER the image position for keyword extraction.', 'all-sources-images' ) ); ?>',
        'tags': '<?php echo esc_js( __( 'Uses post tags as search terms. Choose first, last, or random tag.', 'all-sources-images' ) ); ?>',
        'categories': '<?php echo esc_js( __( 'Uses post categories as search terms with hierarchy level selection.', 'all-sources-images' ) ); ?>',
        'custom_field': '<?php echo esc_js( __( 'Uses a custom field (post meta) value as the search term.', 'all-sources-images' ) ); ?>',
        'custom_request': '<?php echo esc_js( __( 'Build your own search using placeholders: %%Title%%, %%Category%%, %%Tag%%, %%Taxonomy%%.', 'all-sources-images' ) ); ?>',
        'openai_extractor': '<?php echo esc_js( __( 'Uses OpenAI GPT to extract relevant keywords from your post title.', 'all-sources-images' ) ); ?>',
        'ai_image_prompt': '<?php echo esc_js( __( 'Uses OpenAI to generate optimized prompts for AI image generation (DALL-E, Stable Diffusion, etc.).', 'all-sources-images' ) ); ?>'
    };
    
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
        
        // Show/hide based_on fields and update help text
        $(document).on('change', '.based-on-select', function() {
            var $block = $(this).closest('.image-placement-block');
            var value = $(this).val();
            
            // Hide all based-on fields
            $block.find('.based-on-fields').removeClass('visible');
            
            // Show relevant fields based on selection
            if (value === 'text_analyser' || value === 'text_analyser_previous_paragraph' || value === 'text_analyser_next_paragraph') {
                $block.find('.section_text_analyser').addClass('visible');
            } else if (value === 'tags') {
                $block.find('.section_tags').addClass('visible');
            } else if (value === 'categories') {
                $block.find('.section_categories').addClass('visible');
            } else if (value === 'custom_field') {
                $block.find('.section_custom_field').addClass('visible');
            } else if (value === 'custom_request') {
                $block.find('.section_custom_request').addClass('visible');
            } else if (value === 'openai_extractor') {
                $block.find('.section_openai_extractor').addClass('visible');
            } else if (value === 'ai_image_prompt') {
                $block.find('.section_ai_image_prompt').addClass('visible');
            }
            
            // Update help text
            var $helpText = $block.find('.based-on-help-text');
            $helpText.removeClass(function(index, className) {
                return (className.match(/(^|\s)help-\S+/g) || []).join(' ');
            });
            $helpText.addClass('help-' + value);
            $helpText.html('<i class="dashicons dashicons-info-outline"></i> ' + helpTexts[value]);
            
            // Also handle title section visibility
            var $titleSection = $block.find('.section_title');
            if (value === 'title') {
                $titleSection.show();
            } else {
                $titleSection.hide();
            }
        });
        
        // Add new block
        $('#add-image-block').on('click', function() {
            var $template = $('#template-container .image-placement-block.template-block').first();
            var $newBlock = $template.clone();
            
            // Update block index
            $newBlock.removeClass('template-block');
            $newBlock.addClass('image-block-' + blockIndex);
            $newBlock.attr('data-block-index', blockIndex);
            $newBlock.find('.block-number').text(blockIndex);
            
            // Update all input names - replace [0] with new index
            $newBlock.find('[name*="[image_block][0]"]').each(function() {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace('[image_block][0]', '[image_block][' + blockIndex + ']'));
            });
            
            // Handle data-name-template attributes (convert to name with proper index)
            $newBlock.find('[data-name-template]').each(function() {
                var nameTemplate = $(this).attr('data-name-template');
                var newName = nameTemplate.replace('__INDEX__', blockIndex);
                $(this).attr('name', newName);
                $(this).removeAttr('data-name-template');
            });
            
            // Update data-block-index on select
            $newBlock.find('.based-on-select').attr('data-block-index', blockIndex);
            
            // Show the block
            $newBlock.css('display', 'block');
            
            // Append to container (inside the form)
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
        
        // Renumber blocks after deletion - updates BOTH display AND input names
        function renumberBlocks() {
            var newIndex = 1;
            $('#image-blocks-container .image-placement-block:not(.template-block)').each(function() {
                var $block = $(this);
                var oldIndex = $block.attr('data-block-index');
                
                // Update display number
                $block.find('.block-number').text(newIndex);
                
                // Update data-block-index attribute
                $block.attr('data-block-index', newIndex);
                $block.removeClass('image-block-' + oldIndex).addClass('image-block-' + newIndex);
                
                // Update ALL input/select names in this block to use the new index
                $block.find('input, select, textarea').each(function() {
                    var name = $(this).attr('name');
                    if (name && name.indexOf('[image_block]') !== -1) {
                        // Replace [image_block][ANY_NUMBER] with [image_block][newIndex]
                        var newName = name.replace(/\[image_block\]\[\d+\]/, '[image_block][' + newIndex + ']');
                        $(this).attr('name', newName);
                    }
                });
                
                // Update data-block-index on select
                $block.find('.based-on-select').attr('data-block-index', newIndex);
                
                newIndex++;
            });
            
            // Update blockIndex for next new block
            blockIndex = newIndex;
        }
        
        // Also renumber on form submit to ensure consistent indices
        $('#image-placement-form').on('submit', function() {
            renumberBlocks();
        });
        
    });
    
})(jQuery);
</script>
