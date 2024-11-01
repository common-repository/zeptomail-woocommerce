<?php

/**
 * Zeptomail WooCommerce plugin send mail class
 *
 * @author Zoho Mail
 */
if(!defined('ABSPATH')){
	exit;
}
if ( class_exists( 'WC_Email' ) ) :

class ZeptoWoo_Send extends WC_Email {

    public $emailObject;
    
    public function __construct() {
		
		$emailHooks = ZeptoWoo_Helper::$wcZeptoEmailsMapping;

        $instance = $this;

        foreach ($emailHooks as $hook) {

            add_action( $hook['hook'], function($param1, $param2 = "",$param3 =null) use ($hook, $instance) {
				if($hook['wcEmail'] == 'WC_Email_Customer_Reset_Password'){
					$instance->handleUserAccount($hook['wcEmail'],true,$param1, $param2,$param3);
				}
				else if($hook['wcEmail'] == 'WC_Email_Customer_New_Account'){
					$instance->handleUserAccount($hook['wcEmail'],false,$param1, $param2,$param3);
					
				}
				else{
					$instance->handleOrderEmail($hook['hook'],$hook['wcEmail'],$param1,$param2);
				}
                
            }, 10, $hook['args']);
        }
		parent::__construct();
    }
	
	
	
	private function handleUserAccount($wcEmail,$isresetpassword,$param1,$param2='',$param3=false)  
    {
		$templatekey = $this->getTemplateKeyForWCEmail($wcEmail);
		
		if($templatekey == null)
		{
			$this->sendUserMail( $wcEmail,$isresetpassword,$param1,$param2,$param3);
		}
		else {
			$this->sendUserTemplateMail( $wcEmail,$isresetpassword,$param1,$param2,$param3);
		}
		
		
		return [
                'success' => true,
                'status' => 200,
                'response' => 'success'
            ];
	}
	
	protected function sendUserTemplateMail($wcEmail,$isresetpassword,$param1,$param2='',$param3=false) {
		$user = new WP_User($param1);
		
        if (!$user) {
			
            return [
                'success' => false,
                'response' => 'User not found'
            ];
        }
		$zeptoMailAPI = new ZeptoWoo_Api();
		$fromAddress['address'] = get_option('zeptowoo_from_email_id');
		$fromAddress['name'] = get_option('zeptowoo_from_name');

		$emailObject = new $wcEmail();
		$emailObject->object = $user;
		if($isresetpassword){
			$resetEndpointUrl = wc_get_endpoint_url('lost-password', '', wc_get_page_permalink('myaccount'));
			$user_login = $param1;
			$reset_key = $param2;
			$mergeinfo = [
				'user_login'      => $user_login,
				'user_email'      => $user->user_email,
				'user_reset_url'  => add_query_arg(array('key' => $reset_key, 'id' => $user->ID), $resetEndpointUrl),
				'user_login_url'  => wc_get_page_permalink( 'myaccount' ),
				'site_title'      => $emailObject->get_blogname(),
				'site_url'        => wc_get_page_permalink( 'shop' )
				];
		}
		else {
			$user_id = $param1;
			$user_pass = $param2;
			if(is_array($param2)){
				$user_pass = $param2['user_pass'];
			}
			else {
				$user_pass = $param2;
			}
			$password_generated = $param3;
			$key = get_password_reset_key( $emailObject->object );
			if ( ! is_wp_error( $key ) ) {
				$action                 = 'newaccount';
				$emailObject->set_password_url = wc_get_account_endpoint_url( 'lost-password' ) . "?action=$action&key=$key&login=" . rawurlencode( $user->user_login );
			} else {
				$emailObject->set_password_url = wc_get_account_endpoint_url( 'lost-password' );
			}
			$mergeinfo = [
				'user_login'      => $user->user_login,
				'user_email'      => $user->user_email,
				'user_login_url'  => wc_get_page_permalink( 'myaccount' ),
				'user_reset_url'  => $emailObject->set_password_url,
				'site_title'      => $emailObject->get_blogname(),
				'site_url'        => wc_get_page_permalink( 'shop' )
				];
		}
		$toAddress = [];
		$toAddress = [
                'address' => $user->user_email
            ];
		$zmtoeadd['email_address'] = $toAddress;
		$zmtoearr = array($zmtoeadd);
		$data = array(
				"from"       => $fromAddress,
				"to"         =>  $zmtoearr,
				"merge_info" => wp_json_encode($mergeinfo),
			    "template_key" => $this->getTemplateKeyForWCEmail($wcEmail)
				
				);
				$data['mailagent_key']=get_option('zeptowoo_mail_agent');
				
				
		$responseSending = $zeptoMailAPI->sendTemplateMail($data); 
		
	}
	
