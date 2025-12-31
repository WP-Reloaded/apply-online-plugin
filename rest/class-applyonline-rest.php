<?php
/**
 * The updater functionality of the plugin.
 *
 * @link       
 * @since      2.6.7.3
 *
 * @package    Applyonline
 * @subpackage Applyonline/rest
 */

/**
 * The updater functionality of the plugin.
 *
 * Defines the plugin name, version
 *
 * @package    Applyonline
 * @subpackage Applyonline/rest
 * @author     Farhan Noor <profiles.wordpress.org/farhannoor>
 */
class Applyonline_Rest{
    
    /**
	 * The ID of this plugin.
	 *
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $plugin_version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */    
        function __construct( $plugin_name, $version ) {
            $this->plugin_name = $plugin_name;
            $this->plugin_version = $version;
        }
}