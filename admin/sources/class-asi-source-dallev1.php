<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Dallev1 extends ASI_Image_Source {

    private const API_ENDPOINT = 'https://api.openai.com/v1/images/generations';

    public function get_slug() {
        return 'dallev1';
    }

    public function generate( array $context ) {
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $bank_options   = isset( $global_options['dallev1'] ) && is_array( $global_options['dallev1'] ) ? $global_options['dallev1'] : array();
        $api_key        = isset( $bank_options['apikey'] ) ? trim( $bank_options['apikey'] ) : '';
        $img_size       = isset( $bank_options['imgsize'] ) ? $bank_options['imgsize'] : '1024x1024';
        $search_term    = $this->resolve_search_term( $context );
        $selected_image = isset( $context['selected_image'] ) ? $context['selected_image'] : 'first_result';
        $proxy_args     = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        $log            = isset( $context['log'] ) ? $context['log'] : null;
        $using_cloudflare = $this->is_cloudflare_proxy_enabled( $context );

        if ( empty( $api_key ) && ! $using_cloudflare ) {
            return new WP_Error( 'asi_dalle_missing_key', __( 'OpenAI API key is missing.', 'all-sources-images' ) );
        }

        if ( '' === trim( $search_term ) ) {
            return new WP_Error( 'asi_dalle_missing_prompt', __( 'No search prompt available for DALL·E.', 'all-sources-images' ) );
        }

        $payload      = $this->build_payload( $search_term, $img_size );
        $headers = array(
            'Content-Type' => 'application/json',
        );
        if ( ! empty( $api_key ) ) {
            $headers['Authorization'] = 'Bearer ' . $api_key;
        }

        $request_args = $this->merge_proxy_args( array(
            'timeout'     => 45,
            'redirection' => 2,
            'method'      => 'POST',
            'headers'     => $headers,
            'body'        => wp_json_encode( $payload ),
        ), $proxy_args );

        $response = $this->request_with_proxy( 'dallev1', self::API_ENDPOINT, $request_args, $context );

        if ( $log ) {
            $log->info( 'DALL·E request', array(
                'post'  => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'prompt'=> $payload['prompt'],
                'size'  => $payload['size'],
            ) );
        }

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $payload_raw = json_decode( $body_raw, true );

        if ( 200 !== intval( $status_code ) || ! is_array( $payload_raw ) ) {
            return new WP_Error( 'asi_dalle_http_error', __( 'Unexpected response from DALL·E.', 'all-sources-images' ), array(
                'status' => $status_code,
                'body'   => $body_raw,
            ) );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return $payload_raw;
        }

        $items = isset( $payload_raw['data'] ) && is_array( $payload_raw['data'] ) ? $payload_raw['data'] : array();
        if ( empty( $items ) ) {
            return new WP_Error( 'asi_dalle_no_results', __( 'DALL·E returned no images.', 'all-sources-images' ) );
        }

        if ( 'random_result' === $selected_image ) {
            shuffle( $items );
        }

        $translator   = $this->get_translator_callable( $context );
        $source_label = __( 'DALL·E', 'all-sources-images' );

        foreach ( $items as $item ) {
            $image_url = $this->extract_image_url( $item );
            if ( empty( $image_url ) ) {
                continue;
            }

            $file_media = $this->download_image( $image_url, $proxy_args );
            if ( is_wp_error( $file_media ) ) {
                continue;
            }

            if ( $log && isset( $item['revised_prompt'] ) ) {
                $log->info( 'DALL·E revised prompt', array(
                    'post'           => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                    'revised_prompt' => $item['revised_prompt'],
                ) );
            }

            $alt_text = ASI_Source_Text_Helper::build_alt_text( $global_options, $search_term, $source_label, $translator );
            $caption  = ASI_Source_Text_Helper::build_caption( $global_options, '', $source_label );

            return array(
                'url_results' => $image_url,
                'file_media'  => $file_media,
                'alt_img'     => $alt_text,
                'caption_img' => $caption,
                'raw_response'=> $payload_raw,
            );
        }

        return new WP_Error( 'asi_dalle_download_failed', __( 'Unable to download a valid DALL·E image.', 'all-sources-images' ) );
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

    private function build_payload( $search_term, $img_size ) {
        return array(
            'model'   => 'dall-e-3',
            'prompt'  => 'Photorealistic image of ' . $search_term,
            'n'       => 1,
            'size'    => $img_size,
            'quality' => 'hd',
            'style'   => 'vivid',
        );
    }

    private function extract_image_url( array $item ) {
        if ( isset( $item['url'] ) && '' !== $item['url'] ) {
            return $item['url'];
        }
        return '';
    }

    private function download_image( $url, array $proxy_args ) {
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 45,
            'redirection'        => 5,
            'user-agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'reject_unsafe_urls' => false,
            'sslverify'          => false,
            'headers'            => array(
                'Accept' => 'image/*',
            ),
        ), $proxy_args );

        $response = wp_remote_request( $url, $request_args );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code  = wp_remote_retrieve_response_code( $response );
        $content_type = wp_remote_retrieve_header( $response, 'content-type' );

        if ( 200 !== intval( $status_code ) || ( is_string( $content_type ) && false !== strpos( $content_type, 'text/html' ) ) ) {
            return new WP_Error( 'asi_dalle_invalid_media', __( 'DALL·E returned an invalid media payload.', 'all-sources-images' ) );
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
