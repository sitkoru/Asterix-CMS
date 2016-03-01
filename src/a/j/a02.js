var req;
var reqTimeout;
var imgTimer;

if ( w_editor == null )
	var w_editor = 'ck';

if ( w_editor == 'ck' ) {

	document.write('<script src="https://src.sitko.ru/a/ckeditor/ckeditor.js" type="text/javascript"></script>');
	document.write('<script src="https://src.sitko.ru/a/ckeditor/adapters/jquery.js" type="text/javascript"></script>');
}

else {

	document.write('<script src="https://src.sitko.ru/a/j/jquery.FCKeditor.pack.js" type="text/javascript"></script>');
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
			'NumberedList','BulletedList','-','Outdent','Indent','-',
			'Link','Unlink','Anchor','-',
			'Table','Flash','Image','-','PasteFromWord','RemoveFormat','Blockquote','typograf'
			],
		],
		language:'ru',
		/*removePlugins:'scayt,menubutton,contextmenu',*/
		filebrowserUploadUrl:'/admin/upload.php',
		extraPlugins:'iframedialog,typograf'
	});
}

function getsubtree(url,id){
	$j('#'+id).slideToggle('fast');

	var l=$j('#'+id).html().length;
	if( l == 0 ){
		$j('#'+id).html('<img src="https://src.sitko.ru/a/i/lightbox-loading.gif" alt="" />');
		$j.get(url, {'method_marker': 'admin', 'action': 'tree'}, function(data){
			$j('#'+id).html(data);
		});
	}
}
function admin_ask(action,url,id){
	$j.get(url, {'method_marker': 'admin', 'action': action}, function(data){
		$j('#'+id).html(data);
	});
}

function ask(url,method){

	$j('#acms_content').fadeIn();
	$j('#bar').fadeIn();
	$j('#bar_content').html('<img src="https://src.sitko.ru/a/i/lightbox-loading.gif" alt="загрузка..." id="bar_load" />');

	$j.ajax({
		type: method,
		dataType: "html",
		url: url,
		cache: false,
		success: function(html){
			$j('#bar_content').html(html);
			hookAdminActions();
		}
	});
}
function call_admin_interface(method,action,url){

	if ( $j('#bar').css('display') != 'none' && w_editor == 'ck' ) {

		$j('#bar_content textarea.html_editor').each( function() {

			$j(this).ckeditorGet().destroy();
		});
		$j('#bar_content').empty();
	}
	ask( url+'?method_marker='+method+'&action='+action, 'GET');
}


function call2(method,action,url,result_id){

	//Куда выводим результат
	if(!result_id)result_id='bar_content';

	//Скрываем визуальные редакторы
	if ( $j('#bar').css('display') != 'none' && w_editor == 'ck' ) {
		$j('#'+result_id+' textarea.html_editor').each( function() {
			$j(this).ckeditorGet().destroy();
		});
		$j('#'+result_id).empty();
	}

	//AJAX-preloader
	$j('#bar').fadeIn();
	$j('#'+result_id).html('<img src="https://src.sitko.ru/a/i/lightbox-loading.gif" alt="загрузка..." id="bar_load" />');

	//Запрос
	$j.ajax({
		type: 'get',
		data: ({method_marker : method, action: action}),
		dataType: "html",
		url: url,
		cache: false,
		success: function(html){

			//Вписываем результат в нужное место
			$j('#'+result_id).html(html);

			//Привязваем действия
			hookAdminActions();
		}
	});
}

