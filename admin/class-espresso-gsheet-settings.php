<?php

/**
 * The admin-specific functionality of the plugin.

 */
class EspressoGSheet_Settings {
	/**
	 * The ID of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	/**
	 * The events object.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private  $events;
	/**
	 * The events object's eventsAndTitles array.
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $version    The current version of this plugin.
	 */
	public $eventsAndTitles = array();	
	/**
	 * Google Service Account  client_id 
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version the current version of this plugin.
	 */	
	public $client_id;
	/**
	 * Google Service Account  client_secret 
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version the current version of this plugin.
	 */	
	public $client_secret;
	/**
	 * Google Service Account Credentials  
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $version  the current version of this plugin.
	 */	
	public $credentials = array();
	/**
	 * Google Service Account Credentials client_id 
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $version  the current version of this plugin.
	 */	
	public $googleSheet;

	/**
	 * Common methods used in the all the classes 
	 * @since    3.6.0
	 * @var      object    $version    The current version of this plugin.
	 */	
	public $common;

	/**
	 * Initialize the class and set its properties.
	 * @since      1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $eventsObj, $googleSheet, $common ){
		$this->plugin_name 		= $plugin_name;				# Name of the Plugin setting for this Class
		$this->version 			= $version;					# Version of this Plugin setting for this Class
		$this->events 			= $eventsObj;				# Events of This Plugin setting for this Class
		$this->googleSheet 		= $googleSheet;				# Passing $googleSheet object
		$this->common 			= $common;					# Passing common methods object
	}

	public function espresso_gsheet_admin_menu()
    {
        add_menu_page(
            __( 'Espresso Spread Sheet', 'espresso-gsheet' ),
            __( 'Espresso Spread Sheet', 'espresso-gsheet' ),
            'manage_options',
            'espresso-gsheet',
            array( $this, 'espresso_gsheet_settings_view' ),
            'dashicons-media-spreadsheet'
        );
       
        add_submenu_page(
            "espresso-gsheet",
            "Help",
            "Help",
            "manage_options",
            "espresso-gsheet-help",
            array( $this, 'espresso_gsheet_settings_help' )
        );
    }

	# Admin menu init
	public function espresso_gsheet_settings_menu(){
		add_submenu_page('espresso-gsheet', __('Settings','espresso-gsheet' ), __('Settings', 'espresso-gsheet'),'manage_options','espresso-gsheet-settings', array( $this,'espresso_gsheet_settings_view'));
	}


	/**
     * AKA URL routers , And Settings And Log page view Page , Related to menu , menu Page.

    */
	public function espresso_gsheet_settings_view(){
		$credential 	= 	get_option( 'espresso_gsheet_google_credential', false );
		$credential 	= 	json_decode(stripslashes(html_entity_decode($credential)), true);
		
		# For Settings View , If Not log or Help || Default 
		require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/settings-display.php';
		
	}

	public function espresso_gsheet_settings_help(){
		
		# For Settings View , If Not log or Help || Default 
		require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/help-display.php';
	}
	
	public function handle_form(){
		if(isset($_POST['action'])){
			
			if(isset($_POST['espresso_gsheet_google_credential'])){
				$credential = (isset( $_POST['espresso_gsheet_google_credential']) && !empty($_POST['espresso_gsheet_google_credential'])) ? json_decode(stripslashes(trim($_POST['espresso_gsheet_google_credential'])), true) : false ;

				if(isset($credential['private_key']) && isset($credential['client_email'])){
					# saving the Google Credentials on the site option
					# Creating token by service account credentials 
					$token = $this->googleSheet->generatingTokenByCredential($credential);

					if( $token[0] ){
						if( ! get_option('espresso_gsheet_google_credential') ){
							add_option( 'espresso_gsheet_google_credential', json_encode($credential) );
	    					add_option( 'espresso_gsheet_google_token', $token[1] );
						}else{
							update_option( 'espresso_gsheet_google_token',	$token[1] );
							update_option( 'espresso_gsheet_google_credential',	json_encode($credential) );
						}
						wp_redirect( 'admin.php?page=espresso-gsheet&msg=success' );
					} else {
						EspressoGSheet_Settings::my_error_notice('invalid_credential');
					}
				} else {
					EspressoGSheet_Settings::my_error_notice('invalid_credential');
				}
			}

			if(isset($_POST['enable_espresso_gsheet_integration'])){
				if(get_option('enable_espresso_gsheet_integration') === FALSE){
	                add_option('enable_espresso_gsheet_integration', $_POST['enable_espresso_gsheet_integration']);
	            }else{
	                update_option('enable_espresso_gsheet_integration', $_POST['enable_espresso_gsheet_integration']);
	            }
			}else{
				update_option('enable_espresso_gsheet_integration', '');
			}
		}
	}

