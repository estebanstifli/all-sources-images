<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();


?>
<tr valign="top" class="section_basedon image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img">
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Search Based on', 'all-sources-images' ); ?></label>
        </th>

        <td class="based_on radio-list">
                <select name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][based_on]" class="select-custom-location form-control">
                        <option value="title" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'title'); ?>><?php esc_html_e( 'Title', 'all-sources-images' ); ?></option>
                        <option value="text_analyser" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'text_analyser'); ?>><?php esc_html_e( 'Text Analyzer: Full text', 'all-sources-images' ); ?></option>
                        <option value="text_analyser_previous_paragraph" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'text_analyser_previous_paragraph'); ?> <?php echo $disabled; ?> class="option_analyzer <?php echo $class_analyzer; ?>"><?php esc_html_e( 'Text Analyzer: Previous paragraph', 'all-sources-images' ); ?></option>
                        <option value="text_analyser_next_paragraph" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'text_analyser_next_paragraph'); ?> <?php echo $disabled; ?> class="option_analyzer <?php echo $class_analyzer; ?>"><?php esc_html_e( 'Text Analyzer: Next paragraph', 'all-sources-images' ); ?></option>
                        <option value="tags" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'tags'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'Tags', 'all-sources-images' ); ?></option>
                        <option value="categories" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'categories'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'Categories', 'all-sources-images' ); ?></option>
                        <option value="custom_field" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'custom_field'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'Custom Field', 'all-sources-images' ); ?></option>
                        <option value="custom_request" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'custom_request'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'Custom Request', 'all-sources-images' ); ?></option>
                        <option value="openai_extractor" <?php selected(isset($block['based_on']) ? $block['based_on'] : '', 'openai_extractor'); ?> <?php echo $disabled; ?>><?php esc_html_e( 'OpenAI Keyword Extractor', 'all-sources-images' ); ?></option>
                </select>
        </td>
</tr>

<?php 
    
