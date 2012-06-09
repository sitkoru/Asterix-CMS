<div class="tabbable">

	{include file="$path_admin_templates/forms/tabs.tpl"}

	<div class="tab-content">

	{assign var=group_key value=0}
	{foreach from=$action.groups item=group key=key}{if $group.fields}
		{assign var=group_key value=$group_key+1}
		<div class="tab-pane{if $group_key == 1} active{/if}" id="{$group_key}">
			<ol{if isset($group.fields.0.sortable)} class="sortable"{/if}>

			{foreach from=$group.fields item=field}
				<li rec_id="{$field.id}" module_sid="{$field.module}" structure_sid="{$field.structure_sid}" class="acms_panel_groups{if !$field.sortable} not_sorted{/if}">
					{if $field.sortable}<i class="icon-resize-vertical"></i>{/if}
					<i class="icon-remove"></i>
					{if $field.sub}<i class="icon-random"></i>{/if}
					<a href="/admin{$field.url_clear}.editRecord.html">{$field.title}</a> 
				{if $field.module == 'users'} [{$field.login}]{/if}
				{include file="$path_admin_templates/forms/sub.tpl" rec=$field}
				</li>
			{/foreach}
				
			</ol>
		</div>
	{/if}{/foreach}

	</div>
		
</div>
