{if $rec.sub}
	<ol>
	{foreach from=$rec.sub item=sub}{if !$sub.module || $sub.module == 'start'}
		<li rec_id="{$sub.id}" module_sid="{$field.module}" structure_sid="{$field.structure_sid}" class="acms_panel_groups{if !$field.sortable} not_sorted{/if}">
<!--			{if $field.sortable}<i class="icon-resize-vertical"></i>{/if}-->
			<a href="/admin{$sub.url_clear}.editRecord.html">{$sub.title}</a> 
			{if $sub.sub}<i class="icon-random" style="opacity:0.3">></i>{/if}
			{if !$sub.is_link_to_module}<i class="icon-remove" style="opacity:0.3"></i> {/if}

		{if !$sub.sub.0.module || $sub.sub.0.module == 'start'}
			{if count( $sub.sub )>3 }
				<br />
				<i OnClick="$('#sub{$sub.id}_{$field.module}_{$field.structure_sid}').toggle('fast');" style="cursor:pointer;">…</i>
				<div id="sub{$sub.id}_{$field.module}_{$field.structure_sid}" style="display:none;">
			{/if}
			{include file="$path_admin_templates/forms/sub.tpl" rec=$sub}
			{if count( $sub.sub )>3 }
					<br />
				</div>
			{/if}
		{/if}

		</li>
	{/if}{/foreach}
	</ol>
{/if}