?>

    <tr valign="top" class="section_tags image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'tags') ? 'style="display:none;"': ''); ?>>
            <th scope="row">
                    <label for="hseparator"><?php esc_html_e( 'Tags', 'all-sources-images' ); ?></label>
            </th>
            <td class="radio-list tags">
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="first_tag" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][tags]" type="radio" <?php echo( !empty( $block['tags']) && $block['tags'] == 'first_tag' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'First tag', 'all-sources-images' ); ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="last_tag" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][tags]" type="radio" <?php echo( !empty( $block['tags']) && $block['tags'] == 'last_tag' )? 'checked': ''; ?>><span></span> <?php esc_html_e( 'Last tag', 'all-sources-images' ); ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="random_tag" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][tags]" type="radio" <?php echo( !empty( $block['tags']) && $block['tags'] == 'random_tag' )? 'checked': ''; ?>><span></span> <?php esc_html_e( 'Random tag', 'all-sources-images' ); ?></label>
            </td>
    </tr>

    <tr valign="top" class="section_categories image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'categories') ? 'style="display:none;"': ''); ?>>
            <th scope="row">
                    <label for="hseparator"><?php esc_html_e( 'Categories', 'all-sources-images' ); ?></label>
            </th>
            <td class="radio-list categories">
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="first_category" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][categories]" type="radio" <?php echo( !empty( $block['categories']) && $block['categories'] == 'first_category' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'First category', 'all-sources-images' ); ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="last_category" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][categories]" type="radio" <?php echo( !empty( $block['categories']) && $block['categories'] == 'last_category' )? 'checked': ''; ?>><span></span> <?php esc_html_e( 'Last category', 'all-sources-images' ); ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="random_category" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][categories]" type="radio" <?php echo( !empty( $block['categories']) && $block['categories'] == 'random_category' )? 'checked': ''; ?>><span></span> <?php esc_html_e( 'Random category', 'all-sources-images' ); ?></label>
            </td>
    </tr>

    <tr valign="top" class="section_custom_field image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'custom_field') ? 'style="display:none;"': ''); ?>>
            <th scope="row">
                    <label for="hseparator"><?php esc_html_e( 'Custom field Name', 'all-sources-images' ); ?></label>
            </th>
            <td class="custom_field">
                    <label><input type="text" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][custom_field]" class="form-control" value="<?php echo( isset( $block['custom_field'] ) && !empty( $block['custom_field']) )? $block['custom_field']: ''; ?>" ></label>
            </td>
    </tr>

    <tr valign="top" class="section_custom_request image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'custom_request') ? 'style="display:none;"': ''); ?>>

            <th scope="row">
                    <label for="hseparator"><?php esc_html_e( 'Custom Request', 'all-sources-images' ); ?></label>
            </th>
            <td class="custom_field">

                <div id="custom-request-buttons">
                    <p draggable="true"><span class="button-custom" draggable="false">Title</span></p>
                    <p draggable="true"><span class="button-custom" draggable="false">Category</span></p>
                    <p draggable="true"><span class="button-custom" draggable="false">Tag</span></p>
                    <p draggable="true"><span class="button-custom" draggable="false">Taxonomy</span></p>
                </div>

                <div class="textarea-editable" contenteditable="true"><?php echo( isset( $block['custom_request'] ) && !empty( $block['custom_request']) )? $block['custom_request']: esc_html_e( 'This is a simple request including the %%Title%%', 'all-sources-images' ); ?></div>
                <label>
                    <input type="hidden" class="custom_request" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][custom_request]" class="form-control" value="<?php echo( isset( $block['custom_request'] ) && !empty( $block['custom_request']) )? esc_html( $block['custom_request'] ): ''; ?>" >
                </label>

            </td>
    </tr>

    <tr valign="top" class="category_choice based_on_bottom image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'custom_request') ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Category Level', 'all-sources-images' ); ?></label>
                <p class="description">
                    <?php esc_html_e( 'Choose the category level to use.', 'all-sources-images' ); ?>
                </p>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_level" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][category_choice] " type="radio" <?php echo( !empty( $block['category_choice']) && $block['category_choice'] == 'first_level' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Child category', 'all-sources-images' ); ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php echo $class_disabled; ?>"><input value="second_level" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][category_choice] " type="radio" <?php echo( !empty( $block['category_choice']) && $block['category_choice'] == 'second_level' )? 'checked': ''; echo $disabled ?> ><span></span> <?php esc_html_e( 'Parent category', 'all-sources-images' ); ?></label>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php echo $class_disabled; ?>"><input value="third_level" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][category_choice] " type="radio" <?php echo( !empty( $block['category_choice']) && $block['category_choice'] == 'third_level' )? 'checked': ''; echo $disabled ?> ><span></span> <?php esc_html_e( 'Grandparent category', 'all-sources-images' ); ?></label>                                        
        </td>
    </tr>


        <tr valign="top" class="category_choice based_on_bottom image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'custom_request') ? 'style="display:none;"': ''); ?>>
                <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Taxonomy Slug', 'all-sources-images' ); ?></label>
                </th>
                <td class="custom_taxo_field" valign="top">
                <input type="text" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][taxonomy_field]" class="col-lg-4 col-md-9 col-sm-12 form-control" value="<?php echo( isset( $block['taxonomy_field'] ) && !empty( $block['taxonomy_field']) )? $block['taxonomy_field']: ''; ?>" >
                </td>
        </tr>

    

    <tr valign="top" class="category_choice based_on_bottom image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'custom_request') ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Taxonomy Level', 'all-sources-images' ); ?></label>
                <p class="description">
                    <?php esc_html_e( 'Choose the taxonomy level to use.', 'all-sources-images' ); ?>
                </p>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_level" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][taxonomy_choice] " type="radio" <?php echo( !empty( $block['taxonomy_choice']) && $block['taxonomy_choice'] == 'first_level' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Child taxonomy', 'all-sources-images' ); ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php echo $class_disabled; ?>"><input value="second_level" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][taxonomy_choice] " type="radio" <?php echo( !empty( $block['taxonomy_choice']) && $block['taxonomy_choice'] == 'second_level' )? 'checked': ''; echo $disabled ?> ><span></span> <?php esc_html_e( 'Parent taxonomy', 'all-sources-images' ); ?></label>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php echo $class_disabled; ?>"><input value="third_level" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][taxonomy_choice] " type="radio" <?php echo( !empty( $block['taxonomy_choice']) && $block['taxonomy_choice'] == 'third_level' )? 'checked': ''; echo $disabled ?> ><span></span> <?php esc_html_e( 'Grandparent taxonomy', 'all-sources-images' ); ?></label>                                        
        </td>
    </tr>

    <tr valign="top" class="section_openai_extractor image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'openai_extractor') ? 'style="display:none;"': ''); ?>>
            <th scope="row">
                    <label for="hseparator"><?php esc_html_e( 'OpenAI API Key', 'all-sources-images' ); ?></label>
            </th>
            <td id="password-openai" class="password">
                <input type="password" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][openai_extractor_apikey]" class="form-control" value="<?php echo( isset( $block['openai_extractor_apikey'] ) && !empty( $block['openai_extractor_apikey']) )? $block['openai_extractor_apikey']: ''; ?>">
                <i id="togglePassword"></i>
            </td>
    </tr>

        <tr valign="top" class="section_openai_extractor image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'openai_extractor') ? 'style="display:none;"': ''); ?>>
                <th scope="row">
                        <label for="hseparator"><?php esc_html_e( 'Number of keywords to extract from title', 'all-sources-images' ); ?></label>
                </th>
                <td class="number_of_keywords radio-inline">
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="1-2" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][openai_number_of_keywords] " type="radio" <?php echo( !empty( $block['openai_number_of_keywords']) && $block['openai_number_of_keywords'] == '1-2'  )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'From 1 to 2 words', 'all-sources-images' ); ?></label><br/>
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="2"   name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][openai_number_of_keywords] " type="radio" <?php echo( !empty( $block['openai_number_of_keywords']) && $block['openai_number_of_keywords'] == '2'   )?  'checked': ''; ?> ><span></span> <?php esc_html_e( '2 words', 'all-sources-images' ); ?></label><br/>
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="3"   name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][openai_number_of_keywords] " type="radio" <?php echo( !empty( $block['openai_number_of_keywords']) && $block['openai_number_of_keywords'] == '3' )?    'checked': ''; ?> ><span></span> <?php esc_html_e( '3 words', 'all-sources-images' ); ?></label>
                </td>
        </tr>

