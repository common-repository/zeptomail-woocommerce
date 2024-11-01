<?php

/**
 * Zeptomail WooCommerce plugin WC_Email_Customer_Invoice extend class
 *
 * @author Zoho Mail
 */
 
 
if(!defined('ABSPATH')){
	exit;
}


class ZeptoWoo_WC_Email_Customer_Invoice extends WC_Email_Customer_Invoice {
    
	public function __construct() {
		parent::__construct();
	}
	
    public function trigger($arg1, $arg2 =false) {
        do_action( 'zeptowoo_invoice_mail', $arg1, $arg2 );
        return false;
    }
}