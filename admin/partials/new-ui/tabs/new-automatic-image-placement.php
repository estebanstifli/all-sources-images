<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * New UI - Image Placement Tab
 * This file handles the Image Placement configuration in the new admin UI
 */

// Get current settings - use raw option first to see what's actually stored
$allsi_raw_options = get_option( 'ALLSI_plugin_main_settings' );
$allsi_options_main = wp_parse_args( $allsi_raw_options, $this->ALLSI_default_options_main_settings() );

// Get image_blocks from the RAW options first (what's actually in DB)
// If empty or not set, fall back to defaults
if ( isset( $allsi_raw_options['image_block'] ) && is_array( $allsi_raw_options['image_block'] ) && ! empty( $allsi_raw_options['image_block'] ) ) {
    $allsi_raw_image_blocks = $allsi_raw_options['image_block'];
} else {
    $allsi_raw_image_blocks = isset( $allsi_options_main['image_block'] ) ? $allsi_options_main['image_block'] : array();
}

// Re-index image_blocks to use consecutive indices starting from 1
// This fixes issues where blocks had non-consecutive indices (e.g., 2, 5, 7)
$allsi_image_blocks = array();
$allsi_new_index = 1;
foreach ( $allsi_raw_image_blocks as $allsi_block ) {
    if ( is_array( $allsi_block ) ) {
        $allsi_image_blocks[ $allsi_new_index ] = $allsi_block;
        $allsi_new_index++;
    }
}

// Debug: uncomment to see what's stored
// error_log( 'ASI Image Blocks Raw: ' . print_r( $allsi_raw_image_blocks, true ) );
// error_log( 'ASI Image Blocks Re-indexed: ' . print_r( $allsi_image_blocks, true ) );

// Get available image sizes
$allsi_image_sizes = get_intermediate_image_sizes();

// Get available image sources
$allsi_list_api_auto = $this->ALLSI_banks_name_auto();

// Pro feature check
$allsi_disabled = '';
$allsi_class_disabled = '';
$allsi_checkbox_disabled = '';
if ( function_exists( 'ALLSI_freemius' ) && !ALLSI_freemius()->is_premium() ) {
    $allsi_disabled = 'disabled';
    $allsi_class_disabled = 'disabled-option';
    $allsi_checkbox_disabled = 'checkbox-disabled';
}

// Calculate next block index - after the last re-indexed block
$allsi_block_index = count( $allsi_image_blocks ) + 1;

// Get the first block (index 1) for the Featured Image section, with defaults
$allsi_first_block = isset( $allsi_image_blocks[1] ) ? $allsi_image_blocks[1] : array();
$allsi_first_block = wp_parse_args( $allsi_first_block, array(
    'image_location' => 'featured',
    'api_chosen' => 'pixabay',
    'api_chosen_2' => 'none',
    'based_on' => 'title',
    'translation_EN' => 'true',
    'title_selection' => 'full_title',
    'title_length' => 3,
    'selected_image' => 'first_result',
    'custom_request' => '%%Title%% %%Category%%',
    'openai_extractor_apikey' => '',
    'openai_number_of_keywords' => '2',
    'ai_prompt_apikey' => '',
    'ai_prompt_style' => 'photorealistic',
    'custom_field_name' => '',
) );