	public function settings_init()
    {
        $credential 	= 	json_decode(get_option( 'espresso_gsheet_google_credential', false ),true);
        $callback = 'callback';

        if ( isset( $credential['client_email'] ) ) { 
        	$callback = 'status_callback';
        }
        add_settings_section(  
            'espress_gsheet_settings_section', // Section ID 
            'Google Service Account Configuration', // Section Title
            array($this, $callback), // Callback
            'espress_gsheet_settings',
        );
        add_settings_field( // Option 1
            'enable_espresso_gsheet_integration', // Option ID
            'Enable Integration?', // Label
            array($this, 'field_callback'), // !important - This is where the args go!
            'espress_gsheet_settings', // Page it will be displayed (General Settings)
            'espress_gsheet_settings_section', // Name of our section
            array( 
                'option_name' => 'enable_espresso_gsheet_integration',
                'type' => 'checkbox'
            )); 

        if ( !isset( $credential['client_email'] ) ) { 
        	add_settings_field( // Option 2
	            'espresso_gsheet_google_credential', // Option ID
	            'Google Credential *', // Label
	            array($this, 'field_callback'), // !important - This is where the args go!
	            'espress_gsheet_settings', // Page it will be displayed (General Settings)
	            'espress_gsheet_settings_section', // Name of our section
	            array( 
	                'option_name' => 'espresso_gsheet_google_credential',
	                'type' => 'textarea',
	                'tip' => 'Exactly copy the downloaded file Credentials, and Paste it above.'
	            )  
	        );
        }
        register_setting('espress_gsheet_settings','espresso_gsheet_google_credential', array($this, 'validate_credentials'));
    }

    public function validate_credentials($args){
		return $args;
    }


    function my_error_notice($error_code) {
    	switch($error_code){
    		case "invalid_credential": ?>
    			<div class="error notice">
	        		<p><?php _e( 'Google Sheet Credentials are invalid!', 'espresso-gsheet' ); ?></p>
	    		</div>
    		<?php break;
    	
    	}?>
	    <?php
	}

	public function callback(){
		
	}
    

    public function status_callback() { // Section Callback
    	$ret = $this->googleSheet->token_validation_checker();
        if ( $ret[0] ) {
        	echo "<div class='alert alert-success'>Your account is successfully integrated with Google spreadsheet with this service account email address :  " . esc_html( $this->googleSheet->client_email ) . "</div>"; 
        }else{
			echo "<div class='alert alert-success'>Your account is partially connected with Google spreadsheet. Please try to reconnect again using the Google Spread sheet credentials.</div>"; 
        } 
        echo  "<div><a href=" . admin_url( 'admin-post.php?action=google_settings&deleteCredential=1&nonce=' ) . wp_create_nonce( 'wpgsi-google-nonce-delete' ) . " class='button-secondary' style=' text-decoration: none; color: #7f7f7f;'>  Remove Credential  </a></div>" ;
    }



    public function field_callback($args) {  // Textbox Callback
        $option = get_option($args['option_name']);

        if($args['type'] == 'textarea'){
        	echo '<textarea id="' .$args['option_name'].'" name="'. $args['option_name'] .'" cols="80" rows="8"  class="large-text"> '.$option.'</textarea>';
        	echo '<p>' . esc_attr( $args['tip'] ) .'</p>';
        }elseif($args['type'] == 'checkbox'){
        	$checked = ($option == 'Y')? " checked='checked'": '';
        	echo '<input class="regular-text" type="'.$args['type'].'" id="'. $args['option_name'] .'" name="'. $args['option_name'] .'" value="Y" ' . $checked . ' />';
        }else{
        	echo '<input class="regular-text" type="'.$args['type'].'" id="'. $args['option_name'] .'" name="'. $args['option_name'] .'" value="' . $option . '" />';
        }
    }


	/**
	 * this is a Form submission call back function
	 * Below function will save Settings form.
     * Saving Settings Form Submission, Creating token and other Works.
     * @uses 	Settings Page 
    */
	public function google_settings(){

		# Delete Credential from option Table
		if(isset($_GET['deleteCredential'])){
			delete_option( "espresso_gsheet_google_token");
			delete_option( "espresso_gsheet_google_credential");
			wp_redirect( 'admin.php?page=espresso-gsheet&msg=success' );
		}
	}
}
