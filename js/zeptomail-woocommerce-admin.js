(function($) {
    'use strict';

    $(window).load(function() {
		$('[purpose="configure-accordion"]').click(function(e){
			var $self = $(this);
			$("#zeptowooalert").remove();
			var $activeForm = $self.parent().siblings(".accordion-body");
			if(!$self.closest('[purpose=accordion-box]').hasClass('zepto-accordion-disabled')){
				$.each($activeForm,function(index,obj){
					if($(obj).attr("is_configured") === 1){
						$(obj).find('[purpose=configure-accordion]').addClass("zeptoaccordion__trigger--configured");
					}
				});
				
				$self.removeClass("zeptoaccordion__trigger--collapsed  zeptoaccordion__trigger--configured").addClass("zeptoaccordion__trigger--expanded");
				$self.parent().siblings(".accordion-body").removeClass("zepto-accordion-active").addClass("zepto-accordion-inactive");
				$self.parent(".accordion-body").removeClass("zepto-accordion-inactive").addClass("zepto-accordion-active");
			}
			
		});
		$('[purpose="zepto-woo-mail-config"]').click(function(e){
			var $self = $(this);
			
			if($self.is(":checked"))
			{
				$self.closest(".zeptoform-element-block--column").removeClass("zepto-checkbox-inactive").addClass("zepto-checkbox-active");
			}
			else
			{
				$self.closest(".zeptoform-element-block--column").removeClass("zepto-checkbox-active").addClass("zepto-checkbox-inactive");
			}
			
			
		});
        $('[purpose=zepto_template_validate]').click(function(e) {
			e.preventDefault();
			$("#zeptowooalert").remove();
			var $templateForm = $(this).closest('[purpose=zepto-template-form]');
			var $input = $templateForm.find(".template_key")[0];
			$self = $(this);

            var templatename = $($input).attr("template_key");
			var templatekey = $($input).val();
            var data = {
                action: "zeptomail_validate_template",
                template_name: templatename,
				template_key:  templatekey 
            }
			data['_wpnonce']=$("#zepto_email_config_ab").find("[name=_wpnonce]").val();
			data['_wp_http_referer']=$("#zepto_email_config_ab").find("[name=_wp_http_referer]").val();
			var $self = $(this);

            $.post(ajaxurl, data).done(function(data) {
				data = $.parseJSON(data);
				if(data.result == "warning")
				{
					var $zeptoalert = $("#zeptoalert").clone();
					$zeptoalert.attr("id","zeptowooalert");
					if(Object.keys(data.required).length >0){
						$zeptoalert.find("[purpose=alertcontent]").html(data.errorMsg);
						$("#zepto_email_config_ab").append($zeptoalert);
						$("#zeptowooalert").offset($self.position());
						$("#zeptowooalert").find("[purpose=clsbtn]").on('click', function () {
							$zeptoalert.slideUp('fast', function () {
								$zeptoalert.remove();
							});
						});
						var $varContent = $("<b>");
						
						$.each( data.required, function( key, value ) {
							if(typeof(value) === "string"){
								$varContent.append("{{"+value+"}}<br/>");
							}
							if(typeof(value) === "object"){
								$varContent.append("{{#"+key+"}}<br/>");
								$.each( value, function( objkey, objvalue ){
									$varContent.append(" <span class='zepto-tab-space'> {{this."+objvalue+"}}</span><br/>");
								});
								$varContent.append("{{/"+key+"}}<br/>");
							}
						});
						$("#zeptowooalert").find(".zeptopopover__content").append($varContent);
						$zeptoalert.show();
					}
					
				}
				else if(data.result !== "success")
				{
					var error = '<span>'+data.errorMsg+'<span>';
					
					$templateForm.find('[purpose=template-error-text]').html(error).show().addClass("zeptoinput-helptext--error");
					
					
				}
				else{
					var error = '<span>Saved successfully<span>';
					$templateForm.find('[purpose=template-error-text]').html(error).show().addClass("zeptoinput-helptext--success").removeClass("zeptoinput-helptext--error");
					
				}
				
				 
                
            });
        });
		$('#zepto_save_config').click(function(e) {
			e.preventDefault();
			$("[purpose=email-error-text]").hide();
			var $self= $(this);
			var zeptoMailAgent = $("#zeptowoo_mail_agent").val();
			var zeptoFromAddress = $("#zeptowoo_from_email_id").val();
			var zeptoFromName = $("#zeptowoo_from_name").val();
			if(!zeptoValidateEmail(zeptoFromAddress)) {
				var $emailForm = $("#zeptowoo_from_email_id").closest('[purpose=zepto-email-form]');
				var error = '<span>Invalid email<span>';
				$emailForm.find('[purpose=email-error-text]').html(error).show();
				return;
			}
			

            var data = {
                action: "zeptomail_save_mailagent",
                zeptowoo_mail_agent: zeptoMailAgent,
				zeptowoo_from_email_id: zeptoFromAddress,
				zeptowoo_from_name: zeptoFromName
            }
			data['_wpnonce']=$("#mailconfigform").find("[name=_wpnonce]").val();
			data['_wp_http_referer']=$("#mailconfigform").find("[name=_wp_http_referer]").val();
			
			if($("[name=zepto-woo-cc-checkbox]").is(":checked")){
				if(!zeptoValidateEmail($("[name=zeptowoo_cc_email_id]").val())) {
					var $emailForm = $("[name=zeptowoo_cc_email_id]").closest('[purpose=zepto-email-form]');
					var error = '<span>Invalid email<span>';
					$emailForm.find('[purpose=email-error-text]').html(error).show();
					return;
				}
				data['zeptowoo_cc_email_id'] = $("[name=zeptowoo_cc_email_id]").val();
			}
		
            $.post(ajaxurl, data).done(function(data) {
				data = $.parseJSON(data);
				
				
				if(data.result === "failure"){
					
					var notice = $('<div class="notice notice-error"><p>' + data.errorMsg + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');
					$('#wpbody-content').prepend(notice);
					$(".wp-toolbar").scrollTop(0);
					notice.on('click', '.notice-dismiss', function () {
						notice.slideUp('fast', function () {
								notice.remove();
							});
						});

				}
				else{
					var notice = $('<div class="notice is-dismissible"><p>Plugin configured successfully</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');
					$('#wpbody-content').prepend(notice);
					notice.on('click', '.notice-dismiss', function () {
						notice.slideUp('fast', function () {
								notice.remove();
							});
						});
					$("#zepto_send_mail_config_ab").addClass("zepto-accordion-inactive").removeClass("zepto-accordion-active");
					$("#zepto_send_mail_config_ab").find("[purpose=configure-accordion]").addClass("zeptoaccordion__trigger--configured").removeClass("zeptoaccordion__trigger--expanded");
					$("#zepto_email_config_ab").addClass("zepto-accordion-active").removeClass("zepto-accordion-inactive zepto-accordion-disabled");
					$("#zepto_email_config_ab").find("[purpose=configure-accordion]").removeClass("zeptoaccordion__trigger--collapsed").addClass("zeptoaccordion__trigger--expanded");
					$(".wp-toolbar").scrollTop(0);
				}
                
            });
        });
		$('[purpose=mail_content_confiugration] [type=radio]').click(function(){
			var $self = $(this);
			var $mail_type = $self.attr("mail_type")
			if($mail_type && $mail_type === "woo_mail"){
				var cnfrm = confirm('Are you sure?');
				if(cnfrm != true)
				{
					return false;
				}
				else {
					var $input = $(this).parent('[purpose=zepto-template-form]').find(".template_key")[0];
					var templatename = $($input).attr("template_name");
					var $temp_config = $self.closest("[purpose='mail_template_config']");
					var data = {
						action: "zeptomail_reset_template",
						template_name:  $temp_config.attr("template_name")
						}
					data['_wpnonce']=$("#zepto_email_config_ab").find("[name=_wpnonce]").val();
					data['_wp_http_referer']=$("#zepto_email_config_ab").find("[name=_wp_http_referer]").val();
					$self.closest('[purpose=mail_template_config]').find('[purpose=zepto-template-form]').hide();
					$.post(ajaxurl, data).done(function(data) {
						
					});
				}
			}
			else{
				$self.closest('[purpose=mail_template_config]').find('[purpose=zepto-template-form]').show();
			}
			
		});
		$('[purpose=reauthorize]').click(function(e) {
			e.preventDefault();
			$("[name=zeptowoo_domain_name]").removeAttr("disabled");
			$("#zeptowoo_client_id").removeAttr("disabled");
			$("#zeptowoo_client_secret").removeAttr("disabled");
			$("[name=zeptowoo_submit]").parent().removeClass("zepto-dispNone");
			$(this).addClass("zepto-dispNone");
        });
		$('[purpose=zepto_template_input]').focus(function(){
			var $templateForm = $(this).closest('[purpose=zepto-template-form]');
			$templateForm.find('[purpose=template-error-text]').hide();
		});
		function zeptoValidateEmail(email) {
			var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if(!regex.test(email)) {
           return false;
        }else{
           return true;
        }
      }
      $('[purpose=copyredirecturi]').click(function(e) {
            var copyText = document.getElementById('zeptowoo_authorization_uri');
            		copyText.select();
            		copyText.setSelectionRange(0, copyText.value.length);
            		document.execCommand('copy');
      });

    });
	
	
})(jQuery);