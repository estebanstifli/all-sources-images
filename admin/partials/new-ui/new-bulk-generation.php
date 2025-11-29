<?php
/**
 * New Bulk Generation Page
 * 
 * Placeholder page for bulk generation and cron features
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

$banks_settings = get_option( 'ASI_plugin_banks_settings' );
$banks_settings = wp_parse_args( $banks_settings, $this->ASI_default_options_banks_settings() );
?>

<div class="wrap new-bulk-generation-wrap">
    <h1><?php esc_html_e( 'New Bulk Generation', 'all-sources-images' ); ?></h1>
    
    <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
        <div class="alert alert-custom alert-light-primary fade show mb-5" role="alert">
            <div class="alert-icon"><i class="flaticon-info"></i></div>
            <div class="alert-text">
                <h3><?php esc_html_e( 'Bulk Image Generation', 'all-sources-images' ); ?></h3>
                <p><?php esc_html_e( 'This page will allow you to generate images in bulk for multiple posts at once.', 'all-sources-images' ); ?></p>
            </div>
        </div>

        <h2><?php esc_html_e( 'Coming Soon', 'all-sources-images' ); ?></h2>
        
        <p><?php esc_html_e( 'This section is under development. The following features will be available:', 'all-sources-images' ); ?></p>
        
        <ul style="list-style: disc; margin-left: 20px;">
            <li><?php esc_html_e( 'Select post types for bulk generation', 'all-sources-images' ); ?></li>
            <li><?php esc_html_e( 'Filter by category, date range, or missing featured image', 'all-sources-images' ); ?></li>
            <li><?php esc_html_e( 'Preview posts before generation', 'all-sources-images' ); ?></li>
            <li><?php esc_html_e( 'Progress tracking with detailed logs', 'all-sources-images' ); ?></li>
            <li><?php esc_html_e( 'Scheduled/Cron-based generation (Premium)', 'all-sources-images' ); ?></li>
        </ul>

        <hr style="margin: 20px 0;" />

        <h3><?php esc_html_e( 'Current Configuration Summary', 'all-sources-images' ); ?></h3>
        
        <table class="widefat" style="max-width: 600px;">
            <tbody>
                <tr>
                    <td><strong><?php esc_html_e( 'Automatic Source:', 'all-sources-images' ); ?></strong></td>
                    <td><?php echo esc_html( $banks_settings['api_chosen_auto'] ?? 'Not set' ); ?></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e( 'Overwrite Images:', 'all-sources-images' ); ?></strong></td>
                    <td><?php echo ( !empty( $options['rewrite_featured'] ) && $options['rewrite_featured'] == 'true' ) ? esc_html__( 'Yes', 'all-sources-images' ) : esc_html__( 'No', 'all-sources-images' ); ?></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e( 'Image Blocks Configured:', 'all-sources-images' ); ?></strong></td>
                    <td><?php echo count( $options['image_block'] ?? array() ); ?></td>
                </tr>
            </tbody>
        </table>

        <hr style="margin: 20px 0;" />

        <p>
            <a href="<?php echo admin_url( 'edit.php' ); ?>" class="button button-primary">
                <?php esc_html_e( 'Go to Posts List', 'all-sources-images' ); ?>
            </a>
            <span style="margin: 0 10px;"><?php esc_html_e( 'Use the bulk actions dropdown to generate images for selected posts.', 'all-sources-images' ); ?></span>
        </p>
    </div>
</div>

<style>
.new-bulk-generation-wrap .card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}
.new-bulk-generation-wrap h2 {
    color: #1e1e1e;
    font-size: 1.3em;
    margin: 1em 0;
}
.new-bulk-generation-wrap h3 {
    color: #1e1e1e;
    font-size: 1.1em;
    margin: 1em 0;
}
.new-bulk-generation-wrap ul li {
    margin-bottom: 8px;
}
.new-bulk-generation-wrap .widefat td {
    padding: 8px 10px;
}
.new-bulk-generation-wrap .widefat tr:nth-child(odd) {
    background: #f9f9f9;
}
</style>
