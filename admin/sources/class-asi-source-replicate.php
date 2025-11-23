<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Replicate extends ASI_Image_Source {

    private const API_ENDPOINT = 'https://api.replicate.com/v1/predictions';
    private const DEFAULT_TIMEOUT = 120;
    private const NEGATIVE_PROMPT = 'blurry, low quality, distorted, disfigured, ugly, low resolution, watermark';

    public function get_slug() {
        return 'replicate';
    }

    public function generate( array $context ) {
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $bank_options   = isset( $global_options['replicate'] ) && is_array( $global_options['replicate'] ) ? $global_options['replicate'] : array();
        $api_token      = $this->resolve_api_token( $bank_options );
        $search_term    = $this->resolve_search_term( $context );
        $proxy_args     = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        $selected_image = isset( $context['selected_image'] ) ? $context['selected_image'] : 'first_result';
        $log            = isset( $context['log'] ) ? $context['log'] : null;
        $translator     = $this->get_translator_callable( $context );
        $source_label   = __( 'Replicate', 'all-sources-images' );
        $using_cloudflare = $this->is_cloudflare_proxy_enabled( $context );

        if ( '' === $api_token && ! $using_cloudflare ) {
            return new WP_Error( 'asi_replicate_missing_token', __( 'Replicate API token is missing.', 'all-sources-images' ) );
        }

        if ( '' === trim( $search_term ) ) {
            return new WP_Error( 'asi_replicate_missing_prompt', __( 'No search prompt available for Replicate.', 'all-sources-images' ) );
        }

        $model            = ! empty( $bank_options['model'] ) ? $bank_options['model'] : 'black-forest-labs/flux-schnell';
        $version_setting  = isset( $bank_options['version'] ) ? trim( $bank_options['version'] ) : '';
        $timeout          = isset( $bank_options['timeout'] ) ? absint( $bank_options['timeout'] ) : self::DEFAULT_TIMEOUT;
        $polling_interval = isset( $bank_options['polling_interval'] ) ? absint( $bank_options['polling_interval'] ) : 2;
        $timeout          = max( 30, min( 300, $timeout ) );
        $polling_interval = max( 1, min( 10, $polling_interval ) );

        $version_id = $this->resolve_version_id( $model, $version_setting, $api_token, $proxy_args, $context );
        if ( is_wp_error( $version_id ) ) {
            return $version_id;
        }

        $input = $this->build_input_payload( $bank_options, $search_term );
        if ( empty( $input ) ) {
            return new WP_Error( 'asi_replicate_input_error', __( 'Unable to build Replicate payload.', 'all-sources-images' ) );
        }

        $request_body = array(
            'version' => $version_id,
            'input'   => $input,
        );

        if ( ! empty( $bank_options['webhook_url'] ) ) {
            $request_body['webhook'] = esc_url_raw( $bank_options['webhook_url'] );
        }

        $headers = array(
            'Content-Type' => 'application/json',
        );
        if ( ! empty( $api_token ) ) {
            $headers['Authorization'] = 'Token ' . $api_token;
        }

        $request_args = $this->merge_proxy_args( array(
            'timeout' => $timeout,
            'headers' => $headers,
            'body'    => wp_json_encode( $request_body ),
        ), $proxy_args );

        $response = $this->request_with_proxy( 'replicate', self::API_ENDPOINT, $request_args, $context, 'POST' );

        if ( $log ) {
            $log->info( 'Replicate prediction created', array(
                'post'  => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'model' => $model,
            ) );
        }

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $payload     = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 201 !== intval( $status_code ) || ! is_array( $payload ) ) {
            $message = isset( $payload['detail'] ) ? $payload['detail'] : __( 'Unexpected Replicate response.', 'all-sources-images' );
            return new WP_Error( 'asi_replicate_http_error', $message, array( 'status' => $status_code ) );
        }

        $prediction = $this->poll_prediction( $payload, $api_token, $proxy_args, $timeout, $polling_interval, $log, $context );
        if ( is_wp_error( $prediction ) ) {
            return $prediction;
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return $prediction;
        }

        $outputs = isset( $prediction['output'] ) ? (array) $prediction['output'] : array();
        if ( empty( $outputs ) ) {
            return new WP_Error( 'asi_replicate_no_output', __( 'Replicate did not return any images.', 'all-sources-images' ) );
        }

        if ( 'random_result' === $selected_image ) {
            shuffle( $outputs );
        }

        foreach ( $outputs as $image_url ) {
            if ( ! is_string( $image_url ) || '' === $image_url ) {
                continue;
            }
            $file_media = $this->download_image( $image_url, $proxy_args );
            if ( is_wp_error( $file_media ) ) {
                continue;
            }

            $alt_text = ASI_Source_Text_Helper::build_alt_text( $global_options, $search_term, $source_label, $translator );
            $caption  = ASI_Source_Text_Helper::build_caption( $global_options, __( 'Generated with Replicate', 'all-sources-images' ), $source_label );

            return array(
                'url_results'  => $image_url,
                'file_media'   => $file_media,
                'alt_img'      => $alt_text,
                'caption_img'  => $caption,
                'raw_response' => $prediction,
            );
        }

        return new WP_Error( 'asi_replicate_download_failed', __( 'Unable to download a valid Replicate image.', 'all-sources-images' ) );
    }

    private function resolve_api_token( array $bank_options ) {
        if ( ! empty( $bank_options['apitoken'] ) ) {
            return trim( $bank_options['apitoken'] );
        }
        if ( ! empty( $bank_options['apikey'] ) ) {
            return trim( $bank_options['apikey'] );
        }
        return '';
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

    private function build_input_payload( array $bank_options, $search_term ) {
        $output_format = ! empty( $bank_options['output_format'] ) ? $bank_options['output_format'] : 'webp';
        $aspect_ratio  = ! empty( $bank_options['aspect_ratio'] ) ? $bank_options['aspect_ratio'] : '16:9';
        $use_negative  = ! empty( $bank_options['use_negative_prompt'] ) && 'true' === $bank_options['use_negative_prompt'];

        $payload = array(
            'prompt'        => $search_term,
            'output_format' => $output_format,
            'aspect_ratio'  => $aspect_ratio,
        );

        if ( $use_negative ) {
            $payload['negative_prompt'] = self::NEGATIVE_PROMPT;
        }

        return $payload;
    }

    private function resolve_version_id( $model, $version_setting, $api_token, array $proxy_args, array $context ) {
        $version_setting = trim( $version_setting );
        if ( '' !== $version_setting && 0 !== strcasecmp( $version_setting, 'latest' ) ) {
            return $version_setting;
        }

        if ( 'custom' === $model && '' === $version_setting ) {
            return new WP_Error( 'asi_replicate_custom_version', __( 'Provide a version ID for your custom Replicate model.', 'all-sources-images' ) );
        }

        if ( false === strpos( $model, '/' ) ) {
            return new WP_Error( 'asi_replicate_invalid_model', __( 'Replicate model should follow owner/model format.', 'all-sources-images' ) );
        }

        list( $owner, $slug ) = explode( '/', $model, 2 );
        $owner = trim( $owner );
        $slug  = trim( $slug );
        if ( '' === $owner || '' === $slug ) {
            return new WP_Error( 'asi_replicate_invalid_model', __( 'Replicate model should follow owner/model format.', 'all-sources-images' ) );
        }

        $endpoint = sprintf( 'https://api.replicate.com/v1/models/%s/%s', rawurlencode( $owner ), rawurlencode( $slug ) );
        $headers = array(
            'Content-Type' => 'application/json',
        );
        if ( ! empty( $api_token ) ) {
            $headers['Authorization'] = 'Token ' . $api_token;
        }

        $request_args = $this->merge_proxy_args( array(
            'timeout' => 20,
            'headers' => $headers,
        ), $proxy_args );

        $response = $this->request_with_proxy( 'replicate', $endpoint, $request_args, $context );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $payload     = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 !== intval( $status_code ) || ! isset( $payload['latest_version']['id'] ) ) {
            $message = isset( $payload['detail'] ) ? $payload['detail'] : __( 'Unable to determine latest Replicate version.', 'all-sources-images' );
            return new WP_Error( 'asi_replicate_version_lookup', $message, array( 'status' => $status_code ) );
        }

        return $payload['latest_version']['id'];
    }

    private function poll_prediction( array $prediction, $api_token, array $proxy_args, $timeout, $interval, $log, array $context ) {
        if ( empty( $prediction['urls']['get'] ) ) {
            return new WP_Error( 'asi_replicate_missing_poll_url', __( 'Replicate prediction is missing the polling URL.', 'all-sources-images' ) );
        }

        $status   = isset( $prediction['status'] ) ? $prediction['status'] : '';
        $deadline = time() + $timeout;
        $current  = $prediction;

        while ( time() < $deadline && in_array( $status, array( 'starting', 'processing', 'queued' ), true ) ) {
            sleep( $interval );
            $headers = array(
                'Content-Type' => 'application/json',
            );
            if ( ! empty( $api_token ) ) {
                $headers['Authorization'] = 'Token ' . $api_token;
            }

            $request_args = $this->merge_proxy_args( array(
                'timeout' => 20,
                'headers' => $headers,
            ), $proxy_args );

            $response = $this->request_with_proxy( 'replicate', $prediction['urls']['get'], $request_args, $context );
            if ( is_wp_error( $response ) ) {
                return $response;
            }

            $payload = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! is_array( $payload ) ) {
                return new WP_Error( 'asi_replicate_poll_error', __( 'Invalid response while polling Replicate.', 'all-sources-images' ) );
            }

            $current = $payload;
            $status  = isset( $current['status'] ) ? $current['status'] : '';
            if ( $log ) {
                $log->info( 'Replicate polling update', array(
                    'status' => $status,
                    'id'     => isset( $current['id'] ) ? $current['id'] : '',
                ) );
            }
        }

        if ( in_array( $status, array( 'starting', 'processing', 'queued' ), true ) ) {
            return new WP_Error( 'asi_replicate_timeout', __( 'Replicate prediction timed out.', 'all-sources-images' ) );
        }

        if ( 'succeeded' !== $status ) {
            $message = isset( $current['error'] ) ? $current['error'] : __( 'Replicate prediction failed.', 'all-sources-images' );
            return new WP_Error( 'asi_replicate_failed', $message );
        }

        return $current;
    }

    private function download_image( $url, array $proxy_args ) {
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 45,
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
            return new WP_Error( 'asi_replicate_invalid_media', __( 'Replicate returned an invalid media payload.', 'all-sources-images' ) );
        }

        return $response;
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
