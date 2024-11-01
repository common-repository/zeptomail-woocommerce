<?php

/**
 * Zeptomail WooCommerce plugin main class
 *
 * @author Zoho Mail
 */
 
 
if(!defined('ABSPATH')){
	exit;
}
class ZeptoWoo {

	
	protected $loader;

	/**
	 * @var string zeptowoo The string used to uniquely identify this plugin.
	 */
	protected $zeptowoo;
	
	public static $woocommerce_loaded;


	
	public function __construct() {

		$this->get_zeptowoo = 'zeptomail-woocommerce';
		$this->prepare_admin();
		
	}
	public function initialize_hooks() {
		self::$woocommerce_loaded = did_action( 'woocommerce_loaded' ) > 0;
		
		if ( ! self::$woocommerce_loaded ) {
			return;
		}
		require_once plugin_dir_path( __FILE__ ) . '/class-zeptomail-woocommerce-helper.php';
		if(!empty(get_option('zeptowoo_access_token') && !empty(get_option('zeptowoo_from_email_id')))){
			add_filter( 'woocommerce_email_classes', array( $this, 'register_zeptoemail_hooks' ));
		}
		
		

		
	}

	public function register_zeptoemail_hooks($emails )
    {
		require_once plugin_dir_path( __FILE__ ) . '/class-zeptomail-woocommerce-helper.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-zeptomail-woocommerce-api.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-zeptomail-woocommerce-send.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-zeptomail-newaccount-woocommerce.php';
		require_once plugin_dir_path( __FILE__ ) . '/class-zeptomail-invoice.php';
		
		
		$wcEmailHooks = ZeptoWoo_Helper::$wcZeptoEmailsMapping;

        foreach ($wcEmailHooks as $hook) {
			$priority = has_action($hook['hook'], array(  $emails[ $hook['wcEmail'] ], $hook['triggerAction']));
			
            remove_action($hook['hook'], array( $emails[ $hook['wcEmail'] ], $hook['triggerAction']), $priority);
			
        }
		
		$emails['WC_Email_Customer_New_Account']=new ZeptoWoo_WC_Email_Customer_New_Account();
		$emails['WC_Email_Customer_Invoice']=new ZeptoWoo_WC_Email_Customer_Invoice();
	   
		$zeptomail_send = new ZeptoWoo_Send();
		 
		
		return $emails;

	}

	public function zeptomail_validate_template(){
		require_once plugin_dir_path( __FILE__ ) . '/class-zeptomail-woocommerce-api.php';
		if (!function_exists('wc_formatting')) {
			include_once WC_ABSPATH . 'includes/wc-formatting-functions.php';
		}
		$nonce = sanitize_text_field($_REQUEST['_wpnonce']);
		if (!wp_verify_nonce($nonce, 'zeptowoo_template_nonce')) {
			$this->writeErrorMsg($response,"Unauthorized access");
			return;
		}
		
		$templateKey = sanitize_text_field($_POST["template_key"]);
		$templatename = sanitize_text_field($_POST["template_name"]);
		$zeptoAPI = new ZeptoWoo_Api();
		$resData = $zeptoAPI->getMailTemplateKeys($templateKey);
		
		$reqResponse = array();
		if(!empty( $resData["data"]))
		{
			$data = $resData["data"];
			$validatedata = $this->validateJSONData(wp_json_encode($data['sample_merge_info']),$templatename);
			
			if( count($validatedata["required"])>0) {
				$reqResponse["required"] = $validatedata["required"];
				$reqResponse["result"] = "warning";
				$reqResponse["errorMsg"] = __('Some mandatory variables are missing from the template. Variables are','zeptomail-woocommerce');

				update_option("zeptowoo_".$templatename,$templateKey,true);
			}
			else if($validatedata["error"]){
				$reqResponse["result"] = "error";
				$reqResponse["errorMsg"] = $validatedata["error"];
			}
			else{
				$reqResponse["result"] = "success";
				update_option("zeptowoo_".$templatename,$templateKey,true);
			}
			
		}
		else{
			$error = $resData["error"];
			$error_code = $error["code"];
			$reqResponse["result"] = "failure";
			if($error_code == "TM_3501" || $error_code == "SERR_120"){
				$reqResponse["errorMsg"] = __('No template found, enter the valid template key','zeptomail-woocommerce');
			}
			else{
				$reqResponse["errorMsg"] = __('Some error occured','zeptomail-woocommerce');
			}
		}
		echo wp_json_encode($reqResponse);
		wp_die();
	}
	
