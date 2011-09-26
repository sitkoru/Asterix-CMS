	<input type="hidden" name="{$field.sid}_old_id" value="{$field.value.id}" />
	<label for="{$field.sid}_id" style="font-size:1.2em; margin:0;">{$field.title}</label>
{if $field.value.path}
	<span style="display:block; color:grey; font-size:0.8em;">
		загружен файл: <a href="{$field.value.path}" target="_blank">{$field.value.path}</a>,<br />
		размер: {$field.value.size} байт,<br />
		тип mime: {$field.value.type},<br />
	</span>
{/if}
	<input type="file" name="{$field.sid}" name="{$field.sid}_id" style="width:50%; font-size:1em; padding:0; margin-left:-2px;" /><br />
{if $field.value.path}
	<input type="checkbox" name="{$field.sid}_delete" id="{$field.sid}_delete" value="1" style="margin-top:10px;" /><label for="{$field.sid}_delete" style="display:inline;">удалить файл</label>
{/if}

