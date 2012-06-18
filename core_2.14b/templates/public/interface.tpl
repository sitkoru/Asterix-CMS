		<form name="edit_record" id="form_{$form.interface}" method="POST" action="{$form.url}" enctype="multipart/form-data" class="form-horizontal{if $form.ajax} ajax{/if}">
			<input type="hidden" name="interface" value="{$form.interface}" />
		{foreach from=$form.fields item=field}
		{if $field.type == 'hidden'}
			{include file="$path_admin_templates/`$field.template_file`"}
		{/if}
		{/foreach}

			<fieldset>
				<legend>{$form.title}</legend>
			{if $form.comment}
				<div class="alert alert-block alert-info">
					<a class="close" data-dismiss="alert">×</a>
					<h4 class="alert-heading">Внимание!</h4>
					<p>{$form.comment}</p>
				</div>				
			{/if}
			{foreach from=$form.fields item=field}
			{if $field.type != 'hidden'}
				{include file="$path_admin_templates/types/`$field.type`.tpl" key=main}
			{/if}
			{/foreach}
				
				<div class="form-actions">
					<button type="submit" class="btn btn-large btn-primary">{if $form.button_title}{$form.button_title}{else}Сохранить изменения{/if}</button>&nbsp;
<!--
					<button type="reset" class="btn">Отмена</button>
-->
					<br />
					<span style="font-size:0.8em; color:grey;">Для отправки нажмите Ctrl+Enter</span>
				</div>
				
			</fieldset>
			<script type="text/javascript">

	$(function(){
		if (navigator.appName =="Microsoft Internet Explorer"){

			$('#form_{$form.interface}').submit(function(){
				
				var all_ok = true;
				$('#form_{$form.interface} input').each(function(){
					if( $(this).attr('required') )
						if( !$(this).val() ){
							$(this).css('border','1px solid red');
							all_ok = false;
						}
				});

				if( !all_ok ){
					alert('Заполните все необходимые поля!');
					return false;
				}
			});
		}
	});

	
	$(document).keydown(function(e) {
        if (e.keyCode == 13 && e.ctrlKey) {
			$('#form_{$form.interface}').submit();
        }
    });	
			</script>
		</form>
