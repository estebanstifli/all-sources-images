<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ALLSI_Source_Manager {
    /** @var ALLSI_Source_Manager */
    private static $instance;

    /** @var ALLSI_Image_Source[] */
    private $sources = array();

    private function __construct() {
        /**
         * Allow third-parties to register custom sources early.
         */
        do_action( 'ALLSI_register_sources', $this );
    }

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_source( ALLSI_Image_Source $source ) {
        $slug = $source->get_slug();
        $this->sources[ $slug ] = $source;
    }

    public function has_source( $slug ) {
        return isset( $this->sources[ $slug ] );
    }

    /**
     * @param string $slug
     *
     * @return ALLSI_Image_Source|null
     */
    public function get_source( $slug ) {
        return $this->has_source( $slug ) ? $this->sources[ $slug ] : null;
    }

    /**
     * Return all registered sources. Mainly used for debugging/tests.
     *
     * @return ALLSI_Image_Source[]
     */
    public function all() {
        return $this->sources;
    }
}
