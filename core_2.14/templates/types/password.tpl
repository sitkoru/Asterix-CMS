	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}"{if $key != main} style="display:none;"{/if}>
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<input type="password" class="span2" placeholder="****************" name="{$field.sid}" id="field_{$field.sid}" maxlength="250" x-autocompletetype=”{$field.sid}” />
		{if $field.value}	
			<p class="help-block">Если хотите изменить пароль - укажите новый, иначе оставьте поле пустым, чтобы ничего не менять.</p>
		{/if}
		</div>
	</div>
