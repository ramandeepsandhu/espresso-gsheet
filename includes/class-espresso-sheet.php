<?php

class EspressoGSheet {

	protected $loader;
	protected $plugin_name;
	protected $version;


	public function __construct() {
		
		$this->version = '1.0.0';
		
		$this->plugin_name = 'espresso-gsheet';

		$this->load_dependencies();

		//$this->set_locale();

		$this->define_admin_hooks();

		$this->define_public_hooks();
	}

	public function load_dependencies(){
		require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/class-espresso-gsheet-common.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		*/
		require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/class-espresso-gsheet-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		*/
		require_once plugin_dir_path(dirname( __FILE__ )) . 'admin/class-espresso-gsheet-admin.php';


		require_once plugin_dir_path(dirname( __FILE__ )) . 'admin/class-espresso-gsheet-events.php';
		

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		*/
		require_once plugin_dir_path(dirname( __FILE__ )) . 'admin/class-espresso-gsheet-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the Inclued Google .
		*/
		require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/class-espresso-gsheet-api.php';

		$this->loader = new EspressoGSheet_Loader();
	}

	
	public function define_admin_hooks(){
		$common 	 	= new EspressoGSheet_Common($this->get_plugin_name(), $this->get_version());	
		$googleSheet    = new EspressoGSheet_API($this->get_plugin_name(), $this->get_version(), $common); 
		$events 		= new EspressoGSheet_Events($this->get_plugin_name(), $this->get_version(), $common);	
		$settings 		= new EspressoGSheet_Settings($this->get_plugin_name(), $this->get_version(), $events, $googleSheet, $common);

		
		$this->loader->add_action('admin_menu', $settings, 'espresso_gsheet_admin_menu');
		$this->loader->add_action('admin_init', $settings, 'settings_init');
		$this->loader->add_action('admin_post_google_settings',	$settings, 'google_settings');
		$this->loader->add_action('post_updated', $googleSheet, 'attach_spreadsheet_info');

		//$this->loader->add_action('admin_menu', $settings, 'espresso_gsheet_settings_menu');


	}

	public function define_public_hooks(){

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 * @since    1.0.0
	*/
	public function run(){
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	*/
	public function get_plugin_name(){
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 * @since     1.0.0
	*/
	public function get_loader(){
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	*/
	public function get_version(){
		return $this->version;
	}



}