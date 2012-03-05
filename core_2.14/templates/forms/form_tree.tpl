{include file="$path_admin_templates/forms/tabs.tpl"}

<div class="row">
	<div class="span12">

		<ol>
		{assign var=group_key value=0}
		{foreach from=$action.groups item=group key=key}{if $group.fields}
			{assign var=group_key value=$group_key+1}

			{foreach from=$group.fields item=field}
			<li rec_id="{$field.id}" module_sid="{$field.module}" structure_sid="{$field.structure_sid}" class="acms_panel_groups acms_panel_group_{$group_key}{if !$field.sortable} not_sorted{/if}"{if $key != main} style="display:none;"{/if}>
				{if $field.sortable}<i class="icon-resize-vertical"></i>{/if}
				<i class="icon-remove"></i>
				{if $field.sub}<i class="icon-random"></i>{/if}
				<a href="/admin{$field.url_clear}.editRecord.html">{$field.title}</a>
				{if $field.module == 'users'} [{$field.login}]{/if}
{include file="$path_admin_templates/forms/sub.tpl" rec=$field}
			</li>
			{/foreach}
			
		{/if}{/foreach}
		</ol>
		
	</div>
	
	<div class="span4">
	</div>
	  
</div>
