function isset(varname)  {
  if(typeof( window[ varname ] ) != "undefined") return true;
  else return false;
}

var $j = jQuery.noConflict();

function listenInterface(){
	$j('form.ajax').submit(function() {

		//Запоминаем форму с которой работаем
		var form_id=$j(this).attr('id');
	
		//Обираем ошибки с прошлого раза
		$j('#'+form_id+' li').removeClass('err');
	
		//Отправляем и получаем данные
		$j.post($j(this).attr('action'), $j(this).serialize(), function(data){
			
			//Ошибка
			if( data['result'] == 'error' ){
				//Помечаем и выводим ошибки
				for(var key in data['errors']){
					$j('#'+form_id+' #id_'+key).parent().addClass('err');
					alert( data['errors'][key] );
				}
			
			//Вывести общее сообщение
			}else if( data['result'] == 'message' ){
				alert( data['message'] );
			
			//Перенаправить на страницу
			}else if( data['result'] == 'redirect' ){
				document.location.href=data['url'];

			}
			
			//Вызов произвольной функции
			if( data['call'] > 0 ){
				var name=data['call'];
				name.call();
			}

			//Закрываем диалоговое окно
			if( data['close'] ){
				$j('#'+form_id).parent('.interface').hide();
			}
			
		}, "json");
		return false;
	});
}

function updateBasket(){
	alert('basket updated');
}

$j(document).ready( function() {

	//Инициализируем визуальные редакторы
	if(isset($j.fck)){
		$j.fck.config = {path: '/t/adm/fckeditor/'};
		$j('textarea.html_editor').fck({ toolbar:'Public', height:500 }	);
	}

	$j('#do_login').click(function(e){
		$j("#int_reg").hide();
		$j("#int_recover").hide();
		$j("#int_login").toggle();
		return false;
	});
	
	$j('#do_reg').click(function(e){
		$j("#int_login").hide();
		$j("#int_recover").hide();
		$j('#int_reg').toggle();
		return false;
	});
	
	$j('#do_recover').click(function(e){
		$j("#int_login").hide();
		$j('#int_reg').hide();
		$j("#int_recover").toggle();
		return false;
	});

	$j('.kolvo').change(function(e){
		var counter=$j(this).val();
		var id=$j(this).attr('id');
		
		$j.post('/basket.html', { id: id, count: counter },
			function(json){
				$j('#basket').fadeOut('fast',function(){
					$j('#basket_count').html(json.count);
					$j('#basket_summ').html(json.summ);
					$j('#basket_summ_text').html(json.summ+' руб.');
					$j('#basket').fadeIn('fast');
				});
		}, "json");
	});
	
	$j('.items_count').change(function(e){
		var counter=$j(this).val();
		var id=$j(this).attr('id');
		
		$j.post('/basket.html', { id: id, count: counter },
			function(json){
				$j('#basket').fadeOut('fast',function(){
					$j('#basket_count').html(json.count);
					$j('#basket_summ').html(json.summ);
					$j('#basket_summ_text').html(json.summ+' руб.');
					$j('#basket').fadeIn('fast');
				});
		}, "json");
	});
	
	$j('input.cancel').click(function(e){$j(this).parents('div.interface').css('display','none');return false;});
	
	listenInterface();
});

