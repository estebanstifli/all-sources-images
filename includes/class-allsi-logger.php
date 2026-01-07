<?php
/**
 * Simple logger class to replace Monolog
 *
 * Provides the same interface as Monolog (info, error, warning, debug methods)
 * but writes to a custom log file in the plugin's uploads directory.
 *
 * @package    All_Sources_Images
 * @subpackage All_Sources_Images/includes
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ASI Logger class
 *
 * Lightweight logging class that mimics Monolog's basic interface
 * for drop-in replacement without external dependencies.
 */
class ALLSI_Logger {

    /**
     * Log levels
     */
    const DEBUG   = 100;
    const INFO    = 200;
    const WARNING = 300;
    const ERROR   = 400;

    /**
     * Path to the log file
     *
     * @var string
     */
    private $log_file;

    /**
     * Whether logging is enabled
     *
     * @var bool
     */
    private $enabled;

    /**
     * Logger name/channel
     *
     * @var string
     */
    private $name;

    /**
     * Constructor
     *
     * @param string $name    Logger name/channel.
     * @param string $log_file Path to log file.
     * @param bool   $enabled  Whether logging is enabled.
     */
    public function __construct( $name = 'ALLSI_logger', $log_file = '', $enabled = true ) {
        $this->name    = $name;
        $this->enabled = $enabled;
        
        if ( empty( $log_file ) && $enabled ) {
            $logs_dir = $this->get_logs_dir();
            if ( $logs_dir ) {
                $this->log_file = $logs_dir . $this->generate_log_filename();
            }
        } else {
            $this->log_file = $log_file;
        }
    }

    /**
     * Get logs directory path
     *
     * @return string|false
     */
    private function get_logs_dir() {
        if ( function_exists( 'ALLSI_ensure_logs_dir' ) ) {
            return ALLSI_ensure_logs_dir();
        }
        
        $upload_dir = wp_upload_dir();
        $base_dir   = $upload_dir['basedir'];
        
        $dir = trailingslashit( wp_normalize_path( $base_dir ) ) . 'all-sources-images/logs/';
        
        if ( ! file_exists( $dir ) ) {
            if ( ! wp_mkdir_p( $dir ) ) {
                return false;
            }
        }
        
        return $dir;
    }

    /**
     * Generate log filename based on current date
     *
     * @return string
     */
    private function generate_log_filename() {
        return 'allsi-' . gmdate( 'Y-m-d' ) . '.log';
    }

    /**
     * Log a message at the specified level
     *
     * @param int    $level   Log level.
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    private function log( $level, $message, array $context = array() ) {
        if ( ! $this->enabled || empty( $this->log_file ) ) {
            return;
        }

        $level_name = $this->get_level_name( $level );
        $timestamp  = gmdate( 'Y-m-d H:i:s' );
        
        // Format context data
        $context_string = '';
        if ( ! empty( $context ) ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Logging function.
            $context_string = ' ' . wp_json_encode( $context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        }

        // Build log entry
        $log_entry = sprintf(
            "[%s] %s.%s: %s%s\n",
            $timestamp,
            $this->name,
            $level_name,
            $message,
            $context_string
        );

        // Write to log file
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Logging to custom file.
        file_put_contents( $this->log_file, $log_entry, FILE_APPEND | LOCK_EX );
    }

    /**
     * Get the name of a log level
     *
     * @param int $level Log level.
     * @return string
     */
    private function get_level_name( $level ) {
        $levels = array(
            self::DEBUG   => 'DEBUG',
            self::INFO    => 'INFO',
            self::WARNING => 'WARNING',
            self::ERROR   => 'ERROR',
        );

        return isset( $levels[ $level ] ) ? $levels[ $level ] : 'LOG';
    }

    /**
     * Log a debug message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function debug( $message, array $context = array() ) {
        $this->log( self::DEBUG, $message, $context );
    }

    /**
     * Log an info message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function info( $message, array $context = array() ) {
        $this->log( self::INFO, $message, $context );
    }

    /**
     * Log a warning message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function warning( $message, array $context = array() ) {
        $this->log( self::WARNING, $message, $context );
    }

    /**
     * Log an error message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function error( $message, array $context = array() ) {
        $this->log( self::ERROR, $message, $context );
    }
}

/**
 * Null logger class for when logging is disabled
 *
 * All methods are empty no-ops.
 */
class ALLSI_Nolog {

    /**
     * No-op debug method
     *
     * @param string $message Ignored.
     * @param array  $context Ignored.
     * @return void
     */
    public function debug( $message = '', array $context = array() ) {}

    /**
     * No-op info method
     *
     * @param string $message Ignored.
     * @param array  $context Ignored.
     * @return void
     */
    public function info( $message = '', array $context = array() ) {}

    /**
     * No-op warning method
     *
     * @param string $message Ignored.
     * @param array  $context Ignored.
     * @return void
     */
    public function warning( $message = '', array $context = array() ) {}

    /**
     * No-op error method
     *
     * @param string $message Ignored.
     * @param array  $context Ignored.
     * @return void
     */
    public function error( $message = '', array $context = array() ) {}
}
