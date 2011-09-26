var req;
var reqTimeout;
var imgTimer;

if ( w_editor == null )
	var w_editor = 'ck';

if ( w_editor == 'ck' ) {

	document.write('<script src="http://src.sitko.ru/a/ckeditor/ckeditor.js" type="text/javascript"></script>');
	document.write('<script src="http://src.sitko.ru/a/ckeditor/adapters/jquery.js" type="text/javascript"></script>');
}

else {

	document.write('<script src="http://src.sitko.ru/a/j/jquery.FCKeditor.pack.js" type="text/javascript"></script>');
	document.write('<script src="/t/adm/fckeditor/fckeditor.js" type="text/javascript"></script>');
}

var $j = jQuery.noConflict();

var init_ckeditor = function() {

	$j('#bar_content textarea.html_editor').ckeditor({

		toolbar:
		[
			[
			'Source','-','FontSize','Format','-','Bold','Italic','Underline','-',
			'Subscript','Superscript','SpecialChar','-',
			'JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-',
			'NumberedList','BulletedList','-',
			'RemoveFormat','-',
			'Link','Unlink','Anchor','-',
			'Table','Flash','Image','-'
			],
		],
		language:'ru',
		removePlugins:'scayt,menubutton,contextmenu',
		filebrowserUploadUrl:'/admin/upload.php'
	});
}

function getsubtree(url,id){
	$j('#'+id).slideToggle('fast');
	
	var l=$j('#'+id).html().length;
	if( l == 0 ){	
		$j('#'+id).html('<img src="http://src.sitko.ru/a/i/lightbox-loading.gif" alt="" />');
		$j.get(url, {'method_marker': 'admin', 'action': 'tree'}, function(data){
			$j('#'+id).html(data);
		});
	}
}

function admin_ask(action,url,id){
	$j('#'+id).slideToggle('fast');
	
	var l=$j('#'+id).html().length;
	if( l == 0 ){	
		$j('#'+id).html('<img src="http://src.sitko.ru/a/i/lightbox-loading.gif" alt="" />');
		$j.get(url, {'method_marker': 'admin', 'action': action}, function(data){
			$j('#'+id).html(data);
		});
	}
}


//Обращаемся к админке и показываем результат
function call_admin_interface(method, action, url, result_id){

	//Обходим ошибку с переинициализацией CKeditor
	if ( $j('#bar').css('display') != 'none' && w_editor == 'ck' ) {
		$j('#'+result_id+' textarea.html_editor').each( function() {
			$j(this).ckeditorGet().destroy();
		});
		$j('#'+result_id).empty();
	}

	//Показываем AJAX-загрузчик
	$j('#bar').fadeIn();
	$j('#'+result_id).html('<img src="http://src.sitko.ru/a/i/lightbox-loading.gif" alt="загрузка..." id="bar_load" />');

	//Запрос
	$j.ajax({
		type: method,
		data: ({action : action}),
		dataType: 'html',
		url: url,
		cache: false,
		success: function(html){
			//Вставляем полученные данные
			$j('#'+result_id).html(html);

			if(html.length > 0){
				//Вешаем события на загруженные элементы
				hookAdminActions();
			}
		}
	});
}



