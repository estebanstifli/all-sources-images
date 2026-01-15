<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ALLSI_Source_Flickr extends ALLSI_Image_Source {

    const API_ENDPOINT     = 'https://api.flickr.com/services/rest/';

    public function get_slug() {
        return 'flickr';
    }

    public function generate( array $context ) {
        // Log that Flickr source is being called
        if ( function_exists( 'ALLSI_log' ) ) {
            ALLSI_log( array(
                'message' => 'Flickr generate() called',
                'post_id' => isset( $context['post_id'] ) ? $context['post_id'] : 'N/A',
            ), 'FLICKR_SOURCE_START' );
        }
        
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $bank_options   = isset( $global_options['flickr'] ) && is_array( $global_options['flickr'] ) ? $global_options['flickr'] : array();
        $api_key        = $this->resolve_api_key( $bank_options );
        $search_term    = $this->resolve_search_term( $context );
        $proxy_args     = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        $selected_image = isset( $context['selected_image'] ) ? $context['selected_image'] : 'first_result';
        $log            = isset( $context['log'] ) ? $context['log'] : null;
        $page           = isset( $context['page'] ) ? max( 1, intval( $context['page'] ) ) : 1;
        
        // Use Cloudflare fallback if no API key configured
        $use_cloudflare_fallback = empty( $api_key );
        
        // Log API key status
        if ( function_exists( 'ALLSI_log' ) ) {
            ALLSI_log( array(
                'post_id' => isset( $context['post_id'] ) ? $context['post_id'] : 'N/A',
                'has_api_key' => ! empty( $api_key ),
                'use_cloudflare_fallback' => $use_cloudflare_fallback,
                'search_term' => $search_term,
            ), 'FLICKR_CONFIG' );
        }
        
        // Store fallback flag in context for nested requests
        $context['use_cloudflare_fallback'] = $use_cloudflare_fallback;

        if ( '' === trim( $search_term ) ) {
            return new WP_Error( 'ALLSI_flickr_missing_query', __( 'No search query available for Flickr.', 'all-sources-images' ) );
        }

        $query_args   = $this->build_search_query( $bank_options, $api_key, $search_term, $page );
        $search_data  = $this->perform_rest_request( $query_args, $proxy_args, 'ALLSI_flickr_http_error', $context );

        if ( is_wp_error( $search_data ) ) {
            // Log the error
            if ( function_exists( 'ALLSI_log' ) ) {
                ALLSI_log( array(
                    'post_id' => isset( $context['post_id'] ) ? $context['post_id'] : 'N/A',
                    'error_code' => $search_data->get_error_code(),
                    'error_message' => $search_data->get_error_message(),
                ), 'FLICKR_API_ERROR' );
            }
            return $search_data;
        }

        if ( $log ) {
            $log->info( 'Flickr request', array(
                'post'  => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'query' => $search_term,
                'args'  => $query_args,
            ) );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return $search_data;
        }

        $photos = isset( $search_data['photos']['photo'] ) && is_array( $search_data['photos']['photo'] ) ? $search_data['photos']['photo'] : array();
        if ( empty( $photos ) ) {
            return new WP_Error( 'ALLSI_flickr_no_results', __( 'Flickr returned no photos.', 'all-sources-images' ) );
        }

        if ( 'random_result' === $selected_image ) {
            shuffle( $photos );
        }

        $translator   = $this->get_translator_callable( $context );
        $source_label = __( 'Flickr', 'all-sources-images' );

        foreach ( $photos as $photo ) {
            $image_url = $this->resolve_image_url_from_photo( $photo, $api_key, $proxy_args, $context );
            if ( empty( $image_url ) ) {
                continue;
            }
            
            // Log the image URL for debugging
            if ( function_exists( 'ALLSI_log' ) ) {
                ALLSI_log( array(
                    'source' => 'flickr',
                    'search_term' => $search_term,
                    'image_url' => $image_url,
                    'photo_id' => isset( $photo['id'] ) ? $photo['id'] : 'N/A',
                ), 'FLICKR_IMAGE_FOUND' );
            }

            $file_media = $this->download_image( $image_url, $proxy_args, $context );
            if ( is_wp_error( $file_media ) ) {
                continue;
            }

            $author_name = $this->resolve_author_name( $photo, $api_key, $proxy_args, $context );
            $alt_text    = ALLSI_Source_Text_Helper::build_alt_text( $global_options, $search_term, $source_label, $translator );
            $caption     = ALLSI_Source_Text_Helper::build_caption( $global_options, $author_name, $source_label );

            return array(
                'url_results' => $image_url,
                'file_media'  => $file_media,
                'alt_img'     => $alt_text,
                'caption_img' => $caption,
                'raw_response'=> $search_data,
            );
        }

        return new WP_Error( 'ALLSI_flickr_download_failed', __( 'Unable to download a valid Flickr image.', 'all-sources-images' ) );
    }

    private function resolve_api_key( array $bank_options ) {
        $option_key = isset( $bank_options['apikey'] ) ? trim( $bank_options['apikey'] ) : '';
        return apply_filters( 'ALLSI_flickr_api_key', $option_key );
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

    private function build_search_query( array $bank_options, $api_key, $search_term, $page = 1 ) {
        return array(
            'method'         => 'flickr.photos.search',
            'api_key'        => $api_key,
            'text'           => $search_term,
            'per_page'       => 16,
            'page'           => max( 1, intval( $page ) ),
            'format'         => 'json',
            'nojsoncallback' => '1',
            'privacy_filter' => '1',
            'license'        => $this->build_license_param( $bank_options ),
            'sort'           => 'relevance',
            'content_type'   => $this->resolve_content_type( $bank_options ),
            'safe_search'    => 1,
            'extras'         => 'owner_name,url_o,url_k,url_h,url_l,url_c,url_z',
        );
    }

    private function build_license_param( array $bank_options ) {
        if ( isset( $bank_options['rights'] ) && ! empty( $bank_options['rights'] ) ) {
            if ( is_array( $bank_options['rights'] ) ) {
                $values = array();
                foreach ( $bank_options['rights'] as $right ) {
                    if ( '' !== $right ) {
                        $values[] = $right;
                    }
                }
                if ( ! empty( $values ) ) {
                    return implode( ',', $values );
                }
            } elseif ( is_string( $bank_options['rights'] ) ) {
                return $bank_options['rights'];
            }
        }
        return '0,1,2,3,4,5,6,7,8';
    }

    private function resolve_content_type( array $bank_options ) {
        if ( isset( $bank_options['imgtype'] ) && '' !== $bank_options['imgtype'] ) {
            return $bank_options['imgtype'];
        }
        return '7';
    }

    private function resolve_image_url_from_photo( array $photo, $api_key, array $proxy_args, array $context ) {
        $preferred = array( 'url_o', 'url_k', 'url_h', 'url_l', 'url_c', 'url_z' );
        foreach ( $preferred as $key ) {
            if ( isset( $photo[ $key ] ) && '' !== $photo[ $key ] ) {
                return $photo[ $key ];
            }
        }

        if ( empty( $photo['id'] ) ) {
            return '';
        }

        $sizes = $this->perform_rest_request( array(
            'method'         => 'flickr.photos.getSizes',
            'api_key'        => $api_key,
            'photo_id'       => $photo['id'],
            'format'         => 'json',
            'nojsoncallback' => '1',
        ), $proxy_args, 'ALLSI_flickr_size_error', $context );

        if ( is_wp_error( $sizes ) ) {
            return '';
        }

        if ( empty( $sizes['sizes']['size'] ) || ! is_array( $sizes['sizes']['size'] ) ) {
            return '';
        }

        $size_list = $sizes['sizes']['size'];
        $last      = end( $size_list );
        if ( $last && ! empty( $last['source'] ) ) {
            return $last['source'];
        }

        foreach ( array_reverse( $size_list ) as $size ) {
            if ( isset( $size['source'] ) && '' !== $size['source'] ) {
                return $size['source'];
            }
        }

        return '';
    }

    private function resolve_author_name( array $photo, $api_key, array $proxy_args, array $context ) {
        if ( isset( $photo['ownername'] ) && '' !== $photo['ownername'] ) {
            return $photo['ownername'];
        }
        if ( empty( $photo['owner'] ) ) {
            return '';
        }

        $person = $this->perform_rest_request( array(
            'method'         => 'flickr.people.getInfo',
            'api_key'        => $api_key,
            'user_id'        => $photo['owner'],
            'format'         => 'json',
            'nojsoncallback' => '1',
        ), $proxy_args, 'ALLSI_flickr_author_error', $context );

        if ( is_wp_error( $person ) ) {
            return '';
        }

        if ( ! empty( $person['person']['realname']['_content'] ) ) {
            return ucwords( $person['person']['realname']['_content'] );
        }
        if ( ! empty( $person['person']['username']['_content'] ) ) {
            return ucwords( $person['person']['username']['_content'] );
        }
        return '';
    }

    private function perform_rest_request( array $params, array $proxy_args, $error_code, array $context ) {
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 30,
            'method'             => 'GET',
            'redirection'        => 5,
            'user-agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'reject_unsafe_urls' => false,
            'sslverify'          => false,
        ), $proxy_args );

        $url = add_query_arg( $params, self::API_ENDPOINT );
        $use_cloudflare_fallback = isset( $context['use_cloudflare_fallback'] ) ? $context['use_cloudflare_fallback'] : false;
        $response = $this->request_with_proxy( $this->get_slug(), $url, $request_args, $context, 'GET', $use_cloudflare_fallback );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $payload     = json_decode( $body_raw, true );

        if ( 200 !== intval( $status_code ) || ! is_array( $payload ) ) {
            return new WP_Error( $error_code, __( 'Unexpected response from Flickr.', 'all-sources-images' ), array(
                'status' => $status_code,
                'body'   => $body_raw,
            ) );
        }

        if ( isset( $payload['stat'] ) && 'ok' !== $payload['stat'] ) {
            $message = isset( $payload['message'] ) ? $payload['message'] : __( 'Flickr returned an error.', 'all-sources-images' );
            return new WP_Error( $error_code, $message, array( 'status' => $status_code ) );
        }

        return $payload;
    }

    private function download_image( $url, array $proxy_args, array $context ) {
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 30,
            'redirection'        => 9,
            'user-agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'reject_unsafe_urls' => false,
            'sslverify'          => false,
        ), $proxy_args );

        // Image download doesn't use Cloudflare proxy (direct download)
        $response = wp_remote_request( $url, $request_args );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code  = wp_remote_retrieve_response_code( $response );
        $content_type = wp_remote_retrieve_header( $response, 'content-type' );

        if ( 200 !== intval( $status_code ) || ( is_string( $content_type ) && false !== strpos( $content_type, 'text/html' ) ) ) {
            return new WP_Error( 'ALLSI_flickr_invalid_media', __( 'Flickr returned an invalid media payload.', 'all-sources-images' ) );
        }

        return $response;
    }

    private function get_translator_callable( array $context ) {
        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ALLSI_translate_text' ) ) {
            return array( $context['generation'], 'ALLSI_translate_text' );
        }
        return null;
    }
}
