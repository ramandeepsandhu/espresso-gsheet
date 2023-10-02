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
            __( 'Espresso gSheet Integrations', 'espresso-gsheet' ),
            __( 'Espresso gSheet Integrations', 'espresso-gsheet' ),
            'manage_options',
            'espresso-gsheet',
            array( $this, 'espresso_gsheet_settings_view' ),
            'dashicons-media-spreadsheet'
        );
        add_submenu_page(
            "espresso-gsheet",
            "Demo",
            "Demo",
            "manage_options",
            "espresso-gsheet-demo",
            array( $this, 'espresso_gsheet_settings_demo' )
        );
        add_submenu_page(
            "espresso-gsheet",
            "Help",
            "Help",
            "manage_options",
            "espresso-gsheet-help",
            array( $this, 'espresso_gsheet_settings_help' )
        );
        // https://wordpress.stackexchange.com/questions/98226/admin-menus-name-menu-different-from-first-submenu
    }

	# Admin menu init
	public function espresso_gsheet_settings_menu(){
		add_submenu_page('espresso-gsheet', __('Settings','espresso-gsheet' ), __('Settings', 'espresso-gsheet'),'manage_options','espresso-gsheet-settings', array( $this,'espresso_gsheet_settings_view'));
	}

	public function wpgsi_settings_notices(){
		// echo "<pre>";

		// echo "</pre>";
	}

	/**
     * AKA URL routers , And Settings And Log page view Page , Related to menu , menu Page.
     * @uses  wpgsi_settings_menu function.
     */
	public function espresso_gsheet_settings_view(){
		# Change the Code  || it should be Not like This Way 
		$credential 	= 	get_option( 'espresso_gsheet_google_credential', false );
		$credential 	= 	json_decode(stripslashes(html_entity_decode($credential)), true);
		
		# For Settings View , If Not log or Help || Default 
		require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/settings-display.php';
		
	}

	public function espresso_gsheet_settings_demo(){

		return ;
		//https://spreadsheet-coding.com/google-sheets-api-php-client/create-a-blank-spreadsheet
		//https://stackoverflow.com/questions/69806570/google-api-create-accessible-by-link-speadsheet
		//https://github.com/ryancramerdesign/GoogleClientAPI/blob/master/GoogleSheets.php
		//https://spreadsheet-coding.com/google-sheets-api-php-client/create-a-blank-spreadsheet
		# Change the Code  || it should be Not like This Way 
		
		require_once plugin_dir_path(dirname(__FILE__)) .'/vendor/autoload.php';

		// configure the Google Client
		$client = new \Google_Client();
		$client->setApplicationName('Google Sheets API');
		//$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
		$client->setScopes(array(Google_Service_Sheets::SPREADSHEETS, Google_Service_Drive::DRIVE));
		$client->setAccessType('offline');

    	//$client->setScopes('https://www.googleapis.com/auth/spreadsheets');


		// credentials.json is the key file we downloaded while setting up our Google Sheets API
		$credential = 	get_option( 'espresso_gsheet_google_credential', false );
	
		$client->setAuthConfig(json_decode($credential, true));

		//$client->setAuthConfig($path);
		// configure the Sheets Service
		$service = new \Google_Service_Sheets($client);

		$title = 'My Test Spreadsheet';

		/*try{
	        $spreadsheet = new \Google_Service_Sheets_Spreadsheet([
	            'properties' => [
	                'title' => $title
	                ]
	            ]);
	            $spreadsheet = $service->spreadsheets->create($spreadsheet, [
	                'fields' => 'spreadsheetId'
	            ]);
	            printf("Spreadsheet ID: %s\n", $spreadsheet->spreadsheetId);
	            //return $spreadsheet->spreadsheetId;
	    }
	    catch(Exception $e) {
	        // TODO(developer) - handle error appropriately
	        echo 'Message: ' .$e->getMessage();
	      }*/

			//1HYxyOYFWKkVXHfUplxEXECnERaLI1gCkmBQhbGER1dk

	      $spreadsheetId = '1-1Tunet9DqWJTgtW9NoSv4I1EmC8PyOHUZ5OY-_Ar5o';

	      $drive = new \Google_Service_Drive($client);
			$newPermission = new \Google_Service_Drive_Permission();
			$newPermission->setEmailAddress('testshare@gmail.com');
			$newPermission->setType('group');
			$newPermission->setRole('writer');
			$res = $drive->permissions->create($spreadsheetId, $newPermission);
			echo '<pre>';
			print_r($res);

	      //$spreadsheetId =  $spreadsheet->spreadsheetId;
			
			/*$spreadsheet = $service->spreadsheets->get($spreadsheetId);

			$range = 'Sheet1'; // here we use the name of the Sheet to get all the rows
			$response = $service->spreadsheets_values->get($spreadsheetId, $range);
			$values = $response->getValues();
			echo '<pre>';
			print_r($values);


			$newRow = [
			    'Hellboy',
			    time()
			];

			$rows = [$newRow]; // you can append several rows at once
			$valueRange = new \Google_Service_Sheets_ValueRange();
			$valueRange->setValues($rows);
			$range = 'Sheet1'; // the service will detect the last row of this sheet
			$options = ['valueInputOption' => 'USER_ENTERED'];
			$service->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $options);


			$range = 'Sheet1';
			$response = $service->spreadsheets_values->get($spreadsheetId, $range);
			$rows = $response->getValues();
			// Remove the first one that contains headers
			$headers = array_shift($rows);
			// Combine the headers with each following row
			$array = [];
			foreach ($rows as $row) {
			    $array[] = array_combine($headers, $row);
			}

			echo '<pre>';
			print_r($array);*/


	      // 1oicaAM3fuVLgFkXfHmN0CTf_IaOGSibaOD4NsHXMcds 
	      // 1-1Tunet9DqWJTgtW9NoSv4I1EmC8PyOHUZ5OY-_Ar5o


		//Create New Sheet

		
		# For Settings View , If Not log or Help || Default 
		require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/demo-display.php';
		
	}

	

	

	public function espresso_gsheet_settings_help(){
		# Change the Code  || it should be Not like This Way 
		
		# For Settings View , If Not log or Help || Default 
		require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/help-display.php';
		
	}
	

	public function settings_init()
    {
       
        add_settings_section(  
            'espress_gsheet_settings_section', // Section ID 
            'Google Service Account Configuration', // Section Title
            array($this,'pre_section_callback'), // Callback
            'espress_gsheet_settings',
        );
        
        add_settings_field( // Option 1
            'espresso_gsheet_google_credential', // Option ID
            'Google Credential *', // Label
            array($this, 'field_callback'), // !important - This is where the args go!
            'espress_gsheet_settings', // Page it will be displayed (General Settings)
            'espress_gsheet_settings_section', // Name of our section
            array( 
                'option_name' => 'espresso_gsheet_google_credential',
                'type' => 'textarea'
            )  
        ); 
        
        register_setting('espress_gsheet_settings','espresso_gsheet_google_credential', array($this, 'validate_credentials'));
    }

    public function validate_credentials($args){

    	$credential = (isset( $_POST['espresso_gsheet_google_credential']) && !empty($_POST['espresso_gsheet_google_credential'])) ? json_decode(stripslashes($_POST['espresso_gsheet_google_credential']), true) : false ;
    	

		if($credential){
			
			# check for vitals
			if(isset($credential['private_key'], $credential['client_email'])){
				# saving the Google Credentials on the site option

				
				# Creating token by service account credentials 
				$token = $this->googleSheet->generatingTokenByCredential($credential);

				if( $token[0] ){
					if( ! get_option('espresso_gsheet_google_credential') ){
    					add_option( 'espresso_gsheet_google_token', $token[1] );
					}else{
						update_option( 'espresso_gsheet_google_token',	$token[1] );
					}

					//wp_redirect( 'admin.php?page=espresso-gsheet&msg=success' );
				} else {
					//$this->common->gsheet_log( get_class($this), __METHOD__, "702", "ERROR: false credential ! Google said so ;-D ." . json_encode( $token ) );
					//wp_redirect( 'admin.php?page=espresso-gsheet&msg=false' );
				}

			} else {
				# if credential vitals are empty or Missing 
				//$this->common->gsheet_log( get_class($this), __METHOD__, "703", "ERROR:  private_key or client_email is Not set !");
				//wp_redirect( 'admin.php?page=espresso-gsheet&msg=false' );
			}

		}

		return $args;
		//return false;


    }
    


    public function pre_section_callback() { // Section Callback
        echo '<p><b> Exactly copy the downloaded file Credentials, and Paste it here : </b></p>';  
    }

    public function field_callback($args) {  // Textbox Callback

        $option = get_option($args['option_name']);
        
        if($args['type'] == 'textarea'){
        	echo '<textarea id="' .$args['option_name'].'" name="'. $args['option_name'] .'" cols="80" rows="8"  class="large-text"> '.$option.'</textarea>';
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

	/**
     * Removing Logs From Database after 200 
     * @param array  		No Data array.
     * @uses 			    Wp Admin Footer Hook
    */
	public function gsheet_remove_log(){
		$gsheet_log = get_posts( array( 'post_type' => 'gsheet_log', 'posts_per_page' => -1 ) );
		if ( count( $gsheet_log ) > 200 ){
			foreach ($gsheet_log as $key =>  $log ) {
				if (  $key > 200 ){
					wp_delete_post($log->ID, true);
				}
			}
		}
	}

}