// Note: Styles are now enqueued via wp_enqueue_style( 'allsi-image-placement' ) in new-ui-assets.php
// See: admin/css/allsi-image-placement.css
?>

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
                            <input type="radio" data-name-template="ALLSI_plugin_main_settings[image_block][__INDEX__][image_location]" value="featured" checked>
                            <?php esc_html_e( 'Featured Image', 'all-sources-images' ); ?>
                        </label>
                        <label>
                            <input type="radio" data-name-template="ALLSI_plugin_main_settings[image_block][__INDEX__][image_location]" value="custom">
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
                            <select name="ALLSI_plugin_main_settings[image_block][0][image_custom_location_placement]" class="form-control">
                                <option value="before"><?php esc_html_e( 'Before', 'all-sources-images' ); ?></option>
                                <option value="after"><?php esc_html_e( 'After', 'all-sources-images' ); ?></option>
                            </select>
                            <span><?php esc_html_e( 'the', 'all-sources-images' ); ?></span>
                            <select name="ALLSI_plugin_main_settings[image_block][0][image_custom_location_position]" class="form-control">
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
                            <select name="ALLSI_plugin_main_settings[image_block][0][image_custom_location_tag]" class="form-control">
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
                        <select name="ALLSI_plugin_main_settings[image_block][0][image_custom_image_size]" class="form-control" style="max-width: 200px;">
                            <?php foreach ( $allsi_image_sizes as $allsi_image_size ) : ?>
                                <option value="<?php echo esc_attr( $allsi_image_size ); ?>"><?php echo esc_html( $allsi_image_size ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Image Source -->
                <div class="form-row">
                    <label><?php esc_html_e( 'Image Source', 'all-sources-images' ); ?></label>
                    <select name="ALLSI_plugin_main_settings[image_block][0][api_chosen]" class="form-control" style="max-width: 300px;">
                        <?php foreach ( $allsi_list_api_auto as $allsi_api_auto_name => $allsi_api_auto_id ) : ?>
                            <?php if ( true === $allsi_api_auto_id[1] ) : ?>
                                <option value="<?php echo esc_attr( $allsi_api_auto_id[0] ); ?>" <?php selected( 'pixabay', $allsi_api_auto_id[0] ); ?>><?php echo esc_html( $allsi_api_auto_name ); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Second Image Source -->
                <div class="form-row">
                    <label><?php esc_html_e( 'Second Image Source (Fallback)', 'all-sources-images' ); ?></label>
                    <select name="ALLSI_plugin_main_settings[image_block][0][api_chosen_2]" class="form-control" style="max-width: 300px;">
                        <option value="none"><?php esc_html_e( 'None', 'all-sources-images' ); ?></option>
                        <?php foreach ( $allsi_list_api_auto as $allsi_api_auto_name => $allsi_api_auto_id ) : ?>
                            <?php if ( true === $allsi_api_auto_id[1] ) : ?>
                                <option value="<?php echo esc_attr( $allsi_api_auto_id[0] ); ?>"><?php echo esc_html( $allsi_api_auto_name ); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Search Based On -->
                <div class="form-row">
                    <label><?php esc_html_e( 'Search Based On', 'all-sources-images' ); ?></label>
                    <select name="ALLSI_plugin_main_settings[image_block][0][based_on]" class="form-control based-on-select" style="max-width: 300px;" data-block-index="0">
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
                    <select name="ALLSI_plugin_main_settings[image_block][0][text_analyser_lang]" class="form-control" style="max-width: 200px;">
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
                        <label><input type="radio" name="ALLSI_plugin_main_settings[image_block][0][tags]" value="first_tag" checked> <?php esc_html_e( 'First Tag', 'all-sources-images' ); ?></label>
                        <label><input type="radio" name="ALLSI_plugin_main_settings[image_block][0][tags]" value="last_tag"> <?php esc_html_e( 'Last Tag', 'all-sources-images' ); ?></label>
                        <label><input type="radio" name="ALLSI_plugin_main_settings[image_block][0][tags]" value="random_tag"> <?php esc_html_e( 'Random Tag', 'all-sources-images' ); ?></label>
                    </div>
                </div>
                
                <!-- Categories Options -->
                <div class="form-row based-on-fields section_categories">
                    <label><?php esc_html_e( 'Category Selection', 'all-sources-images' ); ?></label>
                    <div class="select-row" style="margin-bottom: 10px;">
                        <select name="ALLSI_plugin_main_settings[image_block][0][categories]" class="form-control" style="max-width: 200px;">
                            <option value="first_category"><?php esc_html_e( 'First Category', 'all-sources-images' ); ?></option>
                            <option value="last_category"><?php esc_html_e( 'Last Category', 'all-sources-images' ); ?></option>
                            <option value="random_category"><?php esc_html_e( 'Random Category', 'all-sources-images' ); ?></option>
                        </select>
                        <select name="ALLSI_plugin_main_settings[image_block][0][categories_level]" class="form-control" style="max-width: 200px;">
                            <option value="child"><?php esc_html_e( 'Child (Most Specific)', 'all-sources-images' ); ?></option>
                            <option value="parent"><?php esc_html_e( 'Parent', 'all-sources-images' ); ?></option>
                            <option value="grandparent"><?php esc_html_e( 'Grandparent (Top Level)', 'all-sources-images' ); ?></option>
                        </select>
                    </div>
                </div>
                
                <!-- Custom Field Options -->
                <div class="form-row based-on-fields section_custom_field">
                    <label><?php esc_html_e( 'Custom Field Name', 'all-sources-images' ); ?></label>
                    <input type="text" name="ALLSI_plugin_main_settings[image_block][0][custom_field]" class="form-control" style="max-width: 300px;" placeholder="my_custom_field">
                    <p class="description"><?php esc_html_e( 'Enter the meta key name of your custom field.', 'all-sources-images' ); ?></p>
                </div>
                
                <!-- Custom Request Options -->
                <div class="form-row based-on-fields section_custom_request">
                    <label><?php esc_html_e( 'Custom Search Template', 'all-sources-images' ); ?></label>
                    <input type="text" name="ALLSI_plugin_main_settings[image_block][0][custom_request]" class="form-control" style="max-width: 400px;" placeholder="%%Title%% %%Category%%" value="%%Title%% %%Category%%">
                    <p class="description">
                        <?php esc_html_e( 'Available placeholders:', 'all-sources-images' ); ?> 
                        <code>%%Title%%</code>, <code>%%Category%%</code>, <code>%%Tag%%</code>, <code>%%Taxonomy%%</code>
                    </p>
                </div>
                
                <!-- OpenAI Keyword Extractor Options -->
                <div class="form-row based-on-fields section_openai_extractor">
                    <label><?php esc_html_e( 'OpenAI API Key', 'all-sources-images' ); ?></label>
                    <input type="password" name="ALLSI_plugin_main_settings[image_block][0][openai_extractor_apikey]" class="form-control" style="max-width: 400px;" placeholder="sk-...">
                    <div style="margin-top: 10px;">
                        <label><?php esc_html_e( 'Number of Keywords', 'all-sources-images' ); ?></label>
                        <select name="ALLSI_plugin_main_settings[image_block][0][openai_number_of_keywords]" class="form-control" style="max-width: 100px;">
                            <option value="1-2">1-2</option>
                            <option value="2" selected>2</option>
                            <option value="3">3</option>
                        </select>
                    </div>
                </div>
                
                <!-- AI Image Prompt Options -->
                <div class="form-row based-on-fields section_ai_image_prompt">
                    <label><?php esc_html_e( 'OpenAI API Key', 'all-sources-images' ); ?></label>
                    <input type="password" name="ALLSI_plugin_main_settings[image_block][0][ai_prompt_apikey]" class="form-control" style="max-width: 400px;" placeholder="sk-...">
                    <div style="margin-top: 10px;">
                        <label><?php esc_html_e( 'Image Style', 'all-sources-images' ); ?></label>
                        <select name="ALLSI_plugin_main_settings[image_block][0][ai_prompt_style]" class="form-control" style="max-width: 200px;">
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
                        <input type="text" name="ALLSI_plugin_main_settings[image_block][0][ai_prompt_custom_instructions]" class="form-control" style="max-width: 400px;" placeholder="<?php esc_attr_e( 'e.g., vibrant colors, no text', 'all-sources-images' ); ?>">
                    </div>
                </div>
                
                <!-- Title Options (shown when based_on = title) -->
                <div class="form-row section_title">
                    <label><?php esc_html_e( 'Title Selection', 'all-sources-images' ); ?></label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="ALLSI_plugin_main_settings[image_block][0][title_selection]" value="full_title" checked>
                            <?php esc_html_e( 'Full title', 'all-sources-images' ); ?>
                        </label>
                        <label>
                            <input type="radio" name="ALLSI_plugin_main_settings[image_block][0][title_selection]" value="cut_title">
                            <?php esc_html_e( 'Specific Part', 'all-sources-images' ); ?>:
                            <input type="number" name="ALLSI_plugin_main_settings[image_block][0][title_length]" min="1" value="3" class="form-control" style="width: 60px; margin-left: 5px;">
                            <span style="margin-left: 5px;"><?php esc_html_e( 'first words', 'all-sources-images' ); ?></span>
                        </label>
                    </div>
                </div>
                
                <!-- Translate to English -->
                <div class="form-row">
                    <label><?php esc_html_e( 'Translate to English', 'all-sources-images' ); ?></label>
                    <div class="field-content">
                        <label class="toggle-switch">
                            <input type="checkbox" name="ALLSI_plugin_main_settings[image_block][0][translation_EN]" value="true" <?php checked( ! isset( $allsi_first_block['translation_EN'] ) || $allsi_first_block['translation_EN'] === 'true' ); ?>>
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
                            <input type="radio" name="ALLSI_plugin_main_settings[image_block][0][selected_image]" value="first_result" checked>
                            <?php esc_html_e( 'First result', 'all-sources-images' ); ?>
                        </label>
                        <label class="<?php echo esc_attr( $allsi_class_disabled ); ?>">
                            <input type="radio" name="ALLSI_plugin_main_settings[image_block][0][selected_image]" value="random_result" <?php echo esc_attr( $allsi_disabled ); ?>>
                            <?php esc_html_e( 'Random result', 'all-sources-images' ); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Template Container -->
        
        <form method="post" action="options.php" id="image-placement-form">
            <?php settings_fields( 'ASI-plugin-main-settings' ); ?>
            <input type="hidden" name="ALLSI_plugin_main_settings[_saving_tab]" value="image_placement">
            
            <!-- Container for saved and new blocks -->
            <div id="image-blocks-container">
                <?php if ( ! empty( $allsi_image_blocks ) ) : ?>
                    <?php $allsi_display_index = 1; ?>
                    <?php foreach ( $allsi_image_blocks as $allsi_index => $allsi_block ) : ?>
                        <?php 
                        $allsi_is_inline = ( isset( $allsi_block['image_location'] ) && $allsi_block['image_location'] === 'custom' );
                        ?>
                        <div class="image-placement-block image-block-<?php echo esc_attr( $allsi_display_index ); ?>" data-block-index="<?php echo esc_attr( $allsi_display_index ); ?>">
                            <div class="block-header">
                                <h4><?php esc_html_e( 'Image Location', 'all-sources-images' ); ?> #<span class="block-number"><?php echo esc_html( $allsi_display_index ); ?></span></h4>
                                <button type="button" class="btn-delete-block"><?php esc_html_e( '[-] Delete', 'all-sources-images' ); ?></button>
                            </div>
                            
                            <!-- Featured Image / Inline Content -->
                            <div class="form-row image_location">
                                <label><?php esc_html_e( 'Image Location Type', 'all-sources-images' ); ?></label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][image_location]" value="featured" <?php checked( isset( $allsi_block['image_location'] ) ? $allsi_block['image_location'] : 'featured', 'featured' ); ?>>
                                        <?php esc_html_e( 'Featured Image', 'all-sources-images' ); ?>
                                    </label>
                                    <label>
                                        <input type="radio" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][image_location]" value="custom" <?php checked( isset( $allsi_block['image_location'] ) ? $allsi_block['image_location'] : '', 'custom' ); ?>>
                                        <?php esc_html_e( 'Inline Content', 'all-sources-images' ); ?>
                                    </label>
                                </div>
                                <p class="description"><?php esc_html_e( '"Inline content" allows you to generate the image anywhere in the content', 'all-sources-images' ); ?></p>
                            </div>
                            
                            <!-- Inline Content Fields -->
                            <div class="inline-content-fields section_custom_image_position <?php echo esc_attr( $allsi_is_inline ? 'visible' : '' ); ?>">
                                <div class="form-row">
                                    <label><?php esc_html_e( 'Image Position', 'all-sources-images' ); ?></label>
                                    <div class="select-row">
                                        <span><?php esc_html_e( 'Insert', 'all-sources-images' ); ?></span>
                                        <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][image_custom_location_placement]" class="form-control">
                                            <option value="before" <?php selected( isset( $allsi_block['image_custom_location_placement'] ) ? $allsi_block['image_custom_location_placement'] : '', 'before' ); ?>><?php esc_html_e( 'Before', 'all-sources-images' ); ?></option>
                                            <option value="after" <?php selected( isset( $allsi_block['image_custom_location_placement'] ) ? $allsi_block['image_custom_location_placement'] : '', 'after' ); ?>><?php esc_html_e( 'After', 'all-sources-images' ); ?></option>
                                        </select>
                                        <span><?php esc_html_e( 'the', 'all-sources-images' ); ?></span>
                                        <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][image_custom_location_position]" class="form-control">
                                            <?php 
                                            $allsi_positions = array(
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
                                            foreach ( $allsi_positions as $allsi_val => $allsi_label ) : ?>
                                                <option value="<?php echo esc_attr( $allsi_val ); ?>" <?php selected( isset( $allsi_block['image_custom_location_position'] ) ? $allsi_block['image_custom_location_position'] : '', $allsi_val ); ?>><?php echo esc_html( $allsi_label ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][image_custom_location_tag]" class="form-control">
                                            <?php 
                                            $allsi_tags = array( 'p' => __( 'paragraph (p)', 'all-sources-images' ), 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4', 'h5' => 'h5', 'h6' => 'h6', 'div' => 'div', 'a' => __( 'link (a)', 'all-sources-images' ) );
                                            foreach ( $allsi_tags as $allsi_val => $allsi_label ) : ?>
                                                <option value="<?php echo esc_attr( $allsi_val ); ?>" <?php selected( isset( $allsi_block['image_custom_location_tag'] ) ? $allsi_block['image_custom_location_tag'] : '', $allsi_val ); ?>><?php echo esc_html( $allsi_label ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <label><?php esc_html_e( 'Image Size', 'all-sources-images' ); ?></label>
                                    <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][image_custom_image_size]" class="form-control" style="max-width: 200px;">
                                        <?php foreach ( $allsi_image_sizes as $allsi_image_size ) : ?>
                                            <option value="<?php echo esc_attr( $allsi_image_size ); ?>" <?php selected( isset( $allsi_block['image_custom_image_size'] ) ? $allsi_block['image_custom_image_size'] : '', $allsi_image_size ); ?>><?php echo esc_html( $allsi_image_size ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Image Source -->
                            <div class="form-row">
                                <label><?php esc_html_e( 'Image Source', 'all-sources-images' ); ?></label>
                                <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][api_chosen]" class="form-control" style="max-width: 300px;">
                                    <?php foreach ( $allsi_list_api_auto as $allsi_api_auto_name => $allsi_api_auto_id ) : ?>
                                        <?php if ( true === $allsi_api_auto_id[1] ) : ?>
                                            <option value="<?php echo esc_attr( $allsi_api_auto_id[0] ); ?>" <?php selected( isset( $allsi_block['api_chosen'] ) ? $allsi_block['api_chosen'] : 'pixabay', $allsi_api_auto_id[0] ); ?>><?php echo esc_html( $allsi_api_auto_name ); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Second Image Source -->
                            <div class="form-row">
                                <label><?php esc_html_e( 'Second Image Source (Fallback)', 'all-sources-images' ); ?></label>
                                <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][api_chosen_2]" class="form-control" style="max-width: 300px;">
                                    <option value="none"><?php esc_html_e( 'None', 'all-sources-images' ); ?></option>
                                    <?php foreach ( $allsi_list_api_auto as $allsi_api_auto_name => $allsi_api_auto_id ) : ?>
                                        <?php if ( true === $allsi_api_auto_id[1] ) : ?>
                                            <option value="<?php echo esc_attr( $allsi_api_auto_id[0] ); ?>" <?php selected( isset( $allsi_block['api_chosen_2'] ) ? $allsi_block['api_chosen_2'] : '', $allsi_api_auto_id[0] ); ?>><?php echo esc_html( $allsi_api_auto_name ); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Search Based On -->
                            <div class="form-row">
                                <label><?php esc_html_e( 'Search Based On', 'all-sources-images' ); ?></label>
                                <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][based_on]" class="form-control based-on-select" style="max-width: 300px;" data-block-index="<?php echo esc_attr( $allsi_display_index ); ?>">
                                    <option value="title" <?php selected( isset( $allsi_block['based_on'] ) ? $allsi_block['based_on'] : '', 'title' ); ?>><?php esc_html_e( 'Title', 'all-sources-images' ); ?></option>
                                    <option value="text_analyser" <?php selected( isset( $allsi_block['based_on'] ) ? $allsi_block['based_on'] : '', 'text_analyser' ); ?>><?php esc_html_e( 'Text Analyzer: Full text', 'all-sources-images' ); ?></option>
                                    <option value="text_analyser_previous_paragraph" <?php selected( isset( $allsi_block['based_on'] ) ? $allsi_block['based_on'] : '', 'text_analyser_previous_paragraph' ); ?>><?php esc_html_e( 'Text Analyzer: Previous paragraph', 'all-sources-images' ); ?></option>
                                    <option value="text_analyser_next_paragraph" <?php selected( isset( $allsi_block['based_on'] ) ? $allsi_block['based_on'] : '', 'text_analyser_next_paragraph' ); ?>><?php esc_html_e( 'Text Analyzer: Next paragraph', 'all-sources-images' ); ?></option>
                                    <option value="tags" <?php selected( isset( $allsi_block['based_on'] ) ? $allsi_block['based_on'] : '', 'tags' ); ?>><?php esc_html_e( 'Tags', 'all-sources-images' ); ?></option>
                                    <option value="categories" <?php selected( isset( $allsi_block['based_on'] ) ? $allsi_block['based_on'] : '', 'categories' ); ?>><?php esc_html_e( 'Categories', 'all-sources-images' ); ?></option>
                                    <option value="custom_field" <?php selected( isset( $allsi_block['based_on'] ) ? $allsi_block['based_on'] : '', 'custom_field' ); ?>><?php esc_html_e( 'Custom Field', 'all-sources-images' ); ?></option>
                                    <option value="custom_request" <?php selected( isset( $allsi_block['based_on'] ) ? $allsi_block['based_on'] : '', 'custom_request' ); ?>><?php esc_html_e( 'Custom Request (Placeholders)', 'all-sources-images' ); ?></option>
                                    <option value="openai_extractor" <?php selected( isset( $allsi_block['based_on'] ) ? $allsi_block['based_on'] : '', 'openai_extractor' ); ?>><?php esc_html_e( 'OpenAI Keyword Extractor', 'all-sources-images' ); ?></option>
                                    <option value="ai_image_prompt" <?php selected( isset( $allsi_block['based_on'] ) ? $allsi_block['based_on'] : '', 'ai_image_prompt' ); ?>><?php esc_html_e( 'AI Image Prompt (Enhanced)', 'all-sources-images' ); ?></option>
                                </select>
                                <!-- Help text container -->
                                <?php 
                                $allsi_current_based_on = isset( $allsi_block['based_on'] ) ? $allsi_block['based_on'] : 'title';
                                $allsi_help_texts = array(
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
                                <div class="based-on-help-text help-<?php echo esc_attr( $allsi_current_based_on ); ?>">
                                    <i class="dashicons dashicons-info-outline"></i>
                                    <?php echo esc_html( $allsi_help_texts[ $allsi_current_based_on ] ); ?>
                                </div>
                            </div>
                            
                            <?php 
                            // Determine which sections should be visible
                            $allsi_show_text_analyser = in_array( $allsi_current_based_on, array( 'text_analyser', 'text_analyser_previous_paragraph', 'text_analyser_next_paragraph' ), true );
                            $allsi_show_tags = ( $allsi_current_based_on === 'tags' );
                            $allsi_show_categories = ( $allsi_current_based_on === 'categories' );
                            $allsi_show_custom_field = ( $allsi_current_based_on === 'custom_field' );
                            $allsi_show_custom_request = ( $allsi_current_based_on === 'custom_request' );
                            $allsi_show_openai_extractor = ( $allsi_current_based_on === 'openai_extractor' );
                            $allsi_show_ai_image_prompt = ( $allsi_current_based_on === 'ai_image_prompt' );
                            ?>
                            
                            <!-- Text Analyzer Language -->
                            <div class="form-row based-on-fields section_text_analyser <?php echo esc_attr( $allsi_show_text_analyser ? 'visible' : '' ); ?>">
                                <label><?php esc_html_e( 'Content Language', 'all-sources-images' ); ?></label>
                                <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][text_analyser_lang]" class="form-control" style="max-width: 200px;">
                                    <?php 
                                    $allsi_langs = array( 'en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'it' => 'Italian', 'pt' => 'Portuguese', 'nl' => 'Dutch', 'ru' => 'Russian', 'ja' => 'Japanese', 'zh' => 'Chinese', 'ar' => 'Arabic' );
                                    $allsi_current_lang = isset( $allsi_block['text_analyser_lang'] ) ? $allsi_block['text_analyser_lang'] : 'en';
                                    foreach ( $allsi_langs as $allsi_code => $allsi_name ) : ?>
                                        <option value="<?php echo esc_attr( $allsi_code ); ?>" <?php selected( $allsi_current_lang, $allsi_code ); ?>><?php echo esc_html( $allsi_name ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Tags Options -->
                            <div class="form-row based-on-fields section_tags <?php echo esc_attr( $allsi_show_tags ? 'visible' : '' ); ?>">
                                <label><?php esc_html_e( 'Tag Selection', 'all-sources-images' ); ?></label>
                                <div class="radio-group">
                                    <?php $allsi_current_tags = isset( $allsi_block['tags'] ) ? $allsi_block['tags'] : 'first_tag'; ?>
                                    <label><input type="radio" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][tags]" value="first_tag" <?php checked( $allsi_current_tags, 'first_tag' ); ?>> <?php esc_html_e( 'First Tag', 'all-sources-images' ); ?></label>
                                    <label><input type="radio" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][tags]" value="last_tag" <?php checked( $allsi_current_tags, 'last_tag' ); ?>> <?php esc_html_e( 'Last Tag', 'all-sources-images' ); ?></label>
                                    <label><input type="radio" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][tags]" value="random_tag" <?php checked( $allsi_current_tags, 'random_tag' ); ?>> <?php esc_html_e( 'Random Tag', 'all-sources-images' ); ?></label>
                                </div>
                            </div>
                            
                            <!-- Categories Options -->
                            <div class="form-row based-on-fields section_categories <?php echo esc_attr( $allsi_show_categories ? 'visible' : '' ); ?>">
                                <label><?php esc_html_e( 'Category Selection', 'all-sources-images' ); ?></label>
                                <div class="select-row" style="margin-bottom: 10px;">
                                    <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][categories]" class="form-control" style="max-width: 200px;">
                                        <?php $allsi_current_cat = isset( $allsi_block['categories'] ) ? $allsi_block['categories'] : 'first_category'; ?>
                                        <option value="first_category" <?php selected( $allsi_current_cat, 'first_category' ); ?>><?php esc_html_e( 'First Category', 'all-sources-images' ); ?></option>
                                        <option value="last_category" <?php selected( $allsi_current_cat, 'last_category' ); ?>><?php esc_html_e( 'Last Category', 'all-sources-images' ); ?></option>
                                        <option value="random_category" <?php selected( $allsi_current_cat, 'random_category' ); ?>><?php esc_html_e( 'Random Category', 'all-sources-images' ); ?></option>
                                    </select>
                                    <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][categories_level]" class="form-control" style="max-width: 200px;">
                                        <?php $allsi_current_level = isset( $allsi_block['categories_level'] ) ? $allsi_block['categories_level'] : 'child'; ?>
                                        <option value="child" <?php selected( $allsi_current_level, 'child' ); ?>><?php esc_html_e( 'Child (Most Specific)', 'all-sources-images' ); ?></option>
                                        <option value="parent" <?php selected( $allsi_current_level, 'parent' ); ?>><?php esc_html_e( 'Parent', 'all-sources-images' ); ?></option>
                                        <option value="grandparent" <?php selected( $allsi_current_level, 'grandparent' ); ?>><?php esc_html_e( 'Grandparent (Top Level)', 'all-sources-images' ); ?></option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Custom Field Options -->
                            <div class="form-row based-on-fields section_custom_field <?php echo esc_attr( $allsi_show_custom_field ? 'visible' : '' ); ?>">
                                <label><?php esc_html_e( 'Custom Field Name', 'all-sources-images' ); ?></label>
                                <input type="text" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][custom_field]" class="form-control" style="max-width: 300px;" placeholder="my_custom_field" value="<?php echo esc_attr( isset( $allsi_block['custom_field'] ) ? $allsi_block['custom_field'] : '' ); ?>">
                                <p class="description"><?php esc_html_e( 'Enter the meta key name of your custom field.', 'all-sources-images' ); ?></p>
                            </div>
                            
                            <!-- Custom Request Options -->
                            <div class="form-row based-on-fields section_custom_request <?php echo esc_attr( $allsi_show_custom_request ? 'visible' : '' ); ?>">
                                <label><?php esc_html_e( 'Custom Search Template', 'all-sources-images' ); ?></label>
                                <input type="text" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][custom_request]" class="form-control" style="max-width: 400px;" placeholder="%%Title%% %%Category%%" value="<?php echo esc_attr( isset( $allsi_block['custom_request'] ) ? $allsi_block['custom_request'] : '%%Title%% %%Category%%' ); ?>">
                                <p class="description">
                                    <?php esc_html_e( 'Available placeholders:', 'all-sources-images' ); ?> 
                                    <code>%%Title%%</code>, <code>%%Category%%</code>, <code>%%Tag%%</code>, <code>%%Taxonomy%%</code>
                                </p>
                            </div>
                            
                            <!-- OpenAI Keyword Extractor Options -->
                            <div class="form-row based-on-fields section_openai_extractor <?php echo esc_attr( $allsi_show_openai_extractor ? 'visible' : '' ); ?>">
                                <label><?php esc_html_e( 'OpenAI API Key', 'all-sources-images' ); ?></label>
                                <input type="password" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][openai_extractor_apikey]" class="form-control" style="max-width: 400px;" placeholder="sk-..." value="<?php echo esc_attr( isset( $allsi_block['openai_extractor_apikey'] ) ? $allsi_block['openai_extractor_apikey'] : '' ); ?>">
                                <div style="margin-top: 10px;">
                                    <label><?php esc_html_e( 'Number of Keywords', 'all-sources-images' ); ?></label>
                                    <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][openai_number_of_keywords]" class="form-control" style="max-width: 100px;">
                                        <?php $allsi_current_num = isset( $allsi_block['openai_number_of_keywords'] ) ? $allsi_block['openai_number_of_keywords'] : '2'; ?>
                                        <option value="1-2" <?php selected( $allsi_current_num, '1-2' ); ?>>1-2</option>
                                        <option value="2" <?php selected( $allsi_current_num, '2' ); ?>>2</option>
                                        <option value="3" <?php selected( $allsi_current_num, '3' ); ?>>3</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- AI Image Prompt Options -->
                            <div class="form-row based-on-fields section_ai_image_prompt <?php echo esc_attr( $allsi_show_ai_image_prompt ? 'visible' : '' ); ?>">
                                <label><?php esc_html_e( 'OpenAI API Key', 'all-sources-images' ); ?></label>
                                <input type="password" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][ai_prompt_apikey]" class="form-control" style="max-width: 400px;" placeholder="sk-..." value="<?php echo esc_attr( isset( $allsi_block['ai_prompt_apikey'] ) ? $allsi_block['ai_prompt_apikey'] : '' ); ?>">
                                <div style="margin-top: 10px;">
                                    <label><?php esc_html_e( 'Image Style', 'all-sources-images' ); ?></label>
                                    <select name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][ai_prompt_style]" class="form-control" style="max-width: 200px;">
                                        <?php $allsi_current_style = isset( $allsi_block['ai_prompt_style'] ) ? $allsi_block['ai_prompt_style'] : 'photorealistic'; ?>
                                        <option value="photorealistic" <?php selected( $allsi_current_style, 'photorealistic' ); ?>><?php esc_html_e( 'Photorealistic', 'all-sources-images' ); ?></option>
                                        <option value="illustration" <?php selected( $allsi_current_style, 'illustration' ); ?>><?php esc_html_e( 'Illustration', 'all-sources-images' ); ?></option>
                                        <option value="digital_art" <?php selected( $allsi_current_style, 'digital_art' ); ?>><?php esc_html_e( 'Digital Art', 'all-sources-images' ); ?></option>
                                        <option value="3d_render" <?php selected( $allsi_current_style, '3d_render' ); ?>><?php esc_html_e( '3D Render', 'all-sources-images' ); ?></option>
                                        <option value="oil_painting" <?php selected( $allsi_current_style, 'oil_painting' ); ?>><?php esc_html_e( 'Oil Painting', 'all-sources-images' ); ?></option>
                                        <option value="watercolor" <?php selected( $allsi_current_style, 'watercolor' ); ?>><?php esc_html_e( 'Watercolor', 'all-sources-images' ); ?></option>
                                        <option value="sketch" <?php selected( $allsi_current_style, 'sketch' ); ?>><?php esc_html_e( 'Sketch/Drawing', 'all-sources-images' ); ?></option>
                                        <option value="minimalist" <?php selected( $allsi_current_style, 'minimalist' ); ?>><?php esc_html_e( 'Minimalist', 'all-sources-images' ); ?></option>
                                        <option value="cinematic" <?php selected( $allsi_current_style, 'cinematic' ); ?>><?php esc_html_e( 'Cinematic', 'all-sources-images' ); ?></option>
                                    </select>
                                </div>
                                <div style="margin-top: 10px;">
                                    <label><?php esc_html_e( 'Custom Instructions (Optional)', 'all-sources-images' ); ?></label>
                                    <input type="text" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][ai_prompt_custom_instructions]" class="form-control" style="max-width: 400px;" placeholder="<?php esc_attr_e( 'e.g., vibrant colors, no text', 'all-sources-images' ); ?>" value="<?php echo esc_attr( isset( $allsi_block['ai_prompt_custom_instructions'] ) ? $allsi_block['ai_prompt_custom_instructions'] : '' ); ?>">
                                </div>
                            </div>
                            
                            <!-- Title Options -->
                            <div class="form-row section_title" style="<?php echo esc_attr( ( isset( $allsi_block['based_on'] ) && $allsi_block['based_on'] !== 'title' ) ? 'display:none;' : '' ); ?>">
                                <label><?php esc_html_e( 'Title Selection', 'all-sources-images' ); ?></label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][title_selection]" value="full_title" <?php checked( isset( $allsi_block['title_selection'] ) ? $allsi_block['title_selection'] : 'full_title', 'full_title' ); ?>>
                                        <?php esc_html_e( 'Full title', 'all-sources-images' ); ?>
                                    </label>
                                    <label>
                                        <input type="radio" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][title_selection]" value="cut_title" <?php checked( isset( $allsi_block['title_selection'] ) ? $allsi_block['title_selection'] : '', 'cut_title' ); ?>>
                                        <?php esc_html_e( 'Specific Part', 'all-sources-images' ); ?>:
                                        <input type="number" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][title_length]" min="1" value="<?php echo isset( $allsi_block['title_length'] ) ? esc_attr( $allsi_block['title_length'] ) : '3'; ?>" class="form-control" style="width: 60px; margin-left: 5px;">
                                        <span style="margin-left: 5px;"><?php esc_html_e( 'first words', 'all-sources-images' ); ?></span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Translate to English -->
                            <div class="form-row">
                                <label><?php esc_html_e( 'Translate to English', 'all-sources-images' ); ?></label>
                                <div class="field-content">
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][translation_EN]" value="true" <?php checked( ! isset( $allsi_block['translation_EN'] ) || $allsi_block['translation_EN'] === 'true' ); ?>>
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
                                        <input type="radio" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][selected_image]" value="first_result" <?php checked( isset( $allsi_block['selected_image'] ) ? $allsi_block['selected_image'] : 'first_result', 'first_result' ); ?>>
                                        <?php esc_html_e( 'First result', 'all-sources-images' ); ?>
                                    </label>
                                    <label class="<?php echo esc_attr( $allsi_class_disabled ); ?>">
                                        <input type="radio" name="ALLSI_plugin_main_settings[image_block][<?php echo esc_attr( $allsi_display_index ); ?>][selected_image]" value="random_result" <?php echo esc_attr( $allsi_disabled ); ?> <?php checked( isset( $allsi_block['selected_image'] ) ? $allsi_block['selected_image'] : '', 'random_result' ); ?>>
                                        <?php esc_html_e( 'Random result', 'all-sources-images' ); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php $allsi_display_index++; ?>
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
