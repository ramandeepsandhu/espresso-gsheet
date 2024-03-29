<?php
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
use \Firebase\JWT\JWT;


class EspressoGSheet_API {

		/**
	 * The ID of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name     The ID of this plugin.
	*/
	private $plugin_name;

	/**
	 * The version of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version   The version of this plugin.
	*/
	private $version;

	/**
	 * The events of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $events    The version of this plugin.
	*/
	private $events;

	/**
	 * Private_key_id of  Google Service Account Credentials  
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $private_key_id   Private_key_id of  Google Service Account Credentials  
	 */				
	public $private_key_id;	

	/**
	 * private_key Google Service Account Credentials  
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $private_key    Private_key Google Service Account Credentials  .
	 */		
	public $private_key;	

	/**
	 * Google Service Account Credentials  client_email aka service account email
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $client_email    Google Service Account Credentials  client_email aka service account email
	*/			
	public $client_email;

	/**
	 * Google Service Account Credentials client id 
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $client_id    Google Service Account Credentials client id 
	*/	
	public $client_id;
	
	/**
	 * Common methods used in the all the classes 
	 * @since    3.6.0
	 * @var      object    $version    The current version of this plugin.
	*/	
	public $common;

	public $google_token ;

	public $google_account_credential;

	protected $service, $permission, $client, $drive;

	/**
	 * construct of this class 
	 * @since    3.6.0
	 * @var      object    $version    The current version of this plugin.
	*/	
	public function __construct($plugin_name, $version, $common){
		# setting Plugin name 
		$this->plugin_name 	= $plugin_name;
		# setting version 
		$this->version 		= $version;
		# Common function
		$this->common 		= $common;
		# getting Gkeys from Saved Options ;
		$credential 	= 	get_option( 'espresso_gsheet_google_credential', false );
		$credential 	= 	json_decode($credential, true);


		$this->google_account_credential = $credential;

		# Assigned the Class Variables Value ;
		if($credential AND isset($credential['private_key_id'], $credential['private_key'], $credential['client_email'], $credential['client_id'])){
			$this->google_token		= get_option( 'espresso_gsheet_google_token', false );
			# setting values from saved meta Data
			$this->private_key_id 	= $credential['private_key_id'];
			$this->private_key 		= $credential['private_key'];
			$this->client_email 	= $credential['client_email'];
			$this->client_id 		= $credential['client_id'];
		}
	}


	public function after_post_updated_callback( $post_id, $post) {
		$this->attach_spreadsheet_info($post_id, $post);
	}

	public function acf_after_save_post_callback( $post_id ) {
		$post   = get_post( $post_id );
		$this->attach_spreadsheet_info($post_id, $post);
	}

	public function attach_spreadsheet_info( $post_id, $post) {
		
		if($post->post_type != 'espresso_events'){
			return;
		}


		if (!class_exists('ACF')) {
			$enable_spreadsheet_integration = get_post_meta( $post_id, 'enable_spreadsheet_integration', true );
			$google_spreadsheet_id = get_post_meta( $post_id, 'google_spreadsheet_id',  true );
			$google_spreadsheet_url = get_post_meta( $post_id, 'google_spreadsheet_url', true );
		}else{
			$enable_spreadsheet_integration = get_field('enable_spreadsheet_integration', $post_id);
			$google_spreadsheet_id = get_field('google_spreadsheet_id', $post_id );
			$google_spreadsheet_url = get_field('google_spreadsheet_url', $post_id);
		}

		$title = $post->post_title;
		if($enable_spreadsheet_integration == 'yes'){
			
			$this->initClient();
	 		$folder_id = '';
	 		
	 		if(get_option('espresso_gsheet_folder_name')){
	 			$folder_id = $this->create_folder(get_option('espresso_gsheet_folder_name'));
		 		if(get_option('espresso_gsheet_google_folder_id')){
			        update_option('espresso_gsheet_google_folder_id', $folder_id);
			    }
			    else {
			      	add_option('espresso_gsheet_google_folder_id', $folder_id);
			    }
	 		}
	 		
			if($google_spreadsheet_id && $google_spreadsheet_url){
				if($folder_id){
					$files = $this->get_files_inside_folders(get_option('espresso_gsheet_google_folder_id'));
					if(!isset($files[$google_spreadsheet_id])){
			 			$this->moveFile($google_spreadsheet_id, $folder_id);
			 		}
		 		}
				//do nothing
			}else{

				$google_spreadsheet_id = $this->create_spreadsheet($title);
				if($google_spreadsheet_id){
					update_post_meta($post_id, 'google_spreadsheet_url', 'https://docs.google.com/spreadsheets/d/'.$google_spreadsheet_id, false); // unique 
                	update_post_meta($post_id, 'google_spreadsheet_id', $google_spreadsheet_id, false);
                	
                	if($folder_id){
                		$this->moveFile($google_spreadsheet_id, $folder_id);
                	}
				}
			}
        }
    }

