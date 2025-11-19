<?php

if ( !function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
?>
<tr>
    <td colspan="2">
        <hr/>
    </td>
</tr>


<tr valign="top" class="image-location-template hidden image-block-0 top-add-block-img">
    <th scope="row">
        <?php 
esc_html_e( 'Featured Image / Inline Content', 'all-sources-images' );
?>
    </th>
    <td class="image_location radio-list">
        <label class="radio radio-outline radio-outline-2x radio-primary">
            <input value="featured" name="ASI_plugin_main_settings[image_block][0][image_location]" type="radio" checked>
            <span></span> <?php 
esc_html_e( 'Featured Image', 'all-sources-images' );
?>
        </label>
        <label class="radio radio-outline radio-outline-2x radio-primary">
            <input value="custom" name="ASI_plugin_main_settings[image_block][0][image_location]" type="radio">
            <span></span> <?php 
esc_html_e( 'Inline content', 'all-sources-images' );
?>
        </label>

        <?php 
?>

        <p class="description"><i><?php 
esc_html_e( '"Inline content" allows you to generate the image anywhere in the content', 'all-sources-images' );
?></i></p>
    </td>
</tr>


<?php 
?>

<tr valign="top" class="section_custom_image_position image-location-template hidden image-inside-content image-block-0 mid-add-block-img">
    <th scope="row">
        <label for="hseparator"><?php 
esc_html_e( 'Image position', 'all-sources-images' );
?></label>
    </th>
    <td class="custom_image_location" valign="top">
        <label><?php 
esc_html_e( 'Insert', 'all-sources-images' );
?>
            <select name="ASI_plugin_main_settings[image_block][0][image_custom_location_placement]" class="select-custom-location form-control">
                <option value="before"><?php 
esc_html_e( 'Before', 'all-sources-images' );
?></option>
                <option value="after"><?php 
esc_html_e( 'After', 'all-sources-images' );
?></option>
            </select> <?php 
esc_html_e( 'the', 'all-sources-images' );
?>
            <select name="ASI_plugin_main_settings[image_block][0][image_custom_location_position]" class="select-custom-location form-control">
                <option value="1"><?php 
esc_html_e( 'First', 'all-sources-images' );
?></option>
                <option value="2"><?php 
esc_html_e( 'Second', 'all-sources-images' );
?></option>
                <option value="3"><?php 
esc_html_e( 'Third', 'all-sources-images' );
?></option>
                <option value="4"><?php 
esc_html_e( 'Fourth', 'all-sources-images' );
?></option>
                <option value="5"><?php 
esc_html_e( 'Fifth', 'all-sources-images' );
?></option>
                <option value="6"><?php 
esc_html_e( 'Sixth', 'all-sources-images' );
?></option>
                <option value="7"><?php 
esc_html_e( 'Seventh', 'all-sources-images' );
?></option>
                <option value="8"><?php 
esc_html_e( 'Eighth', 'all-sources-images' );
?></option>
                <option value="9"><?php 
esc_html_e( 'Ninth', 'all-sources-images' );
?></option>
                <option value="10"><?php 
esc_html_e( 'Tenth', 'all-sources-images' );
?></option>
                <option value="last"><?php 
esc_html_e( 'Last', 'all-sources-images' );
?></option>
            </select>
            <select name="ASI_plugin_main_settings[image_block][0][image_custom_location_tag]" class="select-custom-location form-control">
                <option value="p"><?php 
esc_html_e( 'paragraph (p)', 'all-sources-images' );
?></option>
                <option value="h2">h2</option>
                <option value="h3">h3</option>
                <option value="h4">h4</option>
                <option value="h5">h5</option>
                <option value="h6">h6</option>
                <option value="div">div</option>
                <option value="a"><?php 
esc_html_e( 'link (a)', 'all-sources-images' );
?></option>
            </select>
        </label>
    </td>
</tr>

<tr valign="top" class="section_custom_image_size image-location-template image-inside-content hidden image-block-0 mid-add-block-img">
    <th scope="row">
        <label for="hseparator"><?php 
esc_html_e( 'Image size', 'all-sources-images' );
?></label>
    </th>
    <td class="custom_image_size" valign="top">
        <label>
            <select name="ASI_plugin_main_settings[image_block][0][image_custom_image_size]" class="select-custom-location form-control">
                <?php 
foreach ( $image_sizes as $image_size ) {
    ?>
                    <option value="<?php 
    echo $image_size;
    ?>">
                        <?php 
    echo $image_size;
    ?>
                    </option>
                <?php 
?>
            </select> 
        </label>
    </td>
</tr>

<tr valign="top" class="section_custom_image_bank hidden image-location-template image-block-0 mid-add-block-img">
    <th scope="row">
        <label for="hseparator"><?php 
esc_html_e( 'Image Source', 'all-sources-images' );
?></label>
    </th>
    <td class="custom_image_size" valign="top">
        <label>
            <select name="ASI_plugin_main_settings[image_block][0][api_chosen]" class="select-custom-location form-control">
            <?php 
foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) {
    if ( true === $api_auto_id[1] ) {
        ?>
                    <option value="<?php 
        echo $api_auto_id[0];
        ?>"><?php 
        echo $api_auto_name;
        ?></option>
            <?php 
    }
?>
            </select> 
        </label>
    </td>
</tr>

<tr valign="top" class="section_custom_image_bank hidden image-location-template image-block-0 mid-add-block-img">
    <th scope="row">
        <label for="hseparator"><?php 
esc_html_e( 'Second Image Source', 'all-sources-images' );
?></label>
    </th>
    <td class="custom_image_size" valign="top">
        <label>
            <select name="ASI_plugin_main_settings[image_block][0][api_chosen_2]" class="select-custom-location form-control">
                <option value="none"><?php 
esc_html_e( 'None', 'all-sources-images' );
?></option>
                <?php 
foreach ( $list_api_auto as $api_auto_name => $api_auto_id ) {
    if ( true === $api_auto_id[1] ) {
        ?>
                        <option value="<?php 
        echo $api_auto_id[0];
        ?>"><?php 
        echo $api_auto_name;
        ?></option>
                <?php 
    }
?>
            </select> 
        </label>
    </td>
</tr>



<tr valign="top" class="section_basedon image-location-template hidden image-block-0 mid-add-block-img">
        <th scope="row">
                <label for="hseparator"><?php 
esc_html_e( 'Search Based on', 'all-sources-images' );
?></label>
        </th>
        <td class="based_on radio-list">
                <select name="ASI_plugin_main_settings[image_block][0][based_on]" class="select-custom-location form-control">
                        <option value="title" selected><?php 
esc_html_e( 'Title', 'all-sources-images' );
?></option>
                        <option value="text_analyser"><?php 
esc_html_e( 'Text Analyzer: Full text', 'all-sources-images' );
?></option>
                        <option value="text_analyser_previous_paragraph" class="option_analyzer hidden"><?php 
esc_html_e( 'Text Analyzer: Previous paragraph', 'all-sources-images' );
?></option>
                        <option value="text_analyser_next_paragraph" class="option_analyzer hidden"><?php 
esc_html_e( 'Text Analyzer: Next paragraph', 'all-sources-images' );
?></option>
                        <option value="tags" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Tags', 'all-sources-images' );
?></option>
                        <option value="categories" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Categories', 'all-sources-images' );
?></option>
                        <option value="custom_field" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Custom Field', 'all-sources-images' );
?></option>
                        <option value="custom_request" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'Custom Request', 'all-sources-images' );
?></option>
                        <option value="openai_extractor" <?php 
echo $disabled;
?>><?php 
esc_html_e( 'OpenAI Keyword Extractor', 'all-sources-images' );
?></option>
                </select>
        </td>
</tr>

<?php 

        ?>

    <tr valign="top" class="section_tags image-location-template hidden image-block-0 mid-add-block-img">
            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Tags', 'all-sources-images' );
        ?></label>
            </th>
            <td class="radio-list tags">
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="first_tag" name="ASI_plugin_main_settings[image_block][0][tags]" type="radio" checked><span></span> <?php 
        esc_html_e( 'First tag', 'all-sources-images' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="last_tag" name="ASI_plugin_main_settings[image_block][0][tags]" type="radio"><span></span> <?php 
        esc_html_e( 'Last tag', 'all-sources-images' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="random_tag" name="ASI_plugin_main_settings[image_block][0][tags]" type="radio"><span></span> <?php 
        esc_html_e( 'Random tag', 'all-sources-images' );
        ?></label>
            </td>
    </tr>

    <tr valign="top" class="section_categories image-location-template hidden image-block-0 mid-add-block-img">
            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Categories', 'all-sources-images' );
        ?></label>
            </th>
            <td class="radio-list categories">
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="first_category" name="ASI_plugin_main_settings[image_block][0][categories]" type="radio" checked><span></span> <?php 
        esc_html_e( 'First category', 'all-sources-images' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="last_category" name="ASI_plugin_main_settings[image_block][0][categories]" type="radio"><span></span> <?php 
        esc_html_e( 'Last category', 'all-sources-images' );
        ?></label>
                    <label class="radio radio-outline radio-outline-2x radio-primary"><input value="random_category" name="ASI_plugin_main_settings[image_block][0][categories]" type="radio"><span></span> <?php 
        esc_html_e( 'Random category', 'all-sources-images' );
        ?></label>
            </td>
    </tr>

    <tr valign="top" class="section_custom_field image-location-template hidden image-block-0 mid-add-block-img">
            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Custom field Name', 'all-sources-images' );
        ?></label>
            </th>
            <td class="custom_field">
                    <label><input type="text" name="ASI_plugin_main_settings[image_block][0][custom_field]" class="form-control" value="" ></label>
            </td>
    </tr>

    <tr valign="top" class="section_custom_request image-location-template hidden image-block-0 mid-add-block-img">

            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'Custom Request', 'all-sources-images' );
        ?></label>
            </th>
            <td class="custom_field">

                <div id="custom-request-buttons">
                    <p draggable="true"><span class="button-custom" draggable="false">Title</span></p>
                    <p draggable="true"><span class="button-custom" draggable="false">Category</span></p>
                    <p draggable="true"><span class="button-custom" draggable="false">Tag</span></p>
                    <p draggable="true"><span class="button-custom" draggable="false">Taxonomy</span></p>
                </div>

                <div class="textarea-editable" contenteditable="true"><?php 
        esc_html_e( 'This is a simple request including the %%Title%%', 'all-sources-images' );
        ?></div>
                <label>
                    <input type="hidden" class="custom_request" name="ASI_plugin_main_settings[image_block][0][custom_request]" class="form-control" value="" >
                </label>

            </td>
    </tr>

    <tr valign="top" class="category_choice based_on_bottom image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /* echo(($options['based_on'] != 'custom_request') ? 'style="display:none;"': '');*/
        ?>>
        <th scope="row">
                <label for="hseparator"><?php 
        esc_html_e( 'Category Level', 'all-sources-images' );
        ?></label>
                <p class="description">
                    <?php 
        esc_html_e( 'Choose the category level to use.', 'all-sources-images' );
        ?>
                </p>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_level" name="ASI_plugin_main_settings[image_block][0][category_choice] " type="radio" checked><span></span> <?php 
        esc_html_e( 'Child category', 'all-sources-images' );
        ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="second_level" name="ASI_plugin_main_settings[image_block][0][category_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Parent category', 'all-sources-images' );
        ?></label>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="third_level" name="ASI_plugin_main_settings[image_block][0][category_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Grandparent category', 'all-sources-images' );
        ?></label>                                        
        </td>
    </tr>

    <tr valign="top" class="category_choice based_on_bottom image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /* echo(($block['based_on'] != 'custom_request') ? 'style="display:none;"': '');*/
        ?>>
            <th scope="row">
            <label for="hseparator"><?php 
        esc_html_e( 'Taxonomy Slug', 'all-sources-images' );
        ?></label>
            </th>
            <td class="custom_taxo_field" valign="top">
            <input type="text" name="ASI_plugin_main_settings[image_block][0][taxonomy_field]" class="col-lg-4 col-md-9 col-sm-12 form-control" value="" >
            </td>
    </tr>

    <tr valign="top" class="category_choice based_on_bottom image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /* echo(($options['based_on'] != 'custom_request') ? 'style="display:none;"': '');*/
        ?>>
        <th scope="row">
                <label for="hseparator"><?php 
        esc_html_e( 'Taxonomy Level', 'all-sources-images' );
        ?></label>
                <p class="description">
                    <?php 
        esc_html_e( 'Choose the taxonomy level to use.', 'all-sources-images' );
        ?>
                </p>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_level" name="ASI_plugin_main_settings[image_block][0][taxonomy_choice] " type="radio" checked><span></span> <?php 
        esc_html_e( 'Child taxonomy', 'all-sources-images' );
        ?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="second_level" name="ASI_plugin_main_settings[image_block][0][taxonomy_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Parent taxonomy', 'all-sources-images' );
        ?></label>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
        echo $class_disabled;
        ?>"><input value="third_level" name="ASI_plugin_main_settings[image_block][0][taxonomy_choice] " type="radio" <?php 
        echo $disabled;
        ?> ><span></span> <?php 
        esc_html_e( 'Grandparent taxonomy', 'all-sources-images' );
        ?></label>                                        
        </td>
    </tr>

    <tr valign="top" class="section_openai_extractor image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /*echo(($options['based_on'] != 'openai_extractor') ? 'style="display:none;"': '');*/
        ?>>
            <th scope="row">
                    <label for="hseparator"><?php 
        esc_html_e( 'OpenAI API Key', 'all-sources-images' );
        ?></label>
            </th>
            <td id="password-openai" class="password">
                <input type="password" name="ASI_plugin_main_settings[openai_extractor_apikey]" class="form-control" value="">
                <i id="togglePassword"></i>
            </td>
    </tr>

        <tr valign="top" class="section_openai_extractor image-location-template hidden image-block-0 mid-add-block-img" <?php 
        /*echo(($options['based_on'] != 'openai_extractor') ? 'style="display:none;"': ''); */
        ?>>
                <th scope="row">
                        <label for="hseparator"><?php 
        esc_html_e( 'Number of keywords to extract from title', 'all-sources-images' );
        ?></label>
                </th>
                <td class="number_of_keywords radio-inline">
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="1-2" name="ASI_plugin_main_settings[image_block][0][openai_number_of_keywords] " type="radio" checked><span></span> <?php 
        esc_html_e( 'From 1 to 2 words', 'all-sources-images' );
        ?></label><br/>
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="2"   name="ASI_plugin_main_settings[image_block][0][openai_number_of_keywords] " type="radio"><span></span> <?php 
        esc_html_e( '2 words', 'all-sources-images' );
        ?></label><br/>
                        <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="3"   name="ASI_plugin_main_settings[image_block][0][openai_number_of_keywords] " type="radio"><span></span> <?php 
        esc_html_e( '3 words', 'all-sources-images' );
        ?></label>
                </td>
        </tr>

<?php 
    }
?>

<tr valign="top" class="section_title image-location-template hidden image-block-0 mid-add-block-img" <?php 
/* echo(($options['based_on'] != 'title') ? 'style="display:none;"': '');*/
?>>
        <th scope="row">
                <label for="hseparator"><?php 
esc_html_e( 'Title', 'all-sources-images' );
?></label>
        </th>
        <td class="chosen_title radio-inline">
            <label class="radio radio-outline radio-outline-2x radio-primary"><input value="full_title" name="ASI_plugin_main_settings[image_block][0][title_selection] " type="radio" checked><span></span> <?php 
esc_html_e( 'Full title', 'all-sources-images' );
?></label><br/>
                <label class="radio radio-outline radio-outline-2x radio-primary"><input value="cut_title" name="ASI_plugin_main_settings[image_block][0][title_selection] " type="radio"><span></span> <?php 
esc_html_e( 'Specific Part', 'all-sources-images' );
?> : </label>
                <input type="number" name="ASI_plugin_main_settings[image_block][0][title_length]" min="1" class="col-lg-4 col-md-9 col-sm-12 form-control length_cut_title" value="3" disabled> <i><?php 
esc_html_e( 'first words of the title', 'all-sources-images' );
?></i>
        </td>
</tr>

<tr valign="top" class="section_text_analyser image-location-template hidden image-block-0 mid-add-block-img" <?php 
/*echo(($options['based_on'] != 'text_analyser') ? 'style="display:none;"': '');*/
?>>
        <th scope="row">
                <label for="hseparator"><?php 
esc_html_e( 'Post Content Language', 'all-sources-images' );
?></label>
        </th>
        <td class="text_analyser">
                <select name="ASI_plugin_main_settings[image_block][0][text_analyser_lang]" class="form-control form-control-lg" >
                    <?php 
$current_wp_lang = explode( '-', get_bloginfo( 'language' ) );
$current_wp_lang = $current_wp_lang[0];
$langs = array(
    esc_html__( '-- Default --', 'all-sources-images' ) => '',
    esc_html__( 'Arabic', 'all-sources-images' )        => 'ar',
    esc_html__( 'Bulgarian', 'all-sources-images' )     => 'bg',
    esc_html__( 'Czech', 'all-sources-images' )         => 'cs',
    esc_html__( 'Danish', 'all-sources-images' )        => 'da',
    esc_html__( 'German', 'all-sources-images' )        => 'de',
    esc_html__( 'Greek', 'all-sources-images' )         => 'el',
    esc_html__( 'English', 'all-sources-images' )       => 'en',
    esc_html__( 'Spanish', 'all-sources-images' )       => 'es',
    esc_html__( 'Estonian', 'all-sources-images' )      => 'et',
    esc_html__( 'Persian', 'all-sources-images' )       => 'fa',
    esc_html__( 'Finnish', 'all-sources-images' )       => 'fi',
    esc_html__( 'French', 'all-sources-images' )        => 'fr',
    esc_html__( 'Hebrew', 'all-sources-images' )        => 'he',
    esc_html__( 'Hindi', 'all-sources-images' )         => 'hi',
    esc_html__( 'Croatian', 'all-sources-images' )      => 'hr',
    esc_html__( 'Hungarian', 'all-sources-images' )     => 'hu',
    esc_html__( 'Armenian', 'all-sources-images' )      => 'hy',
    esc_html__( 'Indonesian', 'all-sources-images' )    => 'id',
    esc_html__( 'Italian', 'all-sources-images' )       => 'it',
    esc_html__( 'Japanese', 'all-sources-images' )      => 'ja',
    esc_html__( 'Korean', 'all-sources-images' )        => 'ko',
    esc_html__( 'Lithuanian', 'all-sources-images' )    => 'lt',
    esc_html__( 'Latvian', 'all-sources-images' )       => 'lv',
    esc_html__( 'Dutch', 'all-sources-images' )         => 'nl',
    esc_html__( 'Norwegian', 'all-sources-images' )     => 'no',
    esc_html__( 'Polish', 'all-sources-images' )        => 'pl',
    esc_html__( 'Portuguese', 'all-sources-images' )    => 'pt',
    esc_html__( 'Romanian', 'all-sources-images' )      => 'ro',
    esc_html__( 'Russian', 'all-sources-images' )       => 'ru',
    esc_html__( 'Slovak', 'all-sources-images' )        => 'sk',
    esc_html__( 'Slovenian', 'all-sources-images' )     => 'sl',
    esc_html__( 'Swedish', 'all-sources-images' )       => 'sv',
    esc_html__( 'Thai', 'all-sources-images' )          => 'th',
    esc_html__( 'Turkish', 'all-sources-images' )       => 'tr',
    esc_html__( 'Vietnamese', 'all-sources-images' )    => 'vi',
    esc_html__( 'Chinese', 'all-sources-images' )       => 'zh',
);
ksort( $langs );
foreach ( $langs as $name_lang => $code_lang ) {
    $choose = ( $current_wp_lang == $code_lang ? 'selected="selected"' : '' );
    echo '<option ' . $choose . ' value="' . $code_lang . '">' . $name_lang . '</option>';
?>
            </select>
        </td>
</tr>

<tr valign="top" class="translation_EN image-location-template hidden image-block-0 mid-add-block-img">
    <th scope="row">
            <?php 
esc_html_e( 'Translate to English', 'all-sources-images' );
?>
    </th>
    <td class="checkbox-list">
        <label class="checkbox <?php 
echo $checkbox_disabled;
?>"><input name="ASI_plugin_main_settings[image_block][0][translation_EN]" type="checkbox" value="true"> <span></span> <?php 
esc_html_e( 'Translate', 'all-sources-images' );
?></label>
        <p class="description">
            <?php 
esc_html_e( 'The "based on" phrase /keywords will be translated into English. This helps to get better results with most image databases.', 'all-sources-images' );
?>
        </p>
    </td>
</tr>

<tr valign="top" class="selected_image based_on_bottom image-location-template hidden image-block-0 mid-add-block-img">
        <th scope="row">
                <label for="hseparator"><?php 
esc_html_e( 'Image Selection', 'all-sources-images' );
?></label>
        </th>
        <td class="result_position radio-inline">
            <label  class="radio radio-outline radio-outline-2x radio-primary"><input value="first_result" name="ASI_plugin_main_settings[image_block][0][selected_image] " type="radio" checked><span></span> <?php 
esc_html_e( 'First result', 'all-sources-images' );
?></label><br/>
            <label  class="radio radio-outline radio-outline-2x radio-primary <?php 
echo $class_disabled;
?>"><input value="random_result" name="ASI_plugin_main_settings[image_block][0][selected_image] " type="radio"><span></span> <?php 
esc_html_e( 'Random result', 'all-sources-images' );
?></label>
        </td>
</tr>



<!-- Button to remove a block -->
<tr valign="top" class="image-location-template hidden image-block-0 bottom-add-block-img">
    <td colspan="2">
        <button type="button" class="btn btn-sm font-weight-bolder btn-light-danger remove-block-btn" style="text-decoration: none;">[-] Delete</button>
    </td>
</tr>

<tr class="image-location-template hidden image-block-0">
    <td colspan="2">
        <hr/>
    </td>
</tr>