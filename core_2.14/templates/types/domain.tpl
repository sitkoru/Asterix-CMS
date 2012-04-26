{if count($field.value) > 1}
	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<select size=8 name="{$field.sid}[]" id="field_{$field_sid}">
			{foreach from=$field.value item=val}
				<option value="{$val.value}"{if $val.selected} selected="selected"{/if}>{$val.title}{if !strlen($val.title)}[пусто]{/if}</option>
			{/foreach}
			</select>
			<span class="help-block">
				Чтобы выбрать несколько наименований, используйте клавишу Ctrl
			</span>
		</div>
	</div>

{else}
	<label>{$field.title}: {$field.value.0.title}</label>
	<input type="hidden" name="{$field.sid}" value="{$field.value.0.value}" />
{/if}
