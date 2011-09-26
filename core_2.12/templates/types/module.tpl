<p class="a_field">
	<label for="field_{$field.sid}">{$field.title}:</label>
	<input type="hidden" id="field_{$field.sid}_memory" value="" />
	<script>
		var value=$j('#field_sid').val();
		$j('#field_{$field.sid}_memory').val(value);
	</script>
	<select id="field_{$field.sid}" name="{$field.sid}" style="width:300px" OnChange="JavaScript:
		var value=$j('#field_{$field.sid}').val();

		if(value=='')
			$j('#field_sid').attr('readonly',false);
		else
			$j('#field_sid').attr('readonly','readonly');

		if(value=='')value=$j('#field_{$field.sid}_memory').val();
		$j('#field_sid').val(value);

	">
	{foreach from=$field.value item=val}
		<option value="{$val.value}"{if $val.selected} selected="selected"{/if}>{$val.title}{if !strlen($val.title)}- ссылка на модуль отсутсвует -{/if}</option>
	{/foreach}
</select>

</p>
