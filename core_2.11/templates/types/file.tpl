<p>
<input type="hidden" name="{$field.sid}_old_id" value="{$field.value.id}" />
{$field.title}:<br />
<input type="file" name="{$field.sid}" style="width:50%" maxlength="250" /><br />

{if strlen($field.value.path) gt 0}Уже загружен <a href="{$field.value.path}" target="_blank">файл «{$field.value.title}»</a>.<br />{/if}
<!--
<label for="{$field.sid}_title">Название файла</label><br />
<input type="text" name="{$field.sid}_title" id="{$field.sid}_title" value="{$field.value.title}" style="width:40%;" /><br />
-->
<input type="checkbox" name="{$field.sid}_delete" id="{$field.sid}_delete" value="1" /><label for="{$field.sid}_delete">удалить файл</label><br />

</p>
