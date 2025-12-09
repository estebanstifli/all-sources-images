<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 *
 * @package    All_Sources_Images
 * @subpackage All_Sources_Images/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    All_Sources_Images
 * @subpackage All_Sources_Images/includes
 */
class All_Sources_Images {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      All_Sources_Images_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    4.0.0
	 */
	public function __construct() {
		if ( defined( 'ALL_SOURCES_IMAGES_VERSION' ) ) {
			$this->version = ALL_SOURCES_IMAGES_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'all-sources-images';

		$this->load_dependencies();
		$this->define_admin_hooks();
		//$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - All_Sources_Images_Loader. Orchestrates the hooks of the plugin.
	 * - All_Sources_Images_i18n. Defines internationalization functionality.
	 * - All_Sources_Images_Admin. Defines all hooks for the admin area.
	 * - All_Sources_Images_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-all-sources-images-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-all-sources-images-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-all-sources-images-admin.php';

		/**
		 * MPT Generation features
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-all-sources-images-generation.php';

		/**
		 * Bulk Generation Database Class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-asi-bulk-generation-db.php';

		/**
		 * Bulk Generation AJAX Handlers
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-asi-bulk-generation-ajax.php';

		/**
		 * Bulk Generation Cron Processor
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-asi-bulk-generation-cron.php';

		/**
		 * Plugin Integrations (WP All Import, WPeMatico, FeedWordPress, etc.)
		 * DISABLED: Plugin compatibility features have been removed from the UI.
		 * The file is kept for backward compatibility but not loaded by default.
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-asi-plugin-integrations.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-all-sources-images-public.php';

		$this->loader = new All_Sources_Images_Loader();
		
		/**
		 * Initialize Plugin Integrations
		 * DISABLED: Plugin compatibility features have been removed from the UI.
		 */
		// ASI_Plugin_Integrations::get_instance( $this->plugin_name, $this->version );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new All_Sources_Images_Admin( $this->get_plugin_name(), $this->get_version() );


		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

    	$this->loader->add_action( 'admin_menu', $plugin_admin, 'ASI_main_settings' );

    	$this->loader->add_action( 'init', $plugin_admin, 'ASI_main_actions' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new All_Sources_Images_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    4.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    All_Sources_Images_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
