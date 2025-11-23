<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class ASI_Image_Source {
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

    protected function request_with_proxy( $service, $url, array $request_args, array $context, $method = 'GET' ) {
        $request_args['method'] = strtoupper( $method );

        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ASI_remote_request' ) ) {
            return $context['generation']->ASI_remote_request( $service, $url, $request_args );
        }

        $proxy_args = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        return wp_remote_request( $url, array_merge( $request_args, $proxy_args ) );
    }

        protected function is_cloudflare_proxy_enabled( array $context ) {
            if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ASI_current_proxy_mode' ) ) {
                return 'cloudflare' === $context['generation']->ASI_current_proxy_mode();
            }
            return false;
        }
}
