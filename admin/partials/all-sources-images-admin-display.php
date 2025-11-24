<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      4.0.0
 *
 * @package    All_Sources_Images
 * @subpackage All_Sources_Images/admin/partials
 */

$include_template = true;

$module = ( isset( $_GET['module'] ) ) ? sanitize_text_field( $_GET['module'] ) : 'default';

switch ( $module ) {
    case 'posts':
        $title = esc_html__( 'Settings [Posts]', 'all-sources-images' );
        $tab   = 'posts.php';
        break;
    case 'block':
        $title = esc_html__( 'Settings [Block]', 'all-sources-images' );
        $tab   = 'block.php';
        break;
    case 'images':
        $title = esc_html__( 'Settings [Images]', 'all-sources-images' );
        $tab   = 'images.php';
        break;
    case 'automatic':
        $title = esc_html__( 'Settings [Automatic]', 'all-sources-images' );
        $tab   = 'automatic.php';
        break;
    case 'source':
        $title = esc_html__( 'Settings [Source]', 'all-sources-images' );
        $tab   = 'source.php';
        break;
    case 'interval':
        $title = esc_html__( 'Settings [Interval]', 'all-sources-images' );
        $tab   = 'interval.php';
        break;
    case 'cron':
        $title = esc_html__( 'Pro [Cron]', 'all-sources-images' );
        $tab   = 'cron.php';
        break;
    case 'proxy':
        $title = esc_html__( 'Settings [Proxy]', 'all-sources-images' );
        $tab   = 'proxy.php';
        break;
    case 'affiliation':
        $title = esc_html__( 'Miscellaneous [Affiliation]', 'all-sources-images' );
        $tab   = 'affiliation.php';
        break;
    case 'compatibility':
        $title = esc_html__( 'Miscellaneous [Compatibility]', 'all-sources-images' );
        $tab   = 'compatibility.php';
        break;
    case 'rights':
        $title = esc_html__( 'Miscellaneous [Rights]', 'all-sources-images' );
        $tab   = 'rights.php';
        break;
    case 'logs':
        $title = esc_html__( 'Miscellaneous [Logs]', 'all-sources-images' );
        $tab   = 'logs.php';
        break;
    case 'bulk-generation':
        $title = esc_html__( 'Bulk Generation', 'all-sources-images' );
        $tab   = 'generation_choice.php';
        break;
    case 'bulk-generation-interval':
        $title = esc_html__( 'Bulk Generation with interval', 'all-sources-images' );
        $tab   = 'generation_choice.php';
        break;
    case 'account':
        $title = esc_html__( 'Account', 'all-sources-images' );
        $tab   = 'account.php';
        //$include_template = false;
        break;
    case 'contact':
        $title = esc_html__( 'Contact', 'all-sources-images' );
        $tab   = 'contact.php';
        break;
    default:
        $title = esc_html__( 'Dashboard', 'all-sources-images' );
        $tab   = 'dashboard.php';
}


//Bug with account page, remove header & footer template
if( TRUE === $include_template ) {
    include_once( 'header.php' );
    include_once( 'tabs/' . $tab );
    include_once( 'footer.php' );
} else {
    include_once( 'tabs/' . $tab );
}

?>