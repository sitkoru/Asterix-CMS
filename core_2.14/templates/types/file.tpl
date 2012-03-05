	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}"{if $key != main} style="display:none;"{/if}>
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<input type="hidden" name="{$field.sid}_old_id" value="{$field.value.old|escape}" />
			<input type="file" name="{$field.sid}" id="field_{$field.sid}" />
{if $field.value.path}
			<span style="display:block; color:grey; font-size:0.8em;">
				загружен файл: <a href="{$field.value.path}" target="_blank">{$field.value.path}</a>,<br />
				размер: {$field.value.size} байт,<br />
				тип mime: {$field.value.type},<br />
				<input type="checkbox" name="{$field.sid}_delete" id="{$field.sid}_delete" value="1" style="margin-top:10px;" /><label class="control-label" for="{$field.sid}_delete" style="display:inline;">удалить файл</label>
			</span>
{/if}
		</div>
	</div>
