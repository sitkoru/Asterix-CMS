	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="{$field.sid}_day">{$field.title}</label>
		<div class="controls">
			<div class="inline-inputs">
				<input class="span2" type="text" name="{$field.sid}[date]" value="{$field.value.date}"{if $field.required} required="required"{/if}>
				<input class="span2" type="text" name="{$field.sid}[time]" value="{$field.value.time}">
			</div>
		</div>
	</div>
