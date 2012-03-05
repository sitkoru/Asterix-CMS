	{if $rec.sub}
	<ol>
	{foreach from=$rec.sub item=sub}
		<li rec_id="{$sub.id}" module_sid="{$field.module}" structure_sid="{$field.structure_sid}"{if !$field.sortable} class="not_sorted"{/if}>
			{if $field.sortable}<i class="icon-resize-vertical"></i>{/if}
			<i class="icon-remove"></i> 
			{if $sub.sub}<i class="icon-random"></i>{/if}
			<a href="/admin{$sub.url_clear}.editRecord.html">{$sub.title}</a>
{include file="$path_admin_templates/forms/sub.tpl" rec=$sub}
		</li>
	{/foreach}
	</ol>
	{/if}
