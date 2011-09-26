<p class="a_field">
	<label for="f_{$field_sid}">{$field.title}:</label>
	<select name="{$field.sid}" id="f_{$field_sid}" style="width:300px">
	{foreach from=$field.value item=val}
		<option value="{$val.value}"{if $val.selected} selected="selected"{/if}>{$val.title}{if !strlen($val.title)}[пусто]{/if}</option>
	{/foreach}
	</select>
</p>
