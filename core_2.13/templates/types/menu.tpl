	<label for="id_{$field_sid}">{$field.title}:</label>
	<select name="{$field.sid}" id="id_{$field_sid}">
	{foreach from=$field.value item=val}
		<option value="{$val.value}"{if $val.selected} selected="selected"{/if}>{$val.title}{if !strlen($val.title)}[пусто]{/if}</option>
	{/foreach}
	</select>
