<?php
/**
 * Unsplash API Configuration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$options = get_option('ASI_plugin_banks_settings');
$apikey = isset($options['unsplash']['apikey']) ? $options['unsplash']['apikey'] : '';
$orientation = isset($options['unsplash']['orientation']) ? $options['unsplash']['orientation'] : 'all';
$content_filter = isset($options['unsplash']['content_filter']) ? $options['unsplash']['content_filter'] : 'low';
$color = isset($options['unsplash']['color']) ? $options['unsplash']['color'] : 'all';
$per_page = isset($options['unsplash']['per_page']) ? $options['unsplash']['per_page'] : 10;
$preferred_size = isset($options['unsplash']['preferred_size']) ? $options['unsplash']['preferred_size'] : 'regular';
$add_attribution = isset($options['unsplash']['add_attribution']) ? $options['unsplash']['add_attribution'] : 'yes';
$track_downloads = isset($options['unsplash']['track_downloads']) ? $options['unsplash']['track_downloads'] : 'yes';
?>

<tr>
    <td colspan="2" class="source-logo">
        <img src="<?php echo plugin_dir_url(__FILE__) . 'img/unsplash.png'; ?>" alt="Unsplash" />
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php _e('API Access Key Required', 'magic-post-thumbnail'); ?>:</strong> 
            <?php _e('Register your application at', 'magic-post-thumbnail'); ?> 
            <a href="https://unsplash.com/oauth/applications" target="_blank">unsplash.com/oauth/applications</a> 
            <?php _e('to get your Access Key (instant approval).', 'magic-post-thumbnail'); ?>
        </div>
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php _e('Rate Limits', 'magic-post-thumbnail'); ?>:</strong> 
            <?php _e('Demo mode: 50 requests/hour. Production mode (after approval): 5,000 requests/hour. Image requests do not count against rate limits.', 'magic-post-thumbnail'); ?>
        </div>
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php _e('Attribution Required', 'magic-post-thumbnail'); ?>:</strong> 
            <?php _e('Unsplash license requires crediting photographers. Use format: "Photo by [Photographer Name] on Unsplash" with links to photographer profile and Unsplash.', 'magic-post-thumbnail'); ?>
        </div>
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php _e('Download Tracking', 'magic-post-thumbnail'); ?>:</strong> 
            <?php _e('Per Unsplash API Guidelines, you MUST trigger the download endpoint when using an image. This helps track photo usage and compensate photographers.', 'magic-post-thumbnail'); ?>
        </div>
    </td>
</tr>

<tr>
    <td><?php _e('Access Key', 'magic-post-thumbnail'); ?></td>
    <td id="password-unsplash" class="password">
        <input type="password" class="form-control" name="ASI_plugin_banks_settings[unsplash][apikey]" value="<?php echo esc_attr($apikey); ?>" />
        <i id="togglePassword" class="fa fa-eye-slash" aria-hidden="true"></i>
    </td>
</tr>

<tr>
    <td></td>
    <td>
        <button class="btn btn-primary" id="btnUnsplash" onclick="return false;">
            <?php _e('Test Unsplash Connection', 'magic-post-thumbnail'); ?>
        </button>
        <span id="resultUnsplash">
            <img src="<?php echo plugin_dir_url(__FILE__); ?>../../../img/loader-mpt.gif" width="32" class="hidden" />
        </span>
    </td>
</tr>

<tr>
    <td><?php _e('Orientation Filter', 'magic-post-thumbnail'); ?></td>
    <td>
        <select name="ASI_plugin_banks_settings[unsplash][orientation]">
            <?php
            $orientations = array(
                '' => __('All Orientations', 'magic-post-thumbnail'),
                'landscape' => __('Landscape', 'magic-post-thumbnail'),
                'portrait' => __('Portrait', 'magic-post-thumbnail'),
                'squarish' => __('Square', 'magic-post-thumbnail')
            );
            foreach ($orientations as $value => $label) {
                $selected = ($orientation === $value) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php _e('Filter search results by photo orientation.', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>

<tr>
    <td><?php _e('Content Safety Filter', 'magic-post-thumbnail'); ?></td>
    <td>
        <select name="ASI_plugin_banks_settings[unsplash][content_filter]">
            <?php
            $filters = array(
                'low' => __('Low (Default - No NSFW content)', 'magic-post-thumbnail'),
                'high' => __('High (Family-friendly, suitable for younger audiences)', 'magic-post-thumbnail')
            );
            foreach ($filters as $value => $label) {
                $selected = ($content_filter == $value) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php _e('Removes content violating submission guidelines. High setting further filters potentially unsuitable content.', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>

<tr>
    <td><?php _e('Color Filter', 'magic-post-thumbnail'); ?></td>
    <td>
        <select name="ASI_plugin_banks_settings[unsplash][color]">
            <?php
            $colors = array(
                '' => __('All Colors', 'magic-post-thumbnail'),
                'black_and_white' => __('Black & White', 'magic-post-thumbnail'),
                'black' => __('Black', 'magic-post-thumbnail'),
                'white' => __('White', 'magic-post-thumbnail'),
                'yellow' => __('Yellow', 'magic-post-thumbnail'),
                'orange' => __('Orange', 'magic-post-thumbnail'),
                'red' => __('Red', 'magic-post-thumbnail'),
                'purple' => __('Purple', 'magic-post-thumbnail'),
                'magenta' => __('Magenta', 'magic-post-thumbnail'),
                'green' => __('Green', 'magic-post-thumbnail'),
                'teal' => __('Teal', 'magic-post-thumbnail'),
                'blue' => __('Blue', 'magic-post-thumbnail')
            );
            foreach ($colors as $value => $label) {
                $selected = ($color === $value) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php _e('Filter results by dominant color in the photo.', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>

<tr>
    <td><?php _e('Results Per Search', 'magic-post-thumbnail'); ?></td>
    <td>
        <select name="ASI_plugin_banks_settings[unsplash][per_page]">
            <?php
            $per_page_options = array(
                10 => '10',
                15 => '15',
                20 => '20',
                30 => '30 (Max)'
            );
            foreach ($per_page_options as $value => $label) {
                $selected = ($per_page == $value) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php _e('Number of results to retrieve per search query (max 30 per Unsplash API).', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>

<tr>
    <td><?php _e('Preferred Image Size', 'magic-post-thumbnail'); ?></td>
    <td>
        <select name="ASI_plugin_banks_settings[unsplash][preferred_size]">
            <?php
            $sizes = array(
                'raw' => __('Raw (Original unprocessed)', 'magic-post-thumbnail'),
                'full' => __('Full (Max quality JPEG, jpg&q=80)', 'magic-post-thumbnail'),
                'regular' => __('Regular (1080px wide, default)', 'magic-post-thumbnail'),
                'small' => __('Small (400px wide)', 'magic-post-thumbnail'),
                'thumb' => __('Thumbnail (200px wide)', 'magic-post-thumbnail')
            );
            foreach ($sizes as $value => $label) {
                $selected = ($preferred_size == $value) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php _e('Which image size variant to download from Unsplash. All URLs support dynamic resizing.', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>

<tr>
    <td><?php _e('Auto-add Attribution', 'magic-post-thumbnail'); ?></td>
    <td>
        <label class="checkbox">
            <input type="checkbox" name="ASI_plugin_banks_settings[unsplash][add_attribution]" value="yes" <?php checked($add_attribution, 'yes'); ?> />
            <span></span>
            <?php _e('Automatically add photographer credit to image caption', 'magic-post-thumbnail'); ?>
        </label>
        <p class="description"><?php _e('Format: "Photo by [Photographer Name] on Unsplash" - Required by Unsplash License.', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>

<tr>
    <td><?php _e('Track Downloads', 'magic-post-thumbnail'); ?></td>
    <td>
        <label class="checkbox">
            <input type="checkbox" name="ASI_plugin_banks_settings[unsplash][track_downloads]" value="yes" <?php checked($track_downloads, 'yes'); ?> />
            <span></span>
            <?php _e('Trigger download tracking endpoint (REQUIRED by Unsplash TOS)', 'magic-post-thumbnail'); ?>
        </label>
        <p class="description"><?php _e('When enabled, calls the download endpoint to increment photographer stats. Required per API guidelines.', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>
