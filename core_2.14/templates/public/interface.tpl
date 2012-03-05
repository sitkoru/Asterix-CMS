		<form name="edit_record" id="form_{$form.interface}" method="POST" action="" enctype="multipart/form-data" class="form-horizontal{if $form.ajax} ajax{/if}">
			<input type="hidden" name="interface" value="{$form.interface}" />
		{foreach from=$form.fields item=field}
		{if $field.type == 'hidden'}
			{include file="$path_admin_templates/`$field.template_file`"}
		{/if}
		{/foreach}

			<fieldset>
				<legend>{$form.title}</legend>
				{if $form.comment}<p>{$form.comment}</p>{/if}
			{foreach from=$form.fields item=field}
			{if $field.type != 'hidden'}
				{include file="$path_admin_templates/types/`$field.type`.tpl" key=main}
			{/if}
			{/foreach}
				
				<div class="form-actions">
					<button type="submit" class="btn btn-large btn-primary">{if $form.button_title}{$form.button_title}{else}Сохранить изменения{/if}</button>&nbsp;
					<button type="reset" class="btn">Отмена</button>
				</div>
				
			</fieldset>
		</form>
