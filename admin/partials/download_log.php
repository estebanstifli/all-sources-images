<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Verify nonce
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified on line 10 below
$allsi_action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
if ( 'downloadlog' === $allsi_action ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is where nonce is verified
    $allsi_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
    if ( empty( $allsi_nonce ) || ! wp_verify_nonce( $allsi_nonce, 'download_log' ) ) { 
        return false;
    }
} else {
    return false;
}

// Check the file
$allsi_dir = ALLSI_ensure_logs_dir();
if ( false === $allsi_dir ) {
    return false;
}
$allsi_files  = @scandir( $allsi_dir );
$allsi_result = '';

if ( ! empty( $allsi_files ) ) {
    foreach ( $allsi_files as $allsi_key => $allsi_value ) {
        if ( ! in_array( $allsi_value, array( '.', '..' ), true ) ) {
            if ( ! is_dir( $allsi_value ) && strstr( $allsi_value, '.log' ) ) {
                $allsi_result   = $allsi_value;
                $allsi_filename = $allsi_dir . $allsi_result;
            }
        }
    }
}


// Exit if no log file
if ( empty( $allsi_result ) ) {
    return false;
}
if ( ! is_file( $allsi_filename ) || ! is_readable( $allsi_filename ) ) {
    header( 'HTTP/1.1 404 Not Found' );
    exit;
}
$allsi_size = filesize( $allsi_filename );

// Disable cache
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0,pre-check=0");
header("Cache-Control: max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
 
// Force download with filename
header("Content-Type: application/force-download");
header( 'Content-Disposition: attachment; filename="' . esc_attr( $allsi_result ) . '"' );
 
// File size
header( 'Content-Length: ' . absint( $allsi_size ) );
 
// Send the file using WP_Filesystem
global $wp_filesystem;
if ( empty( $wp_filesystem ) ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
}
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $wp_filesystem->get_contents( $allsi_filename );

?>