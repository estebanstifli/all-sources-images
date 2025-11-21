<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Workers_AI extends ASI_Image_Source {

    public function get_slug() {
        return 'workers_ai';
    }

    public function generate( array $context ) {
        $global_options  = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $options         = isset( $global_options['workers_ai'] ) ? $global_options['workers_ai'] : array();
        $account_id      = isset( $options['account_id'] ) ? trim( $options['account_id'] ) : '';
        $api_token       = isset( $options['api_token'] ) ? trim( $options['api_token'] ) : '';
        $model           = isset( $options['model'] ) ? trim( $options['model'] ) : '@cf/black-forest-labs/flux-1-schnell';
        $steps           = isset( $options['steps'] ) ? absint( $options['steps'] ) : 4;
        $negative_prompt = isset( $options['negative_prompt'] ) ? trim( $options['negative_prompt'] ) : '';
        $prompt          = isset( $context['search'] ) ? $context['search'] : '';
        $log             = isset( $context['log'] ) ? $context['log'] : null;
        $proxy_args      = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();

        if ( empty( $account_id ) ) {
            return new WP_Error( 'asi_workers_ai_missing_account', __( 'Cloudflare Account ID is missing.', 'all-sources-images' ) );
        }
        if ( empty( $api_token ) ) {
            return new WP_Error( 'asi_workers_ai_missing_token', __( 'Cloudflare API token is missing.', 'all-sources-images' ) );
        }
        if ( empty( $prompt ) ) {
            return new WP_Error( 'asi_workers_ai_missing_prompt', __( 'Workers AI prompt is empty.', 'all-sources-images' ) );
        }

        if ( $steps < 1 ) {
            $steps = 1;
        }
        if ( $steps > 8 ) {
            $steps = 8;
        }

        $model_slug = $this->sanitize_model_slug( $model );
        if ( empty( $model_slug ) ) {
            $model_slug = '@cf/black-forest-labs/flux-1-schnell';
        }

        $payload = array(
            'prompt' => $prompt,
            'steps'  => $steps,
        );
        if ( ! empty( $negative_prompt ) ) {
            $payload['negative_prompt'] = $negative_prompt;
        }

        $request_args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode( $payload ),
            'timeout' => 120,
        );

        $translator = $this->get_translator_callable( $context );
        $source_label = __( 'Cloudflare Workers AI', 'all-sources-images' );

        if ( isset( $proxy_args['headers'] ) && is_array( $proxy_args['headers'] ) ) {
            $request_args['headers'] = array_merge( $request_args['headers'], $proxy_args['headers'] );
            unset( $proxy_args['headers'] );
        }
        if ( ! empty( $proxy_args ) ) {
            $request_args = array_merge( $request_args, $proxy_args );
        }

        $endpoint = sprintf( 'https://api.cloudflare.com/client/v4/accounts/%s/ai/run/%s', rawurlencode( $account_id ), ltrim( $model_slug, '/' ) );
        $response = wp_remote_post( $endpoint, $request_args );

        if ( $log ) {
            $log->info( 'Workers AI request', array(
                'post'   => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'model'  => $model_slug,
                'steps'  => $steps,
            ) );
        }

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $body        = json_decode( $body_raw, true );

        if ( 200 !== $status_code || ( isset( $body['success'] ) && true !== $body['success'] ) ) {
            $message = $this->extract_error_message( $body );
            if ( empty( $message ) ) {
                $message = __( 'Unexpected Workers AI response.', 'all-sources-images' );
            }
            return new WP_Error( 'asi_workers_ai_http_error', $message, array( 'status' => $status_code ) );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return $body;
        }

        $image_data = $this->extract_image_payload( $body );
        if ( ! $image_data ) {
            return new WP_Error( 'asi_workers_ai_no_image', __( 'Workers AI did not return an image.', 'all-sources-images' ) );
        }

        list( $base64, $mime ) = $image_data;
        $binary = base64_decode( $base64 );

        if ( false === $binary ) {
            return new WP_Error( 'asi_workers_ai_decode_error', __( 'Unable to decode Workers AI image data.', 'all-sources-images' ) );
        }

        $alt_text = ASI_Source_Text_Helper::build_alt_text( $global_options, $prompt, $source_label, $translator );
        $caption_text = ASI_Source_Text_Helper::build_caption( $global_options, __( 'Generated with Cloudflare Workers AI', 'all-sources-images' ), $source_label );

        return array(
            'url_results'  => 'data:' . $mime . ';base64,' . $base64,
            'file_media'   => $this->build_memory_response( $binary, $mime ),
            'alt_img'      => $alt_text,
            'caption_img'  => $caption_text,
            'raw_response' => $body,
        );
    }

    private function sanitize_model_slug( $model ) {
        $model = trim( $model );
        $model = preg_replace( '#[^@a-zA-Z0-9._/\-]#', '', $model );
        return $model;
    }

    private function extract_error_message( $body ) {
        if ( isset( $body['errors'][0]['message'] ) ) {
            return $body['errors'][0]['message'];
        }
        if ( isset( $body['messages'][0] ) && is_string( $body['messages'][0] ) ) {
            return $body['messages'][0];
        }
        return '';
    }

    private function extract_image_payload( $body ) {
        if ( ! is_array( $body ) ) {
            return null;
        }

        $image_string = '';
        if ( isset( $body['result']['image'] ) && is_string( $body['result']['image'] ) ) {
            $image_string = $body['result']['image'];
        } elseif ( isset( $body['result']['images'][0] ) && is_string( $body['result']['images'][0] ) ) {
            $image_string = $body['result']['images'][0];
        } elseif ( isset( $body['result']['output'][0] ) && is_string( $body['result']['output'][0] ) ) {
            $image_string = $body['result']['output'][0];
        }

        if ( '' === $image_string ) {
            return null;
        }

        $mime = 'image/png';
        if ( strpos( $image_string, 'data:' ) === 0 ) {
            if ( preg_match( '/^data:([^;]+);base64,/', $image_string, $matches ) ) {
                $mime = $matches[1];
            }
            $image_string = substr( $image_string, strpos( $image_string, ',' ) + 1 );
        }

        return array( $image_string, $mime );
    }

    private function get_translator_callable( array $context ) {
        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ASI_translate_text' ) ) {
            return array( $context['generation'], 'ASI_translate_text' );
        }

        return null;
    }
}
