	<label for="field_{$field.dep_path_name}">{$field.title}:</label>
	<select name="{$field.dep_path_name}" id="field_{$field.dep_path_name}">
	{foreach from=$field.value item=value}
		<option value="{$value.sid}"{if $value.selected}  style="background-color:#39f; color:#fff;"{/if}{if $value.disabled} disabled="disabled"{/if}{if $value.selected} selected="selected"{/if}>
			{section name=pre start=1 loop=$value.tree_level max=$value.tree_level}
				&nbsp;&nbsp;&nbsp;&nbsp;|
			{/section}
			{$value.title}
		</option>
	{/foreach}
	</select>
