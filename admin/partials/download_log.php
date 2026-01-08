<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Verify nonce
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified on line 10 below
$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
if ( 'downloadlog' === $action ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is where nonce is verified
    $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
    if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'download_log' ) ) { 
        return false;
    }
} else {
    return false;
}

// Check the file
$dir    = ALLSI_ensure_logs_dir();
if ( false === $dir ) {
    return false;
}
$files  = @scandir( $dir );
$result = '';

if ( ! empty( $files ) ) {
    foreach ( $files as $key => $value ) {
        if ( ! in_array( $value, array( '.', '..' ), true ) ) {
            if ( ! is_dir( $value ) && strstr( $value, '.log' ) ) {
                $result = $value;
                $filename = $dir . $result;
            }
        }
    }
}


// Exit if no log file
if( empty( $result ) ) {
    return false;
}



if ( !is_file($filename) || !is_readable( $filename ) ) {
    header("HTTP/1.1 404 Not Found");
    exit;
}
$size = filesize($filename);

// Disable cache
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0,pre-check=0");
header("Cache-Control: max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
 
// Force download with filename
header("Content-Type: application/force-download");
header('Content-Disposition: attachment; filename="'.$result.'"');
 
// File size
header("Content-Length: ".$size);
 
// Send the file using WP_Filesystem
global $wp_filesystem;
if ( empty( $wp_filesystem ) ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
}
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $wp_filesystem->get_contents( $filename );

?>