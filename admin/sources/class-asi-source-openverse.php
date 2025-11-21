<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Openverse extends ASI_Image_Source {

    /** @var string */
    private $slug;

    public function __construct( $slug = 'openverse' ) {
        $this->slug = $slug;
    }

    public function get_slug() {
        return $this->slug;
    }

    public function generate( array $context ) {
        $global_options = isset( $context['options'] ) && is_array( $context['options'] ) ? $context['options'] : array();
        $bank_options   = isset( $global_options['cc_search'] ) && is_array( $global_options['cc_search'] ) ? $global_options['cc_search'] : array();
        $search_term    = $this->resolve_search_term( $context );
        $proxy_args     = isset( $context['proxy_args'] ) && is_array( $context['proxy_args'] ) ? $context['proxy_args'] : array();
        $selected_image = isset( $context['selected_image'] ) ? $context['selected_image'] : 'first_result';
        $log            = isset( $context['log'] ) ? $context['log'] : null;

        if ( '' === trim( $search_term ) ) {
            return new WP_Error( 'asi_openverse_missing_query', __( 'No search query available for Openverse.', 'all-sources-images' ) );
        }

        $endpoint    = 'https://api.openverse.engineering/v1/images/';
        $query_args  = $this->build_query_args( $bank_options, $search_term );
        $request_url = add_query_arg( $query_args, $endpoint );
        $request_args = $this->merge_proxy_args( array(
            'timeout'            => 30,
            'method'             => 'GET',
            'redirection'        => 9,
            'user-agent'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'reject_unsafe_urls' => false,
            'sslverify'          => false,
        ), $proxy_args );

        $response = wp_remote_request( $request_url, $request_args );

        if ( $log ) {
            $log->info( 'Openverse request', array(
                'post'  => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'query' => $search_term,
                'url'   => $request_url,
                'slug'  => $this->slug,
            ) );
        }

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );

        $this->log_raw_response_to_debug( $status_code, $body_raw, array(
            'post_id' => isset( $context['post_id'] ) ? $context['post_id'] : 0,
            'query'   => $search_term,
            'slug'    => $this->slug,
        ) );

        if ( $log ) {
            $log->debug( 'Openverse raw response', array(
                'post'   => isset( $context['post_id'] ) ? $context['post_id'] : 0,
                'status' => $status_code,
                'body'   => $body_raw,
                'slug'   => $this->slug,
            ) );
        }

        $payload = json_decode( $body_raw, true );

        if ( 200 !== intval( $status_code ) || ! is_array( $payload ) ) {
            return new WP_Error(
                'asi_openverse_http_error',
                __( 'Unexpected response from Openverse.', 'all-sources-images' ),
                array(
                    'status' => $status_code,
                    'body'   => $body_raw,
                )
            );
        }

        if ( ! empty( $context['get_only_thumb'] ) ) {
            return $payload;
        }

        $results = isset( $payload['results'] ) && is_array( $payload['results'] ) ? $payload['results'] : array();
        if ( empty( $results ) ) {
            return new WP_Error( 'asi_openverse_no_results', __( 'Openverse returned no photos.', 'all-sources-images' ), array( 'raw' => $payload ) );
        }

        if ( 'random_result' === $selected_image ) {
            shuffle( $results );
        }

        $translator   = $this->get_translator_callable( $context );
        $source_label = $this->get_source_label();

        foreach ( $results as $result ) {
            $image_url = isset( $result['url'] ) ? $result['url'] : '';
            if ( empty( $image_url ) ) {
                continue;
            }

            $file_media = $this->download_image( $image_url, $proxy_args );
            if ( is_wp_error( $file_media ) ) {
                continue;
            }

            $author_name = isset( $result['creator'] ) ? $result['creator'] : '';
            $alt_text = ASI_Source_Text_Helper::build_alt_text( $global_options, $search_term, $source_label, $translator );
            $caption_text = ASI_Source_Text_Helper::build_caption( $global_options, $author_name, $source_label );

            return array(
                'url_results' => $image_url,
                'file_media'  => $file_media,
                'alt_img'     => $alt_text,
                'caption_img' => $caption_text,
                'raw_response'=> $payload,
            );
        }

        return new WP_Error( 'asi_openverse_download_failed', __( 'Unable to download a valid Openverse image.', 'all-sources-images' ), array( 'raw' => $payload ) );
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

    private function build_query_args( array $bank_options, $search_term ) {
        $imgtype      = ! empty( $bank_options['imgtype'] ) ? $bank_options['imgtype'] : '';
        $aspect_ratio = ! empty( $bank_options['aspect_ratio'] ) ? $bank_options['aspect_ratio'] : '';
        $sources      = $this->get_source_list();

        $args = array(
            'q'         => $search_term,
            'page_size' => 20, // API caps anonymous traffic to 20
            'source'    => implode( ',', $sources ),
        );

        if ( ! empty( $imgtype ) ) {
            $args['imgtype'] = $imgtype;
        }
        if ( ! empty( $aspect_ratio ) ) {
            $args['aspect_ratio'] = $aspect_ratio;
        }

        return $args;
    }

    private function get_source_list() {
        return array(
            'wordpress',
            'woc_tech',
            'wikimedia',
            'wellcome_collection',
            'thorvaldsensmuseum',
            'thingiverse',
            'svgsilh',
            'statensmuseum',
            'spacex',
            'smithsonian_zoo_and_conservation',
            'smithsonian_postal_museum',
            'smithsonian_portrait_gallery',
            'smithsonian_national_museum_of_natural_history',
            'smithsonian_libraries',
            'smithsonian_institution_archives',
            'smithsonian_hirshhorn_museum',
            'smithsonian_gardens',
            'smithsonian_freer_gallery_of_art',
            'smithsonian_cooper_hewitt_museum',
            'smithsonian_anacostia_museum',
            'smithsonian_american_indian_museum',
            'smithsonian_american_history_museum',
            'smithsonian_american_art_museum',
            'smithsonian_air_and_space_museum',
            'smithsonian_african_art_museum',
            'smithsonian_african_american_history_museum',
            'sketchfab',
            'sciencemuseum',
            'rijksmuseum',
            'rawpixel',
            'phylopic',
            'nypl',
            'nasa',
            'museumsvictoria',
            'met',
            'mccordmuseum',
            'iha',
            'geographorguk',
            'floraon',
            'flickr',
            'europeana',
            'eol',
            'digitaltmuseum',
            'deviantart',
            'clevelandmuseum',
            'brooklynmuseum',
            'bio_diversity',
            'behance',
            'animaldiversity',
            'WoRMS',
            'CAPL',
            '500px',
        );
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
            return new WP_Error( 'asi_openverse_invalid_media', __( 'Openverse returned an invalid media payload.', 'all-sources-images' ) );
        }

        return $response;
    }

    private function get_translator_callable( array $context ) {
        if ( isset( $context['generation'] ) && method_exists( $context['generation'], 'ASI_translate_text' ) ) {
            return array( $context['generation'], 'ASI_translate_text' );
        }

        return null;
    }

    private function get_source_label() {
        return ( 'cc_search' === $this->slug ) ? __( 'CC Search', 'all-sources-images' ) : __( 'Openverse', 'all-sources-images' );
    }

    private function log_raw_response_to_debug( $status_code, $body, array $extra_context = array() ) {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        $max_length = 4000;
        $body_length = function_exists( 'mb_strlen' ) ? mb_strlen( $body ) : strlen( $body );
        if ( $body_length > $max_length ) {
            $body = ( function_exists( 'mb_substr' ) ? mb_substr( $body, 0, $max_length ) : substr( $body, 0, $max_length ) ) . '... [truncated]';
        }

        $message = '[All Sources Images][Openverse] status=' . intval( $status_code );
        if ( ! empty( $extra_context['post_id'] ) ) {
            $message .= ' post=' . intval( $extra_context['post_id'] );
        }
        if ( ! empty( $extra_context['query'] ) ) {
            $message .= ' query="' . $extra_context['query'] . '"';
        }
        if ( ! empty( $extra_context['slug'] ) ) {
            $message .= ' slug=' . $extra_context['slug'];
        }
        $message .= ' body=' . $body;

        error_log( $message );
    }
}
