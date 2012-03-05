
<h3>Шаблоны системы</h3>

<p style="color:red">Внимание: этот раздел предназначен для программистов. Изменения в этом разделе могут иметь фатальные последствия и привести сайт к полной поломке. Если вы не уверены на 100% в своих действия - не меняйте ничего в этом разделе.</p>

<form id="tmpl_save" action="/" method="POST" class="acms_panel_form">
<input type="hidden" name="action" value="js" />
<a href="#" OnClick="$('#template_new').toggle(); return false;">Добавить скрипт</a><br /><br />
		<div id="template_new" style="display:none;">
			Название файла скрипта<br /><input type="text" name="new_file" value="new.js" /><br />
			Исходный код<br /> 
			<textarea id="template_new_text" name="new_content" style="width:100%; height:400px;"><!-- Новый файл со скриптами JavaScript --></textarea>
			<script>
editAreaLoader.init({literal}{{/literal}
	id: 'template_new_text'	// id of the textarea to transform		
	,start_highlight: true	// if start with highlight
	,allow_resize: 'both'
	,allow_toggle: true
	,word_wrap: false
	,language: 'ru'
	,syntax: 'javascript'	
	,min_height: 400
{literal}}{/literal});
			</script>
		</div>
<ol class="acms_tree">
{foreach from=$action.recs item=rec key=key}
	<li>
		{$rec.file} - <a href="#" OnClick="$('#template_{$key}').toggle('fast'); return false;">изменить</a>
		<div id="template_{$key}" style="display:none;">
			<textarea id="template_{$key}_text" name="content[{$rec.file}]" style="width:100%; height:400px;">{$rec.content|escape:html}</textarea>
			<script>
editAreaLoader.init({literal}{{/literal}
	id: 'template_{$key}_text'	// id of the textarea to transform		
	,start_highlight: true	// if start with highlight
	,allow_resize: 'both'
	,allow_toggle: true
	,word_wrap: false
	,language: 'ru'
	,syntax: 'javascript'	
	,min_height: 400
{literal}}{/literal});
			</script>
		</div>
	</li>
{/foreach}

	<div class="submit">
		<button type="acms_submit">Сохранить</button>
		<button class="acms_cancel">Отменить</button>
	</div>
</form>

<script>
	$('#tmpl_save').submit(function(){literal}{{/literal}
		$('#tmpl_save textarea').each(function(){literal}{{/literal}
			var id = $(this).attr('id');
			eAL.toggle( id );	
		{literal}}{/literal});
	{literal}}{/literal});
</script>

</ol>
