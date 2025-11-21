<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ASI_Source_Manager {
    /** @var ASI_Source_Manager */
    private static $instance;

    /** @var ASI_Image_Source[] */
    private $sources = array();

    private function __construct() {
        /**
         * Allow third-parties to register custom sources early.
         */
        do_action( 'asi_register_sources', $this );
    }

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_source( ASI_Image_Source $source ) {
        $slug = $source->get_slug();
        $this->sources[ $slug ] = $source;
    }

    public function has_source( $slug ) {
        return isset( $this->sources[ $slug ] );
    }

    /**
     * @param string $slug
     *
     * @return ASI_Image_Source|null
     */
    public function get_source( $slug ) {
        return $this->has_source( $slug ) ? $this->sources[ $slug ] : null;
    }

    /**
     * Return all registered sources. Mainly used for debugging/tests.
     *
     * @return ASI_Image_Source[]
     */
    public function all() {
        return $this->sources;
    }
}
