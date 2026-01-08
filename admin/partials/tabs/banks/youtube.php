<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * YouTube Data API v3 Configuration
 */

$allsi_options           = get_option( 'ALLSI_plugin_banks_settings' );
$allsi_apikey            = isset( $allsi_options['youtube']['apikey'] ) ? $allsi_options['youtube']['apikey'] : '';
$allsi_thumbnail_quality = isset( $allsi_options['youtube']['thumbnail_quality'] ) ? $allsi_options['youtube']['thumbnail_quality'] : 'high';
$allsi_search_order      = isset( $allsi_options['youtube']['search_order'] ) ? $allsi_options['youtube']['search_order'] : 'relevance';
$allsi_video_duration    = isset( $allsi_options['youtube']['video_duration'] ) ? $allsi_options['youtube']['video_duration'] : 'any';
$allsi_safe_search       = isset( $allsi_options['youtube']['safe_search'] ) ? $allsi_options['youtube']['safe_search'] : 'moderate';
$allsi_max_results       = isset( $allsi_options['youtube']['max_results'] ) ? $allsi_options['youtube']['max_results'] : 5;
$allsi_region_code       = isset( $allsi_options['youtube']['region_code'] ) ? $allsi_options['youtube']['region_code'] : '';
?>

<tr>
    <td colspan="2" class="source-logo">
        <img src="<?php echo esc_url( plugin_dir_url(__FILE__) . 'img/youtube.png' ); ?>" alt="YouTube" />
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php esc_html_e('API Key Required', 'all-sources-images'); ?>:</strong> 
            <?php esc_html_e('Create a project in', 'all-sources-images'); ?> 
            <a href="https://console.developers.google.com/" target="_blank">Google Cloud Console</a>, 
            <?php esc_html_e('enable YouTube Data API v3, and create an API key under Credentials.', 'all-sources-images'); ?>
        </div>
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php esc_html_e('Quota Limits', 'all-sources-images'); ?>:</strong> 
            <?php esc_html_e('Default: 10,000 units/day. Each video search costs 100 units (100 searches/day max). Thumbnails retrieved from search results do not consume additional quota.', 'all-sources-images'); ?>
        </div>
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php esc_html_e('Thumbnail Quality', 'all-sources-images'); ?>:</strong> 
            <?php esc_html_e('YouTube provides thumbnails at different resolutions: default (120x90), medium (320x180), high (480x360), standard (640x480), maxresdefault (1280x720 - not always available).', 'all-sources-images'); ?>
        </div>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('API Key', 'all-sources-images'); ?></td>
    <td id="password-youtube" class="password">
        <input type="password" class="form-control" name="ALLSI_plugin_banks_settings[youtube][apikey]" value="<?php echo esc_attr( $allsi_apikey ); ?>" />
        <i id="togglePassword" class="fa fa-eye-slash" aria-hidden="true"></i>
    </td>
</tr>

<tr>
    <td></td>
    <td>
        <button type="button" class="btn btn-primary" id="btnYouTube">
            <?php esc_html_e('Test YouTube Connection', 'all-sources-images'); ?>
        </button>
        <span id="resultYoutube">
            <img src="<?php echo esc_url( plugin_dir_url(__FILE__) . '../../../img/loader-mpt.gif' ); ?>" width="32" class="hidden" />
        </span>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Thumbnail Quality', 'all-sources-images'); ?></td>
    <td>
        <select name="ALLSI_plugin_banks_settings[youtube][thumbnail_quality]">
            <?php
            $allsi_qualities = array(
                'default'      => __( 'Default (120x90px)', 'all-sources-images' ),
                'medium'       => __( 'Medium (320x180px)', 'all-sources-images' ),
                'high'         => __( 'High (480x360px - Recommended)', 'all-sources-images' ),
                'standard'     => __( 'Standard (640x480px)', 'all-sources-images' ),
                'maxresdefault' => __( 'Max Resolution (1280x720px - may not exist)', 'all-sources-images' ),
            );
            foreach ( $allsi_qualities as $allsi_value => $allsi_label ) {
                echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_thumbnail_quality, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e('YouTube thumbnail resolution to download. maxresdefault may not be available for all videos (fallback to lower quality).', 'all-sources-images'); ?></p>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Search Order', 'all-sources-images'); ?></td>
    <td>
        <select name="ALLSI_plugin_banks_settings[youtube][search_order]">
            <?php
            $allsi_orders = array(
                'relevance' => __( 'Relevance (Default)', 'all-sources-images' ),
                'date'      => __( 'Upload Date (Newest first)', 'all-sources-images' ),
                'rating'    => __( 'Rating (Highest first)', 'all-sources-images' ),
                'viewCount' => __( 'View Count (Most viewed)', 'all-sources-images' ),
                'title'     => __( 'Title (Alphabetical)', 'all-sources-images' ),
            );
            foreach ( $allsi_orders as $allsi_value => $allsi_label ) {
                echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_search_order, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e('How to sort YouTube search results.', 'all-sources-images'); ?></p>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Video Duration Filter', 'all-sources-images'); ?></td>
    <td>
        <select name="ALLSI_plugin_banks_settings[youtube][video_duration]">
            <?php
            $allsi_durations = array(
                'any'    => __( 'Any Duration', 'all-sources-images' ),
                'short'  => __( 'Short (< 4 minutes)', 'all-sources-images' ),
                'medium' => __( 'Medium (4-20 minutes)', 'all-sources-images' ),
                'long'   => __( 'Long (> 20 minutes)', 'all-sources-images' ),
            );
            foreach ( $allsi_durations as $allsi_value => $allsi_label ) {
                echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_video_duration, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e('Filter videos by length.', 'all-sources-images'); ?></p>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Safe Search', 'all-sources-images'); ?></td>
    <td>
        <select name="ALLSI_plugin_banks_settings[youtube][safe_search]">
            <?php
            $allsi_safe_search_options = array(
                'none'     => __( 'None (No filtering)', 'all-sources-images' ),
                'moderate' => __( 'Moderate (Filter restricted content - Default)', 'all-sources-images' ),
                'strict'   => __( 'Strict (Filter all sensitive content)', 'all-sources-images' ),
            );
            foreach ( $allsi_safe_search_options as $allsi_value => $allsi_label ) {
                echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_safe_search, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e('YouTube Safe Search filter level.', 'all-sources-images'); ?></p>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Max Results', 'all-sources-images'); ?></td>
    <td>
        <select name="ALLSI_plugin_banks_settings[youtube][max_results]">
            <?php
            $allsi_max_results_options = array(
                5  => '5',
                10 => '10',
                15 => '15',
                20 => '20',
                25 => '25',
                50 => '50 (Max per request)',
            );
            foreach ( $allsi_max_results_options as $allsi_value => $allsi_label ) {
                echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_max_results, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e('Number of video results to retrieve. Higher values consume more quota (each search = 100 units regardless of results).', 'all-sources-images'); ?></p>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Region Code', 'all-sources-images'); ?></td>
    <td>
        <input type="text" class="regular-text" name="ALLSI_plugin_banks_settings[youtube][region_code]" value="<?php echo esc_attr( $allsi_region_code ); ?>" maxlength="2" placeholder="US" />
        <p class="description"><?php esc_html_e('Optional 2-letter ISO 3166-1 country code (e.g., US, GB, ES, FR, DE). Filters videos viewable in specified region.', 'all-sources-images'); ?></p>
    </td>
</tr>
