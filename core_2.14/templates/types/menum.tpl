	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}" mult>
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<select size=8 name="{$field.sid}[]" class="" style="width:80%; display:block;" id="field_{$field_sid}" multiple{if $field.required} required="required"{/if}>
			{if !$field.required}
				<option value="0">- пусто -</option>
			{/if}
			{foreach from=$field.value item=val}
				<option{if !$field.required} size=8{/if} value="{$val.value}"{if $val.selected} selected="selected"{/if}>{$val.title}{if !strlen($val.title)}[пусто]{/if}</option>
			{/foreach}
			</select>
			<span class="help-block">
				Чтобы выбрать несколько наименований, используйте клавишу Ctrl
			</span>
		</div>
	</div>
