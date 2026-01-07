<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ALLSI_Source_Youtube extends ALLSI_Image_Source {

    private const API_ENDPOINT = 'https://www.googleapis.com/youtube/v3/search';

    public function get_slug() {
        return 'youtube';
    }

    public function generate( array $context ) {
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $bank_options   = isset( $global_options['youtube'] ) && is_array( $global_options['youtube'] ) ? $global_options['youtube'] : array();
        $api_key        = isset( $bank_options['apikey'] ) ? trim( $bank_options['apikey'] ) : '';
        $search_term    = $this->resolve_search_term( $context );
        $proxy_args     = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        $selected_image = isset( $context['selected_image'] ) ? $context['selected_image'] : 'first_result';
        $log            = isset( $context['log'] ) ? $context['log'] : null;

        // YouTube does NOT support Cloudflare fallback - API key is always required
        if ( empty( $api_key ) ) {
            return new WP_Error( 'ALLSI_youtube_missing_key', __( 'YouTube API key is missing.', 'all-sources-images' ) );
        }

        if ( '' === trim( $search_term ) ) {
            return new WP_Error( 'ALLSI_youtube_missing_query', __( 'No search query available for YouTube.', 'all-sources-images' ) );
        }

        $query_args   = $this->build_query_args( $bank_options, $api_key, $search_term );
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 30,
            'method'             => 'GET',
            'redirection'        => 5,
            'user-agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'reject_unsafe_urls' => false,
            'sslverify'          => false,
        ), $proxy_args );

        // YouTube uses direct request or legacy proxy only (no Cloudflare fallback)
        $response = $this->request_with_proxy( 'youtube', add_query_arg( $query_args, self::API_ENDPOINT ), $request_args, $context, 'GET', false );

        if ( $log ) {
            $log->info( 'YouTube request', array(
                'post'  => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'query' => $search_term,
                'args'  => $query_args,
            ) );
        }

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $payload     = json_decode( $body_raw, true );

        if ( 200 !== intval( $status_code ) || ! is_array( $payload ) ) {
            return new WP_Error( 'ALLSI_youtube_http_error', __( 'Unexpected response from YouTube.', 'all-sources-images' ), array(
                'status' => $status_code,
                'body'   => $body_raw,
            ) );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return $payload;
        }

        $items = isset( $payload['items'] ) && is_array( $payload['items'] ) ? $payload['items'] : array();
        if ( empty( $items ) ) {
            return new WP_Error( 'ALLSI_youtube_no_results', __( 'YouTube returned no videos.', 'all-sources-images' ) );
        }

        if ( 'random_result' === $selected_image ) {
            shuffle( $items );
        }

        $translator   = $this->get_translator_callable( $context );
        $source_label = __( 'YouTube', 'all-sources-images' );

        foreach ( $items as $item ) {
            $video_id = $this->extract_video_id( $item );
            if ( empty( $video_id ) ) {
                continue;
            }

            $thumbnail_url = $this->pick_thumbnail_url( $item, $bank_options );
            if ( empty( $thumbnail_url ) ) {
                continue;
            }

            $file_media = $this->download_image( $thumbnail_url, $proxy_args );
            if ( is_wp_error( $file_media ) ) {
                continue;
            }

            $author_name = isset( $item['snippet']['channelTitle'] ) ? $item['snippet']['channelTitle'] : '';
            $alt_text    = ALLSI_Source_Text_Helper::build_alt_text( $global_options, $search_term, $source_label, $translator );
            $caption     = ALLSI_Source_Text_Helper::build_caption( $global_options, $author_name, $source_label );

            return array(
                'url_results' => $thumbnail_url,
                'file_media'  => $file_media,
                'alt_img'     => $alt_text,
                'caption_img' => $caption,
                'video_id'    => $video_id,
                'raw_response'=> $payload,
            );
        }

        return new WP_Error( 'ALLSI_youtube_download_failed', __( 'Unable to download a valid YouTube thumbnail.', 'all-sources-images' ) );
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

    private function build_query_args( array $bank_options, $api_key, $search_term ) {
        $order        = ! empty( $bank_options['search_order'] ) ? $bank_options['search_order'] : 'relevance';
        $video_length = ! empty( $bank_options['video_duration'] ) ? $bank_options['video_duration'] : 'any';
        $safe_search  = isset( $bank_options['safe_search'] ) ? $bank_options['safe_search'] : 'moderate';
        $region_code  = isset( $bank_options['region_code'] ) ? strtoupper( trim( $bank_options['region_code'] ) ) : '';
        $max_results  = isset( $bank_options['max_results'] ) ? intval( $bank_options['max_results'] ) : 10;
        $max_results  = max( 1, min( 50, $max_results ) );

        $args = array(
            'key'           => $api_key,
            'q'             => $search_term,
            'part'          => 'snippet',
            'type'          => 'video',
            'order'         => $order,
            'videoDuration' => $video_length,
            'safeSearch'    => $safe_search,
            'maxResults'    => $max_results,
        );

        if ( ! empty( $region_code ) && 2 === strlen( $region_code ) ) {
            $args['regionCode'] = $region_code;
        }

        return $args;
    }

    private function extract_video_id( array $item ) {
        if ( isset( $item['id']['videoId'] ) ) {
            return $item['id']['videoId'];
        }
        return '';
    }

    private function pick_thumbnail_url( array $item, array $bank_options ) {
        $thumbnails = isset( $item['snippet']['thumbnails'] ) ? $item['snippet']['thumbnails'] : array();
        if ( empty( $thumbnails ) ) {
            return '';
        }

        $preferred = isset( $bank_options['thumbnail_quality'] ) ? $bank_options['thumbnail_quality'] : 'high';
        $fallbacks = array_unique( array(
            $preferred,
            'maxresdefault',
            'standard',
            'high',
            'medium',
            'default',
        ) );

        foreach ( $fallbacks as $quality ) {
            if ( isset( $thumbnails[ $quality ]['url'] ) && '' !== $thumbnails[ $quality ]['url'] ) {
                return $thumbnails[ $quality ]['url'];
            }
        }

        foreach ( $thumbnails as $data ) {
            if ( isset( $data['url'] ) && '' !== $data['url'] ) {
                return $data['url'];
            }
        }

        return '';
    }

    private function download_image( $url, array $proxy_args ) {
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 30,
            'redirection'        => 5,
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
            return new WP_Error( 'ALLSI_youtube_invalid_media', __( 'YouTube returned an invalid thumbnail payload.', 'all-sources-images' ) );
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
