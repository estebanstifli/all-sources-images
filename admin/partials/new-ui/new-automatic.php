<?php
/**
 * New Automatic Page - Main container with tabs
 * Uses same layout structure as new-settings.php
 *
 * @since 6.2.0
 */

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

// Determine active tab
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'sources';

// Set title based on active tab
switch ( $active_tab ) {
    case 'plugins':
        $title = esc_html__( 'Automatic [Plugins]', 'all-sources-images' );
        break;
    case 'placement':
        $title = esc_html__( 'Automatic [Image Placement]', 'all-sources-images' );
        break;
    case 'postprocessing':
        $title = esc_html__( 'Automatic [Post-Processing]', 'all-sources-images' );
        break;
    case 'preprocessing':
        $title = esc_html__( 'Automatic [Pre-Processing]', 'all-sources-images' );
        break;
    case 'sources':
    default:
        $title = esc_html__( 'Automatic [Sources]', 'all-sources-images' );
        break;
}

// Include header (opens card structure) - simplified without sidebar
include_once plugin_dir_path( __FILE__ ) . 'header-automatic.php';
?>

<div class="card-body">
    <?php
    switch ( $active_tab ) {
        case 'plugins':
            include_once 'tabs/new-automatic-plugins.php';
            break;
        case 'placement':
            include_once 'tabs/new-automatic-image-placement.php';
            break;
        case 'postprocessing':
            include_once 'tabs/new-automatic-post-processing.php';
            break;
        case 'preprocessing':
            include_once 'tabs/new-automatic-pre-processing.php';
            break;
        case 'sources':
        default:
            include_once 'tabs/new-automatic-sources.php';
            break;
    }
    ?>
</div>

<?php
// Include footer (closes card structure) - simplified
include_once plugin_dir_path( __FILE__ ) . 'footer-simple.php';
?>
