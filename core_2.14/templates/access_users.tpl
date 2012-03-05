<br />
<a href="#" OnClick="call_admin_interface('ADMIN','edit',''); return false;">обновить</a> 
<a href="#" OnClick="document.getElementById('bar').style.display='none'; return false;">отмена</a>
<br />
<h3>Редактируем</h3>

<form name="edit_record" method="POST" action="{$content.url}" enctype="multipart/form-data">
<input type="hidden" name="module" value="{$action.module}" />
<input type="hidden" name="structure_sid" value="{$action.structure_sid}" />

{foreach from=$action.groups item=field_group key=key}
{if count($field_group.fields) gt 0}

<fieldset>
<legend>{$field_group.title} (<a id="fieldset_{$key}_switch" href="#" OnClick="JavaStript:

$('#fieldset_{$key}').slideToggle('fast');

">{if $field_group.open}свернуть{else}развернуть{/if}</a>)</legend>
<div id="fieldset_{$key}" style="display:{if $field_group.open}block{else}none{/if}">

{foreach from=$field_group.fields item=field}
{include file="$path_admin_templates/`$field.template_file`"}
{/foreach}

</div>
</fieldset><br />

{/if}
{/foreach}

<span style="color:grey;">Чтобы добавить запись, сначала исправьте все ошибки.</span><br />
<input type="button" value="Сохранить" OnClick="

if(document.getElementById('field_title').value=='')alert('Заполнител название записи');
if(document.getElementById('field_title').value=='')document.getElementById('field_title').focus();
else
	document.forms['edit_record'].submit();

" />
 <input type="button" value="Отменить" OnClick="JavaScript:
document.getElementById('bar').style.display='none';
return false;
" /><br /><br />
<!--
<input type="button" value="Сохранить и остаться в этой записи" OnClick="

$('form fieldset input').fadeTo('slow',.5);
$('form fieldset textarea').fadeTo('slow',.5);
$('form fieldset select').fadeTo('slow',.5);

" /><br />
<input type="button" value="Сохранить и вернуться на сайт" disabled="disabled" /><br />
<input type="button" value="Сохранить и добавить новую запись" disabled="disabled" /><br />
-->
</form>

