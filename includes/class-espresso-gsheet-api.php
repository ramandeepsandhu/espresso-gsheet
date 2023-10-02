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

	function my_error_notice() {
	    ?>
	    <div class="error notice">
	        <p><?php _e( 'There has been an error. Bummer!', 'my_plugin_textdomain' ); ?></p>
	    </div>
	    <?php
	}
	

	public function token_validation_checker(){
		
		# Check access_token elements is set or not;
		if(! isset($this->token()['access_token']) OR empty($this->token()['access_token'])){
			add_action( 'admin_notices', 'my_error_notice' );
			//$this->common->gsheet_log( get_class($this),__METHOD__, "307", "ERROR: access_token elements is_not_set OR access_token is empty !");
			return array( FALSE, "ERROR: access_token elements is_not_set OR access_token is empty !" );
		}
		# If passed parameter is Array and Not String  || Creating Query URL
		$request = wp_remote_get( "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=" . $this->token()['access_token']);
		# is_wp_error()
		if(is_wp_error($request) OR ! isset($request['response']['code'])  OR $request['response']['code'] != 200){
			//$this->common->gsheet_log(get_class($this),__METHOD__, "309", "ERROR: Token Validation Checked, Invalid token [x]. Response is : " . json_encode($request));
			return array(FALSE,  json_encode($request));
		} else {
			//$this->common->gsheet_log(get_class($this), __METHOD__, "200", "SUCCESS: Token Validation Checked, Valid Token [ok].");
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
			//$this->common->gsheet_log(get_class($this),__METHOD__, "300", "ERROR: credential is Not Array." . $credential);
			return array( FALSE, "ERROR: credential is Not Array !" );
		}
		# Check  client_email is set or not 
		if(! isset($credential['client_email'])){
			//$this->common->gsheet_log(get_class( $this ),__METHOD__,"301", "ERROR: client_email not set.");
			return array( FALSE, array('ERROR:'=> 420 , 'Message' => 'ERROR: client_email not set.'));
		}
		#  check client_email is empty or not
		if( empty($credential['client_email'])){
			//$this->common->gsheet_log(get_class( $this ), __METHOD__, "302", "ERROR: client_email is Empty.");
			return array( FALSE, array('ERROR:'=> 420 , 'Message' => "ERROR: client_email is Empty."));
		}
		# Check private_key is set or not
		if(! isset($credential['private_key'])){
			//$this->common->gsheet_log(get_class( $this ),__METHOD__,"303", "ERROR: private_key not set.");
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
			//$this->common->gsheet_log( get_class($this),__METHOD__,"305","ERROR:  on token Creation." . json_encode($returns, TRUE));
			return array(FALSE, "ERROR :  on token Creation." . json_encode($returns, TRUE));
		} else {
			# inserting SUCCESS log
			//$this->common->gsheet_log(get_class($this),__METHOD__,"200","SUCCESS: Successfully token created.");
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
				//$this->common->gsheet_log(get_class($this), __METHOD__,"504", "ERROR: false credential ! Google said so ;-D. from  GoogleSpreadsheets func. " . json_encode($new_token));
				# return the valid token;
				return false;
			}
		}
		# return the valid token;
		return $google_token;
	}

}

?>