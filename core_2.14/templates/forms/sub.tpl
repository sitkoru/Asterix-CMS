{if $rec.sub}
	<ol>
	{foreach from=$rec.sub item=sub}{if !$sub.module || $sub.module == 'start'}
		<li rec_id="{$sub.id}" module_sid="{$field.module}" structure_sid="{$field.structure_sid}" class="acms_panel_groups{if !$field.sortable} not_sorted{/if}">
<!--			{if $field.sortable}<i class="icon-resize-vertical"></i>{/if}-->
			{if $sub.sub}<i class="icon-random"></i>{/if}
			<a href="/admin{$sub.url_clear}.editRecord.html">{$sub.title}</a> 
			{if !$sub.is_link_to_module}<i class="icon-remove"></i> {/if}
			{if count( $sub.sub )>3 }
				<i class="icon-list" OnClick="$('#sub{$rec.id}_{$field.module}_{$field.structure_sid}').toggle();"></i><br />
				<div id="sub{$rec.id}_{$field.module}_{$field.structure_sid}">
			{/if}
	{include file="$path_admin_templates/forms/sub.tpl" rec=$sub}
			{if count( $sub.sub )>3 }
				</div>
			{/if}
		</li>
	{/if}{/foreach}
	</ol>
{/if}
