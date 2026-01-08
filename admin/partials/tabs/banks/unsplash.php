<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Unsplash API Configuration
 */

$allsi_options         = get_option( 'ALLSI_plugin_banks_settings' );
$allsi_apikey          = isset( $allsi_options['unsplash']['apikey'] ) ? $allsi_options['unsplash']['apikey'] : '';
$allsi_orientation     = isset( $allsi_options['unsplash']['orientation'] ) ? $allsi_options['unsplash']['orientation'] : 'all';
$allsi_content_filter  = isset( $allsi_options['unsplash']['content_filter'] ) ? $allsi_options['unsplash']['content_filter'] : 'low';
$allsi_color           = isset( $allsi_options['unsplash']['color'] ) ? $allsi_options['unsplash']['color'] : 'all';
$allsi_per_page        = isset( $allsi_options['unsplash']['per_page'] ) ? $allsi_options['unsplash']['per_page'] : 10;
$allsi_preferred_size  = isset( $allsi_options['unsplash']['preferred_size'] ) ? $allsi_options['unsplash']['preferred_size'] : 'regular';
$allsi_add_attribution = isset( $allsi_options['unsplash']['add_attribution'] ) ? $allsi_options['unsplash']['add_attribution'] : 'yes';
$allsi_track_downloads = isset( $allsi_options['unsplash']['track_downloads'] ) ? $allsi_options['unsplash']['track_downloads'] : 'yes';
?>

<tr>
    <td colspan="2" class="source-logo">
        <img src="<?php echo esc_url( plugin_dir_url(__FILE__) . 'img/unsplash.png' ); ?>" alt="Unsplash" />
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php esc_html_e('API Access Key Required', 'all-sources-images'); ?>:</strong> 
            <?php esc_html_e('Register your application at', 'all-sources-images'); ?> 
            <a href="https://unsplash.com/oauth/applications" target="_blank">unsplash.com/oauth/applications</a> 
            <?php esc_html_e('to get your Access Key (instant approval).', 'all-sources-images'); ?>
        </div>
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php esc_html_e('Rate Limits', 'all-sources-images'); ?>:</strong> 
            <?php esc_html_e('Demo mode: 50 requests/hour. Production mode (after approval): 5,000 requests/hour. Image requests do not count against rate limits.', 'all-sources-images'); ?>
        </div>
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php esc_html_e('Attribution Required', 'all-sources-images'); ?>:</strong> 
            <?php esc_html_e('Unsplash license requires crediting photographers. Use format: "Photo by [Photographer Name] on Unsplash" with links to photographer profile and Unsplash.', 'all-sources-images'); ?>
        </div>
    </td>
</tr>

<tr>
    <td colspan="2">
        <div class="update-nag">
            <strong><?php esc_html_e('Download Tracking', 'all-sources-images'); ?>:</strong> 
            <?php esc_html_e('Per Unsplash API Guidelines, you MUST trigger the download endpoint when using an image. This helps track photo usage and compensate photographers.', 'all-sources-images'); ?>
        </div>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Access Key', 'all-sources-images'); ?></td>
    <td id="password-unsplash" class="password">
        <input type="password" class="form-control" name="ALLSI_plugin_banks_settings[unsplash][apikey]" value="<?php echo esc_attr( $allsi_apikey ); ?>" />
        <i id="togglePassword" class="fa fa-eye-slash" aria-hidden="true"></i>
    </td>
</tr>

