<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Pexels extends ASI_Image_Source {

    public function get_slug() {
        return 'pexels';
    }

    public function generate( array $context ) {
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $bank_options   = isset( $global_options['pexels'] ) && is_array( $global_options['pexels'] ) ? $global_options['pexels'] : array();
        $api_key        = isset( $bank_options['apikey'] ) ? trim( $bank_options['apikey'] ) : '';
        $log            = isset( $context['log'] ) ? $context['log'] : null;
        $proxy_args     = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        $selected_image = isset( $context['selected_image'] ) ? $context['selected_image'] : 'first_result';
        $search_term    = $this->resolve_search_term( $context );

        if ( empty( $api_key ) ) {
            return new WP_Error( 'asi_pexels_missing_key', __( 'Pexels API key is missing.', 'all-sources-images' ) );
        }

        if ( empty( $search_term ) ) {
            return new WP_Error( 'asi_pexels_missing_query', __( 'No search query available for Pexels.', 'all-sources-images' ) );
        }

        $endpoint    = 'https://api.pexels.com/v1/search';
        $query_args  = $this->build_query_args( $search_term, $bank_options );
        $request_url = add_query_arg( $query_args, $endpoint );
        $request_args = $this->merge_proxy_args( array(
            'headers' => array(
                'Authorization' => $api_key,
            ),
            'timeout'             => 30,
            'method'              => 'GET',
            'redirection'         => 9,
            'user-agent'          => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'reject_unsafe_urls'  => false,
            'sslverify'           => false,
        ), $proxy_args );

        $response = wp_remote_request( $request_url, $request_args );

        if ( $log ) {
            $log->info( 'Pexels request', array(
                'post'  => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'query' => $search_term,
                'url'   => $request_url,
            ) );
        }

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body_raw    = wp_remote_retrieve_body( $response );
        $status_code = wp_remote_retrieve_response_code( $response );

        if ( ! empty( $body_raw ) ) {
            error_log( '[All Sources Images][Pexels RAW] ' . mb_substr( $body_raw, 0, 4000 ) );
        }

        if ( 200 !== intval( $status_code ) ) {
            return new WP_Error( 'asi_pexels_http_error', __( 'Unexpected response from Pexels.', 'all-sources-images' ), array( 'status' => $status_code ) );
        }

        $payload = json_decode( $body_raw, true );
        if ( ! is_array( $payload ) ) {
            return new WP_Error( 'asi_pexels_invalid_payload', __( 'Pexels returned an invalid response.', 'all-sources-images' ) );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return $payload;
        }

        $photos = isset( $payload['photos'] ) && is_array( $payload['photos'] ) ? $payload['photos'] : array();
        $translator = $this->get_translator_callable( $context );
        $source_label = __( 'Pexels', 'all-sources-images' );
        if ( empty( $photos ) ) {
            return new WP_Error( 'asi_pexels_no_results', __( 'Pexels returned no photos.', 'all-sources-images' ) );
        }

        if ( 'random_result' === $selected_image ) {
            shuffle( $photos );
        }

        foreach ( $photos as $photo ) {
            $image_url = isset( $photo['src']['original'] ) ? $photo['src']['original'] : '';
            if ( empty( $image_url ) ) {
                continue;
            }

            $file_media = $this->download_image( $image_url, $proxy_args );
            if ( is_wp_error( $file_media ) ) {
                continue;
            }

            $alt_text = ASI_Source_Text_Helper::build_alt_text( $global_options, $search_term, $source_label, $translator );
            $caption_text = ASI_Source_Text_Helper::build_caption( $global_options, isset( $photo['photographer'] ) ? $photo['photographer'] : '', $source_label );

            $result = array(
                'url_results' => $image_url,
                'file_media'  => $file_media,
                'alt_img'     => $alt_text,
                'caption_img' => $caption_text,
                'raw_response'=> $payload,
            );

            return $result;
        }

        return new WP_Error( 'asi_pexels_download_failed', __( 'Unable to download a valid Pexels image.', 'all-sources-images' ) );
    }

    private function resolve_search_term( array $context ) {
        if ( ! empty( $context['search'] ) ) {
            return $context['search'];
        }
        if ( isset( $context['img_block']['based_on']['title'] ) ) {
            return $context['img_block']['based_on']['title'];
        }
        return '';
    }

    private function build_query_args( $search, array $bank_options ) {
        $args = array(
            'query'    => $search,
            'per_page' => '15',
            'locale'   => ! empty( $bank_options['locale'] ) ? $bank_options['locale'] : 'en-US',
        );

        if ( ! empty( $bank_options['orientation'] ) && 'all' !== $bank_options['orientation'] ) {
            $args['orientation'] = $bank_options['orientation'];
        }
        if ( ! empty( $bank_options['size'] ) && 'all' !== $bank_options['size'] ) {
            $args['size'] = $bank_options['size'];
        }
        if ( ! empty( $bank_options['color'] ) && 'all' !== $bank_options['color'] ) {
            $args['color'] = $bank_options['color'];
        }

        return $args;
    }

    private function merge_proxy_args( array $base_args, array $proxy_args ) {
        if ( empty( $proxy_args ) ) {
            return $base_args;
        }
        if ( isset( $proxy_args['headers'] ) && is_array( $proxy_args['headers'] ) ) {
            if ( isset( $base_args['headers'] ) ) {
                $base_args['headers'] = array_merge( $base_args['headers'], $proxy_args['headers'] );
            } else {
                $base_args['headers'] = $proxy_args['headers'];
            }
            unset( $proxy_args['headers'] );
        }
        return array_merge( $base_args, $proxy_args );
    }

    private function download_image( $url, array $proxy_args ) {
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 30,
            'redirection'        => 9,
            'user-agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'reject_unsafe_urls' => false,
            'sslverify'          => false,
        ), $proxy_args );

        $response = wp_remote_request( $url, $request_args );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $content_type = wp_remote_retrieve_header( $response, 'content-type' );

        if ( 200 !== intval( $status_code ) || ( is_string( $content_type ) && false !== strpos( $content_type, 'text/html' ) ) ) {
            return new WP_Error( 'asi_pexels_invalid_media', __( 'Pexels returned an invalid media payload.', 'all-sources-images' ) );
        }

        return $response;
    }

    private function get_translator_callable( array $context ) {
        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ASI_translate_text' ) ) {
            return array( $context['generation'], 'ASI_translate_text' );
        }

        return null;
    }
}
