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
		ASI_log( 'Activator::activate() called', 'ACTIVATOR' );
		if ( false === ASI_ensure_logs_dir() ) {
			ASI_log( 'Unable to prepare logs directory during activation', 'ACTIVATOR' );
		}
		
		// CRITICAL: Add capabilities immediately during activation
		// This prevents "You do not have sufficient permissions" error on first activation
		self::add_capabilities();
		
		if ( ! get_option( 'ASI_plugin_activation_date' ) ) {
			$result = update_option( 'ASI_plugin_activation_date', time() );
			ASI_log( 'Activation date set: ' . ( $result ? 'SUCCESS' : 'FAILED' ), 'ACTIVATOR' );
		} else {
			ASI_log( 'Activation date already exists', 'ACTIVATOR' );
		}
		
		ASI_log( 'Activator::activate() completed', 'ACTIVATOR' );
	}

	/**
	 * Add plugin capabilities to roles during activation
	 * 
	 * @since    1.0.0
	 */
	private static function add_capabilities() {
		ASI_log( 'Adding capabilities during activation', 'ACTIVATOR' );
		
		// Add capability to administrator role
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->add_cap( 'asi_manage', true );
			ASI_log( 'Added asi_manage capability to administrator role', 'ACTIVATOR' );
		}
		
		// Also add capability to current user immediately (in case role update doesn't take effect yet)
		$current_user = wp_get_current_user();
		if ( $current_user && $current_user->ID > 0 ) {
			$current_user->add_cap( 'asi_manage', true );
			ASI_log( 'Added asi_manage capability to current user (ID: ' . $current_user->ID . ')', 'ACTIVATOR' );
		}
		
		// Load rights settings and add capabilities to other roles if configured
		$options = get_option( 'ASI_plugin_rights_settings' );
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
					$role->add_cap( 'asi_manage', true );
					ASI_log( 'Added asi_manage capability to ' . $role_name . ' role', 'ACTIVATOR' );
				}
			}
		}
	}

}
