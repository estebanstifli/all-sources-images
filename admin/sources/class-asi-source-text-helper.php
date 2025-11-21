<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Text_Helper {

    /**
     * Build ALT text respecting plugin options and optional translations.
     */
    public static function build_alt_text( array $options, $search_term, $source_label, $translator = null ) {
        if ( empty( $options['enable_alt'] ) || 'enable' !== $options['enable_alt'] ) {
            return '';
        }

        $alt_from = isset( $options['alt_from'] ) ? $options['alt_from'] : '';
        $alt_text = '';

        if ( 'source' === $alt_from ) {
            $alt_text = $source_label;
        } elseif ( 'based_on' === $alt_from ) {
            $alt_text = $search_term;
        }

        if ( '' === trim( $alt_text ) ) {
            return '';
        }

        if ( isset( $options['translate_alt'] ) && 'true' === $options['translate_alt'] ) {
            $target_lang = ! empty( $options['translate_alt_lang'] ) ? $options['translate_alt_lang'] : 'en';
            if ( 'en' !== $target_lang && is_callable( $translator ) ) {
                $translated = call_user_func( $translator, $alt_text, 'en', $target_lang );
                if ( false !== $translated && ! is_wp_error( $translated ) ) {
                    $alt_text = $translated;
                }
            }
        }

        return $alt_text;
    }

    /**
     * Build caption text respecting plugin options.
     */
    public static function build_caption( array $options, $author_name, $source_label ) {
        if ( empty( $options['enable_caption'] ) || 'enable' !== $options['enable_caption'] ) {
            return '';
        }

        $caption = (string) $author_name;

        if ( isset( $options['caption_from'] ) && 'author_bank' === $options['caption_from'] ) {
            $caption .= esc_html__( ' from ', 'all-sources-images' ) . $source_label;
        }

        return trim( $caption );
    }
}
