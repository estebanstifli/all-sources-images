<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class ALLSI_Image_Source {
    /**
     * Unique slug used to map bank selection.
     *
     * @return string
     */
    abstract public function get_slug();

    /**
     * Main entry point called by the source manager.
     * Must return an associative array containing url_results, file_media, alt_img, caption_img
     * or a WP_Error when something goes wrong.
     *
     * @param array $context
     *
     * @return array|WP_Error
     */
    abstract public function generate( array $context );

    /**
     * Let sources opt-out dynamically when essential credentials are missing.
     */
    public function is_available() {
        return true;
    }

    /**
     * Convenience helper for building a wp_remote_request-like response array when
     * the source already has the binary payload in memory.
     */
    protected function build_memory_response( $binary, $content_type = 'image/png' ) {
        return array(
            'response' => array(
                'code' => 200,
            ),
            'body'    => $binary,
            'headers' => array(
                'content-type' => $content_type,
            ),
        );
    }

    /**
     * Make HTTP request with proxy support and optional Cloudflare fallback
     * 
     * @param string $service Service name (pixabay, pexels, etc.)
     * @param string $url Target URL
     * @param array $request_args Request arguments
     * @param array $context Generation context
     * @param string $method HTTP method (GET, POST) - only used if not already set in $request_args
     * @param bool $use_cloudflare_fallback Whether to use Cloudflare Worker (when no API key)
     */
    protected function request_with_proxy( $service, $url, array $request_args, array $context, $method = 'GET', $use_cloudflare_fallback = false ) {
        // Only set method if not already specified in request_args
        if ( empty( $request_args['method'] ) ) {
            $request_args['method'] = strtoupper( $method );
        }

        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ALLSI_remote_request' ) ) {
            return $context['generation']->ALLSI_remote_request( $service, $url, $request_args, $use_cloudflare_fallback );
        }

        // Fallback without centralized proxy
        $proxy_args = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        return wp_remote_request( $url, array_merge( $request_args, $proxy_args ) );
    }

    /**
     * Check if Cloudflare proxy is available for fallback
     * (deprecated - kept for backward compatibility)
     */
    protected function is_cloudflare_proxy_enabled( array $context ) {
        return false; // No longer used - fallback is automatic per-source
    }
    
    /**
     * Merge proxy args helper
     */
    protected function merge_proxy_args( array $base_args, array $proxy_args ) {
        if ( empty( $proxy_args ) ) {
            return $base_args;
        }
        return array_merge( $base_args, $proxy_args );
    }
}