function hookAdminActions(){
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

	
	
	//Переключение групп полей (Настройки)
	$j('.acms_tabs a').click(function(){
		//Верхнее меню
		$j('.acms_tabs li.active').removeClass('active');
		$j(this).parents('li').addClass('active');
		
		var id = $j(this).attr('id');
		var group = $j(this).attr('id').replace('_tab_','_panel_group_');
		//Группы полей
		if( id == 'acms_tab_all' ){
			$j('div.acms_panel_groups').fadeIn('fast');
			$j('.acms_panel_groups > li:not(.acms_sub)').fadeIn('fast');
		}else{
			$j('div.acms_panel_groups:not(.'+group+')').fadeOut('fast');
			$j('.acms_panel_groups > li:not(.'+group+', .acms_sub)').fadeOut('fast',function(){
				$j('.'+group).fadeIn('fast');
			});
		}
		return false;
	});
	
	$j('.acms_panel_form input').change(function(){
		$j(this).parent('li').addClass('changed');
	});	
	$j('.acms_panel_form textarea').change(function(){
		$j(this).parent('li').addClass('changed');
	});	
	$j('.acms_field_gallery input[type=file]').change( function(){
		$j(this).parent('li').parent('ol').children('li:first').clone().insertAfter( $j(this).parent('li') );
		
		var img_old = parseInt( $j(this).parent('li').parent('ol').children('li:not(.new)').length ); 
		var img_new = parseInt( $j(this).parent('li').parent('ol').children('li.new').length ); 
		var id = parseInt( img_old + img_new );
		
		var html = $j(this).parent('li').next().html();
		html = html.replace('[-1]','['+ id +']');
		html = html.replace('[-1]','['+ id +']');
		html = html.replace('__','_'+ id +'_');
		html = html.replace('__','_'+ id +'_');
		html = html.replace('__','_'+ id +'_');
		html = html.replace('__','_'+ id +'_');
		$j(this).parent('li').next().html( html );
		
		$j(this).parent('li').next().removeClass('changed');
		hookAdminActions();
	});
	$j('.acms_field_gallery .images li').click(function(){ $j(this).addClass('new'); });

	//Sorting with Drag&Dock
	if( typeof( $j('.sortable').sortable ) == 'function' ){
		$j('.sortable').sortable();
		$j('.sortable').disableSelection();
	}

	//Params field type
	$j('.acms_field_params .add').unbind('click');
	$j('.acms_field_params .add').click(function(){
		var sid = $j(this).attr('sid');
		var c = $j('#field_' + sid + '_params').children('li').length;
		var html = '<li id=\'field_' + sid + '_' + c + '\'><input type=\'text\' name=\'' + sid + '[' + c + '][title]\' value=\'Новая характеристика\' style=\'width:45%;\' /><input type=hidden name=\'' + sid + '[' + c + '][delete]\' id=\'field_' + sid + '_' + c + '_delete\' value=\'0\' /><input type=\'hidden\' name=\'' + sid + '[' + c + '][header]\' id=\'field_' + sid + '_' + c + '_header\' value=\'0\' /><input type=\'text\' name=\'' + sid + '[' + c + '][value]\' value=\'Значение\' style=\'width:35%; margin: 4px;\' /><img src=\'https://src.sitko.ru/a/i/delete.png\' alt=\'\' title=\'Удалить\' class=\'delete\' /><img src=\'https://src.sitko.ru/a/i/header.png\' alt=\'\' title=\'Сделать заголовком\' class=\'header\' style=\'margin-left:4px\' /></li>';
		$j(html).appendTo('#field_' + sid + '_params');
		hookAdminActions();
		return false;	
	});
	$j('.acms_field_params .delete').unbind('click');
	$j('.acms_field_params .delete').click(function(){
		var id = $j(this).parent('li').attr('id');
		$j(this).parent('li').children('input').toggleClass('markDelete');
		$j(this).parent('li').children('img.delete').toggleClass('glowRed');
		if( $j(this).parent('li').children('input').hasClass('markDelete') )
			$j('#'+id+'_delete').val( 1 );
		else
			$j('#'+id+'_delete').val( 0 );
		return false;	
	});
	$j('.acms_field_params .header').unbind('click');
	$j('.acms_field_params .header').click(function(){
		var id = $j(this).parent('li').attr('id');
		if( $j(this).hasClass('glowRed') ){
			$j(this).parent('li').children('input:first').css('width', '45%');
			$j(this).parent('li').children('input:first').css('font-weight', 'normal');
			$j(this).parent('li').children('input:first').css('border', '1px solid #ccc');
			$j(this).parent('li').children('input:last').show();
			$j('#'+id+'_header').val(0);
		}else{
			$j(this).parent('li').children('input:first').css('width', '60%');
			$j(this).parent('li').children('input:first').css('font-weight', 'bold');
			$j(this).parent('li').children('input:first').css('border', '1px solid #eee');
			$j(this).parent('li').children('input:last').hide();
			$j('#'+id+'_header').val(1);
		}
		$j(this).parent('li').children('img.header').toggleClass('glowRed');
		return false;	
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

			parent.children('input[type=file]').unbind();

			if ( $j('#bar_content form').find('input[type=file]').length > 14 )
				parent.next().after('<div class="image expand-image"><span>Можно прикрепить только 15 файлов. Чтобы добавить еще файлы, сохраните текущую форму.</span></div>');

			else
				parent.next().children('input[type=file]').change( image_plus );
		}
	}

	$j('#bar_content .a_gallery .plus-image:last input[type=file]').change( image_plus );

	$j('#bar_content .image:not(.expand-image)').hover( function() {

			if ( $j(this).hasClass('active') )
				return false;

			$j(this).addClass('active');
			var f = $j(this).find('input[type=hidden].file');

			if ( $j('#bar_content form').find('input[type=file]').length < 25 )
				f.replaceWith('<input type="file" name="'+f.attr('name')+'" />');
			else
				f.replaceWith('<span>Можно прикрепить только 15 файлов. Чтобы добавить еще файлы, сохраните текущую форму.</span>');

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
			imageLoading:'https://src.sitko.ru/a/i/lightbox-loading.gif',
			imageBtnPrev:'https://src.sitko.ru/a/i/lightbox-btn-prev.gif',
			imageBtnNext:'https://src.sitko.ru/a/i/lightbox-btn-next.gif',
			imageBtnClose:'https://src.sitko.ru/a/i/lightbox-btn-close.gif',
			imageBlank:'https://src.sitko.ru/a/i/lightbox-blank.gif',
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

	
	
	
	$j('#acms_content .acms_cancel').click( function() {
			$j('#acms_content').fadeOut();
			$j('#bar_content textarea.html_editor').each( function() {
				$j(this).ckeditorGet().destroy();
			});
			$j('#bar_content').empty();
			return false;
	});
	$j('#acms_content .acms_save').click( function() {

		//Toggle html fields
		$j('.acms_field_html textarea').each(function(){
			var id = $j(this).attr('id');
			eAL.toggle( id );	
		});

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

		call_admin_interface( 'GET', $j(this).attr('rel'), $j(this).attr('href') );
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

