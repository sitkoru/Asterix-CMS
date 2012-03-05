
<h3>Модули системы</h3>

<h3>Установленные модули</h3>

<ol>
{foreach from=$action.recs item=module key=key}
	{if $module.sid and ($module.prototype!='users') }
	<li>
		{$module.title} [{$module.prototype}] 
		<a href="#" OnClick="$('#{$module.id}_delete').toggle('fast'); return false;">удалить</a>
		<div id="{$module.id}_structure" style="display:none;">
		{foreach from=$module.structure item=structure}
			<strong>{$structure.title}</strong>
			<table>
				<tr>
					<td>Название</td>
					<td>Тип поля</td>
				</tr>
			{foreach from=$structure.fields item=field key=sid}
				<tr>
					<td>{$field.title}</td>
					<td>
						<select name="type">
						{foreach from=$action.types item=type key=type_sid}
							<option value="{$type_sid}"{if $field.type == $type_sid} selected="selected"{/if}>{$type->default_settings.title}</option>
						{/foreach}
						</select>
					</td>
				</tr>
			{/foreach}
			</table>
		{/foreach}
			<br />
		</div>
		
		<div id="{$module.id}_data" style="display:none;">
		{foreach from=$module.structure item=structure}
			<strong>{$structure.title}</strong>
			<table>
				<tr>
					<td>ID</td>
					<td>Название</td>
					<td>Дата создания</td>
					<td>URL</td>
					<td></td>
				</tr>
			{foreach from=$structure.recs item=row}
				<tr>
					<td>{$row.id}</td>
					<td>{$row.title}</td>
					<td>{$row.date_public}</td>
					<td><a href="{$row.url}" target="_blank">смотреть</a></td>
					<td><a href="{$row.url}" OnClick="call2('GET', 'edit', '{$row.url}?method_marker=admin&action=edit', ''); return false;">изменить</a></td>
				</tr>
			{/foreach}
			</table>
		{/foreach}
			<br />
		</div>
		
		<div id="{$module.id}_delete" style="display:none; border:2px solid red; margin:10px; padding:10px; font-size:1.4em; border-radius:10px;">
			<form id="{$module.id}_delete_form" class="interface ajax" method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="module_delete" />
				<input type="hidden" name="module_id" value="{$module.prototype}" />
				Подтвердите ваше действие:<br />
				<ul style="margin:10px 0;">
					<li><input type="checkbox" name="delete_module" id="{$module.id}_delete_module" value="1" /> <label for="{$module.id}_delete_module">Удалить модуль</label></li>
					<li><input type="checkbox" name="delete_templates" id="{$module.id}_delete_templates" value="1" /> <label for="{$module.id}_delete_templates">Удалить шаблоны</label></li>
					<li><input type="checkbox" name="delete_data" id="{$module.id}_delete_data" value="1" /> <label for="{$module.id}_delete_data">Удалить данные модуля</label></li>
					<li><input type="checkbox" name="delete_rec" id="{$module.id}_delete_rec" value="1" /> <label for="{$module.id}_delete_rec">Удалить раздел сайта</label></li>
				</ul>
				<input type="submit" value="Я подтверждаю безвозвратное удаление" style="font-size:1.4em;" />
			</form>
		</div>
		
	</li>
	{/if}
{/foreach}
</ol>

<h3>Другие доступные модули</h3>
<ol>
{foreach from=$action.recs_all item=rec key=key}
	<li>
		{$rec.title} [{$rec.prototype}] - <a href="#" OnClick="$('#add_module_{$rec.prototype}').toggle('fast'); return false;">подключить</a>
		<div id="add_module_{$rec.prototype}" style="display:none; border:2px solid #ccc; margin:10px; padding:10px; font-size:1.4em;">
			<form id="{$module.id}_update_form" class="interface ajax" method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="module_install" />
				<input type="hidden" name="module_id" value="{$rec.prototype}" />
				<input type="hidden" name="module_title" value="{$rec.title}" />
				<ul>
					<li>Куда поместить модуль: 
						<select name="to" id="add_module_{$rec.prototype}_to">
							<option value="0" style="color:green;">создать новый раздел "{$rec.title}"</option>
						{foreach from=$action.root_recs item=rt}
							<option value="{$rt.id}" style="color:red;">в "{$rt.title}" *</option>
						{/foreach}
						</select><br ><span style="color:red; font-size:0.7em;">* - будет изменён URL раздела и подразделов</span>
					</li>
					<li>
						<input id="add_module_{$rec.prototype}_test" type="checkbox" name="test_vals" value="1" checked="checked" /> <label for="add_module_{$rec.prototype}_test">Добавить первоначальное наполнение</label>
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