<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Stability extends ASI_Image_Source {

    private const BASE_ENDPOINT = 'https://api.stability.ai/v2beta/stable-image/generate/';
    private const NEGATIVE_PROMPT = 'blurry, low quality, distorted, disfigured, ugly, low resolution';

    public function get_slug() {
        return 'stability';
    }

    public function generate( array $context ) {
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $bank_options   = isset( $global_options['stability'] ) && is_array( $global_options['stability'] ) ? $global_options['stability'] : array();
        $api_key        = isset( $bank_options['apikey'] ) ? trim( $bank_options['apikey'] ) : '';
        $search_term    = $this->resolve_search_term( $context );
        $proxy_args     = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        $log            = isset( $context['log'] ) ? $context['log'] : null;
        $translator     = $this->get_translator_callable( $context );
        $source_label   = __( 'Stability AI', 'all-sources-images' );
        $using_cloudflare = $this->is_cloudflare_proxy_enabled( $context );

        if ( '' === $api_key && ! $using_cloudflare ) {
            return new WP_Error( 'asi_stability_missing_key', __( 'Stability AI API key is missing.', 'all-sources-images' ) );
        }

        if ( '' === trim( $search_term ) ) {
            return new WP_Error( 'asi_stability_missing_prompt', __( 'No search prompt available for Stability AI.', 'all-sources-images' ) );
        }

        list( $endpoint, $payload ) = $this->build_payload( $bank_options, $search_term );
        $boundary = 'ASI-' . wp_generate_password( 24, false );
        $body     = $this->build_multipart_body( $payload, $boundary );
        $mime     = $this->map_mime_type( isset( $payload['output_format'] ) ? $payload['output_format'] : 'jpeg' );

        $headers = array(
            'Accept'       => 'image/*',
            'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
        );
        if ( ! empty( $api_key ) ) {
            $headers['Authorization'] = 'Bearer ' . $api_key;
        }

        $request_args = $this->merge_proxy_args( array(
            'timeout' => 60,
            'method'  => 'POST',
            'headers' => $headers,
            'body'    => $body,
        ), $proxy_args );

        $response = $this->request_with_proxy( 'stability', self::BASE_ENDPOINT . $endpoint, $request_args, $context, 'POST' );

        if ( $log ) {
            $log->info( 'Stability request', array(
                'post'   => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'model'  => isset( $payload['model'] ) ? $payload['model'] : 'sd3-large',
                'ratio'  => isset( $payload['aspect_ratio'] ) ? $payload['aspect_ratio'] : '1:1',
            ) );
        }

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $binary_body = wp_remote_retrieve_body( $response );

        if ( 200 !== intval( $status_code ) ) {
            $decoded = json_decode( $binary_body, true );
            $message = isset( $decoded['message'] ) ? $decoded['message'] : __( 'Unexpected Stability AI response.', 'all-sources-images' );
            return new WP_Error( 'asi_stability_http_error', $message, array(
                'status' => $status_code,
            ) );
        }

        $base64 = base64_encode( $binary_body );
        if ( false === $base64 ) {
            return new WP_Error( 'asi_stability_encode_error', __( 'Unable to encode Stability AI image.', 'all-sources-images' ) );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return array(
                'image' => $base64,
                'mime'  => $mime,
            );
        }

        $alt_text = ASI_Source_Text_Helper::build_alt_text( $global_options, $search_term, $source_label, $translator );
        $caption  = ASI_Source_Text_Helper::build_caption( $global_options, __( 'Generated with Stability AI', 'all-sources-images' ), $source_label );

        return array(
            'url_results'  => 'data:' . $mime . ';base64,' . $base64,
            'file_media'   => $this->build_memory_response( $binary_body, $mime ),
            'alt_img'      => $alt_text,
            'caption_img'  => $caption,
            'raw_response' => array(
                'image' => $base64,
                'mime'  => $mime,
            ),
        );
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

    private function build_payload( array $bank_options, $search_term ) {
        $model         = ! empty( $bank_options['model'] ) ? $bank_options['model'] : 'sd3-large';
        $aspect_ratio  = ! empty( $bank_options['aspect_ratio'] ) ? $bank_options['aspect_ratio'] : '16:9';
        $output_format = ! empty( $bank_options['output_format'] ) ? $bank_options['output_format'] : 'jpeg';
        $use_negative  = ! empty( $bank_options['use_negative_prompt'] ) && 'true' === $bank_options['use_negative_prompt'];

        $endpoint_map = array(
            'sd3-large'         => 'sd3',
            'sd3-large-turbo'   => 'sd3-turbo',
            'sd3-medium'        => 'sd3',
            'sd3.5-large-turbo' => 'sd3',
            'sd3.5-large'       => 'sd3',
            'core'              => 'core',
            'ultra'             => 'ultra',
        );
        $endpoint = isset( $endpoint_map[ $model ] ) ? $endpoint_map[ $model ] : 'sd3';

        $payload = array(
            'prompt'        => $search_term,
            'model'         => $model,
            'aspect_ratio'  => $aspect_ratio,
            'output_format' => $output_format,
        );

        if ( in_array( $endpoint, array( 'core', 'ultra' ), true ) ) {
            $dimensions = $this->map_dimensions( $aspect_ratio );
            $payload['width']  = $dimensions['width'];
            $payload['height'] = $dimensions['height'];
        }

        if ( $use_negative ) {
            $payload['negative_prompt'] = self::NEGATIVE_PROMPT;
        }

        return array( $endpoint, $payload );
    }

    private function build_multipart_body( array $payload, $boundary ) {
        $body = '';
        foreach ( $payload as $field => $value ) {
            if ( '' === $value && '0' !== $value ) {
                continue;
            }
            $body .= '--' . $boundary . "\r\n";
            $body .= 'Content-Disposition: form-data; name="' . $field . '"' . "\r\n\r\n";
            $body .= $value . "\r\n";
        }
        $body .= '--' . $boundary . "--\r\n";
        return $body;
    }

    private function map_dimensions( $aspect_ratio ) {
        $map = array(
            '21:9' => array( 'width' => 2560, 'height' => 1097 ),
            '16:9' => array( 'width' => 2560, 'height' => 1440 ),
            '3:2'  => array( 'width' => 2304, 'height' => 1536 ),
            '5:4'  => array( 'width' => 2048, 'height' => 1638 ),
            '1:1'  => array( 'width' => 2048, 'height' => 2048 ),
            '4:5'  => array( 'width' => 1638, 'height' => 2048 ),
            '2:3'  => array( 'width' => 1536, 'height' => 2304 ),
            '9:16' => array( 'width' => 1440, 'height' => 2560 ),
            '9:21' => array( 'width' => 1097, 'height' => 2560 ),
        );

        return isset( $map[ $aspect_ratio ] ) ? $map[ $aspect_ratio ] : $map['16:9'];
    }

    private function map_mime_type( $format ) {
        $format = strtolower( $format );
        $map    = array(
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
        );
        return isset( $map[ $format ] ) ? $map[ $format ] : 'image/jpeg';
    }

    private function get_translator_callable( array $context ) {
        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ASI_translate_text' ) ) {
            return array( $context['generation'], 'ASI_translate_text' );
        }

        return null;
    }
}