	public function zeptomail_save_mailagent(){
		$response = json_decode('{}',true);
		$nonce = sanitize_text_field($_REQUEST['_wpnonce']);
		if (!wp_verify_nonce($nonce, 'zeptowoo_email_submit_nonce')) {
			$this->writeErrorMsg($response,"Unauthorized access");
			return;
		}
		require_once plugin_dir_path( __FILE__ ) . '/class-zeptomail-woocommerce-api.php';
		$zeptowoo_mailagent = sanitize_text_field($_POST["zeptowoo_mail_agent"]);
		$zeptoFromAddress = sanitize_email($_POST["zeptowoo_from_email_id"]);
		$zeptoFromName = sanitize_text_field($_POST["zeptowoo_from_name"]);
		$zeptoCCAddress = sanitize_email($_POST["zeptowoo_cc_email_id"]);
		
		if (!filter_var($zeptoFromAddress, FILTER_VALIDATE_EMAIL)) {
			$this->writeErrorMsg($response,"Invalid from email id");
			return;
		}
		if (!empty($zeptoCCAddress) && !filter_var($zeptoCCAddress, FILTER_VALIDATE_EMAIL)) {
			$this->writeErrorMsg($response,"Invalid cc email id");
			return;
		}
		$zeptoAPI = new ZeptoWoo_Api();
		$agentRes = json_decode($zeptoAPI->getMailAgents(),true);
		$mailagents = $agentRes["data"];
		$isUpdated = false;
		$isAvailable = false;
		
		
		foreach($mailagents as $mailagent)
		{
			$mailagent_key = $mailagent["mailagent_key"];
			if(strcmp($zeptowoo_mailagent,$mailagent_key) == 0)
			{
				$isAvailable = true;
				$fromAddress['address'] = $zeptoFromAddress;
				$fromAddress['name'] = $zeptoFromName;
				$toAddress = [];
				$toAddress = [
					'address' => $zeptoFromAddress,
					'name' => $zeptoFromName
					];
				$zmtoeadd['email_address'] = $toAddress;
				$zmtoearr = array($zmtoeadd);
				$zeptoMailAPI = new ZeptoWoo_Api();
				
				$data = array(
					"from"       => $fromAddress,
					"subject"    => "This is test connection",
					"htmlbody"   => "Zeptomail is configured in woocommerce plugin",
					"to"         =>  $zmtoearr
				
				);
				$data['mailagent_key']=$zeptowoo_mailagent;
				$sendMailRes = json_decode($zeptoMailAPI->sendMail($data),true);
				
				if($sendMailRes["error"])
				{
					$response['result'] = "failure";
					$errorMsg = $sendMailRes["error"];
					$errorCode = $errorMsg["code"];
					$response['errorCode'] = $errorCode;
					if(strcmp($errorCode,"TM_3501") == 0 ){
						$response['errorMsg'] = "Invalid mail agent";
					}
					else if(strcmp($errorCode,"TM_4001") == 0 ){
						$response['errorMsg'] = "Invalid mail address";
					}
				}else if($sendMailRes["data"]){
					$response['result'] = "success";
					update_option("zeptowoo_mail_agent",$zeptowoo_mailagent,true);
					update_option("zeptowoo_from_email_id",$zeptoFromAddress,true);
					update_option("zeptowoo_from_name",$zeptoFromName,true);
					
					update_option("zeptowoo_cc_email_id",$zeptoCCAddress,true);
					$isUpdated = true;
				}
				
				
				break;
				
			}
			
		}
		if(!$isAvailable){
			$response['result'] = "failure";
			$response["code"] = "TM_3501";
			$response["errorMsg"] = "Invalid mail agent";
		}
		echo wp_json_encode($response);
		 wp_die();
	}
	public function zeptomail_reset_template(){
		$nonce = sanitize_text_field($_REQUEST['_wpnonce']);
		if (!wp_verify_nonce($nonce, 'zeptowoo_template_nonce')) {
			$this->writeErrorMsg($response,"Unauthorized access");
			return;
		}
		$zeptowoo_template_name = sanitize_text_field($_POST["template_name"]);
		delete_option("zeptowoo_".$zeptowoo_template_name);
		echo "updated";
		wp_die();
	}