	protected function sendUserMail($wcEmail,$isresetpassword,$param1,$param2='',$param3=false) {
		$user = new WP_User($param1);
		
        if (!$user) {
			
            return [
                'success' => false,
                'response' => 'User not found'
            ];
        }

		$emailObject = new $wcEmail();
		$emailObject->object = $user;
		$emailObject->user_email         = stripslashes( $emailObject->object->user_email );
		$emailObject->recipient  = $emailObject->user_email;
		if($isresetpassword){
			$user_login = $param1;
			$reset_key = $param2;
			$emailObject->object     = get_user_by( 'login', $user_login );
			$emailObject->user_id    = $emailObject->object->ID;
			$emailObject->user_login = $user_login;
			$emailObject->reset_key  = $reset_key;
			$emailObject->user_email = stripslashes( $emailObject->object->user_email );
			$emailObject->recipient  = $emailObject->user_email;
			
			
		}else{
			$user_id = $param1;
			$user_pass = $param2;
			if(is_array($param2)){
				$user_pass = $param2['user_pass'];
			}
			else {
				$user_pass = $param2;
			}
			$password_generated = $param3;
			$emailObject->user_pass          = $user_pass;
			$emailObject->user_login         = stripslashes( $emailObject->object->user_login );
			$emailObject->user_email         = stripslashes( $emailObject->object->user_email );
			$emailObject->recipient          = $emailObject->user_email;
			$emailObject->password_generated = $password_generated;
			
			
			$key = get_password_reset_key( $emailObject->object );
			if ( ! is_wp_error( $key ) ) {
				$action                 = 'newaccount';
				$emailObject->set_password_url = wc_get_account_endpoint_url( 'lost-password' ) . "?action=$action&key=$key&login=" . rawurlencode( $emailObject->object->user_login );
			} else {
				$emailObject->set_password_url = wc_get_account_endpoint_url( 'lost-password' );
			}
			
		}
		
		
		add_filter( 'wp_mail_content_type', array( $emailObject, 'get_content_type' ) );

		
		$mail_callback_params = apply_filters( 'woocommerce_mail_callback_params', array( $emailObject->get_recipient(), $emailObject->get_subject(), $emailObject->style_inline($emailObject->get_content()), $emailObject->get_headers(), $emailObject->get_attachments() ), $emailObject );
		
		$fromAddress['address']  = get_option('zeptowoo_from_email_id');
		$fromAddress['name']     = get_option('zeptowoo_from_name');
		$data = array(
				"from"       => $fromAddress,
				"subject"    => $mail_callback_params[1]
				);
		$current_content_type = apply_filters('wp_mail_content_type', '');
		if($current_content_type== 'text/plain'){
			$data["textbody"]   = $mail_callback_params[2];
		}else{
			$data["htmlbody"]   = $mail_callback_params[2];
		}				
		$to = explode( ',', $emailObject->recipient );
		if(!empty($to) && is_array($to)) {
			$tos = array();
			$count = 0;
			foreach($to as $t) {
				$toa = array();
				$toe = array();
				$pos = strpos($t, '<');
				if($pos !== false) {
					$ad = substr($t, $pos+1, strlen($t)-$pos-2);
					$toa['address'] = sanitize_email($ad);
					$toa['name'] = substr($t,0,$pos-1);
				} 
				else {
					$toa['address'] = sanitize_email($t);
				}
				$toe['email_address'] = $toa;
				$tos[$count] = $toe;
				$count = $count + 1;
			}
			$data['to'] = $tos;
		} else {
			$toa = array();
			$tos = array();
			$toa['address'] = $to;
			$tos[0] = $toa;
			$data['to'] = $to;
		}
		$data['mailagent_key']=get_option('zeptowoo_mail_agent');
		$zeptoMailAPI = new ZeptoWoo_Api();
		$responseSending = $zeptoMailAPI->sendMail($data);
		remove_filter( 'wp_mail_content_type', array( $emailObject, 'get_content_type' ) );
	}


