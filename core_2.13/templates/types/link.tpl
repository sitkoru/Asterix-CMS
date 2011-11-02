	<label for="field_{$field.sid}">{$field.title}:</label>
	<select name="{$field.sid}" id="field_{$field.sid}">
		<option value="0">- пусто -</option>
	{foreach from=$field.value item=value}
		<option value="{$value.value}"{if $value.selected eq 1} selected="selected"{/if}>
			{section name=pre start=1 loop=$value.tree_level max=$value.tree_level}
				&nbsp;&nbsp;&nbsp;&nbsp;|
			{/section}
			{$value.title}{if !strlen($value.title)}[пусто]{/if}
		</option>
	{/foreach}
	</select>
