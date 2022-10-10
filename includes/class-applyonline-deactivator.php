<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://wpreloaded.com/farhan-noor
 * @since      1.0.0
 *
 * @package    Applyonline
 * @subpackage Applyonline/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Applyonline
 * @subpackage Applyonline/includes
 * @author     Farhan Noor <profiles.wordpress.org/farhannoor>
 */
class Applyonline_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
            unregister_post_type('aol_ad');
            unregister_post_type('aol_application');
            flush_rewrite_rules();
            remove_role('aol_manager');
	}
}
