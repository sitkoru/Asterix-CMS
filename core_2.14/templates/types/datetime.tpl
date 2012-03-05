	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}"{if $key != main} style="display:none;"{/if}>
		<label class="control-label" for="{$field.sid}_day">{$field.title}</label>
		<div class="controls">
			<div class="inline-inputs">
				<input class="span2" type="text" name="{$field.sid}[date]" value="{$field.value.date}">
				<input class="span2" type="text" name="{$field.sid}[time]" value="{$field.value.time}">
			</div>
		</div>
	</div>
