{if count($action.recs)}
<ol class="tree">
{foreach from=$action.recs item=rec key=key1}
	<li>
		<a href="#" class="open" OnClick="JavaScript: getsubtree('{$rec.url_clear}', 'list_{$action.prefix}_{$rec.id}'); return false;">+</a>
				
		{$rec.title} - {foreach from=$rec.manage item=button} <a href="#" OnClick="JavaScript: call2('{$button.method}', '{$button.action}', '{$rec.url}', '{$rec.result_id}'); return false;">{$button.title}</a>{/foreach}

		<div id="list_{$action.prefix}_{$rec.id}"{if $rec.sid neq 'index'} style="display:none;"{/if}{if count($rec.sub) gt 0}>
		{if count($rec.sub) gt 0}
			<ol class="tree">
			{foreach from=$rec.sub item=subrec key=key2}
				<li>
					<a href="#" class="open" OnClick="JavaScript: getsubtree('{$subrec.url_clear}','list_{$rec.id}_{$subrec.id}'); return false;">+</a>
				
					{$subrec.title} - {foreach from=$subrec.manage item=button} <a href="#" OnClick="JavaScript: call2('{$button.method}', '{$button.action}', '{$subrec.url}', '{$subrec.result_id}'); return false;">{$button.title}</a> {/foreach}

					<div id="list_{$rec.id}_{$subrec.id}" style="display:none;"></div>

				</li>
			{/foreach}
			</ol>
		{/if}
		</div>{else}></div>{/if}

	</li>{/foreach}
</ol>
{/if}
