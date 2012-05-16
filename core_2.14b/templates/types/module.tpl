	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<select name="{$field.sid}" class="" id="field_{$field_sid}"OnChange="JavaScript:
		var value=$('#field_{$field.sid}').val();

		if(value=='')
			$('#field_sid').attr('readonly',false);
		else
			$('#field_sid').attr('readonly','readonly');

		if(value=='')value=$('#field_{$field.sid}_memory').val();
		$('#field_sid').val(value);

	">
			{foreach from=$field.value item=val}
				<option value="{$val.value}"{if $val.selected} selected="selected"{/if}>{$val.title}{if !strlen($val.title)}- ссылка на модуль отсутсвует -{/if}</option>
			{/foreach}
			</select>
		</div>
	</div>
