<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Verify nonce
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified on line 10 below
$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
if ( 'deletelog' === $action ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is where nonce is verified
    $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
    if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'delete_log' ) ) {
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

// Delete log file
wp_delete_file( $filename );

// Redirect without delete arguments
wp_safe_redirect( remove_query_arg( array( 'action', '_wpnonce' ) ) );
exit;

?>
