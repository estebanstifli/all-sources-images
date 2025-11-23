<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Google_Image extends ASI_Image_Source {

    private const API_ENDPOINT = 'https://www.googleapis.com/customsearch/v1';

    public function get_slug() {
        return 'google_image';
    }

    public function generate( array $context ) {
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $bank_options   = isset( $global_options['googleimage'] ) && is_array( $global_options['googleimage'] ) ? $global_options['googleimage'] : array();
        $api_key        = isset( $bank_options['apikey'] ) ? trim( $bank_options['apikey'] ) : '';
        $cx_id          = isset( $bank_options['cxid'] ) ? trim( $bank_options['cxid'] ) : '';
        $search_term    = $this->resolve_search_term( $context );
        $proxy_args     = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        $selected_image = isset( $context['selected_image'] ) ? $context['selected_image'] : 'first_result';
        $log            = isset( $context['log'] ) ? $context['log'] : null;
        $page           = isset( $context['page'] ) ? max( 1, intval( $context['page'] ) ) : 1;
        $using_cloudflare = $this->is_cloudflare_proxy_enabled( $context );

        if ( empty( $api_key ) && ! $using_cloudflare ) {
            return new WP_Error( 'asi_google_image_missing_key', __( 'Google Custom Search key is missing.', 'all-sources-images' ) );
        }

        if ( empty( $cx_id ) ) {
            return new WP_Error( 'asi_google_image_missing_cx', __( 'Google Custom Search CX ID is missing.', 'all-sources-images' ) );
        }

        if ( '' === trim( $search_term ) ) {
            return new WP_Error( 'asi_google_image_missing_query', __( 'No search query available for Google Images.', 'all-sources-images' ) );
        }

        $query_args   = $this->build_query_args( $bank_options, $api_key, $cx_id, $search_term, $page );
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 30,
            'method'             => 'GET',
            'redirection'        => 5,
            'user-agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'reject_unsafe_urls' => false,
            'sslverify'          => false,
        ), $proxy_args );

        $request_url = add_query_arg( $query_args, self::API_ENDPOINT );
        $response    = $this->request_with_proxy( 'google_image', $request_url, $request_args, $context );

        if ( $log ) {
            $log->info( 'Google Image request', array(
                'post'  => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'query' => $search_term,
                'url'   => $request_url,
            ) );
        }

        if ( is_wp_error( $response ) ) {
            if ( $log ) {
                $log->error( 'Google Image transport error', array(
                    'post'  => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                    'error' => $response->get_error_message(),
                ) );
            }
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $payload     = json_decode( $body_raw, true );

        if ( 200 !== intval( $status_code ) || ! is_array( $payload ) ) {
            if ( $log ) {
                $log->error( 'Google Image HTTP error', array(
                    'status' => $status_code,
                    'body'   => $this->truncate_body( $body_raw ),
                ) );
            }
            return new WP_Error( 'asi_google_image_http_error', __( 'Unexpected response from Google Custom Search.', 'all-sources-images' ), array(
                'status' => $status_code,
                'body'   => $body_raw,
            ) );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return $payload;
        }

        $items = isset( $payload['items'] ) && is_array( $payload['items'] ) ? $payload['items'] : array();
        if ( empty( $items ) ) {
            if ( $log ) {
                $log->warning( 'Google Image returned no results', array(
                    'query' => $search_term,
                    'body'  => $this->truncate_body( $body_raw ),
                ) );
            }
            return new WP_Error( 'asi_google_image_no_results', __( 'Google Custom Search returned no images.', 'all-sources-images' ) );
        }

        if ( 'random_result' === $selected_image ) {
            shuffle( $items );
        }

        $translator   = $this->get_translator_callable( $context );
        $source_label = __( 'Google Images', 'all-sources-images' );

        foreach ( $items as $item ) {
            $image_url = $this->extract_image_url( $item );
            if ( empty( $image_url ) ) {
                continue;
            }

            $file_media = $this->download_image( $image_url, $proxy_args );
            if ( is_wp_error( $file_media ) ) {
                continue;
            }

            $site_label = isset( $item['displayLink'] ) ? $item['displayLink'] : ''; 
            $alt_text   = ASI_Source_Text_Helper::build_alt_text( $global_options, $search_term, $source_label, $translator );
            $caption    = ASI_Source_Text_Helper::build_caption( $global_options, $site_label, $source_label );

            return array(
                'url_results' => $image_url,
                'file_media'  => $file_media,
                'alt_img'     => $alt_text,
                'caption_img' => $caption,
                'raw_response'=> $payload,
                'source_url'  => isset( $item['link'] ) ? $item['link'] : '',
            );
        }

        return new WP_Error( 'asi_google_image_download_failed', __( 'Unable to download a valid Google image.', 'all-sources-images' ) );
    }

    private function resolve_search_term( array $context ) {
        if ( ! empty( $context['search'] ) ) {
            return str_replace( '…', '', $context['search'] );
        }
        if ( isset( $context['img_block']['based_on']['title'] ) ) {
            return str_replace( '…', '', $context['img_block']['based_on']['title'] );
        }
        return '';
    }

    private function build_query_args( array $bank_options, $api_key, $cx_id, $search_term, $page = 1 ) {
        $country  = ! empty( $bank_options['search_country'] ) ? $bank_options['search_country'] : 'en';
        $img_color = ! empty( $bank_options['img_color'] ) ? $bank_options['img_color'] : '';
        $filetype = ! empty( $bank_options['filetype'] ) ? $bank_options['filetype'] : '';
        $imgsz    = ! empty( $bank_options['imgsz'] ) ? $bank_options['imgsz'] : '';
        $imgtype  = ! empty( $bank_options['imgtype'] ) ? $bank_options['imgtype'] : '';
        $safe     = ! empty( $bank_options['safe'] ) ? $bank_options['safe'] : 'medium';
        if ( 'moderate' === $safe ) {
            $safe = 'medium';
        }

        $rights = $this->build_rights_filter( $bank_options );
        $page    = max( 1, intval( $page ) );

        $args = array(
            'key'        => $api_key,
            'cx'         => $cx_id,
            'q'          => $search_term,
            'searchType' => 'image',
            'num'        => 10,
            'hl'         => $country,
            'safe'       => $safe,
            'userIp'     => isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( $_SERVER['SERVER_ADDR'] ) : '0.0.0.0',
            'start'      => 1,
        );

        $calculated_start = ( ( $page - 1 ) * $args['num'] ) + 1;
        // Google Custom Search only returns up to start index 91
        $args['start'] = max( 1, min( $calculated_start, 91 ) );

        if ( ! empty( $img_color ) ) {
            $args['imgDominantColor'] = $img_color;
        }
        if ( ! empty( $filetype ) ) {
            $args['fileType'] = $filetype;
        }
        if ( ! empty( $imgsz ) ) {
            $args['imgSize'] = $imgsz;
        }
        if ( ! empty( $imgtype ) ) {
            $args['imgType'] = $imgtype;
        }
        if ( ! empty( $rights ) ) {
            $args['rights'] = $rights;
        }

        return $args;
    }

    private function build_rights_filter( array $bank_options ) {
        if ( empty( $bank_options['rights'] ) ) {
            return '';
        }

        $rights_values = array_filter( array_map( 'sanitize_text_field', (array) $bank_options['rights'] ) );
        if ( empty( $rights_values ) ) {
            return '';
        }

        return '(' . implode( '|', $rights_values ) . ')';
    }

    private function extract_image_url( array $item ) {
        if ( isset( $item['pagemap']['cse_image'][0]['src'] ) && ! empty( $item['pagemap']['cse_image'][0]['src'] ) ) {
            return $item['pagemap']['cse_image'][0]['src'];
        }
        if ( isset( $item['link'] ) && ! empty( $item['link'] ) ) {
            return $item['link'];
        }
        return '';
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
            return new WP_Error( 'asi_google_image_invalid_media', __( 'Google returned an invalid image payload.', 'all-sources-images' ) );
        }

        return $response;
    }

    private function truncate_body( $body, $limit = 600 ) {
        if ( ! is_string( $body ) ) {
            return '';
        }
        if ( strlen( $body ) <= $limit ) {
            return $body;
        }
        return substr( $body, 0, $limit ) . '...';
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

    private function get_translator_callable( array $context ) {
        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ASI_translate_text' ) ) {
            return array( $context['generation'], 'ASI_translate_text' );
        }

        return null;
    }
}
