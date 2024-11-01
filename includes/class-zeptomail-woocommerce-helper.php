<?php

/**
 * Zeptomail WooCommerce plugin helper class
 *
 * @author Zoho Mail
 */
 if(!defined('ABSPATH')){
	exit;
}
class ZeptoWoo_Helper {
    
    
    public static $wcZeptoEmailsMapping = [
        [
            'hook' => 'woocommerce_new_order',
            'wcEmail' => 'WC_Email_New_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_payment_complete',
            'wcEmail' => 'WC_Email_New_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_pending_to_processing_notification',
            'wcEmail' => 'WC_Email_New_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_pending_to_completed_notification',
            'wcEmail' => 'WC_Email_New_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_pending_to_on-hold_notification',
            'wcEmail' => 'WC_Email_New_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_failed_to_processing_notification',
            'wcEmail' => 'WC_Email_New_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_failed_to_completed_notification',
            'wcEmail' => 'WC_Email_New_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_failed_to_on-hold_notification',
            'wcEmail' => 'WC_Email_New_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_cancelled_to_processing_notification',
            'wcEmail' => 'WC_Email_New_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_cancelled_to_completed_notification',
            'wcEmail' => 'WC_Email_New_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_cancelled_to_on-hold_notification',
            'wcEmail' => 'WC_Email_New_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_pending_to_on-hold_notification',
            'wcEmail' => 'WC_Email_Customer_On_Hold_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_failed_to_on-hold_notification',
            'wcEmail' => 'WC_Email_Customer_On_Hold_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_cancelled_to_on-hold_notification',
            'wcEmail' => 'WC_Email_Customer_On_Hold_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_fully_refunded_notification',
            'wcEmail' => 'WC_Email_Customer_Refunded_Order',
			'triggerAction' => 'trigger_full',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_partially_refunded_notification',
            'wcEmail' => 'WC_Email_Customer_Refunded_Order',
			'triggerAction' => 'trigger_partial',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_processing_to_cancelled_notification',
            'wcEmail' => 'WC_Email_Cancelled_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_on-hold_to_cancelled_notification',
            'wcEmail' => 'WC_Email_Cancelled_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_cancelled_to_processing_notification',
            'wcEmail' => 'WC_Email_Customer_Processing_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_failed_to_processing_notification',
            'wcEmail' => 'WC_Email_Customer_Processing_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_on-hold_to_processing_notification',
            'wcEmail' => 'WC_Email_Customer_Processing_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_pending_to_processing_notification',
            'wcEmail' => 'WC_Email_Customer_Processing_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_pending_to_failed_notification',
            'wcEmail' => 'WC_Email_Failed_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_on-hold_to_failed_notification',
            'wcEmail' => 'WC_Email_Failed_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_order_status_completed_notification',
            'wcEmail' => 'WC_Email_Customer_Completed_Order',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_new_customer_note_notification',
            'wcEmail' => 'WC_Email_Customer_Note',
			'triggerAction' => 'trigger',
            'args' => 1
        ],
        [
            'hook' => 'woocommerce_reset_password_notification',
            'wcEmail' => 'WC_Email_Customer_Reset_Password',
			'triggerAction' => 'trigger',
            'args' => 2
        ],
        [
            'hook' => 'woocommerce_created_customer_notification',
            'wcEmail' => 'WC_Email_Customer_New_Account',
			'triggerAction' => 'trigger',
            'args' => 3
        ],
		[
            'hook' => 'zeptowoo_invoice_mail',
            'wcEmail' => 'WC_Email_Customer_Invoice',
			'triggerAction' => 'trigger',
            'args' => 2
        ]
        
    ];


	public static $zeptoWCTriggerMapping = [

        'New_Order'                     => ['label'=>'New order','file' => 'order.json','type' => 'order'],
        'Cancelled_Order'               => ['label'=> 'Cancelled order','file' => 'order.json','type' => 'order'],
        'Failed_Order'                  => ['label'=>'Failed order','file' => 'order.json','type' => 'order'],
        'Customer_On_Hold_Order'        => ['label'=>'Order on-hold','file' => 'order.json','type' => 'order'],
        'Customer_Processing_Order'     => ['label'=>'Processing order','file' => 'order.json','type' => 'order'],
        'Customer_Completed_Order'      => ['label'=>'Completed order','file' => 'order.json','type' => 'order'],
        'Customer_Refunded_Order'       => ['label'=>'Refunded order','file' => 'order.json','type' => 'order'],
        'Customer_Invoice'              => ['label'=>'Customer invoice','file' => 'order.json','type' => 'order'],
        'Customer_Note'                 => ['label'=>'Customer note','file' => 'order.json','type' => 'order'],
        'Customer_Reset_Password'       => ['label'=>'Reset password','file' => 'reset_password.json','type' => 'user'],
        'Customer_New_Account'          => ['label'=>'New account','file' => 'newaccount.json','type' => 'user']
    ];
	
	public static $domainMapping = [
				"com"          => "zoho.com",
				"eu"           => "zoho.eu", 
				"in"           => "zoho.in", 
				"com.cn"       => "zoho.com.cn",
				"com.au"       => "zoho.com.au",
				"jp"           => "zoho.jp",
				"zohocloud.ca" => "zohocloud.ca",
				"sa"           => "zoho.sa"
	];
	
}