<tr>
    <td></td>
    <td>
        <button type="button" class="btn btn-primary" id="btnUnsplash">
            <?php esc_html_e('Test Unsplash Connection', 'all-sources-images'); ?>
        </button>
        <span id="resultUnsplash">
            <img src="<?php echo esc_url( plugin_dir_url(__FILE__) . '../../../img/loader-mpt.gif' ); ?>" width="32" class="hidden" />
        </span>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Orientation Filter', 'all-sources-images'); ?></td>
    <td>
        <select name="ALLSI_plugin_banks_settings[unsplash][orientation]">
            <?php
            $allsi_orientations = array(
                ''          => __( 'All Orientations', 'all-sources-images' ),
                'landscape' => __( 'Landscape', 'all-sources-images' ),
                'portrait'  => __( 'Portrait', 'all-sources-images' ),
                'squarish'  => __( 'Square', 'all-sources-images' ),
            );
            foreach ( $allsi_orientations as $allsi_value => $allsi_label ) {
                echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_orientation, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e('Filter search results by photo orientation.', 'all-sources-images'); ?></p>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Content Safety Filter', 'all-sources-images'); ?></td>
    <td>
        <select name="ALLSI_plugin_banks_settings[unsplash][content_filter]">
            <?php
            $allsi_filters = array(
                'low'  => __( 'Low (Default - No NSFW content)', 'all-sources-images' ),
                'high' => __( 'High (Family-friendly, suitable for younger audiences)', 'all-sources-images' ),
            );
            foreach ( $allsi_filters as $allsi_value => $allsi_label ) {
                echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_content_filter, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e('Removes content violating submission guidelines. High setting further filters potentially unsuitable content.', 'all-sources-images'); ?></p>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Color Filter', 'all-sources-images'); ?></td>
    <td>
        <select name="ALLSI_plugin_banks_settings[unsplash][color]">
            <?php
            $allsi_colors = array(
                ''               => __( 'All Colors', 'all-sources-images' ),
                'black_and_white' => __( 'Black & White', 'all-sources-images' ),
                'black'          => __( 'Black', 'all-sources-images' ),
                'white'          => __( 'White', 'all-sources-images' ),
                'yellow'         => __( 'Yellow', 'all-sources-images' ),
                'orange'         => __( 'Orange', 'all-sources-images' ),
                'red'            => __( 'Red', 'all-sources-images' ),
                'purple'         => __( 'Purple', 'all-sources-images' ),
                'magenta'        => __( 'Magenta', 'all-sources-images' ),
                'green'          => __( 'Green', 'all-sources-images' ),
                'teal'           => __( 'Teal', 'all-sources-images' ),
                'blue'           => __( 'Blue', 'all-sources-images' ),
            );
            foreach ( $allsi_colors as $allsi_value => $allsi_label ) {
                echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_color, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e('Filter results by dominant color in the photo.', 'all-sources-images'); ?></p>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Results Per Search', 'all-sources-images'); ?></td>
    <td>
        <select name="ALLSI_plugin_banks_settings[unsplash][per_page]">
            <?php
            $allsi_per_page_options = array(
                10 => '10',
                15 => '15',
                20 => '20',
                30 => '30 (Max)',
            );
            foreach ( $allsi_per_page_options as $allsi_value => $allsi_label ) {
                echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_per_page, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e('Number of results to retrieve per search query (max 30 per Unsplash API).', 'all-sources-images'); ?></p>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Preferred Image Size', 'all-sources-images'); ?></td>
    <td>
        <select name="ALLSI_plugin_banks_settings[unsplash][preferred_size]">
            <?php
            $allsi_sizes = array(
                'raw'     => __( 'Raw (Original unprocessed)', 'all-sources-images' ),
                'full'    => __( 'Full (Max quality JPEG, jpg&q=80)', 'all-sources-images' ),
                'regular' => __( 'Regular (1080px wide, default)', 'all-sources-images' ),
                'small'   => __( 'Small (400px wide)', 'all-sources-images' ),
                'thumb'   => __( 'Thumbnail (200px wide)', 'all-sources-images' ),
            );
            foreach ( $allsi_sizes as $allsi_value => $allsi_label ) {
                echo '<option value="' . esc_attr( $allsi_value ) . '" ' . selected( $allsi_preferred_size, $allsi_value, false ) . '>' . esc_html( $allsi_label ) . '</option>';
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e('Which image size variant to download from Unsplash. All URLs support dynamic resizing.', 'all-sources-images'); ?></p>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Auto-add Attribution', 'all-sources-images'); ?></td>
    <td>
        <label class="checkbox">
            <input type="checkbox" name="ALLSI_plugin_banks_settings[unsplash][add_attribution]" value="yes" <?php checked( $allsi_add_attribution, 'yes' ); ?> />
            <span></span>
            <?php esc_html_e('Automatically add photographer credit to image caption', 'all-sources-images'); ?>
        </label>
        <p class="description"><?php esc_html_e('Format: "Photo by [Photographer Name] on Unsplash" - Required by Unsplash License.', 'all-sources-images'); ?></p>
    </td>
</tr>

<tr>
    <td><?php esc_html_e('Track Downloads', 'all-sources-images'); ?></td>
    <td>
        <label class="checkbox">
            <input type="checkbox" name="ALLSI_plugin_banks_settings[unsplash][track_downloads]" value="yes" <?php checked( $allsi_track_downloads, 'yes' ); ?> />
            <span></span>
            <?php esc_html_e('Trigger download tracking endpoint (REQUIRED by Unsplash TOS)', 'all-sources-images'); ?>
        </label>
        <p class="description"><?php esc_html_e('When enabled, calls the download endpoint to increment photographer stats. Required per API guidelines.', 'all-sources-images'); ?></p>
    </td>
</tr>
