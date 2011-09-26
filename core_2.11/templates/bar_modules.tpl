
<h3>Модули системы</h3>
<form id="tmpl_save" action="/" method="POST">
<input type="hidden" name="action" value="templates" />
<a href="#" OnClick="return false;">Добавить модуль</a><br /><br />

<h3>Установленные модули</h3>
<ol>
{foreach from=$action.recs item=rec key=key}
	<li>
		{$rec.title} [{$rec.prototype}] 
		<a href="#" OnClick="$j('#{$rec.id}_structure').toggle(); return false;">структура</a> 
		<a href="#" OnClick="$j('#{$rec.id}_data').toggle(); return false;">данные</a>
		<div id="{$rec.id}_structure" style="display:none;">
		{foreach from=$rec.structure item=str}
			<strong>{$str.title}</strong>
			<table>
				<tr>
					<td>Название</td>
					<td>Тип поля</td>
					<td>Параметры</td>
				</tr>
			{foreach from=$str.fields item=field}
				<tr>
					<td>{$field.title}</td>
					<td>
						<select name="type">
							<option value=""{if $field.type == 'text'} selected="selected"></option>
						</select>
					</td>
					<td>Поле "{$field.title}"</td>
				</tr>
			{/foreach}
			</table>
		{/foreach}
			<br />
		</div>
		<div id="{$rec.id}_data" style="display:none;">
			Данные
			<br />
		</div>
	</li>
{/foreach}
</ol>

<h3>Все доступные модули</h3>
<ol>
{foreach from=$action.recs_all item=rec key=key}
	<li>{$rec.title} [{$rec.prototype}] - <a href="#" OnClick="return false;">подключить</a></li>
{/foreach}
</ol>