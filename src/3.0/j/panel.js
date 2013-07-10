jQuery(document).ready(function(){
	
	$('#acms_bar a').click(function(){
		$('#acms_content').fadeIn('slow')
	});
	
	$('.acms_close').click(function(){
		$('#acms_content').fadeOut('slow')
	});

	$('.acms_lb-close').click(function(){
		$('.acms_lb-background').hide();
		$('.acms_lb-contentWrap').hide();
	});

	//Показать панель авторизации
	$(document).keyup(function(e){
		if(e.keyCode==113||e.keyCode==117){
			$('#acms_lb-background').fadeIn('fast');
			$('#acms_lb-contentWrap').fadeIn('fast');
		}
	});

  $('#amcs_js_companySelector-link').click(function(){
    $('.acms_companySelector-list').fadeIn('fast');
    return false;
  });

  //Выбор домена авторизации
  $('.acms_companySelector-list-item').click(function(){
    $('#amcs_js_companySelector-chosen').text( $(this).text() );
    $('#acms_login_host').val( $(this).attr('data-name') );
    $('#acms_login_openid').val( $(this).attr('data-type') );
    $('.acms_companySelector-list').fadeOut('fast');
    if( $('#acms_login_host').val() == 'localhost' ){
      $('.acms_lb-content-form_openid').hide();
      $('.acms_lb-content-form_local').show();
    }else{
      $('.acms_lb-content-form_local').hide();
      $('.acms_lb-content-form_openid').show();
    }
    return false;
  });
  
	$('form.ajax').live('submit',function(){
		var form_id=$(this).attr('id');
		$('#'+form_id+' li').removeClass('err');
		$.post(
			$(this).attr('action'),
			$(this).serialize(),
			function(data){
				if(data['result']=='error'){
					for(var key in data['errors']){
						$('#'+form_id+' #id_'+key).parent().addClass('err');
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
					$('#'+form_id).parent('.interface').hide();
				}
			},
			"json");
		return false;
	});

	$('.acms_gallery_delete').click(function(){
		if( confirm('Удалить картинку?') )
			$(this).parents('li').remove();
		return false;
	});

});
