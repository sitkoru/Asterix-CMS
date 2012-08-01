{if count($field.value) > 1}
	<label for="field_{$field.sid}">{$field.title}:</label>
	<select name="{$field.sid}[]" id="field_{$field.sid}" multiple="multiple" style="width:300px" size="8">
	{foreach from=$field.value item=value}
		<option value="{$value.value}"{if $value.selected} selected="selected"{/if}>{$value.title}</option>
	{/foreach}
	</select>
	<br /><small class="grey">Чтобы выбрать несколько наименований, используйте клавишу Ctrl</small>
{else}
	<label>{$field.title}: {$field.value.0.title}</label>
	<input type="hidden" name="{$field.sid}" value="{$field.value.0.value}" />
{/if}