function hookAdminActions(){
	//CKeditor
	if ( w_editor == 'ck' )
		init_ckeditor();
	else {
		$j.fck.config = {path: '/t/adm/fckeditor/'};
		$j('textarea.html_editor').fck({ toolbar:'Sitko', height:500 });
	}

	//Карты Goolge maps
	$j('#admin_map_canvas').each(function(){
		gmap_initializeMap('admin_map_canvas',55.161226,61.433507,12,true)
	});

	//Отправка формы редактирования записи
	$j('#bar_content form').submit( function() {

		if ( $j(this).hasClass('validate') && ! $j('#field_title').val() ) {

			$j('#field_title').focus();
			alert( 'Укажите название записи!' );
			return false;
		}

		else if ( $j(this).hasClass('settings-form') ) {

			$j(this).find('li').show();
		}

		return true;
	});

	var image_plus = function() {

		if ( $j(this).val() ) {

			var parent = $j(this).parents('.plus-image');

			parent.after('<div class="image expand-image plus-image">'+parent.html()+'</div>');
			parent.next().children('input').val('');

			l = parent.parent().children('.image').length - 1;

			parent.next().children('input').each( function() {

				var reg = /\[\d+\]/;
				var new_name = $j(this).attr('name').replace(reg, '['+l+']');
				$j(this).attr( 'name', new_name );
			});

			if ( $j('#bar_content form').find('input[type=file]').length > 24 ) {

				parent.next().after('<div class="image expand-image"><span>Можно прикрепить только 25 файлов. Чтобы добавить еще файлы, сохраните текущую форму.</span></div>');
				$j('#bar_content .a_gallery .plus-image:last input[type=file]').die();
			}
		}
	}

	$j('#bar_content .a_gallery .plus-image:last input[type=file]').live( 'change', image_plus );

	$j('#bar_content .image:not(.expand-image)').hover( function() {

			if ( $j(this).hasClass('active') )
				return false;

			$j(this).addClass('active');
			var f = $j(this).find('input[type=hidden].file');

			if ( $j('#bar_content form').find('input[type=file]').length < 25 )
				f.replaceWith('<input type="file" name="'+f.attr('name')+'" />');
			else
				f.replaceWith('<span>Можно прикрепить только 25 файлов. Чтобы добавить еще файлы, сохраните текущую форму.</span>');

			return false;
	},
	function() {

			$j(this).removeClass('active');
			var f = $j(this).find('input[type=file]');

			if ( ! f.val() )
				f.replaceWith('<input type="hidden" class="file" name="'+f.attr('name')+'" />');

			f = $j(this).find('input[type=checkbox]');
			var i = $j(this).find('.img');
	});

	$j('.lightbox').lightBox({
			imageLoading:'http://src.sitko.ru/a/i/lightbox-loading.gif',
			imageBtnPrev:'http://src.sitko.ru/a/i/lightbox-btn-prev.gif',
			imageBtnNext:'http://src.sitko.ru/a/i/lightbox-btn-next.gif',
			imageBtnClose:'http://src.sitko.ru/a/i/lightbox-btn-close.gif',
			imageBlank:'http://src.sitko.ru/a/i/lightbox-blank.gif',
			txtImage:'Изображение',txtOf:'из'
		});

	$j('#bar_content .toggle').click( function() {

		$j(this).toggleClass('up');
		$j(this).next().slideToggle();
	});

	$j('#bar .cancel').click( function() {

			$j('#bar').fadeOut();
			$j('#bar_content textarea.html_editor').each( function() {

				$j(this).ckeditorGet().destroy();
			});
			$j('#bar_content').empty();
			return false;
	});

	$j('#bar .save').click( function() {

		var form = $j('#bar_content form');

		if ( form.length )
			form.submit();
		else
			window.location.reload();
		return false;
	});

	$j('#bar_content .settings-group').click( function() {

		var target_group = $j(this).attr('id').replace('link-','');

		var tree = $j(this).parents('.tree');

		if ( target_group == 'settings-all' ) {

			tree.find('li').fadeIn();
		}

		else {

			tree.find('li:not(.'+target_group+')').hide();
			tree.find('li.'+target_group).fadeIn();
		}

		$j(this).parent().find('.active').removeClass('active');
		$j(this).addClass('active');

		return false;
	});
}

