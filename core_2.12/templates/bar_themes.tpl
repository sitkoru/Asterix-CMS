
<h3>Оформление сайта</h3>

<h3>Установленные темы</h3>

<ol>
{foreach from=$action.recs item=rec key=key}
	<li>
		{$rec.title} [{$rec.id}] 
		<a href="#" OnClick="$j('#{$module.id}_structure').toggle('fast'); return false;">структура</a>
		<a href="#" OnClick="$j('#{$module.id}_data').toggle('fast'); return false;">данные</a>
		<a href="#" OnClick="$j('#{$module.id}_delete').toggle('fast'); return false;">удалить</a>
	</li>
{/foreach}
</ol>

<h3>Другие темы оформления</h3>
<ol>
{foreach from=$action.recs_all item=rec key=key}
	<li>
		{$rec.title} [{$rec.id}] - <a href="#" OnClick="$j('#add_module_{$rec.id}').toggle('fast'); return false;">подключить</a>
		<div id="add_module_{$rec.id}" style="display:none; border:2px solid #ccc; margin:10px; padding:10px; font-size:1.4em;">
			<form id="{$module.id}_update_form" class="interface ajax" method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="module_install" />
				<input type="hidden" name="module_id" value="{$rec.id}" />
				<input type="hidden" name="module_title" value="{$rec.title}" />
				<ul>
					<li>Куда поместить модуль: 
						<select name="to" id="add_module_{$rec.id}_to">
							<option value="0" style="color:green;">создать новый раздел "{$rec.title}"</option>
						{foreach from=$action.root_recs item=rt}
							<option value="{$rt.id}" style="color:red;">в "{$rt.title}" *</option>
						{/foreach}
						</select><br ><span style="color:red; font-size:0.7em;">* - будет изменён URL раздела и подразделов</span>
					</li>
					<li>
						<input id="add_module_{$rec.id}_text" type="checkbox" name="test_vals" value="1" checked="checked" /> <label for="add_module_{$rec.id}_text">Добавить первоначальное наполнение</label>
					</li>
					<li>
						<input type="submit" value="Установить" />
					</li>
				</ul>
			</form>
		</div>
	</li>
{/foreach}
</ol>