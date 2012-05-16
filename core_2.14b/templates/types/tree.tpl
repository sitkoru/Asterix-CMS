	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}">
		<label for="id_{$field_sid}">{$field.title}</label>
		<div class="controls">
			<select name="{$field.sid}" class="" id="id_{$field_sid}">
			{foreach from=$field.value item=value}
				<option value="{$value.sid}"{if $value.disabled} disabled="disabled"{/if}{if $value.selected} selected="selected"{/if}>
				{section name=pre start=1 loop=$value.tree_level max=$value.tree_level}
					&nbsp;&nbsp;&nbsp;&nbsp;&#8680;
				{/section}
					{$value.title}
				</option>
			{/foreach}
			</select>
		</div>
	</div>
