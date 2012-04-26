	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<input type="text" class=" input-xlarge" name="{$field.sid}" id="field_{$field.sid}" value="{$field.value}" maxlength="{if $field.maxlength}{$field.maxlength}{else}250{/if}" x-autocompletetype="{$field.sid}"{if $field.required} required="required"{/if} />
			{if $field.help}<p class="help-block">{$field.help}</p>{/if}
		</div>
	</div>
