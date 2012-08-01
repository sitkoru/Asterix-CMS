	<label for="field_{$field.sid}">{$field.title}:</label>
	<ul id="field_{$field.sid}_params" class="sortable" style="padding:0;">
	{foreach from=$field.value item=param key=key}
		<li id="field_{$field.sid}_{$key}">
			<input type="text" name="{$field.sid}[{$key}][title]" id="field_{$field.sid}_{$key}_title" value="{$param.title}" style="width:45%;" />
			<input type="hidden" name="{$field.sid}[{$key}][delete]" id="field_{$field.sid}_{$key}_delete" value="0" />
			<input type="hidden" name="{$field.sid}[{$key}][header]" id="field_{$field.sid}_{$key}_header" value="0" />
			<input type="text" name="{$field.sid}[{$key}][value]" id="field_{$field.sid}_{$key}_value" value="{$param.value}" style="width:35%;" />
			<img src="http://src.sitko.ru/a/i/delete.png" alt="" title="Удалить" class="delete" />
			<img src="http://src.sitko.ru/a/i/header.png" alt="" title="Сделать заголовком" class="header" />
		</li>
	{/foreach}
	</ul>
	<div>
		<a href="#" class="add" sid="{$field.sid}">
			<img src="http://src.sitko.ru/a/i/btn_plus_white.gif" alt="Добавить ещё характеристику">
		</a>
	</div>
