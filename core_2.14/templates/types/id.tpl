	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<input type="hidden" name="{$field.sid}" value="{$field.value}" />
			<span class="input-xlarge uneditable-input">{$field.value}</span>
			{if $field.help}<p class="help-block">{$field.help}</p>{/if}
		</div>
	</div>

