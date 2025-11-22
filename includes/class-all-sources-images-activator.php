<?php

/**
 * Fired during plugin activation
 *
 * @link       https://magic-post-thumbnail.com/
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
 * @author     Magic Post Thumbnail <contact@magic-post-thumbnail.com>
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
		
		if ( ! get_option( 'ASI_plugin_activation_date' ) ) {
			$result = update_option( 'ASI_plugin_activation_date', time() );
			ASI_log( 'Activation date set: ' . ( $result ? 'SUCCESS' : 'FAILED' ), 'ACTIVATOR' );
		} else {
			ASI_log( 'Activation date already exists', 'ACTIVATOR' );
		}
		
		ASI_log( 'Activator::activate() completed', 'ACTIVATOR' );
	}

}