	public function handleOrderEmail( $hook,$wcEmail,$param1,$param2) {
		$this->sendOrderMail( $hook,$wcEmail,$param1,$param2);
	}
	public function sendOrderMail( $hook,$wcEmail,$param1,$param2=null) {
		$emailObject = new $wcEmail();
		$customer_note  = null;
		if($wcEmail == 'WC_Email_Customer_Note') {
			$orderid                       = $param1['order_id'];
			
			$customer_note                 = $param1['customer_note'];
			$order                         = wc_get_order($orderid);
			$emailObject->object           = $order;
			$emailObject->customer_note    = $param1['customer_note'];
		}else if($wcEmail == 'WC_Email_Customer_Refunded_Order') {
			$orderid                       = $param1;
			$order                         = wc_get_order($orderid);
			$emailObject->object           = $order;
			$ispartial                     = true;
			if($hook == 'woocommerce_order_fully_refunded_notification'){
				$ispartial=false;
			}
			$order->refund                 = wc_get_order($param2);
			$emailObject->partial_refund   = $ispartial;
			$emailObject->id               = $emailObject->partial_refund ? 'customer_partially_refunded_order' : 'customer_refunded_order';
			
			$refund_detail = array();
			$refund_detail['is_refund'] = true;
			$refund_detail['is_partial'] = $ispartial;
			
		}else{
			$orderid = $param1;
			$order = wc_get_order($orderid);
			$emailObject->object=$order;
		}
		
		if($wcEmail == 'WC_Email_New_Order'){
			$email_already_sent = $order->get_new_order_email_sent();
			if ( $email_already_sent && ! apply_filters( 'woocommerce_new_order_email_allows_resend', false ) ) {
				return;
			}
		}
		$is_admin_email = false;
		if($wcEmail == 'WC_Email_New_Order' || $wcEmail == 'WC_Email_Failed_Order' || $wcEmail == 'WC_Email_Cancelled_Order'){
			$recipient =  $emailObject->get_option( 'recipient', get_option( 'admin_email' ) );
			$is_admin_email = true;
		}
		else {
			$recipient = $order->get_billing_email();
		}
		$emailObject->recipient  = $recipient;
		
		if($wcEmail != 'WC_Email_Customer_Invoice'){
			$sendmail = $emailObject->is_enabled() && !empty($emailObject->get_recipient());
		}else {
			$sendmail = true;
		}
		
		if(!$sendmail){
			return;
		}
		$data = array();
		$zmtoearr = array();
		if($is_admin_email){
			
			foreach ( explode( ',', $recipient ) as $email ) {

                if ( filter_var( trim(  $email ), FILTER_VALIDATE_EMAIL ) ) 
				{
					$toAddress = array(
						'address' => $email
						);
					$zmtoeadd['email_address'] = $toAddress;
					array_push($zmtoearr,$zmtoeadd);
                }
            }
		}
		else{
			$toAddress = array(
						'address' => $order->get_billing_email(),
						'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
						);
					$zmtoeadd['email_address'] = $toAddress;
					array_push($zmtoearr,$zmtoeadd);
		}
		
		$fromAddress['address'] = get_option('zeptowoo_from_email_id');
		$fromAddress['name'] = get_option('zeptowoo_from_name');
		
		$data['to'] = $zmtoearr;
		$data['from'] = $fromAddress;
		
		$data['mailagent_key']=get_option('zeptowoo_mail_agent');
		$zeptoMailAPI = new ZeptoWoo_Api();

		if(!empty(get_option('zeptowoo_cc_email_id'))){
			$ccAddress = [
                'address' => get_option('zeptowoo_cc_email_id')
				];
			$zmcceadd['email_address'] = $ccAddress;
			$zmccearr = array($zmcceadd);
			$data['cc'] = $zmccearr;
		}
		if(!empty(get_option('zeptowoo_bcc_email_id'))){
			$ccAddress = [
                'address' => get_option('zeptowoo_bcc_email_id')
				];
			$zmbcceadd['email_address'] = $bccAddress;
			$zmbccearr = array($zmbcceadd);
			$data['bcc'] = $zmbccearr;
		}
		
		
		
		$templateKey = $this->getTemplateKeyForWCEmail($wcEmail);
		
		if(empty($templateKey)){
			$emailObject->setup_locale();
			$emailObject->recipient                      = $recipient;
			$emailObject->placeholders['{order_date}']   = wc_format_datetime( $emailObject->object->get_date_created() );
			$emailObject->placeholders['{order_number}'] = $emailObject->object->get_order_number();
			add_filter( 'wp_mail_content_type', array( $emailObject, 'get_content_type' ) );
			$mail_callback_params = apply_filters( 'woocommerce_mail_callback_params', array( $emailObject->get_recipient(), $emailObject->get_subject(), $emailObject->style_inline($emailObject->get_content()), $emailObject->get_headers(), $emailObject->get_attachments() ), $emailObject );
			
			$current_content_type = apply_filters('wp_mail_content_type', '');
			
			remove_filter( 'wp_mail_content_type', array( $emailObject, 'get_content_type' ) );
			$data["subject"]    = $mail_callback_params[1];
			if($current_content_type== 'text/plain'){
				$data["textbody"]   = $mail_callback_params[2];
			}else{
				$data["htmlbody"]   = $mail_callback_params[2];
			}
			
			$responseSending = $zeptoMailAPI->sendMail($data);
			
		    $emailObject->restore_locale();
		}
		else{
			$mergeinfo = $this->getPayLoadContent($order,$is_admin_email,$refund_detail,$customer_note);
			$data["merge_info"]    = wp_json_encode($mergeinfo);
			$data["template_key"]      = $templateKey;
			$responseSending = $zeptoMailAPI->sendTemplateMail($data);
			
		}
		if($wcEmail == 'WC_Email_New_Order'){
		$order->update_meta_data( '_new_order_email_sent', 'true' );
				$order->save();
		}
		
	}