<?php 
        } 
    ?>

<tr valign="top" class="section_title image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" <?php echo(($block['based_on'] != 'title') ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Title', 'all-sources-images' ); ?></label>
        </th>
        <td class="chosen_title radio-inline">
                <label class="radio radio-outline radio-outline-2x radio-primary"><input value="full_title" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][title_selection] " type="radio" <?php echo( !empty( $block['title_selection']) && $block['title_selection'] == 'full_title' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Full title', 'all-sources-images' ); ?></label><br/>
                <label class="radio radio-outline radio-outline-2x radio-primary"><input value="cut_title" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][title_selection] " type="radio" <?php echo( !empty( $block['title_selection']) && $block['title_selection'] == 'cut_title' )? 'checked': ''; ?>><span></span> <?php esc_html_e( 'Specific Part', 'all-sources-images' ); ?> : </label>
                <input type="number" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][title_length]" min="1" class="col-lg-4 col-md-9 col-sm-12 form-control length_cut_title" value="<?php echo( isset( $block['title_length'] ) && !empty( $block['title_length']) )? (int)$block['title_length']: '3'; ?>" <?php echo( !empty( $block['title_selection']) && $block['title_selection'] == 'cut_title' )? '': 'disabled'; ?>> <i><?php esc_html_e( 'first words of the title', 'all-sources-images' ); ?></i>
        </td>
</tr>

