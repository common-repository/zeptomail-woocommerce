<?php

/**
 * Zeptomail WooCommerce plugin api class
 *
 * @author Zoho Mail
 */

if(!defined('ABSPATH')){
	exit;
}
class ZeptoWoo_Api {
	const get_domain_uri = "/portal/v1.0/domains/mailagents";
	const send_mail_uri = "/v1.0/email";
	const send_template_uri = "/v1.0/email/template";
	const get_token_uri = "/oauth/v2/token";
	const get_agentlist_uri = "/portal/v1.0/mailagents";
	const get_templatelist_uri = "/v1.1/mailagents/";

    public function __construct() {
		$instance = $this;
    }
	
	private function getAccountsUrl() {
		return "https://accounts.".ZeptoWoo_Helper::$domainMapping[get_option("zeptowoo_domain_name")];
	}
	
	private function getZeptoUrl() {
		return "https://zeptomail.".ZeptoWoo_Helper::$domainMapping[get_option("zeptowoo_domain_name")];
	}
	
	public function sendMail($dataarray){
		$header = $this->getApiHeader();
		$urlToSend = $this->getZeptoUrl().$this::send_mail_uri;
		$args = array(
				'body' => wp_json_encode($dataarray),
				'headers' => $header,
				'method' => 'POST'
				);
		$response = wp_remote_post( $urlToSend, $args );
		if (is_wp_error($response)) {
			return $response->get_error_message();
		} else {
			return wp_remote_retrieve_body($response);
		}
	}
	public function sendTemplateMail($dataarray){
		$header = $this->getApiHeader();
		$urlToSend = $this->getZeptoUrl().$this::send_template_uri;
		$args = array(
				'body' => wp_json_encode($dataarray),
				'headers' => $header,
				'method' => 'POST'
				);
	    
		$response = wp_remote_post( $urlToSend, $args );
		if (is_wp_error($response)) {
			return $response->get_error_message();
		} else {
			return wp_remote_retrieve_body($response);
		}
	}
	public function getMailAgents() {
		$header = $this->getApiHeader();
		$urlToSend = $this->getZeptoUrl().$this::get_agentlist_uri;
		$args = array(
				'httpversion' => '1.1',
				'headers' => $header,
				'method' => 'GET'
				);
		$response = wp_remote_get($urlToSend,$args);
		if (is_wp_error($response)) {
			return $response->get_error_message();
		} else {
			return wp_remote_retrieve_body($response);
		}
	}
	public function getMailTemplates() {
		$header = $this->getApiHeader();
		$urlToSend = $this->getZeptoUrl().$this::get_templatelist_uri.get_option('zeptowoo_mail_agent')."/templates";
		
		$args = array(
				'httpversion' => '1.1',
				'headers' => $header,
				'method' => 'GET'
				);
		$response = wp_remote_get($urlToSend,$args);
		if (is_wp_error($response)) {
			return $response->get_error_message();
		} else {
			return wp_remote_retrieve_body($response);
		}
	}
	public function getMailAgentDomains($mailAgent) {
		$header = $this->getApiHeader();
		
		$domainList = array();
		
		$urlToSend = $this->getZeptoUrl().$this::get_domain_uri."/".$mailAgent;
		$args = array(
				'httpversion' => '1.1',
				'headers' => $header,
				'method' => 'GET'
				);
		
		$response = wp_remote_get($urlToSend,$args);
		if (is_wp_error($response)) {
			return $domainList;
		} else {
			$domainListData = json_decode(wp_remote_retrieve_body($response),true);
			$domains = $domainListData["data"];
			foreach($domains as $domainData)
			{
				array_push($domainList,$domainData["domain_name"]);
			}
			return $domainList;
		}
	}
	public function getMailTemplateKeys($templatekey) {
		$mailagentkey = get_option('zeptowoo_mail_agent');
		$header = $this->getApiHeader();
		$urlToSend = $this->getZeptoUrl()."/v1.1/mailagents/".$mailagentkey."/templates/".$templatekey;
		
		$args = array(
				'httpversion' => '1.1',
				'headers' => $header,
				'method' => 'GET'
				);
		$response = wp_remote_get($urlToSend,$args);
		
		if (is_wp_error($response)) {
			return $response->get_error_message();
		} else {
			return json_decode(wp_remote_retrieve_body($response),true);
		}
	}
	private function getApiHeader() {
		return array(
				'Authorization' => 'Zoho-oauthtoken '.$this->getAccessToken(),
				'User-Agent' => 'Zepto_WooCommerce'
				);
		
	}
	
	private function getAccessToken() {
		if( empty(get_option('zeptowoo_timestamp')) || time() - get_option('zeptowoo_timestamp') > 3000) {
			$url = $this->getAccountsUrl()."/oauth/v2/token?refresh_token=".base64_decode(get_option('zeptowoo_refresh_token'))."&client_id=".base64_decode(get_option('zeptowoo_client_id'))."&client_secret=".base64_decode(get_option('zeptowoo_client_secret'))."&redirect_uri=".admin_url()."/admin.php?page=zeptomail-config&grant_type=refresh_token";
			
			$bodyAccessTok = wp_remote_retrieve_body(wp_remote_post( $url));
			$respoJs = json_decode($bodyAccessTok);
			
			if($respoJs->access_token)
			{
				update_option('zeptowoo_access_token',base64_encode($respoJs->access_token), false);
				update_option('zeptowoo_timestamp',time(), false);
				return $respoJs->access_token;
			}
			else{
				return null;
			}
			
		}
		else{
			
			return base64_decode(get_option('zeptowoo_access_token'));
		}
	}


    
   
}
	