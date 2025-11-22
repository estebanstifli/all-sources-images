<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Giphy extends ASI_Image_Source {

    public function get_slug() {
        return 'giphy';
    }

    public function generate( array $context ) {
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $bank_options   = isset( $global_options['giphy'] ) && is_array( $global_options['giphy'] ) ? $global_options['giphy'] : array();
        $api_key        = isset( $bank_options['apikey'] ) ? trim( $bank_options['apikey'] ) : '';
        $proxy_args     = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        $selected_image = isset( $context['selected_image'] ) ? $context['selected_image'] : 'first_result';
        $search_term    = $this->resolve_search_term( $context );
        $log            = isset( $context['log'] ) ? $context['log'] : null;
        $page           = isset( $context['page'] ) ? max( 1, intval( $context['page'] ) ) : 1;

        if ( empty( $api_key ) ) {
            return new WP_Error( 'asi_giphy_missing_key', __( 'GIPHY API key is missing.', 'all-sources-images' ) );
        }

        if ( '' === trim( $search_term ) ) {
            return new WP_Error( 'asi_giphy_missing_query', __( 'No search query available for GIPHY.', 'all-sources-images' ) );
        }

        $endpoint    = $this->get_endpoint( $bank_options );
        $query_args  = $this->build_query_args( $bank_options, $api_key, $search_term, $page );
        $request_url = add_query_arg( $query_args, $endpoint );
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 30,
            'method'             => 'GET',
            'redirection'        => 9,
            'user-agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'reject_unsafe_urls' => false,
            'sslverify'          => false,
        ), $proxy_args );

        $response = wp_remote_request( $request_url, $request_args );

        if ( $log ) {
            $log->info( 'GIPHY request', array(
                'post'  => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'query' => $search_term,
                'url'   => $request_url,
            ) );
        }

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );

        if ( ! empty( $body_raw ) ) {
            $preview = function_exists( 'mb_substr' ) ? mb_substr( $body_raw, 0, 4000 ) : substr( $body_raw, 0, 4000 );
            error_log( '[All Sources Images][Giphy RAW] ' . $preview );
        }

        $payload = json_decode( $body_raw, true );
        if ( 200 !== intval( $status_code ) || ! is_array( $payload ) ) {
            return new WP_Error(
                'asi_giphy_http_error',
                __( 'Unexpected response from GIPHY.', 'all-sources-images' ),
                array(
                    'status' => $status_code,
                    'body'   => $body_raw,
                )
            );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return $payload;
        }

        $items = isset( $payload['data'] ) && is_array( $payload['data'] ) ? $payload['data'] : array();
        if ( empty( $items ) ) {
            return new WP_Error( 'asi_giphy_no_results', __( 'GIPHY returned no results.', 'all-sources-images' ) );
        }

        if ( 'random_result' === $selected_image ) {
            shuffle( $items );
        }

        $translator   = $this->get_translator_callable( $context );
        $source_label = __( 'GIPHY', 'all-sources-images' );

        foreach ( $items as $item ) {
            $image_url = $this->extract_image_url( $item );
            if ( empty( $image_url ) ) {
                continue;
            }

            $file_media = $this->download_image( $image_url, $proxy_args );
            if ( is_wp_error( $file_media ) ) {
                continue;
            }

            $author_name = $this->extract_author_name( $item );
            $alt_text    = ASI_Source_Text_Helper::build_alt_text( $global_options, $search_term, $source_label, $translator );
            $caption     = ASI_Source_Text_Helper::build_caption( $global_options, $author_name, $source_label );

            return array(
                'url_results' => $image_url,
                'file_media'  => $file_media,
                'alt_img'     => $alt_text,
                'caption_img' => $caption,
                'raw_response'=> $payload,
            );
        }

        return new WP_Error( 'asi_giphy_download_failed', __( 'Unable to download a valid GIPHY image.', 'all-sources-images' ), array( 'raw' => $payload ) );
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

    private function get_endpoint( array $bank_options ) {
        $media_type = isset( $bank_options['media_type'] ) ? strtolower( $bank_options['media_type'] ) : 'gifs';
        if ( 'stickers' === $media_type ) {
            return 'https://api.giphy.com/v1/stickers/search';
        }
        return 'https://api.giphy.com/v1/gifs/search';
    }

    private function build_query_args( array $bank_options, $api_key, $search, $page = 1 ) {
        $limit = isset( $bank_options['limit'] ) ? intval( $bank_options['limit'] ) : 25;
        $limit = max( 1, min( 50, $limit ) );
        $rating = isset( $bank_options['rating'] ) ? strtolower( $bank_options['rating'] ) : 'g';
        $lang   = isset( $bank_options['lang'] ) ? strtolower( $bank_options['lang'] ) : 'en';
        $page   = max( 1, intval( $page ) );

        $trimmed_search = function_exists( 'mb_substr' ) ? mb_substr( $search, 0, 50 ) : substr( $search, 0, 50 );
        $offset = ( $page - 1 ) * $limit;

        $args = array(
            'api_key' => $api_key,
            'q'       => $trimmed_search,
            'limit'   => $limit,
            'offset'  => $offset,
            'rating'  => $rating,
            'lang'    => $lang,
        );

        if ( ! empty( $bank_options['bundle'] ) ) {
            $args['bundle'] = $bank_options['bundle'];
        }

        return $args;
    }

    private function extract_image_url( array $item ) {
        if ( isset( $item['images']['original']['url'] ) ) {
            return $item['images']['original']['url'];
        }
        if ( isset( $item['images']['downsized_large']['url'] ) ) {
            return $item['images']['downsized_large']['url'];
        }
        if ( isset( $item['images']['downsized']['url'] ) ) {
            return $item['images']['downsized']['url'];
        }
        return isset( $item['url'] ) ? $item['url'] : '';
    }

    private function extract_author_name( array $item ) {
        if ( ! empty( $item['user']['display_name'] ) ) {
            return $item['user']['display_name'];
        }
        if ( ! empty( $item['username'] ) ) {
            return $item['username'];
        }
        if ( ! empty( $item['source_tld'] ) ) {
            return $item['source_tld'];
        }
        return '';
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

        $status_code  = wp_remote_retrieve_response_code( $response );
        $content_type = wp_remote_retrieve_header( $response, 'content-type' );

        if ( 200 !== intval( $status_code ) || ( is_string( $content_type ) && false !== strpos( $content_type, 'text/html' ) ) ) {
            return new WP_Error( 'asi_giphy_invalid_media', __( 'GIPHY returned an invalid media payload.', 'all-sources-images' ) );
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
