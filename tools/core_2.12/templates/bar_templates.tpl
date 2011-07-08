
<h3>Шаблоны системы</h3>

<p style="color:red">Внимание: этот раздел предназначен для программистов. Изменения в этом разделе могут иметь фатальные последствия и привести сайт к полной поломке. Если вы не уверены на 100% в своих действия - не меняйте ничего в этом разделе.</p>

<form id="tmpl_save" action="/" method="POST">
<input type="hidden" name="action" value="templates" />
<a href="#" OnClick="$j('#template_new').toggle(); return false;">Добавить шаблон</a><br /><br />
		<div id="template_new" style="display:none;">
			Название файла шаблон<br /><input type="text" name="new_file" value="new.tpl" /><br />
			Исходный код<br /> 
			<textarea id="template_new_text" name="new_content" style="width:100%; height:400px;"><!-- Новый шаблон, с применением шаблонизатора Smarty --></textarea>
			<script>
editAreaLoader.init({literal}{{/literal}
	id: 'template_new_text'	// id of the textarea to transform		
	,start_highlight: true	// if start with highlight
	,allow_resize: 'both'
	,allow_toggle: true
	,word_wrap: false
	,language: 'ru'
	,syntax: 'html'	
	,min_height: 400
{literal}}{/literal});
			</script>
		</div>
<ol>
{foreach from=$action.recs item=rec key=key}
	<li>
		{$rec.title} - <a href="#" OnClick="$j('#template_{$rec.id}').toggle('fast'); return false;">изменить</a>
		<div id="template_{$rec.id}" style="display:none;">
			<textarea id="template_{$rec.id}_text" name="content[{$rec.file}]" style="width:100%; height:400px;">{$rec.content|escape:html}</textarea>
			<script>
editAreaLoader.init({literal}{{/literal}
	id: 'template_{$rec.id}_text'	// id of the textarea to transform		
	,start_highlight: true	// if start with highlight
	,allow_resize: 'both'
	,allow_toggle: true
	,word_wrap: false
	,language: 'ru'
	,syntax: 'html'	
	,min_height: 400
{literal}}{/literal});
			</script>
		</div>
	</li>
{/foreach}

<input type="submit" value="Сохранить" />
</form>

<script>
	$j('#tmpl_save').submit(function(){literal}{{/literal}
		$j('#tmpl_save textarea').each(function(){literal}{{/literal}
			var id = $j(this).attr('id');
			eAL.toggle( id );	
		{literal}}{/literal});
	{literal}}{/literal});
</script>

</ol>
