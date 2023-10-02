<?php
/**
 * This is a Common utility Methods class.
 * All those Methods are used in many classes
*/ 
class EspressoGSheet_Common {
	/**
	 * The ID of this plugin.
	 * @since    3.6.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 * @since    3.6.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	*/
	private $version;
	/**
	 * The common object.
	 * @since    3.6.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	*/
	public function __construct($plugin_name, $version){
		$this->plugin_name 	= $plugin_name;				# Name of the Plugin setting for this Class
		$this->version 		= $version;					# Version of this Plugin setting for this Class
	}

	public function gsheet_log(){
		//Add logic to create logs either to database or log file
	}
}
?>