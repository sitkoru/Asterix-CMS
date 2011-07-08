<p>
{$field.title}:<br />
<select name="{$field.sid}" style="width:300px">
{foreach from=$field.value item=val}
	<option value="{$val.id}" style="color:{if $val.admin}#000{else}#999{/if}; font-weight:{if $val.selected}bold;" selected="selected{else}normal'{/if}">{$val.title}</option>
{/foreach}
</select>
<br />

</p>
