<div class="acms_submit_out">
	<img class="acms_cancel" src="http://src.opendev.ru/i/error.png" alt="Закрыть без сохранения" />
	<img class="acms_save" src="http://src.opendev.ru/i/save.png" alt="Сохранить" />
</div>

<ol class="acms_tree">
{foreach from=$action.recs item=rec key=key}
	<li>
		{$rec.title} - <a href="#" OnClick="JavaScript: call2('admin', 'templates', '/?tmpl={$rec.file}'); return false;">изменить</a>
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
