<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * New Settings Page - Main container with tabs
 * Uses same layout structure as original admin pages
 *
 * @since 6.2.0
 */

// Determine active tab
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'source';

// Set title based on active tab
switch ( $active_tab ) {
    case 'proxy':
        $title = esc_html__( 'Settings [Proxy]', 'all-sources-images' );
        break;
    case 'others':
        $title = esc_html__( 'Settings [Others]', 'all-sources-images' );
        break;
    case 'source':
    default:
        $title = esc_html__( 'Settings [Source]', 'all-sources-images' );
        break;
}

// Include header (opens card structure) - simplified without sidebar
include_once plugin_dir_path( __FILE__ ) . 'header-simple.php';
?>

<div class="card-body">
    <?php
    switch ( $active_tab ) {
        case 'proxy':
            include_once 'tabs/new-settings-proxy.php';
            break;
        case 'others':
            include_once 'tabs/new-settings-others.php';
            break;
        case 'source':
        default:
            include_once 'tabs/new-settings-source.php';
            break;
    }
    ?>
</div>

<?php
// Include footer (closes card structure) - simplified
include_once plugin_dir_path( __FILE__ ) . 'footer-simple.php';
?>