	private function prepare_admin()
    {
		add_action("wp_ajax_zeptomail_validate_template" , array( $this,"zeptomail_validate_template"));
		add_action("wp_ajax_zeptomail_save_mailagent" , array( $this,"zeptomail_save_mailagent"));
		add_action("wp_ajax_zeptomail_reset_template" , array( $this,"zeptomail_reset_template"));
		add_filter('plugin_action_links_' . ZEPTOWOO_PLUGIN_NAME_BASE_NAME, array( $this, 'plugin_settings_link' ));
		
	}
	

	public function plugin_settings_link( $links) {
		$settings_link = '<a href="' . admin_url() . 'admin.php?page=zeptomail-config">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	public function get_zeptowoo() {
		return $this->get_zeptowoo;
	}
	public function validateJSONData($jsonData,$template_name){
		
		require_once plugin_dir_path( __FILE__ ) . '/class-zeptomail-woocommerce-helper.php';
		$template = ZeptoWoo_Helper::$zeptoWCTriggerMapping[$template_name];
		$validateData = array();
		$validateData["required"] = array();
		if($template){
			$schema = wp_remote_retrieve_body(wp_remote_get(plugin_dir_url( __FILE__ ) . '../assets/json/'.ZeptoWoo_Helper::$zeptoWCTriggerMapping[$template_name]["file"]));
			$jsonDataArray = json_decode($jsonData, true);
			
			$schemaArray = json_decode($schema, true);
			$this->validateJson($jsonDataArray, $schemaArray,$validateData);
		}else{
			$validateData["error"] = __('Some error occured','zeptomail-woocommerce');
		}
		
		return $validateData;
	}
	public function writeErrorMsg($response,$errorMsg){
		$response["result"] = "failure";
		$response["errorMsg"] = $errorMsg;
		echo wp_json_encode($response);
		wp_die();
	}


/**
 * Validate JSON data against a JSON schema.
 *
 * @param array $data   JSON data as an associative array
 * @param array $schema JSON schema as an associative array
 *
 * @return bool True if the data is valid, false otherwise
 */
function validateJson($data, $schema,&$validateJson,$prefix = '')
{
	foreach ($schema['properties'] as $key => $property) {
		
		if(!$validateJson["required"]){
				$validateJson["required"] = array();
				
			}
        // Check if required property exists
        if (in_array($key, $schema['required']) && !isset($data[$key])) {
			
			if($property['type'] === 'object'){
				
				foreach( $property['required'] as $req){
					array_push($validateJson["required"],$prefix .$key .".". $req);
				}
			}
			else if($property['type'] === 'array'){
				$items = $property['items'];
				$requiredItems = array();
				foreach( $items['required'] as $req){
					array_push($requiredItems,$req);
				}
				if( count($requiredItems)>0){
					$validateJson["required"][$key]=$requiredItems;
				}
			}
			
			else{
				array_push($validateJson["required"],$prefix .$key);
			}
			
			
        }
		

        // Check data type
        if (isset($data[$key])) {
			$dataype = gettype($data[$key]);
			
			if($property['type'] === 'object'){
				if($dataype !== 'array'){
					
					foreach( $property['required'] as $req){
						array_push($requiredItems,$req);
						array_push($validateJson["required"],$prefix .$key .".". $req);
					}
				}
				else{
					
					$this->validateJson($data[$key], $property,$validateJson,$prefix . $key. ".");
				}
			}
			else if($property['type'] === 'array') {
				$items = $property['items'];
				$validateItemJson = array();
				$this->validateJson($data[$key], $items,$validateItemJson,$prefix . "");
				if($validateItemJson["required"]){
					$requiredItems = array();
					foreach( $validateItemJson['required'] as $reqItem){
						array_push($requiredItems,$reqItem);
						
					}
					if( count($requiredItems)>0){
					$validateJson["required"][$key]=$requiredItems;
				    }
				}
			}
			
            
        }

        
    }

   
}


}