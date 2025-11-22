<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Unsplash extends ASI_Image_Source {

    public function get_slug() {
        return 'unsplash';
    }

    public function generate( array $context ) {
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $bank_options   = isset( $global_options['unsplash'] ) ? $global_options['unsplash'] : array();
        $api_key        = isset( $bank_options['apikey'] ) ? trim( $bank_options['apikey'] ) : '';
        $search_term    = $this->resolve_search_term( $context );
        $proxy_args     = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        $selected_image = isset( $context['selected_image'] ) ? $context['selected_image'] : 'first_result';
        $log            = isset( $context['log'] ) ? $context['log'] : null;
        $page           = isset( $context['page'] ) ? max( 1, intval( $context['page'] ) ) : 1;

        if ( empty( $api_key ) ) {
            return new WP_Error( 'asi_unsplash_missing_key', __( 'Unsplash API key is missing.', 'all-sources-images' ) );
        }

        if ( '' === trim( $search_term ) ) {
            return new WP_Error( 'asi_unsplash_missing_query', __( 'No search query available for Unsplash.', 'all-sources-images' ) );
        }

        $endpoint    = 'https://api.unsplash.com/search/photos';
        $query_args  = $this->build_query_args( $bank_options, $api_key, $search_term, $page );
        $request_url = add_query_arg( $query_args, $endpoint );
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 30,
            'method'             => 'GET',
            'redirection'        => 9,
            'user-agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'reject_unsafe_urls' => false,
            'sslverify'          => false,
            'headers'            => array(
                'Accept-Version' => 'v1',
            ),
        ), $proxy_args );

        $response = wp_remote_request( $request_url, $request_args );

        if ( $log ) {
            $log->info( 'Unsplash request', array(
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
        $payload     = json_decode( $body_raw, true );

        if ( 200 !== intval( $status_code ) || ! is_array( $payload ) ) {
            return new WP_Error( 'asi_unsplash_http_error', __( 'Unexpected response from Unsplash.', 'all-sources-images' ), array( 'status' => $status_code ) );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return $payload;
        }

        $results = isset( $payload['results'] ) && is_array( $payload['results'] ) ? $payload['results'] : array();
        if ( empty( $results ) ) {
            return new WP_Error( 'asi_unsplash_no_results', __( 'Unsplash returned no photos.', 'all-sources-images' ) );
        }

        if ( 'random_result' === $selected_image ) {
            shuffle( $results );
        }

        $translator   = $this->get_translator_callable( $context );
        $source_label = __( 'Unsplash', 'all-sources-images' );

        foreach ( $results as $result ) {
            $image_url = $this->extract_image_url( $result );
            if ( empty( $image_url ) ) {
                continue;
            }

            $file_media = $this->download_image( $image_url, $proxy_args );
            if ( is_wp_error( $file_media ) ) {
                continue;
            }

            $author_name = isset( $result['user']['name'] ) ? $result['user']['name'] : '';
            $alt_text = ASI_Source_Text_Helper::build_alt_text( $global_options, $search_term, $source_label, $translator );
            $caption_text = ASI_Source_Text_Helper::build_caption( $global_options, $author_name, $source_label );

            return array(
                'url_results' => $image_url,
                'file_media'  => $file_media,
                'alt_img'     => $alt_text,
                'caption_img' => $caption_text,
                'raw_response'=> $payload,
            );
        }

        return new WP_Error( 'asi_unsplash_download_failed', __( 'Unable to download a valid Unsplash image.', 'all-sources-images' ) );
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

    private function build_query_args( array $bank_options, $api_key, $search_term, $page = 1 ) {
        $content_filter = ! empty( $bank_options['content_filter'] ) ? $bank_options['content_filter'] : 'low';
        $orientation    = ! empty( $bank_options['orientation'] ) && 'all' !== $bank_options['orientation'] ? $bank_options['orientation'] : '';
        $color          = ! empty( $bank_options['color'] ) && 'all' !== $bank_options['color'] ? $bank_options['color'] : '';

        $args = array(
            'query'          => $search_term,
            'per_page'       => 15,
            'client_id'      => $api_key,
            'content_filter' => $content_filter,
            'page'           => max( 1, intval( $page ) ),
        );

        if ( ! empty( $orientation ) ) {
            $args['orientation'] = $orientation;
        }
        if ( ! empty( $color ) ) {
            $args['color'] = $color;
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

        $status_code  = wp_remote_retrieve_response_code( $response );
        $content_type = wp_remote_retrieve_header( $response, 'content-type' );

        if ( 200 !== intval( $status_code ) || ( is_string( $content_type ) && false !== strpos( $content_type, 'text/html' ) ) ) {
            return new WP_Error( 'asi_unsplash_invalid_media', __( 'Unsplash returned an invalid media payload.', 'all-sources-images' ) );
        }

        return $response;
    }

    private function extract_image_url( array $result ) {
        if ( isset( $result['urls']['full'] ) ) {
            return $result['urls']['full'];
        }
        if ( isset( $result['urls']['regular'] ) ) {
            return $result['urls']['regular'];
        }
        if ( isset( $result['urls']['raw'] ) ) {
            return $result['urls']['raw'];
        }
        return '';
    }

    private function get_translator_callable( array $context ) {
        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ASI_translate_text' ) ) {
            return array( $context['generation'], 'ASI_translate_text' );
        }

        return null;
    }
}
