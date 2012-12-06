	<div class="control-group acms_panel_groups acms_panel_group_{$group_key} acms_field_{$field.type}">
		<label class="control-label" for="field_{$field.sid}">{$field.title}</label>
		<div class="controls">
			<div style="margin-bottom: 3px;">
				<span class="label label-info" style="display:inline-block; width:28%; margin-left:20px;">Характеристика</span>
				<span class="label label-info" style="display:inline-block; width:15%; margin-left:5px;">Значение</span>
				<span class="label label-info" style="display:inline-block; width:7%; margin-left:5px;">Ед.изм.</span>
				<span class="label label-info" style="display:inline-block; width:15%; margin-left:5px;">Тип поля</span>
				<span class="label label-info" style="display:inline-block; width:10%; margin-left:5px;">Группа</span>
			</div>
			<ul id="field_{$field.sid}_params" class="unstyled sortable" style="padding:0;">
			{foreach from=$field.value item=param key=key}
				<li id="field_{$field.sid}_{$key}" class="dont_check{if $param.header} well{/if}">
					<i class="icon-resize-vertical"></i> 
					<input type="text" name="{$field.sid}[title][]" id="field_{$field.sid}_{$key}_title" value="{$param.title}" style="width:28%;" />
					<input type="hidden" name="{$field.sid}[header][]" id="field_{$field.sid}_{$key}_header" value="0" />
					<input type="text" name="{$field.sid}[value][]" id="field_{$field.sid}_{$key}_value" value="{$param.value}" style="width:15%;{if $param.header} display:none;{/if}" />
					<input type="text" name="{$field.sid}[ed][]" id="field_{$field.sid}_{$key}_ed" value="{$param.ed}" style="width:7%;{if $param.header} display:none;{/if}" />
					<select name="{$field.sid}[type][]" id="field_{$field.sid}_{$key}_type" style="width:17%;{if $param.header} display:none;{/if}">
						<option value="string"{if $param.type == 'string'} selected="selected"{/if}>строка</option>
						<option value="int"{if $param.type == 'int'} selected="selected"{/if}>целое число</option>
						<option value="float"{if $param.type == 'float'} selected="selected"{/if}>дробное число</option>
						<option value="boolean"{if $param.type == 'boolean'} selected="selected"{/if}>да/нет</option>
					</select>
					<input type="text" name="{$field.sid}[group][]" id="field_{$field.sid}_{$key}_group" value="{$param.group}" style="width:10%;{if $param.header} display:none;{/if}" />
					<a href="" class="delete"><i class="icon-remove-sign"></i></a>
					<a href="" class="header"><i class="icon-text-height"></i></a>
				</li>
			{/foreach}
				<li id="field_{$field.sid}_{$key+1}" class="dont_check">
					<i class="icon-resize-vertical"></i> 
					<input type="text" name="{$field.sid}[title][]" id="field_{$field.sid}_{$key+1}_title" value="" style="width:28%;" />
					<input type="hidden" name="{$field.sid}[header][]" id="field_{$field.sid}_{$key+1}_header" value="0" />
					<input type="text" name="{$field.sid}[value][]" id="field_{$field.sid}_{$key+1}_value" value="" style="width:15%;" />
					<input type="text" name="{$field.sid}[ed][]" id="field_{$field.sid}_{$key+1}_ed" value="" style="width:7%;" />
					<select name="{$field.sid}[type][]" id="field_{$field.sid}_{$key}_type" style="width:17%;">
						<option value="string">строка</option>
						<option value="int">целое число</option>
						<option value="float">дробное число</option>
						<option value="boolean">да/нет</option>
					</select>
					<input type="text" name="{$field.sid}[group][]" id="field_{$field.sid}_{$key+1}_group" value="" style="width:10%;" />
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
