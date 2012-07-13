	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<textarea id="field_{$field.sid}" class="html_editor_admin" name="{$field.sid}" style="height:400px; width:600px;">{$field.value}</textarea>
			<p class="help-block">In addition to freeform text, any HTML5 text-based input appears like so.</p>
		</div>
		<input type="hidden" name="{$field.sid}_meta" id="field_{$field.sid}_meta" value="{$field.meta}" />
	</div>
