<?php
/**
 * YouTube Data API v3 Configuration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$options = get_option('ASI_plugin_banks_settings');
$apikey = isset($options['youtube']['apikey']) ? $options['youtube']['apikey'] : '';
$thumbnail_quality = isset($options['youtube']['thumbnail_quality']) ? $options['youtube']['thumbnail_quality'] : 'high';
$search_order = isset($options['youtube']['search_order']) ? $options['youtube']['search_order'] : 'relevance';
$video_duration = isset($options['youtube']['video_duration']) ? $options['youtube']['video_duration'] : 'any';
$safe_search = isset($options['youtube']['safe_search']) ? $options['youtube']['safe_search'] : 'moderate';
$max_results = isset($options['youtube']['max_results']) ? $options['youtube']['max_results'] : 5;
$region_code = isset($options['youtube']['region_code']) ? $options['youtube']['region_code'] : '';
?>

<tr>
    <td colspan="2" class="source-logo">
        <img src="<?php echo plugin_dir_url(__FILE__) . 'img/youtube.png'; ?>" alt="YouTube" />
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php _e('API Key Required', 'magic-post-thumbnail'); ?>:</strong> 
            <?php _e('Create a project in', 'magic-post-thumbnail'); ?> 
            <a href="https://console.developers.google.com/" target="_blank">Google Cloud Console</a>, 
            <?php _e('enable YouTube Data API v3, and create an API key under Credentials.', 'magic-post-thumbnail'); ?>
        </div>
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php _e('Quota Limits', 'magic-post-thumbnail'); ?>:</strong> 
            <?php _e('Default: 10,000 units/day. Each video search costs 100 units (100 searches/day max). Thumbnails retrieved from search results do not consume additional quota.', 'magic-post-thumbnail'); ?>
        </div>
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php _e('Thumbnail Quality', 'magic-post-thumbnail'); ?>:</strong> 
            <?php _e('YouTube provides thumbnails at different resolutions: default (120x90), medium (320x180), high (480x360), standard (640x480), maxresdefault (1280x720 - not always available).', 'magic-post-thumbnail'); ?>
        </div>
    </td>
</tr>

<tr>
    <td><?php _e('API Key', 'magic-post-thumbnail'); ?></td>
    <td id="password-youtube" class="password">
        <input type="password" class="regular-text password" name="ASI_plugin_banks_settings[youtube][apikey]" value="<?php echo esc_attr($apikey); ?>" />
        <i id="togglePassword" class="fa fa-eye-slash" aria-hidden="true"></i>
    </td>
</tr>

<tr>
    <td></td>
    <td>
        <button class="btn btn-primary" id="btnYouTube" onclick="return false;">
            <?php _e('Test YouTube Connection', 'magic-post-thumbnail'); ?>
            <span class="loadersmall"></span>
        </button>
    </td>
</tr>

<tr>
    <td><?php _e('Thumbnail Quality', 'magic-post-thumbnail'); ?></td>
    <td>
        <select name="ASI_plugin_banks_settings[youtube][thumbnail_quality]">
            <?php
            $qualities = array(
                'default' => __('Default (120x90px)', 'magic-post-thumbnail'),
                'medium' => __('Medium (320x180px)', 'magic-post-thumbnail'),
                'high' => __('High (480x360px - Recommended)', 'magic-post-thumbnail'),
                'standard' => __('Standard (640x480px)', 'magic-post-thumbnail'),
                'maxresdefault' => __('Max Resolution (1280x720px - may not exist)', 'magic-post-thumbnail')
            );
            foreach ($qualities as $value => $label) {
                $selected = ($thumbnail_quality == $value) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php _e('YouTube thumbnail resolution to download. maxresdefault may not be available for all videos (fallback to lower quality).', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>

<tr>
    <td><?php _e('Search Order', 'magic-post-thumbnail'); ?></td>
    <td>
        <select name="ASI_plugin_banks_settings[youtube][search_order]">
            <?php
            $orders = array(
                'relevance' => __('Relevance (Default)', 'magic-post-thumbnail'),
                'date' => __('Upload Date (Newest first)', 'magic-post-thumbnail'),
                'rating' => __('Rating (Highest first)', 'magic-post-thumbnail'),
                'viewCount' => __('View Count (Most viewed)', 'magic-post-thumbnail'),
                'title' => __('Title (Alphabetical)', 'magic-post-thumbnail')
            );
            foreach ($orders as $value => $label) {
                $selected = ($search_order == $value) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php _e('How to sort YouTube search results.', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>

<tr>
    <td><?php _e('Video Duration Filter', 'magic-post-thumbnail'); ?></td>
    <td>
        <select name="ASI_plugin_banks_settings[youtube][video_duration]">
            <?php
            $durations = array(
                'any' => __('Any Duration', 'magic-post-thumbnail'),
                'short' => __('Short (< 4 minutes)', 'magic-post-thumbnail'),
                'medium' => __('Medium (4-20 minutes)', 'magic-post-thumbnail'),
                'long' => __('Long (> 20 minutes)', 'magic-post-thumbnail')
            );
            foreach ($durations as $value => $label) {
                $selected = ($video_duration == $value) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php _e('Filter videos by length.', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>

<tr>
    <td><?php _e('Safe Search', 'magic-post-thumbnail'); ?></td>
    <td>
        <select name="ASI_plugin_banks_settings[youtube][safe_search]">
            <?php
            $safe_search_options = array(
                'none' => __('None (No filtering)', 'magic-post-thumbnail'),
                'moderate' => __('Moderate (Filter restricted content - Default)', 'magic-post-thumbnail'),
                'strict' => __('Strict (Filter all sensitive content)', 'magic-post-thumbnail')
            );
            foreach ($safe_search_options as $value => $label) {
                $selected = ($safe_search == $value) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php _e('YouTube Safe Search filter level.', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>

<tr>
    <td><?php _e('Max Results', 'magic-post-thumbnail'); ?></td>
    <td>
        <select name="ASI_plugin_banks_settings[youtube][max_results]">
            <?php
            $max_results_options = array(
                5 => '5',
                10 => '10',
                15 => '15',
                20 => '20',
                25 => '25',
                50 => '50 (Max per request)'
            );
            foreach ($max_results_options as $value => $label) {
                $selected = ($max_results == $value) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php _e('Number of video results to retrieve. Higher values consume more quota (each search = 100 units regardless of results).', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>

<tr>
    <td><?php _e('Region Code', 'magic-post-thumbnail'); ?></td>
    <td>
        <input type="text" class="regular-text" name="ASI_plugin_banks_settings[youtube][region_code]" value="<?php echo esc_attr($region_code); ?>" maxlength="2" placeholder="US" />
        <p class="description"><?php _e('Optional 2-letter ISO 3166-1 country code (e.g., US, GB, ES, FR, DE). Filters videos viewable in specified region.', 'magic-post-thumbnail'); ?></p>
    </td>
</tr>
