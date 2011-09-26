	<label for="field_{$field.sid}">{$field.title}:</label>
	<select name="{$field.sid}[]" multiple="multiple" size="8" id="field_{$field.sid}">
	{foreach from=$field.value item=value}
		<option value="{$value.value}"{if $value.selected} selected="selected"{/if}>{$value.title}{if !strlen($value.title)}[пусто]{/if}</option>
	{/foreach}
	</select>
	<br /><small class="grey">Чтобы выбрать несколько наименований, используйте клавишу Ctrl</small>
