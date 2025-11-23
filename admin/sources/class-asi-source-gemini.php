<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Gemini extends ASI_Image_Source {

    public function get_slug() {
        return 'gemini';
    }

    public function generate( array $context ) {
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $options   = isset( $global_options['gemini'] ) ? $global_options['gemini'] : array();
        $api_key   = isset( $options['apikey'] ) ? trim( $options['apikey'] ) : '';
        $model     = ! empty( $options['model'] ) ? $options['model'] : 'gemini-2.5-flash-image';
        $aspect    = ! empty( $options['aspect_ratio'] ) ? $options['aspect_ratio'] : '';
        $imageSize = ! empty( $options['image_size'] ) ? strtoupper( $options['image_size'] ) : '';
        $prompt    = isset( $context['search'] ) ? $context['search'] : '';
        $log       = isset( $context['log'] ) ? $context['log'] : null;
        $using_cloudflare = $this->is_cloudflare_proxy_enabled( $context );

        $allowed_models = $this->get_supported_model_slugs();
        if ( ! in_array( $model, $allowed_models, true ) ) {
            if ( $log ) {
                $log->warning( 'Gemini model unsupported for images, falling back to gemini-2.5-flash-image.', array( 'requested_model' => $model ) );
            }
            $model = 'gemini-2.5-flash-image';
        }

        if ( empty( $api_key ) && ! $using_cloudflare ) {
            return new WP_Error( 'asi_gemini_missing_key', __( 'Gemini API key is missing.', 'all-sources-images' ) );
        }

        if ( empty( $prompt ) ) {
            return new WP_Error( 'asi_gemini_missing_prompt', __( 'Gemini prompt is empty.', 'all-sources-images' ) );
        }

        $payload = array(
            'contents'          => array(
                array(
                    'parts' => array(
                        array( 'text' => $prompt ),
                    ),
                ),
            ),
            'generationConfig'  => array(
                'responseModalities' => array( 'IMAGE' ),
            ),
        );

        if ( $aspect || $imageSize ) {
            $payload['generationConfig']['imageConfig'] = array();
            if ( $aspect ) {
                $payload['generationConfig']['imageConfig']['aspectRatio'] = $aspect;
            }
            if ( $imageSize ) {
                $payload['generationConfig']['imageConfig']['imageSize'] = $imageSize;
            }
        }

        $headers = array(
            'Content-Type' => 'application/json',
        );
        if ( ! empty( $api_key ) ) {
            $headers['x-goog-api-key'] = $api_key;
        }

        $request_args = array(
            'headers' => $headers,
            'body'    => wp_json_encode( $payload ),
            'timeout' => 120,
        );

        $translator = $this->get_translator_callable( $context );
        $source_label = __( 'Gemini', 'all-sources-images' );

        if ( ! empty( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ) {
            $proxy_args = $context['proxy_args'];
            if ( isset( $proxy_args['headers'] ) && is_array( $proxy_args['headers'] ) ) {
                $request_args['headers'] = array_merge( $request_args['headers'], $proxy_args['headers'] );
                unset( $proxy_args['headers'] );
            }
            $request_args = array_merge( $request_args, $proxy_args );
        }

        $endpoint = sprintf( 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent', $model );
        $response = $this->request_with_proxy( 'gemini', $endpoint, $request_args, $context, 'POST' );

        if ( $log ) {
            $log->info( 'Gemini request', array(
                'post'   => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'model'  => $model,
                'aspect' => $aspect,
                'size'   => $imageSize,
            ) );
        }

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $body        = json_decode( $body_raw, true );

        if ( 200 !== $status_code ) {
            $message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Unexpected Gemini API response.', 'all-sources-images' );
            return new WP_Error( 'asi_gemini_http_error', $message, array( 'status' => $status_code ) );
        }

        if ( isset( $body['promptFeedback']['blockReason'] ) && $body['promptFeedback']['blockReason'] ) {
            return new WP_Error( 'asi_gemini_blocked', sprintf( __( 'Gemini blocked the prompt: %s', 'all-sources-images' ), $body['promptFeedback']['blockReason'] ) );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return $body;
        }

        $image_part = $this->extract_first_image_part( $body );
        if ( ! $image_part ) {
            return new WP_Error( 'asi_gemini_no_image', __( 'Gemini did not return an image.', 'all-sources-images' ) );
        }

        $base64_data = isset( $image_part['inline_data']['data'] ) ? $image_part['inline_data']['data'] : '';
        $mime_type   = isset( $image_part['inline_data']['mime_type'] ) ? $image_part['inline_data']['mime_type'] : 'image/png';
        $binary      = base64_decode( $base64_data );

        if ( false === $binary ) {
            return new WP_Error( 'asi_gemini_decode_error', __( 'Unable to decode Gemini image data.', 'all-sources-images' ) );
        }

        if ( $log && isset( $body['usageMetadata'] ) ) {
            $log->info( 'Gemini usage metadata', array( 'usage' => $body['usageMetadata'] ) );
        }

        $alt_text = ASI_Source_Text_Helper::build_alt_text( $global_options, $prompt, $source_label, $translator );
        $caption_text = ASI_Source_Text_Helper::build_caption( $global_options, '', $source_label );

        return array(
            'url_results' => 'data:' . $mime_type . ';base64,' . $base64_data,
            'file_media'  => $this->build_memory_response( $binary, $mime_type ),
            'alt_img'     => $alt_text,
            'caption_img' => $caption_text,
            'raw_response' => $body,
        );
    }

    private function extract_first_image_part( $response_body ) {
        if ( empty( $response_body['candidates'] ) ) {
            return null;
        }

        foreach ( $response_body['candidates'] as $candidate ) {
            if ( empty( $candidate['content']['parts'] ) ) {
                continue;
            }
            foreach ( $candidate['content']['parts'] as $part ) {
                if ( isset( $part['thought'] ) && true === $part['thought'] ) {
                    continue;
                }
                if ( isset( $part['inline_data']['data'] ) ) {
                    return $part;
                }
            }
        }

        return null;
    }

    /**
     * List of Gemini models that can return inline images.
     */
    private function get_supported_model_slugs() {
        $models = apply_filters(
            'asi_gemini_supported_models',
            array(
                'gemini-2.5-flash-image'         => __( 'Gemini 2.5 Flash Image', 'all-sources-images' ),
                'gemini-2.5-flash-preview-image' => __( 'Gemini 2.5 Flash Image Preview', 'all-sources-images' ),
                'gemini-3-pro-image-preview'     => __( 'Gemini 3 Pro Image Preview', 'all-sources-images' ),
            )
        );

        return array_keys( (array) $models );
    }

    private function get_translator_callable( array $context ) {
        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ASI_translate_text' ) ) {
            return array( $context['generation'], 'ASI_translate_text' );
        }

        return null;
    }
}
