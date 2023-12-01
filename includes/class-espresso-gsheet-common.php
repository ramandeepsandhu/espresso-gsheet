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

	public function gsheet_log($log){

		$log = array_merge(['datetime' => date('Y-m-d H:i:s')], $log);
		$fileName = date('Y-m').'_log.txt';
		$myfile = fopen(plugin_dir_path( dirname( __FILE__ ) ). "logs/". $fileName, "a+") or die("File does not exist!");
		$txt = print_r($log, true);
		fwrite($myfile, $txt);
		fclose($myfile);
	}


	public function load_custom_admin_style(){

		wp_register_style( 'espresso-gsheet', plugin_dir_url(dirname( __FILE__ )) . 'admin/css/admin.css', false, '1.0.0' );
    	wp_enqueue_style( 'espresso-gsheet' );
	}
}
?>