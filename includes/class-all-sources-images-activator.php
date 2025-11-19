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

		if (!get_option('ASI_plugin_activation_date')) {
			update_option('ASI_plugin_activation_date', time());
		}

	}

}