    public function get_files_inside_folders($folderId){
    	$existingFiles = [];
      	$optParams = array(
      		  "q"  => " '${folderId}' in parents and trashed = false"
      		//'q' => "mimeType='application/vnd.google-apps.spreadsheet' and trashed=false",
            //'pageSize' => 10,
            //'fields' => 'nextPageToken, files(id, name)'
        );

        $results = $this->drive->files->listFiles($optParams);
        if (count($results->getFiles()) == 0) {
            // "No files found.\n";
        } else {
            foreach ($results->getFiles() as $file) {
            	$existingFiles[$file->getId()] = $file->getName();
            }
        }

        return $existingFiles;
    }


     

    public function initClient(){

    	$client = new \Google_Client();
		$client->setApplicationName('Google Sheets API');
		//$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
		$client->setScopes(array(Google_Service_Sheets::SPREADSHEETS, Google_Service_Drive::DRIVE));
		$client->setAccessType('offline');

    	//$client->setScopes('https://www.googleapis.com/auth/spreadsheets');
		// credentials.json is the key file we downloaded while setting up our Google Sheets API
		$credential = 	get_option( 'espresso_gsheet_google_credential', false );
	
		$client->setAuthConfig(json_decode($credential, true));

		$this->client 	= $client;
		$this->service 	= new Google_Service_Sheets($this->client);
		$this->drive 	= new Google_Service_Drive($this->client);

		
    }

    public function checkFolderIfExists( $folder_name = ''){

        $parameters['q'] = "mimeType='application/vnd.google-apps.folder' and name='$folder_name' and trashed=false";
        $files = $this->drive->files->listFiles($parameters);
        $folders = [];

        foreach( $files as $k => $file ){
            $folders[$file->id] = $file->name;
        }
        return $folders;
    }


    public function create_folder($folder_name = ''){
		
		$folders = $this->checkFolderIfExists($folder_name);
		if($folders){
			return key($folders);
		}else{
		    try {
		        
		        $permission = new \Google_Service_Drive_Permission();
		        $espresso_gsheet_share_email = get_option('espresso_gsheet_share_email', false);
				$role = get_option('espresso_gsheet_role', 'reader');
				
				if(filter_var($espresso_gsheet_share_email, FILTER_VALIDATE_EMAIL)){
					$permission->setEmailAddress(trim($espresso_gsheet_share_email));
					$permission->setType('group');
					$permission->setRole($role);
					$permission->allowFileDiscovery;
				}

		        $fileMetadata = new \Google_Service_Drive_DriveFile(array(
		            'name' => $folder_name,
		            'mimeType' => 'application/vnd.google-apps.folder'));
		        
		        $file = $this->drive->files->create($fileMetadata, array(
		            'fields' => 'id'));

		        try {
					$res = $this->drive->permissions->create($file->id, $permission);
				}catch (Exception $e) {
					$this->common->gsheet_log( array(
						'action'	=>	'create_folder', 
						'response'	=> $e->getMessage())
					);
				}

		        return $file->id;

		    }catch(Exception $e) {
		    	$this->common->gsheet_log( array(
					'action'	=>	'create_folder', 
					'response'	=> 'Error occured while accessing google drive. '. $e->getMessage())
				);
		    }
		}
	}

	public function moveFile($fileId, $newParentId) {
	  try {

	    $emptyFileMetadata = new Google_Service_Drive_DriveFile();
	    // Retrieve the existing parents to remove
	    //$file = $this->drive->files->get($fileId, array('fields' => 'parents'));
	    
	    //$previousParents = join(',', $file->parents);
	    // Move the file to the new folder
	    $file = $this->drive->files->update($fileId, $emptyFileMetadata, array(
	      'addParents' => $newParentId,
	      //'removeParents' => $previousParents,
	      'fields' => 'id, parents'));
	    return $file;
	  } catch (Exception $e) {
	    $this->common->gsheet_log( array(
			'action'	=>	'moveFile', 
			'response'	=> 'Error occured while moving file to folder. '.$e->getMessage())
		);
	  }
	}

