	{if $rec.sub}
	<ol>
	{foreach from=$rec.sub item=sub}{if !$sub.module || $sub.module == 'start'}
		<li rec_id="{$sub.id}" module_sid="{$field.module}" structure_sid="{$field.structure_sid}" class="acms_panel_groups{if !$field.sortable} not_sorted{/if}">
			{if $field.sortable}<i class="icon-resize-vertical"></i>{/if}
			{if !$sub.is_link_to_module}<i class="icon-remove"></i> {/if}
			{if $sub.sub}<i class="icon-random"></i>{/if}
			<a href="/admin{$sub.url_clear}.editRecord.html">{$sub.title}</a> 
	{include file="$path_admin_templates/forms/sub.tpl" rec=$sub}
		</li>
	{/if}{/foreach}
	</ol>
	{/if}
