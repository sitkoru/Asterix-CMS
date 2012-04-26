
{if !isset($recs)}
	{assign var=recs value=$action.groups.global.fields}
{/if}

{if count($recs)}
<ol class="acms_tree">
{foreach from=$recs item=rec key=key1}
	<li class="acms_sub">
		<a href="#" class="open" OnClick="JavaScript: getsubtree('{$rec.url_clear}', 'list_{$action.prefix}_{$rec.id}'); return false;">+</a>
				
		<span class="acms_title">{$rec.title}</span> - {foreach from=$rec.manage item=button} <a class="acms_tree_tools" href="#" OnClick="JavaScript: call2('{$button.method}', '{$button.action}', '{$rec.url}', '{$rec.result_id}'); return false;">{$button.title}</a>{/foreach}

		<div id="list_{$action.prefix}_{$rec.id}"{if $rec.sid neq 'index'} style="display:none;"{/if}{if count($rec.sub) gt 0}>
		{if count($rec.sub) gt 0}
			<ol class="acms_tree">
			{foreach from=$rec.sub item=subrec key=key2}
				<li class="acms_sub">
					<a href="#" class="open" OnClick="JavaScript: getsubtree('{$subrec.url_clear}','list_{$rec.id}_{$subrec.id}'); return false;">+</a>
				
					<span class="acms_title">{$subrec.title}</span> - {foreach from=$subrec.manage item=button} <a class="acms_tree_tools" href="#" OnClick="JavaScript: call2('{$button.method}', '{$button.action}', '{$subrec.url}', '{$subrec.result_id}'); return false;">{$button.title}</a> {/foreach}

					<div id="list_{$rec.id}_{$subrec.id}" style="display:none;"></div>

				</li>
			{/foreach}
			</ol>
		{/if}
		</div>{else}></div>{/if}

	</li>{/foreach}
</ol>
{/if}
