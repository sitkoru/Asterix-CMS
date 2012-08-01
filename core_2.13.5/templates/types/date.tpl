{$field.title}:<br />
<input name="{$field.sid}[day]" value="{$field.value.day}" size="2" />
<select name="{$field.sid}[month]">
	<option value="1"{if $field.value.month eq 1} selected="selected"{/if}>января</option>
	<option value="2"{if $field.value.month eq 2} selected="selected"{/if}>февраля</option>
	<option value="3"{if $field.value.month eq 3} selected="selected"{/if}>марта</option>
	<option value="4"{if $field.value.month eq 4} selected="selected"{/if}>апреля</option>
	<option value="5"{if $field.value.month eq 5} selected="selected"{/if}>мая</option>
	<option value="6"{if $field.value.month eq 6} selected="selected"{/if}>июня</option>
	<option value="7"{if $field.value.month eq 7} selected="selected"{/if}>июля</option>
	<option value="8"{if $field.value.month eq 8} selected="selected"{/if}>августа</option>
	<option value="9"{if $field.value.month eq 9} selected="selected"{/if}>сентября</option>
	<option value="10"{if $field.value.month eq 10} selected="selected"{/if}>октября</option>
	<option value="11"{if $field.value.month eq 11} selected="selected"{/if}>ноября</option>
	<option value="12"{if $field.value.month eq 12} selected="selected"{/if}>декабря</option>
</select>
<input name="{$field.sid}[year]" value="{$field.value.year}" size="4" />&nbsp&nbsp;
