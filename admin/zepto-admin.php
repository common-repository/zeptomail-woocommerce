<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if (isset($_POST['zeptowoo_submit']) && !empty($_POST)) {
      $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
      if (!wp_verify_nonce($nonce, 'zeptowoo_settings_nonce')) {
                  echo esc_html('<div class="error"><p><strong>'.esc_html__('Reload the page again','zeptomail-woocommerce').'</strong></p></div>\n');
                } 
                else {
        $zeptowoo_client_id = sanitize_text_field($_POST['zeptowoo_client_id']);
        $zeptowoo_client_secret = sanitize_text_field($_POST['zeptowoo_client_secret']);
        
        $zeptowoo_domain_name = sanitize_text_field($_POST['zeptowoo_domain_name']);
        
		$state=base64_encode(implode(":", array($zeptowoo_client_id, $zeptowoo_client_secret,$zeptowoo_domain_name,$nonce)));
		 
         ?>
         <head> <meta http-equiv="refresh" content="0; url=<?php $completeRedirectUrl=esc_url(admin_url().'admin.php?page=zeptomail-config'); $test="https://accounts.".ZeptoWoo_Helper::$domainMapping[$zeptowoo_domain_name]."/oauth/v2/auth?response_type=code&client_id=".$zeptowoo_client_id."&scope=Zeptomail.MailTemplates.READ,Zeptomail.email.CREATE,Zeptomail.mailagent.read,Zeptomail.domains.read,zeptomail.domain.read&redirect_uri=".$completeRedirectUrl."&prompt=consent&access_type=offline&state=".$state; echo esc_url($test);?>"/> </head>

		 
         <?php
		 return;

    }
    }
	$zeptowoo_variables = array();
	$zeptowoo_variables['order'] = wc_help_tip("<b>You can use the following variables:<br/>
                                            <p class='unset-fontsize'>
                                                {{order_number}}<br/>
                                                {{order_date}}<br/>
                                                {{net_total}}<br/>
                                                {{total_discount}}<br/>
                                                {{total_shipping}}<br />
                                                {{shipping_methods}}<br/>
                                                {{payment_details.method_title}}<br/>
                                            </p>");
	$zeptowoo_variables['user'] = wc_help_tip("<b>You can use the following variables:<br/>
                                            <p class='unset-fontsize'>
                                                {{site_title}}
												{{site_url}}
												{{user_login}}
												{{user_reset_url}}<br/>
                                            </p>");											

	if(isset($_GET['code'])) {
		$completeRedirectUrl=esc_url(admin_url().'admin.php?page=zeptomail-config');
		$state=explode(":",base64_decode(wp_kses_post($_GET['state'])));
		$zeptowoo_client_id = $state[0];
		$zeptowoo_client_secret = $state[1];
		$zeptowoo_domain_name = $state[2];
		$zeptowoo_nonce = $state[3];
		
		$url = "https://accounts.".ZeptoWoo_Helper::$domainMapping[$zeptowoo_domain_name]."/oauth/v2/token?code=".wp_kses_post($_GET['code'])."&client_id=".$zeptowoo_client_id."&client_secret=".$zeptowoo_client_secret."&redirect_uri=".$completeRedirectUrl."&grant_type=authorization_code&state=".wp_kses_post($_GET['state']);
		
		$responseSending = wp_remote_post( $url, array() );
		$body = json_decode(wp_remote_retrieve_body( $responseSending ),true);
		
		$zeptowoo_refresh_token = $body["refresh_token"];
		$zeptowoo_access_token = $body["access_token"];
		update_option('zeptowoo_client_id',base64_encode($zeptowoo_client_id), false);
		update_option('zeptowoo_client_secret',base64_encode($zeptowoo_client_secret), false);
		update_option('zeptowoo_refresh_token',base64_encode($zeptowoo_refresh_token), false);
		update_option('zeptowoo_access_token',base64_encode($zeptowoo_access_token), false);
		update_option('zeptowoo_domain_name',$zeptowoo_domain_name, false);
		update_option('zeptowoo_timestamp',time(), false);
		flush_rewrite_rules();
		if(get_option('zeptowoo_mail_agent') != null){
			$zeptoAPI = new ZeptoWoo_Api();
			$resp = json_decode($zeptoAPI->getMailAgents(),true);

			$zeptowoo_mail_agent = get_option('zeptowoo_mail_agent');
			$isAgentAvailable =false;

			$mailagents = $resp["data"];
			if($mailagents != null){

				foreach($mailagents as $mailagent)
				{
					if(!empty($zeptowoo_mail_agent) && strcmp($mailagent["mailagent_key"],$zeptowoo_mail_agent)==0){
						$isAgentAvailable = true;
					}
				}
			}
			if(!$isAgentAvailable)
			{
				delete_option('zeptowoo_mail_agent');
				delete_option('zeptowoo_from_email_id');
				delete_option('zeptowoo_from_name');
				delete_option('zeptowoo_cc_email_id');
				delete_option('zeptowoo_bcc_email_id');
				foreach(ZeptoWoo_Helper::$zeptoWCTriggerMapping as $zeptoTriggerKey => $zeptoTriggerValue){
					delete_option('zeptowoo_'.$zeptoTriggerKey);
				}
			}
			
		}
		else {
			$isAgentAvailable = true;
		}
		?>
         <head> <meta http-equiv="refresh" content="0; url=<?php echo esc_url($completeRedirectUrl);?>"/> </head>
		 
         <?php
		return;
	}
	$is_mail_configured =false;
$is_account_configured =false;
if(!empty(get_option('zeptowoo_refresh_token')))
{
		
		$zeptoAPI = new ZeptoWoo_Api();
		$resp = json_decode($zeptoAPI->getMailAgents(),true);

		$mailagents = $resp["data"];

		if(!empty(get_option('zeptowoo_from_email_id'))){
			$is_mail_configured = true;
		}
	$is_account_configured =true;	
}
	?>

    <div class="zepto-logo">

        <i class="zeptoi-logo"></i>
        <div>
			<h2>Zoho</h2>
            <h2>ZeptoMail</h2>
            <span>by Zoho Mail</span>
        </div>
    </div>
	<div class="zepto-settings">
    <div class="zepto-main">
		<div purpose="accordion-box" id="zepto_account_setup_ab" is_configured="<?php echo esc_attr($is_account_configured);?>" class="accordion-body   <?php if(!$is_account_configured){?>zepto-accordion-active<?php }else{?> zepto-accordion-inactive<?php } ?>">
        <button purpose="configure-accordion" class="zeptoaccordion__trigger <?php if(!$is_account_configured){?>zeptoaccordion__trigger--expanded<?php }else{?> zeptoaccordion__trigger--configured<?php } ?>">
            <span class="zeptoaccordion__title">
			<?php echo esc_html__( 'Configure Account' ,'zeptomail-woocommerce');?>
            </span>
			<?php if($is_account_configured){?>
			<span class="zeptosetup-status zeptosetup-status--completed">
                <i class="zeptoi-circle-check"></i>
                Authorised
            </span>
			<?php } ?>
            <i class="zeptoi-angle-down"></i>
        </button>
        <form method="post" action="<?php echo esc_attr($_SERVER["REQUEST_URI"]); ?>" class="zeptoaccordion__content">
			<?php wp_nonce_field('zeptowoo_settings_nonce'); ?>
            <span class="zeptosetup-step-desc">
				<?php echo esc_html__( 'You can configure your ZeptoMail account in this plugin to send transactional emails from WooCommerce. Refer this document to generate client ID and client secret.' ,'zeptomail-woocommerce');?>
                
                <span>
                    <button class="zeptobtn zeptobtn--flat zeptobtn--sm">
                        <span class="zeptobtn__text "><?php echo esc_html__( ' Help doc' ,'zeptomail-woocommerce');?></span>
                    </button>
                </span>
            </span>
            <div class="zeptoform-element">
                <label class="zeptoinput-label"><?php echo esc_html__( 'Where is your account hosted?' ,'zeptomail-woocommerce');?></label>
                <div class="zeptotext zeptoselect">
                    <div class="zeptotext-field-wrapper">
                        
                        <div class="zeptotext__adorn "  style="width:100%;"><select class="form--input form--input--select" name="zeptowoo_domain_name" <?php if($is_account_configured){?> disabled <?php }?>>
                        <option value="com" <?php if(get_option('zeptowoo_domain_name') == "com") {?> selected="true"<?php } ?>>zeptomail.zoho.com</option>
                        <option value="eu" <?php if(get_option('zeptowoo_domain_name') == "eu") {?> selected="true"<?php } ?>>zeptomail.zoho.eu</option>
                        <option value="in" <?php if(get_option('zeptowoo_domain_name') == "in") {?> selected="true"<?php }?>>zeptomail.zoho.in</option>
                        <option value="com.cn" <?php if(get_option('zeptowoo_domain_name') == "com.cn") {?>selected="true"<?php }?>>zeptomail.zoho.com.cn</option>
                        <option value="com.au" <?php if(get_option('zeptowoo_domain_name') == "com.au"){?>selected="true"<?php }?>>zeptomail.zoho.com.au</option>
						<option value="jp" <?php if(get_option('zeptowoo_domain_name') == "com.au"){?>selected="true"<?php }?>>zeptomail.zoho.jp</option>
						<option value="zohocloud.ca" <?php if(get_option('zeptowoo_domain_name') == "com.au"){?>selected="true"<?php }?>>zeptomail.zohocloud.ca</option>
						<option value="sa" <?php if(get_option('zeptowoo_domain_name') == "com.au"){?>selected="true"<?php }?>>zeptomail.zoho.sa</option>
                    </select></div>
                    </div>
                </div>
            </div>
            <div class="zeptoform-element">
                <label class="zeptoinput-label">
                    <?php echo esc_html__( 'Client ID' ,'zeptomail-woocommerce');?>
                </label>
                <div class="zeptotext">
                    <div class="zeptotext-field-wrapper">
						<input class="zeptotext__box" type="text" value="<?php echo esc_attr(base64_decode(get_option('zeptowoo_client_id'))) ?>" name="zeptowoo_client_id" <?php if($is_account_configured){?> disabled <?php }?> placeholder="<?php echo esc_attr(__( 'Enter valid client ID' ,'zeptomail-woocommerce'));?>"  id="zeptowoo_client_id" required/>
                        
                    </div>
                </div>
            </div>
            <div class="zeptoform-element">
                <label class="zeptoinput-label">
                    <?php echo esc_html__( 'Client Secret' ,'zeptomail-woocommerce');?>
                </label>
                <div class="zeptotext">
                    <div class="zeptotext-field-wrapper">
						<input type="password" value="<?php echo esc_attr(base64_decode(get_option('zeptowoo_client_secret'))) ?>" name="zeptowoo_client_secret" class="zeptotext__box" id="zeptowoo_client_secret" <?php if($is_account_configured){?> disabled <?php }?> placeholder="<?php echo esc_attr__( 'Enter valid client secret' ,'zeptomail-woocommerce');?>"  required/>
                        
                    </div>
                </div>
            </div>
            <div class="zeptoform-element">
                <label class="zeptoinput-label">
                   <?php echo esc_html__( 'Authorization redirect URL' ,'zeptomail-woocommerce');?>
                </label>
                <div class="zeptotext">
                    <div class="zeptotext-field-wrapper">
						<input type="text" id="zeptowoo_authorization_uri" readonly="readonly" name="zeptowoo_authorization_uri" class="zeptotext__box" value="<?php echo esc_url(admin_url().'admin.php?page=zeptomail-config'); ?>" class="regular-text" readonly="readonly" required/>
                        
                    </div>
                </div>
				<i class="form__row-info"><?php echo esc_html__( 'Copy this URL into Redirect URI field of your Client Id creation' ,'zeptomail-woocommerce');?>  </i>
				<i class="tib-copy" ><a href="javascript:" purpose="copyredirecturi"><?php echo esc_html__( 'Copy text' ,'zeptomail-woocommerce');?></a></i>
            </div>
			
            <span class=" <?php if($is_account_configured){?> zepto-dispNone <?php }?>">
                <input type="submit"  name="zeptowoo_submit" class="zeptobtn" value="Configure"/>
                    
            </span>
				
            <span class="zeptosetup-step-desc <?php if(!$is_account_configured){?> zepto-dispNone <?php }?>">
                <?php echo esc_html__( 'Wish to authorize with other data?' ,'zeptomail-woocommerce');?>
                <button class="zeptobtn zeptobtn--flat zeptobtn--sm" purpose="reauthorize">
                    <span class="zeptobtn__text "><?php echo esc_html__( 'Edit' ,'zeptomail-woocommerce');?></span>
                </button>
            </span>
				
        </form>
        </div>
		<div purpose="accordion-box" id="zepto_send_mail_config_ab" is_configured="<?php echo esc_attr($is_account_configured && $is_mail_configured);?>" class="accordion-body <?php if( $is_account_configured){ if(!$is_mail_configured) {?>zepto-accordion-active<?php }else{?> zepto-accordion-inactive<?php }}else{?>zepto-accordion-inactive zepto-accordion-disabled<?php } ?>" >
		<button purpose="configure-accordion" class="zeptoaccordion__trigger <?php if(!$is_mail_configured){?>zeptoaccordion__trigger--expanded<?php }else{?> zeptoaccordion__trigger--configured<?php } ?>">
            <span class="zeptoaccordion__title">
                <?php echo esc_html__( 'Email sending configuration' ,'zeptomail-woocommerce');?>
            </span>
			<span class="zeptosetup-status zeptosetup-status--completed <?php if(!$is_mail_configured){?>zepto-dispNone<?php }?>">
                <i class="zeptoi-circle-check"></i>
                Confgiured
            </span>
			<i class="zeptoi-angle-down"></i>
        </button>
		<?php 
			if($is_account_configured)
			{
		?>
		<form id="mailconfigform" method="post" action="<?php echo esc_attr($_SERVER["REQUEST_URI"]); ?>"  class="zeptoaccordion__content">
			<?php wp_nonce_field('zeptowoo_email_submit_nonce'); ?>
            <span class="zeptosetup-step-desc">
                <?php echo esc_html__( 'Choose the Mail Agent and From address you wish to send emails from.' ,'zeptomail-woocommerce') ;?>
            </span>
			
            <div class="zeptoform-element">
                <label class="zeptoinput-label"><?php echo esc_html__( 'Mail Agent' ,'zeptomail-woocommerce'); ?></label>
                <div class="zeptotext zeptoselect">
                    <div class="zeptotext-field-wrapper">

						<?php 
						if(!empty(get_option('zeptowoo_refresh_token')))
						{
							$zeptoAPI = new ZeptoWoo_Api();
							$resp = json_decode($zeptoAPI->getMailAgents(),true);
							$mailagents = $resp["data"];
						?>

						<select id="zeptowoo_mail_agent" value="<?php echo esc_attr(get_option('zeptowoo_mail_agent')) ?>">
						<?php
							foreach($mailagents as $mailagent)
							{
						?>
							<option value="<?php echo esc_attr($mailagent["mailagent_key"]);?>"><?php echo esc_html($mailagent["mailagent_name"]);?></option>
						<?php
							}
						?>
				
			</select><?php
                         }
						?>
						
                    </div>
                </div>
            </div>
			<div class="zeptoform-element">
                
                <label class="zeptoinput-label">
                    <?php echo esc_html__( 'From Name' ,'zeptomail-woocommerce');?>
                </label>
                <div class="zeptotext">
                    <div class="zeptotext-field-wrapper">
                        <input type="text" name="zeptowoo_from_name" id="zeptowoo_from_name" class="zeptotext__box" value="<?php echo esc_attr(get_option("zeptowoo_from_name"));?>"/>
                    </div>
                </div>
            </div>
			

            <div class="zeptoform-element" purpose="zepto-email-form">
                
                <label class="zeptoinput-label">
                    <?php echo esc_html__( 'From Address' ,'zeptomail-woocommerce');?>
					
                </label>
                <div class="zeptotext">
                    <div class="zeptotext-field-wrapper">
					
                        <input type="text" name="zeptowoo_from_email_id" id="zeptowoo_from_email_id" class="zeptotext__box" value="<?php echo esc_attr(get_option("zeptowoo_from_email_id"));?>" placeholder="<?php echo esc_attr(__( 'Enter email address' ,'zeptomail-woocommerce'));?>"/>
                    </div>
                </div>
				<p class="zeptoinput-helptext zeptoinput-helptext--error zepto-dispNone" purpose="email-error-text" style="display: none;">    
                        </p>
            </div>
            <div class="zeptoform-element-block zeptoform-element-block--column <?php if(empty(get_option("zeptowoo_cc_email_id"))){ ?> zepto-checkbox-inactive <?php }?>">
                <label class="zeptoinput-control-label">
                    <input purpose="zepto-woo-mail-config" name="zepto-woo-cc-checkbox" type="checkbox" <?php if(!empty(get_option("zeptowoo_cc_email_id"))){ ?> checked <?php }?>>
                    <span>
                        <?php echo esc_html__( 'Add CC' ,'zeptomail-woocommerce');?>
                    </span>
                </label>
                <div class="zeptoform-element" purpose="zepto-email-form">
                    <div class="zeptotext">
                        <div class="zeptotext-field-wrapper">
							<input type="text" name="zeptowoo_cc_email_id" class="zeptotext__box" placeholder="<?php echo esc_attr(__( 'Enter email address' ,'zeptomail-woocommerce'));?>" value="<?php echo esc_attr(get_option("zeptowoo_cc_email_id"));?>"/>
                        </div>
                    </div>
					<p class="zeptoinput-helptext zeptoinput-helptext--error zepto-dispNone" purpose="email-error-text" style="display: none;">    
                        </p>
                    
                </div>
            </div>
			
            <div class="zeptobtn-block">
				<input type="button" class="zeptobtn" id="zepto_save_config" name="zeptowoo_email_submit" class="zeptobtn" value="<?php echo esc_attr(__( 'Save' ,'zeptomail-woocommerce'));?>"/>
               
              
            </div>
       
        </form>
		 <?php
		 }
		 ?>
		 </div>
		<div purpose="accordion-box" id="zepto_email_config_ab" class="accordion-body  <?php if($is_account_configured && $is_mail_configured){?>zepto-accordion-active<?php }else{?>zepto-accordion-inactive zepto-accordion-disabled<?php } ?>">
		<button purpose="configure-accordion" class="zeptoaccordion__trigger zeptoaccordion__trigger--expanded">
            <span class="zeptoaccordion__title">
                <?php echo esc_html__( 'Email settings' ,'zeptomail-woocommerce');?>
            </span>
			<i class="zeptoi-angle-down"></i>
        </button>
		<div class="zeptoaccordion__content" purpose="mail_content_confiugration">
            <span class="zeptosetup-step-desc">
                <?php echo esc_html__( 'Choose the content of the email that will be sent when each of these events occur.' ,'zeptomail-woocommerce');?>
            </span>
			
			<?php wp_nonce_field('zeptowoo_template_nonce'); ?>
			<?php
			
			foreach(ZeptoWoo_Helper::$zeptoWCTriggerMapping as $zeptoTriggerKey => $zeptoTriggerValue)
			{
				$template_id = get_option('zeptowoo_'.$zeptoTriggerKey);
				?>
				<div class="zeptoform-element-block zeptoform-element-block--column" purpose="mail_template_config" template_name="<?php echo esc_attr($zeptoTriggerKey);?>">
                <fieldset class="zeptoform-element">
                    <legend class="zeptoinput-label">
                        <?php echo esc_html($zeptoTriggerValue['label']);?>
                        <?php echo wp_kses($zeptowoo_variables['order'] , true ); ?>
                    </legend>
                    <div class="zeptoinput-group">
                        <label class="zeptoinput-control-label">
                            <input type="radio" name="<?php echo esc_attr($zeptoTriggerKey);?>" mail_type="woo_mail" checked >
                            <span class="zeptoinput-control-label__text">
                                <?php echo esc_html__( 'Default email' ,'zeptomail-woocommerce');?>
                            </span>
                        </label>
                        <label class="zeptoinput-control-label">
                            <input type="radio" name="<?php echo esc_attr($zeptoTriggerKey);?>" <?php if(!empty($template_id)) { ?> checked <?php }?>>
                            <span class="zeptoinput-control-label__text">
                                <?php echo esc_html__( 'Template email' ,'zeptomail-woocommerce');?>
                            </span>
                        </label>
                    </div>
                </fieldset>
                <div class="zeptoform-element-block" purpose="zepto-template-form" <?php if(empty($template_id)) { ?> style="display:none;" <?php }?>>
                    <div class="zeptoform-element ">
                        <div class="zeptotext ">
                            <div class="zeptotext-field-wrapper">
								<input type="text" class="zeptotext__box template_key" purpose="zepto_template_input" placeholder="<?php echo esc_attr__( 'Enter template key' ,'zeptomail-woocommerce');?>" template_key="<?php echo esc_attr($zeptoTriggerKey);?>" value="<?php echo esc_attr($template_id); ?>" />
                            </div>
                        </div>
                        
						<p class="zeptoinput-helptext zeptoinput-helptext--error zepto-dispNone" purpose="template-error-text">
                            
                        </p>
                    </div>
					
					
                    <button class="zeptobtn zeptobtn--flat zeptobtn--outlined" purpose="zepto_template_validate">
                        <span class="zeptobtn__text " ><?php echo esc_html__( 'Save' ,'zeptomail-woocommerce');?></span>
                    </button>
                </div>
            </div>
				<?php 
			}
			?>
			
            
        </div>
		</div>
	</div>
	<div class="zeptopopover" style="display:none" id="zeptoalert">
        <header class="zeptopopover__header">
            <h3 purpose="alertheader"></h3>
            <div class="zeptopopover__header__actions">
                <button class="zeptobtn zeptobtn--default zeptobtn--sm" purpose="clsbtn">
                    <i class="zeptoi-close"></i>
                </button>
            </div>
        </header>
        <div class="zeptopopover__content" purpose="alertcontent">
            
        </div>
    </div>
    </div>
