document.write('<script src="http://src.sitko.ru/3.0/ckeditor/ckeditor.js" type="text/javascript"></script>');
document.write('<script src="http://src.sitko.ru/3.0/ckeditor/adapters/jquery.js" type="text/javascript"></script>');

var init_ckeditor = function() {

	$('.form-group textarea.html_editor').ckeditor({
        toolbar:
            [
                [
                    'Source','-','FontSize','Format','-','Bold','Italic','Underline','-',
                    'Subscript','Superscript','SpecialChar','-',
                    'JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-',
                    'NumberedList','BulletedList','-','Outdent','Indent','-',
                    'Link','Unlink','Anchor','-',
                    'Table','Image','-','PasteFromWord','RemoveFormat','Blockquote','typograf'
                ],
            ],
        language:'ru',
        extraCss: 'body{font-size:1.2em;}',
        height: '500px'
	});

    $('.form-group textarea.html_editor_admin').ckeditor({
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
        filebrowserUploadUrl:'/admin/',
        extraPlugins:'iframedialog,typograf,onchange'
    });
    $('.control-group textarea.html_editor_admin').ckeditor({
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
        filebrowserUploadUrl:'/admin/',
        extraPlugins:'iframedialog,typograf,onchange'
    });
}


function fn(){
	$( ".sortable" ).sortable({
		items: "li:not(.not_sorted)",
		update: function(event, ui) {
			var rec_id = $(ui.item).attr('rec_id');
			var to_id = $(ui.item).prev().attr('rec_id');
			if( (rec_id>0) && (to_id>0) ){
				$.post(
					'/admin/start.move.html', 
					{
						'module_sid':		$(ui.item).attr('module_sid'),
						'structure_sid':	$(ui.item).attr('structure_sid'),
						'record':			rec_id,
						'target':			to_id
					}, 
					function(data) {
					}
				);
			}else if( !$(ul.item).hasClass('dont_check') ){
				alert('Нельзя переместить запись на первое место, лучше сдвиньте другие записи ниже.');
				$(this).sortable('cancel');
			}
		}
	});
	$('.sortable').disableSelection();

	//Params field type
	$('.acms_field_params .add, .acms_field_feedback .add').unbind('click');
	$('.acms_field_params .add, .acms_field_feedback .add').click(function(){
		$(this).parents('.controls').find('ul li:last').clone().appendTo( $(this).parents('.controls').find('ul') );
		$(this).parents('.controls').find('ul li:last input').val('');
		fn();
		return false;	
	});
	$('.acms_field_params .delete, .acms_field_feedback .delete').unbind('click');
	$('.acms_field_params .delete, .acms_field_feedback .delete').click(function(){
		if( confirm('Удалить параметр?') ){
			$(this).parents('li').remove();
		}
		return false;
	});
	$('.acms_field_feedback .required').unbind('click');
	$('.acms_field_feedback .required').click(function(){
		$(this).toggleClass('badge badge-warning').find('i').toggleClass('icon-white');
		if( $(this).hasClass('badge') ) 
			$(this).parents('li').find('.required_field').val( 1 );
		else
			$(this).parents('li').find('.required_field').val( 0 );
		return false;	
	});
	
	$('.acms_field_params .header').unbind('click');
	$('.acms_field_params .header').click(function(){
		var id = $(this).parents('li').attr('id');
		if( $(this).parents('li').hasClass('well') ){
			$(this).parents('li').children('input:last').show();
			$('#'+id+'_header').val(0);
		}else{
			$(this).parents('li').children('input:last').hide();
			$('#'+id+'_header').val(1);
		}
		$(this).parents('li').toggleClass('well');
		return false;	
	});

}

jQuery(document).ready(function(){
	
	fn();
/*	
	//Переключение групп полей (Настройки)
	$('.acms-tabs a').click(function(){
		
		//Верхнее меню
		$('.acms-tabs li.active').removeClass('active');
		$(this).parents('li').addClass('active');
		
		var group = $(this).attr('id').replace('_tab_','_panel_group_');
		
		//Группы полей
		$('.acms_panel_groups').fadeOut('fast');
		$('.'+group+'').fadeIn('fast');
		return false;
	});
*/

//	$(".alert-message").alert();
	init_ckeditor();

	
	$('.acms_panel_groups .icon-ok').click(function(){
		$(this).toggleClass('icon-ok').toggleClass('icon-remove');
	});
	$('.acms_panel_groups .icon-remove').click(function(){
		if( confirm('Удалить запись безвозвратно?') ){
			var rec_id = $(this).parents('li').attr('rec_id');
			$.post(
				'/admin/start.delete.html', 
				{
					'module_sid':		$(this).parents('li').attr('module_sid'),
					'structure_sid':	$(this).parents('li').attr('structure_sid'),
					'record':			rec_id
				}, 
				function(data) {
					document.location.href='/admin.html';
				}
			);
		}
	});
	
	$('.acms_panel_groups .icon-random').click(function(){
		$(this).parent('li').find('ol').toggleClass('well sortable');
		fn();
	});
	
	$('.acms_gallery_delete').click(function(){
		if( confirm('Удалить картинку?') )
			$(this).parents('li').remove();
		return false;
	});
	

});


