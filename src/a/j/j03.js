$j=jQuery.noConflict();

function listenInterface(){
	$j('form.ajax').submit(function(){
		var form_id=$j(this).attr('id');
		$j('#'+form_id+' li').removeClass('err');
		$j.post(
			$j(this).attr('action'),
			$j(this).serialize(),
			function(data){
				if(data['result']=='error'){
					for(var key in data['errors']){
						$j('#'+form_id+' #id_'+key).parent().addClass('err');
						alert( data['errors'][key]);
					}
				}else if(data['result']=='action'){
					eval(data['action'])(data['params']);
				}else if(data['result']=='message'){
					alert(data['message']);
				}else if(data['result']=='redirect'){
					document.location.href=data['url'];
				}
				if(data['close']){
					$j('#'+form_id).parent('.interface').hide();
				}
			},
			"json");
		return false;
	});
}

$j().ready(function(){

	//Показать панель авторизации
	$j(document).keyup(function(e){
		if(e.keyCode==113||e.keyCode==117){
			$j('#acms_lb-background').fadeIn('fast');
			$j('#acms_lb-contentWrap').fadeIn('fast');
		}
	});
	
	//Скрыть панель авторизации
	$j("#acms_lb-close").click(function() {
		if($j.browser.msie && parseInt($j.browser.version, 10) < 8) {
			$j("#acms_lb-background, #acms_lb-contentWrap").hide('fast');
		} else {
			$j("#acms_lb-background, #acms_lb-contentWrap").fadeOut('fast');
		};
	});
	
	$j('.out').live('click',function(){
		$j(this).attr('target','_blank');return true;
	});
	
	$j('.content tbody tr:nth-child(even)').addClass('g');
	
	$j('.default-value').focus(function(){
		var value=$j(this).val().replace("\r",'');
		var def=$j(this).attr('defaultValue').replace("\r",'');
		if(value==def)
			$j(this).val('');
	});
	
	$j('.default-value').blur(function(){
		if(!$j(this).val())
			$j(this).val($j(this).attr('defaultValue'));
		});
		
	$j('#amcs_js_companySelector-link').click(function(){
		$j('.acms_companySelector-list').fadeIn('fast');
		return false;
	});

	//Выбор домена авторизации
	$j('.acms_companySelector-list-item').click(function(){
		$j('#amcs_js_companySelector-chosen').text( $j(this).text() );
		$j('#acms_login_host').val( $j(this).attr('rel') );
		$j('#acms_login_openid').val( $j(this).attr('alt') );
		$j('.acms_companySelector-list').fadeOut('fast');
		if( $j('#acms_login_host').val() == 'localhost' ){
			$j('.acms_lb-content-form_openid').hide();
			$j('.acms_lb-content-form_local').show();
		}else{
			$j('.acms_lb-content-form_local').hide();
			$j('.acms_lb-content-form_openid').show();
		}
		return false;
	});
	
	listenInterface();
});