    public function create_spreadsheet($title){   
	    try{
	        $spreadsheet = new Google_Service_Sheets_Spreadsheet([
	            'properties' => [
	                'title' => $title
	                ]
	        ]);
            $spreadsheet = $this->service->spreadsheets->create($spreadsheet, [
                'fields' => 'spreadsheetId'
            ]);
            
            if($spreadsheet->spreadsheetId){
            	try{

					$permission = new \Google_Service_Drive_Permission();
					//$results = $drive->permissions->listPermissions($spreadsheet->spreadsheetId);
					$espresso_gsheet_share_email = get_option('espresso_gsheet_share_email', false);
					$role = get_option('espresso_gsheet_role', 'reader');
					
					if(filter_var($espresso_gsheet_share_email, FILTER_VALIDATE_EMAIL)){
						$permission->setEmailAddress(trim($espresso_gsheet_share_email));
						$permission->setType('group');
						$permission->setRole($role);
						$res = $this->drive->permissions->create(
							$spreadsheet->spreadsheetId,
							$permission, 
						);
					}else{
						$permission->setType('anyone');
						$permission->setRole($role);
						$res = $this->drive->permissions->create(
							$spreadsheet->spreadsheetId,
							$permission, 
							//['transferOwnership' => 'true']
						);
					}

				}catch(Exception $e) {
		        	$this->common->gsheet_log( array(
						'action'	=>	'create_spreadsheet', 
						'request'	=> $spreadsheet,
						'response'	=> $e->getMessage())
					);
		      	}
			}

	        return $spreadsheet->spreadsheetId;
	    }
	    catch(Exception $e) {
	        $this->common->gsheet_log( array(
				'action'	=>	'initialize_Google_Service_Sheets_Spreadsheet', 
				'response'	=> $e->getMessage())
			);
	      }
	}

	function add_attendee_to_spreadsheet($args = array()){

    	$transaction = EE_Registry::instance()->load_model('Transaction')->get_transaction_from_reg_url_link();
		
		if ( ! $transaction instanceof EE_Transaction ) {
			return;
		}

  		$registrations = $transaction->registrations();
		$answer = EE_Registry::instance()->load_model('Answer');
		$rows = [];
    	
    	foreach ($registrations as $registration) {
			
			$post_id = $registration->event()->ID();
			$meta = get_post_meta( $post_id, 'google_spreadsheet_id');
			$sheet_meta = get_post_meta( $post_id, 'google_spreadsheet_name');
			$attendee_id =	 $registration->attendee_ID();

            if( isset($meta[0]) ) {
				
            	$spreadsheetId = $meta[0];
            	$spreadsheetName = (isset($sheet_meta[0]))?$sheet_meta[0]:'Sheet1';

  		        $transaction_status = ($transaction->is_completed())?'Completed':'Not Completed';
		        try{	

		        	if($registration->question_groups()){
			    		foreach($registration->question_groups() as $question_group){
			    			//$question_group->ID();
			    			if($question_group->questions()){
				    			foreach($question_group->questions() as $question){
									$label = preg_replace("/[^a-zA-Z0-9 ]+/", "", $question->display_text());
				    				$rows[$spreadsheetId][$attendee_id][$label] = addslashes($answer->get_answer_value_to_question($registration, $question->ID(), true));
				    			}
			    			}
			    		}
		    		}

		            $rows[$spreadsheetId][$attendee_id]['Payment Status'] = $transaction_status;
		            $rows[$spreadsheetId][$attendee_id]['Created'] = date('Y/m/d h:i:s');
		            $rows[$spreadsheetId][$attendee_id]['Sheet_Name'] = $spreadsheetName;

		        }catch(Exception $e) {
		        	$this->common->gsheet_log( array(
						'action'	=>	'add_attendee_to_spreadsheet', 
						'request'	=> $registrations,
						'response'	=> $e->getMessage())
					);
		      	}
		    }
        }

        if($rows){
        	$this->save_entry_to_spreadsheet($rows);
        }
	}

