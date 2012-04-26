	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<input type="text" class="" name="{$field.sid}" id="field_{$field.sid}" value="{$field.value}" maxlength="250" x-autocompletetype="{$field.sid}"{if $field.required} required="required"{/if} />
		</div>
	</div>
