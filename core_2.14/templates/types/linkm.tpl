	<div class="control-group acms_panel_groups acms_panel_group_{$group_key}"{if $key != main} style="display:none;"{/if}>
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<select name="{$field.sid}[]" class="" size=8 id="field_{$field_sid}" multiple>
				<option value="0">- пусто -</option>
			{foreach from=$field.value item=value}
				<option value="{$value.value}"{if $value.selected eq 1} selected="selected"{/if}>
				{section name=pre start=1 loop=$value.tree_level max=$value.tree_level}
					&nbsp;&nbsp;&nbsp;&nbsp;&#8680;
				{/section}
					{$value.title}{if !strlen($value.title)}[пусто]{/if}
				</option>
			{/foreach}
			</select>
		</div>
	</div>