	function save_entry_to_spreadsheet($attendee_records){

		$this->initClient();
		foreach($attendee_records as $spreadsheetId => $rows){
			$data = [];
			$current_row = array_pop($rows);
			$range = ($current_row['Sheet_Name'])?$current_row['Sheet_Name']:'Sheet1';
			unset($current_row['Sheet_Name']);

			$response = $this->service->spreadsheets_values->get($spreadsheetId, $range);
			$values_already_exist = $response->getValues();
			
			$keys = array_keys($current_row);
			$values = array_values($current_row);
			
			if(!$values_already_exist){
				$data[] = $keys;
			}
			$data[] = $values;

			try{	
				$valueRange = new \Google_Service_Sheets_ValueRange();
				$valueRange->setValues($data);
				$options = ['valueInputOption' => 'USER_ENTERED'];
				$this->service->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $options);
										  
			}catch(Exception $e) {
				
				$this->common->gsheet_log( array(
					'action'	=>	'save_entry_to_spreadsheet', 
					'request'	=> $data,
					'response'	=> $e->getMessage())
				);
				
			}
		}

	}

	function my_error_notice() {
	    ?>
	    <div class="error notice">
	        <p><?php _e( 'There has been an error!', 'load_espresso_gsheet' ); ?></p>
	    </div>
	    <?php
	}
	

	public function token_validation_checker(){
		
		# Check access_token elements is set or not;
		if(! isset($this->token()['access_token']) OR empty($this->token()['access_token'])){
			add_action( 'admin_notices', 'my_error_notice' );
			return array( FALSE, "ERROR: access_token elements is_not_set OR access_token is empty !" );
		}
		# If passed parameter is Array and Not String  || Creating Query URL
		$request = wp_remote_get( "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=" . $this->token()['access_token']);
		# is_wp_error()
		if(is_wp_error($request) OR ! isset($request['response']['code'])  OR $request['response']['code'] != 200){
			return array(FALSE,  json_encode($request));
		} else {
			return  array(TRUE, $request['body']);
		}
	}

		/**
     * Creating google API tokens & Getting tokens from Google                      		
     * @param string|array  $credential Google Service account token.
     * @note Some error On This Function || When There is No Internat it Show error. 
     * @uses 
    */
	public function generatingTokenByCredential($credential = null){
		# google credential
	  	
	  	if(!isset($credential)){
	  		$credential   = $this->google_account_credential;
	  	}

		# Check is Token array or not
		if(! is_array( $credential )  ){
			return array( FALSE, "ERROR: credential is Not Array !" );
		}
		# Check  client_email is set or not 
		if(! isset($credential['client_email'])){
			return array( FALSE, array('ERROR:'=> 420 , 'Message' => 'ERROR: client_email not set.'));
		}
		#  check client_email is empty or not
		if( empty($credential['client_email'])){
			return array( FALSE, array('ERROR:'=> 420 , 'Message' => "ERROR: client_email is Empty."));
		}
		# Check private_key is set or not
		if(! isset($credential['private_key'])){
			return array( FALSE, array('ERROR:'=> 420 , 'Message' => "ERROR: private_key not set."));
		}
		# Check private_key is Empty or not
		if(empty($credential['private_key'])){
			//$this->common->gsheet_log(get_class( $this ), __METHOD__, "304", "ERROR: private_key is Empty.");
			return array(FALSE, array('ERROR:'=> 420 , 'Message' => "ERROR: private_key is Empty."));
		}
		# Creating payload
		$payload = array(
		    "iss" 	=>  $credential['client_email'],
		    "scope"	=> 'https://www.googleapis.com/auth/drive',
		    "aud" 	=> 'https://oauth2.googleapis.com/token',
		    "exp"	=>	time()+3600,
		    "iat" 	=> 	time(),
		);


		$jwt  = JWT::encode($payload, $credential['private_key'], 'RS256');

		$args = array(
		    'headers' => array(),
		    'body'    => array(
	            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
	            'assertion'  => $jwt,
	        )
		);
		# Token url Remote request 
		$returns  =  wp_remote_post('https://oauth2.googleapis.com/token', $args);
		# Check & Balance 
		if(is_wp_error($returns) OR !is_array($returns) OR !isset($returns['body'])){
			# Inserting error log 
			return array(FALSE, "ERROR :  on token Creation." . json_encode($returns, TRUE));
		} else {
			# inserting SUCCESS log
			return array(TRUE, json_decode($returns['body'], TRUE));
		}
	}

	public function token(){
		# getting google token 
		$google_token = $this->google_token;

		# Checking Token Validation
		if($google_token  &&  time() > $google_token['expires_in']){
			# if Credentials & Not empty
			$new_token = $this->generatingTokenByCredential();
			# Check & Balance
			if($new_token[0]){
				# Change The Token Info
				$new_token[1]['expires_in'] = time() + $new_token[1]['expires_in'];
				# coping The Token
				$google_token = $new_token[1];
				# Save in Options
				update_option('espresso_gsheet_google_token', $new_token[1]);
			}else{
				# ERROR : false credential ! Google said so ;-D ;
				# return the valid token;
				return false;
			}
		}
		# return the valid token;
		return $google_token;
	}

}

?>