<tr valign="top" class="section_text_analyser image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img" 
<?php echo(($block['based_on'] != 'text_analyser') && ($block['based_on'] != 'text_analyser_previous_paragraph') && ($block['based_on'] != 'text_analyser_next_paragraph') ? 'style="display:none;"': ''); ?>>
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Post Content Language', 'all-sources-images' ); ?></label>
        </th>
        <td class="text_analyser">
                <select name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][text_analyser_lang]" class="form-control form-control-lg" >
                    <?php
                    if( isset( $block['text_analyser_lang'] ) && $block['text_analyser_lang'] ) {
                        $current_wp_lang = $block['text_analyser_lang'];
                    } else {
                        $current_wp_lang = explode('-', get_bloginfo('language'));
                        $current_wp_lang = $current_wp_lang[0];
                    }

                    $langs = array(
                            esc_html__( '-- Default --', 'all-sources-images' )    => '',
                            esc_html__( 'Arabic', 'all-sources-images' )           => 'ar',
                            esc_html__( 'Bulgarian', 'all-sources-images' )        => 'bg',
                            esc_html__( 'Czech', 'all-sources-images' )            => 'cs',
                            esc_html__( 'Danish', 'all-sources-images' )           => 'da',
                            esc_html__( 'German', 'all-sources-images' )           => 'de',
                            esc_html__( 'Greek', 'all-sources-images' )            => 'el',
                            esc_html__( 'English', 'all-sources-images' )          => 'en',
                            esc_html__( 'Spanish', 'all-sources-images' )          => 'es',
                            esc_html__( 'Estonian', 'all-sources-images' )         => 'et',
                            esc_html__( 'Persian', 'all-sources-images' )          => 'fa',
                            esc_html__( 'Finnish', 'all-sources-images' )          => 'fi',
                            esc_html__( 'French', 'all-sources-images' )           => 'fr',
                            esc_html__( 'Hebrew', 'all-sources-images' )           => 'he',
                            esc_html__( 'Hindi', 'all-sources-images' )            => 'hi',
                            esc_html__( 'Croatian', 'all-sources-images' )         => 'hr',
                            esc_html__( 'Hungarian', 'all-sources-images' )        => 'hu',
                            esc_html__( 'Armenian', 'all-sources-images' )         => 'hy',
                            esc_html__( 'Indonesian', 'all-sources-images' )       => 'id',
                            esc_html__( 'Italian', 'all-sources-images' )          => 'it',
                            esc_html__( 'Japanese', 'all-sources-images' )         => 'ja',
                            esc_html__( 'Korean', 'all-sources-images' )           => 'ko',
                            esc_html__( 'Lithuanian', 'all-sources-images' )       => 'lt',
                            esc_html__( 'Latvian', 'all-sources-images' )          => 'lv',
                            esc_html__( 'Dutch', 'all-sources-images' )            => 'nl',
                            esc_html__( 'Norwegian', 'all-sources-images' )        => 'no',
                            esc_html__( 'Polish', 'all-sources-images' )           => 'pl',
                            esc_html__( 'Portuguese', 'all-sources-images' )       => 'pt',
                            esc_html__( 'Romanian', 'all-sources-images' )         => 'ro',
                            esc_html__( 'Russian', 'all-sources-images' )          => 'ru',
                            esc_html__( 'Slovak', 'all-sources-images' )           => 'sk',
                            esc_html__( 'Slovenian', 'all-sources-images' )        => 'sl',
                            esc_html__( 'Swedish', 'all-sources-images' )          => 'sv',
                            esc_html__( 'Thai', 'all-sources-images' )             => 'th',
                            esc_html__( 'Turkish', 'all-sources-images' )          => 'tr',
                            esc_html__( 'Vietnamese', 'all-sources-images' )       => 'vi',
                            esc_html__( 'Chinese', 'all-sources-images' )          => 'zh',
                    );
                    ksort( $langs );

                    foreach( $langs as $name_lang => $code_lang ) {
                            $choose=($current_wp_lang == $code_lang)?'selected="selected"': '';
                            echo '<option '. $choose .' value="'. $code_lang .'">'. $name_lang .'</option>';
                    }
                    ?>
            </select>
        </td>
</tr>

<tr valign="top" class="translation_EN image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img">
    <th scope="row">
            <?php esc_html_e( 'Translate to English', 'all-sources-images' ); ?>
    </th>
    <td class="checkbox-list">
        <label class="checkbox <?php echo $checkbox_disabled; ?>"><input <?php echo( !empty( $block['translation_EN']) && $block['translation_EN'] == 'true' )? 'checked': ''; ?> name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][translation_EN]" type="checkbox" value="true"> <span></span> <?php esc_html_e( 'Translate', 'all-sources-images' ); ?></label>
        <p class="description">
            <?php esc_html_e( 'The "based on" phrase /keywords will be translated into English. This helps to get better results with most image databases.', 'all-sources-images' ); ?>
        </p>
    </td>
</tr>

<tr valign="top" class="selected_image based_on_bottom image-location-template image-block-<?php echo $blockIndex; ?> mid-add-block-img">
        <th scope="row">
                <label for="hseparator"><?php esc_html_e( 'Image Selection', 'all-sources-images' ); ?></label>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_result" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][selected_image] " type="radio" <?php echo( !empty( $block['selected_image']) && $block['selected_image'] == 'first_result' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'First result', 'all-sources-images' ); ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="random_result" name="ASI_plugin_main_settings[image_block][<?php echo $blockIndex; ?>][selected_image] " type="radio" <?php echo( !empty( $block['selected_image']) && $block['selected_image'] == 'random_result' )? 'checked': ''; ?> ><span></span> <?php esc_html_e( 'Random result', 'all-sources-images' ); ?></label>
        </td>
</tr>