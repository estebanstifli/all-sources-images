<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ALLSI_Source_Gemini extends ALLSI_Image_Source {

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
        $post_id   = isset( $context['post_id'] ) ? intval( $context['post_id'] ) : 0;
        $using_cloudflare = $this->is_cloudflare_proxy_enabled( $context );

        if ( $log ) {
            $log->info( 'Gemini generation started', array(
                'post'             => $post_id,
                'model_requested'  => $model,
                'has_api_key'      => ! empty( $api_key ),
                'using_cloudflare' => $using_cloudflare,
                'prompt_length'    => strlen( trim( $prompt ) ),
            ) );
        }

        $allowed_models = $this->get_supported_model_slugs();
        if ( ! in_array( $model, $allowed_models, true ) ) {
            if ( $log ) {
                $log->warning( 'Gemini model unsupported for images, falling back to gemini-2.5-flash-image.', array( 'requested_model' => $model ) );
            }
            $model = 'gemini-2.5-flash-image';
        }

        if ( empty( $api_key ) && ! $using_cloudflare ) {
            if ( $log ) {
                $log->error( 'Gemini API key missing and Cloudflare fallback unavailable.', array(
                    'post' => $post_id,
                ) );
            }
            return new WP_Error( 'ALLSI_gemini_missing_key', __( 'Gemini API key is missing.', 'all-sources-images' ) );
        }

        if ( empty( $prompt ) ) {
            if ( $log ) {
                $log->error( 'Gemini prompt is empty.', array(
                    'post' => $post_id,
                ) );
            }
            return new WP_Error( 'ALLSI_gemini_missing_prompt', __( 'Gemini prompt is empty.', 'all-sources-images' ) );
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
        if ( $log ) {
            $log->info( 'Gemini request', array(
                'post'             => $post_id,
                'model'            => $model,
                'endpoint'         => $endpoint,
                'aspect'           => $aspect,
                'size'             => $imageSize,
                'prompt_length'    => strlen( trim( $prompt ) ),
                'using_cloudflare' => $using_cloudflare,
                'has_proxy_args'   => ! empty( $context['proxy_args'] ),
            ) );
        }

        $response = $this->request_with_proxy( 'gemini', $endpoint, $request_args, $context, 'POST' );

        if ( is_wp_error( $response ) ) {
            if ( $log ) {
                $log->error( 'Gemini transport error', array(
                    'post'          => $post_id,
                    'error_code'    => $response->get_error_code(),
                    'error_message' => $response->get_error_message(),
                    'error_data'    => $response->get_error_data(),
                ) );
            }
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $body        = json_decode( $body_raw, true );
        $candidate_summary = $this->summarize_candidates( $body );

        if ( $log ) {
            $log->info( 'Gemini response received', array(
                'post'            => $post_id,
                'status'          => $status_code,
                'body_length'     => strlen( (string) $body_raw ),
                'candidate_count' => $candidate_summary['count'],
            ) );
        }

        if ( null === $body && '' !== trim( (string) $body_raw ) ) {
            if ( $log ) {
                $log->error( 'Gemini response is not valid JSON', array(
                    'post'         => $post_id,
                    'body_preview' => substr( (string) $body_raw, 0, 800 ),
                ) );
            }
        }

        if ( 200 !== $status_code ) {
            $message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Unexpected Gemini API response.', 'all-sources-images' );

            if ( $log ) {
                $log->error( 'Gemini API returned non-200 status', array(
                    'post'          => $post_id,
                    'status'        => $status_code,
                    'error_message' => $message,
                    'body_preview'  => substr( (string) $body_raw, 0, 800 ),
                ) );
            }

            return new WP_Error( 'ALLSI_gemini_http_error', $message, array( 'status' => $status_code ) );
        }

        if ( isset( $body['promptFeedback']['blockReason'] ) && $body['promptFeedback']['blockReason'] ) {
            if ( $log ) {
                $log->warning( 'Gemini blocked prompt', array(
                    'post'         => $post_id,
                    'block_reason' => $body['promptFeedback']['blockReason'],
                    'feedback'     => isset( $body['promptFeedback'] ) ? $body['promptFeedback'] : array(),
                ) );
            }

            /* translators: %s: Block reason returned by the Gemini API. */
            return new WP_Error( 'ALLSI_gemini_blocked', sprintf( __( 'Gemini blocked the prompt: %s', 'all-sources-images' ), $body['promptFeedback']['blockReason'] ) );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            if ( $log ) {
                $log->info( 'Gemini get_only_thumb enabled, returning raw response.', array(
                    'post' => $post_id,
                ) );
            }
            return $body;
        }

        $image_part = $this->extract_first_image_part( $body );
        if ( ! $image_part ) {
            if ( $log ) {
                $log->error( 'Gemini did not return an inline image part', array(
                    'post'              => $post_id,
                    'candidate_count'   => $candidate_summary['count'],
                    'finish_reasons'    => $candidate_summary['finish_reasons'],
                    'parts_per_candidate' => $candidate_summary['parts_per_candidate'],
                    'body_preview'      => substr( (string) $body_raw, 0, 800 ),
                ) );
            }
            return new WP_Error( 'ALLSI_gemini_no_image', __( 'Gemini did not return an image.', 'all-sources-images' ) );
        }

        $inline_data = $this->extract_inline_data_payload( $image_part );
        $base64_data = isset( $inline_data['data'] ) ? $inline_data['data'] : '';
        $mime_type   = ! empty( $inline_data['mime_type'] ) ? $inline_data['mime_type'] : 'image/png';

        if ( '' === $base64_data ) {
            if ( $log ) {
                $log->error( 'Gemini image part found but data payload is empty', array(
                    'post'      => $post_id,
                    'part_keys' => is_array( $image_part ) ? array_keys( $image_part ) : array(),
                ) );
            }
            return new WP_Error( 'ALLSI_gemini_no_image_data', __( 'Gemini did not return image data.', 'all-sources-images' ) );
        }

        $binary      = base64_decode( $base64_data, true );

        if ( false === $binary ) {
            if ( $log ) {
                $log->error( 'Gemini image decode failed', array(
                    'post'          => $post_id,
                    'mime_type'     => $mime_type,
                    'base64_length' => strlen( (string) $base64_data ),
                ) );
            }
            return new WP_Error( 'ALLSI_gemini_decode_error', __( 'Unable to decode Gemini image data.', 'all-sources-images' ) );
        }

        if ( $log && isset( $body['usageMetadata'] ) ) {
            $log->info( 'Gemini usage metadata', array( 'usage' => $body['usageMetadata'] ) );
        }

        if ( $log ) {
            $log->info( 'Gemini image generation completed', array(
                'post'       => $post_id,
                'mime_type'  => $mime_type,
                'image_size' => strlen( $binary ),
            ) );
        }

        $alt_text = ALLSI_Source_Text_Helper::build_alt_text( $global_options, $prompt, $source_label, $translator );
        $caption_text = ALLSI_Source_Text_Helper::build_caption( $global_options, '', $source_label );

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

                $inline_data = $this->extract_inline_data_payload( $part );
                if ( ! empty( $inline_data['data'] ) ) {
                    return $part;
                }
            }
        }

        return null;
    }

    private function extract_inline_data_payload( $part ) {
        $payload = array();

        if ( isset( $part['inline_data'] ) && is_array( $part['inline_data'] ) ) {
            $payload = $part['inline_data'];
        } elseif ( isset( $part['inlineData'] ) && is_array( $part['inlineData'] ) ) {
            $payload = $part['inlineData'];
        }

        if ( isset( $payload['mimeType'] ) && empty( $payload['mime_type'] ) ) {
            $payload['mime_type'] = $payload['mimeType'];
        }

        return is_array( $payload ) ? $payload : array();
    }

    /**
     * Build a lightweight summary of Gemini candidates for diagnostics.
     *
     * @param array|null $response_body
     *
     * @return array
     */
    private function summarize_candidates( $response_body ) {
        $summary = array(
            'count'               => 0,
            'finish_reasons'      => array(),
            'parts_per_candidate' => array(),
        );

        if ( empty( $response_body['candidates'] ) || ! is_array( $response_body['candidates'] ) ) {
            return $summary;
        }

        $summary['count'] = count( $response_body['candidates'] );

        foreach ( $response_body['candidates'] as $candidate ) {
            if ( isset( $candidate['finishReason'] ) ) {
                $summary['finish_reasons'][] = $candidate['finishReason'];
            }

            if ( isset( $candidate['content']['parts'] ) && is_array( $candidate['content']['parts'] ) ) {
                $summary['parts_per_candidate'][] = count( $candidate['content']['parts'] );
            } else {
                $summary['parts_per_candidate'][] = 0;
            }
        }

        $summary['finish_reasons'] = array_values( array_unique( array_filter( $summary['finish_reasons'] ) ) );

        return $summary;
    }

    /**
     * List of Gemini models that can return inline images.
     */
    private function get_supported_model_slugs() {
        $models = apply_filters(
            'ALLSI_gemini_supported_models',
            array(
                'gemini-2.5-flash-image'         => __( 'Gemini 2.5 Flash Image', 'all-sources-images' ),
                'gemini-3.1-flash-image-preview' => __( 'Gemini 3.1 Flash Image Preview', 'all-sources-images' ),
                'gemini-3-pro-image-preview'     => __( 'Gemini 3 Pro Image Preview', 'all-sources-images' ),
            )
        );

        return array_keys( (array) $models );
    }

    private function get_translator_callable( array $context ) {
        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ALLSI_translate_text' ) ) {
            return array( $context['generation'], 'ALLSI_translate_text' );
        }

        return null;
    }
}