function ask(url,method){

	$j('#bar').fadeIn();
	$j('#bar_content').html('<img src="http://src.sitko.ru/a/i/lightbox-loading.gif" alt="загрузка..." id="bar_load" />');

	$j.ajax({
		type: method,
		dataType: "html",
		url: url,
		cache: false,
		success: function(html){
			$j("#bar_content").html(html);
// Editor
			if ( w_editor == 'ck' )
				init_ckeditor();
			else {
				$j.fck.config = {path: '/t/adm/fckeditor/'};
				$j('textarea.html_editor').fck({ toolbar:'Sitko', height:500 });
			}

// Maps
			$j('#admin_map_canvas').each(function(){
				gmap_initializeMap('admin_map_canvas',55.161226,61.433507,12,true)
			});

// Events
			$j('#bar_content form').submit( function() {

				if ( $j(this).hasClass('validate') && ! $j('#field_title').val() ) {

					$j('#field_title').focus();
					alert( 'Укажите название записи!' );
					return false;
				}

				else if ( $j(this).hasClass('settings-form') ) {

					$j(this).find('li').show();
				}

				return true;
			});

			var image_plus = function() {

				if ( $j(this).val() ) {

					var parent = $j(this).parents('.plus-image');

					parent.after('<div class="image expand-image plus-image">'+parent.html()+'</div>');
					parent.next().children('input').val('');

					l = parent.parent().children('.image').length - 1;

					parent.next().children('input').each( function() {

						var reg = /\[\d+\]/;
						var new_name = $j(this).attr('name').replace(reg, '['+l+']');
						$j(this).attr( 'name', new_name );
					});

					if ( $j('#bar_content form').find('input[type=file]').length > 24 ) {

						parent.next().after('<div class="image expand-image"><span>Можно прикрепить только 25 файлов. Чтобы добавить еще файлы, сохраните текущую форму.</span></div>');
						$j('#bar_content .a_gallery .plus-image:last input[type=file]').die();
					}
				}
			}

			$j('#bar_content .a_gallery .plus-image:last input[type=file]').live( 'change', image_plus );

			$j('#bar_content .image:not(.expand-image)').hover( function() {

					if ( $j(this).hasClass('active') )
						return false;

					$j(this).addClass('active');
					var f = $j(this).find('input[type=hidden].file');

					if ( $j('#bar_content form').find('input[type=file]').length < 25 )
						f.replaceWith('<input type="file" name="'+f.attr('name')+'" />');
					else
						f.replaceWith('<span>Можно прикрепить только 25 файлов. Чтобы добавить еще файлы, сохраните текущую форму.</span>');

					return false;
			},
			function() {

					$j(this).removeClass('active');
					var f = $j(this).find('input[type=file]');

					if ( ! f.val() )
						f.replaceWith('<input type="hidden" class="file" name="'+f.attr('name')+'" />');

					f = $j(this).find('input[type=checkbox]');
					var i = $j(this).find('.img');
			});

			$j('.lightbox').lightBox({
					imageLoading:'http://src.sitko.ru/a/i/lightbox-loading.gif',
					imageBtnPrev:'http://src.sitko.ru/a/i/lightbox-btn-prev.gif',
					imageBtnNext:'http://src.sitko.ru/a/i/lightbox-btn-next.gif',
					imageBtnClose:'http://src.sitko.ru/a/i/lightbox-btn-close.gif',
					imageBlank:'http://src.sitko.ru/a/i/lightbox-blank.gif',
					txtImage:'Изображение',txtOf:'из'
				});

			$j('#bar_content .toggle').click( function() {

				$j(this).toggleClass('up');
				$j(this).next().slideToggle();
			});

			$j('#bar .cancel').click( function() {

					$j('#bar').fadeOut();
					$j('#bar_content textarea.html_editor').each( function() {

						$j(this).ckeditorGet().destroy();
					});
					$j('#bar_content').empty();
					return false;
			});

			$j('#bar .save').click( function() {

				var form = $j('#bar_content form');

				if ( form.length )
					form.submit();
				else
					window.location.reload();
				return false;
			});

			$j('#bar_content .settings-group').click( function() {

				var target_group = $j(this).attr('id').replace('link-','');

				var tree = $j(this).parents('.tree');

				if ( target_group == 'settings-all' ) {

					tree.find('li').fadeIn();
				}

				else {

					tree.find('li:not(.'+target_group+')').hide();
					tree.find('li.'+target_group).fadeIn();
				}

				$j(this).parent().find('.active').removeClass('active');
				$j(this).addClass('active');

				return false;
			});
		}
	});
}


/*
function call_admin_interface(method,action,url){

	if ( $j('#bar').css('display') != 'none' && w_editor == 'ck' ) {

		$j('#bar_content textarea.html_editor').each( function() {

			$j(this).ckeditorGet().destroy();
		});
		$j('#bar_content').empty();
	}
	ask( url+'?method_marker='+method+'&action='+action, 'GET', null );
}
*/

function call_put_interface(data,url){
	ask(url,'PUT',data);
}

function getFormValues(form_name){
var result='';
result+='method_marker=PUT';
for(var i=0;i<form_name.length;i++)
	if(form_name[i].name)
	result+='&'+form_name[i].name+'='+form_name[i].value;
return result;
}


//Google Maps Initialize
function initialize() {
  if (GBrowserIsCompatible()) {
	var map = new GMap2(document.getElementById("map_canvas"));

	var x=$j('#map_canvas_x').val();
	var y=$j('#map_canvas_y').val();
	var z=$j('#map_canvas_z').val();

	var center = new GLatLng(x, y);
	map.setCenter(center, parseInt(z));

	var customUI = map.getDefaultUI();
	map.setUI(customUI);

	var marker = new GMarker(center, {draggable: true});

	GEvent.addListener(marker, "dragend", function() {
		var p=marker.getPoint();
		var x=p.lat();
		var y=p.lng();
		$j('#map_canvas_x').val(x);
		$j('#map_canvas_y').val(y);
	});

	GEvent.addListener(map, "zoomend", function(oldLevel, newLevel) {
		$j('#map_canvas_z').val(newLevel);
	});

	map.addOverlay(marker);

  }
}

jQuery(document).ready(function(){

	$j('.call_admin_interface').click( function() {

		call_admin_interface( 'GET', $j(this).attr('rel'), $j(this).attr('href') , 'bar_content');
		return false;
	});

	$j('*').keypress(function(e){
		if(e.altKey){
			if(e.which==69){
				alert('manage');
			}else{
//				alert(e.which);
			}
		}
	});

	bar_position = function() {

		var w = 0;
/*
		var ch = $j('#admin_bar').children();
		for ( var i = 0; i < ch.length; i ++ )
			w += ch[i].width();
*/
		w += 22;
		var l = ( $j(window).width() - $j('#admin_bar').width() ) / 2;
		if ( l < 0 ) l = 5;
		$j('#admin_bar').css({left:l}).fadeIn('slow');
	}
	bar_position();
	$j(window).resize( bar_position );
});

