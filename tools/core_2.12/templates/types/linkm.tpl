<p class="a_field">
	<label for="field_{$field.sid}">{$field.title}:</label>
	<select name="{$field.sid}[]" id="field_{$field.sid}" style="width:300px" multiple="multiple">
		<option value="0">- пусто -</option>
{foreach from=$field.value item=value}
		<option value="{$value.value}"{if $value.selected eq 1} selected="selected"{/if}>{$value.title}{if !strlen($value.title)}[пусто]{/if}</option>
{/foreach}
	</select>
</p>
