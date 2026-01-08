<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * New Automatic Page - Main container with tabs
 * Uses same layout structure as new-settings.php
 *
 * @since 6.2.0
 */

// Determine active tab - Default is now 'placement' since Sources tab is removed
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab parameter is used for display purposes only, no data modification
$allsi_active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'placement';

// Set title based on active tab
switch ( $allsi_active_tab ) {
    case 'postprocessing':
        $allsi_title = esc_html__( 'Bulk Settings [Post-Processing]', 'all-sources-images' );
        break;
    case 'placement':
    default:
        $allsi_title = esc_html__( 'Bulk Settings [Image Placement]', 'all-sources-images' );
        break;
}

// Include header (opens card structure) - simplified without sidebar
include_once plugin_dir_path( __FILE__ ) . 'header-automatic.php';
?>

<div class="card-body">
    <?php
    switch ( $allsi_active_tab ) {
        case 'postprocessing':
            include_once 'tabs/new-automatic-post-processing.php';
            break;
        case 'placement':
        default:
            include_once 'tabs/new-automatic-image-placement.php';
            break;
    }
    ?>
</div>

<?php
// Include footer (closes card structure) - simplified
include_once plugin_dir_path( __FILE__ ) . 'footer-simple.php';
?>
