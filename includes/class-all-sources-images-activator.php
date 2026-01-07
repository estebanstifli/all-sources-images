<?php

/**
 * Fired during plugin activation
 * @since      1.0.0
 *
 * @package    All_Sources_Images
 * @subpackage All_Sources_Images/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    All_Sources_Images
 * @subpackage All_Sources_Images/includes
 */
class All_Sources_Images_Activator {

	/**
	 * Add activation date during activation
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		ALLSI_log( 'Activator::activate() called', 'ACTIVATOR' );
		if ( false === ALLSI_ensure_logs_dir() ) {
			ALLSI_log( 'Unable to prepare logs directory during activation', 'ACTIVATOR' );
		}
		
		// CRITICAL: Add capabilities immediately during activation
		// This prevents "You do not have sufficient permissions" error on first activation
		self::add_capabilities();
		
		// Create bulk generation database tables
		self::create_bulk_generation_tables();
		
		if ( ! get_option( 'ALLSI_plugin_activation_date' ) ) {
			$result = update_option( 'ALLSI_plugin_activation_date', time() );
			ALLSI_log( 'Activation date set: ' . ( $result ? 'SUCCESS' : 'FAILED' ), 'ACTIVATOR' );
		} else {
			ALLSI_log( 'Activation date already exists', 'ACTIVATOR' );
		}
		
		ALLSI_log( 'Activator::activate() completed', 'ACTIVATOR' );
	}

	/**
	 * Create bulk generation database tables
	 *
	 * @since    6.1.7
	 */
	private static function create_bulk_generation_tables() {
		ALLSI_log( 'Creating bulk generation database tables', 'ACTIVATOR' );
		
		// Load the DB class
		$db_class_path = plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-allsi-bulk-generation-db.php';
		if ( file_exists( $db_class_path ) ) {
			require_once $db_class_path;
			ALLSI_Bulk_Generation_DB::create_tables();
			ALLSI_log( 'Bulk generation tables created successfully', 'ACTIVATOR' );
		} else {
			ALLSI_log( 'Bulk generation DB class not found: ' . $db_class_path, 'ACTIVATOR' );
		}
	}

	/**
	 * Add plugin capabilities to roles during activation
	 * 
	 * @since    1.0.0
	 */
	private static function add_capabilities() {
		ALLSI_log( 'Adding capabilities during activation', 'ACTIVATOR' );
		
		// Add capability to administrator role
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->add_cap( 'ALLSI_manage', true );
			ALLSI_log( 'Added ALLSI_manage capability to administrator role', 'ACTIVATOR' );
		}
		
		// Also add capability to current user immediately (in case role update doesn't take effect yet)
		$current_user = wp_get_current_user();
		if ( $current_user && $current_user->ID > 0 ) {
			$current_user->add_cap( 'ALLSI_manage', true );
			ALLSI_log( 'Added ALLSI_manage capability to current user (ID: ' . $current_user->ID . ')', 'ACTIVATOR' );
		}
		
		// Load rights settings and add capabilities to other roles if configured
		$options = get_option( 'ALLSI_plugin_rights_settings' );
		if ( $options ) {
			$roles = array(
				'editor'      => 'rights_editor',
				'author'      => 'rights_author',
				'contributor' => 'rights_contributor',
				'subscriber'  => 'rights_subscriber',
			);
			foreach ( $roles as $role_name => $option_key ) {
				$role = get_role( $role_name );
				if ( $role && isset( $options[$option_key] ) && $options[$option_key] === 'true' ) {
					$role->add_cap( 'ALLSI_manage', true );
					ALLSI_log( 'Added ALLSI_manage capability to ' . $role_name . ' role', 'ACTIVATOR' );
				}
			}
		}
	}

}