	private function getPayLoadContent($order,$is_admin_email,$refund_detail=null,$customer_note = null)
	{
		
	$dp  = wc_get_price_decimals();
	$order_data = array(
			'order_id'                  => $order->get_id(),
			'order_number'              => $order->get_order_number(),
			'created_at'                => wc_format_datetime( $order->get_date_created() ),
			'updated_at'                => wc_format_datetime( $order->get_date_modified()),
			'status'                    => $order->get_status(),
			'currency'                  => $order->get_currency(),
			'currency_symbol'           => get_woocommerce_currency_symbol($order->get_currency()),
			'gross_total'                     => wc_format_decimal( $order->get_total(), $dp ),
			'subtotal'                  => wc_format_decimal( $order->get_subtotal(), $dp ),
			'order_item_quantity'       => $order->get_item_count(),
			'tax_total'                 => wc_format_decimal( $order->get_total_tax(), $dp ),
			'shipping_total'            => wc_format_decimal( $order->get_shipping_total(), $dp ),
			'cart_tax'                  => wc_format_decimal( $order->get_cart_tax(), $dp ),
			'shipping_tax'              => wc_format_decimal( $order->get_shipping_tax(), $dp ),
			'discount_total'            => wc_format_decimal( $order->get_total_discount(), $dp ),
			'shipping_methods'          => $order->get_shipping_method(),
			'payment_details'           => array(
											'method_id'    => $order->get_payment_method(),
											'method_title' => $order->get_payment_method_title(),
											'paid'         => ! is_null( $order->get_date_paid() ),
											'pay_url'      => is_null($order->get_date_paid())?$order->get_checkout_payment_url():'',
											),
			'billing_address'           => array(
											'first_name' => $order->get_billing_first_name(),
											'last_name'  => $order->get_billing_last_name(),
											'company'    => $order->get_billing_company(),
											'address_1'  => $order->get_billing_address_1(),
											'address_2'  => $order->get_billing_address_2(),
											'city'       => $order->get_billing_city(),
											'state'      => WC()->countries->get_states($order->get_billing_country() )[$order->get_billing_state()],
											'postcode'   => $order->get_billing_postcode(),
											'country'    => $order->get_billing_country(),
											'email'      => $order->get_billing_email(),
											'phone'      => $order->get_billing_phone()
											),
			'shipping_address'           => array(
											 'first_name' => $order->get_shipping_first_name(),
											 'last_name'  => $order->get_shipping_last_name(),
											 'company'    => $order->get_shipping_company(),
											 'address_1'  => $order->get_shipping_address_1(),
											 'address_2'  => $order->get_shipping_address_2(),
											 'city'       => $order->get_shipping_city(),
											 'state'      => WC()->countries->get_states($order->get_shipping_country() )[$order->get_shipping_state()],
											 'postcode'   => $order->get_shipping_postcode(),
											 'country'    => $order->get_shipping_country()
											 ),
			'customer_note'             => $customer_note,
			'customer_ip'               => $order->get_customer_ip_address(),
			'customer_user_agent'       => $order->get_customer_user_agent(),
			'customer_id'               => $order->get_user_id(),
			'order_view_url'            => $order->get_view_order_url(),
			'order_items'               => array(),
			'order_refunds'             => array()

		);
		
		
				
		foreach ( $order->get_items() as $item_id => $item ) {
			$product    = $item->get_product();
			$hideprefix = '_';
			$item_meta  = $item->get_all_formatted_meta_data( $hideprefix );

			foreach ( $item_meta as $key => $values ) {
				$item_meta[ $key ]->label = $values->display_key;
				unset( $item_meta[ $key ]->display_key );
				unset( $item_meta[ $key ]->display_value );
			}
			$productImg = $this->getProductImage($product);
			

			$order_item = array(
				'id'           => $item_id,
				'subtotal'     => wc_format_decimal( $order->get_line_subtotal( $item, false, false ), $dp ),
				'tax_subtotal' => wc_format_decimal( $item->get_subtotal_tax(), $dp ),
				'gross_total'        => wc_format_decimal( $order->get_line_total( $item, false, false ), $dp ),
				'tax_total'    => wc_format_decimal( $item->get_total_tax(), $dp ),
				'price'        => wc_format_decimal( $order->get_item_total( $item, false, false ), $dp ),
				'quantity'     => $item->get_quantity(),
				'tax_class'    => $item->get_tax_class(),
				'name'         => $item->get_name(),
				'product_id'   => $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id(),
				'sku'          => is_object( $product ) ? $product->get_sku() : null,
				'image'        => $productImg,
				'url'          => $product->get_permalink()
				
			);
			$refund_item_qty = $order->get_qty_refunded_for_item( $item_id );
			$refund_item_tot = $order->get_total_refunded_for_item( $item_id );
			if($refund_item_qty && $refund_item_qty != 0){
				$order_item['refunded_qty'] = $order->get_qty_refunded_for_item( $item_id )*-1;
				$order_item['net_qty'] = $order_item['quantity'] + $order->get_qty_refunded_for_item( $item_id );
			}
			else {
				$order_item['net_qty'] = $order_item['quantity'];
			}
			if($refund_item_tot && $refund_item_tot != 0){
				$order_item['refunded_total'] = $order->get_total_refunded_for_item( $item_id );
				$order_item['net_total'] = $order_item['gross_total'] - $order->get_total_refunded_for_item( $item_id );
			}
			else {
				$order_item['net_total'] = $order_item['gross_total'];
			}
			$order_data['order_items'][] = $order_item;
			
		}
		
		$order_refunds = $order->get_refunds();
		
		foreach( $order_refunds as $refund ){
			
			foreach( $refund->get_items() as $item_id => $item ){
				$order_data['order_refunds'][] = array(
				'quantity'           => $item->get_quantity(),
				'total'    => $item->get_total());
			}
			
		}

		// Add coupons.
		foreach ( $order->get_items( 'coupon' ) as $coupon_item_id => $coupon_item ) {
			$coupon_line = array(
				'id'     => $coupon_item_id,
				'code'   => $coupon_item->get_code(),
				'amount' => wc_format_decimal( $coupon_item->get_discount(), $dp ),
			);
			$order_data['coupon_lines'][] = $coupon_line;
		}
		$order_total     = $order->get_total();
        $total_refunded  = $order->get_total_refunded();
        $tax_string      = '';
		if ( $total_refunded ){
			$order_data['net_total'] = $order_total - $total_refunded;
		}else{
			$order_data['net_total'] = $order_total;		 
		}
		if($refund_detail){
			$order_data['is_refund'] = true;
			$order_data['is_partial'] = $refund_detail['is_partial'];
		}
		if($customer_note){
			$order_data['customer_note'] = $customer_note;
		}
		if($is_admin_email){
			$order_data['order_editurl']=$order->get_edit_order_url();
		}
		
		return $order_data;	

	}



	private function getProductImage($product, $size = 'thumbnail')
    {
        if ($product->get_image_id()) {

            $image = wp_get_attachment_image_src( $product->get_image_id(), $size, false );
            list( $src, $width, $height ) = $image;

            return $src;
        } else if ($product->get_parent_id()) {

            $parentProduct = wc_get_product( $product->get_parent_id() );
            if ( $parentProduct ) {

                return $this->getProductImage($parentProduct, $size);
            }
        }

        return null;
    }
	
	private function getTemplateKeyForWCEmail($wcEmail){
		$templatekey = str_replace("WC_Email_","",$wcEmail);
		return get_option("zeptowoo_".$templatekey);
	}

	


}


endif;

