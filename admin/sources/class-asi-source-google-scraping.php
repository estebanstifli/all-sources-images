<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Google_Scraping extends ASI_Image_Source {

    private const ENDPOINT = 'https://www.google.com/search';

    public function get_slug() {
        return 'google_scraping';
    }

    public function generate( array $context ) {
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $bank_options   = isset( $global_options['google_scraping'] ) ? $global_options['google_scraping'] : array();
        $search_term    = $this->resolve_search_term( $context );
        $selected_image = isset( $context['selected_image'] ) ? $context['selected_image'] : 'first_result';
        $proxy_args     = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        $log            = isset( $context['log'] ) ? $context['log'] : null;

        if ( '' === trim( $search_term ) ) {
            return new WP_Error( 'asi_google_scraping_missing_query', __( 'No search query available for Google Images (scraping).', 'all-sources-images' ) );
        }

        $query_args   = $this->build_query_args( $bank_options, $search_term );
        $request_url  = add_query_arg( $query_args, self::ENDPOINT );
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 20,
            'redirection'        => 5,
            'user-agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'reject_unsafe_urls' => false,
            'sslverify'          => false,
            'headers'            => array(
                'Accept-Language' => $this->build_accept_language_header( $bank_options ),
            ),
        ), $proxy_args );

        $response = $this->request_with_proxy( 'google_scraping', $request_url, $request_args, $context );

        if ( $log ) {
            $log->info( 'Google scraping request', array(
                'post'  => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'query' => $search_term,
                'url'   => $request_url,
            ) );
        }

        if ( is_wp_error( $response ) ) {
            if ( $log ) {
                $log->error( 'Google scraping transport error', array(
                    'post'  => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                    'error' => $response->get_error_message(),
                ) );
            }
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );

        if ( 200 !== intval( $status_code ) || empty( $body_raw ) ) {
            if ( $log ) {
                $log->error( 'Google scraping HTTP error', array(
                    'status' => $status_code,
                    'body'   => $this->truncate_body( $body_raw ),
                ) );
            }
            return new WP_Error( 'asi_google_scraping_http_error', __( 'Unexpected response from Google Images (scraping).', 'all-sources-images' ), array(
                'status' => $status_code,
                'body'   => $body_raw,
            ) );
        }

        $results = $this->parse_results( $body_raw );
        if ( empty( $results ) ) {
            if ( $log ) {
                $log->warning( 'Google scraping returned zero matches', array(
                    'query' => $search_term,
                    'body'  => $this->truncate_body( $body_raw ),
                ) );
            }
            return new WP_Error( 'asi_google_scraping_no_results', __( 'Google Images (scraping) returned no usable results.', 'all-sources-images' ), array(
                'status' => $status_code,
                'body'   => $body_raw,
            ) );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return array( 'results' => $results );
        }

        if ( 'random_result' === $selected_image ) {
            shuffle( $results );
        }

        $translator   = $this->get_translator_callable( $context );
        $source_label = __( 'Google Images (Scraping)', 'all-sources-images' );

        foreach ( $results as $result ) {
            $image_url = isset( $result['url'] ) ? $result['url'] : '';
            if ( empty( $image_url ) ) {
                continue;
            }

            if ( ! $this->is_candidate_usable( $image_url, $proxy_args ) ) {
                continue;
            }

            $file_media = $this->download_image( $image_url, $proxy_args );
            if ( is_wp_error( $file_media ) ) {
                continue;
            }

            $alt_text     = ASI_Source_Text_Helper::build_alt_text( $global_options, $search_term, $source_label, $translator );
            $caption_text = $this->build_caption_text( $global_options, $result );

            return array(
                'url_results' => $image_url,
                'file_media'  => $file_media,
                'alt_img'     => $alt_text,
                'caption_img' => $caption_text,
                'raw_response'=> array( 'results' => $results ),
            );
        }

        return new WP_Error( 'asi_google_scraping_download_failed', __( 'Unable to download a valid Google image (scraping).', 'all-sources-images' ) );
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

    private function build_query_args( array $bank_options, $search_term ) {
        $country  = ! empty( $bank_options['search_country'] ) ? $bank_options['search_country'] : 'en';
        $img_color = ! empty( $bank_options['img_color'] ) ? $bank_options['img_color'] : '';
        $imgsz    = ! empty( $bank_options['imgsz'] ) ? $bank_options['imgsz'] : '';
        $format   = ! empty( $bank_options['format'] ) ? $bank_options['format'] : '';
        $imgtype  = ! empty( $bank_options['imgtype'] ) ? $bank_options['imgtype'] : '';
        $rights   = ! empty( $bank_options['rights'] ) ? $bank_options['rights'] : '';
        $safe     = ! empty( $bank_options['safe'] ) ? $bank_options['safe'] : 'medium';

        if ( 'moderate' === $safe ) {
            $safe = 'medium';
        }

        $tbs = '';
        if ( ! empty( $rights ) ) {
            $tbs .= 'sur:' . $rights . ',';
        }
        if ( ! empty( $imgtype ) ) {
            $tbs .= 'itp:' . $imgtype . ',';
        }
        if ( ! empty( $imgsz ) ) {
            $tbs .= 'isz:' . $imgsz . ',';
        }
        if ( ! empty( $format ) ) {
            $tbs .= 'iar:' . $format . ',';
        }
        if ( ! empty( $img_color ) ) {
            $tbs .= 'ic:specific,isc:' . $img_color;
        }

        return array(
            'tbm' => 'isch',
            'q'   => $search_term,
            'hl'  => $country,
            'safe'=> $safe,
            'rsz' => '3',
            'tbs' => $tbs,
            'udm' => 2,
            'ijn' => 0,
        );
    }

    private function parse_results( $body ) {
        $results = $this->parse_data_attributes( $body );

        if ( ! empty( $results ) ) {
            return $results;
        }

        return $this->parse_embedded_json( $body );
    }

    private function parse_data_attributes( $body ) {
        preg_match_all( '/data-ou="([^"]*)"/u', $body, $url_matches );
        preg_match_all( '/data-pt="([^"]*)"/u', $body, $alt_matches );
        preg_match_all( '/data-st="([^"]*)"/u', $body, $caption_matches );

        return $this->combine_scraped_arrays(
            isset( $url_matches[1] ) ? $url_matches[1] : array(),
            isset( $alt_matches[1] ) ? $alt_matches[1] : array(),
            isset( $caption_matches[1] ) ? $caption_matches[1] : array()
        );
    }

    private function parse_embedded_json( $body ) {
        preg_match_all( '/"ou":"((?:[^"\\\\]|\\\\.)*)"/u', $body, $url_matches );
        preg_match_all( '/"pt":"((?:[^"\\\\]|\\\\.)*)"/u', $body, $alt_matches );
        preg_match_all( '/"ru":"((?:[^"\\\\]|\\\\.)*)"/u', $body, $caption_matches );

        return $this->combine_scraped_arrays(
            isset( $url_matches[1] ) ? $url_matches[1] : array(),
            isset( $alt_matches[1] ) ? $alt_matches[1] : array(),
            isset( $caption_matches[1] ) ? $caption_matches[1] : array()
        );
    }

    private function combine_scraped_arrays( array $urls, array $alts, array $captions ) {
        $results = array();
        $max     = max( count( $urls ), count( $alts ), count( $captions ) );

        for ( $i = 0; $i < $max; $i++ ) {
            $results[] = array(
                'url'     => $this->sanitize_scraped_url( isset( $urls[ $i ] ) ? $urls[ $i ] : '' ),
                'alt'     => $this->sanitize_scraped_text( isset( $alts[ $i ] ) ? $alts[ $i ] : '' ),
                'caption' => $this->sanitize_scraped_text( isset( $captions[ $i ] ) ? $captions[ $i ] : '' ),
            );
        }

        $filtered = array_filter( $results, function( $item ) {
            return ! empty( $item['url'] );
        } );

        return array_slice( array_values( $filtered ), 0, 30 );
    }

    private function sanitize_scraped_url( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        $decoded = $this->decode_google_value( $value );

        $pattern = '/,\["(http(?:[^"\\\\]|\\\\.)*)",\d+?,\d+?\]/u';
        $clean   = preg_replace( $pattern, '$1', $decoded );

        return esc_url_raw( trim( $clean ) );
    }

    private function sanitize_scraped_text( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        $decoded = $this->decode_google_value( $value );

        return wp_strip_all_tags( $decoded );
    }

    private function decode_google_value( $value ) {
        $value   = html_entity_decode( $value, ENT_QUOTES );
        $encoded = wp_json_encode( $value );
        if ( false === $encoded ) {
            return $value;
        }

        $decoded = json_decode( $encoded, true );
        if ( is_string( $decoded ) ) {
            return $decoded;
        }

        return $value;
    }

    private function is_candidate_usable( $url, array $proxy_args ) {
        $head_args = $this->merge_proxy_args( array(
            'timeout'     => 10,
            'redirection' => 2,
            'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        ), $proxy_args );

        $head_response = wp_remote_head( $url, $head_args );
        if ( is_wp_error( $head_response ) ) {
            return false;
        }

        $status = wp_remote_retrieve_response_code( $head_response );
        if ( 200 !== intval( $status ) ) {
            return false;
        }

        $image_info = @getimagesize( $url );
        return false !== $image_info;
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
            return new WP_Error( 'asi_google_scraping_invalid_media', __( 'Google scraping returned an invalid image payload.', 'all-sources-images' ) );
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

    private function build_caption_text( array $options, array $result ) {
        if ( empty( $options['enable_caption'] ) || 'enable' !== $options['enable_caption'] ) {
            return '';
        }

        return isset( $result['caption'] ) ? $result['caption'] : '';
    }

    private function get_translator_callable( array $context ) {
        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ASI_translate_text' ) ) {
            return array( $context['generation'], 'ASI_translate_text' );
        }

        return null;
    }

    private function build_accept_language_header( array $bank_options ) {
        $country = ! empty( $bank_options['search_country'] ) ? strtolower( $bank_options['search_country'] ) : 'en';
        $region  = ( 2 === strlen( $country ) ) ? strtoupper( $country ) : 'US';
        return $country . '-' . $region . ',en;q=0.9';
    }
}
