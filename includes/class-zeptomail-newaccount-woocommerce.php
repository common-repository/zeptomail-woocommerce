<?php

/**
 * Zeptomail WooCommerce plugin WC_Email_Customer_New_Account extend class
 *
 * @author Zoho Mail 
 */
 
 
if(!defined('ABSPATH')){
	exit;
}


class ZeptoWoo_WC_Email_Customer_New_Account extends WC_Email_Customer_New_Account {
    
	public function __construct() {
		parent::__construct();
	}
	
    public function trigger($user_id, $user_pass = '', $password_generated = false) {
       
        return false;
    }
}