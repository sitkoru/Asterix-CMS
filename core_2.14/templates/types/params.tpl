	<div class="control-group acms_panel_groups acms_panel_group_{$group_key} acms_field_{$field.type}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<ul id="field_{$field.sid}_params" class="unstyled sortable" style="padding:0;">
			{foreach from=$field.value item=param key=key}
				<li id="field_{$field.sid}_{$key}" class="dont_check{if $param.header} well{/if}">
					<i class="icon-resize-vertical"></i> 
					<input type="text" name="{$field.sid}[title][]" id="field_{$field.sid}_{$key}_title" value="{$param.title}" style="width:45%;" />
					<input type="hidden" name="{$field.sid}[header][]" id="field_{$field.sid}_{$key}_header" value="0" />
					<input type="text" name="{$field.sid}[value][]" id="field_{$field.sid}_{$key}_value" value="{$param.value}" style="width:35%;{if $param.header} display:none;{/if}" />
					<a href="" class="delete"><i class="icon-remove-sign"></i></a>
					<a href="" class="header"><i class="icon-text-height"></i></a>
				</li>
			{/foreach}
				<li id="field_{$field.sid}_{$key+1}" class="dont_check">
					<i class="icon-resize-vertical"></i> 
					<input type="text" name="{$field.sid}[title][]" id="field_{$field.sid}_{$key+1}_title" value="" style="width:45%;" />
					<input type="hidden" name="{$field.sid}[header][]" id="field_{$field.sid}_{$key+1}_header" value="0" />
					<input type="text" name="{$field.sid}[value][]" id="field_{$field.sid}_{$ke+1}_value" value="" style="width:35%;" />
					<a href="" class="delete"><i class="icon-remove-sign"></i></a>
					<a href="" class="header"><i class="icon-text-height"></i></a>
				</li>
			</ul>
			<a class="btn add"><i class="icon-plus-sign"></i> Добавить ещё характеристику</a>
		</div>
	</div>
	<style type="text/css">
		.
	</style>
