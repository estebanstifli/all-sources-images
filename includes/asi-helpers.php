<?php
/**
 * Helper functions for All Sources Images plugin
 *
 * @link       https://github.com/yourusername/all-sources-images
 * @since      1.0.0
 *
 * @package    All_Sources_Images
 * @subpackage All_Sources_Images/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Debug logging helper
 * 
 * Logs messages to WordPress debug.log when ASI_DEBUG is enabled
 * 
 * @since 1.0.0
 * @param mixed $message Message to log (string, array, object)
 * @param string $context Optional context/prefix for the log entry
 */
function ASI_log( $message, $context = '' ) {
	// Check if debugging is enabled
	if ( ! defined( 'ASI_DEBUG' ) || ! ASI_DEBUG ) {
		return;
	}
	
	// Build log prefix
	$prefix = '[All Sources Images]';
	if ( ! empty( $context ) ) {
		$prefix .= ' [' . $context . ']';
	}
	
	// Format message
	if ( is_array( $message ) || is_object( $message ) ) {
		$formatted_message = $prefix . ' ' . print_r( $message, true );
	} else {
		$formatted_message = $prefix . ' ' . $message;
	}
	
	// Log to WordPress debug.log
	error_log( $formatted_message );
}

/**
 * Log function entry point (for debugging execution flow)
 * 
 * @since 1.0.0
 * @param string $function_name Name of the function being entered
 * @param array $args Optional function arguments to log
 */
function ASI_log_entry( $function_name, $args = array() ) {
	if ( ! defined( 'ASI_DEBUG' ) || ! ASI_DEBUG ) {
		return;
	}
	
	$message = "→ Entering: {$function_name}";
	if ( ! empty( $args ) ) {
		$message .= ' | Args: ' . print_r( $args, true );
	}
	
	ASI_log( $message, 'TRACE' );
}

/**
 * Log errors with stack trace
 * 
 * @since 1.0.0
 * @param string $message Error message
 * @param Exception|null $exception Optional exception object
 */
function ASI_log_error( $message, $exception = null ) {
	if ( ! defined( 'ASI_DEBUG' ) || ! ASI_DEBUG ) {
		return;
	}
	
	$error_message = $message;
	
	if ( $exception instanceof Exception ) {
		$error_message .= "\n" . $exception->getMessage();
		$error_message .= "\n" . $exception->getTraceAsString();
	}
	
	ASI_log( $error_message, 'ERROR' );
}

if ( ! function_exists( 'ASI_get_logs_dir' ) ) {
	/**
	 * Retrieve the absolute path to the plugin's logs directory within uploads.
	 *
	 * @since 6.1.7
	 * @return string Absolute path ending with trailing slash.
	 */
	function ASI_get_logs_dir() {
		$upload_dir = wp_upload_dir();
		$base_dir   = isset( $upload_dir['basedir'] ) && ! empty( $upload_dir['basedir'] )
			? $upload_dir['basedir']
			: WP_CONTENT_DIR . '/uploads';
		return trailingslashit( wp_normalize_path( $base_dir ) ) . 'all-sources-images/logs/';
	}
}

if ( ! function_exists( 'ASI_ensure_logs_dir' ) ) {
	/**
	 * Ensure the logs directory exists and is writable.
	 *
	 * @since 6.1.7
	 * @return string|false Absolute path when available; false on failure.
	 */
	function ASI_ensure_logs_dir() {
		$dir = ASI_get_logs_dir();
		if ( ! file_exists( $dir ) ) {
			if ( ! wp_mkdir_p( $dir ) ) {
				ASI_log( 'Unable to create logs directory: ' . $dir, 'LOGS_DIR' );
				return false;
			}
		}
		return $dir;
	}
}
