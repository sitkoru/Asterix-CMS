	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<textarea id="field_{$field.sid}" name="{$field.sid}" style="width:600px; height:50px;">{foreach from=$field.value item=val}{$val.title}, {/foreach}</textarea>
			<p class="help-block">
				Введите теги через запятую, например: [телефон, Wi-fi, мобичел]
			</p>
		</div>
	</div>
